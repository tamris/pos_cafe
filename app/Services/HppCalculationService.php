<?php

namespace App\Services;

class HppCalculationService
{
    /**
     * Normalisasi nama satuan agar standar.
     */
    public function normalizeUnit(?string $unit): string
    {
        if (empty($unit)) {
            return 'pcs';
        }

        $u = strtolower(trim($unit));

        // Gram variants
        if (in_array($u, ['g', 'gr', 'gram', 'grams'])) {
            return 'gram';
        }

        // Kilogram variants
        if (in_array($u, ['kg', 'kilo', 'kilogram', 'kilograms'])) {
            return 'kg';
        }

        // Milliliter variants
        if (in_array($u, ['ml', 'mili', 'mililiter', 'milliliter', 'cc'])) {
            return 'ml';
        }

        // Liter variants
        if (in_array($u, ['l', 'lt', 'ltr', 'liter', 'liters'])) {
            return 'liter';
        }

        // Pcs / Unit variants
        if (in_array($u, ['pc', 'pcs', 'buah', 'biji', 'lembar', 'butir', 'cup', 'sheet', 'kantong', 'pack', 'unit'])) {
            return 'pcs';
        }

        // Sachet
        if (in_array($u, ['sachet', 'saset', 'bungkus'])) {
            return 'sachet';
        }

        return $u;
    }

    /**
     * Hitung subtotal harga bahan baku per porsi berdasarkan takaran & harga beli.
     */
    public function calculateIngredientSubtotal(
        float $amount,
        string $unit,
        float $buyPrice,
        float $buyAmount,
        string $buyUnit
    ): float {
        $amount = max(0, $amount);
        $buyPrice = max(0, $buyPrice);
        $buyAmount = $buyAmount > 0 ? $buyAmount : 1.0;

        $normUnit = $this->normalizeUnit($unit);
        $normBuyUnit = $this->normalizeUnit($buyUnit);

        $pricePerBuyUnit = $buyPrice / $buyAmount;
        $multiplier = 1.0;

        // Aturan konversi satuan F&B / Cafe
        if ($normUnit === 'gram' && $normBuyUnit === 'kg') {
            $multiplier = 0.001; // 1 gram = 0.001 kg
        } elseif ($normUnit === 'ml' && $normBuyUnit === 'liter') {
            $multiplier = 0.001; // 1 ml = 0.001 liter
        } elseif ($normUnit === 'kg' && $normBuyUnit === 'gram') {
            $multiplier = 1000.0; // 1 kg = 1000 gram
        } elseif ($normUnit === 'liter' && $normBuyUnit === 'ml') {
            $multiplier = 1000.0; // 1 liter = 1000 ml
        }

        $subtotal = $amount * $multiplier * $pricePerBuyUnit;

        return round($subtotal, 2);
    }

