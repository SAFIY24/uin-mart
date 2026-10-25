<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductApprovalController extends Controller
{
    // Daftar produk yang diajukan penjual
    public function index()
    {
        $products = Product::with(['seller', 'category'])
            ->whereNotNull('seller_id')
            ->orderByRaw(
                "CASE WHEN approval_status = 'pending' THEN 0 ELSE 1 END"
            )
            ->latest('id')
            ->paginate(15);

        return view('admin.product-approvals.index', compact('products'));
    }

    // Persetujuan atau penolakan produk
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'approval_status' => [
                'required',
                Rule::in(['approved', 'rejected']),
            ],
        ]);

        DB::transaction(function () use ($product, $data) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            // Produk Admin tidak memerlukan pengajuan.
            abort_if(
                $product->seller_id === null,
                403,
                'Produk Admin tidak dapat diproses di halaman ini.'
            );

            // Hanya produk pending yang boleh diproses.
            abort_unless(
                $product->approval_status === 'pending',
                422,
                'Produk ini sudah diproses.'
            );

            $approved = $data['approval_status'] === 'approved';

            $product->approval_status = $data['approval_status'];

            // Produk baru aktif setelah disetujui.
            $product->status = $approved ? 'active' : 'inactive';

            $product->save();
        });

        return redirect()
            ->route('admin.product-approvals.index')
            ->with('success', 'Status persetujuan produk berhasil diperbarui.');
    }
    
    // Hapus produk yang diajukan oleh Seller
    public function destroy(Product $product)
    {
        abort_if(
            $product->seller_id === null,
            403,
            'Produk Admin tidak dapat dihapus melalui halaman ini.'
        );

        $product->delete();

        return redirect()
            ->route('admin.product-approvals.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

}
