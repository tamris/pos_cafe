<?php

namespace App\Livewire\Customer;

use App\Models\Setting;
use App\Models\Transaction;
use App\Services\LoyaltyService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.customer')]
#[Title('Klaim Poin Belanja - POS Cafe')]
class ClaimPoints extends Component
{
    public $token = '';
    public $inputToken = '';
    public $phone = '';
    public $name = '';
    public $transaction = null;
    public $pointsToClaim = 0;
    public $isClaimed = false;
    public $errorMessage = '';
    public $successData = null;
    public $setting = null;

    public function mount($token = null)
    {
        $this->setting = Setting::first();
        $this->token = trim($token ?? request()->query('token', ''));

        if (!empty($this->token)) {
            $this->loadTransaction();
        }
    }

    public function searchToken()
    {
        $val = trim($this->inputToken);
        if (empty($val)) {
            $this->errorMessage = 'Silakan masukkan kode klaim atau nomor struk.';
            return;
        }

        $this->errorMessage = '';
        // Look up by claim token or invoice number
        $tx = Transaction::where('claim_token', $val)
            ->orWhere('invoice_number', $val)
            ->orWhere('invoice_number', 'INV-' . $val)
            ->orWhere('invoice_number', '#' . $val)
            ->first();

        if ($tx && !empty($tx->claim_token)) {
            $this->token = $tx->claim_token;
            $this->loadTransaction();
        } else {
            $this->token = $val;
            $this->loadTransaction();
        }
    }

    public function loadTransaction()
    {
        $this->errorMessage = '';
        $this->transaction = Transaction::where('claim_token', $this->token)->first();

        if (!$this->transaction) {
            $this->errorMessage = 'Struk atau kode klaim tidak ditemukan. Pastikan QR Code di struk ter-scan dengan benar.';
            return;
        }

        if ($this->transaction->points_claimed_at !== null || !empty($this->transaction->customer_id)) {
            $claimedAt = $this->transaction->points_claimed_at 
                ? $this->transaction->points_claimed_at->format('d/m/Y H:i') 
                : 'sebelumnya';
            $this->errorMessage = "Poin untuk struk ini sudah pernah diklaim pada {$claimedAt}.";
            return;
        }

        if ($this->transaction->created_at && $this->transaction->created_at->diffInHours(now()) > 24) {
            $this->errorMessage = 'Masa klaim untuk struk ini sudah kedaluwarsa (maksimal 24 jam dari waktu pembelian).';
            return;
        }

        $this->pointsToClaim = $this->transaction->points_earned > 0
            ? $this->transaction->points_earned
            : LoyaltyService::calculateEarnedPoints((float) $this->transaction->subtotal, $this->setting);

        if ($this->pointsToClaim <= 0) {
            $this->errorMessage = 'Transaksi pada struk ini belum memenuhi nominal minimal untuk mendapatkan poin.';
        }
    }

    public function submitClaim()
    {
        $this->validate([
            'phone' => 'required|min:8|max:20',
            'name' => 'nullable|min:2|max:50',
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.min' => 'Nomor WhatsApp minimal 8 digit.',
        ]);

        $res = LoyaltyService::claimReceipt($this->token, $this->phone, $this->name);

        if (!$res['success']) {
            $this->errorMessage = $res['message'];
            return;
        }

        $this->isClaimed = true;
        $this->successData = $res;
    }

    public function render()
    {
        return view('livewire.customer.claim-points');
    }
}
