<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function printStruk($invoice)
    {
        // 1. Ambil Data Transaksi
        $transaction = Transaction::with('details.product')
            ->where('invoice_number', $invoice)
            ->firstOrFail();

        // 2. AMBIL DATA SETTING DARI DATABASE (Ini yang kurang)
        $setting = \App\Models\Setting::first();

        // 3. Kirim $setting ke View pakai compact
        return view('pos.print-struk', compact('transaction', 'setting'));
    }
}