    /**
     * Proses kalkulasi menyeluruh bahan baku, biaya operasional, tier harga, dan margin.
     *
     * @param array $ingredients List bahan baku
     * @param array $options Opsi tambahan: mode_alokasi_ops, operational_cost, biaya_tetap_items,
     *                       target_penjualan_bulanan, kenaikan_persen, selling_price, target_laba_bulanan, hari_operasional_sebulan
     */
    public function calculate(array $ingredients = [], array $options = []): array
    {
        $processedIngredients = [];
        $totalVariableCost = 0.0;

        foreach ($ingredients as $index => $item) {
            $name = trim((string) ($item['name'] ?? $item['nama'] ?? 'Bahan ' . ($index + 1)));
            $amount = (float) ($item['amount'] ?? $item['takaran'] ?? 0);
            $unit = (string) ($item['unit'] ?? $item['satuan_takaran'] ?? 'gram');
            $buyPrice = (float) ($item['buy_price'] ?? $item['harga_beli'] ?? 0);
            $buyAmount = (float) ($item['buy_amount'] ?? $item['jumlah_beli'] ?? 1);
            if ($buyAmount <= 0) {
                $buyAmount = 1.0;
            }
            $buyUnit = (string) ($item['buy_unit'] ?? $item['satuan_beli'] ?? 'kg');

            $subtotal = $this->calculateIngredientSubtotal($amount, $unit, $buyPrice, $buyAmount, $buyUnit);
            $totalVariableCost += $subtotal;

            $processedIngredients[] = [
                'name' => $name,
                'amount' => $amount,
                'unit' => $this->normalizeUnit($unit),
                'buy_price' => $buyPrice,
                'buy_amount' => $buyAmount,
                'buy_unit' => $this->normalizeUnit($buyUnit),
                'subtotal' => $subtotal,
            ];
        }

        // Alokasi Biaya Tetap / Operasional
        $modeAlokasi = $options['mode_alokasi_ops'] ?? 'manual';
        $targetPenjualanBulanan = max(1, (int) ($options['target_penjualan_bulanan'] ?? 3000));
        $biayaTetapItems = $options['biaya_tetap_items'] ?? [];
        $operationalCostPerUnit = 0.0;
        $totalBiayaTetapBulanan = 0.0;

        if ($modeAlokasi === 'rincian' && is_array($biayaTetapItems) && !empty($biayaTetapItems)) {
            foreach ($biayaTetapItems as $biaya) {
                $totalBiayaTetapBulanan += (float) ($biaya['nominal'] ?? 0);
            }
            $operationalCostPerUnit = round($totalBiayaTetapBulanan / $targetPenjualanBulanan, 2);
        } else {
            $operationalCostPerUnit = (float) ($options['operational_cost'] ?? $options['alokasi_biaya_tetap'] ?? 0);
        }

        // Simulasi Kenaikan Harga Bahan (Inflasi / Fluktuasi)
        $kenaikanPersen = (float) ($options['kenaikan_persen'] ?? 0);
        $simulatedVariableCost = round($totalVariableCost * (1 + ($kenaikanPersen / 100)), 2);

        // Total HPP Murni & HPP Tersimulasi
        $baseHpp = round($totalVariableCost + $operationalCostPerUnit, 2);
        $simulatedHpp = round($simulatedVariableCost + $operationalCostPerUnit, 2);

        // Rekomendasi 3 Tier Harga Jual (dibulatkan ke kelipatan Rp 500 ke atas)
        $pricingTiers = $this->buildPricingTiers($simulatedHpp > 0 ? $simulatedHpp : $baseHpp);

        // Analisis Harga Jual Aktual jika diberikan
        $sellingPrice = (float) ($options['selling_price'] ?? $options['price'] ?? 0);
        $customAnalysis = null;

        if ($sellingPrice > 0) {
            $effectiveHpp = $simulatedHpp > 0 ? $simulatedHpp : $baseHpp;
            $profit = round($sellingPrice - $effectiveHpp, 2);
            $marginPercent = round(($profit / $sellingPrice) * 100, 1);
            $foodCostPercent = round(($effectiveHpp / $sellingPrice) * 100, 1);

            $customAnalysis = [
                'selling_price' => $sellingPrice,
                'profit' => $profit,
                'margin_percent' => $marginPercent,
                'food_cost_percent' => $foodCostPercent,
                'is_healthy_margin' => $marginPercent >= 45.0,
            ];
        }

        // Proyeksi Penjualan & BEP
        $targetLabaBulanan = (float) ($options['target_laba_bulanan'] ?? 5000000);
        $hariOperasional = max(1, (int) ($options['hari_operasional_sebulan'] ?? 30));
        $effectiveSellingPrice = $sellingPrice > 0 ? $sellingPrice : ($pricingTiers['standar']['harga'] ?? 0);
        $effectiveUnitCost = $simulatedHpp > 0 ? $simulatedHpp : $baseHpp;

        $netMarginPerUnit = max(0, $effectiveSellingPrice - $effectiveUnitCost);
        $totalUnitsMonth = ($netMarginPerUnit > 0 && $targetLabaBulanan > 0)
            ? (int) ceil($targetLabaBulanan / $netMarginPerUnit)
            : 0;
        $targetUnitsDay = (int) ceil($totalUnitsMonth / $hariOperasional);
        $potensiOmzet = $totalUnitsMonth * $effectiveSellingPrice;
        $totalBiayaProduksi = $totalUnitsMonth * $simulatedVariableCost;
        $totalBiayaTetap = $totalUnitsMonth * $operationalCostPerUnit;
        $proyeksiLabaBersih = $potensiOmzet - $totalBiayaProduksi - $totalBiayaTetap;

        return [
            'ingredients' => $processedIngredients,
            'summary' => [
                'total_variable_cost' => round($totalVariableCost, 2),
                'simulated_variable_cost' => round($simulatedVariableCost, 2),
                'kenaikan_persen' => $kenaikanPersen,
                'mode_alokasi_ops' => $modeAlokasi,
                'operational_cost_per_unit' => round($operationalCostPerUnit, 2),
                'total_biaya_tetap_bulanan' => round($totalBiayaTetapBulanan, 2),
                'target_penjualan_bulanan' => $targetPenjualanBulanan,
                'base_hpp' => round($baseHpp, 2),
                'simulated_hpp' => round($simulatedHpp, 2),
                'effective_hpp' => round($simulatedHpp > 0 ? $simulatedHpp : $baseHpp, 2),
            ],
            'pricing_tiers' => $pricingTiers,
            'custom_analysis' => $customAnalysis,
            'sales_projection' => [
                'selling_price' => $effectiveSellingPrice,
                'unit_cost' => $effectiveUnitCost,
                'net_margin_per_unit' => round($netMarginPerUnit, 2),
                'target_laba_bulanan' => $targetLabaBulanan,
                'hari_operasional_sebulan' => $hariOperasional,
                'target_units_per_day' => $targetUnitsDay,
                'target_units_per_month' => $totalUnitsMonth,
                'potensi_omzet' => round($potensiOmzet, 2),
                'total_biaya_produksi' => round($totalBiayaProduksi, 2),
                'total_biaya_tetap' => round($totalBiayaTetap, 2),
                'proyeksi_laba_bersih' => round($proyeksiLabaBersih, 2),
            ],
        ];
    }

