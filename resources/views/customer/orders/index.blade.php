
@extends('layouts.app')

@section('content')

<style>
.my-orders {
    max-width: 1150px;
    width: calc(100% - 32px);
    margin: 35px auto 65px;
}

.my-orders-header {
    background: #800e19;
    color: white;
    padding: 25px 30px;
    border-radius: 14px;
    margin-bottom: 22px;
}

.my-orders-header h1 {
    color: #ffffff !important;
    margin: 0 0 8px;
    font-size: 27px;
}

.my-orders-header p {
    margin: 0;
    color: #f7e2e4;
}

.my-orders-panel {
    background: white;
    border: 1px solid #e9e1e3;
    border-radius: 13px;
    padding: 24px;
    overflow-x: auto;
}

.my-orders-table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
    text-align: left;
}

.my-orders-table th,
.my-orders-table td {
    padding: 14px;
    border-bottom: 1px solid #eee;
    font-size: 13px;
}

.my-orders-table th {
    color: #800e19;
    background: #faf5f6;
}

.my-orders-btn {
    display: inline-block;
    padding: 9px 15px;
    background: #800e19;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
}


.my-orders-back {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 20px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 9px;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 700;
    transition: background 0.2s ease;
}

.my-orders-back:hover {
    background: #650b14;
    color: #ffffff !important;
}

</style>

<div class="my-orders">

    <div class="my-orders-header">
        <h1>Pesanan Saya</h1>
        <p>Riwayat pembelian dan status pesanan Anda.</p>
    </div>

    <div class="my-orders-panel">

        @if($orders->count() > 0)

            <table class="my-orders-table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>

                            <td>
                                {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>

                            <td>
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </td>

                            <td>
                                {{ ucfirst($order->payment_status) }}
                            </td>

                            <td>
                                {{ ucfirst($order->order_status) }}
                            </td>

                            <td>
                                <a
                                    class="my-orders-btn"
                                    href="{{ route('customer.orders.show', $order->id) }}"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $orders->links() }}
            </div>

        @else

            <div style="text-align:center;padding:35px;color:#777;">
                <h3>Belum Ada Pesanan</h3>
                <p>Anda belum memiliki riwayat pembelian.</p>

                <a class="my-orders-btn" href="{{ route('home') }}">
                    Mulai Belanja
                </a>
            </div>

        @endif

    </div>

    <a class="my-orders-back" href="{{ route('home') }}">
        ← Kembali ke Beranda
    </a>

</div>

@endsection
