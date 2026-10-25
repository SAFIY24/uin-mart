
@extends('layouts.admin')

@section('title', ($product->exists ? 'Edit' : 'Tambah') . ' Produk · UIN Mart')

@section('admin_content')

@php
    $isEdit = $product->exists;

    $formAction = $isEdit
        ? route('admin.products.update', $product)
        : route('admin.products.store');

    $productStatus = old('status', $product->status ?? 'active');

    $existingImage = null;

    if ($product->image) {
        $existingImage = str_starts_with($product->image, 'products/')
            ? asset('storage/' . $product->image)
            : asset('uploads/products/' . basename($product->image));
    }
@endphp

<div class="ua-product-form-page">

    <div class="ua-form-page-header">
        <div>
            <p class="ua-eyebrow">ADMIN / PRODUK</p>

            <h1>{{ $isEdit ? 'Edit Produk' : 'Tambah Produk' }}</h1>

            <p class="ua-subtitle">
                {{ $isEdit
                    ? 'Perbarui informasi produk yang sudah terdaftar.'
                    : 'Tambahkan produk baru ke katalog UIN Mart.' }}
            </p>
        </div>

        <a href="{{ route('admin.products.index') }}"
           class="ua-btn ua-btn-light">
            ← Kembali
        </a>
    </div>

    <div class="ua-product-form-layout">

        <!-- FORM INFORMASI PRODUK -->
        <section class="ua-panel ua-form-card">

            <div class="ua-form-section-heading">
                <h2>Informasi Produk</h2>
                <p>Lengkapi informasi produk dengan benar.</p>
            </div>

            <form
                id="productForm"
                method="POST"
                action="{{ $formAction }}"
                enctype="multipart/form-data"
            >
                @csrf

                @if($isEdit)
                    @method('PUT')
                @endif

                <!-- NAMA PRODUK -->
                <div class="ua-form-field">
                    <label for="name">
                        Nama Produk <span>*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $product->name) }}"
                        placeholder="Masukkan nama produk"
                        maxlength="200"
                        required
                    >

                    @error('name')
                        <small class="ua-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <!-- KATEGORI -->
                <div class="ua-form-field">
                    <label for="category_id">
                        Kategori <span>*</span>
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >
                        <option value="">Pilih kategori</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id', $product->category_id) == $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <small class="ua-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <!-- DESKRIPSI -->
                <div class="ua-form-field">
                    <label for="description">Deskripsi</label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        maxlength="5000"
                        placeholder="Tuliskan deskripsi produk..."
                    >{{ old('description', $product->description) }}</textarea>

                    @error('description')
                        <small class="ua-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <!-- HARGA DAN STOK -->
                <div class="ua-form-two-col">

                    <div class="ua-form-field">
                        <label for="price">
                            Harga (Rp) <span>*</span>
                        </label>

                        
<input
    id="price"
    type="number"
    name="price"
    min="0"
    max="9999999999"
    step="1"
    inputmode="numeric"
    value="{{ old('price', $product->price !== null ? (int) $product->price : '') }}"
    placeholder="Contoh: 100000"
    required
>

<small class="ua-field-help">
    Masukkan harga tanpa titik.
    Contoh: 100000 = Rp 100.000.
</small>

                    </div>

                    <div class="ua-form-field">
                        <label for="stock">
                            Stok <span>*</span>
                        </label>

                        <input
                            id="stock"
                            type="number"
                            name="stock"
                            min="0"
                            step="1"
                            value="{{ old('stock', $product->stock ?? 0) }}"
                            required
                        >

                        @error('stock')
                            <small class="ua-field-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- STATUS -->
                <div class="ua-form-field">
                    <label for="status">
                        Status Produk <span>*</span>
                    </label>

                    <select id="status" name="status" required>
                        <option
                            value="active"
                            @selected($productStatus === 'active')
                        >
                            Aktif
                        </option>

                        <option
                            value="inactive"
                            @selected($productStatus === 'inactive')
                        >
                            Nonaktif
                        </option>
                    </select>

                    @error('status')
                        <small class="ua-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <!-- FOTO -->
                <div class="ua-form-field">
                    <label for="photo">Foto Produk</label>

                    <input
                        id="photo"
                        type="file"
                        name="photo"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small class="ua-field-help">
                        Format JPG, PNG, atau WebP. Maksimal 2 MB.
                        Foto langsung terlihat di Live Preview.
                    </small>

                    @error('photo')
                        <small class="ua-field-error">{{ $message }}</small>
                    @enderror
                </div>

                
<!-- FOTO TAMBAHAN PRODUK -->
<div class="ua-extra-section">

    <div class="ua-extra-heading">
        <h3>Foto Tambahan Produk</h3>
        <p>
            Tambahkan maksimal 3 foto tambahan.
            Maksimal 2 MB per foto.
        </p>
    </div>

    <div class="ua-extra-grid">

        @for($i = 0; $i < 3; $i++)

            @php
                $existingExtra = $isEdit
                    ? $product->images->firstWhere('sort_order', $i + 1)
                    : null;
            @endphp

            <div class="ua-extra-box">

                <label for="extra_photo_{{ $i }}">
                    Foto Tambahan {{ $i + 1 }}
                </label>

                <div class="ua-extra-image-area">

                    <img
                        id="extra_preview_{{ $i }}"
                        class="ua-extra-preview"
                        src="{{ $existingExtra ? asset('storage/' . $existingExtra->image_path) : '' }}"
                        alt="Foto tambahan {{ $i + 1 }}"
                        style="{{ $existingExtra ? '' : 'display:none;' }}"
                    >

                    <div
                        id="extra_empty_{{ $i }}"
                        class="ua-extra-empty"
                        style="{{ $existingExtra ? 'display:none;' : '' }}"
                    >
                        Belum ada foto
                    </div>

                </div>

                <input
                    type="file"
                    id="extra_photo_{{ $i }}"
                    name="extra_photos[{{ $i }}]"
                    accept=".jpg,.jpeg,.png,.webp"
                    data-extra-preview="extra_preview_{{ $i }}"
                    data-extra-empty="extra_empty_{{ $i }}"
                >

            </div>

        @endfor

    </div>

    <small class="ua-extra-help">
        Pilih foto pada kotak yang ingin ditambahkan atau diganti.
        Foto lama tetap digunakan jika tidak dipilih foto baru.
    </small>

</div>


                <!-- BUTTONS -->
                <div class="ua-form-actions">
                    <a
                        href="{{ route('admin.products.index') }}"
                        class="ua-btn ua-btn-light"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="ua-btn ua-btn-primary"
                    >
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' }}
                    </button>
                </div>
            </form>
        </section>

        <!-- LIVE PREVIEW -->
        <aside
            class="ua-panel ua-product-preview ua-live-preview"
            id="livePreview"
            aria-label="Live Preview Produk"
        >

            <div class="ua-live-preview-heading">
                <h2>Live Preview</h2>
                <span class="ua-live-label">Pratinjau</span>
            </div>

            <p class="ua-live-description">
                Preview otomatis berubah saat Anda mengisi formulir.
            </p>

            <!-- GAMBAR -->
            <div class="ua-live-image-box">

                <img
                    id="previewImage"
                    src="{{ $existingImage ?? '' }}"
                    alt="Pratinjau foto produk"
                    style="{{ $existingImage ? '' : 'display:none;' }}"
                >

                <div
                    class="ua-live-image-placeholder"
                    id="previewPlaceholder"
                    style="{{ $existingImage ? 'display:none;' : '' }}"
                >
                    <span class="ua-live-image-icon">▣</span>
                    <p>Belum ada foto produk</p>
                </div>

                <span
                    class="ua-live-status"
                    id="previewStatus"
                >
                    Aktif
                </span>
            </div>

            <!-- INFORMASI -->
            <div class="ua-live-product-info">

                <h3 id="previewName">Nama Produk</h3>

                <p
                    class="ua-live-category"
                    id="previewCategory"
                >
                    Belum ada kategori
                </p>

                <strong
                    class="ua-live-price"
                    id="previewPrice"
                >
                    Rp 0
                </strong>

                <p
                    class="ua-live-stock"
                    id="previewStock"
                >
                    Stok: 0
                </p>

                <div class="ua-live-description-box">
                    <strong>Deskripsi</strong>
                    <p id="previewDescription">
                        Belum ada deskripsi produk.
                    </p>
                </div>
            </div>

            <div class="ua-live-note">
                Ini hanya pratinjau. Perubahan belum tersimpan
                sampai Anda menekan tombol Simpan.
            </div>
        </aside>
    </div>
</div>

<style>
/* =========================================
   UIN MART - LIVE PRODUCT PREVIEW
   ========================================= */

.ua-admin-body .ua-product-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 24px;
    align-items: start;
}

