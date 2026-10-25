
@extends('layouts.admin')

@section('title', 'Detail Pesanan · UIN Mart')

@section('admin_content')

@php
    /*
    |--------------------------------------------------------------------------
    | LABEL STATUS
    |--------------------------------------------------------------------------
    */

    $statusLabels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    $paymentLabels = [
        'pending' => 'Belum Dibayar',
        'paid' => 'Sudah Dibayar',
        'failed' => 'Gagal',
    ];

    $methodLabels = [
        'cod' => 'COD — Bayar di Tempat',
        'bank_transfer' => 'Transfer Bank',
    ];

    /*
    |--------------------------------------------------------------------------
    | DATA PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    $paymentAllocations = $paymentAllocations ?? collect();

    $adminPayment = $adminPayment ?? null;

    $subtotal = (float) $order->subtotal;

    $voucherDiscount = (float) $order->discount_amount;

    $grandTotal = (float) $order->total;

    $totalAllocated = $paymentAllocations->sum(
        fn ($payment) => (int) $payment->amount_due
    );

    $totalPaid = $paymentAllocations
        ->where('status', 'paid')
        ->sum('amount_due');

    $totalPending = $paymentAllocations
        ->where('status', 'pending')
        ->sum('amount_due');

    $allocationMatches =
        $paymentAllocations->isNotEmpty()
        && (int) $totalAllocated === (int) round($grandTotal);

    /*
    |--------------------------------------------------------------------------
    | HAK KONFIRMASI PEMBAYARAN ADMIN
    |--------------------------------------------------------------------------
    */

    $canConfirmAdmin =
        $adminPayment
        && $adminPayment->status === 'pending'
        && $order->payment_status === 'pending'
        && $order->order_status !== 'cancelled'
        && $allocationMatches;

    /*
    |--------------------------------------------------------------------------
    | DATA PENGIRIMAN PRODUK ADMIN
    |--------------------------------------------------------------------------
    |
    | Produk Admin tidak menggunakan seller_order_fulfillments.
    | Setiap pesanan yang berisi produk Admin mempunyai
    | satu catatan dalam admin_order_fulfillments.
    |
    */

    $hasAdminProducts = $order->items->contains(
        fn ($item) => $item->seller_id === null
    );

    $adminFulfillment = $hasAdminProducts
        ? \Illuminate\Support\Facades\DB::table(
            'admin_order_fulfillments'
        )
            ->where('order_id', $order->id)
            ->first()
        : null;

    $canHandoverAdmin =
        $adminFulfillment
        && $adminFulfillment->status === 'pending'
        && !in_array(
            $order->order_status,
            ['cancelled', 'completed'],
            true
        );

    /*
    |--------------------------------------------------------------------------
    | PERSENTASE KONFIRMASI PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    $paymentProgress = $grandTotal > 0
        ? min(
            100,
            max(0, round(((float) $totalPaid / $grandTotal) * 100))
        )
        : 0;

@endphp

<div class="uo-detail-page">

    <!-- HEADER -->
    <div class="uo-detail-header">

        <div>
            <p class="ua-eyebrow">
                ADMIN / PESANAN / DETAIL
            </p>

            <h1>Detail Pesanan</h1>

            <p class="ua-subtitle">
                No. Pesanan: {{ $order->order_number }}
            </p>
        </div>

        <a
            href="{{ route('admin.orders') }}"
            class="uo-back"
        >
            &larr; Kembali ke Pesanan
        </a>

    </div>

    <!-- NOTIFIKASI BERHASIL -->
    @if(session('success'))

        <div class="uo-alert uo-alert-success">
            {{ session('success') }}
        </div>

    @endif

    <!-- NOTIFIKASI ERROR -->
    @if($errors->any())

        <div class="uo-alert uo-alert-error">

            <strong>Terjadi kesalahan:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif

    <!-- INFORMASI UTAMA -->
    <div class="uo-detail-grid">

        <!-- INFORMASI PELANGGAN -->
        <section class="uo-card">

            <h2>Informasi Pelanggan</h2>

            <dl>

                <dt>Nama Penerima</dt>
                <dd>{{ $order->customer_name }}</dd>

                <dt>Nomor Telepon</dt>
                <dd>{{ $order->phone }}</dd>

                <dt>Alamat Pengiriman</dt>
                <dd class="uo-address">
                    {{ $order->address ?: 'Tidak tersedia' }}
                </dd>

                <dt>Catatan Pesanan</dt>
                <dd class="uo-address">
                    {{ $order->notes ?: 'Tidak ada catatan' }}
                </dd>

            </dl>

        </section>

        <!-- INFORMASI PESANAN -->
        <section class="uo-card">

            <h2>Informasi Pesanan</h2>

            <dl>

                <dt>Tanggal Pesanan</dt>
                <dd>
                    {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                </dd>

                <dt>Metode Pembayaran</dt>
                <dd>
                    {{ $methodLabels[$order->payment_method] ?? $order->payment_method }}
                </dd>

                <dt>Status Pembayaran</dt>
                <dd>

                    <span class="uo-badge
                        {{ $order->payment_status === 'paid'
                            ? 'uo-badge-success'
                            : (
                                $order->payment_status === 'failed'
                                    ? 'uo-badge-danger'
                                    : 'uo-badge-warning'
                            ) }}">
                        {{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}
                    </span>

                </dd>

                <dt>Status Pesanan</dt>
                <dd>

                    <span class="uo-badge
                        {{ $order->order_status === 'completed'
                            ? 'uo-badge-success'
                            : (
                                $order->order_status === 'cancelled'
                                    ? 'uo-badge-danger'
                                    : 'uo-badge-warning'
                            ) }}">
                        {{ $statusLabels[$order->order_status] ?? $order->order_status }}
                    </span>

                </dd>

            </dl>

        </section>

    </div>

    <!-- PRODUK YANG DIPESAN -->
    <section class="uo-card">

        <h2>Produk yang Dipesan</h2>

        <div class="uo-table-wrap">

            <table class="uo-table">

                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Penjual</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($order->items as $item)

                        <tr>

                            <td>
                                <strong>
                                    {{ $item->product_name }}
                                </strong>
                            </td>

                            <td>

                                @if($item->seller_id === null)

                                    <span class="uo-badge uo-badge-admin">
                                        Admin UIN Mart
                                    </span>

                                @else

                                    <span class="uo-badge uo-badge-neutral">
                                        Seller #{{ $item->seller_id }}
                                    </span>

                                @endif

                            </td>

                            <td>
                                Rp {{ number_format(
                                    (float) $item->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td>
                                {{ $item->quantity }}
                            </td>

                            <td>
                                <strong>
                                    Rp {{ number_format(
                                        (float) $item->subtotal,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="uo-empty">
                                Tidak ada detail produk.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- RINCIAN TOTAL -->
        <div class="uo-summary">

            <div class="uo-summary-row">

                <span>Subtotal Produk</span>

                <strong>
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </strong>

            </div>

            @if($voucherDiscount > 0)

                <div class="uo-summary-row uo-discount">

                    <span>
                        Diskon Voucher

                        @if($order->promotion_code)
                            ({{ $order->promotion_code }})
                        @endif
                    </span>

                    <strong>
                        - Rp {{ number_format(
                            $voucherDiscount,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>

                </div>

            @endif

            <div class="uo-summary-row uo-grand-total">

                <span>Total Pembayaran</span>

                <strong>
                    Rp {{ number_format(
                        $grandTotal,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

        </div>

    </section>

    <!-- RINCIAN PEMBAYARAN MARKETPLACE -->
    <section class="uo-card">

        <div class="uo-section-heading">

            <div>

                <h2>Rincian Pembayaran Marketplace</h2>

                <p>
                    Pembayaran dibagi berdasarkan produk milik
                    setiap penjual setelah diskon Voucher.
                </p>

            </div>

            <span class="uo-badge
                {{ $order->payment_status === 'paid'
                    ? 'uo-badge-success'
                    : (
                        $order->payment_status === 'failed'
                            ? 'uo-badge-danger'
                            : 'uo-badge-warning'
                    ) }}">
                {{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}
            </span>

        </div>

        <!-- STATISTIK PEMBAYARAN -->
        <div class="uo-payment-stats">

            <div class="uo-stat">

                <span>Total Tagihan</span>

                <strong>
                    Rp {{ number_format(
                        $grandTotal,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

            <div class="uo-stat">

                <span>Sudah Dikonfirmasi</span>

                <strong class="uo-money-success">
                    Rp {{ number_format(
                        (float) $totalPaid,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

            <div class="uo-stat">

                <span>Belum Dikonfirmasi</span>

                <strong class="uo-money-warning">
                    Rp {{ number_format(
                        (float) $totalPending,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

        </div>

        <!-- PROGRESS -->
        @if($paymentAllocations->isNotEmpty())

            <div class="uo-progress">

                <div class="uo-progress-label">

                    <span>Progres Konfirmasi Pembayaran</span>

                    <strong>
                        {{ $paymentProgress }}%
                    </strong>

                </div>

                <div class="uo-progress-track">

                    <div
                        class="uo-progress-fill"
                        style="width: {{ $paymentProgress }}%;"
                    ></div>

                </div>

            </div>

        @endif

        <!-- TABEL ALOKASI -->
        @if($paymentAllocations->isEmpty())

            <div class="uo-alert uo-alert-warning">
                Data pembagian pembayaran belum tersedia
                untuk pesanan ini.
            </div>

        @else

            @unless($allocationMatches)

                <div class="uo-alert uo-alert-error">
                    Total pembagian pembayaran tidak sesuai
                    dengan total pesanan. Periksa data
                    sebelum melakukan konfirmasi.
                </div>

            @endunless

            <div class="uo-table-wrap">

                <table class="uo-table">

                    <thead>
                        <tr>
                            <th>Penerima</th>
                            <th>Metode</th>
                            <th>Jumlah Tagihan</th>
                            <th>Status</th>
                            <th>Dikonfirmasi Oleh</th>
                            <th>Waktu Konfirmasi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($paymentAllocations as $payment)

                            <tr>

                                <td>

                                    @if($payment->recipient_type === 'admin')

                                        <span class="uo-badge uo-badge-admin">
                                            Admin UIN Mart
                                        </span>

                                    @else

                                        <span class="uo-badge uo-badge-neutral">
                                            Seller #{{ $payment->recipient_id }}
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    {{ $methodLabels[$payment->payment_method]
                                        ?? $payment->payment_method }}
                                </td>

                                <td>
                                    <strong>
                                        Rp {{ number_format(
                                            (float) $payment->amount_due,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </td>

                                <td>

                                    @if($payment->status === 'paid')

                                        <span class="uo-badge uo-badge-success">
                                            Sudah Diterima
                                        </span>

                                    @else

                                        <span class="uo-badge uo-badge-warning">
                                            Menunggu
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    {{ $payment->confirmed_by
                                        ? 'User #' . $payment->confirmed_by
                                        : '-' }}
                                </td>

                                <td>
                                    {{ $payment->confirmed_at
                                        ? \Carbon\Carbon::parse(
                                            $payment->confirmed_at
                                        )->format('d/m/Y H:i')
                                        : '-' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </section>

    <!-- KONFIRMASI PEMBAYARAN ADMIN -->
    <section class="uo-card">

        <div class="uo-section-heading">

            <div>

                <h2>Konfirmasi Pembayaran Admin</h2>

                <p>
                    Hanya untuk pembayaran produk yang dijual
                    langsung oleh Admin UIN Mart.
                </p>

            </div>

        </div>

        @if(!$adminPayment)

            <div class="uo-alert uo-alert-warning">
                Pesanan ini tidak memiliki tagihan
                khusus Admin. Pembayaran Seller harus
                dikonfirmasi oleh Seller masing-masing.
            </div>

        @else

            <div class="uo-admin-payment">

                <div>

                    <span class="uo-small-label">
                        Jumlah Tagihan Admin
                    </span>

                    <div class="uo-admin-payment-amount">
                        Rp {{ number_format(
                            (float) $adminPayment->amount_due,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>

                </div>

                @if($adminPayment->status === 'paid')

                    <span class="uo-badge uo-badge-success">
                        Pembayaran Diterima
                    </span>

                @else

                    <span class="uo-badge uo-badge-warning">
                        Belum Dikonfirmasi
                    </span>

                @endif

            </div>

            @if($adminPayment->status === 'paid')

                <div class="uo-alert uo-alert-success">

                    Pembayaran produk Admin telah dikonfirmasi.

                    @if($adminPayment->confirmed_at)

                        <p>
                            Tanggal:
                            {{ \Carbon\Carbon::parse(
                                $adminPayment->confirmed_at
                            )->format('d/m/Y H:i') }}
                        </p>

                    @endif

                </div>

            @elseif($canConfirmAdmin)

                <div class="uo-payment-notice">

                    Pastikan Admin benar-benar telah menerima
                    pembayaran sebesar

                    <strong>
                        Rp {{ number_format(
                            (float) $adminPayment->amount_due,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>

                    sebelum melakukan konfirmasi.

                </div>

                <form
                    action="{{ route('admin.orders.payment.confirm', $order) }}"
                    method="POST"
                    onsubmit="return confirm('Apakah Admin benar-benar sudah menerima pembayaran untuk produk Admin?')"
                >

                    @csrf

                    <button
                        type="submit"
                        class="uo-confirm-button"
                    >
                        Konfirmasi Pembayaran Diterima
                    </button>

                </form>

            @else

                <div class="uo-alert uo-alert-warning">
                    Konfirmasi pembayaran tidak tersedia.
                    Periksa status pesanan dan total
                    alokasi pembayaran.
                </div>

            @endif

        @endif

    </section>

    <!-- ======================================================
         PENGIRIMAN PRODUK ADMIN
         BAGIAN INI SEKARANG BERADA DI LUAR <style>
         DAN DI DALAM .uo-detail-page
         ====================================================== -->

    @if($hasAdminProducts)

        <section class="uo-card">

            <div class="uo-section-heading">

                <div>

                    <h2>Pengiriman Produk Admin</h2>

                    <p>
                        Kelola penyerahan produk yang dijual
                        langsung oleh Admin UIN Mart.
                    </p>

                </div>

                @if(!$adminFulfillment)

                    <span class="uo-badge uo-badge-danger">
                        Data Tidak Tersedia
                    </span>

                @elseif($adminFulfillment->status === 'completed')

                    <span class="uo-badge uo-badge-success">
                        Selesai
                    </span>

                @elseif($adminFulfillment->status === 'handed_over')

                    <span class="uo-badge uo-badge-warning">
                        Menunggu Konfirmasi Pembeli
                    </span>

                @else

                    <span class="uo-badge uo-badge-warning">
                        Belum Diserahkan
                    </span>

                @endif

            </div>

            @if(!$adminFulfillment)

                <div class="uo-alert uo-alert-error">
                    Data pengiriman produk Admin tidak ditemukan.
                    Periksa tabel admin_order_fulfillments.
                </div>

            @else

                <!-- STATUS PENGIRIMAN ADMIN -->
                <div class="uo-delivery-status">

                    <div>

                        <span class="uo-small-label">
                            Status Pengiriman
                        </span>

                        <strong class="uo-delivery-value">
                            @if($adminFulfillment->status === 'pending')
                                Belum Diserahkan
                            @elseif($adminFulfillment->status === 'handed_over')
                                Sudah Diserahkan
                            @elseif($adminFulfillment->status === 'completed')
                                Sudah Diterima Pembeli
                            @else
                                {{ strtoupper($adminFulfillment->status) }}
                            @endif
                        </strong>

                    </div>

                    <div>

                        <span class="uo-small-label">
                            Status Pesanan
                        </span>

                        <strong>
                            {{ $statusLabels[$order->order_status]
                                ?? $order->order_status }}
                        </strong>

                    </div>

                </div>

                <!-- WAKTU PENYERAHAN -->
                @if($adminFulfillment->handed_over_at)

                    <div class="uo-delivery-time">

                        <span>Waktu Penyerahan</span>

                        <strong>
                            {{ \Carbon\Carbon::parse(
                                $adminFulfillment->handed_over_at
                            )->format('d/m/Y H:i') }}
                        </strong>

                    </div>

                @endif

                <!-- WAKTU PENERIMAAN -->
                @if($adminFulfillment->completed_at)

                    <div class="uo-delivery-time">

                        <span>Waktu Diterima Pembeli</span>

                        <strong>
                            {{ \Carbon\Carbon::parse(
                                $adminFulfillment->completed_at
                            )->format('d/m/Y H:i') }}
                        </strong>

                    </div>

                @endif

                <!-- TOMBOL PENYERAHAN -->
                @if($canHandoverAdmin)

                    <div class="uo-payment-notice">

                        Pastikan produk Admin benar-benar
                        telah diserahkan kepada pembeli.

                        Setelah konfirmasi, Customer dapat
                        mengonfirmasi penerimaan melalui
                        halaman Pesanan Saya.

                    </div>

                    <form
                        action="{{ route('admin.orders.admin.handover', $order) }}"
                        method="POST"
                        onsubmit="return confirm('Apakah produk Admin benar-benar sudah diserahkan kepada pembeli?')"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="uo-confirm-button"
                        >
                            Barang Admin Sudah Diserahkan
                        </button>

                    </form>

                @elseif($adminFulfillment->status === 'handed_over')

                    <div class="uo-alert uo-alert-warning">

                        Produk Admin telah diserahkan.

                        Menunggu Customer mengonfirmasi
                        penerimaan barang melalui
                        halaman Pesanan Saya.

                    </div>

                @elseif($adminFulfillment->status === 'completed')

                    <div class="uo-alert uo-alert-success">

                        Produk Admin telah diterima
                        dan dikonfirmasi oleh Customer.

                        Proses pengiriman produk Admin selesai.

                    </div>

                @elseif($order->order_status === 'cancelled')

                    <div class="uo-alert uo-alert-error">
                        Pesanan telah dibatalkan.
                        Produk tidak dapat diserahkan.
                    </div>

                @else

                    <div class="uo-alert uo-alert-warning">
                        Konfirmasi penyerahan tidak tersedia
                        pada status pesanan saat ini.
                    </div>

                @endif

            @endif

        </section>

    @endif

</div>

<!-- =========================================================
     CSS GIAO DIEN ADMIN
     Toan bo HTML da ket thuc truoc the style.
     ========================================================= -->

<style>

.uo-detail-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.uo-detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.uo-detail-header h1 {
    margin: 7px 0;
    font-size: 27px;
    color: #2e1c22;
}

.uo-detail-header .ua-eyebrow {
    color: #a48890;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
}

.uo-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 16px;
    border: 1px solid #eadfe2;
    border-radius: 9px;
    color: #800e19;
    background: #fff;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    transition: background .2s;
}

.uo-back:hover {
    background: #fff4f5;
}

.uo-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.uo-card {
    background: #fff;
    border: 1px solid #ece3e6;
    border-radius: 15px;
    padding: 25px;
    min-width: 0;
}

.uo-card h2 {
    margin: 0 0 18px;
    color: #321d24;
    font-size: 19px;
}

.uo-card dl {
    margin: 0;
    display: grid;
    grid-template-columns: 150px minmax(0, 1fr);
    gap: 17px;
}

.uo-card dt {
    font-size: 12px;
    font-weight: 700;
    color: #8c7e83;
}

.uo-card dd {
    margin: 0;
    font-size: 13px;
    color: #31262b;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.uo-address {
    white-space: pre-wrap;
    line-height: 1.7;
}

.uo-table-wrap {
    overflow-x: auto;
}

.uo-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
    font-size: 13px;
}

.uo-table th {
    padding: 14px;
    text-align: left;
    background: #faf5f6;
    color: #6a5158;
    white-space: nowrap;
}

.uo-table td {
    padding: 16px 14px;
    border-bottom: 1px solid #eee6e9;
    vertical-align: middle;
}

.uo-table tr:last-child td {
    border-bottom: none;
}

.uo-table strong {
    color: #38272c;
}

.uo-empty {
    text-align: center;
    color: #85797e;
}

.uo-summary {
    max-width: 450px;
    margin: 25px 0 0 auto;
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.uo-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    font-size: 13px;
}

.uo-summary-row strong {
    font-weight: 800;
}

.uo-discount {
    color: #18734e;
}

.uo-grand-total {
    border-top: 1px solid #eee5e8;
    padding-top: 17px;
    margin-top: 5px;
    font-weight: 800;
}

.uo-grand-total strong {
    color: #800e19;
    font-size: 23px;
}

.uo-section-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.uo-section-heading h2 {
    margin-bottom: 7px;
}

.uo-section-heading p {
    margin: 0;
    color: #8a7e83;
    font-size: 12px;
    line-height: 1.7;
}

.uo-payment-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.uo-stat {
    background: #faf7f8;
    border: 1px solid #eee7e9;
    border-radius: 12px;
    padding: 17px;
}

.uo-stat span,
.uo-small-label {
    display: block;
    color: #84777c;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 9px;
}

.uo-stat strong {
    color: #38252d;
    font-size: 20px;
}

.uo-money-success {
    color: #14794d !important;
}

.uo-money-warning {
    color: #a36813 !important;
}

.uo-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 6px;
    padding: 7px 10px;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.3;
    white-space: normal;
}

.uo-badge-success {
    background: #e4f7ec;
    color: #14794d;
}

.uo-badge-warning {
    background: #fff1d7;
    color: #986000;
}

.uo-badge-danger {
    background: #fde8e8;
    color: #b42332;
}

.uo-badge-admin {
    background: #fcebee;
    color: #800e19;
}

.uo-badge-neutral {
    background: #f0f0f2;
    color: #4e5260;
}

.uo-alert {
    border-radius: 10px;
    padding: 15px 17px;
    font-size: 13px;
    line-height: 1.8;
    margin-bottom: 15px;
}

.uo-alert p {
    margin: 8px 0 0;
}

.uo-alert ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

.uo-alert-success {
    background: #eaf8f0;
    border: 1px solid #bce8cc;
    color: #126f44;
}

.uo-alert-warning {
    background: #fff8e8;
    border: 1px solid #f4dcaa;
    color: #91620c;
}

.uo-alert-error {
    background: #fff0f1;
    border: 1px solid #f3c5cb;
    color: #a02635;
}

.uo-admin-payment {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
    padding: 20px;
    background: #faf7f8;
    border-radius: 12px;
    margin-bottom: 20px;
}

.uo-admin-payment-amount {
    font-size: 28px;
    font-weight: 800;
    color: #800e19;
}

.uo-payment-notice {
    background: #fff8e8;
    border: 1px solid #f2d9a8;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 18px;
    color: #795711;
    font-size: 13px;
    line-height: 1.8;
}

.uo-confirm-button {
    border: none;
    background: #800e19;
    color: #fff;
    border-radius: 9px;
    padding: 14px 22px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: background .2s;
}

.uo-confirm-button:hover {
    background: #650812;
}

.uo-confirm-button:focus-visible {
    outline: 3px solid #e7a6ae;
    outline-offset: 3px;
}

.uo-progress {
    margin-bottom: 23px;
}

.uo-progress-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #756970;
    font-weight: 700;
    margin-bottom: 9px;
}

.uo-progress-track {
    width: 100%;
    height: 9px;
    background: #eee7e9;
    border-radius: 50px;
    overflow: hidden;
}

.uo-progress-fill {
    height: 100%;
    background: #178451;
    border-radius: 50px;
    transition: width .2s ease;
}

.uo-delivery-status {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.uo-delivery-status > div {
    background: #faf7f8;
    border: 1px solid #eee7e9;
    border-radius: 12px;
    padding: 20px;
}

.uo-delivery-value {
    display: block;
    font-size: 17px;
    color: #321d24;
}

.uo-delivery-time {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 13px 0;
    border-bottom: 1px solid #eee6e9;
    font-size: 13px;
}

.uo-delivery-time span {
    color: #84777c;
}

.uo-delivery-time strong {
    color: #38272c;
}

@media (max-width: 800px) {

    .uo-detail-grid {
        grid-template-columns: 1fr;
    }

    .uo-payment-stats {
        grid-template-columns: 1fr;
    }

    .uo-delivery-status {
        grid-template-columns: 1fr;
    }

    .uo-card dl {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .uo-card {
        padding: 19px;
    }

    .uo-confirm-button {
        width: 100%;
    }

    .uo-summary {
        max-width: 100%;
    }

}

</style>

@endsection
