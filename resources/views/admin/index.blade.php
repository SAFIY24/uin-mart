
@extends('layouts.admin')

@section('title', 'Dashboard Admin · UIN Mart')

@section('admin_content')

@php
    $pricing = app(\App\Services\ProductPricing::class);

    $total = $stats['products'] ?? 0;
    $active = $stats['active'] ?? 0;
    $low = $stats['low'] ?? 0;
    $orders = $stats['orders'] ?? 0;
    $discounted = $stats['discounted'] ?? 0;
    $vouchers = $stats['vouchers'] ?? 0;
    $activeVouchers = $stats['active_vouchers'] ?? 0;
@endphp

<div class="ua-main-top">
    <div>
        <p class="ua-eyebrow">OVERVIEW / ADMIN</p>
        <h1>Dashboard</h1>
        <p class="ua-subtitle">Ringkasan aktivitas UIN Mart</p>
    </div>

    <div class="ua-top-actions">
        <a class="ua-btn ua-btn-light"
           href="{{ route('admin.orders') }}">
            Lihat Pesanan
        </a>

        <a class="ua-btn ua-btn-primary"
           href="{{ route('admin.products.create') }}">
            + Tambah Produk
        </a>
    </div>
</div>

<!-- STATISTIK UTAMA -->
<section class="ua-stat-grid">

    <article class="ua-stat">
        <span class="ua-stat-icon">▣</span>
        <span class="ua-stat-label">Total Produk</span>
        <strong>{{ $total }}</strong>
        <small>Semua produk terdaftar</small>
    </article>

    <article class="ua-stat">
        <span class="ua-stat-icon">✓</span>
        <span class="ua-stat-label">Produk Aktif</span>
        <strong>{{ $active }}</strong>
        <small>Siap ditampilkan di toko</small>
    </article>

    <article class="ua-stat">
        <span class="ua-stat-icon">%</span>
        <span class="ua-stat-label">Produk Diskon</span>
        <strong>{{ $discounted }}</strong>
        <small>Diskon sedang berlaku</small>
    </article>

    <article class="ua-stat">
        <span class="ua-stat-icon">▤</span>
        <span class="ua-stat-label">Total Pesanan</span>
        <strong>{{ $orders }}</strong>
        <small>Semua status pesanan</small>
    </article>

</section>

<!-- DASHBOARD OPERASIONAL -->
<div class="ua-dash-panels">

    <!-- RINGKASAN TOKO -->
    <section class="ua-panel ua-dash-card">

        <div class="ua-dash-heading">
            <div>
                <h2>Ringkasan Toko</h2>
                <p>Pantau kondisi stok produk</p>
            </div>
            <span class="ua-dash-label">Live Data</span>
        </div>

        <div class="ua-dash-mini-stats">
            <div class="ua-dash-mini">
                <span>Produk Aktif</span>
                <strong>{{ $active }}</strong>
            </div>

            <div class="ua-dash-mini">
                <span>Stok Rendah (&lt;5)</span>
                <strong>{{ $low }}</strong>
            </div>
        </div>

        <div class="ua-dash-subheading">
            <h3>Perlu Perhatian</h3>
            <span>Stok kurang dari 5</span>
        </div>

        @forelse($lowStockProducts as $product)
            <div class="ua-dash-list-row">
                <div>
                    <strong>{{ $product->name }}</strong>
                    <small>Stok tersisa: {{ $product->stock }}</small>
                </div>

                <a href="{{ route('admin.products.edit', $product) }}">
                    Atur Stok →
                </a>
            </div>
        @empty
            <div class="ua-dash-ok">
                ✓ Semua produk aktif memiliki stok yang cukup.
            </div>
        @endforelse

        <a href="{{ route('admin.products.index') }}"
           class="ua-dash-link">
            Lihat Semua Produk →
        </a>

    </section>

    <!-- DISKON & PROMOSI -->
    <section class="ua-panel ua-dash-card">

        <div class="ua-dash-heading">
            <div>
                <h2>Diskon & Promosi</h2>
                <p>Kelola promosi toko dari satu tempat</p>
            </div>

            <span class="ua-dash-label">Live Data</span>
        </div>

        <div class="ua-dash-mini-stats">
            <div class="ua-dash-mini">
                <span>Diskon Produk Aktif</span>
                <strong>{{ $discounted }}</strong>
            </div>

            <div class="ua-dash-mini">
                <span>Voucher Aktif</span>
                <strong>{{ $activeVouchers }}</strong>
                <small>{{ $vouchers }} voucher terdaftar</small>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="ua-dash-actions">

            <a href="{{ route('admin.promotions.index') }}"
               class="ua-dash-action">
                <span class="ua-dash-action-icon">%</span>
                <strong>Diskon Produk</strong>
                <small>Atur diskon dan periode</small>
                <span class="ua-dash-action-link">Kelola Diskon →</span>
            </a>

            <a href="{{ route('admin.vouchers.index') }}"
               class="ua-dash-action">
                <span class="ua-dash-action-icon">▤</span>
                <strong>Voucher</strong>
                <small>Tambah, edit, dan hapus voucher</small>
                <span class="ua-dash-action-link">Kelola Voucher →</span>
            </a>

        </div>

        <!-- ACTIVE VOUCHERS -->
        <div class="ua-dash-subheading">
            <h3>Voucher Sedang Aktif</h3>
        </div>

        @forelse($activeVoucherCodes as $voucher)
            <div class="ua-dash-list-row">
                <div>
                    <strong>{{ $voucher->code }}</strong>
                    <small>
                        {{ $voucher->type === 'percentage'
                            ? number_format((float) $voucher->value, 0, ',', '.') . '%'
                            : 'Rp ' . number_format((float) $voucher->value, 0, ',', '.') }}
                    </small>
                </div>

                <a href="{{ route('admin.vouchers.edit', $voucher) }}">
                    Edit →
                </a>
            </div>
        @empty
            <p class="ua-dash-empty">
                Belum ada voucher yang sedang berlaku.
            </p>
        @endforelse

    </section>
</div>

<!-- PROMOSI MENDEKATI AKHIR -->
@if($upcomingPromos->isNotEmpty())
    <section class="ua-panel ua-dash-expiring">
        <div class="ua-dash-heading">
            <div>
                <h2>Promosi Segera Berakhir</h2>
                <p>Program yang berakhir dalam 7 hari ke depan</p>
            </div>
        </div>

        @foreach($upcomingPromos as $promo)
            <div class="ua-dash-list-row">
                <div>
                    <strong>{{ $promo['name'] }}</strong>
                    <small>
                        {{ $promo['type'] }} ·
                        Berakhir {{ $promo['ends_at']->format('d/m/Y H:i') }}
                    </small>
                </div>

                <a href="{{ $promo['url'] }}">
                    Kelola →
                </a>
            </div>
        @endforeach
    </section>
@endif

<!-- MANAJEMEN PRODUK -->
<section class="ua-panel ua-products-panel" id="daftar-produk">

    <div class="ua-panel-title ua-products-title">
        <div>
            <h2>Manajemen Produk</h2>
            <p>Harga normal, diskon, stok, dan status produk</p>
        </div>

        <a class="ua-btn ua-btn-primary ua-btn-small"
           href="{{ route('admin.products.create') }}">
            + Tambah Produk
        </a>
    </div>

    <form method="GET"
          action="{{ route('admin.index') }}"
          class="ua-search-form">

        <input type="search"
               name="q"
               value="{{ request('q') }}"
               placeholder="Cari nama produk...">

        <button type="submit">Cari Produk</button>
    </form>

    <div class="ua-table-scroll">
        <table class="ua-table ua-dashboard-product-table">

            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga Normal</th>
                    <th>Diskon</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($products as $product)

                    @php
                        $normal = (float) $pricing->originalPrice($product);
                        $percent = (int) $pricing->discountPercentage($product);
                        $final = (float) $pricing->finalPrice($product);
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            <small>#{{ $product->id }}</small>
                        </td>

                        <td>
                            {{ $product->category->name ?? 'Tanpa kategori' }}
                        </td>

                        <td>
                            <span class="{{ $percent > 0 ? 'ua-dash-old-price' : '' }}">
                                Rp {{ number_format($normal, 0, ',', '.') }}
                            </span>
                        </td>

                        <td>
                            @if($percent > 0)
                                <span class="ua-dash-discount">
                                    -{{ $percent }}%
                                </span>
                            @else
                                <span class="ua-dash-muted">—</span>
                            @endif
                        </td>

                        <td>
                            <strong class="ua-dash-final-price">
                                Rp {{ number_format($final, 0, ',', '.') }}
                            </strong>
                        </td>

                        <td>{{ $product->stock }}</td>

                        <td>
                            <span class="ua-status {{ $product->status === 'active' ? 'ua-status-active' : 'ua-status-inactive' }}">
                                {{ $product->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>

                        <td>
                            <a class="ua-dash-edit"
                               href="{{ route('admin.products.edit', $product) }}">
                                Edit
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="ua-empty">
                            Tidak ada produk yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ua-pagination">
        {{ $products->links() }}
    </div>

</section>

<style>
.ua-dash-panels {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin: 22px 0;
}

.ua-dash-card {
    padding: 23px;
    min-width: 0;
}

.ua-dash-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.ua-dash-heading h2 {
    margin: 0 0 6px;
    font-size: 19px;
    color: #30151b;
}

.ua-dash-heading p {
    margin: 0;
    font-size: 12px;
    color: #938489;
}

.ua-dash-label {
    background: #e6f6ec;
    color: #087746;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 750;
    white-space: nowrap;
}

.ua-dash-mini-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.ua-dash-mini {
    background: #faf7f8;
    border: 1px solid #f0e7e9;
    border-radius: 11px;
    padding: 14px;
}

.ua-dash-mini > span {
    display: block;
    font-size: 11px;
    color: #87777c;
}

.ua-dash-mini > strong {
    display: block;
    margin-top: 5px;
    font-size: 24px;
    color: #800e19;
}

.ua-dash-mini > small {
    font-size: 10px;
    color: #938489;
}

.ua-dash-subheading {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin: 20px 0 10px;
}

.ua-dash-subheading h3 {
    margin: 0;
    font-size: 13px;
}

.ua-dash-subheading span {
    color: #998d90;
    font-size: 11px;
}

.ua-dash-list-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f0e9ea;
}

.ua-dash-list-row strong {
    display: block;
    font-size: 12px;
}

.ua-dash-list-row small {
    display: block;
    margin-top: 3px;
    font-size: 11px;
    color: #90858a;
}

.ua-dash-list-row a,
.ua-dash-link,
.ua-dash-edit {
    color: #800e19;
    font-size: 12px;
    font-weight: 750;
    text-decoration: none;
    white-space: nowrap;
}

.ua-dash-link {
    display: inline-block;
    margin-top: 18px;
}

.ua-dash-ok {
    background: #e9f7ee;
    color: #12744b;
    padding: 15px;
    border-radius: 9px;
    font-size: 12px;
    line-height: 1.6;
}

.ua-dash-empty {
    font-size: 12px;
    color: #90858a;
}

.ua-dash-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.ua-dash-action {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 16px;
    border: 1px solid #eadfe1;
    border-radius: 11px;
    color: #34282c;
    text-decoration: none;
    transition: border-color .2s, background .2s;
}

.ua-dash-action:hover {
    border-color: #800e19;
    background: #fff8f8;
}

.ua-dash-action-icon {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    background: #fff0f0;
    color: #800e19;
    border-radius: 9px;
    font-weight: 850;
    font-size: 20px;
}

.ua-dash-action strong {
    font-size: 13px;
}

.ua-dash-action small {
    font-size: 11px;
    color: #887c81;
    line-height: 1.5;
}

.ua-dash-action-link {
    margin-top: auto;
    color: #800e19;
    font-size: 11px;
    font-weight: 750;
}

.ua-dash-expiring {
    padding: 22px;
    margin-bottom: 22px;
}

.ua-dashboard-product-table {
    min-width: 1000px;
}

.ua-dashboard-product-table .ua-dash-old-price {
    color: #989095;
    text-decoration: line-through;
    font-size: 12px;
}

.ua-dashboard-product-table .ua-dash-discount {
    display: inline-block;
    padding: 4px 9px;
    background: #800e19;
    color: #fff;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.ua-dashboard-product-table .ua-dash-final-price {
    font-size: 13px;
    font-weight: 700;
    color: #800e19;
}

.ua-dashboard-product-table .ua-dash-muted {
    color: #a59ba0;
}

@media (max-width: 1000px) {
    .ua-dash-panels {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .ua-dash-card {
        padding: 16px;
    }

    .ua-dash-actions {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection
