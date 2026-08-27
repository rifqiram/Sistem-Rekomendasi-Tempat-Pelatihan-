<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tabel_users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,user,pencari_kerja',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Alamat email ini sudah digunakan. Silakan gunakan email lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