    /**
     * Membentuk 3 Tier Rekomendasi Harga Jual (Kompetitif, Standar, Premium)
     * dibulatkan ke kelipatan Rp 500 terdekat.
     */
    public function buildPricingTiers(float $baseHpp): array
    {
        if ($baseHpp <= 0) {
            return [
                'kompetitif' => ['tier' => 'kompetitif', 'label' => 'Kompetitif (Promo / Volume)', 'harga' => 0, 'margin' => 0, 'profit' => 0],
                'standar' => ['tier' => 'standar', 'label' => 'Standar Cafe (Rekomendasi)', 'harga' => 0, 'margin' => 0, 'profit' => 0],
                'premium' => ['tier' => 'premium', 'label' => 'Premium / Specialty', 'harga' => 0, 'margin' => 0, 'profit' => 0],
            ];
        }

        // Tier Kompetitif: target food cost 50% (margin ~50%)
        // Tier Standar: target food cost 42% (margin ~58%)
        // Tier Premium: target food cost 33% (margin ~67%)
        $kompetitif = ceil(($baseHpp / 0.5) / 500) * 500;
        $standar = ceil(($baseHpp / 0.42) / 500) * 500;
        $premium = ceil(($baseHpp / 0.33) / 500) * 500;

        return [
            'kompetitif' => [
                'tier' => 'kompetitif',
                'label' => 'Kompetitif (Promo / Volume)',
                'harga' => (float) $kompetitif,
                'margin' => round((($kompetitif - $baseHpp) / $kompetitif) * 100, 1),
                'profit' => round($kompetitif - $baseHpp, 2),
            ],
            'standar' => [
                'tier' => 'standar',
                'label' => 'Standar Cafe (Rekomendasi)',
                'harga' => (float) $standar,
                'margin' => round((($standar - $baseHpp) / $standar) * 100, 1),
                'profit' => round($standar - $baseHpp, 2),
            ],
            'premium' => [
                'tier' => 'premium',
                'label' => 'Premium / Specialty',
                'harga' => (float) $premium,
                'margin' => round((($premium - $baseHpp) / $premium) * 100, 1),
                'profit' => round($premium - $baseHpp, 2),
            ],
        ];
    }
}
