
@extends('layouts.app')

@section('title', 'Daftar · UIN Mart')

@section('content')

<section class="um-login-page um-register-page">

    <div class="um-login-container">

        <!-- CỘT TRÁI: THƯƠNG HIỆU UIN MART -->
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
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <circle cx="9" cy="8" r="4"/>
                        <path d="M2 21c0-5 3-8 7-8s7 3 7 8"/>
                        <path d="M19 7v8M15 11h8"/>
                    </svg>
                </div>

                <h1>
                    Bergabung dengan<br>
                    UIN Mart!
                </h1>

                <p>
                    Daftarkan akun mahasiswa Anda dan nikmati
                    pengalaman belanja yang mudah, hemat,
                    dan nyaman di UIN Mart.
                </p>

            </div>

            <div class="um-login-brand-bottom">
                <span class="um-login-brand-line"></span>

                <p>
                    Belanja Praktis.<br>
                    Harga Terjangkau.
                </p>
            </div>

            <div class="um-login-decoration um-login-decoration-one"></div>
            <div class="um-login-decoration um-login-decoration-two"></div>

        </div>

        <!-- CỘT PHẢI: FORM ĐĂNG KÝ -->
        <div class="um-login-form-panel">

            <div class="um-login-form-inner">

                <span class="um-login-eyebrow">
                    SELAMAT DATANG DI UIN MART
                </span>

                <h2>Buat Akun Mahasiswa</h2>

                <p class="um-login-description">
                    Lengkapi data berikut untuk membuat akun UIN Mart.
                </p>

                <form
                    method="POST"
                    action="{{ route('register.submit') }}"
                    class="um-login-form"
                >
                    @csrf

                    <!-- 1. NAMA PENGGUNA -->
                    <div class="um-login-field">

                        <label for="reg-username">
                            Nama Pengguna
                        </label>

                        <input
                            id="reg-username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Masukkan nama pengguna"
                            autocomplete="username"
                            maxlength="100"
                            @error('username') aria-invalid="true" @enderror
                            required
                            autofocus
                        >

                        @error('username')
                            <small class="um-register-field-error">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <!-- 2. NIM -->
                    <div class="um-login-field">

                        <label for="reg-nim">
                            NIM (Nomor Induk Mahasiswa)
                        </label>

                        <input
                            id="reg-nim"
                            type="text"
                            name="nim"
                            value="{{ old('nim') }}"
                            placeholder="Masukkan NIM mahasiswa"
                            inputmode="numeric"
                            pattern="[0-9]{6,20}"
                            minlength="6"
                            maxlength="20"
                            autocomplete="off"
                            @error('nim') aria-invalid="true" @enderror
                            required
                        >

                        @error('nim')
                            <small class="um-register-field-error">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <!-- 3. EMAIL -->
                    <div class="um-login-field">

                        <label for="reg-email">
                            Email
                        </label>

                        <input
                            id="reg-email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Masukkan alamat email"
                            autocomplete="email"
                            maxlength="150"
                            @error('email') aria-invalid="true" @enderror
                            required
                        >

                        @error('email')
                            <small class="um-register-field-error">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <!-- 4. PASSWORD -->
                    <div class="um-login-field">

                        <label for="reg-password">
                            Kata Sandi
                        </label>

                        <div class="um-login-password-wrap">

                            <input
                                id="reg-password"
                                type="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                minlength="8"
                                @error('password') aria-invalid="true" @enderror
                                required
                            >

                            <button
                                type="button"
                                class="um-login-eye"
                                data-toggle-password="reg-password"
                                aria-label="Tampilkan kata sandi"
                                aria-controls="reg-password"
                                aria-pressed="false"
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

                        @error('password')
                            <small class="um-register-field-error">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <!-- 5. KONFIRMASI PASSWORD -->
                    <div class="um-login-field">

                        <label for="reg-confirm">
                            Konfirmasi Kata Sandi
                        </label>

                        <div class="um-login-password-wrap">

                            <input
                                id="reg-confirm"
                                type="password"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                            <button
                                type="button"
                                class="um-login-eye"
                                data-toggle-password="reg-confirm"
                                aria-label="Tampilkan konfirmasi kata sandi"
                                aria-controls="reg-confirm"
                                aria-pressed="false"
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

                    <!-- NÚT ĐĂNG KÝ -->
                    <button
                        type="submit"
                        class="um-login-submit"
                    >
                        Daftar Sekarang
                        <span aria-hidden="true">→</span>
                    </button>

                </form>

                <!-- CHUYỂN VỀ LOGIN -->
                <p class="um-login-register">
                    Sudah punya akun?
                    <a href="{{ route('login') }}">
                        Masuk
                    </a>
                </p>

            </div>

        </div>

    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-toggle-password]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const input = document.getElementById(
                    button.dataset.togglePassword
                );

                if (!input) return;

                const visible = input.type === 'password';

                input.type = visible ? 'text' : 'password';

                button.setAttribute(
                    'aria-pressed',
                    String(visible)
                );

                button.setAttribute(
                    'aria-label',
                    visible
                        ? 'Sembunyikan kata sandi'
                        : 'Tampilkan kata sandi'
                );
            });
        });
});
</script>

@endsection
