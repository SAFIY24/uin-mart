<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDiscount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductDiscountController extends Controller
{
    // Hiển thị danh sách sản phẩm và giảm giá
    public function index(Request $request)
    {
        $products = Product::with('discount')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(
                    'name',
                    'like',
                    '%' . $request->input('q') . '%'
                );
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.promotions.products', compact('products'));
    }

    // Lưu cấu hình giảm giá sản phẩm
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'percentage' => ['required', 'integer', 'between:0,90'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        if (
            !empty($data['starts_at']) &&
            !empty($data['ends_at']) &&
            strtotime($data['ends_at']) < strtotime($data['starts_at'])
        ) {
            throw ValidationException::withMessages([
                'ends_at' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
            ]);
        }

        if ((int) $data['percentage'] === 0) {
            $data['is_active'] = false;
        }

        ProductDiscount::updateOrCreate(
            ['product_id' => $product->id],
            [
                'percentage' => (int) $data['percentage'],
                'is_active' => (bool) $data['is_active'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Diskon produk berhasil diperbarui.');
    }
}
