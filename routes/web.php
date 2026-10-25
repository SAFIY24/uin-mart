
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\Admin\ProductDiscountController;
use App\Http\Controllers\Admin\VoucherController;

use App\Http\Controllers\SellerApplicationController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\SellerProductController;

use App\Http\Controllers\Admin\SellerApplicationController as AdminSellerApplicationController;
use App\Http\Controllers\Admin\ProductApprovalController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\SellerOrderController;


/*
|--------------------------------------------------------------------------
| AUTH - PENGGUNA BELUM LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/masuk', [AuthController::class, 'loginForm'])
        ->name('login');

    Route::post('/masuk', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.submit');

    Route::get('/daftar', [AuthController::class, 'registerForm'])
        ->name('register');

    Route::post('/daftar', [AuthController::class, 'register'])
        ->middleware('throttle:6,1')
        ->name('register.submit');

});


/*
|--------------------------------------------------------------------------
| CUSTOMER DAN SELLER - AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | BERANDA
    |--------------------------------------------------------------------------
    */

    Route::get('/', [StoreController::class, 'index'])
        ->name('home');

    Route::get('/produk/{product}', [StoreController::class, 'show'])
        ->name('products.show');


    /*
    |--------------------------------------------------------------------------
    | PENDAFTARAN PENJUAL
    |--------------------------------------------------------------------------
    */

    Route::get('/daftar-penjual', [
        SellerApplicationController::class,
        'create'
    ])->name('seller.register');

    Route::post('/daftar-penjual', [
        SellerApplicationController::class,
        'store'
    ])->name('seller.register.store');


    /*
    |--------------------------------------------------------------------------
    | SELLER DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/toko-saya', [
        SellerDashboardController::class,
        'index'
    ])->name('seller.dashboard');


    /*
    |--------------------------------------------------------------------------
    | SELLER - MANAJEMEN PRODUK
    |--------------------------------------------------------------------------
    */

    Route::get('/toko-saya/produk/tambah', [
        SellerProductController::class,
        'create'
    ])->name('seller.products.create');

    Route::post('/toko-saya/produk', [
        SellerProductController::class,
        'store'
    ])->name('seller.products.store');

    Route::get('/toko-saya/produk/{product}/edit', [
        SellerProductController::class,
        'edit'
    ])->name('seller.products.edit');

    Route::put('/toko-saya/produk/{product}', [
        SellerProductController::class,
        'update'
    ])->name('seller.products.update');


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    Route::post('/keluar', [
        AuthController::class,
        'logout'
    ])->name('logout');


    /*
    |--------------------------------------------------------------------------
    | KERANJANG BELANJA
    |--------------------------------------------------------------------------
    */

    Route::get('/keranjang', [
        CartController::class,
        'index'
    ])->name('cart.index');

    Route::post('/keranjang', [
        CartController::class,
        'add'
    ])->name('cart.add');

    Route::patch('/keranjang', [
        CartController::class,
        'update'
    ])->name('cart.update');

    Route::delete('/keranjang/{product}', [
        CartController::class,
        'remove'
    ])->name('cart.remove');


    /*
    |--------------------------------------------------------------------------
    | CHECKOUT DAN VOUCHER
    |--------------------------------------------------------------------------
    */

    Route::post('/beli-sekarang', [
        CheckoutController::class,
        'buyNow'
    ])->name('checkout.buy');

    Route::get('/checkout', [
        CheckoutController::class,
        'index'
    ])->name('checkout.index');

    Route::post('/checkout', [
        CheckoutController::class,
        'store'
    ])->name('checkout.store');

    Route::post('/checkout/voucher', [
        CheckoutController::class,
        'applyVoucher'
    ])->name('checkout.voucher.apply');

    Route::get('/pesanan/{order}', [
        CheckoutController::class,
        'success'
    ])->name('checkout.success');

});


