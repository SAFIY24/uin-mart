
@extends('layouts.admin')

@section('title', 'Diskon Produk · UIN Mart')

@section('admin_content')

<div class="ua-promotions-page">

    <div class="ua-promo-header">
        <div>
            <p class="ua-promo-eyebrow">ADMIN / DISKON & PROMOSI</p>
            <h1>Diskon Produk</h1>
            <p>Atur harga diskon setiap produk secara langsung.</p>
        </div>

        <a href="{{ route('admin.index') }}" class="ua-promo-back">
            ← Dashboard
        </a>
    </div>

    
<div class="ua-promo-tabs">

    <a href="{{ route('admin.promotions.index') }}"
       class="ua-promo-tab active">
        Diskon Produk
    </a>

    <a href="{{ route('admin.vouchers.index') }}"
       class="ua-promo-tab">
        Voucher
    </a>

</div>


    <div class="ua-promo-panel">

        <div class="ua-promo-panel-top">
            <div>
                <h2>Kelola Diskon Produk</h2>
                <p>
                    Pilih produk, atur persentase diskon,
                    lalu klik Simpan.
                </p>
            </div>

            <form method="GET"
                  action="{{ route('admin.promotions.index') }}"
                  class="ua-promo-search">

                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari produk..."
                    aria-label="Cari produk"
                >

                <button type="submit">Cari</button>
            </form>
        </div>

        <div class="ua-promo-table-wrap">
            <table class="ua-promo-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga Normal</th>
                        <th>Diskon</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Harga Setelah Diskon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($products as $product)

                        @php
                            $discount = $product->discount;
                            $percentage = (int) ($discount?->percentage ?? 0);

                            $isActive = $discount
                                ? $discount->isCurrentlyActive()
                                : false;

                            $normalPrice = (float) $product->price;

                            $finalPrice = $isActive
                                ? round($normalPrice * (100 - $percentage) / 100, 2)
                                : $normalPrice;

                            $formId = 'discount-form-' . $product->id;
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                <small>ID: {{ $product->id }}</small>
                            </td>

                            <td>
                                Rp {{ number_format($normalPrice, 0, ',', '.') }}
                            </td>

                            <td>
                                <label for="percentage-{{ $product->id }}">
                                    Persentase (%)
                                </label>

                                <input
                                    id="percentage-{{ $product->id }}"
                                    form="{{ $formId }}"
                                    type="number"
                                    name="percentage"
                                    min="0"
                                    max="90"
                                    step="1"
                                    value="{{ $percentage }}"
                                    required
                                    class="ua-promo-percent"
                                >
                            </td>

                            <td>
                                <label for="start-{{ $product->id }}">
                                    Mulai
                                </label>

                                <input
                                    id="start-{{ $product->id }}"
                                    form="{{ $formId }}"
                                    type="datetime-local"
                                    name="starts_at"
                                    value="{{ $discount?->starts_at?->format('Y-m-d\TH:i') }}"
                                >

                                <label for="end-{{ $product->id }}">
                                    Berakhir
                                </label>

                                <input
                                    id="end-{{ $product->id }}"
                                    form="{{ $formId }}"
                                    type="datetime-local"
                                    name="ends_at"
                                    value="{{ $discount?->ends_at?->format('Y-m-d\TH:i') }}"
                                >
                            </td>

                            <td>
                                <label for="active-{{ $product->id }}">
                                    Status
                                </label>

                                <select
                                    id="active-{{ $product->id }}"
                                    form="{{ $formId }}"
                                    name="is_active"
                                >
                                    <option value="1"
                                        @selected($discount?->is_active)>
                                        Aktif
                                    </option>

                                    <option value="0"
                                        @selected(!$discount?->is_active)>
                                        Nonaktif
                                    </option>
                                </select>

                                @if($isActive)
                                    <span class="ua-promo-status on">
                                        Sedang Berlaku
                                    </span>
                                @else
                                    <span class="ua-promo-status off">
                                        Tidak Berlaku
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($isActive)
                                    <small class="ua-promo-old">
                                        Rp {{ number_format($normalPrice, 0, ',', '.') }}
                                    </small>

                                    <strong class="ua-promo-new">
                                        Rp {{ number_format($finalPrice, 0, ',', '.') }}
                                    </strong>

                                    <span class="ua-promo-badge">
                                        -{{ $percentage }}%
                                    </span>
                                @else
                                    <strong>
                                        Rp {{ number_format($normalPrice, 0, ',', '.') }}
                                    </strong>
                                @endif
                            </td>

                            <td>
                                <form
                                    id="{{ $formId }}"
                                    method="POST"
                                    action="{{ route('admin.promotions.products.update', $product) }}"
                                >
                                    @csrf
                                    @method('PUT')
                                </form>

                                <button
                                    form="{{ $formId }}"
                                    type="submit"
                                    class="ua-promo-save"
                                >
                                    Simpan
                                </button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="ua-promo-empty">
                                Produk tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ua-promo-pagination">
            {{ $products->links() }}
        </div>

    </div>

