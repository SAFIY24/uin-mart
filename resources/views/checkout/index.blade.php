@extends('layouts.app')

@section('title', 'Checkout · UIN Mart')

@section('content')

@php
    $isBuyNow = session('checkout_source') === 'buy_now';
    $backUrl = $isBuyNow ? route('home') : route('cart.index');

    // Subtotal harga normal sebelum diskon produk.
    $subtotal = $items->sum(function ($product) {
        return (float) ($product->original_price ?? $product->price)
            * (int) $product->cart_quantity;
    });

    // Total aktual sesuai harga dari CheckoutController.
    $total = $items->sum(function ($product) {
        return (float) ($product->final_price ?? $product->price)
            * (int) $product->cart_quantity;
    });

    $discountAmount = max(0, $subtotal - $total);
@endphp

<link rel="stylesheet" href="{{ asset('assets/uin-checkout-discount.css') }}?v=3">

<div class="um-checkout-page">

    <!-- HEADER -->

    <div class="um-checkout-heading">

        <div>

            <p class="um-checkout-eyebrow">

                UIN MART / PEMBAYARAN

            </p>

            <h1>Checkout</h1>

            <p>

                Lengkapi informasi pengiriman

                untuk menyelesaikan pesanan Anda.

            </p>

        </div>

        <a href="{{ $backUrl }}" class="um-checkout-top-back">

            ← Kembali Belanja

        </a>

    </div>

    <div class="um-checkout-layout">

        <!-- INFORMASI PENGIRIMAN -->

        <section class="um-checkout-card um-checkout-shipping">

            <div class="um-checkout-section-heading">

                <span class="um-checkout-step">1</span>

                <div>

                    <h2>Informasi Pengiriman</h2>

                    <p>Masukkan data penerima dan alamat pengiriman.</p>

                </div>

            </div>

            <form

                id="checkoutOrderForm"

                method="POST"

                action="{{ route('checkout.store') }}"

            >

                @csrf

                <div class="um-checkout-field">

                    <label for="customer_name">

                        Nama Penerima <span>*</span>

                    </label>

                    <input

                        id="customer_name"

                        type="text"

                        name="customer_name"

                        value="{{ old('customer_name', auth()->user()->username ?? '') }}"

                        placeholder="Nama lengkap penerima"

                        maxlength="150"

                        autocomplete="name"

                        required

                    >

                    @error('customer_name')

                        <small class="um-checkout-field-error">

                            {{ $message }}

                        </small>

                    @enderror

                </div>

                <div class="um-checkout-field">

                    <label for="phone">

                        Nomor Telepon <span>*</span>

                    </label>

                    <input

                        id="phone"

                        type="tel"

                        name="phone"

                        value="{{ old('phone') }}"

                        placeholder="Contoh: 081234567890"

                        maxlength="30"

                        autocomplete="tel"

                        required

                    >

                    @error('phone')

                        <small class="um-checkout-field-error">

                            {{ $message }}

                        </small>

                    @enderror

                </div>

                <div class="um-checkout-field">

                    <label for="address">

                        Alamat Pengiriman <span>*</span>

                    </label>

                    <textarea

                        id="address"

                        name="address"

                        rows="4"

                        maxlength="500"

                        autocomplete="street-address"

                        placeholder="Nama jalan, nomor rumah atau kos, kecamatan, kota..."

                        required

                    >{{ old('address') }}</textarea>

                    <small class="um-checkout-help">

                        Pastikan alamat lengkap agar pesanan mudah dikirim.

                    </small>

                    @error('address')

                        <small class="um-checkout-field-error">

                            {{ $message }}

                        </small>

                    @enderror

                </div>

                <div class="um-checkout-field">

                    <label for="notes">

                        Catatan Pesanan

                        <span class="um-checkout-optional">(Opsional)</span>

                    </label>

                    <textarea

                        id="notes"

                        name="notes"

                        rows="3"

                        maxlength="1000"

                        placeholder="Contoh: Hubungi sebelum mengantar..."

                    >{{ old('notes') }}</textarea>

                    @error('notes')

                        <small class="um-checkout-field-error">

                            {{ $message }}

                        </small>

                    @enderror

                </div>

                
