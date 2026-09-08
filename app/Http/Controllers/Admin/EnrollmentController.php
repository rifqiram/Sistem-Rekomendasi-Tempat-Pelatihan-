<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LogActivity;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $enrollments = Enrollment::with(['user.profile', 'user.questionnaireResponse', 'trainingCenter', 'pelatihan'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->successResponse($enrollments, 'Data pendaftar berhasil diambil.');
    }

    /**
     * Update status pendaftaran
     */
    public function updateStatus(Request $request, $id)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $enrollment = Enrollment::findOrFail($id);
        $enrollment->status = $data['status'];
        $enrollment->save();

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'update_enrollment',
            'training_center_id' => $enrollment->training_center_id,
            'pelatihan_id' => $enrollment->pelatihan_id,
            'details' => 'Mengubah status pendaftaran ID: ' . $id . ' menjadi ' . $data['status'],
        ]);

        return $this->successResponse($enrollment, 'Status pendaftaran berhasil diperbarui.');
    }
}