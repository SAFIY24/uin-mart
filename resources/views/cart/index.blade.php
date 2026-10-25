
@extends('layouts.app')

@section('title', 'Keranjang Belanja · UIN Mart')

@section('content')

<div class="um-cart-page">

    <div class="um-cart-heading">
        <div>
            <p class="um-cart-eyebrow">UIN MART / KERANJANG</p>
            <h1>Keranjang Belanja</h1>
            <p>Periksa produk pilihanmu sebelum checkout.</p>
        </div>

        <a href="{{ route('home') }}" class="um-cart-back">
            ← Lanjut Belanja
        </a>
    </div>

    @if($items->isEmpty())

        <div class="um-cart-empty">
            <div class="um-cart-empty-icon">🛒</div>
            <h2>Keranjang Masih Kosong</h2>
            <p>Yuk, temukan produk menarik untuk kebutuhanmu!</p>

            <a href="{{ route('home') }}" class="um-cart-primary">
                Mulai Belanja →
            </a>
        </div>

    @else

        <div class="um-cart-layout">

            <section class="um-cart-list">

                <div class="um-cart-list-header">
                    <div>
                        <h2>Produk di Keranjang</h2>
                        <p>Produk yang sudah kamu pilih</p>
                    </div>

                    <span class="um-cart-counter">
                        {{ $items->sum('cart_quantity') }} barang
                    </span>
                </div>

                <form
                    id="um-cart-update"
                    method="POST"
                    action="{{ route('cart.update') }}"
                >
                    @csrf
                    @method('PATCH')

                    @foreach($items as $p)

                        @php
                            $imageUrl = $p->image
                                ? (
                                    str_starts_with($p->image, 'products/')
                                        ? asset('storage/' . $p->image)
                                        : asset('uploads/products/' . basename($p->image))
                                )
                                : null;
                        @endphp

                        <article class="um-cart-item">

                            <a
                                href="{{ route('products.show', $p) }}"
                                class="um-cart-product-image"
                                aria-label="Lihat {{ $p->name }}"
                            >
                                @if($imageUrl)
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $p->name }}"
                                        loading="lazy"
                                    >
                                @else
                                    <span>UIN MART</span>
                                @endif
                            </a>

                            <div class="um-cart-info">

                                <a
                                    href="{{ route('products.show', $p) }}"
                                    class="um-cart-product-name"
                                >
                                    {{ $p->name }}
                                </a>

                                <p class="um-cart-category">
                                    {{ $p->category->name ?? 'Produk UIN Mart' }}
                                </p>

@if(($p->discount_percentage ?? 0) > 0)
    <div style="display:flex; align-items:center; gap:9px; margin-bottom:5px;">
        <span style="color:#999; text-decoration:line-through; font-size:13px;">
            Rp {{ number_format((float) $p->original_price, 0, ',', '.') }}
        </span>

        <span style="background:#ffe7e9; color:#a01020; padding:3px 8px; border-radius:5px; font-size:11px; font-weight:800;">
            -{{ $p->discount_percentage }}%
        </span>
    </div>
@endif

<p class="um-cart-unit-price">
    Rp {{ number_format((float) $p->price, 0, ',', '.') }}
    <small>/ item</small>
</p>

                                <p class="um-cart-stock">
                                    Stok tersedia: {{ $p->stock }}
                                </p>

                                <button
                                    type="submit"
                                    form="remove{{ $p->id }}"
                                    class="um-cart-remove"
                                    onclick="return confirm('Hapus produk ini dari keranjang?')"
                                >
                                    Hapus Produk
                                </button>
                            </div>

                            <div class="um-cart-item-right">

                                <label for="quantity-{{ $p->id }}">
                                    Jumlah
                                </label>

                                <input
                                    id="quantity-{{ $p->id }}"
                                    type="number"
                                    name="quantities[{{ $p->id }}]"
                                    value="{{ $p->cart_quantity }}"
                                    min="0"
                                    max="{{ $p->stock }}"
                                    step="1"
                                    class="um-cart-quantity"
                                    required
                                >

                                <small>Subtotal</small>

                                <strong class="um-cart-subtotal">
                                    Rp {{ number_format((float) $p->subtotal, 0, ',', '.') }}
                                </strong>

                            </div>
                        </article>

                    @endforeach
                </form>

                @foreach($items as $p)
                    <form
                        id="remove{{ $p->id }}"
                        method="POST"
                        action="{{ route('cart.remove', $p) }}"
                    >
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach

                <div class="um-cart-list-footer">
                    <p>Ubah jumlah produk lalu klik perbarui.</p>

                    <button
                        type="submit"
                        form="um-cart-update"
                        class="um-cart-update"
                    >
                        Perbarui Keranjang
                    </button>
                </div>

            </section>

            <aside class="um-cart-summary">

                <h2>Ringkasan Belanja</h2>

                <p class="um-cart-summary-desc">
                    Rincian belanja kamu di UIN Mart.
                </p>

                <div class="um-cart-summary-line">
                    <span>Total Barang</span>
                    <strong>
                        {{ $items->sum('cart_quantity') }}
                    </strong>
                </div>

                <div class="um-cart-summary-line">
                    <span>Subtotal</span>
                    <strong>
                        Rp {{ number_format((float) $items->sum('subtotal'), 0, ',', '.') }}
                    </strong>
                </div>

                <div class="um-cart-summary-divider"></div>

                <div class="um-cart-summary-total">
                    <span>Total Belanja</span>
                    <strong>
                        Rp {{ number_format((float) $items->sum('subtotal'), 0, ',', '.') }}
                    </strong>
                </div>

                <a
                    href="{{ route('checkout.index', ['source' => 'cart']) }}"
                    class="um-cart-checkout"
                >
                    Lanjut Checkout
                    <span aria-hidden="true">→</span>
                </a>

                <p class="um-cart-summary-note">
                    Pastikan jumlah produk sudah benar sebelum checkout.
                    Jika kamu mengubah jumlah, klik Perbarui Keranjang
                    terlebih dahulu.
                </p>

                <div class="um-cart-safe">
                    ✓ Belanja praktis di UIN Mart
                </div>

            </aside>
        </div>

    @endif

</div>

<style>
/* ==============================================
   UIN MART - KERANJANG BELANJA
   ============================================== */

.mart-body .um-cart-page {
    width: min(1320px, calc(100% - 40px));
    margin: 32px auto 65px;
    color: #29232a;
    font-size: 14px;
}

.mart-body .um-cart-page *,
.mart-body .um-cart-page *::before,
.mart-body .um-cart-page *::after {
    box-sizing: border-box;
}

.mart-body .um-cart-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.mart-body .um-cart-eyebrow {
    margin: 0 0 8px;
    color: #a1787f;
    font-size: 11px;
    letter-spacing: 1px;
    font-weight: 800;
}

.mart-body .um-cart-heading h1 {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 800;
    color: #271d22;
}

.mart-body .um-cart-heading p:not(.um-cart-eyebrow) {
    margin: 0;
    color: #827980;
    font-size: 13px;
}

.mart-body .um-cart-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 17px;
    border: 1px solid #e8dfe1;
    border-radius: 9px;
    background: #fff;
    color: #800e19;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.mart-body .um-cart-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 350px;
    align-items: start;
    gap: 22px;
}

/* DAFTAR PRODUK */

.mart-body .um-cart-list,
.mart-body .um-cart-summary,
.mart-body .um-cart-empty {
    min-width: 0;
    background: #fff;
    border: 1px solid #ece3e5;
    border-radius: 16px;
    box-shadow: 0 5px 24px rgba(55, 20, 28, .035);
}

.mart-body .um-cart-list {
    padding: 24px;
}

.mart-body .um-cart-list-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
}

.mart-body .um-cart-list-header h2,
.mart-body .um-cart-summary h2 {
    margin: 0;
    font-size: 19px;
    color: #2e1b20;
}

.mart-body .um-cart-list-header p,
.mart-body .um-cart-summary-desc {
    margin: 7px 0 0;
    font-size: 12px;
    color: #91848a;
}

.mart-body .um-cart-counter {
    padding: 7px 12px;
    border-radius: 20px;
    background: #fff0f1;
    color: #800e19;
    font-size: 12px;
    font-weight: 700;
}

/* HANG SAN PHAM */

.mart-body .um-cart-item {
    display: grid;
    grid-template-columns: 115px minmax(0, 1fr) 145px;
    gap: 18px;
    align-items: center;
    padding: 22px 0;
    border-bottom: 1px solid #f0e9eb;
}

.mart-body .um-cart-product-image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 115px;
    height: 115px;
    overflow: hidden;
    border: 1px solid #eee5e7;
    border-radius: 12px;
    background: #faf8f8;
    text-decoration: none;
}

.mart-body .um-cart-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mart-body .um-cart-product-image > span {
    color: #aaa1a6;
    font-size: 13px;
    letter-spacing: 1px;
    font-weight: 800;
}

.mart-body .um-cart-info {
    min-width: 0;
}

.mart-body .um-cart-product-name {
    display: inline-block;
    margin-bottom: 6px;
    color: #302127;
    font-size: 17px;
    font-weight: 800;
    text-decoration: none;
    overflow-wrap: anywhere;
}

.mart-body .um-cart-product-name:hover {
    color: #800e19;
}

.mart-body .um-cart-category {
    margin: 0 0 12px;
    color: #97898e;
    font-size: 12px;
}

.mart-body .um-cart-unit-price {
    margin: 0 0 8px;
    color: #800e19;
    font-size: 18px;
    font-weight: 800;
}

.mart-body .um-cart-unit-price small {
    color: #998c91;
    font-size: 11px;
    font-weight: 400;
}

