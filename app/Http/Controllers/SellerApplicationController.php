<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use Illuminate\Http\Request;

class SellerApplicationController extends Controller
{
    // Halaman pendaftaran penjual
    public function create(Request $request)
    {
        $application = SellerApplication::where(
            'user_id',
            $request->user()->id
        )->first();

        return view('seller.register', compact('application'));
    }

    // Simpan pengajuan penjual
    public function store(Request $request)
    {
        $user = $request->user();

        $application = SellerApplication::where(
            'user_id',
            $user->id
        )->first();

        // Tidak boleh mengajukan ulang jika masih diproses
        // atau sudah disetujui.
        if ($application && in_array(
            $application->status,
            ['pending', 'approved', 'suspended'],
            true
        )) {
            return redirect()
                ->route('seller.register')
                ->withErrors([
                    'seller' => 'Pengajuan Anda sudah terdaftar.'
                ]);
        }

        $data = $request->validate([
            'shop_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($application && $application->status === 'rejected') {
            // Pengajuan ditolak boleh diperbaiki dan diajukan ulang.
            $application->update([
                ...$data,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'admin_note' => null,
            ]);
        } else {
            SellerApplication::create([
                'user_id' => $user->id,
                ...$data,
                'status' => 'pending',
            ]);
        }

        return redirect()
            ->route('seller.register')
            ->with('success', 'Pengajuan penjual berhasil dikirim.');
    }
}
