<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Promotion;
use App\Services\ProductPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMIN DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $pricing = app(ProductPricing::class);

        $products = Product::with(['category', 'discount'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(
                    'name',
                    'like',
                    '%' . $request->input('q') . '%'
                );
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $allProducts = Product::with('discount')->get();

        $discountedProducts = $allProducts->filter(
            fn ($product) =>
                $product->status === 'active'
                && $pricing->discountPercentage($product) > 0
        );

        $allVouchers = Promotion::all();

        $activeVouchers = $allVouchers->filter(
            fn ($voucher) => $voucher->isCurrentlyActive()
        );

        $stats = [
            'products' => $allProducts->count(),
            'active' => $allProducts->where('status', 'active')->count(),
            'low' => $allProducts
                ->where('status', 'active')
                ->where('stock', '<', 5)
                ->count(),
            'orders' => Order::count(),
            'discounted' => $discountedProducts->count(),
            'vouchers' => $allVouchers->count(),
            'active_vouchers' => $activeVouchers->count(),
        ];

        $lowStockProducts = Product::where('status', 'active')
            ->where('stock', '<', 5)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $now = now();
        $deadline = now()->addDays(7);

        $upcomingPromos = collect();

        foreach ($discountedProducts as $product) {
            $discount = $product->discount;

            if (
                $discount
                && $discount->ends_at
                && $discount->ends_at->between($now, $deadline)
            ) {
                $upcomingPromos->push([
                    'type' => 'Diskon Produk',
                    'name' => $product->name,
                    'ends_at' => $discount->ends_at,
                    'url' => route('admin.promotions.index'),
                ]);
            }
        }

        foreach ($activeVouchers as $voucher) {
            if (
                $voucher->ends_at
                && $voucher->ends_at->between($now, $deadline)
            ) {
                $upcomingPromos->push([
                    'type' => 'Voucher',
                    'name' => $voucher->code,
                    'ends_at' => $voucher->ends_at,
                    'url' => route('admin.vouchers.edit', $voucher),
                ]);
            }
        }

        $upcomingPromos = $upcomingPromos
            ->sortBy('ends_at')
            ->take(5)
            ->values();

        $activeVoucherCodes = $activeVouchers
            ->take(3)
            ->values();

        return view('admin.index', compact(
            'products',
            'stats',
            'lowStockProducts',
            'upcomingPromos',
            'activeVoucherCodes'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MANAJEMEN PRODUK
    |--------------------------------------------------------------------------
    */

    public function products(Request $request)
    {
        $products = Product::with(['category', 'discount'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(
                    'name',
                    'like',
                    '%' . $request->input('q') . '%'
                );
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'products' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'low' => Product::where('stock', '<', 5)->count(),
        ];

        return view('admin.products', compact('products', 'stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH PRODUK
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT PRODUK
    |--------------------------------------------------------------------------
    */

    public function edit(Product $product)
    {
        $product->load('images');

        return view('admin.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI PRODUK
    |--------------------------------------------------------------------------
    */

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',

            // Foto utama
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Maksimal tiga posisi foto tambahan
            'extra_photos' => 'nullable|array|max:3',
            'extra_photos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN PRODUK BARU
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $extraUploads = $request->file('extra_photos', []);

        foreach ($extraUploads as $index => $file) {
            if (!in_array((string) $index, ['0', '1', '2'], true)) {
                throw ValidationException::withMessages([
                    'extra_photos' => 'Posisi foto tambahan tidak valid.',
                ]);
            }
        }

        unset($data['photo'], $data['extra_photos']);

        $uploadedPaths = [];
        $extraPaths = [];

        try {
            // Upload foto utama
            if ($request->hasFile('photo')) {
                $mainPath = $request->file('photo')
                    ->store('products', 'public');

                if (!$mainPath) {
                    throw new \RuntimeException(
                        'Gagal mengunggah foto utama.'
                    );
                }

                $uploadedPaths[] = $mainPath;
                $data['image'] = $mainPath;
            }

            // Upload foto tambahan
            foreach ($extraUploads as $index => $file) {
                $path = $file->store('products', 'public');

                if (!$path) {
                    throw new \RuntimeException(
                        'Gagal mengunggah foto tambahan.'
                    );
                }

                $uploadedPaths[] = $path;
                $extraPaths[(int) $index] = $path;
            }

            DB::transaction(function () use ($data, $extraPaths) {
                $product = Product::create($data);

                foreach ($extraPaths as $index => $path) {
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index + 1,
                    ]);
                }
            });

        } catch (\Throwable $e) {
            // Bersihkan file baru jika penyimpanan gagal
            if (!empty($uploadedPaths)) {
                Storage::disk('public')->delete($uploadedPaths);
            }

            throw $e;
        }

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Produk berhasil ditambahkan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PRODUK
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);

        $extraUploads = $request->file('extra_photos', []);

        foreach ($extraUploads as $index => $file) {
            if (!in_array((string) $index, ['0', '1', '2'], true)) {
                throw ValidationException::withMessages([
                    'extra_photos' => 'Posisi foto tambahan tidak valid.',
                ]);
            }
        }

        unset($data['photo'], $data['extra_photos']);

        $oldMainImage = $product->image;
        $newMainImage = null;

        $uploadedPaths = [];
        $newExtraPaths = [];
        $oldExtraPaths = [];

        try {
            // Upload foto utama baru jika dipilih
            if ($request->hasFile('photo')) {
                $newMainImage = $request->file('photo')
                    ->store('products', 'public');

                if (!$newMainImage) {
                    throw new \RuntimeException(
                        'Gagal mengunggah foto utama.'
                    );
                }

                $uploadedPaths[] = $newMainImage;
                $data['image'] = $newMainImage;
            }

            // Upload hanya foto tambahan yang dipilih
            foreach ($extraUploads as $index => $file) {
                $path = $file->store('products', 'public');

                if (!$path) {
                    throw new \RuntimeException(
                        'Gagal mengunggah foto tambahan.'
                    );
                }

                $uploadedPaths[] = $path;
                $newExtraPaths[(int) $index] = $path;
            }

            DB::transaction(function () use (
                $product,
                $data,
                $newExtraPaths,
                &$oldExtraPaths
            ) {
                // Simpan perubahan data produk
                $product->update($data);

                // Perbarui masing-masing posisi foto tambahan
                foreach ($newExtraPaths as $index => $path) {
                    $position = $index + 1;

                    $existingImage = $product->images()
                        ->where('sort_order', $position)
                        ->first();

                    if ($existingImage) {
                        $oldExtraPaths[] = $existingImage->image_path;

                        $existingImage->update([
                            'image_path' => $path,
                        ]);
                    } else {
                        $product->images()->create([
                            'image_path' => $path,
                            'sort_order' => $position,
                        ]);
                    }
                }
            });

        } catch (\Throwable $e) {
            // Hapus file baru apabila transaksi gagal
            if (!empty($uploadedPaths)) {
                Storage::disk('public')->delete($uploadedPaths);
            }

            throw $e;
        }

        // Hapus foto utama lama setelah berhasil disimpan
        if (
            $newMainImage
            && $oldMainImage
            && $oldMainImage !== $newMainImage
            && str_starts_with($oldMainImage, 'products/')
        ) {
            Storage::disk('public')->delete($oldMainImage);
        }

        // Hapus foto tambahan yang telah diganti
        if (!empty($oldExtraPaths)) {
            Storage::disk('public')->delete($oldExtraPaths);
        }

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Produk berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NONAKTIFKAN PRODUK
    |--------------------------------------------------------------------------
    */

    public function destroy(Product $product)
    {
        if ($product->stock > 0) {
            return back()->withErrors([
                'product' => 'Kosongkan stok sebelum menonaktifkan produk.',
            ]);
        }

        $product->update([
            'status' => 'inactive',
        ]);

        return back()->with(
            'success',
            'Produk dinonaktifkan agar riwayat pesanan tetap aman.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS PERMANEN PRODUK ADMIN
    |--------------------------------------------------------------------------
    */

    public function forceDestroy(Product $product)
    {
        // Hanya produk milik Admin yang boleh dihapus
        abort_unless($product->seller_id === null, 403);

        // Produk dengan riwayat pesanan tidak boleh dihapus
        $hasOrders = DB::table('order_items')
            ->where('product_id', $product->id)
            ->exists();

        if ($hasOrders) {
            return back()->withErrors([
                'product' => 'Produk memiliki riwayat pesanan. Gunakan Nonaktifkan.',
            ]);
        }

        try {
            $product->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors([
                'product' => 'Produk tidak dapat dihapus karena masih terhubung dengan data lain.',
            ]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Produk berhasil dihapus.'
            );
    }

  
    /*
    |--------------------------------------------------------------------------
    | MANAJEMEN PESANAN
    |--------------------------------------------------------------------------
    */

    public function orders()
    {
        $orders = Order::with('items')
            ->latest('id')
            ->paginate(15);

        return view(
            'admin.orders',
            compact('orders')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL PESANAN
    |--------------------------------------------------------------------------
    */

    public function showOrder(Order $order)
    {
        $order->load('items');

        // Seluruh pembagian pembayaran pesanan.
        $paymentAllocations = DB::table('order_payment_allocations')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        // Tagihan khusus produk yang dijual Admin.
        $adminPayment = $paymentAllocations->first(
            fn ($payment) =>
                $payment->recipient_type === 'admin'
                && (int) $payment->recipient_id === 0
        );

        return view(
            'admin.order-detail',
            compact(
                'order',
                'paymentAllocations',
                'adminPayment'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS PESANAN
    |--------------------------------------------------------------------------
    |
    | Admin hanya mengubah status pengiriman pesanan.
    | Status pembayaran tidak boleh diperbarui manual.
    |
    */

    public function updateOrder(Request $request, Order $order)
    {
        $data = $request->validate([
            'order_status' =>
                'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        DB::transaction(function () use ($order, $data) {

            $currentOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $sellerIds = $currentOrder->items()
                ->whereNotNull('seller_id')
                ->pluck('seller_id')
                ->unique();

            $completedSellerIds = DB::table(
                'seller_order_fulfillments'
            )
                ->where('order_id', $currentOrder->id)
                ->whereIn('seller_id', $sellerIds->all())
                ->where('status', 'completed')
                ->pluck('seller_id')
                ->unique();

            // Semua Seller harus menyelesaikan pengiriman.
            if (
                $data['order_status'] === 'completed'
                && $sellerIds->diff($completedSellerIds)->isNotEmpty()
            ) {
                throw ValidationException::withMessages([
                    'order_status' =>
                        'Pengiriman Seller belum semuanya selesai.',
                ]);
            }

            // Pesanan selesai tidak bisa dibuka kembali.
            if (
                $currentOrder->order_status === 'completed'
                && $data['order_status'] !== 'completed'
            ) {
                throw ValidationException::withMessages([
                    'order_status' =>
                        'Pesanan selesai tidak dapat dibuka kembali.',
                ]);
            }

            // Pesanan yang telah dibatalkan tidak boleh
            // dibuka kembali lewat perubahan status biasa.
            if (
                $currentOrder->order_status === 'cancelled'
                && $data['order_status'] !== 'cancelled'
            ) {
                throw ValidationException::withMessages([
                    'order_status' =>
                        'Pesanan dibatalkan tidak dapat dibuka kembali.',
                ]);
            }

            if ($data['order_status'] === 'cancelled') {

                $hasReceivedItems = DB::table(
                    'seller_order_fulfillments'
                )
                    ->where('order_id', $currentOrder->id)
                    ->where('status', 'completed')
                    ->exists();

                $hasPaidAllocation = DB::table(
                    'order_payment_allocations'
                )
                    ->where('order_id', $currentOrder->id)
                    ->where('status', 'paid')
                    ->exists();

                if (
                    $hasReceivedItems
                    || $hasPaidAllocation
                    || $currentOrder->payment_status === 'paid'
                ) {
                    throw ValidationException::withMessages([
                        'order_status' =>
                            'Pesanan yang sudah diterima atau dibayar memerlukan proses penanganan khusus.',
                    ]);
                }
            }

            // Status pembayaran tidak diubah di sini.
            $currentOrder->update([
                'order_status' => $data['order_status'],
            ]);
        });

        return back()->with(
            'success',
            'Status pesanan berhasil diperbarui.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI PEMBAYARAN ADMIN
    |--------------------------------------------------------------------------
    |
    | Admin hanya dapat mengonfirmasi pembayaran
    | produk milik Admin (seller_id = NULL).
    |
    | Tagihan Seller lain tidak boleh diubah.
    |
    | Pesanan utama menjadi PAID hanya setelah
    | seluruh alokasi pembayaran dikonfirmasi.
    |
    */

    public function confirmAdminPayment(Request $request, Order $order)
    {
        DB::transaction(function () use ($order, $request) {

            $currentOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Pesanan dibatalkan tidak dapat dibayar.
            if ($currentOrder->order_status === 'cancelled') {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Pesanan sudah dibatalkan.',
                ]);
            }

            // Pembayaran utama harus masih pending.
            if ($currentOrder->payment_status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Status pembayaran pesanan tidak mengizinkan konfirmasi.',
                ]);
            }

            // Pastikan ada produk Admin di pesanan.
            $hasAdminItems = $currentOrder->items()
                ->whereNull('seller_id')
                ->exists();

            if (!$hasAdminItems) {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Pesanan tidak memiliki produk Admin.',
                ]);
            }

            // Kunci seluruh alokasi pembayaran.
            $payments = DB::table('order_payment_allocations')
                ->where('order_id', $currentOrder->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $adminPayment = $payments->first(
                fn ($payment) =>
                    $payment->recipient_type === 'admin'
                    && (int) $payment->recipient_id === 0
            );

            if (!$adminPayment) {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Tagihan Admin tidak ditemukan.',
                ]);
            }

            // Cegah konfirmasi ganda.
            if ($adminPayment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Pembayaran Admin sudah dikonfirmasi sebelumnya.',
                ]);
            }

            // Validasi total seluruh tagihan.
            $totalAllocated = $payments->sum(
                fn ($payment) => (int) $payment->amount_due
            );

            $orderTotal = (int) round(
                (float) $currentOrder->total
            );

            if (
                $payments->isEmpty()
                || $totalAllocated !== $orderTotal
            ) {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Total alokasi pembayaran tidak sesuai.',
                ]);
            }

            // Tandai hanya bagian Admin sebagai PAID.
            DB::table('order_payment_allocations')
                ->where('id', $adminPayment->id)
                ->update([
                    'status' => 'paid',
                    'confirmed_by' => $request->user()->id,
                    'confirmed_at' => now(),
                    'updated_at' => now(),
                ]);

            // Hitung tagihan lain yang belum dibayar.
            $remaining = DB::table('order_payment_allocations')
                ->where('order_id', $currentOrder->id)
                ->where('status', '!=', 'paid')
                ->count();

            // Semua penerima telah mengonfirmasi.
            if ($remaining === 0) {
                $currentOrder->payment_status = 'paid';
                $currentOrder->save();
            }
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with(
                'success',
                'Pembayaran produk Admin berhasil dikonfirmasi.'
            );
    }


/*
|--------------------------------------------------------------------------
| ADMIN - KONFIRMASI BARANG SUDAH DISERAHKAN
|--------------------------------------------------------------------------
*/

public function handoverAdminOrder(Request $request, Order $order)
{
    DB::transaction(function () use ($order) {
        $currentOrder = Order::query()
            ->whereKey($order->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (in_array(
            $currentOrder->order_status,
            ['cancelled', 'completed'],
            true
        )) {
            throw ValidationException::withMessages([
                'delivery' =>
                    'Pesanan sudah ditutup.',
            ]);
        }

        $hasAdminItems = $currentOrder->items()
            ->whereNull('seller_id')
            ->exists();

        abort_unless($hasAdminItems, 404);

        $fulfillment = DB::table('admin_order_fulfillments')
            ->where('order_id', $currentOrder->id)
            ->lockForUpdate()
            ->first();

        if (!$fulfillment) {
            throw ValidationException::withMessages([
                'delivery' =>
                    'Data pengiriman Admin tidak ditemukan.',
            ]);
        }

        if ($fulfillment->status !== 'pending') {
            throw ValidationException::withMessages([
                'delivery' =>
                    'Produk Admin sudah ditangani sebelumnya.',
            ]);
        }

        DB::table('admin_order_fulfillments')
            ->where('id', $fulfillment->id)
            ->update([
                'status' => 'handed_over',
                'handed_over_at' => now(),
                'updated_at' => now(),
            ]);
    });

    return redirect()
        ->route('admin.orders.show', $order)
        ->with(
            'success',
            'Produk Admin sudah diserahkan. Menunggu konfirmasi pembeli.'
        );
}


}


