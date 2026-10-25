<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SellerApplication;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // Statistik akun
        $stats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'seller' => SellerApplication::where('status', 'approved')->count(),
            'customer' => User::where('role', 'customer')->count(),
        ];

        // Daftar akun pengguna
        $users = User::query()
            ->select([
                'id',
                'name',
                'username',
                'email',
                'nim',
                'role',
                'email_verified_at',
                'created_at',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subquery) use ($q) {
                    $subquery->where('name', 'like', '%' . $q . '%')
                        ->orWhere('username', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%')
                        ->orWhere('nim', 'like', '%' . $q . '%');
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Status Seller berdasarkan tabel seller_applications
        $sellerStatuses = SellerApplication::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('admin.users.index', compact(
            'users',
            'stats',
            'sellerStatuses',
            'q'
        ));
    }
    // DETAIL AKUN PENGGUNA
public function show(\App\Models\User $user)
{
    $sellerApplication = \App\Models\SellerApplication::query()
        ->where('user_id', $user->id)
        ->first();

    $totalProducts = \App\Models\Product::query()
        ->where('seller_id', $user->id)
        ->count();

    $totalOrders = \App\Models\Order::query()
        ->where('user_id', $user->id)
        ->count();

// Riwayat pesanan pengguna
$orders = \App\Models\Order::query()
    ->where('user_id', $user->id)
    ->with('items')
    ->latest('id')
    ->paginate(10);

return view('admin.users.show', compact(
    'user',
    'sellerApplication',
    'totalProducts',
    'totalOrders',
    'orders'
));
}
}
