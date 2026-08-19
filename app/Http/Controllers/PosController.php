<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\ReceiptPrintService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function printStruk($invoice)
    {
        $transaction = Transaction::with(['details.product', 'user'])
            ->where('invoice_number', $invoice)
            ->firstOrFail();

        $setting = Setting::first();
        $rawbtBase64 = base64_encode(ReceiptPrintService::buildTransactionEscPos($transaction, $setting));

        return view('pos.print-struk', compact('transaction', 'setting', 'rawbtBase64'));
    }

    public function printShift($id)
    {
        $shift = CashierShift::with(['user', 'transactions.details.product'])->findOrFail($id);
        $setting = Setting::first();
        $rawbtShiftBase64 = base64_encode(ReceiptPrintService::buildShiftEscPos($shift, $setting));

        return view('pos.print-shift', compact('shift', 'setting', 'rawbtShiftBase64'));
    }

    public function rawbtStruk($invoice)
    {
        $transaction = Transaction::with(['details.product', 'user'])
            ->where('invoice_number', $invoice)
            ->firstOrFail();

        $setting = Setting::first();
        $rawbt = base64_encode(ReceiptPrintService::buildTransactionEscPos($transaction, $setting));

        return response()->json([
            'status' => 'success',
            'invoice' => $invoice,
            'rawbt' => $rawbt,
        ]);
    }

    public function rawbtShift($id)
    {
        $shift = CashierShift::with(['user', 'transactions.details.product'])->findOrFail($id);
        $setting = Setting::first();
        $rawbt = base64_encode(ReceiptPrintService::buildShiftEscPos($shift, $setting));

        return response()->json([
            'status' => 'success',
            'shift_id' => $id,
            'rawbt' => $rawbt,
        ]);
    }
}