<!-- METODE PENGIRIMAN -->
<div class="um-checkout-field">

    <label for="delivery_method">
        Metode Pengiriman <span>*</span>
    </label>

    <select
        id="delivery_method"
        name="delivery_method"
        required
    >
        <option value="">
            Pilih Metode Pengiriman
        </option>

        <option
            value="pickup"
            @selected(old('delivery_method') === 'pickup')
        >
            Ambil di Kampus
        </option>

        <option
            value="seller_delivery"
            @selected(old('delivery_method') === 'seller_delivery')
        >
            Diantar Penjual
        </option>
    </select>

    <small class="um-checkout-help">
        Pengambilan di kampus atau pengantaran
        akan dikoordinasikan dengan penjual.
        Belum ada biaya pengiriman otomatis.
    </small>

    @error('delivery_method')
        <small class="um-checkout-field-error">
            {{ $message }}
        </small>
    @enderror

</div>

<!-- LOKASI PENGAMBILAN -->
<div
    class="um-checkout-field"
    id="pickup_location_field"
    style="display:none;"
>
    <label for="pickup_location">
        Lokasi Pengambilan <span>*</span>
    </label>

    <input
        type="text"
        id="pickup_location"
        name="pickup_location"
        value="{{ old('pickup_location') }}"
        maxlength="255"
        placeholder="Contoh: lokasi pertemuan di area kampus"
    >

    <small class="um-checkout-help">
        Tulis lokasi pengambilan yang diinginkan.
        Lokasi akhir perlu disepakati dengan penjual.
    </small>

    @error('pickup_location')
        <small class="um-checkout-field-error">
            {{ $message }}
        </small>
    @enderror
</div>


                <div class="um-checkout-divider"></div>

                <!-- PEMBAYARAN -->

                <div class="um-checkout-section-heading um-checkout-payment-heading">

                    <span class="um-checkout-step">2</span>

                    <div>

                        <h2>Metode Pembayaran</h2>

                        <p>Pilih metode pembayaran pesanan Anda.</p>

                    </div>

                </div>

                <div class="um-checkout-field">

                    <label for="payment_method">

                        Metode Pembayaran <span>*</span>

                    </label>

                    <select

                        id="payment_method"

                        name="payment_method"

                        required

                    >

                        <option

                            value="cod"

                            @selected(old('payment_method', 'cod') === 'cod')

                        >

                            COD — Bayar di Tempat

                        </option>

                        <option

                            value="bank_transfer"

                            @selected(old('payment_method', 'cod') === 'bank_transfer')

                        >

                            Transfer Bank (Konfirmasi Manual)

                        </option>

                    </select>

                    @error('payment_method')

                        <small class="um-checkout-field-error">

                            {{ $message }}

                        </small>

                    @enderror

                </div>

                <div class="um-checkout-payment-notice">

                    <strong>Informasi Pembayaran</strong>

                    <p>

                        Detail pembayaran dan status pesanan

                        akan tersedia setelah pesanan dibuat.

                    </p>

                </div>

                <!-- ACTION -->

                <div class="um-checkout-actions">

                    <a

                        href="{{ $backUrl }}"

                        class="um-checkout-back"

                    >

                        ← Kembali

                    </a>

                    <button

                        type="submit"

                        class="um-checkout-submit"

                    >

                        Buat Pesanan →

                    </button>

                </div>

            </form>

        </section>

        <!-- RINGKASAN -->

        <aside class="um-checkout-card um-checkout-summary">

            <div class="um-checkout-summary-heading">

                <h2>Ringkasan Pesanan</h2>

                <span>

                    {{ $items->sum('cart_quantity') }} barang

                </span>

            </div>

            <div class="um-checkout-products">

                @foreach($items as $product)

                    @php

                        $imageUrl = $product->image

                            ? (

                                str_starts_with($product->image, 'products/')

                                    ? asset('storage/' . $product->image)

                                    : asset('uploads/products/' . basename($product->image))

                            )

                            : null;

                        $originalPrice = (float) ($product->original_price ?? $product->price);
            $finalPrice = (float) ($product->final_price ?? $product->price);
            $discountPercent = (int) ($product->discount_percentage ?? 0);
            $lineTotal = $finalPrice * (int) $product->cart_quantity;

                    @endphp

                    <div class="um-checkout-product">

                        <div class="um-checkout-product-image">

                            @if($imageUrl)

                                <img

                                    src="{{ $imageUrl }}"

                                    alt="{{ $product->name }}"

                                    loading="lazy"

                                >

                            @else

                                <span>UIN</span>

                            @endif

                        </div>

                        <div class="um-checkout-product-info">
                <strong>{{ $product->name }}</strong>
                @if($discountPercent > 0)
                    <div class="um-checkout-discount-info">
                        <span class="um-checkout-old-price">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                        <span class="um-checkout-discount-badge">-{{ $discountPercent }}%</span>
                    </div>
                @endif
                <small>
                    {{ $product->cart_quantity }} × Rp {{ number_format($finalPrice, 0, ',', '.') }}
                </small>
            </div>

                        <strong class="um-checkout-product-total">

                            Rp {{ number_format($lineTotal, 0, ',', '.') }}

                        </strong>

                    </div>

                @endforeach

            </div>

            <div class="um-checkout-divider"></div>

            <!-- VOUCHER -->

            
