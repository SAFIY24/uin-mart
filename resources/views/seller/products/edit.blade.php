
@extends('layouts.app')

@section('title', 'Edit Produk - UIN Mart')

@section('content')

<style>
.sp-edit {
    width: calc(100% - 32px);
    max-width: 900px;
    margin: 35px auto 65px;
    color: #29252a;
}

.sp-edit-card {
    background: #ffffff;
    border: 1px solid #e9e0e2;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 24px rgba(0, 0, 0, .04);
}

.sp-edit-header {
    padding: 25px 30px;
    background: #800e19;
    color: #ffffff;
}

.sp-edit-header h1 {
    margin: 0 0 6px;
    font-size: 26px;
    font-weight: 800;
    color: #ffffff !important;
}

.sp-edit-header p {
    margin: 0;
    font-size: 13px;
    color: #f6dfe2 !important;
}

.sp-edit-body {
    padding: 30px;
}

.sp-edit-field {
    margin-bottom: 22px;
}

.sp-edit-field > label {
    display: block;
    margin-bottom: 9px;
    font-size: 13px;
    font-weight: 700;
    color: #29252a;
}

.sp-edit-field input,
.sp-edit-field select,
.sp-edit-field textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #ded6d9;
    border-radius: 9px;
    background: #ffffff;
    color: #222222;
    font: inherit;
    box-sizing: border-box;
}

.sp-edit-field input:focus,
.sp-edit-field select:focus,
.sp-edit-field textarea:focus {
    outline: none;
    border-color: #800e19;
}

.sp-edit-field textarea {
    min-height: 125px;
    resize: vertical;
}

.sp-edit-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.sp-edit-notice {
    padding: 14px 16px;
    background: #fff8e8;
    border: 1px solid #f1dda6;
    border-radius: 9px;
    color: #7b5a10;
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 22px;
}

.sp-edit-errors {
    padding: 14px 16px;
    background: #fff0f1;
    color: #a32030;
    border-radius: 9px;
    margin-bottom: 22px;
}

.sp-edit-errors ul {
    margin: 9px 0 0;
    padding-left: 20px;
}

.sp-edit-main-photo {
    width: 165px;
    height: 165px;
    object-fit: contain;
    background: #f8f4f5;
    border: 1px solid #e9e0e2;
    border-radius: 10px;
    display: block;
    margin-bottom: 12px;
}

.sp-edit-help {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    color: #777777;
    line-height: 1.6;
}

/* FOTO TAMBAHAN */

.sp-extra-section {
    margin-top: 12px;
    margin-bottom: 26px;
    padding: 22px;
    background: #fffafb;
    border: 1px solid #eadde0;
    border-radius: 14px;
}

.sp-extra-heading {
    margin-bottom: 15px;
}

.sp-extra-heading h2 {
    margin: 0 0 5px;
    font-size: 17px;
    color: #800e19 !important;
}

.sp-extra-heading p {
    margin: 0;
    color: #777777;
    font-size: 12px;
    line-height: 1.6;
}

.sp-extra-photo-grid {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 14px;
    align-items: start;
}

.sp-extra-photo-box {
    min-width: 0;
    padding: 12px;
    background: #ffffff !important;
    border: 1px solid #e6d8db;
    border-radius: 12px;
    text-align: center;
}

.sp-extra-photo-box > label {
    display: block;
    color: #800e19 !important;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 12px;
}

.sp-extra-preview,
.sp-extra-placeholder {
    width: 100% !important;
    height: 145px !important;
    max-width: 100% !important;
    border-radius: 9px;
    margin-bottom: 12px;
    box-sizing: border-box;
}

.sp-extra-preview {
    display: block;
    object-fit: contain !important;
    background: #f8f4f5;
    border: 1px solid #eee5e7;
}

.sp-extra-placeholder {
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f8f4f5;
    border: 1px dashed #d9c2c7;
    color: #9b858a;
    font-size: 12px;
}

.sp-extra-photo-box input[type="file"] {
    display: block;
    width: 100% !important;
    max-width: 100%;
    min-width: 0;
    padding: 7px !important;
    background: #ffffff !important;
    border: 1px solid #ded6d9;
    border-radius: 8px;
    color: #333333;
    font-size: 11px;
    box-sizing: border-box;
}

.sp-extra-help {
    display: block;
    margin-top: 14px;
    color: #777777;
    font-size: 12px;
    line-height: 1.6;
}

.sp-edit-submit {
    display: block;
    width: 100%;
    padding: 14px 18px;
    background: #800e19 !important;
    color: #ffffff !important;
    border: 0;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: background .2s ease;
}

.sp-edit-submit:hover {
    background: #650b14 !important;
}

.sp-edit-back {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 20px;
    background: #800e19;
    color: #ffffff !important;
    border-radius: 9px;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 700;
}

.sp-edit-back:hover {
    background: #650b14;
    color: #ffffff !important;
}

