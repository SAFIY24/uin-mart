
@extends('layouts.admin')

@section('admin_content')

<style>
    .seller-admin-page {
        width: 100%;
        max-width: 1350px;
        margin: 0 auto;
        color: #25252a;
    }

    .seller-admin-header {
        margin-bottom: 24px;
    }

    .seller-admin-header h1 {
        margin: 0 0 7px;
        color: #800e19;
        font-size: 27px;
        font-weight: 800;
    }

    .seller-admin-header p {
        margin: 0;
        color: #777;
        font-size: 14px;
    }

    .seller-admin-alert {
        padding: 14px 17px;
        margin-bottom: 18px;
        border-radius: 10px;
        background: #e8f7ee;
        color: #246b40;
    }

    .seller-admin-error {
        background: #fff0f1;
        color: #a62130;
    }

    .seller-admin-table-wrap {
        background: white;
        border: 1px solid #e8e1e2;
        border-radius: 14px;
        overflow-x: auto;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .04);
    }

    .seller-admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 900px;
    }

    .seller-admin-table th {
        padding: 16px;
        background: #f9f5f6;
        color: #800e19;
        font-size: 12px;
        font-weight: 800;
    }

    .seller-admin-table td {
        padding: 16px;
        border-top: 1px solid #f0ebec;
        vertical-align: top;
        font-size: 13px;
    }

    .seller-admin-table small {
        display: block;
        margin-top: 5px;
        color: #777;
        font-size: 12px;
    }

    .seller-status {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 11px;
    }

    .seller-status.pending {
        color: #91620b;
        background: #fff4d5;
    }

    .seller-status.approved {
        color: #267a44;
        background: #e5f6eb;
    }

    .seller-status.rejected {
        color: #b52d37;
        background: #ffeaed;
    }

    .seller-status.suspended {
        color: #606775;
        background: #edf0f4;
    }

    .seller-action-form {
        margin-bottom: 9px;
    }

    .seller-admin-btn {
        display: inline-block;
        padding: 8px 12px;
        border: none;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 750;
        cursor: pointer;
    }

    .seller-admin-btn.approve {
        background: #228b4e;
        color: #fff;
    }

    .seller-admin-btn.reject {
        background: #b42936;
        color: #fff;
    }

    .seller-admin-btn.suspend {
        background: #555c68;
        color: #fff;
    }

    .seller-admin-note {
        display: block;
        width: 100%;
        min-width: 185px;
        min-height: 58px;
        margin-bottom: 7px;
        padding: 9px;
        border: 1px solid #ded7da;
        border-radius: 7px;
        font: inherit;
        font-size: 12px;
        box-sizing: border-box;
        resize: vertical;
    }

    .seller-empty {
        padding: 45px;
        text-align: center;
        color: #888;
    }

    .seller-pagination {
        margin-top: 20px;
    }
</style>

<div class="seller-admin-page">

    <div class="seller-admin-header">
        <h1>Pengajuan Penjual</h1>
        <p>
            Kelola pendaftaran toko dan verifikasi
            penjual UIN Mart.
        </p>
    </div>

    @if(session('success'))
        <div class="seller-admin-alert">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="seller-admin-alert seller-admin-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="seller-admin-table-wrap">

        <table class="seller-admin-table">

            <thead>
                <tr>
                    <th>Nama Toko</th>
                    <th>Pemilik</th>
                    <th>Kontak</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi Admin</th>
                </tr>
            </thead>

            <tbody>

                @forelse($applications as $application)

                    <tr>

                        <td>
                            <strong>
                                {{ $application->shop_name }}
                            </strong>
                        </td>

                        <td>
                            {{ $application->user?->name ?? 'Pengguna tidak ditemukan' }}

                            <small>
                                {{ $application->user?->email ?? '-' }}
                            </small>
                        </td>

                        <td>
                            {{ $application->phone }}
                        </td>

                        <td>
                            {{ $application->description ?: '-' }}

                            @if($application->admin_note)
                                <small>
                                    <strong>Catatan Admin:</strong>
                                    {{ $application->admin_note }}
                                </small>
                            @endif
                        </td>

                        <td>
                            <span class="seller-status {{ $application->status }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </td>

                        <td>
                            {{ $application->created_at?->format('d/m/Y') }}
                        </td>

                        <td>

                            @if($application->status === 'pending')

                                <form
                                    class="seller-action-form"
                                    action="{{ route('admin.sellers.update', $application->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Setujui penjual ini?')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="approved"
                                    >

                                    <button
                                        type="submit"
                                        class="seller-admin-btn approve"
                                    >
                                        Setujui
                                    </button>
                                </form>

                                <form
                                    class="seller-action-form"
                                    action="{{ route('admin.sellers.update', $application->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Tolak pengajuan ini?')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="rejected"
                                    >

                                    <textarea
                                        name="admin_note"
                                        class="seller-admin-note"
                                        placeholder="Alasan penolakan (wajib)"
                                        maxlength="2000"
                                        required
                                    ></textarea>

                                    <button
                                        type="submit"
                                        class="seller-admin-btn reject"
                                    >
                                        Tolak
                                    </button>
                                </form>

                            @elseif($application->status === 'approved')

                                <form
                                    class="seller-action-form"
                                    action="{{ route('admin.sellers.update', $application->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Tangguhkan penjual ini?')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="suspended"
                                    >

                                    <textarea
                                        name="admin_note"
                                        class="seller-admin-note"
                                        placeholder="Alasan penangguhan"
                                        maxlength="2000"
                                    ></textarea>

                                    <button
                                        type="submit"
                                        class="seller-admin-btn suspend"
                                    >
                                        Tangguhkan
                                    </button>
                                </form>

                            @else

                                <small>
                                    Tidak ada tindakan tersedia.
                                </small>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="seller-empty">
                            Belum ada pengajuan penjual.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="seller-pagination">
        {{ $applications->links() }}
    </div>

</div>

@endsection
