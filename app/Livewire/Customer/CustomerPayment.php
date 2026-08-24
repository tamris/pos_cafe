<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Setting;
use App\Services\MidtransService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.customer')]
#[Title('Pembayaran Midtrans & QRIS - Self Order')]
class CustomerPayment extends Component
{
    public $token;
    public $transaction;
    public $snapToken = null;
    public $snapError = null;
    public $isSimulating = false;
    public $isCheckingStatus = false;

    public function mount($token)
    {
        $this->token = $token;
        $this->transaction = Transaction::with(['details.product'])
            ->where('order_token', $token)
            ->firstOrFail();

        // If already paid, go straight to order status
        if ($this->transaction->payment_status === 'paid') {
            return redirect()->route('customer.status', ['token' => $this->token]);
        }

        $this->initSnap();
    }

    public function initSnap()
    {
        try {
            $midtransService = app(MidtransService::class);
            $this->snapToken = $midtransService->getSnapToken($this->transaction);

            if (!$this->snapToken) {
                $this->snapError = 'Tidak dapat memuat sesi pembayaran online Midtrans. Silakan coba lagi atau gunakan simulasi.';
            }
        } catch (\Throwable $e) {
            $this->snapError = 'Koneksi Payment Gateway: ' . $e->getMessage();
        }
    }

    /**
     * Cek status pembayaran langsung ke server Midtrans (Fallback / Instant Sync)
     */
    public function checkPaymentStatus()
    {
        $this->isCheckingStatus = true;

        $midtransService = app(MidtransService::class);
        $status = $midtransService->checkTransactionStatus($this->transaction->invoice_number);

        // Reload data transaksi
        $this->transaction = Transaction::with(['details.product'])
            ->where('order_token', $this->token)
            ->firstOrFail();

        $this->isCheckingStatus = false;

        if ($this->transaction->payment_status === 'paid') {
            return redirect()->route('customer.status', ['token' => $this->token]);
        }

        $transactionStatus = $status['transaction_status'] ?? 'pending';
        if ($transactionStatus === 'pending') {
            $this->dispatch('alert', type: 'info', message: 'Pembayaran belum terdeteksi. Silakan selesaikan pembayaran pada popup Midtrans.');
        } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
            $this->dispatch('alert', type: 'error', message: 'Status pembayaran: ' . strtoupper($transactionStatus) . '. Silakan buat pesanan baru.');
        } else {
            $this->dispatch('alert', type: 'warning', message: 'Status pembayaran belum berubah (Status: ' . ($transactionStatus ?: 'Belum dibayar') . ').');
        }
    }

    public function simulatePaymentSuccess()
    {
        $this->isSimulating = true;

        $transaction = Transaction::where('order_token', $this->token)->firstOrFail();
        
        $transaction->update([
            'payment_status' => 'paid',
            'paid' => (float) $transaction->total,
            'change' => 0,
            'status' => 'processing',
        ]);

        return redirect()->route('customer.status', ['token' => $this->token]);
    }

    public function cancelOrder()
    {
        $transaction = Transaction::where('order_token', $this->token)->firstOrFail();
        
        // Only allow cancel if still unpaid
        if ($transaction->payment_status === 'unpaid') {
            $transaction->update([
                'status' => 'cancelled',
                'cancelled_reason' => 'Dibatalkan oleh pelanggan sebelum pembayaran.',
                'cancelled_at' => now(),
            ]);

            $this->dispatch('remove-token-localstorage', token: $this->token);
        }

        return redirect()->route('customer.order');
    }

    public function render()
    {
        $setting = Setting::first();
        return view('livewire.customer.customer-payment', [
            'setting' => $setting,
            'clientKey' => config('midtrans.client_key'),
            'isProduction' => config('midtrans.is_production', false),
        ]);
    }
}