@media (max-width: 650px) {
    .sp-edit-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .sp-edit-header,
    .sp-edit-body {
        padding: 20px;
    }

    .sp-extra-section {
        padding: 15px;
    }

    .sp-extra-photo-grid {
        grid-template-columns: 1fr !important;
    }

    .sp-extra-preview,
    .sp-extra-placeholder {
        height: 170px !important;
    }
}
</style>

<div class="sp-edit">
    <div class="sp-edit-card">

        <div class="sp-edit-header">
            <h1>Edit Produk</h1>
            <p>Perbarui informasi produk toko Anda.</p>
        </div>

        <div class="sp-edit-body">

            @if($errors->any())
                <div class="sp-edit-errors">
                    <strong>Periksa kembali data:</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="sp-edit-notice">
                Perubahan nama, harga, kategori, deskripsi,
                atau foto produk memerlukan persetujuan
                ulang dari Admin sebelum dipublikasikan.
            </div>

            <form
                action="{{ route('seller.products.update', $product->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                <!-- NAMA PRODUK -->
                <div class="sp-edit-field">
                    <label for="name">Nama Produk *</label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        maxlength="200"
                        value="{{ old('name', $product->name) }}"
                        required
                    >
                </div>

                <!-- KATEGORI -->
                <div class="sp-edit-field">
                    <label for="category_id">Kategori *</label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >
                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id', $product->category_id) == $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- HARGA DAN STOK -->
                <div class="sp-edit-row">

                    <div class="sp-edit-field">
                        <label for="price">Harga (Rp) *</label>

                        <input
                            id="price"
                            type="number"
                            name="price"
                            min="0"
                            step="0.01"
                            value="{{ old('price', $product->price) }}"
                            required
                        >
                    </div>

                    <div class="sp-edit-field">
                        <label for="stock">Stok *</label>

                        <input
                            id="stock"
                            type="number"
                            name="stock"
                            min="0"
                            step="1"
                            value="{{ old('stock', $product->stock) }}"
                            required
                        >
                    </div>

                </div>

                <!-- DESKRIPSI -->
                <div class="sp-edit-field">
                    <label for="description">Deskripsi Produk</label>

                    <textarea
                        id="description"
                        name="description"
                        maxlength="5000"
                    >{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- FOTO UTAMA -->
                <div class="sp-edit-field">
                    <label for="photo">Ganti Foto Produk</label>

                    @if($product->image)
                        @php
                            $mainImageUrl = str_starts_with($product->image, 'products/')
                                ? asset('storage/' . $product->image)
                                : asset('uploads/products/' . basename($product->image));
                        @endphp

                        <img
                            class="sp-edit-main-photo"
                            src="{{ $mainImageUrl }}"
                            alt="{{ $product->name }}"
                        >
                    @endif

                    <input
                        id="photo"
                        type="file"
                        name="photo"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="sp-edit-help">
                        Kosongkan jika tidak ingin mengganti foto utama.
                        Maksimal 2 MB.
                    </small>
                </div>

                <!-- TIGA FOTO TAMBAHAN -->
                <div class="sp-extra-section">

                    <div class="sp-extra-heading">
                        <h2>Foto Tambahan Produk</h2>
                        <p>
                            Tambahkan atau ganti foto tambahan.
                            Maksimal 3 foto, masing-masing 2 MB.
                        </p>
                    </div>

                    <div class="sp-extra-photo-grid">

                        @for($i = 0; $i < 3; $i++)

                            @php
                                $existingPhoto = $product->images
                                    ->firstWhere('sort_order', $i + 1);
                            @endphp

                            <div class="sp-extra-photo-box">

                                <label for="extra_photo_{{ $i }}">
                                    Foto Tambahan {{ $i + 1 }}
                                </label>

                                @if($existingPhoto)

                                    <img
                                        class="sp-extra-preview"
                                        src="{{ asset('storage/' . $existingPhoto->image_path) }}"
                                        alt="Foto tambahan {{ $i + 1 }}"
                                    >

                                @else

                                    <div class="sp-extra-placeholder">
                                        Belum ada foto
                                    </div>

                                @endif

                                <input
                                    id="extra_photo_{{ $i }}"
                                    type="file"
                                    name="extra_photos[{{ $i }}]"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                            </div>

                        @endfor

                    </div>

                    <small class="sp-extra-help">
                        Pilih foto pada kotak yang ingin diganti.
                        Kotak yang tidak diubah tetap menggunakan foto lama.
                        Perubahan foto memerlukan persetujuan ulang Admin.
                    </small>

                </div>

                <!-- SIMPAN -->
                <button
                    type="submit"
                    class="sp-edit-submit"
                >
                    Simpan Perubahan
                </button>

            </form>

            <a
                class="sp-edit-back"
                href="{{ route('seller.dashboard') }}"
            >
                ← Kembali ke Toko Saya
            </a>

        </div>
    </div>
</div>

@endsection
