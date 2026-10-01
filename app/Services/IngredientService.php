<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\ProductIngredient;
use App\Models\StockMutation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IngredientService
{
    protected ?HppCalculationService $hppService = null;

    public function __construct(?HppCalculationService $hppService = null)
    {
        $this->hppService = $hppService ?? app(HppCalculationService::class);
    }

    /**
     * Konversi takaran dari satuan resep ke satuan master stok bahan baku.
     */
    public function convertAmountToStockUnit(float $amount, ?string $fromUnit, ?string $toUnit): float
    {
        $amount = max(0, $amount);
        $normFrom = $this->hppService ? $this->hppService->normalizeUnit($fromUnit) : strtolower(trim((string) $fromUnit));
        $normTo = $this->hppService ? $this->hppService->normalizeUnit($toUnit) : strtolower(trim((string) $toUnit));

        if ($normFrom === $normTo || empty($normFrom) || empty($normTo)) {
            return $amount;
        }

        // Gram to Kg: 1 gram = 0.001 kg
        if ($normFrom === 'gram' && $normTo === 'kg') {
            return $amount / 1000.0;
        }
        // Kg to Gram: 1 kg = 1000 gram
        if ($normFrom === 'kg' && $normTo === 'gram') {
            return $amount * 1000.0;
        }
        // Ml to Liter: 1 ml = 0.001 liter
        if ($normFrom === 'ml' && $normTo === 'liter') {
            return $amount / 1000.0;
        }
        // Liter to Ml: 1 liter = 1000 ml
        if ($normFrom === 'liter' && $normTo === 'ml') {
            return $amount * 1000.0;
        }

        return $amount;
    }

    /**
     * Deduct stock based on a completed transaction's details.
     */
    public function deductStockForTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return;
        }

        // Idempotency: cegah double potong stok jika method terpanggil ulang
        $alreadyDeducted = StockMutation::where('reference', $transaction->invoice_number)
            ->where('type', 'out_sale')
            ->exists();
        if ($alreadyDeducted) {
            return;
        }

        try {
            DB::transaction(function () use ($transaction) {
                $details = $transaction->details()->with('product')->get();

                foreach ($details as $detail) {
                    if (!$detail->product_id) continue;

                    $productIngredients = ProductIngredient::where('product_id', $detail->product_id)
                        ->whereNotNull('ingredient_id')
                        ->get();

                    foreach ($productIngredients as $recipe) {
                        $ingredient = Ingredient::lockForUpdate()->find($recipe->ingredient_id);
                        if (!$ingredient) continue;

                        $rawRecipeAmount = (float) $recipe->amount * (float) $detail->quantity;
                        $totalUsedInStockUnit = $this->convertAmountToStockUnit(
                            $rawRecipeAmount,
                            $recipe->unit,
                            $ingredient->unit
                        );

                        if ($totalUsedInStockUnit <= 0) continue;

                        $stockBefore = (float) $ingredient->stock;
                        $stockAfter = $stockBefore - $totalUsedInStockUnit;

                        $ingredient->stock = $stockAfter;
                        $ingredient->save();

                        $productName = $detail->product?->name ?? 'Menu';

                        StockMutation::create([
                            'ingredient_id' => $ingredient->id,
                            'type' => 'out_sale',
                            'amount' => -$totalUsedInStockUnit,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'cost' => $totalUsedInStockUnit * (float) $ingredient->cost_per_unit,
                            'reference' => $transaction->invoice_number,
                            'notes' => "Penjualan menu {$productName} (x{$detail->quantity})",
                            'user_id' => $transaction->user_id,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('[IngredientService] Failed deducting stock for transaction ' . $transaction->invoice_number . ': ' . $e->getMessage());
        }
    }

    /**
     * Return stock for a voided/cancelled transaction.
     */
    public function returnStockForVoidTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'cancelled') {
            return;
        }

        // Cek apakah transaksi ini pernah dipotong stoknya
        $existingMutations = StockMutation::where('reference', $transaction->invoice_number)
            ->where('type', 'out_sale')
            ->get();

        if ($existingMutations->isEmpty()) {
            return;
        }

        // Idempotency: cegah double return jika void dipanggil ulang
        $alreadyReturned = StockMutation::where('reference', $transaction->invoice_number)
            ->where('type', 'void_return')
            ->exists();

        if ($alreadyReturned) {
            return;
        }

        try {
            DB::transaction(function () use ($transaction) {
                $details = $transaction->details()->with('product')->get();

                foreach ($details as $detail) {
                    if (!$detail->product_id) continue;

                    $productIngredients = ProductIngredient::where('product_id', $detail->product_id)
                        ->whereNotNull('ingredient_id')
                        ->get();

                    foreach ($productIngredients as $recipe) {
                        $ingredient = Ingredient::lockForUpdate()->find($recipe->ingredient_id);
                        if (!$ingredient) continue;

                        $rawRecipeAmount = (float) $recipe->amount * (float) $detail->quantity;
                        $totalReturnedInStockUnit = $this->convertAmountToStockUnit(
                            $rawRecipeAmount,
                            $recipe->unit,
                            $ingredient->unit
                        );

                        if ($totalReturnedInStockUnit <= 0) continue;

                        $stockBefore = (float) $ingredient->stock;
                        $stockAfter = $stockBefore + $totalReturnedInStockUnit;

                        $ingredient->stock = $stockAfter;
                        $ingredient->save();

                        $productName = $detail->product?->name ?? 'Menu';

                        StockMutation::create([
                            'ingredient_id' => $ingredient->id,
                            'type' => 'void_return',
                            'amount' => $totalReturnedInStockUnit,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'cost' => $totalReturnedInStockUnit * (float) $ingredient->cost_per_unit,
                            'reference' => $transaction->invoice_number,
                            'notes' => "Pembatalan penjualan menu {$productName} (x{$detail->quantity})",
                            'user_id' => $transaction->cancelled_by ?? $transaction->user_id,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('[IngredientService] Failed returning stock for void transaction ' . $transaction->invoice_number . ': ' . $e->getMessage());
        }
    }
}
