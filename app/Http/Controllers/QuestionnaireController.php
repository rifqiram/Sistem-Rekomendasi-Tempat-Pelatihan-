<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionnaireRequest;
use App\Models\LogActivity;
use App\Models\QuestionnaireResponse;
use App\Services\RecommendationEngine;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    /**
     * Tampilkan kuesioner user.
     */
    public function show(Request $request)
    {
        $response = QuestionnaireResponse::where('user_id', $request->user()->id)->first();

        if (!$response) {
            return $this->errorResponse('Kuesioner belum diisi', 404);
        }

        return $this->successResponse(json_decode($response->answers, true), 'Data kuesioner berhasil diambil');
    }

    /**
     * Simpan kuesioner dan jalankan Recommendation Engine.
     */
    public function store(StoreQuestionnaireRequest $request, RecommendationEngine $engine)
    {
        $data = $request->validated();

        $response = QuestionnaireResponse::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['answers' => json_encode($data['answers'])]
        );

        // Setelah kuesioner disubmit/diupdate, trigger Recommendation Engine
        $engine->generateForUser($request->user()->id);

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'update_questionnaire',
        ]);

        return $this->successResponse(
            json_decode($response->answers, true),
            'Kuesioner berhasil disimpan dan rekomendasi diperbarui'
        );
    }
}
