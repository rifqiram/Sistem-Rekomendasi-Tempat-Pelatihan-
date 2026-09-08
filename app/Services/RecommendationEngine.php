<?php

namespace App\Services;

use App\Models\Pelatihan;
use App\Models\Profile;
use App\Models\QuestionnaireResponse;
use App\Models\Recommendation;
use App\Models\TrainingCenter;

class RecommendationEngine
{
    /**
     * Jalankan Recommendation Engine untuk user.
     * Mengikuti 3 Fase dari AGENTS.md
     */
    public function generateForUser(int $userId): void
    {
        $profile = Profile::where('user_id', $userId)->first();
        $questionnaire = QuestionnaireResponse::where('user_id', $userId)->first();

        if (!$profile || !$questionnaire) {
            return; // Profil/Kuesioner belum lengkap, skip.
        }

        $answers = json_decode($questionnaire->answers, true) ?? [];

        // Fase 1: Hard Filter
        $eligibleTrainings = $this->applyHardFilter($answers);

        if ($eligibleTrainings->isEmpty()) {
            // Bersihkan rekomendasi lama jika tidak ada yang eligible
            Recommendation::where('user_id', $userId)->delete();
            return;
        }

        // Fase 2: Weighted Score & Aggregate ke Training Center
        $scoredCenters = $this->calculateWeightedScore($eligibleTrainings, $answers, $profile);

        // Fase 3: Persist Recommendation
        $this->persistRecommendations($userId, $scoredCenters);

        \App\Models\LogActivity::create([
            'user_id' => $userId,
            'activity_type' => 'generate_recommendation',
        ]);
    }

    private function applyHardFilter(array $answers)
    {
        // Status Aktif: is_active = true
        // Pelatihan memiliki training_center_id
        $query = Pelatihan::where('is_active', true)
            ->whereNotNull('training_center_id')
            ->withCount(['enrollments as approved_enrollments_count' => fn($q) => $q->where('status', 'approved')]);

        // Bidang Pelatihan
        if (isset($answers['bidang_diminati'])) {
            $query->where('interest_category', $answers['bidang_diminati']);
        }

        // Skill Level
        if (isset($answers['tingkat_keahlian'])) {
            $userSkill = strtolower($answers['tingkat_keahlian']);
            if ($userSkill === 'beginner') {
                $query->whereIn('required_skill', ['Beginner', 'beginner']);
            } elseif ($userSkill === 'intermediate') {
                $query->whereIn('required_skill', ['Beginner', 'beginner', 'Intermediate', 'intermediate']);
            }
            // Advanced bisa akses semuanya
        }

        // Metode
        if (isset($answers['metode_pelatihan'])) {
            $query->where(function($q) use ($answers) {
                $q->where('method', $answers['metode_pelatihan'])
                  ->orWhereNull('method')
                  ->orWhere('method', 'Hybrid'); // Hybrid cocok dengan online/offline
            });
        }

        return $query->get();
    }

