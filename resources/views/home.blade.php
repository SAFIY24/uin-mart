@extends('layouts.app')

@section('title', 'Beranda · UIN Mart')

@section('content')

@php
    $catIcon = function ($name) {
        $name = strtolower($name);

        if (str_contains($name, 'elektronik')) return 'phone';
        if (str_contains($name, 'fashion')) return 'shirt';
        if (str_contains($name, 'buku')) return 'book';
        if (str_contains($name, 'kuliah')) return 'school';
        if (str_contains($name, 'rumah') || str_contains($name, 'kos')) return 'home';
        if (str_contains($name, 'makanan') || str_contains($name, 'minuman')) return 'food';
        if (str_contains($name, 'olahraga')) return 'ball';

        return 'grid';
    };
@endphp

<!-- ICON SVG -->
<svg class="mart-svg-library"
     xmlns="http://www.w3.org/2000/svg"
     aria-hidden="true"
     focusable="false">

    <symbol id="mart-phone" viewBox="0 0 24 24">
        <rect x="6" y="2.5" width="12" height="19" rx="2"/>
        <path d="M10 5h4M11 18.5h2"/>
    </symbol>

    <symbol id="mart-shirt" viewBox="0 0 24 24">
        <path d="m8 3 4 3 4-3 5 4-3 5-3-2v11H9V10l-3 2-3-5z"/>
    </symbol>

    <symbol id="mart-book" viewBox="0 0 24 24">
        <path d="M12 6c-3-2.4-6-2.6-10-1v15c4-1.6 7-1.4 10 1 3-2.4 6-2.6 10-1V5c-4-1.6-7-1.4-10 1Zm0 0v15"/>
        <path d="M5 9c2-.5 3-.2 4 .4m6 0c1-.6 2-1 4-.4"/>
    </symbol>

    <symbol id="mart-school" viewBox="0 0 24 24">
        <path d="m2 9 10-6 10 6-10 6zM6 12v6c4 3 8 3 12 0v-6M22 9v8"/>
    </symbol>

    <symbol id="mart-home" viewBox="0 0 24 24">
        <path d="M2 11 12 3l10 8M5 10v11h14V10M10 21v-7h4v7"/>
    </symbol>

    <symbol id="mart-food" viewBox="0 0 24 24">
        <path d="M4 3v7c0 2 4 2 4 0V3M6 3v18M16 21V4c3 0 5 3 5 7v4h-5"/>
    </symbol>

    <symbol id="mart-ball" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <path d="M4 6c5 1 7 5 8 16M20 6c-5 1-7 5-8 16M2 12h20"/>
    </symbol>

    <symbol id="mart-grid" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
    </symbol>

    <symbol id="mart-list" viewBox="0 0 24 24">
        <path d="M8 6h13M8 12h13M8 18h13M3 6h1M3 12h1M3 18h1"/>
    </symbol>

    <symbol id="mart-cart" viewBox="0 0 24 24">
        <circle cx="9" cy="21" r="1"/>
        <circle cx="19" cy="21" r="1"/>
        <path d="M2 3h2l3 13h13l2-10H5"/>
    </symbol>

</svg>

<div class="mart-shop">

    <!-- SIDEBAR DANH MỤC -->
    <aside class="mart-sidebar" aria-label="Kategori produk">

        <h2>
            <svg class="mart-icon">
                <use href="#mart-list"/>
            </svg>
            Semua Kategori
        </h2>

  
<nav class="mart-sidebar-items">

    <a href="{{ route('home') }}"
       class="mart-side-item {{ !$categoryId ? 'active' : '' }}">
        <svg class="mart-icon">
            <use href="#mart-grid"/>
        </svg>
        <span>Semua Produk</span>
        <b>›</b>
    </a>

    @auth
        @php
            $sellerApproved = \App\Models\SellerApplication::where('user_id', auth()->id())
                ->where('status', 'approved')
                ->exists();
        @endphp

        @if($sellerApproved)
            <a href="{{ route('seller.dashboard') }}" class="mart-side-item">
                <svg class="mart-icon">
                    <use href="#mart-home"/>
                </svg>
                <span>Toko Saya</span>
                <b>›</b>
            </a>

            <a href="{{ route('seller.products.create') }}" class="mart-side-item">
                <svg class="mart-icon">
                    <use href="#mart-school"/>
                </svg>
                <span>Jual Produk</span>
                <b>›</b>
            </a>
            
<!-- PESANAN MASUK SELLER -->
<a href="{{ route('seller.orders.index') }}"
   class="mart-side-item">

    <svg class="mart-icon"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="1.8"
         stroke-linecap="round"
         stroke-linejoin="round">

        <rect x="3" y="5" width="18" height="15" rx="2"/>
        <path d="M3 9h18"/>
        <path d="M7 14h6"/>
        <path d="m15 15 2 2 3-3"/>
    </svg>

    <span>Pesanan Masuk</span>
    <b>&rsaquo;</b>
</a>

        @else
            <a href="{{ route('seller.register') }}" class="mart-side-item">
                <svg class="mart-icon">
                    <use href="#mart-school"/>
                </svg>
                <span>Daftar Penjual</span>
                <b>›</b>
            </a>
        @endif

   
        <!-- KERANJANG SAYA -->
        <a href="{{ route('cart.index') }}" class="mart-side-item">
            <svg class="mart-icon">
                <use href="#mart-cart"/>
            </svg>
            <span>Keranjang Saya</span>
            <b>&rsaquo;</b>
        </a>

        <!-- PESANAN SAYA -->
 
<a href="{{ route('customer.orders.index') }}" class="mart-side-item">
    <svg class="mart-icon"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="1.8"
         stroke-linecap="round"
         stroke-linejoin="round">
        <path d="M3 7 12 3 21 7 12 11 3 7Z"/>
        <path d="M3 7v10l9 4 9-4V7"/>
        <path d="M12 11v10"/>
        <path d="m16 14 2 2 3-3"/>
    </svg>
    <span>Pesanan Saya</span>
    <b>&rsaquo;</b>
