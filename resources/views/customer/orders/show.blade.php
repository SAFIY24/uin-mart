
@extends('layouts.app')

@section('title', 'Detail Pesanan - UIN Mart')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | INFORMASI PESANAN DAN HARGA
    |--------------------------------------------------------------------------
    */

    $items = $order->items;

    $hasFullPriceHistory = $items->isNotEmpty()
        && $items->every(
            fn ($item) =>
                $item->original_price !== null
                && $item->product_discount_amount !== null
        );

    $afterProductDiscount = (float) (
        $order->subtotal
        ?? (
            (float) $order->total
            + (float) ($order->discount_amount ?? 0)
        )
    );

    $voucherDiscount = (float) ($order->discount_amount ?? 0);

    $normalSubtotal = $hasFullPriceHistory
        ? $items->sum(
            fn ($item) =>
                (float) $item->original_price
                * (int) $item->quantity
        )
        : null;

    $productDiscount = $hasFullPriceHistory
        ? $items->sum(
            fn ($item) => (float) $item->product_discount_amount
        )
        : null;

    /*
    |--------------------------------------------------------------------------
    | STATUS PESANAN DAN PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    $orderStatusLabels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    $paymentStatusLabels = [
        'pending' => 'Belum Dibayar',
        'paid' => 'Sudah Dibayar',
        'failed' => 'Gagal',
    ];

    $paymentMethodLabels = [
        'cod' => 'COD — Bayar di Tempat',
        'bank_transfer' => 'Transfer Bank',
    ];

    $orderStatus = $order->order_status ?? 'pending';
    $paymentStatus = $order->payment_status ?? 'pending';

    $orderStatusClass = $orderStatus === 'completed'
        ? 'co-status-success'
        : (
            $orderStatus === 'cancelled'
                ? 'co-status-danger'
                : 'co-status-pending'
        );

    $paymentStatusClass = $paymentStatus === 'paid'
        ? 'co-status-success'
        : (
            $paymentStatus === 'failed'
                ? 'co-status-danger'
                : 'co-status-pending'
        );

    /*
    |--------------------------------------------------------------------------
    | STATUS PENGIRIMAN SELLER
    |--------------------------------------------------------------------------
    */

    $deliveryLabels = [
        'pending' => 'Menunggu Konfirmasi Penjual',
        'confirmed' => 'Dikonfirmasi Penjual',
        'preparing' => 'Sedang Dipersiapkan',
        'ready' => 'Siap Dikirim / Diambil',
        'delivering' => 'Dalam Pengiriman',
        'handed_over' => 'Menunggu Konfirmasi Pembeli',
        'completed' => 'Barang Diterima',
        'cancelled' => 'Dibatalkan',
    ];

    /*
    |--------------------------------------------------------------------------
    | PENGIRIMAN PRODUK ADMIN
    |--------------------------------------------------------------------------
    |
    | Controller mengirim variabel $adminFulfillment.
    | Produk Admin hanya ditampilkan jika pesanan
    | memiliki item dengan seller_id NULL.
    |
    */

    $adminFulfillment = $adminFulfillment ?? null;

    $adminItems = $items->filter(
        fn ($item) => $item->seller_id === null
    );

    $hasAdminItems = $adminItems->isNotEmpty();

    $canConfirmAdminReceived =
        $hasAdminItems
        && $adminFulfillment
        && $adminFulfillment->status === 'handed_over'
        && !in_array(
            $orderStatus,
            ['cancelled', 'completed'],
            true
        );
@endphp

<style>
/* =========================================================
   CUSTOMER ORDER DETAIL - UIN MART
   ========================================================= */

.co-page {
    width: calc(100% - 40px);
    max-width: 1180px;
    margin: 30px auto 60px;
    color: #29252a;
    box-sizing: border-box;
}

/* HEADER */

.co-hero {
    background: #800e19;
    color: #ffffff;
    padding: 27px 30px;
    border-radius: 15px;
    margin-bottom: 20px;
}

.co-hero h1 {
    margin: 0 0 8px;
    font-size: 27px;
    color: #ffffff !important;
}