/*
|--------------------------------------------------------------------------
| ADMIN - KHUSUS ADMINISTRATOR
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | LAPORAN
        |--------------------------------------------------------------------------
        */

        Route::get('/laporan', [
            ReportController::class,
            'index'
        ])->name('reports.index');


        /*
        |--------------------------------------------------------------------------
        | ADMIN DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get('/', [
            AdminController::class,
            'index'
        ])->name('index');


        /*
        |--------------------------------------------------------------------------
        | PENGAJUAN PENJUAL
        |--------------------------------------------------------------------------
        */

        Route::get('/penjual', [
            AdminSellerApplicationController::class,
            'index'
        ])->name('sellers.index');

        Route::patch('/penjual/{application}', [
            AdminSellerApplicationController::class,
            'update'
        ])->name('sellers.update');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN MAHASISWA
        |--------------------------------------------------------------------------
        */

        Route::get('/mahasiswa', [
            UserManagementController::class,
            'index'
        ])->name('users.index');

        Route::get('/mahasiswa/{user}', [
            UserManagementController::class,
            'show'
        ])->name('users.show');


        /*
        |--------------------------------------------------------------------------
        | PERSETUJUAN PRODUK SELLER
        |--------------------------------------------------------------------------
        */

        Route::get('/persetujuan-produk', [
            ProductApprovalController::class,
            'index'
        ])->name('product-approvals.index');

        Route::patch('/persetujuan-produk/{product}', [
            ProductApprovalController::class,
            'update'
        ])->name('product-approvals.update');

        Route::delete('/persetujuan-produk/{product}', [
            ProductApprovalController::class,
            'destroy'
        ])->name('product-approvals.destroy');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN PRODUK ADMIN
        |--------------------------------------------------------------------------
        */

        Route::get('/produk', [
            AdminController::class,
            'products'
        ])->name('products.index');

        Route::get('/produk/tambah', [
            AdminController::class,
            'create'
        ])->name('products.create');

        Route::post('/produk', [
            AdminController::class,
            'store'
        ])->name('products.store');

        Route::get('/produk/{product}/edit', [
            AdminController::class,
            'edit'
        ])->name('products.edit');

        Route::put('/produk/{product}', [
            AdminController::class,
            'update'
        ])->name('products.update');

        Route::delete('/produk/{product}', [
            AdminController::class,
            'destroy'
        ])->name('products.destroy');

        Route::delete('/produk/{product}/hapus', [
            AdminController::class,
            'forceDestroy'
        ])->name('products.force-destroy');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN PESANAN ADMIN
        |--------------------------------------------------------------------------
        */

        Route::get('/pesanan', [
            AdminController::class,
            'orders'
        ])->name('orders');

        Route::get('/pesanan/{order}/detail', [
            AdminController::class,
            'showOrder'
        ])->name('orders.show');

        Route::patch('/pesanan/{order}', [
            AdminController::class,
            'updateOrder'
        ])->name('orders.update');


        
        /*
        |--------------------------------------------------------------------------
        | ADMIN - KONFIRMASI PEMBAYARAN
        |--------------------------------------------------------------------------
        |
        | Hanya Admin yang boleh mengonfirmasi pembayaran
        | untuk produk milik Admin sendiri.
        |
        */

        Route::post('/pesanan/{order}/konfirmasi-pembayaran', [
            AdminController::class,
            'confirmAdminPayment'
        ])->name('orders.payment.confirm');


        /*
        |--------------------------------------------------------------------------
        | ADMIN - PENYERAHAN PRODUK
        |--------------------------------------------------------------------------
        */

        Route::post('/pesanan/{order}/serahkan-produk-admin', [
            AdminController::class,
            'handoverAdminOrder'
        ])->name('orders.admin.handover');


        /*
        |--------------------------------------------------------------------------
        | DISKON PRODUK
        |--------------------------------------------------------------------------
        */

        Route::get('/promosi', [
            ProductDiscountController::class,
            'index'
        ])->name('promotions.index');

        Route::put('/promosi/produk/{product}', [
            ProductDiscountController::class,
            'update'
        ])->name('promotions.products.update');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN VOUCHER
        |--------------------------------------------------------------------------
        */

        Route::get('/promosi/voucher', [
            VoucherController::class,
            'index'
        ])->name('vouchers.index');

        Route::get('/promosi/voucher/tambah', [
            VoucherController::class,
            'create'
        ])->name('vouchers.create');

        Route::post('/promosi/voucher', [
            VoucherController::class,
            'store'
        ])->name('vouchers.store');

        Route::get('/promosi/voucher/{voucher}/edit', [
            VoucherController::class,
            'edit'
        ])->name('vouchers.edit');

        Route::put('/promosi/voucher/{voucher}', [
            VoucherController::class,
            'update'
        ])->name('vouchers.update');

        Route::delete('/promosi/voucher/{voucher}', [
            VoucherController::class,
            'destroy'
        ])->name('vouchers.destroy');

        Route::patch('/promosi/voucher/{voucher}/toggle', [
            VoucherController::class,
            'toggle'
        ])->name('vouchers.toggle');

        Route::patch('/promosi/voucher/{id}/restore', [
            VoucherController::class,
            'restore'
        ])
            ->whereNumber('id')
            ->name('vouchers.restore');

    });


