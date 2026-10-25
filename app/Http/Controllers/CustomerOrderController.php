<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerOrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR PESANAN CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL PESANAN CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, Order $order)
    {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            403,
            'Anda tidak memiliki izin melihat pesanan ini.'
        );

        $order->load('items');

        $sellerIds = $order->items
            ->pluck('seller_id')
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values()
            ->all();

        $fulfillments = DB::table('seller_order_fulfillments')
            ->where('order_id', $order->id)
            ->whereIn('seller_id', $sellerIds)
            ->orderBy('seller_id')
            ->get();

        // Data pengiriman produk Admin.
        $adminFulfillment = DB::table('admin_order_fulfillments')
            ->where('order_id', $order->id)
            ->first();

        return view('customer.orders.show', compact(
            'order',
            'fulfillments',
            'adminFulfillment'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | PERIKSA APAKAH SEMUA BARANG SUDAH DITERIMA
    |--------------------------------------------------------------------------
    */

    private function completeOrderIfAllReceived(Order $order): void
    {
        $items = $order->items()
            ->select('seller_id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $hasAdminItems = $items->contains(
            fn ($item) => $item->seller_id === null
        );

        $sellerIds = $items
            ->pluck('seller_id')
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values();

        // Periksa seluruh pengiriman Seller.
        $completedSellerIds = DB::table(
            'seller_order_fulfillments'
        )
            ->where('order_id', $order->id)
            ->whereIn('seller_id', $sellerIds->all())
            ->where('status', 'completed')
            ->pluck('seller_id')
            ->unique();

        $allSellersCompleted =
            $completedSellerIds->count() === $sellerIds->count();

        // Jika ada produk Admin, Customer juga harus
        // mengonfirmasi penerimaan produk Admin.
        $adminCompleted = true;

        if ($hasAdminItems) {
            $adminCompleted = DB::table('admin_order_fulfillments')
                ->where('order_id', $order->id)
                ->where('status', 'completed')
                ->exists();
        }

        if ($allSellersCompleted && $adminCompleted) {
            $order->update([
                'order_status' => 'completed',
            ]);
        }

        // Jangan ubah payment_status di sini.
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER KONFIRMASI BARANG SELLER DITERIMA
    |--------------------------------------------------------------------------
    */

    public function confirmReceived(
        Request $request,
        Order $order,
        int $sellerId
    ) {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            403,
            'Anda tidak memiliki izin mengubah pesanan ini.'
        );

        DB::transaction(function () use (
            $request,
            $order,
            $sellerId
        ) {
            $currentOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $currentOrder->user_id === (int) $request->user()->id,
                403
            );

            if (in_array(
                $currentOrder->order_status,
                ['cancelled', 'completed'],
                true
            )) {
                throw ValidationException::withMessages([
                    'delivery' =>
                        'Pesanan sudah dibatalkan atau selesai.',
                ]);
            }

            $hasSellerItems = $currentOrder->items()
                ->where('seller_id', $sellerId)
                ->exists();

            abort_unless(
                $hasSellerItems,
                404,
                'Pengiriman tidak ditemukan dalam pesanan ini.'
            );

            $fulfillment = DB::table('seller_order_fulfillments')
                ->where('order_id', $currentOrder->id)
                ->where('seller_id', $sellerId)
                ->lockForUpdate()
                ->first();

            if (!$fulfillment) {
                throw ValidationException::withMessages([
                    'delivery' =>
                        'Data pengiriman tidak ditemukan.',
                ]);
            }

            if ($fulfillment->status !== 'handed_over') {
                throw ValidationException::withMessages([
                    'delivery' =>
                        'Barang belum dapat dikonfirmasi diterima.',
                ]);
            }

            DB::table('seller_order_fulfillments')
                ->where('id', $fulfillment->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->completeOrderIfAllReceived($currentOrder);
        });

        return redirect()
            ->route('customer.orders.show', $order)
            ->with(
                'success_delivery',
                'Penerimaan barang Seller berhasil dikonfirmasi.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER KONFIRMASI PRODUK ADMIN DITERIMA
    |--------------------------------------------------------------------------
    */

    public function confirmAdminReceived(Request $request, Order $order)
    {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            403
        );

        DB::transaction(function () use ($request, $order) {
            $currentOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $currentOrder->user_id === (int) $request->user()->id,
                403
            );

            if (in_array(
                $currentOrder->order_status,
                ['cancelled', 'completed'],
                true
            )) {
                throw ValidationException::withMessages([
                    'delivery' => 'Pesanan sudah ditutup.',
                ]);
            }

            $hasAdminItems = $currentOrder->items()
                ->whereNull('seller_id')
                ->exists();

            abort_unless($hasAdminItems, 404);

            $adminFulfillment = DB::table('admin_order_fulfillments')
                ->where('order_id', $currentOrder->id)
                ->lockForUpdate()
                ->first();

            if (!$adminFulfillment) {
                throw ValidationException::withMessages([
                    'delivery' =>
                        'Data pengiriman Admin belum tersedia.',
                ]);
            }

            if ($adminFulfillment->status !== 'handed_over') {
                throw ValidationException::withMessages([
                    'delivery' =>
                        'Produk Admin belum diserahkan.',
                ]);
            }

            DB::table('admin_order_fulfillments')
                ->where('id', $adminFulfillment->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->completeOrderIfAllReceived($currentOrder);
        });

        return redirect()
            ->route('customer.orders.show', $order)
            ->with(
                'success_delivery',
                'Penerimaan produk Admin berhasil dikonfirmasi.'
            );
    }
}
