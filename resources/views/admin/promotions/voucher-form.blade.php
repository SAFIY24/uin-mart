
@extends('layouts.admin')

@section('title', 'Form Voucher · UIN Mart')

@section('admin_content')

@php
    $isEdit = $voucher->exists;

    $action = $isEdit
        ? route('admin.vouchers.update', $voucher)
        : route('admin.vouchers.store');
@endphp

<div class="uv-form-page">

    <div class="uv-form-heading">
        <div>
            <p class="uv-form-eyebrow">
                ADMIN / DISKON & PROMOSI / VOUCHER
            </p>

            <h1>
                {{ $isEdit ? 'Edit Voucher' : 'Tambah Voucher' }}
            </h1>

            <p>
                Atur kode voucher dan ketentuan diskon UIN Mart.
            </p>
        </div>

        <a href="{{ route('admin.vouchers.index') }}"
           class="uv-form-back">
            ← Kembali
        </a>
    </div>

    <form method="POST"
          action="{{ $action }}"
          class="uv-form-card">

        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="uv-form-grid">

            <div class="uv-form-field">
                <label for="name">Nama Voucher *</label>

                <input id="name"
                       type="text"
                       name="name"
                       value="{{ old('name', $voucher->name) }}"
                       placeholder="Promo Mahasiswa UIN"
                       maxlength="150"
                       required>
            </div>

            <div class="uv-form-field">
                <label for="code">Kode Voucher *</label>

                <input id="code"
                       type="text"
                       name="code"
                       value="{{ old('code', $voucher->code) }}"
                       placeholder="UINMART10"
                       maxlength="50"
                       required>
            </div>

            <div class="uv-form-field">
                <label for="type">Jenis Diskon *</label>

                <select id="type" name="type" required>
                    <option value="percentage"
                        @selected(old('type', $voucher->type ?? 'percentage') === 'percentage')>
                        Persentase (%)
                    </option>

                    <option value="fixed"
                        @selected(old('type', $voucher->type) === 'fixed')>
                        Nominal (Rp)
                    </option>
                </select>
            </div>

            <div class="uv-form-field">
                <label for="value">Nilai Diskon *</label>

                <input id="value"
                       type="number"
                       name="value"
                       min="0.01"
                       step="0.01"
                       value="{{ old('value', $voucher->value) }}"
                       placeholder="10"
                       required>

                <small>
                    Contoh: 10 untuk 10%, atau 20000 untuk Rp 20.000.
                </small>
            </div>

            <div class="uv-form-field">
                <label for="min_order_amount">
                    Minimum Belanja (Rp) *
                </label>

                <input id="min_order_amount"
                       type="number"
                       name="min_order_amount"
                       min="0"
                       step="0.01"
                       value="{{ old('min_order_amount', $voucher->min_order_amount ?? 0) }}"
                       required>
            </div>

            <div class="uv-form-field">
                <label for="max_discount_amount">
                    Maksimum Diskon (Rp)
                </label>

                <input id="max_discount_amount"
                       type="number"
                       name="max_discount_amount"
                       min="0.01"
                       step="0.01"
                       value="{{ old('max_discount_amount', $voucher->max_discount_amount) }}"
                       placeholder="20000">
            </div>

            <div class="uv-form-field">
                <label for="usage_limit">
                    Batas Penggunaan
                </label>

                <input id="usage_limit"
                       type="number"
                       name="usage_limit"
                       min="1"
                       step="1"
                       value="{{ old('usage_limit', $voucher->usage_limit) }}"
                       placeholder="Kosongkan jika tanpa batas">
            </div>

            <div class="uv-form-field">
                <label for="starts_at">
                    Tanggal Mulai
                </label>

                <input id="starts_at"
                       type="datetime-local"
                       name="starts_at"
                       value="{{ old('starts_at', $voucher->starts_at?->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="uv-form-field">
                <label for="ends_at">
                    Tanggal Berakhir
                </label>

                <input id="ends_at"
                       type="datetime-local"
                       name="ends_at"
                       value="{{ old('ends_at', $voucher->ends_at?->format('Y-m-d\TH:i')) }}">
            </div>

        </div>

        <div class="uv-form-options">

            <label class="uv-form-check">
                <input type="hidden"
                       name="is_active"
                       value="0">

                <input type="checkbox"
                       name="is_active"
                       value="1"
                       @checked(old('is_active', $voucher->is_active ?? false))>

                Aktifkan Voucher
            </label>

            <label class="uv-form-check">
                <input type="hidden"
                       name="is_stackable"
                       value="0">

                <input type="checkbox"
                       name="is_stackable"
                       value="1"
                       @checked(old('is_stackable', $voucher->is_stackable ?? true))>

                Boleh Digabung dengan Diskon Produk
            </label>

        </div>

        <div class="uv-form-actions">
            <a href="{{ route('admin.vouchers.index') }}"
               class="uv-form-cancel">
                Batal
            </a>

            <button type="submit" class="uv-form-save">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Voucher' }}
            </button>
        </div>

    </form>

</div>

<style>
.uv-form-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
    max-width: 1050px;
}

.uv-form-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.uv-form-eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #a1757c;
}

.uv-form-heading h1 {
    margin: 7px 0;
    color: #35151b;
    font-size: 27px;
}

.uv-form-heading p {
    color: #82777c;
    font-size: 13px;
}

.uv-form-back,
.uv-form-cancel {
    display: inline-block;
    padding: 11px 17px;
    border: 1px solid #e5d9dc;
    border-radius: 9px;
    color: #800e19;
    background: #fff;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

.uv-form-card {
    background: #fff;
    padding: 28px;
    border: 1px solid #eee5e7;
    border-radius: 15px;
}

.uv-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.uv-form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.uv-form-field label {
    color: #55454a;
    font-size: 12px;
    font-weight: 750;
}

.uv-form-field input,
.uv-form-field select {
    width: 100%;
    min-height: 44px;
    padding: 11px 13px;
    border: 1px solid #e5dfe1;
    border-radius: 9px;
    background: #fff;
    color: #35282d;
    font-size: 13px;
}

.uv-form-field input:focus,
.uv-form-field select:focus {
    outline: none;
    border-color: #800e19;
    box-shadow: 0 0 0 3px #800e1912;
}

.uv-form-field small {
    color: #8e8589;
    font-size: 11px;
}

.uv-form-options {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    margin-top: 28px;
    padding: 20px 0;
    border-top: 1px solid #eee5e7;
    border-bottom: 1px solid #eee5e7;
}

.uv-form-check {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #51454a;
    font-size: 13px;
    font-weight: 600;
}

.uv-form-check input[type="checkbox"] {
    width: 17px;
    height: 17px;
    accent-color: #800e19;
}

.uv-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

.uv-form-save {
    padding: 11px 22px;
    border: none;
    border-radius: 9px;
    background: #800e19;
    color: #fff;
    font-size: 13px;
    font-weight: 750;
    cursor: pointer;
}

.uv-form-save:hover {
    background: #660b14;
}

@media (max-width: 700px) {
    .uv-form-card {
        padding: 18px;
    }

    .uv-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection
