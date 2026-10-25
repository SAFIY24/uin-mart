<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SellerApplication;
use App\Services\ProductPricing;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CHECK PRODUCT AVAILABILITY
    |--------------------------------------------------------------------------
    */

    public static function isAvailable(Product $product): bool
    {
        // Produk harus aktif dan disetujui Admin.
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

        // Produk Seller hanya tersedia jika akun penjual disetujui.
        return SellerApplication::query()
            ->where('user_id', $product->seller_id)
            ->where('status', 'approved')
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | GET CART ITEMS
    |--------------------------------------------------------------------------
    */

    public static function items(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return collect();
        }

        $pricing = app(ProductPricing::class);

        // Ambil hanya produk yang boleh dijual.
        $products = Product::with('discount')
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

        return $products->map(function ($product) use ($cart, $pricing) {

            $quantity = (int) ($cart[$product->id] ?? 0);

            $originalPrice = $pricing->originalPrice($product);
            $finalPrice = $pricing->finalPrice($product);
            $discountPercentage = $pricing->discountPercentage($product);

            // Informasi tambahan untuk tampilan keranjang.
            $product->cart_quantity = $quantity;
            $product->original_price = $originalPrice;
            $product->final_price = $finalPrice;
            $product->discount_percentage = $discountPercentage;

            // Harga yang digunakan dalam keranjang.
            $product->price = $finalPrice;
            $product->subtotal = round($finalPrice * $quantity, 2);

            return $product;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CART PAGE
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $request->session()->forget('buy_now');

        $items = self::items($request);

        return view('cart.index', compact('items'));
    }

    /*
    |--------------------------------------------------------------------------
    | ADD TO CART
    |--------------------------------------------------------------------------
    */

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        $product = Product::findOrFail($data['product_id']);

        // Produk harus aktif, disetujui, dan penjual masih diizinkan.
        abort_unless(self::isAvailable($product), 404);

        $cart = $request->session()->get('cart', []);

        $newQuantity = (int) ($cart[$product->id] ?? 0)
            + (int) $data['quantity'];

        if ($newQuantity > $product->stock) {
            return back()->withErrors([
                'stock' => 'Stok tidak mencukupi.',
            ]);
        }

        $cart[$product->id] = $newQuantity;

        $request->session()->put('cart', $cart);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk ditambahkan ke keranjang.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE CART
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $data = $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:0|max:999',
        ]);

        $cart = $request->session()->get('cart', []);

        foreach ($data['quantities'] as $id => $quantity) {

            if (!array_key_exists($id, $cart)) {
                continue;
            }

            if ((int) $quantity === 0) {
                unset($cart[$id]);
                continue;
            }

            $product = Product::find($id);

            if (
                !$product ||
                !self::isAvailable($product) ||
                $product->stock < (int) $quantity
            ) {
                return back()->withErrors([
                    'stock' => 'Produk tidak tersedia atau stok tidak mencukupi.',
                ]);
            }

            $cart[$id] = (int) $quantity;
        }

        $request->session()->put('cart', $cart);

        return back()->with(
            'success',
            'Keranjang diperbarui.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE FROM CART
    |--------------------------------------------------------------------------
    */

    public function remove(Request $request, Product $product)
    {
        $cart = $request->session()->get('cart', []);

        unset($cart[$product->id]);

        $request->session()->put('cart', $cart);

        return back();
    }
}
