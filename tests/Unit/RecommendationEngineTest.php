<?php

namespace Tests\Unit;

use App\Models\Pelatihan;
use App\Models\Profile;
use App\Models\QuestionnaireResponse;
use App\Models\Recommendation;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Services\RecommendationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationEngineTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new RecommendationEngine();
    }

    // ==========================================
    // HELPER
    // ==========================================

    private function createUserWithProfileAndQuestionnaire(array $answers): User
    {
        $user = User::factory()->create(['role' => 'user']);

        Profile::create([
            'user_id' => $user->id,
            'age' => 22,
            'education' => 'S1',
            'district' => 'Kota A',
            'phone' => '081234567890',
            // Default location
            'latitude' => -7.6400,
            'longitude' => 111.3200,
        ]);

        QuestionnaireResponse::create([
            'user_id' => $user->id,
            'answers' => json_encode($answers),
        ]);

        return $user;
    }

    private function createTraining(array $overrides = []): Pelatihan
    {
        // Pastikan Training Center ada
        $tcId = $overrides['training_center_id'] ?? null;
        if (!$tcId) {
            $tc = TrainingCenter::create([
                'nama' => 'Dummy TC',
                'alamat' => 'Alamat TC',
                'latitude' => -7.6500,
                'longitude' => 111.3300,
                'telepon' => '000',
            ]);
            $tcId = $tc->id;
        }

        return Pelatihan::create(array_merge([
            'judul' => 'Training Default',
            'deskripsi' => 'Deskripsi default',
            'interest_category' => 'IT',
            'method' => 'Online',
            'location' => 'Kota A',
            'required_skill' => 'Beginner',
            'priority' => 3,
            'popularity' => 50,
            'kategori' => 'IT',
            'level' => 'Beginner',
            'durasi' => '10 Jam',
            'sertifikat' => 'Ya',
            'mentor_id' => null,
            'training_center_id' => $tcId,
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-07-10',
            'is_active' => true,
            'status' => 'Aktif',
        ], $overrides));
    }

    // ==========================================
    // PHASE 1: HARD FILTER
    // ==========================================

    public function test_hard_filter_eliminates_advanced_training_for_beginner_user(): void
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'bidang_diminati' => 'IT',
            'metode_pelatihan' => 'Online',
            'tingkat_keahlian' => 'Beginner',
        ]);

        $trainingA = $this->createTraining([
            'judul' => 'Training A - Beginner',
            'required_skill' => 'Beginner',
        ]);

        // Berikan TC berbeda untuk Training B agar tidak agregasi menutupi skor
        $tcB = TrainingCenter::create(['nama' => 'TC B', 'alamat' => 'Alamat B', 'telepon' => '0']);

        $trainingB = $this->createTraining([
            'judul' => 'Training B - Advanced',
            'required_skill' => 'Advanced',
            'training_center_id' => $tcB->id,
        ]);

        $this->engine->generateForUser($user->id);

        $recommendations = Recommendation::where('user_id', $user->id)->get();

        $tcIds = $recommendations->pluck('training_center_id')->toArray();
        $this->assertContains($trainingA->training_center_id, $tcIds);
        $this->assertNotContains($trainingB->training_center_id, $tcIds);
    }

    public function test_tc_beyond_max_distance_is_excluded()
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'jarak_maksimal' => 10,
            'bidang_diminati' => 'Programming',
        ]);

        $tcWithin = TrainingCenter::create([
            'nama' => 'TC Dalam Radius',
            'alamat' => 'Alamat',
            'telepon' => '000',
            'latitude' => -7.6500,
            'longitude' => 111.3200,
        ]);

        $tcBeyond = TrainingCenter::create([
            'nama' => 'TC Luar Radius',
            'alamat' => 'Alamat',
            'telepon' => '000',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
        ]);

        $this->createTraining(['training_center_id' => $tcWithin->id, 'interest_category' => 'Programming']);
        $this->createTraining(['training_center_id' => $tcBeyond->id, 'interest_category' => 'Programming']);

        $this->engine->generateForUser($user->id);
        $recommendations = Recommendation::where('user_id', $user->id)->get();
        
        $this->assertCount(1, $recommendations);
        $this->assertEquals($tcWithin->id, $recommendations->first()->training_center_id);
    }

    public function test_recommendation_stores_score_breakdown()
    {
        $user = $this->createUserWithProfileAndQuestionnaire(["bidang_diminati" => "Programming", "metode_pelatihan" => "Online", "tingkat_keahlian" => "Beginner"]);
        $tc = TrainingCenter::create([
            'nama' => 'TC Dalam Radius',
            'alamat' => 'Alamat',
            'telepon' => '000',
            'latitude' => -7.6500,
            'longitude' => 111.3200,
        ]);
        $this->createTraining(['training_center_id' => $tc->id, 'interest_category' => 'Programming', 'method' => 'Online', 'required_skill' => 'Beginner']);
        
        $this->engine->generateForUser($user->id);
        $rec = Recommendation::where('user_id', $user->id)->first();
        
        $this->assertNotNull($rec->score_breakdown);
        $breakdown = json_decode($rec->score_breakdown, true);
        $this->assertArrayHasKey('interest', $breakdown);
        $this->assertArrayHasKey('skill', $breakdown);
        $this->assertArrayHasKey('method', $breakdown);
        $this->assertArrayHasKey('distance', $breakdown);
    }

    public function test_hard_filter_eliminates_mismatched_method(): void
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'bidang_diminati' => 'IT',
            'metode_pelatihan' => 'Online',
            'tingkat_keahlian' => 'Beginner',
        ]);

        $trainingOnline = $this->createTraining([
            'judul' => 'Training Online',
            'method' => 'Online',
        ]);

        $tcOffline = TrainingCenter::create([
            'nama' => 'TC Offline',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        $trainingOffline = $this->createTraining([
            'judul' => 'Training Offline',
            'method' => 'Offline',
            'training_center_id' => $tcOffline->id,
        ]);

        $this->engine->generateForUser($user->id);

        $tcIds = Recommendation::where('user_id', $user->id)->pluck('training_center_id')->toArray();
        $this->assertContains($trainingOnline->training_center_id, $tcIds);
        $this->assertNotContains($trainingOffline->training_center_id, $tcIds);
    }

    public function test_hard_filter_allows_hybrid_method(): void
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'bidang_diminati' => 'IT',
            'metode_pelatihan' => 'Online',
            'tingkat_keahlian' => 'Beginner',
        ]);

        $trainingHybrid = $this->createTraining([
            'judul' => 'Training Hybrid',
            'method' => 'Hybrid',
        ]);

        $this->engine->generateForUser($user->id);

        $tcIds = Recommendation::where('user_id', $user->id)->pluck('training_center_id')->toArray();
        $this->assertContains($trainingHybrid->training_center_id, $tcIds);
    }

    // ==========================================
    // PHASE 2: WEIGHTED SCORE
    // ==========================================

    public function test_perfect_match_training_gets_highest_score(): void
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'bidang_diminati' => 'IT',
            'metode_pelatihan' => 'Online',
            'tingkat_keahlian' => 'Beginner',
        ]);

        // Perfect match
        $tcPerfect = TrainingCenter::create([
            'nama' => 'TC Perfect',
            'alamat' => 'A',
            'telepon' => '1',
            'latitude' => -7.6400,
            'longitude' => 111.3200,
        ]);

        $perfectMatch = $this->createTraining([
            'judul' => 'Perfect Match',
            'interest_category' => 'IT',
            'method' => 'Online',
            'required_skill' => 'Beginner',
            'popularity' => 100,
            'training_center_id' => $tcPerfect->id,
        ]);

        // Partial match
        $tcPartial = TrainingCenter::create([
            'nama' => 'TC Partial',
            'alamat' => 'Alamat',
            'telepon' => '000',
            // Default TC dummy coordinate
            'latitude' => -7.6500,
            'longitude' => 111.3300,
        ]);

        $partialMatch = $this->createTraining([
            'judul' => 'Partial Match',
            // Gunakan interest "IT" (Match), tetapi method "Offline" (Mismatch -> Hard Filter drop)
            // Jadi kita pakai method "Online", skill "Beginner", tapi bidang_diminati "Bisnis" agar parsial.
            // ATAU, karena ini test bobot, kita buat bidangnya "Design" dan pastikan dia LOLOS hard filter dulu.
            // Hard Filter pada engine mensyaratkan:
            // - is_active = true
            // - bidang_diminati match (JIKA SET) -> Nah! Disini hard filter mengecek bidang_diminati!
            // Jika user memilih 'IT', maka training dengan 'Design' PASTI DROPPED di Phase 1 Hard Filter.
            // Ini penyebab utama partialMatch tidak mendapat rekomendasi sama sekali.

            // Solusi: Kita buat agar user TIDAK mensyaratkan bidang (agar lolos Phase 1),
            // ATAU partialMatch tetap di bidang 'IT', tetapi popularitasnya kita set 0
            // agar skornya lebih rendah dari perfectMatch.
            'interest_category' => 'IT',
            'method' => 'Online',
            'required_skill' => 'Beginner',
            'popularity' => 10,
            'training_center_id' => $tcPartial->id,
        ]);

        $this->engine->generateForUser($user->id);

        $recommendations = Recommendation::where('user_id', $user->id)
            ->orderBy('rank', 'asc')
            ->get();

        $this->assertCount(2, $recommendations, 'Engine harus meromendasikan 2 Training Center');

        $this->assertEquals($perfectMatch->training_center_id, $recommendations->first()->training_center_id);

        $perfectScoreData = $recommendations->where('training_center_id', $perfectMatch->training_center_id)->first();
        $partialScoreData = $recommendations->where('training_center_id', $partialMatch->training_center_id)->first();

        $this->assertGreaterThan((float) $partialScoreData->score, (float) $perfectScoreData->score);
    }

    public function test_score_is_calculated_correctly_for_perfect_match(): void
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'bidang_diminati' => 'IT',
            'metode_pelatihan' => 'Online',
            'tingkat_keahlian' => 'Beginner',
        ]);

        $tc = TrainingCenter::create([
            'nama' => 'TC Perfect',
            'alamat' => 'A',
            'telepon' => '1',
            // Samakan koordinat dengan user (jarak 0km -> Distance Score 20)
            'latitude' => -7.6400,
            'longitude' => 111.3200,
        ]);

        $this->createTraining([
            'judul' => 'Full Score Training',
            'interest_category' => 'IT',     // +35
            'method' => 'Online',            // +15
            'required_skill' => 'Beginner',  // +20
            'popularity' => 100,             // +(100/100)*10 = 10
            'training_center_id' => $tc->id,
        ]);

        $this->engine->generateForUser($user->id);

        $rec = Recommendation::where('user_id', $user->id)->first();

        // Ensure recommendation was created
        $this->assertNotNull($rec, 'No recommendation generated');

        if ($rec) {
            // 35(Bidang) + 20(Skill) + 15(Metode) + 10(Popularitas) + 20(Distance 0km) = 100
            $this->assertEquals(100.00, (float) $rec->score);
        }
    }

    // ==========================================
    // PHASE 3: PERSIST RECOMMENDATION
    // ==========================================

    public function test_old_recommendations_are_replaced(): void
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'bidang_diminati' => 'IT',
            'metode_pelatihan' => 'Online',
            'tingkat_keahlian' => 'Beginner',
        ]);

        $this->createTraining([
            'interest_category' => 'IT',
            'method' => 'Online',
            'required_skill' => 'Beginner'
        ]);

        $this->engine->generateForUser($user->id);
        $countFirst = Recommendation::where('user_id', $user->id)->count();

        $this->engine->generateForUser($user->id);
        $countSecond = Recommendation::where('user_id', $user->id)->count();

        // Karena countFirst bisa jadi > 0 jika lolos hard filter.
        $this->assertGreaterThan(0, $countFirst);
        $this->assertEquals($countFirst, $countSecond);
    }

    public function test_persist_saves_top_5_only(): void
    {
        $user = $this->createUserWithProfileAndQuestionnaire([
            'bidang_diminati' => 'IT',
            'metode_pelatihan' => 'Online',
            'tingkat_keahlian' => 'Beginner',
        ]);

        // Buat 8 TC berbeda agar ada 8 rekomendasi
        for ($i = 1; $i <= 8; $i++) {
            $tc = TrainingCenter::create(['nama' => "TC $i", 'alamat' => 'A', 'telepon' => '1']);
            $this->createTraining([
                'judul' => "Training $i",
                'training_center_id' => $tc->id,
                'interest_category' => 'IT',
                'method' => 'Online',
                'required_skill' => 'Beginner'
            ]);
        }

        $this->engine->generateForUser($user->id);

        $count = Recommendation::where('user_id', $user->id)->count();
        // Rekomendasi di persist Top N = 5
        $this->assertEquals(5, $count);
    }

    public function test_skips_when_profile_missing(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        QuestionnaireResponse::create([
            'user_id' => $user->id,
            'answers' => json_encode(['bidang_diminati' => 'IT']),
        ]);

        $this->createTraining();
        $this->engine->generateForUser($user->id);

        $this->assertEquals(0, Recommendation::where('user_id', $user->id)->count());
    }

    public function test_skips_when_questionnaire_missing(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        Profile::create([
            'user_id' => $user->id,
            'age' => 22,
            'education' => 'S1',
            'district' => 'Kota A',
        ]);

        $this->createTraining();
        $this->engine->generateForUser($user->id);

        $this->assertEquals(0, Recommendation::where('user_id', $user->id)->count());
    }
}
