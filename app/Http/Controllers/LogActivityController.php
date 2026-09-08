<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogActivityRequest;
use App\Models\LogActivity;

class LogActivityController extends Controller
{
    /**
     * Catat aktivitas pengguna.
     */
    public function store(StoreLogActivityRequest $request)
    {
        $data = $request->validated();

        $log = LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => $data['activity_type'],
            'training_center_id' => $data['training_center_id'] ?? null,
            'pelatihan_id' => $data['pelatihan_id'] ?? null,
            'details' => $data['details'] ?? null,
        ]);

        return $this->successResponse($log, 'Aktivitas berhasil dicatat');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $logs = LogActivity::with(['user', 'trainingCenter', 'pelatihan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->successResponse($logs, 'Log aktivitas berhasil diambil');
    }

    public function destroy(\Illuminate\Http\Request $request, $id)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $log = LogActivity::findOrFail($id);
        $log->delete();

        return $this->successResponse(null, 'Log aktivitas berhasil dihapus');
    }
}