<style>
.um-checkout-voucher-row button[type="submit"] {
    background: #800e19 !important;
    color: #ffffff !important;
    opacity: 1 !important;
    cursor: pointer !important;
    border: none !important;
    border-radius: 9px;
    font-weight: 700;
}

.um-checkout-voucher-row button[type="submit"]:hover {
    background: #650b14 !important;
}
</style>

 
<div class="um-checkout-voucher">

    <label for="promotion_code">
        Kode Voucher
    </label>

    <form
        action="{{ route('checkout.voucher.apply') }}"
        method="POST"
    >
        @csrf

        <div class="um-checkout-voucher-row">

            <input
                id="promotion_code"
                type="text"
                name="promotion_code"
                placeholder="Contoh: UINMART10"
                maxlength="50"
                value="{{ old('promotion_code', session('checkout_voucher', '')) }}"
                required
            >

            <button type="submit">
                Gunakan
            </button>

        </div>

    </form>

    @error('promotion_code')
        <small style="display:block;margin-top:10px;color:#b42936;">
            {{ $message }}
        </small>
    @enderror

    @if(session('success_voucher'))
        <small style="display:block;margin-top:10px;color:#267a44;">
            {{ session('success_voucher') }}
        </small>
    @endif

    
@if(!empty($voucherCode))
    <small style="display:block;margin-top:10px;color:#267a44;">
        Voucher {{ $voucherCode }} aktif.
        Diskon Rp {{ number_format($voucherDiscount, 0, ',', '.') }}
    </small>
@endif

@if(!empty($voucherError))
    <small style="display:block;margin-top:10px;color:#b42936;">
        {{ $voucherError }}
    </small>
@endif


    <small id="voucher-help" style="display:block;margin-top:10px;">
        Masukkan kode voucher untuk memeriksa diskon.
    </small>

</div>


            <!-- TOTAL HARGA -->

            <div class="um-checkout-price-row">

                <span>Subtotal (Harga Normal)</span>

                <strong>

                    Rp {{ number_format($subtotal, 0, ',', '.') }}

                </strong>

            </div>

            <div class="um-checkout-price-row">

                <span>Diskon Produk</span>

                <strong>

                    - Rp {{ number_format($discountAmount, 0, ',', '.') }}

                </strong>

            </div>

            <div class="um-checkout-divider"></div>

            
@if($voucherDiscount > 0)
    <div class="um-checkout-price-row">
        <span>Diskon Voucher ({{ $voucherCode }})</span>

        <strong style="color:#267a44;">
            - Rp {{ number_format($voucherDiscount, 0, ',', '.') }}
        </strong>
    </div>

    <div class="um-checkout-divider"></div>
@endif


            <div class="um-checkout-total">

                <span>Total Pembayaran</span>

                <strong>

                    Rp {{ number_format($voucherTotal, 0, ',', '.') }}

                </strong>

            </div>

            <p class="um-checkout-note">

                Total pembayaran akan dihitung kembali

                oleh sistem saat pesanan dibuat.

            </p>

            <div class="um-checkout-safe">

                ✓ Pesanan diproses melalui UIN Mart

            </div>

        </aside>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const method = document.getElementById('delivery_method');
    const pickupField = document.getElementById('pickup_location_field');
    const pickupInput = document.getElementById('pickup_location');

    if (!method || !pickupField || !pickupInput) {
        return;
    }

    function updateDeliveryFields() {
        const isPickup = method.value === 'pickup';

        pickupField.style.display = isPickup ? 'block' : 'none';
        pickupInput.required = isPickup;
        pickupInput.disabled = !isPickup;
    }

    method.addEventListener('change', updateDeliveryFields);

    updateDeliveryFields();
});
</script>


@endsection
