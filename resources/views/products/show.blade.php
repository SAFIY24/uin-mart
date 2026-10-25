
@extends('layouts.app')

@section('title', $product->name . ' · UIN Mart')

@section('content')


<style>
.um-product-thumbnails {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-top: 15px;
    margin-bottom: 12px;
}

.um-product-thumb {
    width: 100%;
    height: 100px;
    padding: 5px;
    background: #ffffff;
    border: 2px solid #e9e1e3;
    border-radius: 10px;
    cursor: pointer;
    overflow: hidden;
    transition: border-color .2s ease;
}

.um-product-thumb:hover,
.um-product-thumb.is-active {
    border-color: #800e19;
}

.um-product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 5px;
}

@media(max-width: 650px) {
    .um-product-thumbnails {
        gap: 7px;
    }

    .um-product-thumb {
        height: 75px;
    }
}
</style>


@php
    $imageUrl = $product->image
        ? (
            str_starts_with($product->image, 'products/')
                ? asset('storage/' . $product->image)
                : asset('uploads/products/' . basename($product->image))
        )
        : null;

    $available = $product->status === 'active'
        && (int) $product->stock > 0;

    $quantityMax = max(1, (int) $product->stock);

    $categoryName = $product->category?->name ?? 'Lainnya';

    $pricing = app(\App\Services\ProductPricing::class);

$originalPrice = $pricing->originalPrice($product);
$finalPrice = $pricing->finalPrice($product);
$discountPercentage = $pricing->discountPercentage($product);
$discountAmount = $pricing->discountAmount($product);

@endphp

<div class="um-detail-page">

    <!-- BREADCRUMB -->
    <nav class="um-detail-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span>›</span>
        <span>{{ $categoryName }}</span>
        <span>›</span>
        <strong>{{ $product->name }}</strong>
    </nav>

    <!-- MAIN PRODUCT -->
    <div class="um-detail-card">

        <!-- GAMBAR -->
        <section class="um-detail-gallery">
            <div class="um-detail-image">

                @if($imageUrl)
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $product->name }}"
                        id="umMainProductImage"
                    >
                @else
                    <div class="um-detail-no-image">
                        <span>▣</span>
                        <strong>UIN MART</strong>
                        <small>Foto produk belum tersedia</small>
                    </div>
                @endif

            </div>

            
@if($imageUrl || $product->images->isNotEmpty())

    <div class="um-product-thumbnails">

        {{-- Foto utama --}}
        @if($imageUrl)
            <button
                type="button"
                class="um-product-thumb is-active"
                data-image="{{ $imageUrl }}"
                aria-label="Lihat foto utama"
            >
                <img src="{{ $imageUrl }}" alt="Foto utama">
            </button>
        @endif

        {{-- Maksimal 3 foto tambahan --}}
        @foreach($product->images->take(3) as $extraImage)
            <button
                type="button"
                class="um-product-thumb"
                data-image="{{ asset('storage/' . $extraImage->image_path) }}"
                aria-label="Lihat foto tambahan {{ $loop->iteration }}"
            >
                <img
                    src="{{ asset('storage/' . $extraImage->image_path) }}"
                    alt="Foto tambahan {{ $loop->iteration }}"
                    loading="lazy"
                >
            </button>
        @endforeach

    </div>

@endif


            <div class="um-detail-gallery-note">
                <span>✓</span>
                Foto produk yang diunggah oleh admin UIN Mart.
            </div>
        </section>

        <!-- INFO PRODUK -->
        <section class="um-detail-info">

            <div class="um-detail-badges">
                <span class="um-detail-category">
                    {{ $categoryName }}
                </span>

                @if($available)
                    <span class="um-detail-available">
                        ● Tersedia
                    </span>
                @else
                    <span class="um-detail-unavailable">
                        ● Stok Habis
                    </span>
                @endif
            </div>

            <h1>{{ $product->name }}</h1>

            <p class="um-detail-code">
                Kode Produk: #{{ $product->id }}
            </p>


