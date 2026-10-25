<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SellerOrderController extends Controller
{
    private function ensureApprovedSeller(Request $request): void
    {
        abort_unless($request->user(), 401);

        $approved = SellerApplication::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->exists();

        abort_unless(
            $approved,
            403,
            'Akun penjual belum disetujui.'
        );
    }

    // DAFTAR PESANAN SELLER
    public function index(Request $request)
    {
        $this->ensureApprovedSeller($request);

        $sellerId = (int) $request->user()->id;

        $orders = Order::query()
            ->whereHas('items', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->with([
                'items' => function ($query) use ($sellerId) {
                    $query->where('seller_id', $sellerId);
                },
            ])
            ->latest('id')
            ->paginate(10);

        $orders->getCollection()->each(function ($order) use ($sellerId) {
            $order->seller_quantity = $order->items->sum('quantity');
            $order->seller_subtotal = $order->items->sum('subtotal');

            $order->seller_fulfillment = DB::table(
                'seller_order_fulfillments'
            )
                ->where('order_id', $order->id)
                ->where('seller_id', $sellerId)
                ->first();
        });

        $sellerOrders = Order::query()
            ->whereHas('items', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            });

        $totalOrders = (clone $sellerOrders)->count();

        $pendingOrders = DB::table('seller_order_fulfillments')
            ->join(
                'orders',
                'orders.id',
                '=',
                'seller_order_fulfillments.order_id'
            )
            ->where('seller_order_fulfillments.seller_id', $sellerId)
            ->where('seller_order_fulfillments.status', 'pending')
            ->where('orders.order_status', '!=', 'cancelled')
            ->count();

        $completedOrders = DB::table('seller_order_fulfillments')
            ->where('seller_id', $sellerId)
            ->where('status', 'completed')
            ->count();

        return view('seller.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'completedOrders'
        ));
    }

    // DETAIL PESANAN SELLER
    public function show(Request $request, Order $order)
    {
        $this->ensureApprovedSeller($request);

        $sellerId = (int) $request->user()->id;

        $sellerItems = $order->items()
            ->where('seller_id', $sellerId)
            ->get();

        abort_if(
            $sellerItems->isEmpty(),
            403,
            'Anda tidak memiliki akses ke pesanan ini.'
        );

        $sellerQuantity = $sellerItems->sum('quantity');
        $sellerSubtotal = $sellerItems->sum('subtotal');

        $fulfillment = DB::table('seller_order_fulfillments')
            ->where('order_id', $order->id)
            ->where('seller_id', $sellerId)
            ->first();

        // BARU: Pembayaran milik Seller yang login.
        $paymentAllocation = DB::table('order_payment_allocations')
            ->where('order_id', $order->id)
            ->where('recipient_type', 'seller')
            ->where('recipient_id', $sellerId)
            ->first();

        return view('seller.orders.show', compact(
            'order',
            'sellerItems',
            'sellerQuantity',
            'sellerSubtotal',
            'fulfillment',
            'paymentAllocation'
        ));
    }

    // PERUBAHAN STATUS PENGIRIMAN
    private function transition(
        Request $request,
        Order $order,
        string $from,
        string $to,
        string $message,
        array $additional = []
    ) {
        $this->ensureApprovedSeller($request);

        $sellerId = (int) $request->user()->id;

        DB::transaction(function () use (
            $order,
            $sellerId,
            $from,
            $to,
            $additional
        ) {
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
                        'Pesanan sudah dibatalkan atau selesai.',
                ]);
            }

            $ownsItems = $currentOrder->items()
                ->where('seller_id', $sellerId)
                ->exists();

            abort_unless(
                $ownsItems,
                403,
                'Anda tidak memiliki akses ke pesanan ini.'
            );

            $fulfillment = DB::table('seller_order_fulfillments')
                ->where('order_id', $currentOrder->id)
                ->where('seller_id', $sellerId)
                ->lockForUpdate()
                ->first();

            if (!$fulfillment) {
                throw ValidationException::withMessages([
                    'delivery' =>
                        'Data pengiriman belum tersedia untuk pesanan ini.',
                ]);
            }

            if ($fulfillment->status !== $from) {
                throw ValidationException::withMessages([
                    'delivery' =>
                        'Status pengiriman sudah berubah. Muat ulang halaman.',
                ]);
            }

            DB::table('seller_order_fulfillments')
                ->where('id', $fulfillment->id)
                ->update(array_merge(
                    [
                        'status' => $to,
                        'updated_at' => now(),
                    ],
                    $additional
                ));
        });

        return redirect()
            ->route('seller.orders.show', $order)
            ->with('success', $message);
    }

    // 1. KONFIRMASI PESANAN
    public function confirm(Request $request, Order $order)
    {
        return $this->transition(
            $request,
            $order,
            'pending',
            'confirmed',
            'Pesanan berhasil dikonfirmasi.',
            ['confirmed_at' => now()]
        );
    }

    // 2. SIAPKAN PRODUK
    public function prepare(Request $request, Order $order)
    {
        return $this->transition(
            $request,
            $order,
            'confirmed',
            'preparing',
            'Produk sedang dipersiapkan.'
        );
    }

    // 3. PRODUK SIAP
    public function ready(Request $request, Order $order)
    {
        return $this->transition(
            $request,
            $order,
            'preparing',
            'ready',
            'Produk siap dikirim atau diambil.'
        );
    }

    // 4. SERAHKAN BARANG
    public function handover(Request $request, Order $order)
    {
        return $this->transition(
            $request,
            $order,
            'ready',
            'handed_over',
            'Barang ditandai sudah diserahkan. Menunggu konfirmasi pembeli.',
            ['handed_over_at' => now()]
        );
    }

    // SELLER MENETAPKAN JADWAL
    public function proposeSchedule(Request $request, Order $order)
    {
        $this->ensureApprovedSeller($request);

        $data = $request->validate([
            'scheduled_at' => [
                'required',
                'date_format:Y-m-d\TH:i',
                'after:now',
            ],
            'schedule_note' => 'nullable|string|max:1000',
        ]);

        $sellerId = (int) $request->user()->id;

        DB::transaction(function () use ($order, $sellerId, $data) {
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
                    'scheduled_at' => 'Pesanan sudah ditutup.',
                ]);
            }

            $ownsItems = $currentOrder->items()
                ->where('seller_id', $sellerId)
                ->exists();

            abort_unless($ownsItems, 403);

            $fulfillment = DB::table('seller_order_fulfillments')
                ->where('order_id', $currentOrder->id)
                ->where('seller_id', $sellerId)
                ->lockForUpdate()
                ->first();

            if (!$fulfillment) {
                throw ValidationException::withMessages([
                    'scheduled_at' =>
                        'Data pengiriman tidak ditemukan.',
                ]);
            }

            if (
                !in_array(
                    $fulfillment->status,
                    ['pending', 'confirmed', 'preparing', 'ready'],
                    true
                )
                || !in_array(
                    $fulfillment->schedule_status,
                    [
                        'not_proposed',
                        'proposed',
                        'change_requested',
                        'confirmed',
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'scheduled_at' =>
                        'Jadwal tidak dapat diubah pada status ini.',
                ]);
            }

            DB::table('seller_order_fulfillments')
                ->where('id', $fulfillment->id)
                ->update([
                    'scheduled_at' => $data['scheduled_at'],
                    'schedule_note' => $data['schedule_note'] ?? null,
                    'schedule_status' => 'confirmed',
                    'schedule_change_reason' => null,
                    'schedule_confirmed_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('seller.orders.show', $order)
            ->with('success', 'Jadwal pengiriman berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | BARU - KONFIRMASI PEMBAYARAN DITERIMA
    |--------------------------------------------------------------------------
    |
    | Seller menerima uang secara langsung dari pembeli.
    | Seller hanya dapat mengonfirmasi tagihan miliknya.
    | Pesanan utama dibayar hanya jika SEMUA alokasi lunas.
    |
    */

    public function confirmPayment(Request $request, Order $order)
    {
        $this->ensureApprovedSeller($request);

        $sellerId = (int) $request->user()->id;

        DB::transaction(function () use ($order, $sellerId) {

            // Kunci pesanan utama terlebih dahulu.
            $currentOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($currentOrder->order_status === 'cancelled') {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Pembayaran pesanan yang dibatalkan tidak dapat dikonfirmasi.',
                ]);
            }

            if ($currentOrder->payment_status === 'failed') {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Status pembayaran gagal. Hubungi Admin.',
                ]);
            }

            // Seller harus memiliki produk dalam pesanan ini.
            $ownsItems = $currentOrder->items()
                ->where('seller_id', $sellerId)
                ->exists();

            abort_unless(
                $ownsItems,
                403,
                'Anda tidak memiliki akses ke pembayaran ini.'
            );

            // Pastikan barang sudah diserahkan.
            $fulfillment = DB::table('seller_order_fulfillments')
                ->where('order_id', $currentOrder->id)
                ->where('seller_id', $sellerId)
                ->lockForUpdate()
                ->first();

            if (
                !$fulfillment
                || !in_array(
                    $fulfillment->status,
                    ['handed_over', 'completed'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Konfirmasi pembayaran tersedia setelah barang diserahkan.',
                ]);
            }

            // Kunci alokasi pembayaran Seller ini.
            $payment = DB::table('order_payment_allocations')
                ->where('order_id', $currentOrder->id)
                ->where('recipient_type', 'seller')
                ->where('recipient_id', $sellerId)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Tagihan Seller belum tersedia. Hubungi Admin.',
                ]);
            }

            if ($payment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Pembayaran sudah dikonfirmasi sebelumnya.',
                ]);
            }

            if ($currentOrder->payment_status === 'paid') {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Status pembayaran utama tidak sesuai dengan tagihan Seller.',
                ]);
            }

            // Pastikan semua nilai tagihan sama dengan total pesanan.
            $allPayments = DB::table('order_payment_allocations')
                ->where('order_id', $currentOrder->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $totalAllocated = $allPayments->sum(
                fn ($row) => (int) $row->amount_due
            );

            $orderTotal = (int) round((float) $currentOrder->total);

            if (
                $allPayments->isEmpty()
                || $totalAllocated !== $orderTotal
            ) {
                throw ValidationException::withMessages([
                    'payment' =>
                        'Total tagihan tidak sesuai dengan total pesanan.',
                ]);
            }

            // Konfirmasi hanya bagian Seller yang sedang login.
            DB::table('order_payment_allocations')
                ->where('id', $payment->id)
                ->update([
                    'status' => 'paid',
                    'confirmed_by' => $sellerId,
                    'confirmed_at' => now(),
                    'updated_at' => now(),
                ]);

            // Periksa semua tagihan, termasuk Seller lain
            // dan produk yang dijual Admin.
            $remaining = DB::table('order_payment_allocations')
                ->where('order_id', $currentOrder->id)
                ->where('status', '!=', 'paid')
                ->count();

            if ($remaining === 0) {
                $currentOrder->payment_status = 'paid';
                $currentOrder->save();
            }
        });

        return redirect()
            ->route('seller.orders.show', $order)
            ->with(
                'success',
                'Pembayaran berhasil dikonfirmasi telah diterima.'
            );
    }
}
