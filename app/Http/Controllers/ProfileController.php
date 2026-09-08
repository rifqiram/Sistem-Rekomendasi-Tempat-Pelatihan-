<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Models\LogActivity;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Tampilkan profile user yang sedang login.
     */
    public function show(Request $request)
    {
        $profile = Profile::where('user_id', $request->user()->id)->first();

        if (!$profile) {
            return $this->errorResponse('Profile belum lengkap', 404);
        }

        return $this->successResponse($profile, 'Profile berhasil diambil');
    }

    /**
     * Simpan atau update profile user.
     */
    public function store(StoreProfileRequest $request, \App\Services\RecommendationEngine $engine)
    {
        $data = $request->validated();

        $profile = Profile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        // Setelah profil (lokasi peta) diperbarui, jalankan ulang Recommendation Engine
        // agar skor jarak (Distance Score) terkalibrasi ulang.
        $engine->generateForUser($request->user()->id);

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'update_profile',
        ]);

        return $this->successResponse($profile, 'Profile berhasil disimpan');
    }
}