<div class="um-detail-price-box">
    <span>Harga Produk</span>

    @if($discountPercentage > 0)
        <div class="um-discount-row">
            <span class="um-original-price">
                Rp {{ number_format($originalPrice, 0, ',', '.') }}
            </span>

            <span class="um-discount-badge">
                Diskon {{ $discountPercentage }}%
            </span>
        </div>

        <strong>
            Rp {{ number_format($finalPrice, 0, ',', '.') }}
        </strong>

        <div class="um-saving-label">
            Hemat Rp {{ number_format($discountAmount, 0, ',', '.') }}
        </div>
    @else
        <strong>
            Rp {{ number_format($originalPrice, 0, ',', '.') }}
        </strong>
    @endif

    <small>
        Harga sesuai promo produk yang sedang berlaku.
        Voucher checkout dihitung secara terpisah.
    </small>
</div>


            <!-- DESKRIPSI SINGKAT -->
            <div class="um-detail-intro">
                <h2>Tentang Produk</h2>

                <p>
                    {{ $product->description
                        ?: 'Informasi deskripsi produk belum ditambahkan oleh admin UIN Mart.' }}
                </p>
            </div>

            <!-- STOCK -->
            <div class="um-detail-stock-line">
                <span>Ketersediaan</span>

                @if($available)
                    <strong class="um-detail-stock-ok">
                        {{ $product->stock }} produk tersedia
                    </strong>
                @else
                    <strong class="um-detail-stock-empty">
                        Stok tidak tersedia
                    </strong>
                @endif
            </div>

            <!-- PURCHASE -->
            <div class="um-detail-purchase">

                <h2>Pilih Jumlah</h2>

                <form
                    method="POST"
                    action="{{ route('cart.add') }}"
                    id="umDetailPurchaseForm"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="product_id"
                        value="{{ $product->id }}"
                    >

                    <div class="um-detail-quantity-row">

                        <div class="um-detail-quantity-control">
                            <button
                                type="button"
                                id="umQtyMinus"
                                aria-label="Kurangi jumlah"
                                @disabled(!$available)
                            >
                                −
                            </button>

                            <input
                                type="number"
                                id="umQtyInput"
                                name="quantity"
                                value="1"
                                min="1"
                                max="{{ $quantityMax }}"
                                step="1"
                                aria-label="Jumlah produk"
                                required
                                @disabled(!$available)
                            >

                            <button
                                type="button"
                                id="umQtyPlus"
                                aria-label="Tambah jumlah"
                                @disabled(!$available)
                            >
                                +
                            </button>
                        </div>

                        <span>
                            Maksimal {{ $product->stock }} produk
                        </span>
                    </div>

                    <div class="um-detail-buttons">

                        <button
                            type="submit"
                            class="um-detail-add-cart"
                            @disabled(!$available)
                        >
                            <span>🛒</span>
                            Tambah ke Keranjang
                        </button>

                        <button
                            type="submit"
                            formaction="{{ route('checkout.buy') }}"
                            class="um-detail-buy-now"
                            @disabled(!$available)
                        >
                            Beli Sekarang →
                        </button>

                    </div>
                </form>

                <p class="um-detail-purchase-note">
                    Jumlah yang dipilih digunakan untuk
                    Tambah ke Keranjang maupun Beli Sekarang.
                </p>

            </div>

        </section>
    </div>

    <!-- DETAIL TAMBAHAN -->
    <div class="um-detail-bottom">

        <section class="um-detail-description-card">
            <div class="um-detail-section-heading">
                <h2>Deskripsi Produk</h2>
                <span>Informasi Lengkap</span>
            </div>

            <div class="um-detail-description-text">
                @if(filled($product->description))
                    {{ $product->description }}
                @else
                    <p>
                        Deskripsi lengkap untuk produk ini
                        belum tersedia.
                    </p>
                    <p>
                        Silakan lihat informasi produk atau
                        hubungi pengelola UIN Mart untuk
                        informasi lebih lanjut.
                    </p>
                @endif
            </div>
        </section>

        <aside class="um-detail-spec-card">
            <h2>Informasi Produk</h2>

            <dl>
                <div>
                    <dt>Nama Produk</dt>
                    <dd>{{ $product->name }}</dd>
                </div>

                <div>
                    <dt>Kategori</dt>
                    <dd>{{ $categoryName }}</dd>
                </div>

                <div>
                    <dt>Kode Produk</dt>
                    <dd>#{{ $product->id }}</dd>
                </div>

                <div>
                    <dt>Harga</dt>
                    <dd>
                        Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                    </dd>
                </div>

                <div>
                    <dt>Stok</dt>
                    <dd>{{ $product->stock }}</dd>
                </div>

                <div>
                    <dt>Status</dt>
                    <dd>
                        {{ $available ? 'Tersedia' : 'Tidak Tersedia' }}
                    </dd>
                </div>
            </dl>
        </aside>
    </div>

    <!-- INFORMASI PEMBELIAN -->
    <section class="um-detail-help">
        <div class="um-detail-help-item">
            <span>✓</span>
            <div>
                <strong>Informasi Produk</strong>
                <p>
                    Data produk dikelola melalui Admin UIN Mart.
                </p>
            </div>
        </div>

        <div class="um-detail-help-item">
            <span>▣</span>
            <div>
                <strong>Proses Pemesanan</strong>
                <p>
                    Pilih produk, masukkan jumlah,
                    lalu lanjutkan ke checkout.
                </p>
            </div>
        </div>

        <div class="um-detail-help-item">
            <span>↗</span>
            <div>
                <strong>Lanjut Belanja</strong>
                <p>
                    Temukan produk lain untuk kebutuhan kuliah.
                </p>
            </div>
        </div>
    </section>

    <a href="{{ route('home') }}" class="um-detail-return">
        ← Kembali ke Beranda
    </a>

