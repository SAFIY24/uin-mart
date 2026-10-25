
@extends('layouts.admin')

@section('title', 'Manajemen Mahasiswa · UIN Mart')

@section('admin_content')

<style>
    .um-page {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
    }

    .um-heading {
        margin-bottom: 24px;
    }

    .um-heading h1 {
        margin: 5px 0 8px;
        color: #800e19;
        font-size: 27px;
    }

    .um-heading p {
        color: #777;
        font-size: 13px;
        margin: 0;
    }

    .um-eyebrow {
        color: #a16d0b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .um-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }

    .um-stat {
        background: #fff;
        border: 1px solid #eee3e5;
        border-radius: 13px;
        padding: 20px;
    }

    .um-stat span {
        color: #777;
        font-size: 12px;
    }

    .um-stat strong {
        display: block;
        color: #800e19;
        font-size: 27px;
        margin-top: 10px;
    }

    .um-panel {
        background: #fff;
        border: 1px solid #eee3e5;
        border-radius: 13px;
        padding: 22px;
    }

    .um-panel h2 {
        margin: 0 0 18px;
        font-size: 19px;
        color: #29252a;
    }

    .um-search {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .um-search input {
        flex: 1;
        min-width: 230px;
        padding: 12px 14px;
        border: 1px solid #e5dadd;
        border-radius: 8px;
        outline: none;
    }

    .um-search button {
        border: none;
        background: #800e19;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
    }

    .um-reset {
        display: inline-flex;
        align-items: center;
        color: #800e19;
        text-decoration: none;
        font-weight: 700;
        font-size: 13px;
    }

    .um-table-wrap {
        overflow-x: auto;
    }

    .um-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        text-align: left;
    }

    .um-table th {
        background: #faf5f6;
        color: #800e19;
        font-size: 12px;
        padding: 15px 12px;
    }

    .um-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        vertical-align: middle;
    }

    .um-table small {
        display: block;
        margin-top: 5px;
        color: #888;
    }

    .um-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
    }

    .um-admin {
        background: #f5e7eb;
        color: #800e19;
    }

    .um-customer {
        background: #edf0f6;
        color: #415575;
    }

    .um-seller {
        background: #e4f6ea;
        color: #267a44;
    }

    .um-pending {
        background: #fff3d5;
        color: #93610b;
    }

    .um-rejected {
        background: #ffeaed;
        color: #b42936;
    }

    .um-empty {
        text-align: center;
        padding: 35px;
        color: #777;
    }

    .um-pagination {
        margin-top: 20px;
    }

    @media (max-width: 900px) {
        .um-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 550px) {
        .um-stats {
            grid-template-columns: 1fr;
        }

        .um-panel {
            padding: 14px;
        }
    }
</style>

<div class="um-page">

    <div class="um-heading">
        <span class="um-eyebrow">ADMIN / MAHASISWA</span>
        <h1>Manajemen Mahasiswa</h1>
        <p>
            Lihat data akun mahasiswa, pembeli, dan penjual
            yang terdaftar di UIN Mart.
        </p>
    </div>

    <div class="um-stats">

        <div class="um-stat">
            <span>Total Akun</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>

        <div class="um-stat">
            <span>Administrator</span>
            <strong>{{ $stats['admin'] }}</strong>
        </div>

        <div class="um-stat">
            <span>Penjual Disetujui</span>
            <strong>{{ $stats['seller'] }}</strong>
        </div>

        <div class="um-stat">
            <span>Akun Customer</span>
            <strong>{{ $stats['customer'] }}</strong>
        </div>

    </div>

    <div class="um-panel">

        <h2>Daftar Akun Pengguna</h2>

        <form
            method="GET"
            action="{{ route('admin.users.index') }}"
            class="um-search"
        >
            <input
                type="search"
                name="q"
                value="{{ $q }}"
                placeholder="Cari nama, username, email, atau NIM..."
            >

            <button type="submit">
                Cari Akun
            </button>

            @if($q !== '')
                <a
                    href="{{ route('admin.users.index') }}"
                    class="um-reset"
                >
                    Reset
                </a>
            @endif
        </form>

        <div class="um-table-wrap">

            <table class="um-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>NIM</th>
                        <th>Peran</th>
                        <th>Status Penjual</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)

                        @php
                            $sellerApplication = $sellerStatuses->get($user->id);
                        @endphp

                        <tr>
                            <td>#{{ $user->id }}</td>

                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>

                            <td>{{ $user->username }}</td>

                            <td>{{ $user->email }}</td>

                            <td>{{ $user->nim ?? '-' }}</td>

                            <td>
                                @if($user->role === 'admin')
                                    <span class="um-badge um-admin">
                                        Admin
                                    </span>
                                @else
                                    <span class="um-badge um-customer">
                                        Customer
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if(!$sellerApplication)
                                    <span>-</span>

                                @elseif($sellerApplication->status === 'approved')
                                    <span class="um-badge um-seller">
                                        Disetujui
                                    </span>

                                @elseif($sellerApplication->status === 'pending')
                                    <span class="um-badge um-pending">
                                        Menunggu
                                    </span>

                                @elseif($sellerApplication->status === 'rejected')
                                    <span class="um-badge um-rejected">
                                        Ditolak
                                    </span>

                                @elseif($sellerApplication->status === 'suspended')
                                    <span class="um-badge um-rejected">
                                        Ditangguhkan
                                    </span>

                                @else
                                    {{ ucfirst($sellerApplication->status) }}
                                @endif
                            </td>

                            <td>
                                {{ $user->created_at?->format('d/m/Y') ?? '-' }}
                            </td>
                        </tr>
                        <td>
    <a
        href="{{ route('admin.users.show', $user) }}"
        style="color:#800e19;font-weight:700;text-decoration:none;"
    >
        Detail →
    </a>
</td>

                    @empty

                        <tr>
                            <td colspan="9" class="um-empty">
                                Tidak ada akun yang ditemukan.
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="um-pagination">
            {{ $users->links() }}
        </div>

    </div>

</div>

@endsection
