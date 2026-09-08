<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingCenterRequest;
use App\Http\Requests\UpdateTrainingCenterRequest;
use App\Models\LogActivity;
use App\Models\TrainingCenter;
use Illuminate\Http\Request;

class TrainingCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trainingCenters = TrainingCenter::all();
        return $this->successResponse($trainingCenters, 'Data Training Center berhasil diambil.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainingCenterRequest $request)
    {
        // Pengecekan authorization (role admin) sudah ditangani oleh FormRequest (StoreTrainingCenterRequest->authorize())
        $trainingCenter = TrainingCenter::create($request->validated());
        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'create_tc',
            'training_center_id' => $trainingCenter->id,
        ]);
        return $this->successResponse($trainingCenter, 'Training Center berhasil ditambahkan.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $trainingCenter = TrainingCenter::findOrFail($id);

        return $this->successResponse($trainingCenter, 'Data Training Center berhasil diambil.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainingCenterRequest $request, $id)
    {
        // Pengecekan authorization sudah ditangani FormRequest
        $trainingCenter = TrainingCenter::findOrFail($id);

        $trainingCenter->update($request->validated());

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'update_tc',
            'training_center_id' => $trainingCenter->id,
        ]);

        return $this->successResponse($trainingCenter, 'Training Center berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $trainingCenter = TrainingCenter::findOrFail($id);

        $trainingCenter->delete();

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'delete_tc',
            'details' => 'Menghapus TC ID: ' . $id,
        ]);

        return $this->successResponse(null, 'Training Center berhasil dihapus.');
    }
}