</div>

<style>
/* ============================================
   UIN MART - PRODUCT DETAIL V2
   ============================================ */

.mart-body .um-detail-page {
    width: min(1320px, calc(100% - 40px));
    margin: 25px auto 70px;
    color: #2c2227;
}

.mart-body .um-detail-page *,
.mart-body .um-detail-page *::before,
.mart-body .um-detail-page *::after {
    box-sizing: border-box;
}

.mart-body .um-detail-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 22px;
    color: #91838a;
    font-size: 12px;
}

.mart-body .um-detail-breadcrumb a {
    color: #800e19;
    text-decoration: none;
}

.mart-body .um-detail-breadcrumb strong {
    color: #43383e;
    font-weight: 650;
}

/* MAIN CARD */

.mart-body .um-detail-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    align-items: start;
    gap: 36px;
    padding: 32px;
    background: #fff;
    border: 1px solid #ede5e7;
    border-radius: 16px;
    box-shadow: 0 6px 24px rgba(50,20,28,.035);
}

.mart-body .um-detail-gallery,
.mart-body .um-detail-info {
    min-width: 0;
}

/* IMAGE */

.mart-body .um-detail-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 430px;
    overflow: hidden;
    border: 1px solid #eee8ea;
    border-radius: 13px;
    background: #f8f7f8;
}

.mart-body .um-detail-image img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.mart-body .um-detail-no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 9px;
    color: #aaa0a5;
}

.mart-body .um-detail-no-image > span {
    font-size: 50px;
}

.mart-body .um-detail-no-image strong {
    font-size: 18px;
}

.mart-body .um-detail-no-image small {
    font-size: 12px;
}

.mart-body .um-detail-gallery-note {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 13px;
    font-size: 11px;
    color: #8d8086;
}

.mart-body .um-detail-gallery-note span {
    color: #138455;
    font-weight: 800;
}

/* PRODUCT INFORMATION */

.mart-body .um-detail-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-bottom: 14px;
}

.mart-body .um-detail-category,
.mart-body .um-detail-available,
.mart-body .um-detail-unavailable {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 750;
}

.mart-body .um-detail-category {
    color: #800e19;
    background: #fff0f1;
}

.mart-body .um-detail-available {
    color: #138052;
    background: #e6f7ed;
}

.mart-body .um-detail-unavailable {
    color: #b83340;
    background: #fff0f1;
}

