
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'UIN Mart')</title>

    <!-- CSS WEBSITE -->
    <link rel="stylesheet"
          href="{{ asset('assets/app.css') }}?v=2">

    <link rel="stylesheet"
          href="{{ asset('assets/uin-modern-reference.css') }}?v=65">

    <!-- CSS AUTH -->
    <link rel="stylesheet"
          href="{{ asset('assets/uin-login.css') }}?v=12">
          <link rel="stylesheet"
      href="{{ asset('assets/uin-cart-checkout.css') }}?v=1">

    @stack('styles')

</head>

<body class="mart-body">

    <!-- HEADER WEBSITE -->
    <header class="mart-header">
        <div class="mart-header-inner">

            <!-- LOGO -->
            <a href="{{ route('home') }}"
               class="mart-brand"
               aria-label="Beranda UIN Mart">

                <img
                    src="{{ asset('assets/uin-logo.png') }}"
                    alt="Logo UIN Imam Bonjol Padang"
                    width="70"
                    height="70"
                >

                <span class="mart-brand-words">
                    <strong>UIN <em>MART</em></strong>
                    <small>UIN IMAM BONJOL PADANG</small>
                </span>

            </a>

            @auth
                <!-- PENCARIAN -->
                <form
                    class="mart-search"
                    action="{{ route('home') }}"
                    method="GET"
                    role="search"
                >
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari produk, kategori, atau merek..."
                        aria-label="Cari produk"
                    >

                    <button type="submit" aria-label="Cari produk">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="10.5" cy="10.5" r="7.5"/>
                            <path d="m16 16 5 5"/>
                        </svg>
                    </button>
                </form>

                <!-- MENU AKUN -->
                <nav
                    class="mart-header-right"
                    aria-label="Akun dan keranjang"
                >

                    <span class="mart-greeting">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21c0-5 3-8 8-8s8 3 8 8"/>
                        </svg>

                        Halo, {{ auth()->user()->username }}
                    </span>

                    @if(auth()->user()->role === 'admin')
                        <a
                            class="mart-admin-link"
                            href="{{ route('admin.index') }}"
                        >
                            Admin
                        </a>
                    @endif

                    <!-- LOGOUT -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button class="mart-logout" type="submit">
                            Keluar
                        </button>
                    </form>

                    <!-- KERANJANG -->
                    <a
                        class="mart-cart-header"
                        href="{{ route('cart.index') }}"
                        aria-label="Lihat keranjang"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="19" cy="21" r="1"/>
                            <path d="M2 3h2l3 13h13l2-10H5"/>
                        </svg>

                        <span class="mart-cart-count">
                            {{ array_sum(session('cart', [])) }}
                        </span>
                    </a>

                </nav>
            @endauth

        </div>
    </header>

    <!-- KONTEN WEBSITE -->
    <main class="mart-page" id="main-content">

        @if(session('success'))
            <div class="mart-alert" role="status">
                {{ session('success') }}
            </div>
        @endif

        @unless(request()->routeIs('login', 'register'))
            @if($errors->any())
                <div class="mart-alert mart-alert-error" role="alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        @endunless

        @yield('content')

    </main>

    <!-- FOOTER -->
    <footer class="mart-footer">
        © {{ date('Y') }} UIN Mart · UIN Imam Bonjol Padang
    </footer>

    @stack('scripts')

</body>
</html>