/*
|--------------------------------------------------------------------------
| PESANAN CUSTOMER DAN SELLER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER - PESANAN SAYA
    |--------------------------------------------------------------------------
    */

    Route::get('/pesanan-saya', [
        CustomerOrderController::class,
        'index'
    ])->name('customer.orders.index');

    Route::get('/pesanan-saya/{order}', [
        CustomerOrderController::class,
        'show'
    ])->name('customer.orders.show');

    // Customer mengonfirmasi barang diterima.
    Route::post(
        '/pesanan-saya/{order}/terima/{sellerId}',
        [CustomerOrderController::class, 'confirmReceived']
    )
        ->whereNumber('sellerId')
        ->name('customer.orders.received');

        
    /*
    |--------------------------------------------------------------------------
    | CUSTOMER - KONFIRMASI PRODUK ADMIN DITERIMA
    |--------------------------------------------------------------------------
    */

    Route::post('/pesanan-saya/{order}/terima-admin', [
        CustomerOrderController::class,
        'confirmAdminReceived'
    ])->name('customer.orders.admin.received');



    /*
    |--------------------------------------------------------------------------
    | SELLER - PESANAN MASUK
    |--------------------------------------------------------------------------
    */

    Route::get('/toko-saya/pesanan', [
        SellerOrderController::class,
        'index'
    ])->name('seller.orders.index');

    Route::get('/toko-saya/pesanan/{order}', [
        SellerOrderController::class,
        'show'
    ])->name('seller.orders.show');


    /*
    |--------------------------------------------------------------------------
    | SELLER - PROSES PENGIRIMAN
    |--------------------------------------------------------------------------
    */

    // 1. Konfirmasi pesanan.
    Route::post('/toko-saya/pesanan/{order}/konfirmasi', [
        SellerOrderController::class,
        'confirm'
    ])->name('seller.orders.confirm');

    // 2. Mulai menyiapkan produk.
    Route::post('/toko-saya/pesanan/{order}/siapkan', [
        SellerOrderController::class,
        'prepare'
    ])->name('seller.orders.prepare');

    // 3. Produk siap dikirim atau diambil.
    Route::post('/toko-saya/pesanan/{order}/siap', [
        SellerOrderController::class,
        'ready'
    ])->name('seller.orders.ready');

    // 4. Barang sudah diserahkan.
    Route::post('/toko-saya/pesanan/{order}/serahkan', [
        SellerOrderController::class,
        'handover'
    ])->name('seller.orders.handover');


    /*
    |--------------------------------------------------------------------------
    | SELLER - JADWAL PENGIRIMAN / PENGAMBILAN
    |--------------------------------------------------------------------------
    |
    | Route baru untuk Seller mengajukan tanggal dan jam
    | pengiriman atau pengambilan barang.
    |
    */

    Route::post(
        '/toko-saya/pesanan/{order}/jadwal',
        [SellerOrderController::class, 'proposeSchedule']
    )->name('seller.orders.schedule.propose');

    
    // SELLER - Konfirmasi uang telah diterima
    Route::post(
        '/toko-saya/pesanan/{order}/konfirmasi-pembayaran',
        [SellerOrderController::class, 'confirmPayment']
    )->name('seller.orders.payment.confirm');


});
