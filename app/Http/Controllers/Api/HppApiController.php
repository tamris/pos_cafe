<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\GeminiAIService;
use App\Services\HppCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HppApiController extends Controller
{
    protected HppCalculationService $hppService;

    public function __construct(HppCalculationService $hppService)
    {
        $this->hppService = $hppService;
    }

    /**
     * 1. Simulasi Hitung HPP & Margin on-the-fly (Tanpa simpan ke DB).
     * POST /api/admin/hpp/calculate
     */
    public function calculate(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Antisipasi jika ingredients dikirim dalam format JSON string (misal dari multipart/form-data mobile)
        if (isset($payload['ingredients']) && is_string($payload['ingredients'])) {
            $decoded = json_decode($payload['ingredients'], true);
            if (is_array($decoded)) {
                $payload['ingredients'] = $decoded;
            }
        }

        if (isset($payload['biaya_tetap_items']) && is_string($payload['biaya_tetap_items'])) {
            $decoded = json_decode($payload['biaya_tetap_items'], true);
            if (is_array($decoded)) {
                $payload['biaya_tetap_items'] = $decoded;
            }
        }

        $validator = Validator::make($payload, [
            'ingredients' => 'nullable|array',
            'ingredients.*.name' => 'nullable|string',
            'ingredients.*.amount' => 'nullable|numeric|min:0',
            'ingredients.*.unit' => 'nullable|string',
            'ingredients.*.buy_price' => 'nullable|numeric|min:0',
            'ingredients.*.buy_amount' => 'nullable|numeric|min:0',
            'ingredients.*.buy_unit' => 'nullable|string',
            'mode_alokasi_ops' => 'nullable|in:manual,rincian',
            'operational_cost' => 'nullable|numeric|min:0',
            'biaya_tetap_items' => 'nullable|array',
            'biaya_tetap_items.*.nama' => 'nullable|string',
            'biaya_tetap_items.*.nominal' => 'nullable|numeric|min:0',
            'target_penjualan_bulanan' => 'nullable|integer|min:1',
            'kenaikan_persen' => 'nullable|numeric|min:0|max:100',
            'selling_price' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'target_laba_bulanan' => 'nullable|numeric|min:0',
            'hari_operasional_sebulan' => 'nullable|integer|min:1|max:31',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ingredients = $payload['ingredients'] ?? [];
        $options = [
            'mode_alokasi_ops' => $payload['mode_alokasi_ops'] ?? 'manual',
            'operational_cost' => (float) ($payload['operational_cost'] ?? 0),
            'biaya_tetap_items' => $payload['biaya_tetap_items'] ?? [],
            'target_penjualan_bulanan' => (int) ($payload['target_penjualan_bulanan'] ?? 3000),
            'kenaikan_persen' => (float) ($payload['kenaikan_persen'] ?? 0),
            'selling_price' => (float) ($payload['selling_price'] ?? $payload['price'] ?? 0),
            'target_laba_bulanan' => (float) ($payload['target_laba_bulanan'] ?? 5000000),
            'hari_operasional_sebulan' => (int) ($payload['hari_operasional_sebulan'] ?? 30),
        ];

        $result = $this->hppService->calculate($ingredients, $options);

        return response()->json([
            'success' => true,
            'message' => 'Simulasi kalkulasi HPP dan rekomendasi harga berhasil.',
            'data' => $result,
        ]);
    }

    /**
     * 2. Estimasi Resep Bahan Baku via Gemini AI.
     * POST /api/admin/hpp/ai-recipe
     */
    public function aiRecipe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required_without:nama_produk|string|min:3',
            'nama_produk' => 'required_without:product_name|string|min:3',
        ], [
            'product_name.required_without' => 'Nama menu wajib diisi minimal 3 karakter.',
            'nama_produk.required_without' => 'Nama menu wajib diisi minimal 3 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama produk wajib diisi sebelum analisis AI.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $productName = trim((string) ($request->input('product_name') ?: $request->input('nama_produk')));

        try {
            /** @var GeminiAIService $aiService */
            $aiService = app(GeminiAIService::class);

            $prompt = "Buatkan estimasi takaran bahan standar cafe untuk menu: {$productName}.
ATURAN SATUAN & JUMLAH BELI:
1. Bahan per butir/lembar/pcs (Paper Filter, Cup, Tutup, Sedotan, Drip Bag, dll):
   - Wajib gunakan 'satuan_takaran': 'pcs'
   - Wajib gunakan 'satuan_beli': 'pcs'
   - Pada 'jumlah_beli': Wajib isi dengan TOTAL ISI PCS dalam 1 kemasan beli (Contoh: Paper Filter 1 pack isi 100 lembar harga 45000 -> harga_beli: 45000, jumlah_beli: 100, satuan_beli: 'pcs'. Cup 1 pack isi 50 pcs harga 30000 -> harga_beli: 30000, jumlah_beli: 50, satuan_beli: 'pcs').
2. Bahan bubuk/kopi/gula/es:
   - 'satuan_takaran': 'gram'
   - 'satuan_beli': 'kg' (atau 'gram'), 'jumlah_beli': 1 (jika kg) atau 1000 (jika gram)
3. Bahan cair/susu/sirup/air:
   - 'satuan_takaran': 'ml'
   - 'satuan_beli': 'liter' (atau 'ml'), 'jumlah_beli': 1 (jika liter) atau 1000 (jika ml). Jika air galon 19L harga 20000 -> harga_beli: 20000, jumlah_beli: 19, satuan_beli: 'liter'.

Gunakan format JSON array dengan keys:
- nama: nama bahan baku (string)
- takaran: angka takaran per porsi (number)
- satuan_takaran: salah satu dari 'gram', 'ml', 'pcs', 'sachet'
- harga_beli: estimasi harga beli pasaran dalam Rupiah (number)
- jumlah_beli: total isi kemasan yang dibeli sesuai satuan_beli (number)
- satuan_beli: salah satu dari 'kg', 'liter', 'gram', 'ml', 'pcs', 'sachet'";

            $rawIngredients = $aiService->generateJson($prompt);

            if (!is_array($rawIngredients) || empty($rawIngredients)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI tidak menghasilkan resep untuk menu ini. Coba buat nama menu lebih spesifik (misal: "Caramel Macchiato").',
                ], 422);
            }

            // Normalisasi dan hitung estimasi awal HPP
            $calculation = $this->hppService->calculate($rawIngredients, [
                'mode_alokasi_ops' => 'manual',
                'operational_cost' => 1000, // default alokasi ops standar cafe
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resep AI untuk ' . $productName . ' berhasil di-generate.',
                'data' => [
                    'product_name' => $productName,
                    'ingredients' => $calculation['ingredients'],
                    'summary' => $calculation['summary'],
                    'pricing_tiers' => $calculation['pricing_tiers'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[API AI Recipe Error] ' . $e->getMessage(), [
                'product_name' => $productName,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghasilkan resep AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3. Ringkasan / Health Check HPP & Margin Menu Cafe.
     * GET /api/admin/hpp/summary
     */
    public function summary(): JsonResponse
    {
        $products = Product::where('is_active', true)->with('category:id,name')->get();

        $totalCount = $products->count();
        $totalMarginPercent = 0.0;
        $totalFoodCostPercent = 0.0;
        $lowMarginProducts = [];

        foreach ($products as $p) {
            $hpp = (float) $p->harga_beli;
            $price = (float) $p->price;
            $profit = max(0, $price - $hpp);
            $margin = $price > 0 ? round(($profit / $price) * 100, 1) : 0.0;
            $foodCost = $price > 0 ? round(($hpp / $price) * 100, 1) : 0.0;

            $totalMarginPercent += $margin;
            $totalFoodCostPercent += $foodCost;

            if ($margin < 35.0 && $price > 0) {
                $lowMarginProducts[] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category?->name ?? 'Uncategorized',
                    'price' => $price,
                    'harga_beli' => $hpp,
                    'margin_percent' => $margin,
                    'food_cost_percent' => $foodCost,
                ];
            }
        }

        $avgMargin = $totalCount > 0 ? round($totalMarginPercent / $totalCount, 1) : 0.0;
        $avgFoodCost = $totalCount > 0 ? round($totalFoodCostPercent / $totalCount, 1) : 0.0;

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan performa HPP menu berhasil dimuat.',
            'data' => [
                'total_active_products' => $totalCount,
                'average_margin_percent' => $avgMargin,
                'average_food_cost_percent' => $avgFoodCost,
                'is_margin_healthy' => $avgMargin >= 50.0,
                'low_margin_count' => count($lowMarginProducts),
                'low_margin_alert_threshold_percent' => 35.0,
                'low_margin_products' => $lowMarginProducts,
            ],
        ]);
    }

    /**
     * 4. List Kategori Menu untuk Dropdown di Apps.
     * GET /api/admin/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->whereNull('deleted_at');
            }])
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'description' => $cat->description,
                    'products_count' => (int) $cat->products_count,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil dimuat.',
            'data' => $categories,
        ]);
    }
}
