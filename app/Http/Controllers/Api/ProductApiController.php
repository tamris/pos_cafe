<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Services\HppCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductApiController extends Controller
{
    protected HppCalculationService $hppService;

    public function __construct(HppCalculationService $hppService)
    {
        $this->hppService = $hppService;
    }

    /**
     * 1. List Menu & Produk dengan Filter dan HPP Summary.
     * GET /api/admin/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category:id,name', 'ingredients'])
            ->orderBy('name', 'asc');

        // Filter: Pencarian nama, SKU, atau barcode
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter: Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Filter: Status arsip (soft-deleted) & aktif
        $status = $request->query('status');
        $isArchived = filter_var($request->query('is_archived'), FILTER_VALIDATE_BOOLEAN);

        if ($status === 'archived' || $isArchived) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($status !== 'archived' && !$isArchived) {
            if ($status === 'active' || ($request->has('is_active') && filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN))) {
                $query->where('is_active', true);
            } elseif ($status === 'inactive' || ($request->has('is_active') && !filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN))) {
                $query->where('is_active', false);
            }
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $paginate = filter_var($request->query('paginate', true), FILTER_VALIDATE_BOOLEAN);

        if ($paginate) {
            $paginator = $query->paginate($perPage);
            $items = collect($paginator->items())->map(fn ($p) => $this->transformProduct($p));

            return response()->json([
                'success' => true,
                'message' => 'Daftar produk berhasil dimuat.',
                'data' => $items,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        }

        $items = $query->get()->map(fn ($p) => $this->transformProduct($p));

        return response()->json([
            'success' => true,
            'message' => 'Daftar seluruh produk berhasil dimuat.',
            'data' => $items,
            'total' => $items->count(),
        ]);
    }

    /**
     * 2. Detail Menu Lengkap Beserta Resep & Analisis HPP.
     * GET /api/admin/products/{id}
     */
    public function show($id): JsonResponse
    {
        $product = Product::with(['category:id,name', 'ingredients'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $productData = $this->transformProduct($product, true);

        // Simulasi analisis HPP terkini
        $ingredientsArray = $product->ingredients->map(function ($ing) {
            return [
                'name' => $ing->name,
                'amount' => (float) $ing->amount,
                'unit' => $ing->unit,
                'buy_price' => (float) $ing->buy_price,
                'buy_amount' => (float) $ing->buy_amount,
                'buy_unit' => $ing->buy_unit,
            ];
        })->toArray();

        $meta = is_array($product->ai_pricing_data) ? $product->ai_pricing_data : [];

        $hppAnalysis = $this->hppService->calculate($ingredientsArray, [
            'mode_alokasi_ops' => $meta['mode_alokasi_ops'] ?? 'manual',
            'operational_cost' => (float) $product->operational_cost,
            'target_penjualan_bulanan' => $meta['target_penjualan_bulanan'] ?? 3000,
            'kenaikan_persen' => $meta['kenaikan_persen'] ?? 0,
            'selling_price' => (float) $product->price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil dimuat.',
            'data' => array_merge($productData, [
                'hpp_analysis' => $hppAnalysis,
            ]),
        ]);
    }

    /**
     * 3. Tambah Produk Baru dengan Resep Bahan Baku & HPP.
     * POST /api/admin/products
     */
    public function store(Request $request): JsonResponse
    {
        $payload = $this->preprocessRequestPayload($request);

        $skuRule = Rule::unique('products', 'sku')->whereNull('deleted_at');
        $barcodeRule = Rule::unique('products', 'barcode')->whereNull('deleted_at');

        $validator = Validator::make($payload, [
            'name' => 'required|string|min:2|max:255',
            'sku' => ['nullable', 'string', 'max:50', $skuRule],
            'barcode' => ['nullable', 'string', 'max:100', $barcodeRule],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'operational_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'ingredients' => 'nullable|array',
            'ingredients.*.name' => 'required_with:ingredients|string|max:255',
            'ingredients.*.amount' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.unit' => 'required_with:ingredients|string|max:50',
            'ingredients.*.buy_price' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.buy_amount' => 'nullable|numeric|min:0',
            'ingredients.*.buy_unit' => 'nullable|string|max:50',
            'mode_alokasi_ops' => 'nullable|in:manual,rincian',
            'target_penjualan_bulanan' => 'nullable|integer|min:1',
            'selected_tier' => 'nullable|string',
            'kenaikan_persen' => 'nullable|numeric|min:0|max:100',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'price.required' => 'Harga jual wajib diisi.',
            'sku.unique' => 'SKU sudah digunakan oleh produk lain.',
            'barcode.unique' => 'Barcode sudah terdaftar pada produk lain.',
            'image.max' => 'Ukuran gambar maksimal adalah 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi data produk gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $payload) {
                // 1. Generate SKU jika kosong
                $sku = !empty(trim((string) ($payload['sku'] ?? '')))
                    ? trim($payload['sku'])
                    : $this->generateUniqueSku((int) $payload['category_id'], $payload['name']);

                // 2. Hitung HPP & Resep via Service
                $ingredients = $payload['ingredients'] ?? [];
                $operationalCost = (float) ($payload['operational_cost'] ?? 0);
                $modeAlokasi = $payload['mode_alokasi_ops'] ?? 'manual';
                $targetUnits = (int) ($payload['target_penjualan_bulanan'] ?? 3000);
                $kenaikan = (float) ($payload['kenaikan_persen'] ?? 0);

                $calculation = null;
                $finalHpp = (float) ($payload['harga_beli'] ?? 0);

                if (!empty($ingredients)) {
                    $calculation = $this->hppService->calculate($ingredients, [
                        'mode_alokasi_ops' => $modeAlokasi,
                        'operational_cost' => $operationalCost,
                        'target_penjualan_bulanan' => $targetUnits,
                        'kenaikan_persen' => $kenaikan,
                        'selling_price' => (float) $payload['price'],
                    ]);
                    $finalHpp = (float) ($calculation['summary']['effective_hpp'] ?? $finalHpp);
                    $operationalCost = (float) ($calculation['summary']['operational_cost_per_unit'] ?? $operationalCost);
                }

                // 3. Upload Gambar jika ada
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('products', 'public');
                }

                // 4. Susun Metadata Pricing
                $pricingMetadata = [
                    'mode_alokasi_ops' => $modeAlokasi,
                    'manual_alokasi_nominal' => ($modeAlokasi === 'manual') ? $operationalCost : 0,
                    'selected_tier' => $payload['selected_tier'] ?? 'standar',
                    'kenaikan_persen' => $kenaikan,
                    'target_penjualan_bulanan' => $targetUnits,
                ];

                // 5. Simpan Record Produk
                $product = Product::create([
                    'name' => trim($payload['name']),
                    'category_id' => (int) $payload['category_id'],
                    'sku' => $sku,
                    'barcode' => !empty(trim((string) ($payload['barcode'] ?? ''))) ? trim($payload['barcode']) : null,
                    'description' => $payload['description'] ?? null,
                    'price' => (float) $payload['price'],
                    'harga_beli' => $finalHpp,
                    'operational_cost' => $operationalCost,
                    'ai_pricing_data' => $pricingMetadata,
                    'is_active' => isset($payload['is_active']) ? (bool) $payload['is_active'] : true,
                    'image' => $imagePath,
                ]);

                // 6. Simpan Bahan Baku (Ingredients)
                if (!empty($ingredients)) {
                    foreach ($calculation['ingredients'] as $ing) {
                        ProductIngredient::create([
                            'product_id' => $product->id,
                            'name' => $ing['name'],
                            'amount' => (float) $ing['amount'],
                            'unit' => $ing['unit'],
                            'buy_price' => (float) $ing['buy_price'],
                            'buy_amount' => (float) $ing['buy_amount'],
                            'buy_unit' => $ing['buy_unit'],
                            'subtotal' => (float) $ing['subtotal'],
                        ]);
                    }
                }

                $product->load(['category:id,name', 'ingredients']);

                return response()->json([
                    'success' => true,
                    'message' => "Menu '{$product->name}' berhasil ditambahkan ke database.",
                    'data' => $this->transformProduct($product, true),
                ], 201);
            });
        } catch (\Throwable $e) {
            Log::error('[API Product Store Error] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan menu baru: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 4. Update Produk & Resep Bahan Baku.
     * POST /api/admin/products/{id} OR PUT /api/admin/products/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $payload = $this->preprocessRequestPayload($request);

        $skuRule = Rule::unique('products', 'sku')->ignore($product->id)->whereNull('deleted_at');
        $barcodeRule = Rule::unique('products', 'barcode')->ignore($product->id)->whereNull('deleted_at');

        $validator = Validator::make($payload, [
            'name' => 'sometimes|required|string|min:2|max:255',
            'sku' => ['nullable', 'string', 'max:50', $skuRule],
            'barcode' => ['nullable', 'string', 'max:100', $barcodeRule],
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'operational_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'ingredients' => 'nullable|array',
            'ingredients.*.name' => 'required_with:ingredients|string|max:255',
            'ingredients.*.amount' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.unit' => 'required_with:ingredients|string|max:50',
            'ingredients.*.buy_price' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.buy_amount' => 'nullable|numeric|min:0',
            'ingredients.*.buy_unit' => 'nullable|string|max:50',
            'mode_alokasi_ops' => 'nullable|in:manual,rincian',
            'target_penjualan_bulanan' => 'nullable|integer|min:1',
            'selected_tier' => 'nullable|string',
            'kenaikan_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi perubahan menu gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $payload, $product) {
                $dataToUpdate = [];

                if (isset($payload['name'])) $dataToUpdate['name'] = trim($payload['name']);
                if (isset($payload['category_id'])) $dataToUpdate['category_id'] = (int) $payload['category_id'];
                if (isset($payload['sku'])) $dataToUpdate['sku'] = trim($payload['sku']);
                if (array_key_exists('barcode', $payload)) {
                    $dataToUpdate['barcode'] = !empty(trim((string) $payload['barcode'])) ? trim($payload['barcode']) : null;
                }
                if (array_key_exists('description', $payload)) $dataToUpdate['description'] = $payload['description'];
                if (isset($payload['price'])) $dataToUpdate['price'] = (float) $payload['price'];
                if (isset($payload['is_active'])) $dataToUpdate['is_active'] = (bool) $payload['is_active'];

                // Upload image baru jika ada
                if ($request->hasFile('image')) {
                    if ($product->image && Storage::disk('public')->exists($product->image)) {
                        Storage::disk('public')->delete($product->image);
                    }
                    $dataToUpdate['image'] = $request->file('image')->store('products', 'public');
                }

                // Handle Update Resep Bahan Baku & HPP
                if (array_key_exists('ingredients', $payload)) {
                    $ingredients = $payload['ingredients'] ?? [];
                    $modeAlokasi = $payload['mode_alokasi_ops'] ?? ($product->ai_pricing_data['mode_alokasi_ops'] ?? 'manual');
                    $operationalCost = isset($payload['operational_cost'])
                        ? (float) $payload['operational_cost']
                        : (float) $product->operational_cost;
                    $targetUnits = (int) ($payload['target_penjualan_bulanan'] ?? ($product->ai_pricing_data['target_penjualan_bulanan'] ?? 3000));
                    $kenaikan = (float) ($payload['kenaikan_persen'] ?? ($product->ai_pricing_data['kenaikan_persen'] ?? 0));
                    $sellingPrice = isset($payload['price']) ? (float) $payload['price'] : (float) $product->price;

                    if (!empty($ingredients)) {
                        $calculation = $this->hppService->calculate($ingredients, [
                            'mode_alokasi_ops' => $modeAlokasi,
                            'operational_cost' => $operationalCost,
                            'target_penjualan_bulanan' => $targetUnits,
                            'kenaikan_persen' => $kenaikan,
                            'selling_price' => $sellingPrice,
                        ]);

                        $dataToUpdate['harga_beli'] = (float) ($calculation['summary']['effective_hpp'] ?? 0);
                        $dataToUpdate['operational_cost'] = (float) ($calculation['summary']['operational_cost_per_unit'] ?? $operationalCost);

                        // Hapus bahan baku lama dan ganti dengan yang baru
                        ProductIngredient::where('product_id', $product->id)->delete();

                        foreach ($calculation['ingredients'] as $ing) {
                            ProductIngredient::create([
                                'product_id' => $product->id,
                                'name' => $ing['name'],
                                'amount' => (float) $ing['amount'],
                                'unit' => $ing['unit'],
                                'buy_price' => (float) $ing['buy_price'],
                                'buy_amount' => (float) $ing['buy_amount'],
                                'buy_unit' => $ing['buy_unit'],
                                'subtotal' => (float) $ing['subtotal'],
                            ]);
                        }
                    } else {
                        // Jika sengaja dikosongkan
                        ProductIngredient::where('product_id', $product->id)->delete();
                        if (isset($payload['harga_beli'])) {
                            $dataToUpdate['harga_beli'] = (float) $payload['harga_beli'];
                        }
                    }

                    // Metadata update
                    $meta = is_array($product->ai_pricing_data) ? $product->ai_pricing_data : [];
                    $meta['mode_alokasi_ops'] = $modeAlokasi;
                    $meta['manual_alokasi_nominal'] = ($modeAlokasi === 'manual') ? $operationalCost : 0;
                    $meta['target_penjualan_bulanan'] = $targetUnits;
                    $meta['kenaikan_persen'] = $kenaikan;
                    if (isset($payload['selected_tier'])) {
                        $meta['selected_tier'] = $payload['selected_tier'];
                    }
                    $dataToUpdate['ai_pricing_data'] = $meta;
                } elseif (isset($payload['harga_beli'])) {
                    $dataToUpdate['harga_beli'] = (float) $payload['harga_beli'];
                }

                $product->update($dataToUpdate);
                $product->load(['category:id,name', 'ingredients']);

                return response()->json([
                    'success' => true,
                    'message' => "Menu '{$product->name}' berhasil diperbarui.",
                    'data' => $this->transformProduct($product, true),
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('[API Product Update Error] ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 5. Hapus Produk (Soft Delete).
     * DELETE /api/admin/products/{id}
     */
        /**
     * 6. Pulihkan Produk dari Arsip (Restore Soft Delete).
     * POST /api/admin/products/{id}/restore
     */
    public function restore($id): JsonResponse
    {
        $product = Product::onlyTrashed()->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan di arsip atau belum dihapus.',
            ], 404);
        }

        $product->restore();

        return response()->json([
            'success' => true,
            'message' => "Menu '{$product->name}' berhasil dipulihkan dari arsip.",
            'data' => $this->transformProduct($product),
        ]);
    }
    public function destroy($id): JsonResponse
    {
        $product = Product::withTrashed()->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $productName = $product->name;
        if ($product->trashed()) {
            // Hapus bahan baku terkait dan hapus permanen
            $product->ingredients()->delete();
            $product->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Menu '{$productName}' berhasil dihapus permanen dari arsip.",
            ]);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => "Menu '{$productName}' berhasil dipindahkan ke arsip.",
        ]);
    }

    /**
     * Helper: Pre-process input dari form-data mobile (antisipasi JSON string & format uang).
     */
    protected function preprocessRequestPayload(Request $request): array
    {
        $payload = $request->all();

        // Antisipasi jika ingredients dikirim sebagai JSON string (standar Flutter/React Native multipart)
        if (isset($payload['ingredients']) && is_string($payload['ingredients'])) {
            $decoded = json_decode($payload['ingredients'], true);
            if (is_array($decoded)) {
                $payload['ingredients'] = $decoded;
            }
        }

        // Sanitasi harga jika dikirim dengan string titik / Rp
        foreach (['price', 'harga_beli', 'operational_cost', 'kenaikan_persen'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key])) {
                $cleaned = preg_replace('/[^\d.]/', '', $payload[$key]);
                $payload[$key] = $cleaned !== '' ? (float) $cleaned : null;
            }
        }

        return $payload;
    }

    /**
     * Helper: Generate SKU Unik Berdasarkan Kategori Menu.
     */
    protected function generateUniqueSku(int $categoryId, string $productName): string
    {
        $prefix = 'PRD';
        $category = Category::find($categoryId);

        if ($category) {
            $catName = strtolower($category->name);
            if (str_contains($catName, 'coffee') && !str_contains($catName, 'non-coffee')) {
                $prefix = 'COF';
            } elseif (str_contains($catName, 'non-coffee') || str_contains($catName, 'tea')) {
                $prefix = 'NCF';
            } elseif (str_contains($catName, 'pastry') || str_contains($catName, 'bakery') || str_contains($catName, 'cake')) {
                $prefix = 'PST';
            } elseif (str_contains($catName, 'food') || str_contains($catName, 'makanan')) {
                $prefix = 'FOD';
            } elseif (str_contains($catName, 'snack') || str_contains($catName, 'cemilan')) {
                $prefix = 'SNK';
            } else {
                $clean = preg_replace('/[^A-Za-z0-9]/', '', $category->name);
                $prefix = strtoupper(substr($clean, 0, 3));
                if (strlen($prefix) < 3) $prefix = 'CFE';
            }
        }

        // Loop untuk memastikan SKU benar-benar unik
        do {
            $randomNum = rand(1000, 9999);
            $sku = "{$prefix}-{$randomNum}";
        } while (Product::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Helper: Format Output Produk yang Bersih & Konsisten untuk Mobile.
     */
    protected function transformProduct(Product $product, bool $includeDetails = false): array
    {
        $imageUrl = null;
        if ($product->image) {
            $imageUrl = Str::startsWith($product->image, 'http')
                ? $product->image
                : asset('storage/' . $product->image);
        }

        $price = (float) $product->price;
        $hpp = (float) $product->harga_beli;
        $profit = max(0, $price - $hpp);
        $marginPercent = $price > 0 ? round(($profit / $price) * 100, 1) : 0.0;
        $foodCostPercent = $price > 0 ? round(($hpp / $price) * 100, 1) : 0.0;

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name ?? 'Uncategorized',
            'description' => $product->description,
            'price' => $price,
            'harga_beli' => $hpp,
            'operational_cost' => (float) $product->operational_cost,
            'profit' => $profit,
            'margin_percent' => $marginPercent,
            'food_cost_percent' => $foodCostPercent,
            'is_healthy_margin' => $marginPercent >= 45.0,
            'is_active' => (bool) $product->is_active,
            'is_archived' => !is_null($product->deleted_at),
            'deleted_at' => $product->deleted_at?->toIso8601String(),
            'image' => $product->image,
            'image_url' => $imageUrl,
            'ingredients_count' => $product->relationLoaded('ingredients') ? $product->ingredients->count() : 0,
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];

        if ($includeDetails) {
            $data['pricing_metadata'] = $product->ai_pricing_data;
            $data['ingredients'] = $product->ingredients->map(function ($ing) {
                return [
                    'id' => $ing->id,
                    'name' => $ing->name,
                    'amount' => (float) $ing->amount,
                    'unit' => $ing->unit,
                    'buy_price' => (float) $ing->buy_price,
                    'buy_amount' => (float) $ing->buy_amount,
                    'buy_unit' => $ing->buy_unit,
                    'subtotal' => (float) $ing->subtotal,
                ];
            });
        }

        return $data;
    }
}
