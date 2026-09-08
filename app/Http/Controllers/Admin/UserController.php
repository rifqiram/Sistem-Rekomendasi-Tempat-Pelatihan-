<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $users = User::where('role', 'user')->paginate(15);

        return $this->successResponse($users, 'Data user berhasil diambil.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $user = User::with(['profile', 'recommendations.trainingCenter.pelatihans'])
            ->where('role', 'user')
            ->findOrFail($id);

        return $this->successResponse($user, 'Detail user berhasil diambil.');
    }

    /**
     * Update status aktif user.
     */
    public function updateStatus(Request $request, $id)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $user = User::where('role', 'user')->findOrFail($id);

        $data = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $user->update(['is_active' => $data['is_active']]);

        LogActivity::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'update_user_status',
            'details' => 'Mengubah status user ID: ' . $id . ' menjadi ' . ($data['is_active'] ? 'Aktif' : 'Non-Aktif'),
        ]);

        return $this->successResponse($user, 'Status user berhasil diperbarui.');
    }
}
