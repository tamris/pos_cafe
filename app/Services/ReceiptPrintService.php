<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\CashierShift;

class ReceiptPrintService
{
    public static function line32(string $left, string $right, int $width = 32): string
    {
        $leftLen = strlen($left);
        $rightLen = strlen($right);
        if ($leftLen + $rightLen >= $width) {
            return $left . " " . $right;
        }
        $spaces = $width - $leftLen - $rightLen;
        return $left . str_repeat(' ', $spaces) . $right;
    }

    public static function buildTransactionEscPos(Transaction $transaction, ?Setting $setting = null): string
    {
        if (!$setting) {
            $setting = Setting::first();
        }

        $esc = "\x1b";
        $gs = "\x1d";

        $INIT = $esc . "@";
        $ALIGN_CENTER = $esc . "a\x01";
        $ALIGN_LEFT = $esc . "a\x00";
        $BOLD_ON = $esc . "E\x01";
        $BOLD_OFF = $esc . "E\x00";
        $DOUBLE_SIZE = $esc . "!\x30";
        $DOUBLE_HEIGHT = $esc . "!\x10";
        $NORMAL_SIZE = $esc . "!\x00";

        $raw = $INIT;

        // 1. Header (Center)
        $raw .= $ALIGN_CENTER;
        $raw .= $DOUBLE_SIZE . $BOLD_ON . ($setting->shop_name ?? 'POS CAFE & ROASTERY') . "\n" . $NORMAL_SIZE . $BOLD_OFF;
        if (!empty($setting->address)) {
            $raw .= $setting->address . "\n";
        }
        if (!empty($setting->phone)) {
            $raw .= "Telp: " . $setting->phone . "\n";
        }
        $raw .= "--------------------------------\n";

        // 2. Metadata (Left)
        $raw .= $ALIGN_LEFT;
        $raw .= self::line32("No. Inv", $transaction->invoice_number) . "\n";
        $raw .= self::line32("Waktu", date('d/m/Y H:i', strtotime($transaction->created_at))) . "\n";
        $raw .= self::line32("Kasir", $transaction->user->name ?? 'Staff') . "\n";

        if (!empty($transaction->table_number) || !empty($transaction->customer_name)) {
            $orderTypeStr = (($transaction->order_type ?? 'dine_in') === 'dine_in')
                ? 'DINE IN' . ($transaction->table_number ? ' (MEJA ' . $transaction->table_number . ')' : '')
                : ((($transaction->order_type ?? '') === 'take_away') ? 'TAKE AWAY' : 'DELIVERY');
            $raw .= self::line32("Pesanan", $orderTypeStr) . "\n";

            if (!empty($transaction->customer_name)) {
                $raw .= self::line32("Pelanggan", $transaction->customer_name) . "\n";
            }
        }
        $raw .= "--------------------------------\n";

        // 3. Items
        foreach ($transaction->details as $detail) {
            $raw .= $BOLD_ON . $detail->product->name . "\n" . $BOLD_OFF;
            $qtyPrice = $detail->quantity . " x " . number_format($detail->price, 0, ',', '.');
            $sub = number_format($detail->subtotal, 0, ',', '.');
            $raw .= self::line32($qtyPrice, $sub) . "\n";
            if (!empty($detail->notes)) {
                $raw .= "  * " . $detail->notes . "\n";
            }
        }
        $raw .= "--------------------------------\n";

        // 4. Calculations
        $raw .= self::line32("Subtotal", number_format($transaction->subtotal, 0, ',', '.')) . "\n";

        if ($transaction->discount > 0) {
            $discNominal = ($transaction->discount <= 100)
                ? ($transaction->subtotal * $transaction->discount / 100)
                : $transaction->discount;
            $discLabel = "Diskon" . ($transaction->discount <= 100 ? ' (' . $transaction->discount . '%)' : '');
            $raw .= self::line32($discLabel, "-" . number_format($discNominal, 0, ',', '.')) . "\n";
        }

        if ($transaction->tax > 0) {
            $taxNominal = ($transaction->tax <= 100)
                ? ($transaction->subtotal * $transaction->tax / 100)
                : $transaction->tax;
            $taxLabel = "Pajak" . ($transaction->tax <= 100 ? ' (' . $transaction->tax . '%)' : '');
            $raw .= self::line32($taxLabel, "+" . number_format($taxNominal, 0, ',', '.')) . "\n";
        }
        $raw .= "--------------------------------\n";

        // Grand Total (Big & Bold)
        $raw .= $DOUBLE_HEIGHT . $BOLD_ON;
        $raw .= self::line32("TOTAL", "Rp " . number_format($transaction->total, 0, ',', '.')) . "\n";
        $raw .= $NORMAL_SIZE . $BOLD_OFF;
        $raw .= "--------------------------------\n";

        // Payment
        $payMethod = strtoupper($transaction->payment_method === 'cash' ? 'TUNAI' : ($transaction->payment_method === 'transfer' ? 'TRANSFER' : 'QRIS'));
        $raw .= self::line32("Bayar (" . $payMethod . ")", number_format($transaction->paid, 0, ',', '.')) . "\n";
        $raw .= $BOLD_ON . self::line32("Kembali", number_format($transaction->change, 0, ',', '.')) . "\n" . $BOLD_OFF;

        // Wifi
        if (!empty($setting->wifi_name) || !empty($setting->wifi_password)) {
            $raw .= "--------------------------------\n";
            $raw .= $ALIGN_CENTER;
            $wifiStr = "WiFi: " . ($setting->wifi_name ?? '-');
            if (!empty($setting->wifi_password)) {
                $wifiStr .= " | Pass: " . $setting->wifi_password;
            }
            $raw .= $wifiStr . "\n";
        }

        $raw .= "--------------------------------\n";

        // Footer
        $raw .= $ALIGN_CENTER;
        $raw .= ($setting->receipt_footer ?? 'Terima kasih atas kunjungannya!') . "\n";
        $raw .= "-- Have a Good Coffee Day --\n";

        // Feed 3 baris agar pas di sobek manual
        $raw .= "\n\n\n";

        return $raw;
    }

