
@extends('layouts.app')

@section('title', 'Toko Saya - UIN Mart')

@section('content')

<style>
.seller-dashboard {
    max-width: 1150px;
    width: calc(100% - 32px);
    margin: 30px auto 60px;
    color: #29252a;
}

/* HEADER */

.seller-dashboard-header {
    background: #800e19;
    color: #ffffff;
    padding: 26px 30px;
    border-radius: 15px;
    margin-bottom: 22px;
}

.seller-dashboard-header h1 {
    color: #ffffff !important;
    font-size: 27px;
    margin: 0 0 7px;
}

.seller-dashboard-header p {
    margin: 0;
    color: #f7e2e4 !important;
    font-size: 13px;
    line-height: 1.6;
}

/* STATISTIK */

.seller-dashboard-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.seller-dashboard-card {
    display: block;
    background: #ffffff;
    border: 1px solid #e9e1e3;
    padding: 22px;
    border-radius: 13px;
    min-width: 0;
}

.seller-dashboard-card span {
    display: block;
    color: #777777;
    font-size: 13px;
}

.seller-dashboard-card strong {
    display: block;
    font-size: 26px;
    color: #800e19;
    margin-top: 8px;
}

.seller-dashboard-card small {
    display: block;
    margin-top: 7px;
    color: #777777;
    font-size: 12px;
}

/* LINK STATISTIK PESANAN */

