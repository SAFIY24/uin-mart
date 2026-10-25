<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    // Danh sách voucher
    public function index()
    {
        $vouchers = Promotion::latest('id')->paginate(12);

        $deletedVouchers = Promotion::onlyTrashed()
            ->latest('deleted_at')
            ->get();

        return view('admin.promotions.vouchers', compact(
            'vouchers',
            'deletedVouchers'
        ));
    }

    // Form tạo voucher
    public function create()
    {
        return view('admin.promotions.voucher-form', [
            'voucher' => new Promotion()
        ]);
    }

    // Lưu voucher mới
    public function store(Request $request)
    {
        $data = $this->validateVoucher($request);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');
        $data['is_stackable'] = $request->boolean('is_stackable');
        $data['used_count'] = 0;

        Promotion::create($data);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil ditambahkan.');
    }

    // Form sửa voucher
    public function edit(Promotion $voucher)
    {
        return view('admin.promotions.voucher-form', compact('voucher'));
    }

    // Cập nhật voucher
    public function update(Request $request, Promotion $voucher)
    {
        $data = $this->validateVoucher($request, $voucher);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');
        $data['is_stackable'] = $request->boolean('is_stackable');

        $voucher->update($data);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil diperbarui.');
    }

    // Xóa mềm
    public function destroy(Promotion $voucher)
    {
        $voucher->update(['is_active' => false]);
        $voucher->delete();

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil dihapus.');
    }

    // Khôi phục voucher
    public function restore(int $id)
    {
        $voucher = Promotion::onlyTrashed()->findOrFail($id);

        $voucher->restore();
        $voucher->update(['is_active' => false]);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil dipulihkan.');
    }

    // Bật/tắt voucher
    public function toggle(Promotion $voucher)
    {
        $voucher->update([
            'is_active' => !$voucher->is_active
        ]);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Status voucher berhasil diperbarui.');
    }

    // Kiểm tra dữ liệu
    private function validateVoucher(
        Request $request,
        ?Promotion $voucher = null
    ): array {
        $uniqueCode = Rule::unique('promotions', 'code');

        if ($voucher) {
            $uniqueCode->ignore($voucher->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                $uniqueCode,
            ],

            'type' => [
                'required',
                Rule::in(['percentage', 'fixed'])
            ],

            'value' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999'
            ],

            'min_order_amount' => [
                'required',
                'numeric',
                'min:0'
            ],

            'max_discount_amount' => [
                'nullable',
                'numeric',
                'gt:0'
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1'
            ],

            'starts_at' => [
                'nullable',
                'date'
            ],

            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

            'is_stackable' => [
                'sometimes',
                'boolean'
            ]
        ] + []);
    }
}