</a>

    @endauth



        </nav>
    </aside>

    <!-- NỘI DUNG CHÍNH -->
    <div class="mart-content">

        @if(!request('q') && !request('category'))

            <!-- =========================
                 BANNER SLIDER UIN MART
                 ========================= -->

            <section class="um-banner-slider"
                     id="umBannerSlider"
                     aria-label="Banner promosi UIN Mart"
                     aria-roledescription="carousel">

                <div class="um-banner-track">

                    <!-- BANNER 1 -->
                    <div class="um-banner-slide"
                         aria-label="Banner 1 dari 3">

                        <img
                            src="{{ asset('assets/uinbarubanner.png') }}"
                            alt="Selamat datang di UIN Mart"
                            draggable="false"
                            fetchpriority="high"
                        >

                    </div>

                    <!-- BANNER 2 -->
                    <div class="um-banner-slide"
                         aria-label="Banner 2 dari 3">

                        <img
                            src="{{ asset('assets/safiy2.png') }}"
                            alt="Promo spesial mahasiswa UIN Mart"
                            draggable="false"
                            loading="lazy"
                        >

                    </div>

                    <!-- BANNER 3 -->
                    <div class="um-banner-slide"
                         aria-label="Banner 3 dari 3">

                        <img
                            src="{{ asset('assets/skm1.png') }}"
                            alt="Kebutuhan kuliah di UIN Mart"
                            draggable="false"
                            loading="lazy"
                        >

                    </div>

                </div>

                <!-- MŨI TÊN TRÁI -->
                <button
                    class="um-banner-arrow um-banner-prev"
                    type="button"
                    aria-label="Banner sebelumnya"
                >
                    &#10094;
                </button>

                <!-- MŨI TÊN PHẢI -->
                <button
                    class="um-banner-arrow um-banner-next"
                    type="button"
                    aria-label="Banner berikutnya"
                >
                    &#10095;
                </button>

                <!-- KHÔNG CÓ DẤU CHẤM PHÍA DƯỚI -->

            </section>

        @endif


        <!-- DANH MỤC NỔI BẬT -->
        <section class="mart-categories"
                 id="kategori"
                 aria-label="Kategori pilihan">

            @foreach($categories as $c)

                <a href="{{ route('home', ['category' => $c->id]) }}"
                   class="mart-category {{ (int) $categoryId === (int) $c->id ? 'active' : '' }}">

                    <span class="mart-category-circle">

                        <svg class="mart-icon">
                            <use href="#mart-{{ $catIcon($c->name) }}"/>
                        </svg>

                    </span>

                    <span class="mart-category-name">
                        {{ $c->name }}
                    </span>

                </a>

            @endforeach

        </section>


        <!-- DANH SÁCH SẢN PHẨM -->
        <section id="produk" class="mart-products-section">

            <div class="mart-products-heading">

                <h1>
                    {{ $q
                        ? 'Hasil Pencarian'
                        : ($categoryId
                            ? 'Produk Berdasarkan Kategori'
                            : 'Produk Terbaru')
                    }}
                </h1>

                <a href="{{ route('home') }}#produk">
                    Lihat Semua →
                </a>

            </div>

            @if($q)
                <p class="mart-search-result">
                    Hasil pencarian: “{{ $q }}”
                    · {{ $products->total() }} produk
                </p>
            @endif

            <div class="mart-products-grid">

                @forelse($products as $product)

                    <article class="mart-product">

                        <!-- ẢNH SẢN PHẨM -->
                        <a class="mart-product-img"
                           href="{{ route('products.show', $product) }}">

                            @if($product->image)

                                <img
                                    loading="lazy"
                                    src="{{ str_starts_with($product->image, 'products/')
                                        ? asset('storage/'.$product->image)
                                        : asset('uploads/products/'.basename($product->image)) }}"
                                    alt="{{ $product->name }}"
                                >

                            @else

                                <span class="mart-no-photo">
                                    UIN MART
                                </span>

                            @endif

                        </a>

                        <!-- THÔNG TIN SẢN PHẨM -->
                        <div class="mart-product-info">

                            <a class="mart-product-title"
                               href="{{ route('products.show', $product) }}">
                                {{ $product->name }}
                            </a>

                            {{-- Harga memakai data ProductPricing dari StoreController --}}
                            @if(($product->discount_percentage ?? 0) > 0 && ($product->final_price ?? $product->price) < $product->price)
                                <div class="mart-product-discount">
                                    <span class="mart-discount-badge">-{{ $product->discount_percentage }}%</span>
                                    <span class="mart-price-original">Rp {{ number_format($product->original_price, 0, ',', '.') }}</span>
                                </div>
                                <strong class="mart-product-price mart-price-sale">
                                    Rp {{ number_format($product->final_price, 0, ',', '.') }}
                                </strong>
                            @else
                                <strong class="mart-product-price">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </strong>
                            @endif

                            <span class="mart-product-stock {{ $product->stock < 1 ? 'is-out' : '' }}">
                                {{ $product->stock > 0
                                    ? 'Stok: '.$product->stock
                                    : 'Stok habis'
                                }}
                            </span>

                            <!-- NÚT MUA HÀNG -->
                            <div class="mart-product-actions">

                                <!-- THÊM VÀO GIỎ -->
                                <form method="POST"
                                      action="{{ route('cart.add') }}">

                                    @csrf

                                    <input type="hidden"
                                           name="product_id"
                                           value="{{ $product->id }}">

                                    <input type="hidden"
                                           name="quantity"
                                           value="1">

                                    <button
                                        class="mart-add-button"
                                        type="submit"
                                        title="Tambah ke keranjang"
                                        aria-label="Tambah {{ $product->name }} ke keranjang"
                                        @disabled($product->stock < 1)
                                    >
                                        <svg class="mart-icon">
                                            <use href="#mart-cart"/>
                                        </svg>

                                        <span>Keranjang</span>
                                    </button>

                                </form>

                                <!-- MUA NGAY -->
                                @auth

                                    <form method="POST"
                                          action="{{ route('checkout.buy') }}">

                                        @csrf

                                        <input type="hidden"
                                               name="product_id"
                                               value="{{ $product->id }}">

                                        <input type="hidden"
                                               name="quantity"
                                               value="1">

                                        <button
                                            type="submit"
                                            class="mart-buy-button"
                                            @disabled($product->stock < 1)
                                        >
                                            Beli
                                        </button>

                                    </form>

                                @else

                                    <a
                                        href="{{ $product->stock > 0 ? route('login') : '#' }}"
                                        class="mart-buy-button {{ $product->stock < 1 ? 'is-disabled' : '' }}"
                                    >
                                        Beli
                                    </a>

                                @endauth

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="mart-empty">
                        Belum ada produk.
                        Silakan pilih kategori lain atau
                        <a href="{{ route('home') }}">
                            lihat semua produk
                        </a>.
                    </div>

                @endforelse

            </div>

            <div class="mart-pagination">
                {{ $products->links() }}
            </div>

        </section>

    </div>

