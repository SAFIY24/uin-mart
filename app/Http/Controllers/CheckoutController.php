<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\SellerApplication;
use App\Services\ProductPricing;
use App\Services\VoucherService;
use App\Services\OrderPaymentAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VALIDASI PRODUK MARKETPLACE
    |--------------------------------------------------------------------------
    */

    private function isAvailable(Product $product): bool
    {
        if (
            $product->status !== 'active' ||
            $product->approval_status !== 'approved'
        ) {
            return false;
        }

        // Produk Admin tidak memiliki seller_id.
        if ($product->seller_id === null) {
            return true;
        }

        // Produk Seller hanya tersedia jika penjual disetujui.
        return SellerApplication::query()
            ->where('user_id', $product->seller_id)
            ->where('status', 'approved')
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | BELI SEKARANG
    |--------------------------------------------------------------------------
    */

    public function buyNow(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        $product = Product::findOrFail($data['product_id']);

        abort_unless($this->isAvailable($product), 404);

        if ((int) $data['quantity'] > $product->stock) {
            return back()->withErrors([
                'stock' => 'Stok tidak mencukupi.',
            ]);
        }

        $request->session()->put('buy_now', [
            $product->id => (int) $data['quantity'],
        ]);

        $request->session()->put(
            'checkout_source',
            'buy_now'
        );

        return redirect()->route('checkout.index');
    }

    /*
    |--------------------------------------------------------------------------
    | SUMBER CHECKOUT
    |--------------------------------------------------------------------------
    */

    private function cart(Request $request): array
    {
        if (
            $request->session()->get('checkout_source')
            === 'buy_now'
        ) {
            return $request->session()->get('buy_now', []);
        }

        return $request->session()->get('cart', []);
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN CHECKOUT
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        ProductPricing $pricing
    ) {
        if ($request->query('source') === 'cart') {
            $request->session()->put('checkout_source', 'cart');
            $request->session()->forget('buy_now');
        }

        $cart = $this->cart($request);

        if (!$cart) {
            return redirect()->route('cart.index');
        }

        // Produk harus aktif dan disetujui Admin.
        $items = Product::with('discount')
            ->whereIn('id', array_keys($cart))
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->where(function ($query) {
                $query->whereNull('seller_id')
                    ->orWhereIn(
                        'seller_id',
                        SellerApplication::query()
                            ->select('user_id')
                            ->where('status', 'approved')
                    );
            })
            ->get();

        // Tidak boleh checkout sebagian.
        if ($items->count() !== count($cart)) {
            return redirect()
                ->route('cart.index')
                ->withErrors([
                    'stock' =>
                        'Beberapa produk tidak tersedia atau belum disetujui Admin.',
                ]);
        }

        foreach ($items as $product) {
            $quantity = (int) ($cart[$product->id] ?? 0);

            if (
                $quantity < 1 ||
                $quantity > 999 ||
                $product->stock < $quantity
            ) {
                return redirect()
                    ->route('cart.index')
                    ->withErrors([
                        'stock' =>
                            'Stok tidak mencukupi: ' . $product->name,
                    ]);
            }

            // Harga dan diskon produk.
            $originalPrice = $pricing->originalPrice($product);
            $finalPrice = $pricing->finalPrice($product);
            $discountPercentage =
                $pricing->discountPercentage($product);

            $product->cart_quantity = $quantity;
            $product->original_price = $originalPrice;
            $product->final_price = $finalPrice;
            $product->discount_percentage =
                $discountPercentage;

            // Harga hanya diubah pada object untuk tampilan.
            $product->price = $finalPrice;
            $product->subtotal = round(
                $finalPrice * $quantity,
                2
            );
        }

        /*
        |--------------------------------------------------------------------------
        | HITUNG DISKON PRODUK
        |--------------------------------------------------------------------------
        */

        $subtotalAfterProductDiscount = round(
            (float) $items->sum('subtotal'),
            2
        );

        $hasProductDiscount = $items->contains(
            function ($product) {
                return round(
                    (float) $product->final_price,
                    2
                ) < round(
                    (float) $product->original_price,
                    2
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | HITUNG VOUCHER
        |--------------------------------------------------------------------------
        */

        $voucherCode = null;
        $voucherDiscount = 0;
        $voucherTotal = $subtotalAfterProductDiscount;
        $voucherError = null;

        $savedCode = $request->session()
            ->get('checkout_voucher');

        if ($savedCode) {
            try {
                $result = app(VoucherService::class)
                    ->calculate(
                        $savedCode,
                        $subtotalAfterProductDiscount,
                        $hasProductDiscount
                    );

                $voucherCode = $result['voucher']->code;
                $voucherDiscount = $result['discount'];
                $voucherTotal = $result['total'];

            } catch (ValidationException $e) {
                $request->session()
                    ->forget('checkout_voucher');

                $voucherError = $e->validator
                    ->errors()
                    ->first('promotion_code');
            }
        }

        return view('checkout.index', compact(
            'items',
            'voucherCode',
            'voucherDiscount',
            'voucherTotal',
            'voucherError'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI DAN SIMPAN PESANAN
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        ProductPricing $pricing,
        OrderPaymentAllocationService $paymentAllocationService
    ) {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI CHECKOUT
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'customer_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',

            'payment_method' =>
                'required|in:cod,bank_transfer',

            'delivery_method' =>
                'required|in:pickup,seller_delivery',

            'pickup_location' => [
                'required_if:delivery_method,pickup',
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $cart = $this->cart($request);

        if (!$cart) {
            return redirect()->route('cart.index');
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI DATABASE
        |--------------------------------------------------------------------------
        */

        $order = DB::transaction(function () use (
            $request,
            $cart,
            $data,
            $pricing,
            $paymentAllocationService
        ) {
            $total = 0;
            $details = [];
            $hasProductDiscount = false;

            // Urutkan produk sebelum dikunci.
            ksort($cart);

            /*
            |--------------------------------------------------------------------------
            | PERIKSA PRODUK DAN STOK
            |--------------------------------------------------------------------------
            */

            foreach ($cart as $id => $qty) {
                if (
                    !filter_var($id, FILTER_VALIDATE_INT) ||
                    (
                        !is_int($qty) &&
                        !ctype_digit((string) $qty)
                    ) ||
                    (int) $qty < 1 ||
                    (int) $qty > 999
                ) {
                    throw ValidationException::withMessages([
                        'cart' => 'Keranjang tidak valid.',
                    ]);
                }

                $qty = (int) $qty;

                // Kunci produk selama transaksi.
                $product = Product::query()
                    ->where('status', 'active')
                    ->where('approval_status', 'approved')
                    ->lockForUpdate()
                    ->find($id);

                if (!$product || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'cart' =>
                            'Produk tidak tersedia atau stok tidak mencukupi.',
                    ]);
                }

                // Pastikan Seller masih disetujui Admin.
                if ($product->seller_id !== null) {
                    $sellerApproved = SellerApplication::query()
                        ->where('user_id', $product->seller_id)
                        ->where('status', 'approved')
                        ->lockForUpdate()
                        ->first();

                    if (!$sellerApproved) {
                        throw ValidationException::withMessages([
                            'cart' =>
                                'Penjual produk tidak tersedia.',
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | HITUNG HARGA PRODUK
                |--------------------------------------------------------------------------
                */

                $product->load('discount');

                $unitPrice = $pricing->finalPrice($product);
                $originalPrice = $pricing->originalPrice($product);

                $productDiscountAmount = round(
                    max(0, $originalPrice - $unitPrice) * $qty,
                    2
                );

                if (
                    round((float) $unitPrice, 2)
                    < round((float) $originalPrice, 2)
                ) {
                    $hasProductDiscount = true;
                }

                $subtotal = round(
                    $unitPrice * $qty,
                    2
                );

                $total = round(
                    $total + $subtotal,
                    2
                );

                $details[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'original_price' => $originalPrice,
                    'product_discount_amount' =>
                        $productDiscountAmount,
                    'subtotal' => $subtotal,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | HITUNG VOUCHER TERBARU
            |--------------------------------------------------------------------------
            */

            $subtotalBeforeVoucher = $total;
            $voucherDiscount = 0;
            $voucher = null;

            $voucherCode = $request->session()
                ->get('checkout_voucher');

            if ($voucherCode) {
                $voucher = \App\Models\Promotion::query()
                    ->where('code', $voucherCode)
                    ->lockForUpdate()
                    ->first();

                if (!$voucher) {
                    throw ValidationException::withMessages([
                        'promotion_code' =>
                            'Voucher tidak tersedia.',
                    ]);
                }

                $result = app(VoucherService::class)
                    ->calculate(
                        $voucherCode,
                        $subtotalBeforeVoucher,
                        $hasProductDiscount
                    );

                $voucherDiscount = $result['discount'];
                $total = $result['total'];
            }

            /*
            |--------------------------------------------------------------------------
            | BUAT PESANAN UTAMA
            |--------------------------------------------------------------------------
            */

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'],

                'user_id' => $request->user()->id,

                'order_number' =>
                    'UIN'
                    . now()->format('YmdHis')
                    . strtoupper(Str::random(6)),

                'subtotal' => $subtotalBeforeVoucher,

                'discount_amount' => $voucherDiscount,
                'promotion_id' => $voucher?->id,
                'promotion_code' => $voucher?->code,

                'total' => $total,

                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            /*
            |--------------------------------------------------------------------------
            | SIMPAN ORDER ITEMS DAN KURANGI STOK
            |--------------------------------------------------------------------------
            */

            foreach ($details as $detail) {
                $product = $detail['product'];
                $quantity = $detail['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,

                    // Seller saat transaksi dibuat.
                    'seller_id' => $product->seller_id,

                    'product_name' => $product->name,

                    'price' => $detail['unit_price'],
                    'quantity' => $quantity,
                    'subtotal' => $detail['subtotal'],

                    'original_price' =>
                        $detail['original_price'],

                    'product_discount_amount' =>
                        $detail['product_discount_amount'],
                ]);

                $product->decrement('stock', $quantity);
            }

            /*
            |--------------------------------------------------------------------------
            | BUAT PENGIRIMAN UNTUK SETIAP SELLER
            |--------------------------------------------------------------------------
            */

            $sellerIds = collect($details)
                ->pluck('product')
                ->pluck('seller_id')
                ->filter(fn ($id) => $id !== null)
                ->unique()
                ->values();

            foreach ($sellerIds as $sellerId) {

                // Metode pengiriman dipilih Customer.
                $deliveryMethod = $data['delivery_method'];

                // Jika diantar, simpan alamat.
                $deliveryAddress =
                    $deliveryMethod === 'seller_delivery'
                        ? $data['address']
                        : null;

                // Jika ambil di kampus, simpan lokasi.
                $pickupLocation =
                    $deliveryMethod === 'pickup'
                        ? $data['pickup_location']
                        : null;

                DB::table('seller_order_fulfillments')
                    ->insert([
                        'order_id' => $order->id,

                        'seller_id' => $sellerId,

                        'delivery_method' =>
                            $deliveryMethod,

                        'status' => 'pending',

                        'delivery_address' =>
                            $deliveryAddress,

                        'pickup_location' =>
                            $pickupLocation,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | BARU: ALOKASI PEMBAYARAN MARKETPLACE
            |--------------------------------------------------------------------------
            |
            | Hitung tagihan tiap Seller dan produk Admin.
            | Voucher dialokasikan secara proporsional.
            |
            | Tetap di dalam transaksi Checkout agar jika
            | alokasi gagal, seluruh pesanan dibatalkan.
            |
            */

            
// Buat pengiriman Admin jika pesanan
// memiliki produk yang dijual langsung oleh Admin.
$hasAdminItems = collect($details)->contains(
    fn ($detail) => $detail['product']->seller_id === null
);

if ($hasAdminItems) {
    DB::table('admin_order_fulfillments')->insert([
        'order_id' => $order->id,
        'status' => 'pending',
        'handed_over_at' => null,
        'completed_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}


            $paymentAllocationService->createForOrder($order);

            /*
            |--------------------------------------------------------------------------
            | CATAT PENGGUNAAN VOUCHER
            |--------------------------------------------------------------------------
            */

            if ($voucher) {
                $voucher->increment('used_count');
            }

            return $order;
        });

        /*
        |--------------------------------------------------------------------------
        | BERSIHKAN SESSION SETELAH BERHASIL
        |--------------------------------------------------------------------------
        */

        if (
            $request->session()->get('checkout_source')
            === 'buy_now'
        ) {
            $request->session()->forget('buy_now');
        } else {
            $request->session()->forget('cart');
        }

        $request->session()->forget('checkout_source');
        $request->session()->forget('checkout_voucher');

        return redirect()
            ->route('checkout.success', $order);
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN PESANAN BERHASIL
    |--------------------------------------------------------------------------
    */

    public function success(
        Request $request,
        Order $order
    ) {
        abort_unless(
            (int) $order->user_id
                === (int) $request->user()->id,
            403
        );

        return view('checkout.success', compact('order'));
    }

    /*
    |--------------------------------------------------------------------------
    | TERAPKAN VOUCHER
    |--------------------------------------------------------------------------
    */

    public function applyVoucher(
        Request $request,
        ProductPricing $pricing,
        VoucherService $voucherService
    ) {
        $data = $request->validate([
            'promotion_code' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        $cart = $this->cart($request);

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        // Ambil produk aktif yang telah disetujui.
        $products = Product::with('discount')
            ->whereIn('id', array_keys($cart))
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->get();

        if ($products->count() !== count($cart)) {
            return back()->withErrors([
                'promotion_code' => 'Produk tidak tersedia.',
            ]);
        }

        $subtotal = 0;
        $hasProductDiscount = false;

        foreach ($products as $product) {

            if (!$this->isAvailable($product)) {
                return back()->withErrors([
                    'promotion_code' => 'Produk tidak tersedia.',
                ]);
            }

            $quantity = (int) ($cart[$product->id] ?? 0);

            if (
                $quantity < 1 ||
                $quantity > 999 ||
                $quantity > $product->stock
            ) {
                return back()->withErrors([
                    'promotion_code' =>
                        'Stok produk tidak mencukupi.',
                ]);
            }

            $originalPrice =
                $pricing->originalPrice($product);

            $finalPrice =
                $pricing->finalPrice($product);

            if ($finalPrice < $originalPrice) {
                $hasProductDiscount = true;
            }

            $subtotal += $finalPrice * $quantity;
        }

        $result = $voucherService->calculate(
            $data['promotion_code'],
            round($subtotal, 2),
            $hasProductDiscount
        );

        $request->session()->put(
            'checkout_voucher',
            $result['voucher']->code
        );

        return redirect()
            ->route('checkout.index')
            ->with(
                'success_voucher',
                'Voucher berhasil diterapkan.'
            );
    }
}
