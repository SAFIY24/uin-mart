@extends('layouts.app')

@section('title', 'Pengaturan Profil - UIN Mart')

@push('styles')
<style>
    .profile-page{--pf-maroon:#8a0f1c;--pf-soft:#fbe9ea;--pf-line:#e5e7eb;--pf-muted:#6b7280;max-width:1000px;margin:0 auto;padding:24px 16px 48px}
    .profile-page h1{font-size:28px;margin:4px 0 20px}
    .profile-crumb{font-size:13px;color:var(--pf-muted)}
    .profile-crumb a{color:var(--pf-maroon);text-decoration:none}
    .profile-grid{display:grid;grid-template-columns:260px 1fr;gap:20px;align-items:start}
    .profile-card{background:#fff;border:1px solid var(--pf-line);border-radius:14px;padding:22px;margin-bottom:20px}
    .profile-card h2{font-size:18px;margin:0 0 16px}
    .profile-side{text-align:center}
    .profile-avatar{width:96px;height:96px;border-radius:50%;background:var(--pf-soft);color:var(--pf-maroon);font-size:32px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
    .profile-side h3{margin:0;font-size:17px}
    .profile-side small{color:var(--pf-muted)}
    .profile-meta{margin-top:16px;text-align:left;font-size:14px}
    .profile-meta div{display:flex;justify-content:space-between;padding:10px 0;border-top:1px dashed var(--pf-line)}
    .profile-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .profile-field{margin-bottom:14px}
    .profile-field label{display:block;font-size:13px;font-weight:700;margin-bottom:6px}
    .profile-field input{width:100%;padding:11px 12px;border:1px solid var(--pf-line);border-radius:10px;font-size:14px;box-sizing:border-box}
    .profile-field input:focus{outline:none;border-color:var(--pf-maroon)}
    .profile-field input[readonly]{background:#efefef;color:#555}
    .profile-field input.is-invalid{border-color:#b42318}
    .profile-hint{font-size:12px;color:var(--pf-muted);margin-top:4px}
    .profile-error{font-size:12px;color:#b42318;margin-top:4px}
    .profile-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:6px}
    .profile-btn{padding:10px 18px;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;border:1px solid var(--pf-maroon);text-decoration:none;display:inline-block}
    .profile-btn-primary{background:var(--pf-maroon);color:#fff}
    .profile-btn-outline{background:#fff;color:var(--pf-maroon)}
    @media(max-width:800px){.profile-grid,.profile-row{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="profile-page">

    <div class="profile-crumb">
        <a href="{{ route('home') }}">UIN Mart</a> / Pengaturan Profil
    </div>
    <h1>Pengaturan Profil</h1>

    <div class="profile-grid">

        {{-- KARTU RINGKASAN (kiri) --}}
        <aside class="profile-card profile-side">
            <div class="profile-avatar">
                {{ strtoupper(mb_substr($user->name, 0, 2)) }}
            </div>
            <h3>{{ $user->name }}</h3>
            @if($user->nim)
                <small>NIM {{ $user->nim }}</small>
            @endif

            <div class="profile-meta">
                <div>
                    <span>Akun</span>
                    <strong>{{ $user->role === 'admin' ? 'Admin' : 'Mahasiswa' }}</strong>
                </div>
                <div>
                    <span>Bergabung</span>
                    <strong>{{ $user->created_at?->format('M Y') }}</strong>
                </div>
            </div>
        </aside>

        <div>

            {{-- FORM DATA PRIBADI --}}
            <form class="profile-card"
                  method="POST"
                  action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <h2>Data Pribadi</h2>

                <div class="profile-field">
                    <label for="name">Nama Lengkap</label>
                    <input id="name" type="text" name="name"
                           value="{{ old('name', $user->name) }}"
                           class="@error('name', 'profile') is-invalid @enderror">
                    @error('name', 'profile')
                        <div class="profile-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="profile-row">
                    <div class="profile-field">
                        <label for="nim">NIM</label>
                        <input id="nim" type="text" value="{{ $user->nim }}" readonly>
                        <div class="profile-hint">NIM tidak dapat diubah.</div>
                    </div>

                    <div class="profile-field">
                        <label for="whatsapp">No. WhatsApp Aktif</label>
                        <input id="whatsapp" type="text" name="whatsapp"
                               placeholder="081234567890"
                               value="{{ old('whatsapp', $user->whatsapp) }}"
                               class="@error('whatsapp', 'profile') is-invalid @enderror">
                        @error('whatsapp', 'profile')
                            <div class="profile-error">{{ $message }}</div>
                        @else
                            <div class="profile-hint">Dipakai untuk koordinasi COD.</div>
                        @enderror
                    </div>
                </div>

                <div class="profile-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           class="@error('email', 'profile') is-invalid @enderror">
                    @error('email', 'profile')
                        <div class="profile-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="profile-actions">
                    <a class="profile-btn profile-btn-outline"
                       href="{{ route('profile.edit') }}">Batal</a>
                    <button class="profile-btn profile-btn-primary" type="submit">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            {{-- FORM UBAH KATA SANDI --}}
            <form class="profile-card"
                  method="POST"
                  action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <h2>Ubah Kata Sandi</h2>

                <div class="profile-row">
                    <div class="profile-field">
                        <label for="current_password">Kata Sandi Lama</label>
                        <input id="current_password" type="password" name="current_password"
                               placeholder="Masukkan kata sandi lama"
                               class="@error('current_password', 'password') is-invalid @enderror">
                        @error('current_password', 'password')
                            <div class="profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label for="password">Kata Sandi Baru</label>
                        <input id="password" type="password" name="password"
                               placeholder="Minimal 8 karakter"
                               class="@error('password', 'password') is-invalid @enderror">
                        @error('password', 'password')
                            <div class="profile-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="profile-actions">
                    <button class="profile-btn profile-btn-outline" type="submit">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection