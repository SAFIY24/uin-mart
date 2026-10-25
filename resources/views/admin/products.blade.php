
@extends('layouts.admin')

@section('title', 'Manajemen Produk · UIN Mart')

@section('admin_content')

@php
    $totalProducts = $stats['products'] ?? 0;
    $activeProducts = $stats['active'] ?? 0;
    $lowStock = $stats['low'] ?? 0;

    $pricing = app(\App\Services\ProductPricing::class);
@endphp

<div class="ua-main-top">
    <div>
        <p class="ua-eyebrow">ADMIN / PRODUK</p>
        <h1>Manajemen Produk</h1>
        <p class="ua-subtitle">
            Kelola katalog, harga, diskon, stok,
            dan status produk UIN Mart.
        </p>
    </div>

    <div class="ua-top-actions">
        <a
            class="ua-btn ua-btn-light"
            href="{{ route('admin.promotions.index') }}"
        >
            % Atur Diskon
        </a>

        <a
            class="ua-btn ua-btn-primary"
            href="{{ route('admin.products.create') }}"
        >
            + Tambah Produk
        </a>
    </div>
</div>

<section class="ua-stat-grid">
    <article class="ua-stat">
        <span class="ua-stat-icon">▣</span>
        <span class="ua-stat-label">Total Produk</span>
        <strong>{{ $totalProducts }}</strong>
        <small>Produk terdaftar</small>
    </article>

    <article class="ua-stat">
        <span class="ua-stat-icon">✓</span>
        <span class="ua-stat-label">Produk Aktif</span>
        <strong>{{ $activeProducts }}</strong>
        <small>Ditampilkan di toko</small>
    </article>

    <article class="ua-stat">
        <span class="ua-stat-icon">!</span>
        <span class="ua-stat-label">Stok Rendah</span>
        <strong>{{ $lowStock }}</strong>
        <small>Stok kurang dari 5</small>
    </article>
</section>

<section class="ua-panel ua-products-panel">

    <div class="ua-panel-title ua-products-title">
        <div>
            <h2>Daftar Produk</h2>
            <p>
                Pantau harga normal dan diskon aktif
                secara langsung.
            </p>
        </div>

        <a
            href="{{ route('admin.promotions.index') }}"
            class="ua-btn ua-btn-light"
        >
            Kelola Diskon →
        </a>
    </div>

    <form
        method="GET"
        action="{{ route('admin.products.index') }}"
        class="ua-search-form"
    >
        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari nama produk..."
            aria-label="Cari produk"
        >

        <button type="submit">Cari Produk</button>

        @if(request()->filled('q'))
            <a href="{{ route('admin.products.index') }}">
                Reset
            </a>
        @endif
    </form>

    <div class="ua-table-scroll">
        <table class="ua-table ua-discount-products-table">
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
                        $normalPrice = (float) $pricing->originalPrice($product);

                        $discountPercent = (int) $pricing->discountPercentage($product);

                        $finalPrice = (float) $pricing->finalPrice($product);

                        $hasDiscount = $discountPercent > 0;
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
                            <span class="{{ $hasDiscount ? 'ua-price-old' : 'ua-price-normal' }}">
                                Rp {{ number_format($normalPrice, 0, ',', '.') }}
                            </span>
                        </td>

                        <td>
                            @if($hasDiscount)
                                <span class="ua-discount-tag">
                                    -{{ $discountPercent }}%
                                </span>
                            @else
                                <span class="ua-no-discount">
                                    Tidak ada
                                </span>
                            @endif
                        </td>

                        <td>
                            <strong class="{{ $hasDiscount ? 'ua-price-final' : 'ua-price-normal' }}">
                                Rp {{ number_format($finalPrice, 0, ',', '.') }}
                            </strong>

                            @if($hasDiscount)
                                <small class="ua-discount-active">
                                    Diskon aktif
                                </small>
                            @endif
                        </td>

                        <td>
                            <span class="{{ $product->stock < 5 ? 'ua-stock-low' : '' }}">
                                {{ $product->stock }}
                            </span>
                        </td>

                        <td>
                            <span class="ua-status {{ $product->status === 'active' ? 'ua-status-active' : 'ua-status-inactive' }}">
                                {{ $product->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>

                        <td>
 
<div class="ua-action-links">

    <!-- EDIT PRODUK -->
    <a href="{{ route('admin.products.edit', $product) }}">
        Edit
    </a>

    <!-- NONAKTIFKAN PRODUK -->
    @if($product->status === 'active')
        <form
            method="POST"
            action="{{ route('admin.products.destroy', $product) }}"
            onsubmit="return confirm('Nonaktifkan produk ini?')"
        >
            @csrf
            @method('DELETE')

            <button type="submit">
                Nonaktifkan
            </button>
        </form>
    @endif

    <!-- HAPUS PRODUK ADMIN -->
    @if($product->seller_id === null)
        <form
            method="POST"
            action="{{ route('admin.products.force-destroy', $product) }}"
            onsubmit="return confirm('Yakin ingin menghapus produk ini secara permanen? Tindakan ini tidak dapat dibatalkan.')"
        >
            @csrf
            @method('DELETE')

            <button
                type="submit"
                style="color:#b42936;font-weight:700;"
            >
                Hapus
            </button>
        </form>
    @endif

</div>

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
/* ======================================
   UIN MART - DISKON DI DAFTAR PRODUK
   ====================================== */

.ua-discount-products-table {
    width: 100%;
    min-width: 1080px;
    border-collapse: collapse;
}

.ua-discount-products-table th {
    white-space: nowrap;
}

.ua-discount-products-table td {
    vertical-align: middle;
}

.ua-price-old {
    color: #999096;
    text-decoration: line-through;
    font-size: 12px;
    white-space: nowrap;
}

.ua-price-normal {
    color: #382a2e;
    font-weight: 700;
    white-space: nowrap;
}

.ua-price-final {
    color: #800e19;
    font-size: 15px;
    font-weight: 800;
    white-space: nowrap;
}

.ua-discount-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 6px 10px;
    border-radius: 7px;

    background: #800e19;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.ua-no-discount {
    color: #a49a9e;
    font-size: 12px;
}

.ua-discount-products-table .ua-discount-active {
    display: block;
    margin-top: 5px;
    color: #168353;
    font-size: 11px;
    font-weight: 650;
}

.ua-discount-products-table .ua-action-links {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.ua-discount-products-table .ua-action-links form {
    margin: 0;
}

@media (max-width: 900px) {
    .ua-discount-products-table {
        min-width: 1050px;
    }
}
</style>

@endsection
