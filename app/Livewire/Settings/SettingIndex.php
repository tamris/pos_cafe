<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Pengaturan Struk & Toko - POS Cafe')]
class SettingIndex extends Component
{
    public $shop_name;
    public $address;
    public $phone;
    public $receipt_footer;
    public $wifi_name;
    public $wifi_password;
    public $auto_print_receipt = true;
    public $auto_print_kitchen = false;

    public function mount()
    {
        // Ambil data pertama (karena cuma ada 1 pengaturan)
        $setting = Setting::first();

        if ($setting) {
            $this->shop_name = $setting->shop_name;
            $this->address = $setting->address;
            $this->phone = $setting->phone;
            $this->receipt_footer = $setting->receipt_footer;
            $this->wifi_name = $setting->wifi_name;
            $this->wifi_password = $setting->wifi_password;
            $this->auto_print_receipt = (bool) ($setting->auto_print_receipt ?? true);
            $this->auto_print_kitchen = (bool) ($setting->auto_print_kitchen ?? false);
        }
    }

    public function update()
    {
        $this->validate([
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'wifi_name' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
        ]);

        // Update data pertama, atau create kalau belum ada
        $setting = Setting::first();
        
        $data = [
            'shop_name' => $this->shop_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'receipt_footer' => $this->receipt_footer,
            'wifi_name' => $this->wifi_name,
            'wifi_password' => $this->wifi_password,
            'auto_print_receipt' => $this->auto_print_receipt,
            'auto_print_kitchen' => $this->auto_print_kitchen,
        ];

        if (!$setting) {
            Setting::create($data);
        } else {
            $setting->update($data);
        }

        session()->flash('success', 'Pengaturan toko, struk & cetak otomatis berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.settings.setting-index');
    }
}