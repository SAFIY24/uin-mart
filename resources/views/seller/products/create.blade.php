
@extends('layouts.app')

@section('title', 'Tambah Produk - UIN Mart')

@section('content')

<style>
.sp-create {
    width: calc(100% - 32px);
    max-width: 900px;
    margin: 35px auto 65px;
    color: #29252a;
}

.sp-card {
    background: #ffffff;
    border: 1px solid #e9e0e2;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 24px rgba(0,0,0,.04);
}

.sp-header {
    padding: 25px 30px;
    background: #800e19;
    color: #ffffff;
}

.sp-header h1 {
    margin: 0 0 6px;
    color: #ffffff !important;
    font-size: 26px;
}

.sp-header p {
    margin: 0;
    color: #f6dfe2 !important;
    font-size: 13px;
}

.sp-body {
    padding: 30px;
}

.sp-field {
    margin-bottom: 20px;
}

.sp-field label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
}

.sp-field input,
.sp-field select,
.sp-field textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #ded6d9;
    border-radius: 9px;
    background: #ffffff;
    color: #222222;
    font: inherit;
    box-sizing: border-box;
}

.sp-field textarea {
    min-height: 125px;
    resize: vertical;
}

.sp-field input:focus,
.sp-field select:focus,
.sp-field textarea:focus {
    outline: none;
    border-color: #800e19;
    box-shadow: 0 0 0 3px rgba(128,14,25,.10);
}

.sp-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.sp-help {
    display: block;
    margin-top: 7px;
    font-size: 12px;
    color: #777777;
    line-height: 1.6;
}

.sp-alert {
    padding: 13px 16px;
    margin-bottom: 20px;
    border-radius: 9px;
    background: #fff0f1;
    color: #a32030;
    font-size: 13px;
}

.sp-alert ul {
    margin: 6px 0 0;
    padding-left: 20px;
}

.sp-info {
    padding: 13px 16px;
    margin-bottom: 22px;
    background: #fff8e8;
    border: 1px solid #f1dda6;
    border-radius: 9px;
    color: #7b5a10;
    font-size: 13px;
    line-height: 1.6;
}

/* FOTO TAMBAHAN */

.sp-create-extra-section {
    margin: 24px 0;
    padding: 20px;
    background: #fffafb;
    border: 1px solid #eadde0;
    border-radius: 14px;
}

.sp-create-extra-section h3 {
    margin: 0 0 6px;
    font-size: 17px;
    color: #800e19 !important;
}

.sp-create-extra-section p {
    margin: 0 0 18px;
    color: #777777;
    font-size: 12px;
}

.sp-create-extra-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.sp-create-extra-box {
    min-width: 0;
    background: #ffffff;
    padding: 12px;
    border: 1px solid #e6d8db;
    border-radius: 12px;
    text-align: center;
}

.sp-create-extra-box label {
    display: block;
    margin-bottom: 10px;
    color: #800e19 !important;
    font-size: 12px;
    font-weight: 700;
}

.sp-create-preview-area {
    width: 100%;
    height: 135px;
    margin-bottom: 12px;
}

.sp-create-preview,
.sp-create-empty {
    width: 100% !important;
    height: 135px !important;
    border-radius: 9px;
    box-sizing: border-box;
}

.sp-create-preview {
    display: block;
    object-fit: contain !important;
    background: #f8f4f5;
}

.sp-create-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f4f5;
    border: 1px dashed #d9c2c7;
    color: #9b858a;
    font-size: 12px;
}

.sp-create-extra-box input[type="file"] {
    display: block;
    width: 100% !important;
    min-width: 0;
    max-width: 100%;
    padding: 7px;
    font-size: 11px;
    background: #ffffff;
    border: 1px solid #ded6d9;
    border-radius: 8px;
    box-sizing: border-box;
}

.sp-create-extra-help {
    display: block;
    margin-top: 14px;
    color: #777777;
    font-size: 12px;
    line-height: 1.6;
}

/* SIMPAN PRODUK */

.sp-submit {
    width: 100%;
    padding: 14px 20px;
    background: #800e19;
    color: #ffffff !important;
    border: 0;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: background .2s ease;
}

.sp-submit:hover {
    background: #660b14;
}

/* KEMBALI */

.sp-back-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
}

.sp-back {
    display: inline-block;
    padding: 12px 20px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
}

.sp-back:hover {
    background: #650b14;
    color: #ffffff !important;
}

/* RESPONSIVE */

@media (max-width: 650px) {
    .sp-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .sp-header,
    .sp-body {
        padding: 20px;
    }

    .sp-create-extra-grid {
        grid-template-columns: 1fr;
    }

    .sp-create-extra-section {
        padding: 15px;
    }
}
</style>