    public static function buildShiftEscPos(CashierShift $shift, ?Setting $setting = null): string
    {
        if (!$setting) {
            $setting = Setting::first();
        }

        $esc = "\x1b";

        $raw = $esc . "@";
        $raw .= $esc . "a\x01"; // Center
        $raw .= $esc . "!\x30" . ($setting->shop_name ?? 'POS CAFE') . "\n" . $esc . "!\x00";
        if (!empty($setting->address)) {
            $raw .= $setting->address . "\n";
        }
        if (!empty($setting->phone)) {
            $raw .= "Telp: " . $setting->phone . "\n";
        }
        $raw .= "--------------------------------\n";
        $raw .= $esc . "E\x01" . "*** REKAP SHIFT KASIR ***\n" . $esc . "E\x00";
        $raw .= ($shift->status === 'closed' ? 'STATUS: DITUTUP (FINAL)' : 'STATUS: SHIFT AKTIF') . "\n";
        $raw .= "--------------------------------\n";

        // Meta
        $raw .= $esc . "a\x00"; // Left
        $raw .= self::line32("Shift ID", "#SFT-" . str_pad($shift->id, 5, '0', STR_PAD_LEFT)) . "\n";
        $raw .= self::line32("Kasir", $shift->user->name ?? '-') . "\n";
        $raw .= self::line32("Buka Shift", $shift->start_time ? $shift->start_time->format('d/m/y H:i') : '-') . "\n";
        $raw .= self::line32("Tutup Shift", $shift->end_time ? $shift->end_time->format('d/m/y H:i') : '(Belum Ditutup)') . "\n";
        if ($shift->end_time) {
            $durStr = $shift->start_time->diffInHours($shift->end_time) . " Jam " . ($shift->start_time->diffInMinutes($shift->end_time) % 60) . " Mnt";
            $raw .= self::line32("Durasi", $durStr) . "\n";
        }
        $raw .= "--------------------------------\n";

        // Penjualan
        $raw .= $esc . "E\x01" . "RINCIAN PENJUALAN\n" . $esc . "E\x00";
        $raw .= self::line32("Total Struk", number_format($shift->total_transactions, 0, ',', '.') . " Trx") . "\n";
        $raw .= self::line32("Penjualan Tunai", "Rp " . number_format($shift->cash_sales, 0, ',', '.')) . "\n";
        $raw .= self::line32("Penjualan QRIS", "Rp " . number_format($shift->qris_sales, 0, ',', '.')) . "\n";
        $raw .= self::line32("Penjualan Transfer", "Rp " . number_format($shift->transfer_sales, 0, ',', '.')) . "\n";
        $raw .= "--------------------------------\n";

        // Total Omset
        $raw .= $esc . "!\x10" . $esc . "E\x01";
        $raw .= self::line32("TOTAL OMSET", "Rp " . number_format($shift->total_sales, 0, ',', '.')) . "\n";
        $raw .= $esc . "!\x00" . $esc . "E\x00";
        $raw .= "--------------------------------\n";

        // Kas Laci
        $raw .= $esc . "E\x01" . "REKONSILIASI KAS LACI\n" . $esc . "E\x00";
        $raw .= self::line32("Modal Kas Awal", "Rp " . number_format($shift->starting_cash, 0, ',', '.')) . "\n";
        $raw .= self::line32("(+) Total Tunai", "Rp " . number_format($shift->cash_sales, 0, ',', '.')) . "\n";
        $raw .= $esc . "E\x01" . self::line32("(=) Kas Harapan", "Rp " . number_format($shift->expected_cash, 0, ',', '.')) . "\n" . $esc . "E\x00";
        if ($shift->status === 'closed') {
            $raw .= $esc . "E\x01" . self::line32("Uang Fisik Kas", "Rp " . number_format($shift->actual_cash ?? 0, 0, ',', '.')) . "\n" . $esc . "E\x00";
            $diff = (float) ($shift->difference ?? 0);
            $diffStr = $diff == 0 ? "Rp 0 (PAS)" : ($diff > 0 ? "+Rp " . number_format($diff, 0, ',', '.') . " (LEBIH)" : "-Rp " . number_format(abs($diff), 0, ',', '.') . " (KURANG)");
            $raw .= "--------------------------------\n";
            $raw .= $esc . "E\x01" . self::line32("SELISIH KAS", $diffStr) . "\n" . $esc . "E\x00";
        }

        if (!empty($shift->notes)) {
            $raw .= "Catatan: " . $shift->notes . "\n";
        }

        $raw .= "--------------------------------\n";
        $raw .= $esc . "a\x01"; // Center
        $raw .= "Dicetak pada " . now()->format('d/m/Y H:i:s') . "\n";
        $raw .= "\n\n\n";

        return $raw;
    }
}
