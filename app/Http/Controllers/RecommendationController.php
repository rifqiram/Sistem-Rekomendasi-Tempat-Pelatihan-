<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * Display a listing of the recommendations.
     * Mengambil dari tabel rekomendasi hasil persisten dari Engine.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Ambil rekomendasi beserta relasi Training Center dan pelatihannya
        $recommendations = Recommendation::with(['trainingCenter.pelatihans'])
            ->where('user_id', $user->id)
            ->orderBy('rank', 'asc')
            ->get();

        // 2. Format output sesuai kebutuhan
        $formatted = $recommendations->map(function ($rec) {
            $tc = $rec->trainingCenter;
            return [
                'training_center' => $tc,
                'score' => (float) $rec->score,
                'distance' => $rec->distance !== null ? (float) $rec->distance : null,
                'score_breakdown' => $rec->score_breakdown ? json_decode($rec->score_breakdown, true) : null,
                'rank' => $rec->rank,
                'jumlah_pelatihan' => $tc ? $tc->pelatihans->count() : 0,
                'daftar_pelatihan' => $tc ? $tc->pelatihans : []
            ];
        });

        return $this->successResponse($formatted, 'Rekomendasi berhasil diambil');
    }
}