<div class="sp-create">

    <div class="sp-card">

        <!-- HEADER -->
        <div class="sp-header">
            <h1>Tambah Produk</h1>
            <p>Tambahkan produk baru ke toko Anda di UIN Mart.</p>
        </div>

        <div class="sp-body">

            <!-- VALIDATION ERRORS -->
            @if($errors->any())
                <div class="sp-alert">
                    <strong>Periksa kembali data produk:</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- INFO -->
            <div class="sp-info">
                Produk baru akan disimpan sebagai
                <strong>belum dipublikasikan</strong>.
                Produk tidak langsung tampil di halaman toko
                sebelum disetujui Admin.
            </div>

            <form
                action="{{ route('seller.products.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <!-- NAMA PRODUK -->
                <div class="sp-field">
                    <label for="name">Nama Produk *</label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        maxlength="200"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Buku Pemrograman Python"
                        required
                    >
                </div>

                <!-- KATEGORI -->
                <div class="sp-field">
                    <label for="category_id">Kategori *</label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >
                        <option value="">Pilih Kategori</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id') == $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- HARGA DAN STOK -->
                <div class="sp-row">

                    <div class="sp-field">
                        <label for="price">Harga (Rp) *</label>

                        <input
                            id="price"
                            type="number"
                            name="price"
                            min="0"
                            max="999999999999.99"
                            step="0.01"
                            value="{{ old('price') }}"
                            placeholder="Contoh: 50000"
                            required
                        >
                    </div>

                    <div class="sp-field">
                        <label for="stock">Stok *</label>

                        <input
                            id="stock"
                            type="number"
                            name="stock"
                            min="0"
                            step="1"
                            value="{{ old('stock', 1) }}"
                            placeholder="Contoh: 10"
                            required
                        >
                    </div>

                </div>

                <!-- DESKRIPSI -->
                <div class="sp-field">
                    <label for="description">Deskripsi Produk</label>

                    <textarea
                        id="description"
                        name="description"
                        maxlength="5000"
                        placeholder="Jelaskan kondisi dan detail produk..."
                    >{{ old('description') }}</textarea>
                </div>

                <!-- FOTO UTAMA -->
                <div class="sp-field">
                    <label for="photo">Foto Produk</label>

                    <input
                        id="photo"
                        type="file"
                        name="photo"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <span class="sp-help">
                        Format JPG, PNG, atau WebP. Maksimal 2 MB.
                    </span>
                </div>

                <!-- FOTO TAMBAHAN -->
                <div class="sp-create-extra-section">

                    <h3>Foto Tambahan Produk</h3>

                    <p>
                        Tambahkan maksimal 3 foto tambahan.
                        Format JPG, PNG, atau WebP.
                    </p>

                    <div class="sp-create-extra-grid">

                        @for($i = 0; $i < 3; $i++)

                            <div class="sp-create-extra-box">

                                <label for="extra_photo_{{ $i }}">
                                    Foto Tambahan {{ $i + 1 }}
                                </label>

                                <div class="sp-create-preview-area">

                                    <img
                                        id="extra_preview_{{ $i }}"
                                        class="sp-create-preview"
                                        alt="Foto tambahan {{ $i + 1 }}"
                                        style="display:none;"
                                    >

                                    <div
                                        id="extra_empty_{{ $i }}"
                                        class="sp-create-empty"
                                    >
                                        Belum ada foto
                                    </div>

                                </div>

                                <input
                                    type="file"
                                    id="extra_photo_{{ $i }}"
                                    name="photos[{{ $i }}]"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    data-preview="extra_preview_{{ $i }}"
                                    data-empty="extra_empty_{{ $i }}"
                                >

                            </div>

                        @endfor

                    </div>

                    <small class="sp-create-extra-help">
                        Setiap foto maksimal 2 MB.
                        Produk baru memerlukan persetujuan Admin
                        sebelum ditampilkan di UIN Mart.
                    </small>

                </div>

                <!-- SIMPAN -->
                <button class="sp-submit" type="submit">
                    Simpan Produk
                </button>

            </form>

            <!-- KEMBALI -->
            <div class="sp-back-actions">

                <a
                    href="{{ route('seller.dashboard') }}"
                    class="sp-back"
                >
                    ← Toko Saya
                </a>

                <a
                    href="{{ route('home') }}"
                    class="sp-back"
                >
                    ← Kembali ke Beranda
                </a>

            </div>

        </div>
    </div>
</div>

<!-- PREVIEW FOTO TAMBAHAN -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll(
        '.sp-create-extra-box input[type="file"]'
    ).forEach(function (input) {

        const preview = document.getElementById(
            input.dataset.preview
        );

        const empty = document.getElementById(
            input.dataset.empty
        );

        let objectUrl = null;

        input.addEventListener('change', function () {

            const file = input.files[0];

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (!file) {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                empty.style.display = 'flex';
                return;
            }

            const allowed = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (
                !allowed.includes(file.type) ||
                file.size > 2 * 1024 * 1024
            ) {
                alert(
                    'Gunakan JPG, PNG, atau WebP. Maksimal 2 MB.'
                );

                input.value = '';
                preview.removeAttribute('src');
                preview.style.display = 'none';
                empty.style.display = 'flex';
                return;
            }

            objectUrl = URL.createObjectURL(file);

            preview.src = objectUrl;
            preview.style.display = 'block';
            empty.style.display = 'none';
        });

        window.addEventListener('pagehide', function () {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
        });
    });
});
</script>

@endsection
