
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin · UIN Mart')</title>

    <link rel="stylesheet"
          href="{{ asset('assets/uin-admin-dashboard.css') }}?v=4">

    <link rel="stylesheet"
          href="{{ asset('assets/uin-admin-v3.css') }}?v=3">

    @stack('styles')
</head>

<body class="ua-admin-body">

<div class="ua-shell">

    <!-- SIDEBAR ADMIN -->
    <aside class="ua-sidebar" aria-label="Menu Admin">

        <a class="ua-side-heading"
           href="{{ route('admin.index') }}"
           aria-label="Dashboard UIN Mart">

            <img
                class="ua-brand-logo ua-brand-logo-sidebar"
                src="{{ asset('assets/uin-logo.png') }}"
                alt="Logo UIN Imam Bonjol Padang"
                width="52"
                height="52"
            >

            <span class="ua-brand-label">
                <strong>UIN MART</strong>
                <small>ADMIN PANEL</small>
            </span>
        </a>

        <p class="ua-side-label">MENU UTAMA</p>

        <nav class="ua-nav">

            <!-- DASHBOARD -->
            <a
                class="ua-nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}"
                href="{{ route('admin.index') }}"
            >
                <span class="ua-nav-icon" aria-hidden="true">▦</span>
                <span>Dashboard</span>
            </a>

            <!-- MANAJEMEN PRODUK -->
            <a
                class="ua-nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                href="{{ route('admin.products.index') }}"
            >
                <span class="ua-nav-icon" aria-hidden="true">▣</span>
                <span>Produk</span>
            </a>

           
<!-- MANAJEMEN PESANAN -->
<a
    class="ua-nav-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}"
    href="{{ route('admin.orders') }}"
>
    <span class="ua-nav-icon" aria-hidden="true">▤</span>
    <span>Pesanan</span>
</a>

<!-- PENGAJUAN PENJUAL -->
<a
    class="ua-nav-item {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}"
    href="{{ route('admin.sellers.index') }}"
>
    <span class="ua-nav-icon" aria-hidden="true">♧</span>
    <span>Pengajuan Penjual</span>
</a>

<!-- PERSETUJUAN PRODUK -->
<a
    class="ua-nav-item {{ request()->routeIs('admin.product-approvals.*') ? 'active' : '' }}"
    href="{{ route('admin.product-approvals.index') }}"
>
    <span class="ua-nav-icon" aria-hidden="true">▣</span>
    <span>Persetujuan Produk</span>
</a>

 
<!-- MANAJEMEN MAHASISWA -->
<a
    class="ua-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
    href="{{ route('admin.users.index') }}"
>
    <span class="ua-nav-icon" aria-hidden="true">♟</span>
    <span>Mahasiswa</span>
</a>

            <!-- DISKON & PROMOSI -->
            
<a
    class="ua-nav-item {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}"
    href="{{ route('admin.promotions.index') }}"
>
    <span class="ua-nav-icon">%</span>
    <span>Diskon & Promo</span>
</a>


  
<!-- LAPORAN -->
<a
    href="{{ route('admin.reports.index') }}"
    class="ua-nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
>
    <span class="ua-nav-icon" aria-hidden="true">▥</span>
    <span>Laporan</span>
</a>


        <!-- SIDEBAR FOOTER -->
        <div class="ua-side-bottom">

            <small>UIN Imam Bonjol Padang</small>
        </div>

    </aside>

    <!-- MAIN WORKSPACE -->
    <div class="ua-workspace">

        <!-- HEADER ADMIN -->
        <header class="ua-topbar">

            <div class="ua-topbar-brand">
                <img
                    class="ua-brand-logo ua-brand-logo-topbar"
                    src="{{ asset('assets/uin-logo.png') }}"
                    alt="Logo UIN Imam Bonjol Padang"
                    width="42"
                    height="42"
                >

                <span>
                    UIN MART <b>ADMIN</b>
                </span>
            </div>

            <div class="ua-topbar-right">

                <span class="ua-admin-name">
                    Halo, {{ auth()->user()?->username ?? 'Admin' }}
                </span>

                <a
                    class="ua-store-link"
                    href="{{ route('home') }}"
                >
                    Lihat Toko
                </a>

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                >
                    @csrf

                    <button class="ua-logout" type="submit">
                        Keluar
                    </button>
                </form>

            </div>
        </header>

        <!-- NỘI DUNG TRANG ADMIN -->
        <main class="ua-main">

            @if(session('success'))
                <div class="ua-alert ua-alert-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="ua-alert ua-alert-danger" role="alert">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('admin_content')

        </main>

        <!-- FOOTER ADMIN -->
        <footer class="ua-footer">
            © {{ date('Y') }} UIN Mart · UIN Imam Bonjol Padang
        </footer>

    </div>

</div>

@stack('scripts')

</body>
</html>
