<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim($data['login']);

        // Login menggunakan email atau NIM.
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'nim';

        $credentials = [
            $field => $identity,
            'password' => $data['password'],
        ];

        // Validasi akun dan password.
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => 'Email/NIM atau kata sandi salah.',
            ]);
        }

        // Regenerasi session setelah berhasil login.
        $request->session()->regenerate();

        // Admin langsung masuk Dashboard Admin V3.
        if ($request->user()->role === 'admin') {
            return redirect()->route('admin.index');
        }

        // Customer dan Seller masuk halaman toko.
        return redirect()->route('home');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username'),
            ],
            'nim' => [
                'required',
                'string',
                'regex:/^[0-9]{6,20}$/',
                Rule::unique('users', 'nim'),
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'NIM harus terdiri dari 6 sampai 20 angka.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'username.unique' => 'Nama pengguna sudah digunakan.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $data['username'],
            'username' => $data['username'],
            'nim' => $data['nim'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', 'Akun berhasil dibuat!');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