.mart-body .um-cart-stock {
    margin: 0 0 12px;
    font-size: 12px;
    color: #168054;
    font-weight: 650;
}

.mart-body .um-cart-remove {
    display: inline-flex;
    width: auto;
    min-height: 34px;
    padding: 7px 11px;
    border: 1px solid #f3d2d6;
    border-radius: 7px;
    background: #fff4f5;
    color: #bb2b3d;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.mart-body .um-cart-remove:hover {
    background: #ffe5e8;
}

/* SO LUONG */

.mart-body .um-cart-item-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 9px;
}

.mart-body .um-cart-item-right label,
.mart-body .um-cart-item-right > small {
    color: #867980;
    font-size: 12px;
    font-weight: 600;
}

.mart-body .um-cart-page input.um-cart-quantity {
    width: 95px !important;
    height: 43px;
    min-height: 43px;
    padding: 8px 10px;
    border: 1px solid #e2d8dc;
    border-radius: 8px;
    background: #fff;
    color: #30262c;
    text-align: center;
    font-size: 14px;
}

.mart-body .um-cart-subtotal {
    color: #800e19;
    font-size: 17px;
    font-weight: 800;
    white-space: nowrap;
}

.mart-body .um-cart-list-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    padding-top: 23px;
}

.mart-body .um-cart-list-footer p {
    margin: 0;
    color: #91848a;
    font-size: 12px;
}

.mart-body .um-cart-update,
.mart-body .um-cart-checkout,
.mart-body .um-cart-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border: 0;
    border-radius: 9px;
    background: #800e19;
    color: #fff;
    padding: 13px 19px;
    font-size: 13px;
    font-weight: 750;
    text-decoration: none;
    cursor: pointer;
    transition: background .2s;
}

.mart-body .um-cart-update:hover,
.mart-body .um-cart-checkout:hover,
.mart-body .um-cart-primary:hover {
    background: #650a14;
    color: #fff;
}

/* RINGKASAN */

.mart-body .um-cart-summary {
    position: sticky;
    top: 20px;
    padding: 25px;
}

.mart-body .um-cart-summary-desc {
    margin-bottom: 25px;
}

.mart-body .um-cart-summary-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin: 16px 0;
    color: #70666a;
    font-size: 13px;
}

.mart-body .um-cart-summary-line strong {
    color: #32252b;
    font-weight: 750;
}

.mart-body .um-cart-summary-divider {
    border-top: 1px solid #eee5e7;
    margin: 22px 0;
}

.mart-body .um-cart-summary-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 23px;
}

.mart-body .um-cart-summary-total span {
    font-size: 13px;
    font-weight: 700;
}

.mart-body .um-cart-summary-total strong {
    color: #800e19;
    font-size: 23px;
    font-weight: 850;
    white-space: nowrap;
}

.mart-body .um-cart-checkout {
    width: 100%;
    min-height: 47px;
}

.mart-body .um-cart-summary-note {
    margin: 15px 0;
    color: #94878c;
    text-align: center;
    font-size: 11px;
    line-height: 1.7;
}

.mart-body .um-cart-safe {
    padding: 13px;
    text-align: center;
    background: #f0faf4;
    color: #167449;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 650;
}

/* KERANJANG KOSONG */

.mart-body .um-cart-empty {
    max-width: 600px;
    margin: 0 auto;
    padding: 55px 25px;
    text-align: center;
}

.mart-body .um-cart-empty-icon {
    font-size: 48px;
    line-height: 1;
    margin-bottom: 18px;
}

.mart-body .um-cart-empty h2 {
    margin: 0 0 10px;
    font-size: 23px;
}

.mart-body .um-cart-empty p {
    color: #94878c;
    font-size: 13px;
    margin-bottom: 24px;
}

/* RESPONSIVE */

@media (max-width: 1000px) {
    .mart-body .um-cart-layout {
        grid-template-columns: 1fr;
    }

    .mart-body .um-cart-summary {
        position: static;
    }
}

@media (max-width: 650px) {
    .mart-body .um-cart-page {
        width: calc(100% - 24px);
        margin: 22px auto 40px;
    }

    .mart-body .um-cart-list,
    .mart-body .um-cart-summary {
        padding: 16px;
    }

    .mart-body .um-cart-item {
        grid-template-columns: 85px minmax(0, 1fr);
        gap: 12px;
    }

    .mart-body .um-cart-product-image {
        width: 85px;
        height: 85px;
    }

    .mart-body .um-cart-item-right {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 1fr 100px;
        align-items: center;
        justify-items: end;
    }

    .mart-body .um-cart-item-right label {
        justify-self: start;
    }

    .mart-body .um-cart-item-right > small {
        justify-self: start;
    }

    .mart-body .um-cart-unit-price {
        font-size: 15px;
    }
}
</style>

@endsection
