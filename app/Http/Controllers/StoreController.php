<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\SellerApplication;
use App\Services\ProductPricing;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request, ProductPricing $pricing)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->integer('category');

        $categories = Category::orderBy('id')->get();

        $products = Product::with(['category', 'discount'])
            ->where('status', 'active')
            ->where('approval_status', 'approved')

            // Produk Admin tetap ditampilkan.
            // Produk Seller hanya ditampilkan jika penjual disetujui.
            ->where(function ($query) {
                $query->whereNull('seller_id')
                    ->orWhereIn(
                        'seller_id',
                        SellerApplication::query()
                            ->select('user_id')
                            ->where('status', 'approved')
                    );
            })

            // Pencarian nama produk
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%');
            })

            // Filter kategori
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })

            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        // Menghitung harga produk setelah diskon
        foreach ($products as $product) {
            $product->original_price = $pricing->originalPrice($product);
            $product->final_price = $pricing->finalPrice($product);
            $product->discount_percentage = $pricing->discountPercentage($product);
        }

        return view('home', compact(
            'products',
            'categories',
            'q',
            'categoryId'
        ));
    }

    public function show(Product $product, ProductPricing $pricing)
    {
        // Produk harus aktif dan sudah disetujui Admin
        abort_unless(
            $product->status === 'active'
                && $product->approval_status === 'approved',
            404
        );

        // Jika produk milik Seller, akun Seller harus disetujui
        if ($product->seller_id !== null) {
            $sellerApproved = SellerApplication::query()
                ->where('user_id', $product->seller_id)
                ->where('status', 'approved')
                ->exists();

            abort_unless($sellerApproved, 404);
        }

        $product->load(['category', 'discount', 'images']);

        // Menghitung harga produk setelah diskon
        $product->original_price = $pricing->originalPrice($product);
        $product->final_price = $pricing->finalPrice($product);
        $product->discount_percentage = $pricing->discountPercentage($product);

        return view('products.show', compact('product'));
    }
}
