<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\ProductIngredient;
use App\Services\HppCalculationService;
use App\Models\StockMutation;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IngredientApiController extends Controller
{
    /**
     * Display a listing of ingredients with summary.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $lifecycle = $request->input('lifecycle', null);

        // Lifecycle query support: 'active', 'inactive', 'archived', 'all'
        if ($lifecycle === 'archived' || $status === 'archived') {
            $query = Ingredient::onlyTrashed();
        } elseif ($lifecycle === 'all_with_archived' || $status === 'all_with_archived') {
            $query = Ingredient::withTrashed();
        } else {
            $query = Ingredient::query();
        }

        if ($lifecycle === 'active' || $status === 'active') {
            $query->where('is_active', true);
        } elseif ($lifecycle === 'inactive' || $status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
                if (\Illuminate\Support\Facades\Schema::hasColumn('ingredients', 'sku')) {
                    $q->orWhere('sku', 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Stock status filter: 'empty', 'warning', 'safe'
        if ($request->filled('stock_status') || in_array($status, ['empty', 'warning', 'safe', 'out_of_stock', 'low_stock'])) {
            $stockFilter = $request->input('stock_status', $status);
            if ($stockFilter === 'empty' || $stockFilter === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($stockFilter === 'warning' || $stockFilter === 'low_stock') {
                $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
            } elseif ($stockFilter === 'safe') {
                $query->whereColumn('stock', '>', 'min_stock');
            }
        }

        $query->with([
            'productIngredients' => function ($q) {
                $q->select('id', 'product_id', 'ingredient_id', 'amount', 'unit', 'subtotal');
            },
            'productIngredients.product' => function ($q) {
                $q->select('id', 'name', 'price', 'category_id')->with('category:id,name');
            }
        ])->withCount('productIngredients as products_count');

        // Sorting support: name, stock_asc, stock_desc, value_desc
        $sort = $request->input('sort', 'name');
        if ($sort === 'stock_asc') {
            $query->orderBy('stock', 'asc');
        } elseif ($sort === 'stock_desc') {
            $query->orderBy('stock', 'desc');
        } elseif ($sort === 'value_desc') {
            $query->orderByRaw('(stock * cost_per_unit) desc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->input('per_page');

        $formatCollection = function ($collection) {
            return $collection->map(function ($ing) {
                $usedIn = $ing->productIngredients->map(function ($pi) {
                    return [
                        'product_id' => $pi->product_id,
                        'product_name' => $pi->product?->name ?? 'Menu',
                        'category_name' => $pi->product?->category?->name ?? 'Umum',
                        'amount' => (float) $pi->amount,
                        'unit' => $pi->unit,
                        'subtotal' => (float) $pi->subtotal,
                    ];
                })->values();

                $data = $ing->toArray();
                $data['is_archived'] = !is_null($ing->deleted_at);
                $data['deleted_at'] = $ing->deleted_at ? $ing->deleted_at->toIso8601String() : null;
                $data['products_count'] = (int) ($ing->products_count ?? $usedIn->count());
                $data['used_in_products'] = $usedIn;
                return $data;
            });
        };

        if ($perPage) {
            $paginated = $query->paginate($perPage);
            $formatted = $formatCollection($paginated->getCollection());
            $paginated->setCollection($formatted);
            $ingredients = $paginated;
        } else {
            $ingredients = $formatCollection($query->get());
        }

        // Calculate Aggregate Summary directly in database across all matching records (supports pagination)
        $summaryQuery = (clone $query)->toBase();
        $summaryQuery->columns = null;
        $summaryQuery->orders = null;

        $summaryStats = $summaryQuery->selectRaw("
            COUNT(*) as total_ingredients,
            COUNT(CASE WHEN stock > 0 AND stock <= min_stock THEN 1 END) as low_stock_count,
            COUNT(CASE WHEN stock <= 0 THEN 1 END) as out_of_stock_count,
            COUNT(CASE WHEN stock < 0 THEN 1 END) as debt_count,
            COUNT(CASE WHEN stock > min_stock THEN 1 END) as safe_stock_count,
            COALESCE(SUM(CASE WHEN stock > 0 THEN stock * cost_per_unit ELSE 0 END), 0) as total_inventory_value
        ")->first();

        $totalIngredients = (int) ($summaryStats->total_ingredients ?? 0);
        $lowStockCount = (int) ($summaryStats->low_stock_count ?? 0);
        $outOfStockCount = (int) ($summaryStats->out_of_stock_count ?? 0);
        $debtCount = (int) ($summaryStats->debt_count ?? 0);
        $safeStockCount = (int) ($summaryStats->safe_stock_count ?? 0);
        $totalInventoryValue = (float) ($summaryStats->total_inventory_value ?? 0);

        return response()->json([
            'success' => true,
            'message' => 'Data bahan baku berhasil dimuat.',
            'data' => $ingredients,
            'summary' => [
                'total_ingredients' => $totalIngredients,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'debt_count' => $debtCount,
                'safe_stock_count' => $safeStockCount,
                'total_inventory_value' => $totalInventoryValue,
            ]
        ]);
    }

    /**
     * Store a newly created ingredient.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'stock' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|numeric|min:0',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'buy_price' => 'nullable|numeric|min:0',
            'buy_amount' => 'nullable|numeric|min:0',
            'buy_unit' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();

            // Otomatis hitung cost_per_unit jika tidak dikirim tapi buy_price & buy_amount ada
            if (empty($data['cost_per_unit']) || (float) $data['cost_per_unit'] <= 0) {
                if (!empty($data['buy_price']) && !empty($data['buy_amount']) && (float) $data['buy_amount'] > 0) {
                    $hppService = app(HppCalculationService::class);
                    $data['cost_per_unit'] = $hppService->calculateIngredientSubtotal(
                        1.0,
                        $data['unit'] ?? 'unit',
                        (float) $data['buy_price'],
                        (float) $data['buy_amount'],
                        (string) ($data['buy_unit'] ?? 'unit')
                    );
                }
            }

            $ingredient = Ingredient::create($data);

            if ($ingredient->stock > 0) {
                StockMutation::create([
                    'ingredient_id' => $ingredient->id,
                    'type' => 'in_purchase',
                    'amount' => $ingredient->stock,
                    'stock_before' => 0,
                    'stock_after' => $ingredient->stock,
                    'cost' => $ingredient->stock * $ingredient->cost_per_unit,
                    'notes' => 'Stok awal bahan baku',
                    'user_id' => $request->user()?->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bahan baku berhasil ditambahkan.',
                'data' => $ingredient
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan bahan baku: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified ingredient.
     */
    public function show($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ingredient
        ]);
    }

    /**
     * Update the specified ingredient.
     */
    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'unit' => 'sometimes|required|string|max:50',
            'min_stock' => 'nullable|numeric|min:0',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'buy_price' => 'nullable|numeric|min:0',
            'buy_amount' => 'nullable|numeric|min:0',
            'buy_unit' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Note: Direct stock update is not allowed here. Stock must be updated via restock/opname.
        $data = $request->except(['stock']);

        // Otomatis hitung cost_per_unit jika kosong / 0 tapi buy_price & buy_amount ada
        if (empty($data['cost_per_unit']) || (float) $data['cost_per_unit'] <= 0) {
            $buyPrice = (float) ($data['buy_price'] ?? $ingredient->buy_price);
            $buyAmount = (float) ($data['buy_amount'] ?? $ingredient->buy_amount);
            $buyUnit = (string) ($data['buy_unit'] ?? $ingredient->buy_unit ?? 'unit');
            $unit = (string) ($data['unit'] ?? $ingredient->unit ?? 'unit');

            if ($buyPrice > 0 && $buyAmount > 0) {
                $hppService = app(HppCalculationService::class);
                $data['cost_per_unit'] = $hppService->calculateIngredientSubtotal(
                    1.0,
                    $unit,
                    $buyPrice,
                    $buyAmount,
                    $buyUnit
                );
            }
        }
        
        $ingredient->update($data);

        // SYNC REAL-TIME: Sinkronkan data & harga beli ke semua resep menu yang terhubung
        try {
            $linkedRecipes = ProductIngredient::where('ingredient_id', $ingredient->id)->get();
            if ($linkedRecipes->isNotEmpty()) {
                $hppService = app(HppCalculationService::class);
                $affectedProductIds = [];
                foreach ($linkedRecipes as $recipe) {
                    $recipe->name = $ingredient->name;
                    $recipe->buy_price = (float) $ingredient->buy_price;
                    $recipe->buy_amount = (float) ($ingredient->buy_amount > 0 ? $ingredient->buy_amount : 1.0);
                    $recipe->buy_unit = $ingredient->buy_unit;
                    $recipe->subtotal = $hppService->calculateIngredientSubtotal(
                        (float) $recipe->amount,
                        (string) $recipe->unit,
                        (float) $ingredient->buy_price,
                        (float) ($ingredient->buy_amount > 0 ? $ingredient->buy_amount : 1.0),
                        (string) $ingredient->buy_unit
                    );
                    $recipe->save();
                    $affectedProductIds[] = $recipe->product_id;
                }

                // Recalculate HPP untuk semua produk menu yang terdampak perubahan harga bahan
                foreach (array_unique($affectedProductIds) as $pId) {
                    $product = \App\Models\Product::find($pId);
                    if ($product) {
                        $totalHpp = \App\Models\ProductIngredient::where('product_id', $product->id)->sum('subtotal');
                        $product->harga_beli = $totalHpp;
                        $product->save();
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to sync recipe ingredients for ingredient {$ingredient->id}: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Bahan baku berhasil diperbarui.',
            'data' => $ingredient
        ]);
    }

    /**
     * Remove the specified ingredient.
     */
    public function destroy($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ], 404);
        }

        // Automatic Unlink: Detach all recipe links and recalculate product HPP
        $productIds = \App\Models\ProductIngredient::where('ingredient_id', $ingredient->id)
            ->pluck('product_id')
            ->unique();

        if ($productIds->isNotEmpty()) {
            \App\Models\ProductIngredient::where('ingredient_id', $ingredient->id)->delete();
            foreach ($productIds as $pId) {
                $product = \App\Models\Product::find($pId);
                if ($product) {
                    $totalHpp = \App\Models\ProductIngredient::where('product_id', $product->id)->sum('subtotal');
                    $product->harga_beli = $totalHpp;
                    $product->save();
                }
            }
        }

        // Soft delete (Arsip)
        $ingredient->delete();

        return response()->json([
            'success' => true,
            'message' => "Bahan baku '{$ingredient->name}' berhasil diarsipkan dan kaitan resep telah diputuskan.",
            'data' => [
                'detached_recipes_count' => $productIds->count(),
            ]
        ]);
    }

    /**
     * Toggle active/nonactive status for an ingredient (keeps recipes intact).
     */
    public function toggleActive($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ], 404);
        }

        $ingredient->is_active = !$ingredient->is_active;
        $ingredient->save();

        $statusText = $ingredient->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success' => true,
            'message' => "Bahan baku '{$ingredient->name}' berhasil {$statusText}.",
            'data' => $ingredient
        ]);
    }

    /**
     * Restore an archived ingredient.
     */
    public function restore($id)
    {
        $ingredient = Ingredient::onlyTrashed()->find($id);

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku terarsip tidak ditemukan.'
            ], 404);
        }

        $ingredient->restore();

        return response()->json([
            'success' => true,
            'message' => "Bahan baku '{$ingredient->name}' berhasil dipulihkan dari arsip.",
            'data' => $ingredient
        ]);
    }

    /**
     * Permanently delete an ingredient from the system.
     */
    public function forceDelete($id)
    {
        $ingredient = Ingredient::withTrashed()->find($id);

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ], 404);
        }

        // Clean up recipe links if any remain
        \App\Models\ProductIngredient::where('ingredient_id', $ingredient->id)->delete();

        $name = $ingredient->name;
        $ingredient->forceDelete();

        return response()->json([
            'success' => true,
            'message' => "Bahan baku '{$name}' berhasil dihapus secara permanen."
        ]);
    }

    /**
     * Restock ingredient.
     */
    public function restock(Request $request, $id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'total_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'record_to_expense' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $amount = (float) $request->amount;
            $stockBefore = (float) $ingredient->stock;
            $stockAfter = $stockBefore + $amount;

            $ingredient->stock = $stockAfter;
            $ingredient->save();

            StockMutation::create([
                'ingredient_id' => $ingredient->id,
                'type' => 'in_purchase',
                'amount' => $amount,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'cost' => $request->total_cost,
                'notes' => $request->notes ?? "Restock {$ingredient->name}",
                'user_id' => $request->user()?->id,
            ]);

            if ($request->input('record_to_expense', false)) {
                CashMovement::create([
                    'type' => 'out',
                    'source' => 'drawer', // Assuming default from drawer, could be made dynamic
                    'category_name' => 'Bahan Baku',
                    'amount' => $request->total_cost,
                    'notes' => "Restock {$ingredient->name}",
                    'user_id' => $request->user()?->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stok bahan baku berhasil ditambahkan.',
                'data' => $ingredient
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah stok: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stock Opname (Physical count).
     */
    public function opname(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.actual_stock' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $ingredient = Ingredient::find($item['ingredient_id']);
                if (!$ingredient) continue;

                $actualStock = (float) $item['actual_stock'];
                $currentStock = (float) $ingredient->stock;
                $diff = $actualStock - $currentStock;

                if ($diff != 0) {
                    $ingredient->stock = $actualStock;
                    $ingredient->save();

                    $rawReason = $item['reason'] ?? 'adjustment';
                    if ($diff > 0) {
                        $type = 'opname_adjustment';
                        $reasonText = 'Koreksi Fisik Lebih';
                    } else {
                        if ($rawReason === 'waste') {
                            $type = 'waste';
                            $reasonText = 'Bahan Rusak / Basi';
                        } elseif ($rawReason === 'expired') {
                            $type = 'expired';
                            $reasonText = 'Kadaluarsa';
                        } elseif ($rawReason === 'missing') {
                            $type = 'missing';
                            $reasonText = 'Selisih Hitung / Hilang';
                        } else {
                            $type = 'opname_adjustment';
                            $reasonText = 'Koreksi Stok Rutin (Susut)';
                        }
                    }

                    $customNote = !empty($request->notes) ? trim($request->notes) : '';
                    $mutationNote = $customNote !== '' ? "$customNote ($reasonText)" : "Opname: $reasonText";

                    StockMutation::create([
                        'ingredient_id' => $ingredient->id,
                        'type' => $type,
                        'amount' => $diff,
                        'stock_before' => $currentStock,
                        'stock_after' => $actualStock,
                        'cost' => abs($diff) * (float) $ingredient->cost_per_unit,
                        'notes' => $mutationNote,
                        'user_id' => $request->user()?->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock opname berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan stock opname: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve stock mutations.
     */
    public function mutations(Request $request)
    {
        $query = StockMutation::with(['ingredient:id,name,unit', 'user:id,name']);

        if ($request->filled('ingredient_id')) {
            $query->where('ingredient_id', $request->ingredient_id);
        }

        if ($request->filled('type')) {
            $type = strtolower($request->type);
            if ($type === 'restock' || $type === 'in_purchase') {
                $query->whereIn('type', ['restock', 'in_purchase']);
            } elseif ($type === 'pos' || $type === 'sale' || $type === 'out_sale') {
                $query->whereIn('type', ['out_sale', 'pos_usage', 'sale', 'usage']);
            } elseif ($type === 'opname' || $type === 'opname_adjustment') {
                $query->whereIn('type', ['opname', 'opname_adjustment']);
            } elseif ($type === 'waste' || $type === 'damaged') {
                $query->whereIn('type', ['waste', 'damaged', 'expired']);
            } else {
                $query->where('type', $request->type);
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Hitung total ringkasan riil (Masuk, Keluar, Netto) dari seluruh baris yang difilter
        $summary = (clone $query)->selectRaw("
            COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as total_in,
            COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) as total_out,
            COUNT(*) as total_count
        ")->first();

        $perPage = $request->input('per_page', 20);
        $mutations = $query->latest()->paginate($perPage);

        $totalIn = (float) ($summary->total_in ?? 0);
        $totalOut = (float) ($summary->total_out ?? 0);

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat mutasi stok berhasil dimuat.',
            'data' => $mutations,
            'summary' => [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'net_change' => $totalIn - $totalOut,
                'total_count' => (int) ($summary->total_count ?? $mutations->total()),
            ]
        ]);
    }

    /**
     * Attach this ingredient to a product recipe.
     */
    public function attachToProduct(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|numeric|min:0.001',
            'unit' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $ingredient = Ingredient::findOrFail($id);
        $product = \App\Models\Product::findOrFail($request->product_id);
        $hppService = app(\App\Services\HppCalculationService::class);

        $amount = (float) $request->amount;
        $unit = $request->unit ?? $ingredient->unit;
        $buyPrice = (float) $ingredient->buy_price;
        $buyAmount = (float) $ingredient->buy_amount;
        $buyUnit = $ingredient->buy_unit;

        $subtotal = $hppService->calculateIngredientSubtotal($amount, $unit, $buyPrice, $buyAmount, $buyUnit);

        // Check if already in recipe
        $recipe = \App\Models\ProductIngredient::where('product_id', $product->id)
            ->where(function ($q) use ($ingredient) {
                $q->where('ingredient_id', $ingredient->id)
                  ->orWhere('name', $ingredient->name);
            })
            ->first();

        if ($recipe) {
            $recipe->ingredient_id = $ingredient->id;
            $recipe->amount = $amount;
            $recipe->unit = $unit;
            $recipe->buy_price = $buyPrice;
            $recipe->buy_amount = $buyAmount;
            $recipe->buy_unit = $buyUnit;
            $recipe->subtotal = $subtotal;
            $recipe->save();
        } else {
            $recipe = \App\Models\ProductIngredient::create([
                'product_id' => $product->id,
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'amount' => $amount,
                'unit' => $unit,
                'buy_price' => $buyPrice,
                'buy_amount' => $buyAmount,
                'buy_unit' => $buyUnit,
                'subtotal' => $subtotal,
            ]);
        }

        // Recalculate Product HPP
        $totalHpp = \App\Models\ProductIngredient::where('product_id', $product->id)->sum('subtotal');
        $product->harga_beli = $totalHpp;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => "Bahan baku '{$ingredient->name}' berhasil ditautkan ke menu '{$product->name}'.",
            'data' => [
                'recipe' => $recipe,
                'product_hpp' => (float) $totalHpp,
            ]
        ]);
    }

    /**
     * Detach this ingredient from a product recipe.
     */
    public function detachFromProduct($id, $productId)
    {
        $ingredient = Ingredient::findOrFail($id);
        $product = \App\Models\Product::findOrFail($productId);

        \App\Models\ProductIngredient::where('product_id', $product->id)
            ->where('ingredient_id', $ingredient->id)
            ->delete();

        // Recalculate Product HPP
        $totalHpp = \App\Models\ProductIngredient::where('product_id', $product->id)->sum('subtotal');
        $product->harga_beli = $totalHpp;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => "Bahan baku '{$ingredient->name}' berhasil dihapus dari resep '{$product->name}'.",
            'data' => [
                'product_hpp' => (float) $totalHpp,
            ]
        ]);
    }
    /**
     * Detach this ingredient from ALL product recipes.
     */
    public function detachFromAllProducts($id)
    {
        $ingredient = Ingredient::findOrFail($id);

        $productIds = \App\Models\ProductIngredient::where('ingredient_id', $ingredient->id)
            ->pluck('product_id')
            ->unique();

        $count = $productIds->count();

        if ($count === 0) {
            return response()->json([
                'success' => true,
                'message' => "Bahan baku '{$ingredient->name}' tidak terikat pada menu apa pun.",
                'data' => [
                    'detached_count' => 0,
                ]
            ]);
        }

        // Delete all recipe items linking to this ingredient
        \App\Models\ProductIngredient::where('ingredient_id', $ingredient->id)->delete();

        // Recalculate HPP for all affected products
        foreach ($productIds as $pId) {
            $product = \App\Models\Product::find($pId);
            if ($product) {
                $totalHpp = \App\Models\ProductIngredient::where('product_id', $product->id)->sum('subtotal');
                $product->harga_beli = $totalHpp;
                $product->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil memutuskan kaitan {$count} menu dari bahan baku '{$ingredient->name}'.",
            'data' => [
                'detached_count' => $count,
            ]
        ]);
    }
}
