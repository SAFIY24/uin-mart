@extends('layouts.app')

@section('content')
<div style="max-width:700px;margin:40px auto;padding:30px;background:white;border:1px solid #eee;border-radius:14px;">

    <h1 style="color:#800e19;">
        Daftar Menjadi Penjual
    </h1>

    <p>
        Mulai berjualan di UIN Mart.
        Pengajuan Anda akan diperiksa oleh Admin.
    </p>

    @if(session('success'))
        <p style="color:green;">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <ul style="color:red;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if($application && $application->status === 'pending')

        <h3>Menunggu Persetujuan Admin</h3>
        <p>Nama Toko: {{ $application->shop_name }}</p>

    @elseif($application && $application->status === 'approved')

        <h3 style="color:green;">Akun Penjual Disetujui!</h3>
        <p>Nama Toko: {{ $application->shop_name }}</p>

    @elseif($application && $application->status === 'suspended')

        <h3 style="color:red;">Akun Penjual Ditangguhkan</h3>
        <p>{{ $application->admin_note }}</p>

    @else

        @if($application && $application->status === 'rejected')
            <p style="color:red;">
                Pengajuan ditolak:
                {{ $application->admin_note }}
            </p>
        @endif

        <form action="{{ route('seller.register.store') }}" method="POST">
            @csrf

            <p>
                <label>Nama Toko</label>
                <input type="text" name="shop_name"
                       value="{{ old('shop_name', $application?->shop_name) }}"
                       maxlength="150" required
                       style="display:block;width:100%;padding:12px;">
            </p>

            <p>
                <label>Nomor Telepon</label>
                <input type="tel" name="phone"
                       value="{{ old('phone', $application?->phone) }}"
                       maxlength="30" required
                       style="display:block;width:100%;padding:12px;">
            </p>

            <p>
                <label>Deskripsi Toko</label>
                <textarea name="description" maxlength="2000"
                          style="display:block;width:100%;padding:12px;">{{ old('description', $application?->description) }}</textarea>
            </p>

            <button type="submit"
                    style="background:#800e19;color:white;padding:13px 20px;border:0;border-radius:8px;">
                Ajukan Pendaftaran
            </button>
        </form>

    @endif

    <p>
        <a href="{{ route('home') }}">← Kembali ke Beranda</a>
    </p>

</div>
@endsection