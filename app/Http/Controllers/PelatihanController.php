<?php

namespace App\Http\Controllers;

use App\Http\Resources\PelatihanResource;
use App\Models\LogActivity;
use App\Models\Pelatihan;
use App\Models\TrainingCenter;
use Illuminate\Http\Request;

class PelatihanController extends Controller
{
    public function index()
    {
        return $this->successResponse(
            PelatihanResource::collection(Pelatihan::with(['trainingCenter'])->withCount(['enrollments as approved_enrollments_count' => fn($q) => $q->where('status', 'approved')])->get()),
            'Data pelatihan berhasil diambil'
        );
    }

    public function store(Request $request)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        if (TrainingCenter::count() === 0) {
            return $this->errorResponse('Silakan tambahkan Tempat Pelatihan terlebih dahulu.', 400);
        }

        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'interest_category' => 'nullable|string',
            'method' => 'nullable|string',
            'required_skill' => 'nullable|string',
            'kategori' => 'nullable|string',
            'level' => 'nullable|string',
            'durasi' => 'nullable|string',
            'sertifikat' => 'nullable|string',
            'training_center_id' => 'required|exists:training_centers,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'
        ]);

        $data['is_active'] = $data['is_active'] ?? true;
        $pelatihan = Pelatihan::create($data);

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'create_pelatihan',
            'training_center_id' => $pelatihan->training_center_id,
            'pelatihan_id' => $pelatihan->id,
        ]);

        return $this->successResponse(new PelatihanResource($pelatihan->load(['trainingCenter'])), 'Pelatihan berhasil dibuat', 201);
    }

    public function show(Pelatihan $pelatihan)
    {
        return $this->successResponse(
            new PelatihanResource($pelatihan->load(['trainingCenter'])->loadCount(['enrollments as approved_enrollments_count' => fn($q) => $q->where('status', 'approved')])),
            'Detail pelatihan berhasil diambil'
        );
    }

    public function update(Request $request, Pelatihan $pelatihan)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $data = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'interest_category' => 'nullable|string',
            'method' => 'nullable|string',
            'required_skill' => 'nullable|string',
            'kategori' => 'nullable|string',
            'level' => 'nullable|string',
            'durasi' => 'nullable|string',
            'sertifikat' => 'nullable|string',
            'training_center_id' => 'sometimes|required|exists:training_centers,id',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_selesai' => 'sometimes|required|date|after_or_equal:tanggal_mulai',
            'status' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'
        ]);

        $pelatihan->update($data);

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'update_pelatihan',
            'training_center_id' => $pelatihan->training_center_id,
            'pelatihan_id' => $pelatihan->id,
        ]);

        return $this->successResponse(new PelatihanResource($pelatihan->load(['trainingCenter'])), 'Pelatihan berhasil diperbarui');
    }

        public function trending(Request $request)
    {
        $popularTrainings = Pelatihan::with(['trainingCenter:id,nama'])
            ->withCount(['enrollments as approved_count' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->where('is_active', true)
            ->having('approved_count', '>', 0)
            ->orderByDesc('approved_count')
            ->limit(5)
            ->get(['id', 'judul', 'interest_category', 'training_center_id', 'method', 'required_skill']);

        return $this->successResponse($popularTrainings, 'Data trending pelatihan berhasil diambil');
    }
    public function destroy(Request $request, Pelatihan $pelatihan)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        // Use modern enrollments table check
        if ($pelatihan->enrollments()->exists()) {
            return $this->errorResponse('Masih ada peserta terdaftar', 400);
        }

        $pelatihan->delete();

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'delete_pelatihan',
            'details' => 'Menghapus Pelatihan ID: ' . $pelatihan->id,
        ]);

        return $this->successResponse(null, 'Pelatihan berhasil dihapus');
    }
}