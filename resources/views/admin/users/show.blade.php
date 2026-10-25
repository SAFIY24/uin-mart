
@extends('layouts.admin')

@section('title', 'Detail Akun · UIN Mart')

@section('admin_content')

<style>
    .ud-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    }

    .ud-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 20px;
    margin-bottom: 22px;
    }
    .ud-header {
        margin-bottom: 24px;
    }

    .ud-header h1 {
        margin: 8px 0;
        color: #800e19;
        font-size: 27px;
    }

    .ud-header p {
        color: #777;
        font-size: 13px;
    }

    .ud-back {
        display: inline-block;
        margin-bottom: 15px;
        color: #800e19;
        text-decoration: none;
        font-weight: 700;
    }

    .ud-card {
        background: white;
        border: 1px solid #e9e1e3;
        border-radius: 13px;
        padding: 24px;
    }

    .ud-card h2 {
        margin: 0 0 20px;
        color: #800e19;
        font-size: 19px;
    }

    .ud-info {
    display: grid;
    grid-template-columns: minmax(130px, 35%) minmax(0, 1fr);
    align-items: start;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0e9eb;
    }

    .ud-info:last-child {
        border-bottom: none;
    }

    .ud-info span {
    display: block;
    font-size: 13px;
    color: #888;
    margin: 0;
    }

    .ud-info strong {
    display: block;
    font-size: 14px;
    color: #29252a;
    overflow-wrap: anywhere;
    }

    .ud-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .ud-stat {
        background: white;
        border: 1px solid #e9e1e3;
        border-radius: 13px;
        padding: 24px;
    }

    .ud-stat span {
        color: #777;
        font-size: 13px;
    }

    .ud-stat strong {
        display: block;
        color: #800e19;
        font-size: 29px;
        margin-top: 10px;
    }

    @media(max-width: 700px) {
        .ud-grid,
        .ud-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ud-page">

    <a href="{{ route('admin.users.index') }}" class="ud-back">
        ← Kembali ke Mahasiswa
    </a>

    <div class="ud-header">
        <h1>Detail Akun Pengguna</h1>
        <p>Informasi akun yang terdaftar di UIN Mart.</p>
    </div>

    <div class="ud-grid">

        <!-- INFORMASI AKUN -->
        <div class="ud-card">
            <h2>Informasi Akun</h2>

            <div class="ud-info">
                <span>ID Pengguna</span>
                <strong>#{{ $user->id }}</strong>
            </div>

            <div class="ud-info">
                <span>Nama Lengkap</span>
                <strong>{{ $user->name }}</strong>
            </div>

            <div class="ud-info">
                <span>Username</span>
                <strong>{{ $user->username }}</strong>
            </div>

            <div class="ud-info">
                <span>Email</span>
                <strong>{{ $user->email }}</strong>
            </div>

            <div class="ud-info">
                <span>NIM</span>
                <strong>{{ $user->nim ?? '-' }}</strong>
            </div>

            <div class="ud-info">
                <span>Peran</span>
                <strong>{{ ucfirst($user->role) }}</strong>
            </div>

            <div class="ud-info">
                <span>Tanggal Registrasi</span>
                <strong>
                    {{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}
                </strong>
            </div>
        </div>

        <!-- INFORMASI SELLER -->
        <div class="ud-card">
            <h2>Informasi Penjual</h2>

            @if($sellerApplication)

                <div class="ud-info">
                    <span>Nama Toko</span>
                    <strong>{{ $sellerApplication->shop_name }}</strong>
                </div>

                <div class="ud-info">
                    <span>Status Penjual</span>
                    <strong>
                        {{ ucfirst($sellerApplication->status) }}
                    </strong>
                </div>

                <div class="ud-info">
                    <span>Nomor Telepon</span>
                    <strong>{{ $sellerApplication->phone ?? '-' }}</strong>
                </div>

                <div class="ud-info">
                    <span>Deskripsi Toko</span>
                    <strong>{{ $sellerApplication->description ?? '-' }}</strong>
                </div>

            @else

                <p style="color:#777;font-size:13px;">
                    Pengguna ini belum mendaftar sebagai penjual.
                </p>

            @endif

        </div>

    </div>

    <!-- STATISTIK AKTIVITAS -->
    <div class="ud-stats">

        <div class="ud-stat">
            <span>Total Produk Diunggah</span>
            <strong>{{ $totalProducts }}</strong>
        </div>

        <div class="ud-stat">
            <span>Total Pesanan Dibuat</span>
            <strong>{{ $totalOrders }}</strong>
        </div>

    </div>

<!-- RIWAYAT PESANAN PENGGUNA -->
<section style="background:#fff;border:1px solid #e9e1e3;border-radius:13px;padding:24px;margin-top:24px;">

    <h2 style="color:#800e19;font-size:20px;margin:0 0 8px;">
        Riwayat Pesanan
    </h2>

    <p style="font-size:13px;color:#777;margin-bottom:20px;">
        Daftar pesanan yang dibuat oleh pengguna ini.
    </p>

    <div style="overflow-x:auto;">
        <table style="width:100%;min-width:800px;border-collapse:collapse;">
            <thead>
                <tr style="background:#faf5f6;color:#800e19;text-align:left;">
                    <th style="padding:13px;">Nomor Pesanan</th>
                    <th style="padding:13px;">Tanggal</th>
                    <th style="padding:13px;">Produk</th>
                    <th style="padding:13px;">Total</th>
                    <th style="padding:13px;">Pembayaran</th>
                    <th style="padding:13px;">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($orders as $order)
                    <tr style="border-bottom:1px solid #eee;">

                        <td style="padding:13px;font-size:13px;">
                            <strong>{{ $order->order_number }}</strong>
                        </td>

                        <td style="padding:13px;font-size:13px;">
                            {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>

                        <td style="padding:13px;font-size:13px;">
                            @forelse($order->items as $item)
                                <div style="margin-bottom:5px;">
                                    {{ $item->product_name }}
                                    × {{ $item->quantity }}
                                </div>
                            @empty
                                -
                            @endforelse
                        </td>

                        <td style="padding:13px;font-size:13px;font-weight:700;color:#800e19;">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </td>

                        <td style="padding:13px;font-size:13px;">
                            {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}
                            <div style="color:#777;margin-top:5px;">
                                {{ ucfirst($order->payment_status) }}
                            </div>
                        </td>

                        <td style="padding:13px;font-size:13px;">
                            <span style="display:inline-block;padding:6px 10px;border-radius:20px;background:#f5e7eb;color:#800e19;font-weight:700;">
                                {{ ucfirst($order->order_status) }}
                            </span>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:35px;color:#777;">
                            Pengguna ini belum memiliki riwayat pesanan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $orders->links() }}
    </div>

</section>

</div>

@endsection