    private function calculateWeightedScore($trainings, array $answers, Profile $profile)
    {
        $centerScores = [];
        $centerMaxScores = []; // <-- Variable tambahan untuk breakdown data
        $distanceService = new DistanceService();

        // Ambil data training centers sekaligus
        $tcIds = $trainings->pluck('training_center_id')->unique();
        $trainingCenters = TrainingCenter::whereIn('id', $tcIds)->get()->keyBy('id');

        foreach ($trainings as $training) {
            $score = 0;

            $interestScore = 0;
            $skillScore = 0;
            $methodScore = 0;
            $popularityScore = 0;

            // Bidang (35%)
            if (isset($answers['bidang_diminati']) && strtolower($training->interest_category) === strtolower($answers['bidang_diminati'])) {
                $interestScore = 35;
                $score += $interestScore;
            }

            // Skill (20%)
            if (isset($answers['tingkat_keahlian']) && strtolower($training->required_skill) === strtolower($answers['tingkat_keahlian'])) {
                $skillScore = 20;
                $score += $skillScore;
            }

            // Metode (15%)
            if (isset($answers['metode_pelatihan']) && strtolower($training->method) === strtolower($answers['metode_pelatihan'])) {
                $methodScore = 15;
                $score += $methodScore;
            } elseif (strtolower($training->method) === 'hybrid') {
                $methodScore = 10;
                $score += $methodScore; // Partial match
            }

            // Popularitas (10%)
            $popularityVal = min($training->approved_enrollments_count ?? 0, 100);
            $popularityScore = ($popularityVal / 100) * 10;
            $score += $popularityScore;

            // Aggregate ke Training Center: ambil skor base pelatihan tertinggi di TC tersebut
            $tcId = $training->training_center_id;

            if (!isset($centerScores[$tcId]) || $score > $centerScores[$tcId]) {
                $centerScores[$tcId] = $score;
                $centerMaxScores[$tcId] = [
                    'interest' => $interestScore,
                    'skill' => $skillScore,
                    'method' => $methodScore,
                    'popularity' => $popularityScore
                ];
            }
        }

        $scored = [];
        foreach ($centerScores as $tcId => $baseScore) {
            $tc = $trainingCenters->get($tcId);
            $distanceKm = null;
            $distScore = null;
            $finalScore = $baseScore;

            // Hitung Distance (20%) jika koordinat tersedia
            if ($tc && $profile->latitude && $profile->longitude && $tc->latitude && $tc->longitude) {
                $distanceKm = $distanceService->calculateDistance(
                    $profile->latitude, $profile->longitude,
                    $tc->latitude, $tc->longitude
                );

                // Gunakan jarak maksimal dari kuesioner user, fallback ke 100km jika kosong
                $maxDistance = isset($answers['jarak_maksimal']) && is_numeric($answers['jarak_maksimal']) && $answers['jarak_maksimal'] > 0
                    ? (float) $answers['jarak_maksimal']
                    : 100;

                // HARD FILTER: Jarak tidak boleh melebihi maxDistance
                if ($distanceKm > $maxDistance) {
                    continue; // Skip TC ini sepenuhnya
                }

                $distScore = max(0, (1 - ($distanceKm / $maxDistance)) * 20);
                $finalScore += $distScore;
            }

            $scored[] = [
                'training_center_id' => $tcId,
                'score' => round($finalScore, 2),
                'distance' => $distanceKm,
                'score_breakdown' => [
                    'interest' => [
                        'score' => isset($answers['bidang_diminati']) && isset($centerMaxScores[$tcId]['interest']) ? $centerMaxScores[$tcId]['interest'] : 0,
                        'max' => 35,
                        'status' => (isset($answers['bidang_diminati']) && isset($centerMaxScores[$tcId]['interest']) && $centerMaxScores[$tcId]['interest'] == 35) ? 'match' : 'none',
                        'label' => 'Minat yang Anda pilih: ' . (isset($answers['bidang_diminati']) ? $answers['bidang_diminati'] : 'Belum memilih')
                    ],
                    'skill' => [
                        'score' => isset($answers['tingkat_keahlian']) && isset($centerMaxScores[$tcId]['skill']) ? $centerMaxScores[$tcId]['skill'] : 0,
                        'max' => 20,
                        'status' => (isset($answers['tingkat_keahlian']) && isset($centerMaxScores[$tcId]['skill']) && $centerMaxScores[$tcId]['skill'] == 20) ? 'match' : 'none',
                        'label' => 'Keahlian yang Anda pilih: ' . (isset($answers['tingkat_keahlian']) ? $answers['tingkat_keahlian'] : 'Belum memilih')
                    ],
                    'method' => [
                        'score' => isset($answers['metode_pelatihan']) && isset($centerMaxScores[$tcId]['method']) ? $centerMaxScores[$tcId]['method'] : 0,
                        'max' => 15,
                        'status' => (isset($answers['metode_pelatihan']) && isset($centerMaxScores[$tcId]['method'])) ? ($centerMaxScores[$tcId]['method'] == 15 ? 'match' : ($centerMaxScores[$tcId]['method'] == 10 ? 'partial' : 'none')) : 'none',
                        'label' => 'Metode yang Anda pilih: ' . (isset($answers['metode_pelatihan']) ? $answers['metode_pelatihan'] : 'Belum memilih')
                    ],
                    'popularity' => [
                        'score' => isset($centerMaxScores[$tcId]['popularity']) ? round($centerMaxScores[$tcId]['popularity'], 2) : 0,
                        'max' => 10,
                        'status' => (isset($centerMaxScores[$tcId]['popularity']) && $centerMaxScores[$tcId]['popularity'] >= 8) ? 'match' : ((isset($centerMaxScores[$tcId]['popularity']) && $centerMaxScores[$tcId]['popularity'] > 0) ? 'partial' : 'none'),
                        'label' => 'Berdasarkan popularitas (enrollment yang disetujui)'
                    ],
                    'distance' => [
                        'score' => round(isset($distScore) ? $distScore : 0, 2),
                        'max' => 20,
                        'status' => (isset($distScore)) ? ($distScore >= 16 ? 'match' : ($distScore >= 8 ? 'partial' : 'none')) : 'none',
                        'label' => $distanceKm !== null ? round($distanceKm, 2) . ' km dari lokasi Anda (Maks: ' . $maxDistance . ' km)' : 'Jarak tidak diketahui'
                    ]
                ]
            ];
        }

        // Urutkan descending berdasarkan final score
        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $scored;
    }

    private function persistRecommendations(int $userId, array $scoredCenters): void
    {
        // Hapus rekomendasi lama milik user
        Recommendation::where('user_id', $userId)->delete();

        // Ambil Top 5
        $topN = array_slice($scoredCenters, 0, 5);
        $rank = 1;

        foreach ($topN as $item) {
            Recommendation::create([
                'user_id' => $userId,
                'training_center_id' => $item['training_center_id'],
                'score' => $item['score'],
                'distance' => $item['distance'],
                'score_breakdown' => json_encode($item['score_breakdown']),
                'rank' => $rank++
            ]);
        }
    }
}
