<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use App\Models\Setting;
use App\Services\LoyaltyService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.customer')]
#[Title('Kartu Member & Cek Poin - POS Cafe')]
class MemberCard extends Component
{
    public $phone = '';
    public $customer = null;
    public $histories = [];
    public $searched = false;
    public $setting = null;

    public function mount()
    {
        $this->setting = Setting::first();
        $phoneParam = request()->query('phone', '');

        if (!empty($phoneParam)) {
            $this->phone = $phoneParam;
            $this->checkPoints();
        }
    }

    public function checkPoints()
    {
        $this->validate([
            'phone' => 'required|min:8|max:20',
        ], [
            'phone.required' => 'Silakan masukkan nomor WhatsApp Anda.',
            'phone.min' => 'Nomor WhatsApp minimal 8 digit.',
        ]);

        $this->searched = true;
        $normalized = LoyaltyService::normalizePhone($this->phone);

        $this->customer = Customer::with(['pointHistories' => function ($q) {
            $q->latest()->take(5);
        }])
        ->where('phone', $normalized)
        ->orWhere('phone', $this->phone)
        ->first();

        if ($this->customer) {
            $this->histories = $this->customer->pointHistories;
        } else {
            $this->histories = [];
        }
    }

    public function render()
    {
        return view('livewire.customer.member-card');
    }
}