.mart-body .um-detail-info h1 {
    margin: 0 0 8px;
    color: #291c22;
    font-size: 31px;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.mart-body .um-detail-code {
    margin: 0 0 20px;
    color: #9c8f94;
    font-size: 12px;
}

/* PRICE */

.mart-body .um-detail-price-box {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 23px;
    padding: 20px;
    background: #fdf7f8;
    border: 1px solid #f5e7e9;
    border-radius: 12px;
}

.mart-body .um-detail-price-box > span {
    color: #897b80;
    font-size: 12px;
}

.mart-body .um-detail-price-box strong {
    color: #800e19;
    font-size: 31px;
    line-height: 1.25;
    font-weight: 850;
}

.mart-body .um-detail-price-box small {
    color: #9a8a90;
    font-size: 11px;
    line-height: 1.6;
}

/* DESCRIPTION */

.mart-body .um-detail-intro h2,
.mart-body .um-detail-purchase h2 {
    margin: 0 0 10px;
    font-size: 16px;
    color: #322329;
}

.mart-body .um-detail-intro p {
    margin: 0 0 20px;
    color: #746970;
    font-size: 13px;
    line-height: 1.8;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.mart-body .um-detail-stock-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 0;
    border-top: 1px solid #eee7e9;
    border-bottom: 1px solid #eee7e9;
    font-size: 13px;
}

.mart-body .um-detail-stock-line > span {
    color: #81757b;
}

.mart-body .um-detail-stock-ok {
    color: #138052;
}

.mart-body .um-detail-stock-empty {
    color: #c13645;
}

/* PURCHASE */

.mart-body .um-detail-purchase {
    padding-top: 22px;
}

.mart-body .um-detail-quantity-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.mart-body .um-detail-quantity-row > span {
    color: #94878c;
    font-size: 12px;
}

.mart-body .um-detail-quantity-control {
    display: inline-flex;
    align-items: center;
    border: 1px solid #e4dadd;
    border-radius: 9px;
    overflow: hidden;
    background: #fff;
}

.mart-body .um-detail-quantity-control button {
    display: grid;
    place-items: center;
    width: 42px;
    height: 44px;
    padding: 0;
    border: 0;
    background: #fff;
    color: #800e19;
    font-size: 22px;
    font-weight: 600;
    cursor: pointer;
}

.mart-body .um-detail-page .um-detail-quantity-control input {
    width: 65px;
    height: 44px;
    min-height: 44px;
    margin: 0;
    padding: 5px;
    border: 0;
    border-left: 1px solid #eee7e9;
    border-right: 1px solid #eee7e9;
    border-radius: 0;
    text-align: center;
    font-size: 14px;
    color: #33252c;
    box-shadow: none;
    appearance: textfield;
    -moz-appearance: textfield;
}

.mart-body .um-detail-quantity-control input::-webkit-inner-spin-button,
.mart-body .um-detail-quantity-control input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.mart-body .um-detail-quantity-control button:disabled {
    color: #bbb1b5;
    cursor: not-allowed;
}

.mart-body .um-detail-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.mart-body .um-detail-add-cart,
.mart-body .um-detail-buy-now {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 49px;
    padding: 12px 15px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: background .2s;
}

.mart-body .um-detail-add-cart {
    border: 1px solid #800e19;
    color: #800e19;
    background: #fff;
}

.mart-body .um-detail-buy-now {
    border: 1px solid #800e19;
    color: #fff;
    background: #800e19;
}

.mart-body .um-detail-add-cart:hover:not(:disabled) {
    background: #fff1f2;
}

.mart-body .um-detail-buy-now:hover:not(:disabled) {
    background: #650b14;
}

.mart-body .um-detail-buttons button:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.mart-body .um-detail-purchase-note {
    margin: 11px 0 0;
    font-size: 11px;
    color: #95888e;
    line-height: 1.6;
}

/* LOWER SECTIONS */

.mart-body .um-detail-bottom {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    align-items: start;
    gap: 22px;
    margin-top: 24px;
}

.mart-body .um-detail-description-card,
.mart-body .um-detail-spec-card {
    min-width: 0;
    padding: 28px;
    border: 1px solid #ede5e7;
    border-radius: 15px;
    background: #fff;
}

.mart-body .um-detail-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.mart-body .um-detail-section-heading h2,
.mart-body .um-detail-spec-card h2 {
    margin: 0;
    font-size: 20px;
    color: #312029;
}

.mart-body .um-detail-section-heading > span {
    color: #95878d;
    font-size: 11px;
}

.mart-body .um-detail-description-text {
    color: #6f646a;
    font-size: 13px;
    line-height: 1.9;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.mart-body .um-detail-description-text p {
    margin: 0 0 12px;
}

.mart-body .um-detail-spec-card h2 {
    margin-bottom: 20px;
}

.mart-body .um-detail-spec-card dl {
    margin: 0;
}

.mart-body .um-detail-spec-card dl > div {
    display: grid;
    grid-template-columns: 125px minmax(0, 1fr);
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f1eaed;
}

.mart-body .um-detail-spec-card dt {
    color: #8f8187;
    font-size: 12px;
}

.mart-body .um-detail-spec-card dd {
    margin: 0;
    color: #392b31;
    font-size: 12px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

/* HELP INFORMATION */

.mart-body .um-detail-help {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
    margin-top: 24px;
    padding: 23px;
    background: #fff;
    border: 1px solid #ede5e7;
    border-radius: 15px;
}

.mart-body .um-detail-help-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.mart-body .um-detail-help-item > span {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    border-radius: 9px;
    background: #fff0f1;
    color: #800e19;
    font-size: 19px;
    font-weight: 800;
}

.mart-body .um-detail-help-item strong {
    font-size: 12px;
    color: #34252c;
}

.mart-body .um-detail-help-item p {
    margin: 5px 0 0;
    color: #93868b;
    font-size: 11px;
    line-height: 1.7;
}

.mart-body .um-detail-return {
    display: inline-block;
    margin-top: 23px;
    color: #800e19;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

/* RESPONSIVE */

@media (max-width: 1050px) {
    .mart-body .um-detail-card {
        gap: 22px;
        padding: 22px;
    }

    .mart-body .um-detail-image {
        height: 350px;
    }
}

@media (max-width: 800px) {
    .mart-body .um-detail-card,
    .mart-body .um-detail-bottom {
        grid-template-columns: 1fr;
    }

    .mart-body .um-detail-help {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .mart-body .um-detail-page {
        width: calc(100% - 24px);
        margin: 20px auto 45px;
    }

    .mart-body .um-detail-card {
        padding: 15px;
    }

    .mart-body .um-detail-image {
        height: 290px;
    }

    .mart-body .um-detail-info h1 {
        font-size: 25px;
    }

    .mart-body .um-detail-price-box strong {
        font-size: 26px;
    }

    .mart-body .um-detail-buttons {
        grid-template-columns: 1fr;
    }

    .mart-body .um-detail-description-card,
    .mart-body .um-detail-spec-card {
        padding: 19px;
    }
}

/* UIN MART - PRODUCT DISCOUNT */

.um-detail-page .um-discount-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.um-detail-page .um-original-price {
    font-size: 15px;
    color: #9c9196;
    text-decoration: line-through;
}

.um-detail-page .um-discount-badge {
    padding: 5px 10px;
    border-radius: 6px;
    background: #ffe5e8;
    color: #a01020;
    font-size: 12px;
    font-weight: 800;
}

.um-detail-page .um-saving-label {
    font-size: 13px;
    font-weight: 700;
    color: #16804b;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('umQtyInput');
    const minus = document.getElementById('umQtyMinus');
    const plus = document.getElementById('umQtyPlus');

    if (!input || !minus || !plus) return;

    const max = Number(input.max) || 1;

    function normalizeQuantity(value) {
        let quantity = Number(value);

        if (!Number.isFinite(quantity)) quantity = 1;

        quantity = Math.floor(quantity);

        return Math.max(1, Math.min(max, quantity));
    }

    minus.addEventListener('click', function () {
        input.value = normalizeQuantity(Number(input.value) - 1);
    });

    plus.addEventListener('click', function () {
        input.value = normalizeQuantity(Number(input.value) + 1);
    });

    input.addEventListener('change', function () {
        input.value = normalizeQuantity(input.value);
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainImage = document.getElementById('umMainProductImage');
    const thumbnails = document.querySelectorAll('.um-product-thumb');

    if (!mainImage || !thumbnails.length) {
        return;
    }

    thumbnails.forEach(function (thumbnail) {
        thumbnail.addEventListener('click', function () {
            const newImage = thumbnail.dataset.image;

            if (!newImage) {
                return;
            }

            mainImage.src = newImage;

            thumbnails.forEach(function (item) {
                item.classList.remove('is-active');
            });

            thumbnail.classList.add('is-active');
        });
    });
});
</script>


@endsection
