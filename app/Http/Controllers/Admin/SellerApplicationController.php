<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SellerApplicationController extends Controller
{
    public function index()
    {
        $applications = SellerApplication::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.sellers.index', compact('applications'));
    }

    public function update(Request $request, SellerApplication $application)
    {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['approved', 'rejected', 'suspended']),
            ],
            'admin_note' => 'nullable|string|max:2000',
        ]);

        if (
            $data['status'] === 'rejected'
            && empty(trim($data['admin_note'] ?? ''))
        ) {
            return back()->withErrors([
                'admin_note' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($application, $data, $request) {
            $application = SellerApplication::query()
                ->lockForUpdate()
                ->findOrFail($application->id);

            // Hanya pengajuan menunggu yang dapat disetujui.
            if (
                $data['status'] === 'approved'
                && $application->status !== 'pending'
            ) {
                abort(422, 'Hanya pengajuan pending yang dapat disetujui.');
            }

            $application->update([
                'status' => $data['status'],
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Status penjual berhasil diperbarui.'
        );
    }
}