.seller-order-stat-link {
    text-decoration: none !important;
    color: inherit !important;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.seller-order-stat-link:hover {
    border-color: #800e19;
    box-shadow: 0 5px 18px rgba(128, 14, 25, .08);
}

/* NOTIFIKASI PESANAN */

.seller-order-notice {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
    padding: 20px 24px;
    margin-bottom: 24px;
    background: #fff8e8;
    border: 1px solid #f1dda6;
    border-radius: 13px;
}

.seller-order-notice h3 {
    margin: 0 0 7px;
    font-size: 17px;
    color: #7b4e09 !important;
}

.seller-order-notice p {
    margin: 0;
    color: #7b5a10 !important;
    font-size: 13px;
    line-height: 1.6;
}

.seller-order-btn {
    display: inline-block;
    padding: 12px 18px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
    white-space: nowrap;
    transition: background .2s ease;
}

.seller-order-btn:hover {
    background: #650b14;
    color: #ffffff !important;
}

/* PANEL PRODUK */

.seller-product-panel {
    background: #ffffff;
    border: 1px solid #e9e1e3;
    border-radius: 13px;
    padding: 23px;
    overflow-x: auto;
}

.seller-product-panel h2 {
    margin: 0 0 17px;
    font-size: 20px;
    color: #29252a;
}

.seller-add-product {
    display: inline-block;
    margin-bottom: 18px;
    padding: 11px 18px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
}

.seller-add-product:hover {
    background: #650b14;
    color: #ffffff !important;
}

.seller-product-table {
    width: 100%;
    min-width: 740px;
    border-collapse: collapse;
    text-align: left;
}

.seller-product-table th,
.seller-product-table td {
    padding: 13px;
    border-bottom: 1px solid #eeeeee;
    vertical-align: middle;
}

.seller-product-table th {
    color: #800e19;
    background: #faf5f6;
    font-size: 12px;
}

.seller-product-table td {
    font-size: 13px;
}

.seller-product-table td strong {
    color: #29252a;
}

/* STATUS */

.seller-status-label {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.seller-status-pending {
    background: #fff3d5;
    color: #93610b;
}

.seller-status-approved {
    background: #e4f6ea;
    color: #267a44;
}

.seller-status-rejected {
    background: #ffeaed;
    color: #b42936;
}

.seller-dashboard-empty {
    padding: 35px 15px;
    color: #777777;
    text-align: center;
}

/* BUTTON EDIT */

.seller-edit-btn {
    display: inline-block;
    padding: 8px 14px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 7px;
    text-decoration: none !important;
    font-weight: 700;
    font-size: 12px;
}

.seller-edit-btn:hover {
    background: #650b14;
    color: #ffffff !important;
}

/* BUTTON KEMBALI */

.seller-dashboard-back {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 20px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none !important;
    transition: background .2s ease;
}

.seller-dashboard-back:hover {
    background: #650b14;
    color: #ffffff !important;
}

/* RESPONSIVE */

@media (max-width: 800px) {
    .seller-dashboard-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 650px) {
    .seller-dashboard-stats {
        grid-template-columns: 1fr;
    }

    .seller-dashboard-header {
        padding: 20px;
    }

    .seller-product-panel {
        padding: 16px;
    }

    .seller-order-notice {
        padding: 18px;
    }
}
</style>

<div class="seller-dashboard">

    <!-- HEADER TOKO -->
    <div class="seller-dashboard-header">

        <h1>Toko Saya</h1>

        <p>
            Selamat datang di {{ $application->shop_name }}!
            Kelola toko dan produk Anda di UIN Mart.
        </p>

    </div>

    <!-- STATISTIK TOKO -->
    <div class="seller-dashboard-stats">

        <!-- TOTAL PRODUK -->
        <div class="seller-dashboard-card">

            <span>Total Produk Saya</span>

            <strong>
                {{ $products->total() }}
            </strong>

            <small>Produk yang terdaftar di toko</small>

        </div>

        <!-- STATUS SELLER -->
        <div class="seller-dashboard-card">

            <span>Status Penjual</span>

            <strong style="color:#25834b;font-size:22px;">
                Disetujui
            </strong>

            <small>Akun penjual telah disetujui Admin</small>

        </div>

        <!-- PESANAN MASUK -->
        <a
            href="{{ route('seller.orders.index') }}"
            class="seller-dashboard-card seller-order-stat-link"
        >

            <span>Pesanan Masuk</span>

            <strong>
                {{ $totalOrders }}
            </strong>

            <small>
                {{ $pendingOrders }} pesanan menunggu
            </small>

        </a>

    </div>

    <!-- NOTIFIKASI PESANAN -->
    @if($pendingOrders > 0)

        <div class="seller-order-notice">

            <div>

                <h3>
                    Anda memiliki {{ $pendingOrders }} pesanan menunggu!
                </h3>

                <p>
                    Ada pesanan pelanggan yang menunggu diproses.
                    Periksa detail pesanan toko Anda.
                </p>

            </div>

            <a
                href="{{ route('seller.orders.index') }}"
                class="seller-order-btn"
            >
                Lihat Pesanan Masuk
            </a>

        </div>

    @endif

    <!-- PANEL PRODUK -->
    <div class="seller-product-panel">

        <h2>Produk Saya</h2>

        <a
            href="{{ route('seller.products.create') }}"
            class="seller-add-product"
        >
            + Tambah Produk
        </a>

        <!-- NOTIFIKASI BERHASIL -->
        @if(session('success'))

            <div style="background:#e4f6ea;color:#267a44;padding:12px 15px;border-radius:8px;margin-bottom:18px;font-size:13px;">

                {{ session('success') }}

            </div>

        @endif

        <!-- TABEL PRODUK -->
        @if($products->count() > 0)

            <table class="seller-product-table">

                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status Produk</th>
                        <th>Status Persetujuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($products as $product)

                        <tr>

                            <!-- NAMA -->
                            <td>
                                <strong>{{ $product->name }}</strong>
                            </td>

                            <!-- HARGA -->
                            <td>
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>

                            <!-- STOK -->
                            <td>
                                {{ $product->stock }}
                            </td>

                            <!-- STATUS PRODUK -->
                            <td>

                                @if($product->status === 'active')

                                    <span class="seller-status-label seller-status-approved">
                                        Aktif
                                    </span>

                                @else

                                    <span class="seller-status-label seller-status-rejected">
                                        Tidak Aktif
                                    </span>

                                @endif

                            </td>

                            <!-- STATUS PERSETUJUAN -->
                            <td>

                                @if($product->approval_status === 'pending')

                                    <span class="seller-status-label seller-status-pending">
                                        Menunggu Persetujuan
                                    </span>

                                @elseif($product->approval_status === 'approved')

                                    <span class="seller-status-label seller-status-approved">
                                        Disetujui
                                    </span>

                                @elseif($product->approval_status === 'rejected')

                                    <span class="seller-status-label seller-status-rejected">
                                        Ditolak
                                    </span>

                                @else

                                    <span class="seller-status-label">
                                        {{ ucfirst($product->approval_status ?? '-') }}
                                    </span>

                                @endif

                            </td>

                            <!-- AKSI -->
                            <td>

                                <a
                                    href="{{ route('seller.products.edit', $product->id) }}"
                                    class="seller-edit-btn"
                                >
                                    Edit
                                </a>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <!-- PAGINATION -->
            <div style="margin-top:20px;">
                {{ $products->links() }}
            </div>

        @else

            <div class="seller-dashboard-empty">

                Belum ada produk di toko Anda.

                <p>
                    Klik tombol
                    <strong>+ Tambah Produk</strong>
                    untuk mengajukan produk pertama Anda.
                </p>

            </div>

        @endif

    </div>

    <!-- KEMBALI -->
    <a
        class="seller-dashboard-back"
        href="{{ route('home') }}"
    >
        ← Kembali ke Beranda
    </a>

</div>

@endsection