.co-hero p {
    margin: 0;
    color: #f7e2e4;
    font-size: 13px;
    overflow-wrap: anywhere;
}

/* GRID */

.co-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    align-items: stretch;
}

/* CARD */

.co-card {
    background: #ffffff;
    border: 1px solid #e9e1e3;
    border-radius: 14px;
    padding: 23px;
    min-width: 0;
}

.co-card-full {
    grid-column: 1 / -1;
}

.co-card h2 {
    font-size: 19px;
    margin: 0 0 15px;
    color: #800e19;
    font-weight: 800;
}

/* INFORMATION ROWS */

.co-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    padding: 13px 0;
    border-bottom: 1px solid #f0e9ea;
}

.co-row:last-child {
    border-bottom: none;
}

.co-row > span {
    color: #777777;
    font-size: 13px;
}

.co-row > strong {
    color: #29252a;
    font-size: 13px;
    font-weight: 700;
    text-align: right;
    overflow-wrap: anywhere;
    white-space: pre-line;
}

/* STATUS BADGES */

.co-status {
    display: inline-block;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.co-status-pending {
    background: #fff3d5;
    color: #93610b;
}

.co-status-success {
    background: #e4f6ea;
    color: #267a44;
}

.co-status-danger {
    background: #ffeaed;
    color: #b42936;
}

/* PRODUCT TABLE */

.co-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.co-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    min-width: 690px;
}

.co-table th {
    background: #faf5f6;
    color: #800e19;
    padding: 13px;
    font-size: 12px;
    font-weight: 800;
}

.co-table td {
    padding: 15px 13px;
    border-bottom: 1px solid #f0e9ea;
    font-size: 13px;
    vertical-align: middle;
}

.co-table td strong {
    color: #29252a;
}

/* PAYMENT */

.co-discount {
    color: #267a44 !important;
    font-weight: 800 !important;
}

.co-total-row {
    background: #fff8f9;
    border-radius: 10px;
    padding: 18px;
    margin-top: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.co-total-row span {
    font-weight: 800;
    color: #29252a;
}

.co-total-value {
    font-size: 25px;
    color: #800e19 !important;
    font-weight: 900;
    text-align: right;
}

/* DELIVERY */

.co-delivery-item {
    background: #fffafb;
    border: 1px solid #eadde0;
    border-radius: 12px;
    padding: 20px;
    margin-top: 16px;
}

.co-delivery-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 15px;
}

.co-delivery-top h3 {
    color: #800e19;
    font-size: 16px;
    margin: 0;
}

.co-delivery-badge {
    display: inline-block;
    padding: 8px 12px;
    background: #fff1d6;
    color: #93610b;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 800;
}

.co-delivery-badge.success {
    background: #e4f6ea;
    color: #267a44;
}

.co-delivery-products {
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #eadfe2;
    border-radius: 9px;
    background: #ffffff;
}

.co-delivery-products p {
    margin: 0;
    color: #30252a;
    font-size: 13px;
    line-height: 1.8;
}

.co-delivery-note {
    color: #777777;
    font-size: 13px;
    line-height: 1.8;
    margin: 14px 0;
}

.co-delivery-confirm-btn {
    display: block;
    width: 100%;
    padding: 14px 20px;
    border: none;
    border-radius: 9px;
    background: #800e19;
    color: #ffffff !important;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: background .2s ease;
}

.co-delivery-confirm-btn:hover {
    background: #650b14;
}

.co-delivery-confirm-btn:focus-visible {
    outline: 3px solid #e9aab0;
    outline-offset: 3px;
}

.co-delivery-message {
    padding: 13px 16px;
    margin-bottom: 15px;
    border-radius: 9px;
    font-size: 13px;
    line-height: 1.7;
}

.co-delivery-message.success {
    background: #e4f6ea;
    color: #267a44;
}

.co-delivery-message.error {
    background: #ffeaed;
    color: #b42936;
}

.co-delivery-message.pending {
    background: #fff3d5;
    color: #93610b;
}

/* CUSTOMER SHIPPING */

