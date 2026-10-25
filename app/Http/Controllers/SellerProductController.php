<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SellerProductController extends Controller
{
    // Memastikan akun sudah disetujui sebagai penjual
    private function ensureApprovedSeller(Request $request): void
    {
        $approved = SellerApplication::where(
            'user_id',
            $request->user()->id
        )
            ->where('status', 'approved')
            ->exists();

        abort_unless(
            $approved,
            403,
            'Akun penjual belum disetujui.'
        );
    }

    // Menampilkan formulir tambah produk
    public function create(Request $request)
    {
        $this->ensureApprovedSeller($request);

        $categories = Category::orderBy('name')->get();

        return view(
            'seller.products.create',
            compact('categories')
        );
    }

    // Menyimpan produk baru milik penjual
    public function store(Request $request)
    {
        $this->ensureApprovedSeller($request);

        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',

            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        unset($data['photo'], $data['photos']);

        $uploadedPaths = [];

        try {
            if ($request->hasFile('photo')) {
                $mainPath = $request->file('photo')
                    ->store('products', 'public');

                if (!$mainPath) {
                    throw new \RuntimeException('Gagal mengunggah foto utama.');
                }

                $uploadedPaths[] = $mainPath;
                $data['image'] = $mainPath;
            }

            $extraPaths = [];

            foreach ($request->file('photos', []) as $index => $photo) {
                $path = $photo->store('products', 'public');

                if (!$path) {
                    throw new \RuntimeException('Gagal mengunggah foto tambahan.');
                }

                $uploadedPaths[] = $path;

                $extraPaths[] = $path;
            }

            DB::transaction(function () use (
                $request,
                $data,
                $extraPaths
            ) {
                $product = new Product($data);

                $product->seller_id = $request->user()->id;

                // Produk baru harus disetujui Admin
                $product->approval_status = 'pending';
                $product->status = 'inactive';

                $product->save();

                // Simpan maksimal 3 foto tambahan
                foreach ($extraPaths as $index => $path) {
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index + 1,
                    ]);
                }
            });

        } catch (\Throwable $e) {
            if (!empty($uploadedPaths)) {
                Storage::disk('public')->delete($uploadedPaths);
            }

            throw $e;
        }

        return redirect()
            ->route('seller.dashboard')
            ->with(
                'success',
                'Produk berhasil disimpan dan menunggu persetujuan Admin.'
            );
    }

    // Menampilkan formulir edit produk
    public function edit(Request $request, Product $product)
    {
        $this->ensureApprovedSeller($request);

        // Seller hanya dapat mengedit produknya sendiri
        abort_unless(
            (int) $product->seller_id === (int) $request->user()->id,
            403,
            'Anda tidak memiliki izin mengedit produk ini.'
        );

        $categories = Category::orderBy('name')->get();

        $product->load('images');

        return view(
            'seller.products.edit',
            compact('product', 'categories')
        );
    }

    // Menyimpan perubahan produk
    public function update(Request $request, Product $product)
    {
        $this->ensureApprovedSeller($request);

        abort_unless(
            (int) $product->seller_id === (int) $request->user()->id,
            403,
            'Anda tidak memiliki izin mengedit produk ini.'
        );

        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',

            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Tiga posisi foto tambahan
            'extra_photos' => 'nullable|array',
            'extra_photos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $extraUploads = $request->file('extra_photos', []);

        // Hanya menerima posisi 0, 1, dan 2
        foreach ($extraUploads as $index => $file) {
            if (!in_array((string) $index, ['0', '1', '2'], true)) {
                throw ValidationException::withMessages([
                    'extra_photos' => 'Posisi foto tambahan tidak valid.',
                ]);
            }
        }

        unset($data['photo'], $data['extra_photos']);

        // Perubahan selain stok memerlukan persetujuan ulang
        $needsApproval =
            $product->name !== $data['name']
            || (int) $product->category_id !== (int) $data['category_id']
            || (string) ($product->description ?? '') !== (string) ($data['description'] ?? '')
            || round((float) $product->price, 2) !== round((float) $data['price'], 2)
            || $request->hasFile('photo')
            || count($extraUploads) > 0;

        $oldMainImage = $product->image;

        $newMainImage = null;

        $newExtraPaths = [];
        $oldExtraPaths = [];

        $uploadedPaths = [];

        try {
            // Upload foto utama baru
            if ($request->hasFile('photo')) {
                $newMainImage = $request->file('photo')
                    ->store('products', 'public');

                if (!$newMainImage) {
                    throw new \RuntimeException('Gagal mengunggah foto utama.');
                }

                $uploadedPaths[] = $newMainImage;

                $data['image'] = $newMainImage;
            }

            // Upload foto tambahan berdasarkan posisi
            foreach ($extraUploads as $index => $file) {
                $path = $file->store('products', 'public');

                if (!$path) {
                    throw new \RuntimeException('Gagal mengunggah foto tambahan.');
                }

                $uploadedPaths[] = $path;

                $newExtraPaths[(int) $index] = $path;
            }

            DB::transaction(function () use (
                $product,
                $data,
                $needsApproval,
                $newExtraPaths,
                &$oldExtraPaths
            ) {
                $product->fill($data);

                if ($needsApproval) {
                    $product->approval_status = 'pending';
                    $product->status = 'inactive';
                }

                $product->save();

                // Perbarui hanya foto yang dipilih
                foreach ($newExtraPaths as $index => $path) {
                    $position = $index + 1;

                    $existingImage = $product->images()
                        ->where('sort_order', $position)
                        ->first();

                    if ($existingImage) {
                        $oldExtraPaths[] = $existingImage->image_path;

                        $existingImage->update([
                            'image_path' => $path,
                        ]);
                    } else {
                        $product->images()->create([
                            'image_path' => $path,
                            'sort_order' => $position,
                        ]);
                    }
                }
            });

        } catch (\Throwable $e) {
            // Hapus file baru jika penyimpanan gagal
            if (!empty($uploadedPaths)) {
                Storage::disk('public')->delete($uploadedPaths);
            }

            throw $e;
        }

        // Hapus foto utama lama hanya setelah berhasil
        if (
            $newMainImage
            && $oldMainImage
            && $oldMainImage !== $newMainImage
            && str_starts_with($oldMainImage, 'products/')
        ) {
            Storage::disk('public')->delete($oldMainImage);
        }

        // Hapus foto tambahan yang sudah diganti
        if (!empty($oldExtraPaths)) {
            Storage::disk('public')->delete($oldExtraPaths);
        }

        return redirect()
            ->route('seller.dashboard')
            ->with(
                'success',
                $needsApproval
                    ? 'Perubahan produk disimpan dan menunggu persetujuan Admin.'
                    : 'Stok produk berhasil diperbarui.'
            );
    }
}