.ua-admin-body .ua-product-form-page .ua-form-two-col {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.ua-admin-body .ua-live-preview {
    padding: 22px;
    min-width: 0;
    position: sticky;
    top: 22px;
}

.ua-live-preview-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

.ua-live-preview-heading h2 {
    margin: 0;
    color: #32181e;
    font-size: 19px;
}

.ua-live-label {
    background: #fff0db;
    color: #986000;
    border-radius: 20px;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 800;
}

.ua-live-description {
    margin: 10px 0 18px;
    color: #8c8084;
    font-size: 12px;
    line-height: 1.6;
}

.ua-live-image-box {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 220px;
    overflow: hidden;
    border-radius: 12px;
    background: #f6f4f4;
    border: 1px solid #eee5e7;
}

.ua-live-image-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.ua-live-image-placeholder {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    color: #9a9296;
    text-align: center;
    gap: 7px;
    font-size: 12px;
}

.ua-live-image-icon {
    font-size: 40px;
    color: #c7bdc1;
}

.ua-live-status {
    position: absolute;
    top: 10px;
    left: 10px;
    border-radius: 6px;
    padding: 5px 10px;
    background: #e4f5ea;
    color: #087345;
    font-size: 10px;
    font-weight: 800;
}

.ua-live-status.inactive {
    background: #f1e8eb;
    color: #8e5662;
}

.ua-live-product-info {
    display: flex;
    flex-direction: column;
    gap: 9px;
    padding-top: 18px;
}

.ua-live-product-info h3 {
    margin: 0;
    font-size: 18px;
    color: #30242a;
    overflow-wrap: anywhere;
}

.ua-live-category {
    margin: 0;
    color: #8c8084;
    font-size: 12px;
}

.ua-live-price {
    color: #800e19;
    font-size: 23px;
    font-weight: 850;
}

.ua-live-stock {
    margin: 0;
    color: #14794d;
    font-size: 12px;
    font-weight: 700;
}

.ua-live-stock.is-out {
    color: #c23e46;
}

.ua-live-description-box {
    margin-top: 8px;
    padding: 13px;
    background: #faf8f8;
    border-radius: 9px;
}

.ua-live-description-box strong {
    font-size: 12px;
    color: #5a444a;
}

.ua-live-description-box p {
    margin: 8px 0 0;
    font-size: 12px;
    color: #81767b;
    line-height: 1.6;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.ua-live-note {
    margin-top: 18px;
    padding: 12px;
    border-radius: 9px;
    background: #fff7e9;
    color: #9a701e;
    font-size: 11px;
    line-height: 1.6;
}

@media (max-width: 1050px) {
    .ua-admin-body .ua-product-form-layout {
        grid-template-columns: 1fr;
    }

    .ua-admin-body .ua-live-preview {
        position: static;
    }
}

@media (max-width: 600px) {
    .ua-admin-body .ua-product-form-page .ua-form-two-col {
        grid-template-columns: 1fr;
    }
}


/* FOTO TAMBAHAN ADMIN */

.ua-extra-section {
    margin: 22px 0;
    padding: 20px;
    background: #fffafb;
    border: 1px solid #eadde0;
    border-radius: 14px;
}

.ua-extra-heading h3 {
    margin: 0 0 6px;
    font-size: 17px;
    color: #800e19 !important;
}

.ua-extra-heading p {
    margin: 0 0 18px;
    font-size: 12px;
    color: #777;
}

.ua-extra-grid {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 12px;
}

.ua-extra-box {
    min-width: 0;
    padding: 12px;
    background: #ffffff !important;
    border: 1px solid #e6d8db;
    border-radius: 12px;
    text-align: center;
}

.ua-extra-box label {
    display: block;
    margin-bottom: 10px;
    font-size: 12px;
    font-weight: 700;
    color: #800e19 !important;
}

.ua-extra-image-area {
    width: 100%;
    height: 125px;
    margin-bottom: 12px;
}

.ua-extra-preview,
.ua-extra-empty {
    width: 100% !important;
    height: 125px !important;
    border-radius: 9px;
    box-sizing: border-box;
}

.ua-extra-preview {
    object-fit: contain !important;
    background: #f8f4f5;
}

.ua-extra-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f4f5;
    border: 1px dashed #d9c2c7;
    color: #9b858a;
    font-size: 12px;
}

.ua-extra-box input[type="file"] {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    padding: 7px;
    font-size: 11px;
    box-sizing: border-box;
}

.ua-extra-help {
    display: block;
    margin-top: 14px;
    color: #777;
    font-size: 12px;
    line-height: 1.6;
}

@media (max-width: 700px) {
    .ua-extra-grid {
        grid-template-columns: 1fr !important;
    }
}


</style>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    function initProductPreview() {
        const form = document.getElementById('productForm');

        if (!form) return;

        const nameInput = form.querySelector('[name="name"]');
        const categoryInput = form.querySelector('[name="category_id"]');
        const descriptionInput = form.querySelector('[name="description"]');
        const priceInput = form.querySelector('[name="price"]');
        const stockInput = form.querySelector('[name="stock"]');
        const statusInput = form.querySelector('[name="status"]');
        const photoInput = form.querySelector('[name="photo"]');

        const previewName = document.getElementById('previewName');
        const previewCategory = document.getElementById('previewCategory');
        const previewDescription = document.getElementById('previewDescription');
        const previewPrice = document.getElementById('previewPrice');
        const previewStock = document.getElementById('previewStock');
        const previewStatus = document.getElementById('previewStatus');

        const previewImage = document.getElementById('previewImage');
        const previewPlaceholder = document.getElementById('previewPlaceholder');

        const originalImage = previewImage.getAttribute('src');
        let currentObjectUrl = null;

        const rupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });

        function updatePreview() {
            const productName = nameInput.value.trim();
            const description = descriptionInput.value.trim();

            const categoryOption = categoryInput.options[
                categoryInput.selectedIndex
            ];

            const categoryName = categoryInput.value
                ? categoryOption.textContent.trim()
                : 'Belum ada kategori';

            const price = Number(priceInput.value);
            const stock = Number(stockInput.value);

            const isActive = statusInput.value === 'active';

            previewName.textContent = productName || 'Nama Produk';
            previewCategory.textContent = categoryName;

            previewDescription.textContent =
                description || 'Belum ada deskripsi produk.';

            previewPrice.textContent = rupiah.format(
                Number.isFinite(price) && price > 0 ? price : 0
            );

            previewStock.textContent = stock > 0
                ? 'Stok: ' + stock
                : 'Stok habis';

            previewStock.classList.toggle('is-out', stock <= 0);

            previewStatus.textContent = isActive
                ? 'Aktif'
                : 'Nonaktif';

            previewStatus.classList.toggle('inactive', !isActive);
        }

        function updateImage() {
            const file = photoInput.files && photoInput.files[0];

            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }

            if (file) {
                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!allowedTypes.includes(file.type)) {
                    photoInput.value = '';
                } else if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran foto maksimal 2 MB.');
                    photoInput.value = '';
                } else {
                    currentObjectUrl = URL.createObjectURL(file);
                    previewImage.src = currentObjectUrl;
                    previewImage.style.display = 'block';
                    previewPlaceholder.style.display = 'none';
                    return;
                }
            }

            if (originalImage) {
                previewImage.src = originalImage;
                previewImage.style.display = 'block';
                previewPlaceholder.style.display = 'none';
            } else {
                previewImage.removeAttribute('src');
                previewImage.style.display = 'none';
                previewPlaceholder.style.display = 'flex';
            }
        }

        previewImage.addEventListener('error', function () {
            previewImage.style.display = 'none';
            previewPlaceholder.style.display = 'flex';
        });

        [
            nameInput,
            descriptionInput,
            priceInput,
            stockInput
        ].forEach(function (input) {
            input.addEventListener('input', updatePreview);
        });

        categoryInput.addEventListener('change', updatePreview);
        statusInput.addEventListener('change', updatePreview);
        photoInput.addEventListener('change', updateImage);

        window.addEventListener('pagehide', function () {
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
            }
        });

        updatePreview();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductPreview);
    } else {
        initProductPreview();
    }
})();
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll(
        'input[data-extra-preview]'
    ).forEach(function (input) {

        const preview = document.getElementById(
            input.dataset.extraPreview
        );

        const empty = document.getElementById(
            input.dataset.extraEmpty
        );

        const originalSrc = preview.getAttribute('src');
        let objectUrl = null;

        input.addEventListener('change', function () {

            const file = input.files[0];

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (file) {
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
                } else {
                    objectUrl = URL.createObjectURL(file);

                    preview.src = objectUrl;
                    preview.style.display = 'block';
                    empty.style.display = 'none';

                    return;
                }
            }

            if (originalSrc) {
                preview.src = originalSrc;
                preview.style.display = 'block';
                empty.style.display = 'none';
            } else {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                empty.style.display = 'flex';
            }
        });

        window.addEventListener('pagehide', function () {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
        });
    });
});
</script>


@endpush