</div>

<style>
.ua-promotions-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
    width: 100%;
    min-width: 0;
}

.ua-promo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.ua-promo-eyebrow {
    color: #a1757c;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.ua-promo-header h1 {
    color: #34151a;
    margin: 0 0 8px;
    font-size: 27px;
}

.ua-promo-header p {
    color: #8c8083;
    font-size: 13px;
}

.ua-promo-back {
    text-decoration: none;
    color: #800e19;
    background: #fff;
    border: 1px solid #eadfe1;
    border-radius: 9px;
    padding: 11px 15px;
    font-size: 12px;
    font-weight: 700;
}

.ua-promo-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}


.ua-promo-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 19px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    background: #ffffff;
    border: 1px solid #eadfe1;
    color: #800e19;
    cursor: pointer;
}

.ua-promo-tab:hover {
    background: #fff1f2;
}

.ua-promo-tab.active {
    background: #800e19;
    color: #ffffff;
    border-color: #800e19;
}


.ua-promo-tab.active {
    background: #800e19;
    color: #fff;
}

.ua-promo-tab.disabled {
    background: #f0edef;
    color: #a39a9d;
}

.ua-promo-panel {
    background: #fff;
    border: 1px solid #f0e7e9;
    border-radius: 16px;
    padding: 22px;
    min-width: 0;
}

.ua-promo-panel-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 23px;
}

.ua-promo-panel-top h2 {
    font-size: 18px;
    color: #391c21;
    margin: 0 0 6px;
}

.ua-promo-panel-top p {
    font-size: 12px;
    color: #8d8588;
}

.ua-promo-search {
    display: flex;
    gap: 8px;
}

.ua-promo-search input {
    padding: 11px 13px;
    border: 1px solid #e6dfe1;
    border-radius: 9px;
    min-width: 180px;
    max-width: 100%;
}

.ua-promo-search button,
.ua-promo-save {
    background: #800e19;
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 11px 16px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.ua-promo-save:hover,
.ua-promo-search button:hover {
    background: #670a13;
}

.ua-promo-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.ua-promo-table {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    font-size: 12px;
}

.ua-promo-table th {
    background: #faf6f7;
    text-align: left;
    color: #67565b;
    padding: 14px 12px;
    white-space: nowrap;
}

.ua-promo-table td {
    padding: 17px 12px;
    border-bottom: 1px solid #f1eaec;
    vertical-align: middle;
}

.ua-promo-table td small {
    display: block;
    color: #999;
    margin-top: 5px;
}

.ua-promo-table td label {
    display: block;
    font-size: 10px;
    color: #888;
    margin: 5px 0;
}

.ua-promo-table td input,
.ua-promo-table td select {
    padding: 9px;
    border: 1px solid #e6dfe1;
    border-radius: 7px;
    background: white;
    font-size: 12px;
    max-width: 190px;
}

.ua-promo-percent {
    width: 75px;
}

.ua-promo-old {
    text-decoration: line-through;
}

.ua-promo-new {
    color: #800e19;
    display: block;
    margin-top: 5px;
}

.ua-promo-badge {
    display: inline-block;
    margin-top: 5px;
    padding: 4px 7px;
    background: #fff0e2;
    color: #a45a00;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 700;
}

.ua-promo-status {
    display: inline-block;
    padding: 5px 8px;
    margin-top: 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}

.ua-promo-status.on {
    color: #12734f;
    background: #e7f7ef;
}

.ua-promo-status.off {
    color: #8b7780;
    background: #f3edf0;
}

.ua-promo-empty {
    text-align: center;
    color: #999;
    padding: 35px !important;
}

.ua-promo-pagination {
    margin-top: 20px;
}

@media (max-width: 700px) {
    .ua-promo-panel {
        padding: 14px;
    }

    .ua-promo-search {
        width: 100%;
    }

    .ua-promo-search input {
        min-width: 0;
        flex: 1;
    }
}
</style>

@endsection
