<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Menampilkan halaman Pengaturan Profil.
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    // Menyimpan perubahan data pribadi.
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Rapikan nomor WhatsApp: hapus spasi dan tanda strip.
        if ($request->filled('whatsapp')) {
            $request->merge([
                'whatsapp' => preg_replace('/[\s\-]+/', '', $request->input('whatsapp')),
            ]);
        }

        $data = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'email' => [
                'required',
                'email',
                'max:150',
                // Email boleh sama dengan email miliknya sendiri.
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        $data['whatsapp'] = $data['whatsapp'] ?? null;

        // NIM tidak ikut diupdate, jadi tidak bisa diubah.
        $user->update($data);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Perubahan profil berhasil disimpan.');
    }

    // Mengganti kata sandi.
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'different:current_password'],
        ], [
            'current_password.required' => 'Kata sandi lama wajib diisi.',
            'current_password.current_password' => 'Kata sandi lama salah.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
        ]);

        // Password otomatis di-hash karena ada cast 'hashed' di User.php.
        $request->user()->update([
            'password' => $request->input('password'),
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }
}