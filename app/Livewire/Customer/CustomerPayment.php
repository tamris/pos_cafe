<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Setting;
use App\Services\MidtransService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.customer')]
#[Title('Pembayaran QRIS - Self Order')]
class CustomerPayment extends Component
{
    public $token;
    public $transaction;
    public $qrisUrl = null;
    public $qrString = null;
    public $isSimulating = false;

    public function mount($token)
    {
        $this->token = $token;
        $this->transaction = Transaction::with(['details.product'])
            ->where('order_token', $token)
            ->firstOrFail();

        // If already paid or already cancelled, go straight to order status
        if ($this->transaction->payment_status === 'paid' || $this->transaction->status === 'cancelled') {
            return redirect()->route('customer.status', ['token' => $this->token]);
        }

        // Check if 15 minutes have already passed since creation
        if ($this->transaction->created_at && $this->transaction->created_at->addMinutes(15)->isPast()) {
            return $this->expireOrder();
        }

        $this->loadQris();
    }

    public function loadQris()
    {
        try {
            $midtransService = app(MidtransService::class);
            $qrisData = $midtransService->generateQris($this->transaction);

            $this->qrisUrl = $qrisData['qris_url'] ?? null;
            $this->qrString = $qrisData['qr_string'] ?? null;
        } catch (\Throwable $e) {
            $this->qrisUrl = 'https://api.sandbox.midtrans.com/v2/qris/' . $this->transaction->invoice_number . '/qr-code';
        }
    }

    /**
     * Silent Background Polling: mengecek status transaksi di latar belakang tanpa efek loading di tombol
     */
    public function checkPaymentStatus()
    {
        try {
            $midtransService = app(MidtransService::class);
            $midtransService->checkTransactionStatus($this->transaction->invoice_number);

            $this->transaction = Transaction::with(['details.product'])
                ->where('order_token', $this->token)
                ->firstOrFail();

            if ($this->transaction->payment_status === 'paid') {
                return redirect()->route('customer.status', ['token' => $this->token]);
            }

            // Jika status dari Midtrans sudah expire / cancel
            if ($this->transaction->payment_status === 'failed' || ($this->transaction->created_at && $this->transaction->created_at->addMinutes(15)->isPast())) {
                return $this->expireOrder();
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Menandai pesanan kadaluarsa otomatis saat timer 15 menit habis
     */
    public function expireOrder()
    {
        $transaction = Transaction::where('order_token', $this->token)->first();
        if ($transaction && $transaction->payment_status === 'unpaid') {
            $transaction->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
                'cancelled_reason' => 'Batas waktu pembayaran QRIS telah kadaluarsa.',
                'cancelled_at' => now(),
            ]);

            $this->dispatch('remove-token-localstorage', token: $this->token);
        }

        return redirect()->route('customer.status', ['token' => $this->token]);
    }

    /**
     * Manual Trigger saat pelanggan klik tombol "Saya Sudah Membayar"
     * [DEV MODE SIMULASI]: Mensimulasikan pembayaran QRIS berhasil secara langsung untuk kebutuhan testing.
     * [CATATAN]: Saat Go-Live Production, ubah kembali method ini untuk memanggil $this->checkPaymentStatus()!
     */
    public function manualCheckPayment()
    {
        // Dev Simulation Mode: Langsung ubah status transaksi menjadi PAID (Lunas) & masuk antrean dapur
        return $this->simulatePaymentSuccess();
    }

    public function simulatePaymentSuccess()
    {
        $this->isSimulating = true;

        $transaction = Transaction::where('order_token', $this->token)->firstOrFail();
        
        $transaction->update([
            'payment_status' => 'paid',
            'payment_method' => 'qris',
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
        $expiresAtTimestamp = $this->transaction->created_at ? $this->transaction->created_at->addMinutes(15)->timestamp : now()->addMinutes(15)->timestamp;
        
        return view('livewire.customer.customer-payment', [
            'setting' => $setting,
            'expiresAtTimestamp' => $expiresAtTimestamp,
        ]);
    }
}
