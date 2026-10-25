
@extends('layouts.admin')

@section('title', 'Manajemen Pesanan · UIN Mart')

@section('admin_content')

@php
    $statusLabels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    $paymentLabels = [
        'pending' => 'Belum Dibayar',
        'paid' => 'Lunas',
        'failed' => 'Gagal',
    ];
@endphp

<div class="ua-orders-page">

    <!-- TIÊU ĐỀ -->
    <div class="ua-main-top">
        <div>
            <p class="ua-eyebrow">ADMIN / PESANAN</p>

            <h1>Manajemen Pesanan</h1>

            <p class="ua-subtitle">
                Kelola status pembayaran dan pengiriman pesanan UIN Mart.
            </p>
        </div>

        <div class="ua-top-actions">
            <a href="{{ route('admin.index') }}"
               class="ua-btn ua-btn-light">
                ← Dashboard
            </a>
        </div>
    </div>

    <!-- THỐNG KÊ ĐƠN TRÊN TRANG HIỆN TẠI -->
    <section class="ua-order-stats">

        <div class="ua-panel ua-order-stat">
            <span>Pesanan di Halaman Ini</span>
            <strong>{{ $orders->count() }}</strong>
            <small>Daftar pesanan yang ditampilkan</small>
        </div>

        <div class="ua-panel ua-order-stat">
            <span>Menunggu Diproses</span>
            <strong>
                {{ $orders->getCollection()->where('order_status', 'pending')->count() }}
            </strong>
            <small>Hanya halaman saat ini</small>
        </div>

        <div class="ua-panel ua-order-stat">
            <span>Dalam Proses</span>
            <strong>
                {{ $orders->getCollection()->where('order_status', 'processing')->count() }}
            </strong>
            <small>Hanya halaman saat ini</small>
        </div>

        <div class="ua-panel ua-order-stat">
            <span>Selesai</span>
            <strong>
                {{ $orders->getCollection()->where('order_status', 'completed')->count() }}
            </strong>
            <small>Hanya halaman saat ini</small>
        </div>

    </section>

    <!-- DANH SÁCH ĐƠN HÀNG -->
    <section class="ua-panel ua-orders-panel">

        <div class="ua-panel-title">
            <div>
                <h2>Daftar Pesanan</h2>
                <p>
                    Total {{ $orders->total() }} pesanan terdaftar.
                    Menampilkan {{ $orders->count() }} pesanan pada halaman ini.
                </p>
            </div>
        </div>

        <div class="ua-table-scroll">
            <table class="ua-table ua-orders-table">

                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($orders as $order)

                        <tr>

                            <!-- MÃ ĐƠN -->
                            <td>
                                <strong>{{ $order->order_number }}</strong>

                                <a href="{{ route('admin.orders.show', $order) }}"
   style="display:inline-block;margin-top:8px;color:#800e19;font-weight:700;font-size:12px;">
    Lihat Detail →
</a>

                                <small>
                                    {{ $order->created_at?->format('d/m/Y H:i') }}
                                </small>
                            </td>

                            <!-- KHÁCH HÀNG -->
                            <td>
                                <strong>{{ $order->customer_name }}</strong>
                                <small>{{ $order->phone }}</small>
                            </td>

                            <!-- SẢN PHẨM TRONG ĐƠN -->
                            <td>
                                <details class="ua-order-details">
                                    <summary>
                                        {{ $order->items->count() }} produk
                                    </summary>

                                    <div class="ua-order-items">
                                        @forelse($order->items as $item)
                                            <div class="ua-order-item">
                                                <span>
                                                    {{ $item->product_name }}
                                                    × {{ $item->quantity }}
                                                </span>

                                                <strong>
                                                    Rp {{ number_format(
                                                        $item->subtotal,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}
                                                </strong>
                                            </div>
                                        @empty
                                            <p>Tidak ada detail produk.</p>
                                        @endforelse
                                    </div>
                                </details>
                            </td>

                            <!-- TỔNG TIỀN -->
                            <td>
                                <strong class="ua-order-total">
                                    Rp {{ number_format(
                                        $order->total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>
                            </td>

                            <!-- FORM CẬP NHẬT -->
                            <td colspan="3">

                                <form
                                    method="POST"
                                    action="{{ route('admin.orders.update', $order) }}"
                                    class="ua-order-update-form"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <div class="ua-order-control">

                                        <label for="payment-{{ $order->id }}">
                                            Pembayaran
                                        </label>

                                        <select
                                            id="payment-{{ $order->id }}"
                                            name="payment_status"
                                            aria-label="Status pembayaran {{ $order->order_number }}"
                                            required
                                        >
                                            @foreach($paymentLabels as $value => $label)
                                                <option
                                                    value="{{ $value }}"
                                                    @selected($order->payment_status === $value)
                                                >
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>

                                    <div class="ua-order-control">

                                        <label for="status-{{ $order->id }}">
                                            Status Pesanan
                                        </label>

                                        <select
                                            id="status-{{ $order->id }}"
                                            name="order_status"
                                            aria-label="Status pesanan {{ $order->order_number }}"
                                            required
                                        >
                                            @foreach($statusLabels as $value => $label)
                                                <option
                                                    value="{{ $value }}"
                                                    @selected($order->order_status === $value)
                                                >
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>

                                    <button
                                        type="submit"
                                        class="ua-btn ua-btn-primary ua-order-save"
                                    >
                                        Simpan
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="ua-empty">
                                Belum ada pesanan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="ua-pagination">
            {{ $orders->links() }}
        </div>

    </section>

</div>

@endsection
