<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerApplication;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Filter pesanan berdasarkan tanggal
        $orders = Order::query()
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            });

        $totalOrders = (clone $orders)->count();

        // Pendapatan dari pesanan yang sudah dibayar
        $totalRevenue = (clone $orders)
            ->where('payment_status', 'paid')
            ->sum('total');

        // Produk terjual dari pesanan yang sudah dibayar
        $productsSold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('orders.created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('orders.created_at', '<=', $endDate);
            })
            ->sum('order_items.quantity');

        $totalSellers = SellerApplication::query()
            ->where('status', 'approved')
            ->count();

        $totalProducts = Product::count();

        // Jumlah pesanan per bulan dalam 12 bulan terakhir
        $monthlyOrders = Order::query()
            ->selectRaw("YEAR(created_at) as tahun")
            ->selectRaw("MONTH(created_at) as bulan")
            ->selectRaw("COUNT(*) as jumlah")
            ->where('created_at', '>=', now()->startOfMonth()->subMonths(11))
            ->groupByRaw("YEAR(created_at), MONTH(created_at)")
            ->orderByRaw("YEAR(created_at), MONTH(created_at)")
            ->get();

        return view('admin.reports.index', compact(
            'totalOrders',
            'totalRevenue',
            'productsSold',
            'totalSellers',
            'totalProducts',
            'monthlyOrders',
            'startDate',
            'endDate'
        ));
    }
}
