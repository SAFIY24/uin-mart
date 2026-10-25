<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\SellerApplication;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $application = SellerApplication::where(
            'user_id',
            $user->id
        )->first();

        // Hanya penjual yang telah disetujui
        // oleh Admin yang boleh mengakses dashboard.
        if (!$application || $application->status !== 'approved') {
            return redirect()
                ->route('seller.register')
                ->withErrors([
                    'seller' => 'Akun penjual Anda belum disetujui oleh Admin.'
                ]);
        }

        // Hanya menampilkan produk milik penjual ini.
        $products = Product::where('seller_id', $user->id)
            ->latest()
            ->paginate(10);

 
        // Jumlah pesanan masuk milik Seller
        $totalOrders = Order::query()
            ->whereHas('items.product', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })
            ->count();

        // Pesanan yang masih menunggu diproses
        $pendingOrders = Order::query()
            ->where('order_status', 'pending')
            ->whereHas('items.product', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })
            ->count();

        return view('seller.dashboard', compact(
            'application',
            'products',
            'totalOrders',
            'pendingOrders'
        ));

    }
}
