
@extends('layouts.admin')

@section('title', 'Manajemen Voucher · UIN Mart')

@section('admin_content')

<div class="uv-page">

    <div class="uv-heading">
        <div>
            <p class="uv-eyebrow">ADMIN / DISKON & PROMOSI</p>
            <h1>Manajemen Voucher</h1>
            <p>Tambah, edit, aktifkan, dan hapus voucher UIN Mart.</p>
        </div>

        <a href="{{ route('admin.vouchers.create') }}"
           class="uv-button uv-primary">
            + Tambah Voucher
        </a>
    </div>

    <div class="uv-tabs">
        <a href="{{ route('admin.promotions.index') }}"
           class="uv-tab">
            Diskon Produk
        </a>

        <a href="{{ route('admin.vouchers.index') }}"
           class="uv-tab active">
            Voucher
        </a>
    </div>

    <section class="uv-section">
        <div class="uv-section-heading">
            <h2>Daftar Voucher</h2>
            <span>{{ $vouchers->total() }} voucher</span>
        </div>

        <div class="uv-grid">

            @forelse($vouchers as $voucher)

                @php
                    $typeLabel = $voucher->type === 'percentage'
                        ? number_format((float) $voucher->value, 0, ',', '.') . '%'
                        : 'Rp ' . number_format((float) $voucher->value, 0, ',', '.');

                    $minOrder = number_format(
                        (float) $voucher->min_order_amount,
                        0,
                        ',',
                        '.'
                    );
                @endphp

                <article class="uv-card">

                    <div class="uv-card-top">
                        <div>
                            <h3>{{ $voucher->code }}</h3>
                            <p>{{ $voucher->name }}</p>
                        </div>

                        @if($voucher->isCurrentlyActive())
                            <span class="uv-badge uv-active">Aktif</span>
                        @elseif($voucher->is_active)
                            <span class="uv-badge uv-waiting">
                                Belum Berlaku
                            </span>
                        @else
                            <span class="uv-badge uv-inactive">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="uv-card-body">
                        <p>
                            Diskon <strong>{{ $typeLabel }}</strong>
                            · Minimal Rp {{ $minOrder }}
                        </p>

                        <p>
                            Maksimal:
                            <strong>
                                @if($voucher->max_discount_amount !== null)
                                    Rp {{ number_format(
                                        (float) $voucher->max_discount_amount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                @else
                                    Tanpa batas
                                @endif
                            </strong>
                        </p>

                        <p>
                            Penggunaan:
                            {{ $voucher->used_count }}
                            /
                            {{ $voucher->usage_limit ?? '∞' }}
                        </p>

                        <p>
                            Periode:
                            {{ $voucher->starts_at?->format('d/m/Y H:i') ?? 'Kapan saja' }}
                            —
                            {{ $voucher->ends_at?->format('d/m/Y H:i') ?? 'Tanpa batas' }}
                        </p>

                        <p>
                            Gabung dengan diskon produk:
                            <strong>
                                {{ $voucher->is_stackable ? 'Boleh' : 'Tidak' }}
                            </strong>
                        </p>
                    </div>

                    <div class="uv-card-actions">

                        <a href="{{ route('admin.vouchers.edit', $voucher) }}"
                           class="uv-button uv-outline">
                            Edit
                        </a>

                        <form method="POST"
                              action="{{ route('admin.vouchers.toggle', $voucher) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="uv-button uv-outline">
                                {{ $voucher->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('admin.vouchers.destroy', $voucher) }}"
                              onsubmit="return confirm('Hapus voucher ini? Voucher dapat dipulihkan nanti.');">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="uv-button uv-danger">
                                Hapus
                            </button>
                        </form>

                    </div>

                </article>

            @empty

                <div class="uv-empty">
                    <h3>Belum ada voucher</h3>
                    <p>
                        Klik Tambah Voucher untuk membuat
                        voucher pertama UIN Mart.
                    </p>

                    <a href="{{ route('admin.vouchers.create') }}"
                       class="uv-button uv-primary">
                        + Tambah Voucher
                    </a>
                </div>

            @endforelse

        </div>

        <div class="uv-pagination">
            {{ $vouchers->links() }}
        </div>
    </section>

    @if($deletedVouchers->isNotEmpty())

        <section class="uv-section">

            <div class="uv-section-heading">
                <h2>Voucher Dihapus</h2>
                <span>{{ $deletedVouchers->count() }} voucher</span>
            </div>

            <div class="uv-deleted-list">

                @foreach($deletedVouchers as $voucher)

                    <div class="uv-deleted-item">

                        <div>
                            <strong>{{ $voucher->code }}</strong>
                            <p>
                                Dihapus:
                                {{ $voucher->deleted_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <form method="POST"
                              action="{{ route('admin.vouchers.restore', $voucher->id) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="uv-button uv-outline">
                                Pulihkan
                            </button>
                        </form>

                    </div>

                @endforeach

            </div>
        </section>

    @endif

</div>

<style>
.uv-page {
    width: 100%;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.uv-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.uv-eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #a1757c;
}

.uv-heading h1 {
    margin: 8px 0;
    font-size: 27px;
    color: #35151b;
}

.uv-heading p {
    color: #82777c;
    font-size: 13px;
}

.uv-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.uv-tab {
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 9px;
    background: #fff;
    border: 1px solid #e9dfe1;
    color: #800e19;
    font-size: 13px;
    font-weight: 700;
}

.uv-tab.active {
    background: #800e19;
    border-color: #800e19;
    color: #fff;
}

.uv-section {
    background: #fff;
    border: 1px solid #eee5e7;
    border-radius: 15px;
    padding: 22px;
    min-width: 0;
}

.uv-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
}

.uv-section-heading h2 {
    margin: 0;
    color: #35151b;
    font-size: 18px;
}

.uv-section-heading span {
    color: #8b8185;
    font-size: 12px;
}

.uv-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 17px;
}

.uv-card {
    min-width: 0;
    padding: 20px;
    background: #fff;
    border: 1px solid #eee3e5;
    border-radius: 13px;
    box-shadow: 0 3px 12px #00000005;
}

.uv-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.uv-card h3 {
    margin: 0;
    color: #800e19;
    font-size: 20px;
    overflow-wrap: anywhere;
}

.uv-card-top p {
    margin: 6px 0 0;
    color: #777078;
    font-size: 12px;
}

.uv-badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}

.uv-active {
    color: #087447;
    background: #e5f6ed;
}

.uv-inactive {
    color: #766d72;
    background: #f0edf0;
}

.uv-waiting {
    color: #96620c;
    background: #fff1d4;
}

.uv-card-body {
    padding: 17px 0;
}

.uv-card-body p {
    margin: 0 0 9px;
    font-size: 12px;
    color: #61585e;
    line-height: 1.6;
}

.uv-card-body strong {
    color: #31262a;
}

.uv-card-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding-top: 15px;
    border-top: 1px solid #eee6e8;
}

.uv-card-actions form {
    margin: 0;
}

.uv-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    min-height: 39px;
    padding: 10px 14px;
    border-radius: 8px;
    font: inherit;
    font-size: 12px;
    font-weight: 750;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}

.uv-primary {
    background: #800e19;
    border: 1px solid #800e19;
    color: #fff;
}

.uv-primary:hover {
    background: #660b14;
}

.uv-outline {
    background: #fff;
    border: 1px solid #dccdd0;
    color: #800e19;
}

.uv-outline:hover {
    background: #fff3f4;
}

.uv-danger {
    background: #fff1f1;
    border: 1px solid #f4cdcd;
    color: #c02b35;
}

.uv-danger:hover {
    background: #ffe3e5;
}

.uv-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 45px 20px;
    border: 1px dashed #e6d4d8;
    border-radius: 12px;
}

.uv-empty h3 {
    color: #800e19;
    margin: 0 0 10px;
}

.uv-empty p {
    color: #8c8186;
    font-size: 13px;
    margin-bottom: 20px;
}

.uv-pagination {
    margin-top: 20px;
}

.uv-deleted-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.uv-deleted-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    padding: 14px 16px;
    background: #faf8f8;
    border: 1px solid #f0e9ea;
    border-radius: 10px;
}

.uv-deleted-item strong {
    color: #800e19;
}

.uv-deleted-item p {
    margin: 5px 0 0;
    color: #8c8186;
    font-size: 11px;
}

.uv-deleted-item form {
    margin: 0;
}

@media (max-width: 900px) {
    .uv-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .uv-section {
        padding: 14px;
    }

    .uv-heading h1 {
        font-size: 23px;
    }

    .uv-card {
        padding: 15px;
    }
}
</style>

@endsection
