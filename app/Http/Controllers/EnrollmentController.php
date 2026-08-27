<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Pelatihan;
use App\Models\LogActivity;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Menampilkan riwayat pendaftaran user yang sedang login.
     */
    public function index(Request $request)
    {
        $enrollments = Enrollment::with(['trainingCenter', 'pelatihan'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($enrollments, 'Riwayat pendaftaran berhasil diambil.');
    }

    /**
     * Mendaftar ke sebuah pelatihan.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'pelatihan_id' => 'required|exists:tabel_pelatihan,id',
        ]);

        $pelatihan = Pelatihan::findOrFail($data['pelatihan_id']);

        $exists = Enrollment::where('user_id', $request->user()->id)
            ->where('pelatihan_id', $pelatihan->id)
            ->exists();

        if ($exists) {
            return $this->errorResponse('Anda sudah terdaftar pada pelatihan ini.', 409);
        }

        $enrollment = Enrollment::create([
            'user_id' => $request->user()->id,
            'training_center_id' => $pelatihan->training_center_id,
            'pelatihan_id' => $pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'pending',
        ]);

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'enroll',
            'training_center_id' => $pelatihan->training_center_id,
            'pelatihan_id' => $pelatihan->id,
        ]);

        return $this->successResponse($enrollment, 'Pendaftaran berhasil.', 201);
    }
}