<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->errorResponse('Email atau password salah', 401);
        }

        if (! $user->is_active) {
            return $this->errorResponse('Akun Anda dinonaktifkan oleh administrator.', 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        LogActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'login',
        ]);

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login berhasil');
    }

    public function me(Request $request)
    {
        return $this->successResponse([
            'user' => new UserResource($request->user()),
        ], 'Data user berhasil diambil');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $requestedRole = $data['role'] ?? 'user';
        $role = in_array($requestedRole, ['user', 'pencari_kerja'], true) ? 'user' : 'user';

        if ($request->user()?->role === 'admin' && $requestedRole === 'admin') {
            $role = 'admin';
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
            'api_token' => \Illuminate\Support\Str::random(60),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Register berhasil', 201);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            LogActivity::create([
                'user_id' => $request->user()->id,
                'activity_type' => 'logout',
            ]);
            $request->user()->currentAccessToken()?->delete();
        }

        return $this->successResponse(null, 'Logout berhasil');
    }
}