</div>


<!-- JAVASCRIPT BANNER SLIDER -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const slider = document.getElementById('umBannerSlider');

    if (!slider) return;

    const track = slider.querySelector('.um-banner-track');
    const slides = slider.querySelectorAll('.um-banner-slide');
    const prev = slider.querySelector('.um-banner-prev');
    const next = slider.querySelector('.um-banner-next');

    if (slides.length < 2) return;

    let current = 0;
    let timer = null;
    let startX = null;

    const reducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    function showSlide(index) {
        current = (index + slides.length) % slides.length;

        track.style.transform =
            'translateX(-' + (current * 100) + '%)';
    }

    function stopAuto() {
        if (timer !== null) {
            clearInterval(timer);
            timer = null;
        }
    }

    function startAuto() {
        stopAuto();

        if (reducedMotion || document.hidden) return;

        timer = setInterval(function () {
            showSlide(current + 1);
        }, 5000);
    }

    // BẤM MŨI TÊN TRÁI
    prev.addEventListener('click', function () {
        showSlide(current - 1);
        startAuto();
    });

    // BẤM MŨI TÊN PHẢI
    next.addEventListener('click', function () {
        showSlide(current + 1);
        startAuto();
    });

    // KÉO CHUỘT / VUỐT ĐIỆN THOẠI
    slider.addEventListener('pointerdown', function (event) {
        if (event.target.closest('button')) return;

        startX = event.clientX;
        stopAuto();
    });

    slider.addEventListener('pointerup', function (event) {
        if (startX === null) return;

        const distance = event.clientX - startX;
        startX = null;

        if (Math.abs(distance) > 50) {
            if (distance < 0) {
                showSlide(current + 1);
            } else {
                showSlide(current - 1);
            }
        }

        startAuto();
    });

    slider.addEventListener('pointercancel', function () {
        startX = null;
        startAuto();
    });

    // DỪNG KHI RÊ CHUỘT LÊN BANNER
    slider.addEventListener('mouseenter', stopAuto);

    slider.addEventListener('mouseleave', function () {
        if (startX === null) startAuto();
    });

    // DỪNG KHI CHUYỂN SANG TAB KHÁC
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopAuto();
        } else {
            startAuto();
        }
    });

    showSlide(0);
    startAuto();

});
</script>


<style>
/* Diskon Produk: giá giảm trên thẻ sản phẩm, giữ nguyên bố cục cũ */
.mart-product-discount {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 6px 0 3px;
}
.mart-discount-badge {
    background: #800e19;
    color: #fff;
    border-radius: 6px;
    padding: 3px 7px;
    font-size: 11px;
    font-weight: 700;
}
.mart-price-original {
    color: #878087;
    font-size: 12px;
    text-decoration: line-through;
}
.mart-price-sale { color: #800e19; }
</style>

@endsection
