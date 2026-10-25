
@extends('layouts.app')

@section('title', 'Masuk - UIN Mart')

@section('content')

<section class="um-login-page">

    <div class="um-login-container">

        <!-- CỘT TRÁI: GIỚI THIỆU UIN MART -->
        <div class="um-login-brand">

            <div class="um-login-brand-top">
                <img
                    src="{{ asset('assets/uin-logo.png') }}"
                    alt="Logo UIN Imam Bonjol Padang"
                >

                <div>
                    <h2>UIN <span>MART</span></h2>
                    <p>UIN IMAM BONJOL PADANG</p>
                </div>
            </div>

            <div class="um-login-welcome">

                <div class="um-login-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                        aria-hidden="true"
                    >
                        <path d="M6 7h15l-2 9H8L5 3H2"/>
                        <circle cx="9" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                    </svg>
                </div>

                <h1>
                    Selamat Datang<br>
                    Kembali!
                </h1>

                <p>
                    Belanja mudah, hemat, dan nyaman
                    untuk seluruh mahasiswa UIN
                    Imam Bonjol Padang.
                </p>

            </div>

            <div class="um-login-brand-bottom">
                <span class="um-login-brand-line"></span>

                <p>
                    Belanja Cerdas,<br>
                    Untuk Mahasiswa UIN.
                </p>
            </div>

            <div class="um-login-decoration um-login-decoration-one"></div>
            <div class="um-login-decoration um-login-decoration-two"></div>

        </div>

        <!-- CỘT PHẢI: FORM LOGIN -->
        <div class="um-login-form-panel">

            <div class="um-login-form-inner">

                <span class="um-login-eyebrow">
                    SELAMAT DATANG DI UIN MART
                </span>

                <h2>Masuk ke Akun</h2>

                <p class="um-login-description">
                    Masuk menggunakan email atau NIM
                    untuk melanjutkan pengalaman belanja Anda.
                </p>

                <!-- THÔNG BÁO LỖI ĐĂNG NHẬP -->
                @if ($errors->any())
                    <div class="um-login-error" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('login.submit') }}"
                    class="um-login-form"
                >
                    @csrf

                    <!-- EMAIL HOẶC NIM -->
                    <div class="um-login-field">

                        <label for="login-identity">
                            Email atau NIM
                        </label>

                        <input
                            id="login-identity"
                            type="text"
                            name="login"
                            value="{{ old('login') }}"
                            placeholder="Masukkan email atau NIM"
                            autocomplete="username"
                            maxlength="255"
                            required
                            autofocus
                        >

                    </div>

                    <!-- PASSWORD -->
                    <div class="um-login-field">

                        <label for="login-password">
                            Kata Sandi
                        </label>

                        <div class="um-login-password-wrap">

                            <input
                                id="login-password"
                                type="password"
                                name="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                required
                            >

                            <!-- HIỆN / ẨN MẬT KHẨU -->
                            <button
                                type="button"
                                class="um-login-eye"
                                id="umTogglePassword"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                                aria-controls="login-password"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>

                        </div>

                    </div>

                    <!-- GHI NHỚ ĐĂNG NHẬP -->
                    <label class="um-login-remember">

                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                        >

                        <span>Ingat saya</span>

                    </label>

                    <!-- NÚT ĐĂNG NHẬP -->
                    <button
                        type="submit"
                        class="um-login-submit"
                    >
                        Masuk
                        <span aria-hidden="true">→</span>
                    </button>

                </form>

                <!-- LIÊN KẾT ĐĂNG KÝ -->
                <p class="um-login-register">
                    Belum punya akun?
                    <a href="{{ route('register') }}">
                        Daftar Sekarang
                    </a>
                </p>

                <!-- BẢO MẬT -->
                <div class="um-login-security">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect x="5" y="10" width="14" height="11" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>

                    <span>
                        Masuk dengan aman ke UIN Mart
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('login-password');
    const button = document.getElementById('umTogglePassword');

    if (!input || !button) {
        return;
    }

    button.addEventListener('click', function () {

        const showPassword = input.type === 'password';

        input.type = showPassword ? 'text' : 'password';

        button.setAttribute(
            'aria-pressed',
            String(showPassword)
        );

        button.setAttribute(
            'aria-label',
            showPassword
                ? 'Sembunyikan kata sandi'
                : 'Tampilkan kata sandi'
        );

    });

});
</script>

@endsection
