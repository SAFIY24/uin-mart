
@extends('layouts.app')

@section('title', 'Pesanan Masuk - UIN Mart')

@section('content')

<style>
.so-page {
    max-width: 1150px;
    width: calc(100% - 32px);
    margin: 35px auto 65px;
    color: #30252a;
}

.so-header {
    padding: 28px;
    background: #800e19;
    border-radius: 15px;
    margin-bottom: 22px;
}

.so-header h1 {
    margin: 0 0 8px;
    color: #ffffff !important;
    font-size: 27px;
}

.so-header p {
    margin: 0;
    color: #f7dfe3 !important;
    font-size: 13px;
}

.so-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 24px;
}

.so-stat {
    background: #ffffff;
    border: 1px solid #eadfe2;
    border-radius: 12px;
    padding: 22px;
}

.so-stat span {
    display: block;
    margin-bottom: 9px;
    color: #777777;
    font-size: 13px;
}

.so-stat strong {
    color: #800e19;
    font-size: 28px;
}

.so-panel {
    background: #ffffff;
    border: 1px solid #eadfe2;
    border-radius: 13px;
    overflow: hidden;
}

.so-panel-title {
    padding: 22px;
    font-size: 18px;
    font-weight: 800;
    border-bottom: 1px solid #eee;
}

.so-table-wrap {
    overflow-x: auto;
}

.so-table {
    width: 100%;
    min-width: 790px;
    border-collapse: collapse;
    text-align: left;
}

.so-table th {
    padding: 15px;
    background: #faf6f7;
    color: #65545a;
    font-size: 12px;
}

.so-table td {
    padding: 17px 15px;
    border-top: 1px solid #f0e8ea;
    font-size: 13px;
}

.so-small {
    margin-top: 5px;
    color: #888;
    font-size: 12px;
}

.so-status {
    display: inline-block;
    padding: 7px 11px;
    border-radius: 7px;
    background: #fff4dd;
    color: #986000;
    font-size: 11px;
    font-weight: 800;
}

.so-status.processing {
    background: #eaf2ff;
    color: #205caa;
}

.so-status.shipped {
    background: #e9f5ff;
    color: #166083;
}

.so-status.completed {
    background: #e8f7ed;
    color: #14794d;
}

.so-status.cancelled {
    background: #f9e9eb;
    color: #a52e3b;
}

.so-btn {
    display: inline-block;
    padding: 11px 16px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 8px;
    text-decoration: none !important;
    font-weight: 700;
    font-size: 12px;
}

.so-btn:hover {
    background: #650b14;
}

.so-empty {
    padding: 35px;
    color: #888;
    text-align: center;
}

.so-pagination {
    padding: 20px;
}

.so-footer {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 22px;
}

@media(max-width:650px) {
    .so-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="so-page">

    <div class="so-header">
        <h1>Pesanan Masuk</h1>
        <p>
            Pantau pesanan pelanggan yang membeli produk toko Anda.
        </p>
    </div>

    <div class="so-stats">

        <div class="so-stat">
            <span>Total Pesanan</span>
            <strong>{{ $totalOrders }}</strong>
        </div>

        <div class="so-stat">
            <span>Menunggu</span>
            <strong>{{ $pendingOrders }}</strong>
        </div>

        <div class="so-stat">
            <span>Selesai</span>
            <strong>{{ $completedOrders }}</strong>
        </div>

    </div>

    <div class="so-panel">

        <div class="so-panel-title">
            Daftar Pesanan Pelanggan
        </div>

        @if($orders->count())

            <div class="so-table-wrap">

                <table class="so-table">

                    <thead>
                        <tr>
                            <th>Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Produk Terjual</th>
                            <th>Subtotal Toko</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orders as $order)

                            @php
                                $statusLabels = [
                                    'pending' => 'Menunggu',
                                    'processing' => 'Diproses',
                                    'shipped' => 'Dikirim',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                ];
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{ $order->order_number ?: '#' . $order->id }}
                                    </strong>

                                    <div class="so-small">
                                        {{ $order->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                <td>
                                    {{ $order->customer_name }}
                                </td>

                                <td>
                                    {{ $order->seller_quantity }} item
                                </td>

                                <td>
                                    <strong>
                                        Rp {{ number_format($order->seller_subtotal, 0, ',', '.') }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="so-status {{ $order->order_status }}">
                                        {{ $statusLabels[$order->order_status] ?? $order->order_status }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        class="so-btn"
                                        href="{{ route('seller.orders.show', $order) }}"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>

                </table>

            </div>

            <div class="so-pagination">
                {{ $orders->links() }}
            </div>

        @else

            <div class="so-empty">
                Belum ada pesanan untuk produk toko Anda.
            </div>

        @endif

    </div>

 
<div class="so-footer">

    <a
        class="so-btn"
        href="{{ route('seller.dashboard') }}"
    >
        ← Kembali ke Toko Saya
    </a>

    <a
        class="so-btn"
        href="{{ route('home') }}"
    >
        ← Kembali ke Beranda
    </a>

</div>


</div>

@endsection
