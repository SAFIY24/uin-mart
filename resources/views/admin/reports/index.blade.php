
@extends('layouts.admin')

@section('title', 'Laporan · UIN Mart')

@section('admin_content')

<style>
.lr-page { width:100%; max-width:1400px; margin:0 auto; }
.lr-heading { margin-bottom:24px; }
.lr-heading h1 { margin:6px 0; color:#800e19; font-size:27px; }
.lr-heading p { margin:0; color:#777; font-size:13px; }
.lr-eyebrow { color:#a16d0b; font-size:11px; font-weight:800; letter-spacing:1px; }

.lr-filter {
    display:flex; flex-wrap:wrap; align-items:end; gap:12px;
    padding:20px; background:#fff; border:1px solid #e9e1e3;
    border-radius:13px; margin-bottom:24px;
}
.lr-field { display:flex; flex-direction:column; gap:7px; }
.lr-field label { font-size:12px; color:#666; font-weight:700; }
.lr-field input {
    padding:11px; border:1px solid #e5dadd;
    border-radius:8px; font:inherit;
}
.lr-btn {
    padding:11px 18px; background:#800e19; color:#fff;
    border:0; border-radius:8px; font-weight:700; cursor:pointer;
}
.lr-reset { color:#800e19; font-weight:700; text-decoration:none; padding:11px; }

.lr-stats {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:16px; margin-bottom:24px;
}
.lr-card {
    background:white; border:1px solid #e9e1e3;
    border-radius:13px; padding:22px; min-width:0;
}
.lr-card span { display:block; font-size:12px; color:#777; }
.lr-card strong {
    display:block; margin-top:12px;
    font-size:25px; color:#800e19; overflow-wrap:anywhere;
}
.lr-card small { color:#999; font-size:11px; }

.lr-panel {
    background:#fff; border:1px solid #e9e1e3;
    border-radius:13px; padding:24px;
}
.lr-panel h2 { margin:0 0 7px; font-size:20px; color:#800e19; }
.lr-panel p { margin:0 0 22px; color:#777; font-size:13px; }

.lr-chart {
    display:flex; align-items:end; gap:12px;
    height:260px; padding:18px 4px 28px;
    border-bottom:1px solid #e9e1e3;
}
.lr-chart-col {
    flex:1; height:100%; min-width:0;
    display:flex; align-items:end; position:relative;
}
.lr-bar {
    width:100%; background:#800e19;
    border-radius:6px 6px 0 0;
    min-height:3px;
}
.lr-chart-label {
    position:absolute; bottom:-25px; left:0;
    width:100%; text-align:center; font-size:11px; color:#777;
}
.lr-chart-value {
    position:absolute; bottom:calc(var(--h) + 6px);
    width:100%; text-align:center; font-size:11px; color:#800e19;
    font-weight:700;
}
.lr-chart-note {
    color:#777; font-size:12px; margin-top:42px;
}

@media(max-width:1000px) {
    .lr-stats { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media(max-width:600px) {
    .lr-stats { grid-template-columns:1fr; }
    .lr-chart { gap:5px; }
    .lr-chart-label { font-size:9px; }
}
</style>

<div class="lr-page">

    <div class="lr-heading">
        <span class="lr-eyebrow">ADMIN / LAPORAN</span>
        <h1>Laporan UIN Mart</h1>
        <p>Ringkasan penjualan, transaksi, dan aktivitas marketplace.</p>
    </div>

    <form class="lr-filter" method="GET" action="{{ route('admin.reports.index') }}">
        <div class="lr-field">
            <label for="start_date">Dari Tanggal</label>
            <input
                id="start_date"
                type="date"
                name="start_date"
                value="{{ $startDate }}"
            >
        </div>

        <div class="lr-field">
            <label for="end_date">Sampai Tanggal</label>
            <input
                id="end_date"
                type="date"
                name="end_date"
                value="{{ $endDate }}"
            >
        </div>

        <button type="submit" class="lr-btn">Tampilkan</button>
        <a class="lr-reset" href="{{ route('admin.reports.index') }}">Reset</a>
    </form>

    <div class="lr-stats">
        <div class="lr-card">
            <span>Total Pesanan</span>
            <strong>{{ number_format($totalOrders, 0, ',', '.') }}</strong>
            <small>Sesuai filter tanggal</small>
        </div>

        <div class="lr-card">
            <span>Total Pendapatan</span>
            <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
            <small>Pesanan dengan pembayaran paid</small>
        </div>

        <div class="lr-card">
            <span>Produk Terjual</span>
            <strong>{{ number_format($productsSold, 0, ',', '.') }}</strong>
            <small>Jumlah unit dari pesanan paid</small>
        </div>

        <div class="lr-card">
            <span>Penjual Disetujui</span>
            <strong>{{ number_format($totalSellers, 0, ',', '.') }}</strong>
            <small>Jumlah saat ini</small>
        </div>

        <div class="lr-card">
            <span>Total Produk</span>
            <strong>{{ number_format($totalProducts, 0, ',', '.') }}</strong>
            <small>Jumlah saat ini</small>
        </div>
    </div>

    <section class="lr-panel">
        <h2>Grafik Pesanan Bulanan</h2>
        <p>Jumlah pesanan yang dibuat selama 12 bulan terakhir.</p>

        @php
            $monthlyMap = $monthlyOrders->keyBy(
                fn ($item) => $item->tahun . '-' . str_pad($item->bulan, 2, '0', STR_PAD_LEFT)
            );

            $months = collect(range(11, 0))->map(function ($offset) use ($monthlyMap) {
                $date = now()->startOfMonth()->subMonths($offset);
                $key = $date->format('Y-m');

                return [
                    'label' => $date->format('M'),
                    'value' => (int) ($monthlyMap->get($key)?->jumlah ?? 0),
                ];
            });

            $maxOrders = max(1, (int) $months->max('value'));
        @endphp

        <div class="lr-chart">
            @foreach($months as $month)
                @php
                    $height = round(($month['value'] / $maxOrders) * 100, 2);
                @endphp

                <div class="lr-chart-col">
                    <div
                        class="lr-bar"
                        style="height:{{ $height }}%;"
                        title="{{ $month['value'] }} pesanan"
                    ></div>

                    <span class="lr-chart-value" style="--h:{{ $height }}%;">
                        {{ $month['value'] }}
                    </span>

                    <span class="lr-chart-label">{{ $month['label'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="lr-chart-note">
            Catatan: Grafik menampilkan semua pesanan, termasuk yang belum dibayar.
            Filter tanggal hanya berlaku untuk statistik pesanan dan penjualan
            di bagian atas.
        </div>
    </section>

</div>

@endsection
