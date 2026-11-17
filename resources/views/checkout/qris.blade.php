@extends('layouts.app')

@section('content')
<section class="panel center">

    <h1>Pembayaran QRIS</h1>

    <p>
        Nomor pesanan:
        <strong>{{ $order->order_number }}</strong>
    </p>

    <p>
        Total pembayaran:
        <strong>
            Rp {{ number_format($paymentTransaction->gross_amount, 0, ',', '.') }}
        </strong>
    </p>

    <h3>Scan QR Code untuk Membayar</h3>

    @if ($paymentTransaction->qr_url)
    <div style="margin: 20px auto;">
        <img
            src="{{ $paymentTransaction->qr_url }}"
            alt="QR Code Pembayaran"
            style="max-width: 280px; width: 100%; height: auto;">
    </div>
    @else
    <p>QR Code belum tersedia.</p>
    @endif

    <p>
        Status pembayaran:
        <strong>{{ $paymentTransaction->status }}</strong>
    </p>

    <p>
        Silakan lakukan pembayaran melalui aplikasi
        yang mendukung QRIS.
    </p>

    <a class="btn" href="{{ route('home') }}">
        Kembali ke Beranda
    </a>

</section>
@endsection