.co-customer-shipping {
    margin: 15px 0;
    padding: 15px 18px;
    border: 1px solid #eadde0;
    border-radius: 10px;
    background: #ffffff;
}

.co-customer-shipping-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    padding: 11px 0;
    border-bottom: 1px solid #f0e9ea;
}

.co-customer-shipping-row:last-child {
    border-bottom: none;
}

.co-customer-shipping-row span {
    color: #777777;
    font-size: 13px;
}

.co-customer-shipping-row strong {
    color: #800e19;
    font-size: 13px;
    text-align: right;
    overflow-wrap: anywhere;
}

/* SELLER SCHEDULE */

.co-schedule-divider {
    margin: 15px 0 5px;
    padding-top: 15px;
    border-top: 2px solid #f0e0e3;
}

.co-schedule-heading {
    margin: 0 0 10px;
    color: #800e19;
    font-size: 14px;
    font-weight: 800;
}

.co-schedule-status {
    display: inline-block;
    padding: 7px 11px;
    border-radius: 8px;
    background: #e4f6ea;
    color: #267a44;
    font-size: 12px;
    font-weight: 800;
}

.co-schedule-pending {
    background: #fff3d5;
    color: #93610b;
}

.co-schedule-hint {
    margin: 12px 0 0;
    color: #777777;
    font-size: 12px;
    line-height: 1.7;
}

/* ADMIN DELIVERY */

.co-admin-section {
    margin-top: 20px;
}

.co-admin-description {
    font-size: 13px;
    color: #777777;
    line-height: 1.7;
    margin: 0 0 16px;
}

.co-admin-info {
    background: #faf7f8;
    border: 1px solid #eadfe2;
    border-radius: 10px;
    padding: 18px;
    margin: 15px 0;
}

.co-admin-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 11px 0;
    border-bottom: 1px solid #eee5e8;
}

.co-admin-info-row:last-child {
    border-bottom: none;
}

.co-admin-info-row span {
    color: #777777;
    font-size: 13px;
}

.co-admin-info-row strong {
    color: #800e19;
    font-size: 13px;
    text-align: right;
}

.co-admin-product-list {
    background: #ffffff;
    border: 1px solid #eadfe2;
    border-radius: 10px;
    padding: 16px;
    margin: 16px 0;
}

.co-admin-product-list p {
    color: #30252a;
    font-size: 13px;
    margin: 6px 0;
}

.co-admin-notice {
    background: #fff8e8;
    color: #93610b;
    border: 1px solid #f2deb3;
    padding: 15px 17px;
    border-radius: 9px;
    font-size: 13px;
    line-height: 1.8;
    margin: 16px 0;
}

/* FOOTER BUTTONS */

.co-bottom-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 22px;
}

.co-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 17px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
    transition: background .2s ease;
}

.co-btn-dark {
    background: #800e19;
    color: #ffffff !important;
}

.co-btn-dark:hover {
    background: #650b14;
}

.co-btn-outline {
    background: #ffffff;
    color: #800e19 !important;
    border: 1px solid #dfcdd0;
}

.co-btn-outline:hover {
    background: #fff3f5;
}

/* RESPONSIVE */

@media (max-width: 800px) {
    .co-grid {
        grid-template-columns: 1fr;
    }

    .co-card-full {
        grid-column: auto;
    }

    .co-page {
        width: calc(100% - 24px);
        margin: 20px auto 40px;
    }

    .co-hero {
        padding: 22px 20px;
    }

    .co-card {
        padding: 18px;
    }
}

@media (max-width: 650px) {
    .co-customer-shipping-row,
    .co-admin-info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .co-customer-shipping-row strong,
    .co-admin-info-row strong {
        text-align: left;
    }
}

@media (max-width: 480px) {
    .co-row {
        flex-direction: column;
        gap: 7px;
    }

    .co-row > strong {
        text-align: left;
    }

    .co-total-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .co-total-value {
        text-align: left;
    }

    .co-bottom-actions {
        flex-direction: column;
    }

    .co-btn {
        width: 100%;
        box-sizing: border-box;
    }
}
</style>

