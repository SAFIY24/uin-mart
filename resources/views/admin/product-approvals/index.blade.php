
@extends('layouts.admin')

@section('title', 'Persetujuan Produk · UIN Mart')

@section('admin_content')

<style>
.pa-page { width:100%; max-width:1350px; margin:0 auto; }
.pa-heading { margin-bottom:22px; }
.pa-heading h1 { margin:0 0 7px; color:#800e19; font-size:26px; }
.pa-heading p { margin:0; color:#777; font-size:13px; }
.pa-card { background:#fff; border:1px solid #e9e1e3; border-radius:13px; overflow-x:auto; }
.pa-table { width:100%; min-width:850px; border-collapse:collapse; }
.pa-table th { background:#faf5f6; color:#800e19; text-align:left; font-size:12px; padding:15px; }
.pa-table td { padding:15px; border-top:1px solid #eee; vertical-align:top; font-size:13px; }
.pa-table small { display:block; margin-top:5px; color:#777; }
.pa-image { width:65px; height:65px; object-fit:cover; border-radius:8px; background:#f4f4f4; }
.pa-status { display:inline-block; padding:5px 10px; border-radius:20px; font-size:11px; font-weight:800; }
.pa-status.pending { background:#fff3d5; color:#93610b; }
.pa-status.approved { background:#e4f6ea; color:#267a44; }
.pa-status.rejected { background:#ffeaed; color:#b42936; }
.pa-actions { display:flex; flex-wrap:wrap; gap:8px; }
.pa-button { border:0; border-radius:7px; padding:9px 13px; font-size:12px; font-weight:700; cursor:pointer; }
.pa-approve { background:#25834b; color:white; }
.pa-reject { background:#b42936; color:white; }
.pa-empty { padding:40px; text-align:center; color:#777; }
.pa-pagination { margin-top:20px; }
</style>

<div class="pa-page">

    <div class="pa-heading">
        <h1>Persetujuan Produk</h1>
        <p>Verifikasi produk yang diajukan oleh penjual UIN Mart.</p>
    </div>

    <div class="pa-card">
        <table class="pa-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Penjual</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->image)
                                <img
                                    class="pa-image"
                                    src="{{ str_starts_with($product->image, 'products/')
                                        ? asset('storage/'.$product->image)
                                        : asset('uploads/products/'.basename($product->image)) }}"
                                    alt="{{ $product->name }}"
                                >
                            @endif

                            <strong>{{ $product->name }}</strong>
                            <small>ID: {{ $product->id }}</small>
                            <small>{{ \Illuminate\Support\Str::limit($product->description, 80) }}</small>
                        </td>

                        <td>
                            {{ $product->seller?->name ?? $product->seller?->username ?? 'Penjual tidak ditemukan' }}
                            <small>{{ $product->seller?->email ?? '-' }}</small>
                        </td>

                        <td>{{ $product->category?->name ?? '-' }}</td>

                        <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>

                        <td>{{ $product->stock }}</td>

                        <td>
                            <span class="pa-status {{ $product->approval_status }}">
                                {{ ucfirst($product->approval_status) }}
                            </span>
                        </td>

                        <td>
                            @if($product->approval_status === 'pending')
                                <div class="pa-actions">

                                    <!-- SETUJUI PRODUK -->
                                    <form
                                        method="POST"
                                        action="{{ route('admin.product-approvals.update', $product) }}"
                                        onsubmit="return confirm('Setujui produk ini?')"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="approval_status" value="approved">

                                        <button type="submit" class="pa-button pa-approve">
                                            Setujui
                                        </button>
                                    </form>

                                    <!-- TOLAK PRODUK -->
                                    <form
                                        method="POST"
                                        action="{{ route('admin.product-approvals.update', $product) }}"
                                        onsubmit="return confirm('Tolak produk ini?')"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="approval_status" value="rejected">

                                        <button type="submit" class="pa-button pa-reject">
                                            Tolak
                                        </button>
                                    </form>

                                </div>
                            @else
                                <small>Sudah diproses</small>
                            @endif

                            <!-- HAPUS PRODUK -->
                            <form
                                method="POST"
                                action="{{ route('admin.product-approvals.destroy', $product) }}"
                                onsubmit="return confirm('Yakin ingin menghapus produk ini secara permanen? Tindakan ini tidak dapat dibatalkan.')"
                                style="margin-top:8px;"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="pa-button pa-reject">
                                    Hapus Produk
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="pa-empty">
                            Belum ada pengajuan produk dari penjual.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pa-pagination">
        {{ $products->links() }}
    </div>

</div>

@endsection
