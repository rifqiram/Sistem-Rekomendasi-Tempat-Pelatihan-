<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Pelatihan;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $totalUsers = User::where('role', 'user')->count();
        $totalTC = TrainingCenter::count();
        $totalPelatihan = Pelatihan::count();
        $totalEnrollment = Enrollment::count();

                // Top 5 Pelatihan Terpopuler (Berdasarkan jumlah pendaftar disetujui)
        $popularTrainings = Pelatihan::withCount(['enrollments as approved_enrollments_count' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->orderByDesc('approved_enrollments_count')
            ->limit(5)
            ->get(['id', 'judul']);

        // 5 Pendaftar Terbaru
        $recentEnrollments = Enrollment::with(['user', 'pelatihan', 'trainingCenter'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse([
            'metrics' => [
                'users' => $totalUsers,
                'training_centers' => $totalTC,
                'pelatihan' => $totalPelatihan,
                'enrollments' => $totalEnrollment,
            ],
            'recent_enrollments' => $recentEnrollments,
            'popular_trainings' => $popularTrainings,
        ], 'Statistik Admin berhasil diambil');
    }
}