<div class="co-page">

    {{-- HEADER --}}
    <div class="co-hero">
        <h1>Detail Pesanan</h1>

        <p>
            No. Pesanan: {{ $order->order_number }}
        </p>
    </div>

    {{-- NOTIFIKASI --}}
    @if(session('success_delivery'))
        <div class="co-delivery-message success">
            {{ session('success_delivery') }}
        </div>
    @endif

    @if($errors->has('delivery'))
        <div class="co-delivery-message error">
            {{ $errors->first('delivery') }}
        </div>
    @endif

    {{-- INFORMASI PESANAN --}}
    <div class="co-grid">

        <section class="co-card">
            <h2>Informasi Pesanan</h2>

            <div class="co-row">
                <span>No. Pesanan</span>
                <strong>{{ $order->order_number }}</strong>
            </div>

            <div class="co-row">
                <span>Tanggal Pesanan</span>
                <strong>
                    {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                </strong>
            </div>

            <div class="co-row">
                <span>Status Pesanan</span>
                <strong>
                    <span class="co-status {{ $orderStatusClass }}">
                        {{ $orderStatusLabels[$orderStatus] ?? ucfirst($orderStatus) }}
                    </span>
                </strong>
            </div>

            <div class="co-row">
                <span>Status Pembayaran</span>
                <strong>
                    <span class="co-status {{ $paymentStatusClass }}">
                        {{ $paymentStatusLabels[$paymentStatus] ?? ucfirst($paymentStatus) }}
                    </span>
                </strong>
            </div>

            <div class="co-row">
                <span>Metode Pembayaran</span>
                <strong>
                    {{ $paymentMethodLabels[$order->payment_method]
                        ?? strtoupper(str_replace('_', ' ', $order->payment_method)) }}
                </strong>
            </div>
        </section>

        {{-- INFORMASI PENGIRIMAN --}}
        <section class="co-card">
            <h2>Informasi Pengiriman</h2>

            <div class="co-row">
                <span>Nama Penerima</span>
                <strong>{{ $order->customer_name }}</strong>
            </div>

            <div class="co-row">
                <span>Nomor Telepon</span>
                <strong>{{ $order->phone }}</strong>
            </div>

            <div class="co-row">
                <span>Alamat Pengiriman</span>
                <strong>{{ $order->address ?: '-' }}</strong>
            </div>

            @if($order->notes)
                <div class="co-row">
                    <span>Catatan Pesanan</span>
                    <strong>{{ $order->notes }}</strong>
                </div>
            @endif
        </section>

        {{-- DAFTAR PRODUK --}}
        <section class="co-card co-card-full">
            <h2>Produk yang Dipesan</h2>

            <div class="co-table-wrap">
                <table class="co-table">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Harga Normal</th>
                            <th>Diskon Produk</th>
                            <th>Harga Jual</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>

                                    <div style="
                                        color:#8b7c81;
                                        font-size:11px;
                                        margin-top:5px;
                                    ">
                                        {{ $item->seller_id === null
                                            ? 'Admin UIN Mart'
                                            : 'Toko #' . $item->seller_id }}
                                    </div>
                                </td>

                                <td>
                                    @if($item->original_price !== null)
                                        Rp {{ number_format(
                                            (float) $item->original_price,
                                            0, ',', '.'
                                        ) }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($item->product_discount_amount !== null)
                                        <span class="co-discount">
                                            - Rp {{ number_format(
                                                (float) $item->product_discount_amount,
                                                0, ',', '.'
                                            ) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    Rp {{ number_format(
                                        (float) $item->price,
                                        0, ',', '.'
                                    ) }}
                                </td>

                                <td>{{ $item->quantity }}</td>

                                <td>
                                    <strong>
                                        Rp {{ number_format(
                                            (float) $item->subtotal,
                                            0, ',', '.'
                                        ) }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    style="text-align:center;color:#777;">
                                    Detail produk tidak tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- RINGKASAN PEMBAYARAN --}}
        <section class="co-card co-card-full">
            <h2>Ringkasan Pembayaran</h2>

            @if($hasFullPriceHistory)

                <div class="co-row">
                    <span>Subtotal (Harga Normal)</span>
                    <strong>
                        Rp {{ number_format(
                            (float) $normalSubtotal, 0, ',', '.'
                        ) }}
                    </strong>
                </div>

                <div class="co-row">
                    <span>Diskon Produk</span>
                    <strong class="co-discount">
                        - Rp {{ number_format(
                            (float) $productDiscount, 0, ',', '.'
                        ) }}
                    </strong>
                </div>

            @else

                <div class="co-row">
                    <span>Subtotal Setelah Diskon Produk</span>
                    <strong>
                        Rp {{ number_format(
                            $afterProductDiscount, 0, ',', '.'
                        ) }}
                    </strong>
                </div>

            @endif

            @if($order->promotion_code && $voucherDiscount > 0)
                <div class="co-row">
                    <span>
                        Diskon Voucher
                        ({{ $order->promotion_code }})
                    </span>

                    <strong class="co-discount">
                        - Rp {{ number_format(
                            $voucherDiscount, 0, ',', '.'
                        ) }}
                    </strong>
                </div>
            @endif

            <div class="co-total-row">
                <span>Total Pembayaran</span>

                <strong class="co-total-value">
                    Rp {{ number_format(
                        (float) $order->total, 0, ',', '.'
                    ) }}
                </strong>
            </div>
        </section>

    </div>

    {{-- ====================================================
         KONFIRMASI PENERIMAAN BARANG SELLER
         ==================================================== --}}

    <section class="co-card" style="margin-top:20px;">
        <h2>Konfirmasi Penerimaan Barang</h2>

        @forelse($fulfillments as $fulfillment)

            @php
                $sellerProducts = $items->filter(
                    fn ($item) =>
                        (int) $item->seller_id
                        === (int) $fulfillment->seller_id
                );

                $canConfirmSeller =
                    $fulfillment->status === 'handed_over'
                    && !in_array(
                        $orderStatus,
                        ['cancelled', 'completed'],
                        true
                    );

                $hasSchedule =
                    !empty($fulfillment->scheduled_at)
                    && ($fulfillment->schedule_status ?? null) === 'confirmed';

                $sellerSchedule = $hasSchedule
                    ? \Carbon\Carbon::parse($fulfillment->scheduled_at)
                    : null;
            @endphp

            <div class="co-delivery-item">

                <div class="co-delivery-top">
                    <h3>
                        Toko #{{ $fulfillment->seller_id }}
                    </h3>

                    <span class="co-delivery-badge
                        {{ $fulfillment->status === 'completed'
                            ? 'success'
                            : '' }}">
                        {{ $deliveryLabels[$fulfillment->status]
                            ?? $fulfillment->status }}
                    </span>
                </div>

                {{-- INFORMASI PENGIRIMAN SELLER --}}
                <div class="co-customer-shipping">

                    @if($fulfillment->delivery_method === 'pickup')

                        <div class="co-customer-shipping-row">
                            <span>Metode Pengiriman</span>
                            <strong>Ambil di Kampus</strong>
                        </div>

                        <div class="co-customer-shipping-row">
                            <span>Lokasi Pengambilan</span>
                            <strong>
                                {{ $fulfillment->pickup_location
                                    ?: 'Belum ditentukan' }}
                            </strong>
                        </div>

                    @elseif($fulfillment->delivery_method === 'seller_delivery')

                        <div class="co-customer-shipping-row">
                            <span>Metode Pengiriman</span>
                            <strong>Diantar Penjual</strong>
                        </div>

                        <div class="co-customer-shipping-row">
                            <span>Alamat Pengiriman</span>
                            <strong>
                                {{ $fulfillment->delivery_address
                                    ?: $order->address }}
                            </strong>
                        </div>

                    @else

                        <div class="co-customer-shipping-row">
                            <span>Metode Pengiriman</span>
                            <strong>Belum ditentukan</strong>
                        </div>

                    @endif

                    {{-- JADWAL SELLER --}}
                    <div class="co-schedule-divider">
                        <h4 class="co-schedule-heading">
                            Jadwal Pengiriman / Pengambilan
                        </h4>
                    </div>

                    @if($hasSchedule)

                        <div class="co-customer-shipping-row">
                            <span>Status Jadwal</span>

                            <strong>
                                <span class="co-schedule-status">
                                    Jadwal Ditetapkan Penjual
                                </span>
                            </strong>
                        </div>

                        <div class="co-customer-shipping-row">
                            <span>
                                Tanggal Pengiriman / Pengambilan
                            </span>

                            <strong>
                                {{ $sellerSchedule->format('d/m/Y') }}
                            </strong>
                        </div>

                        <div class="co-customer-shipping-row">
                            <span>
                                Jam Pengiriman / Pengambilan
                            </span>

                            <strong>
                                {{ $sellerSchedule->format('H:i') }} WIB
                            </strong>
                        </div>

                        @if($fulfillment->schedule_note)
                            <div class="co-customer-shipping-row">
                                <span>Catatan Penjual</span>

                                <strong>
                                    {{ $fulfillment->schedule_note }}
                                </strong>
                            </div>
                        @endif

                        <p class="co-schedule-hint">
                            Jadwal ini ditentukan oleh penjual.
                            Silakan datang atau bersiap menerima
                            barang sesuai tanggal dan jam yang tertera.
                        </p>

                    @else

                        <div class="co-customer-shipping-row">
                            <span>Status Jadwal</span>

                            <strong>
                                <span class="co-schedule-status co-schedule-pending">
                                    Belum Ditetapkan Penjual
                                </span>
                            </strong>
                        </div>

                        <p class="co-schedule-hint">
                            Penjual belum menentukan jadwal
                            pengiriman atau pengambilan barang.
                            Silakan periksa kembali nanti.
                        </p>

                    @endif

                </div>

                {{-- PRODUK MILIK SELLER --}}
                <div class="co-delivery-products">
                    @foreach($sellerProducts as $product)
                        <p>
                            {{ $product->product_name }}
                            × {{ $product->quantity }}
                        </p>
                    @endforeach
                </div>

                {{-- KONFIRMASI DARI CUSTOMER --}}
                @if($fulfillment->status === 'completed')

                    <div class="co-delivery-message success">
                        Barang telah diterima.
                    </div>

                @elseif($canConfirmSeller)

                    <p class="co-delivery-note">
                        Penjual telah menyerahkan barang.
                        Konfirmasi hanya jika barang benar-benar
                        sudah Anda terima.
                    </p>

                    <form
                        action="{{ route('customer.orders.received', [
                            'order' => $order->id,
                            'sellerId' => $fulfillment->seller_id
                        ]) }}"
                        method="POST"
                        onsubmit="return confirm('Apakah barang sudah Anda terima?')"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="co-delivery-confirm-btn"
                        >
                            Pesanan Diterima
                        </button>
                    </form>

                @else

                    <p class="co-delivery-note">
                        Menunggu proses pengiriman atau
                        konfirmasi dari penjual.
                    </p>

                @endif

            </div>

        @empty

            @unless($hasAdminItems)
                <p class="co-delivery-note">
                    Belum ada data pengiriman Seller
                    untuk pesanan ini.
                </p>
            @endunless

        @endforelse

    </section>

    {{-- ====================================================
         PENGIRIMAN PRODUK ADMIN UIN MART
         ==================================================== --}}

    @if($hasAdminItems)

        <section class="co-card co-admin-section">

            <div class="co-delivery-top">

                <h2 style="margin:0;">
                    Pengiriman Produk Admin UIN Mart
                </h2>

                @if(!$adminFulfillment)

                    <span class="co-status co-status-danger">
                        Data Tidak Tersedia
                    </span>

                @elseif($adminFulfillment->status === 'completed')

                    <span class="co-status co-status-success">
                        Barang Diterima
                    </span>

                @elseif($adminFulfillment->status === 'handed_over')

                    <span class="co-status co-status-pending">
                        Sudah Diserahkan
                    </span>

                @else

                    <span class="co-status co-status-pending">
                        Belum Diserahkan
                    </span>

                @endif

            </div>

            <p class="co-admin-description">
                Bagian ini khusus untuk produk yang dijual
                langsung oleh Admin UIN Mart.
                Konfirmasi penerimaan dilakukan secara terpisah
                dari produk Seller.
            </p>

            {{-- DAFTAR PRODUK ADMIN --}}
            <div class="co-admin-product-list">

                <strong style="color:#800e19;font-size:13px;">
                    Produk Admin dalam Pesanan
                </strong>

                @foreach($adminItems as $adminItem)
                    <p>
                        {{ $adminItem->product_name }}
                        × {{ $adminItem->quantity }}
                    </p>
                @endforeach

            </div>

            @if(!$adminFulfillment)

                <div class="co-delivery-message error">
                    Data pengiriman produk Admin belum tersedia.
                    Silakan hubungi Admin UIN Mart.
                </div>

            @else

                {{-- INFORMASI STATUS --}}
                <div class="co-admin-info">

                    <div class="co-admin-info-row">
                        <span>Status Pengiriman</span>

                        <strong>
                            @if($adminFulfillment->status === 'pending')
                                Belum Diserahkan
                            @elseif($adminFulfillment->status === 'handed_over')
                                Sudah Diserahkan
                            @elseif($adminFulfillment->status === 'completed')
                                Barang Diterima
                            @else
                                {{ ucfirst($adminFulfillment->status) }}
                            @endif
                        </strong>
                    </div>

                    @if($adminFulfillment->handed_over_at)

                        <div class="co-admin-info-row">
                            <span>Waktu Penyerahan</span>

                            <strong>
                                {{ \Carbon\Carbon::parse(
                                    $adminFulfillment->handed_over_at
                                )->format('d/m/Y H:i') }}
                            </strong>
                        </div>

                    @endif

                    @if($adminFulfillment->completed_at)

                        <div class="co-admin-info-row">
                            <span>Waktu Penerimaan</span>

                            <strong>
                                {{ \Carbon\Carbon::parse(
                                    $adminFulfillment->completed_at
                                )->format('d/m/Y H:i') }}
                            </strong>
                        </div>

                    @endif

                </div>

                {{-- SUDAH DITERIMA --}}
                @if($adminFulfillment->status === 'completed')

                    <div class="co-delivery-message success">
                        Produk Admin telah diterima dan
                        dikonfirmasi oleh pembeli.
                    </div>

                {{-- ADMIN SUDAH MENYERAHKAN BARANG --}}
                @elseif($canConfirmAdminReceived)

                    <div class="co-admin-notice">
                        Admin telah menyerahkan produk.
                        Silakan konfirmasi hanya setelah
                        barang benar-benar Anda terima.
                    </div>

                    <form
                        action="{{ route(
                            'customer.orders.admin.received',
                            $order
                        ) }}"
                        method="POST"
                        onsubmit="return confirm('Apakah produk Admin benar-benar sudah Anda terima?')"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="co-delivery-confirm-btn"
                        >
                            Pesanan Admin Diterima
                        </button>
                    </form>

                {{-- PESANAN DIBATALKAN --}}
                @elseif($orderStatus === 'cancelled')

                    <div class="co-delivery-message error">
                        Pesanan telah dibatalkan.
                    </div>

                {{-- MENUNGGU ADMIN --}}
                @else

                    <div class="co-delivery-message pending">
                        Menunggu Admin menyerahkan produk.
                    </div>

                @endif

            @endif

        </section>

    @endif

    {{-- ====================================================
         NAVIGASI BAWAH
         ==================================================== --}}

    <div class="co-bottom-actions">

        <a
            href="{{ route('customer.orders.index') }}"
            class="co-btn co-btn-dark"
        >
            &larr; Kembali ke Pesanan Saya
        </a>

        <a
            href="{{ route('home') }}"
            class="co-btn co-btn-outline"
        >
            Kembali ke Beranda
        </a>

    </div>

</div>

@endsection
