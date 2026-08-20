<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Pengaturan Struk & Toko - POS Cafe')]
class SettingIndex extends Component
{
    use WithFileUploads;

    public $shop_name;
    public $address;
    public $phone;
    public $receipt_footer;
    public $wifi_name;
    public $wifi_password;
    public $auto_print_receipt = true;
    public $auto_print_kitchen = false;

    // Logo & Header Struk
    public $shop_logo;
    public $new_logo;
    public $show_logo_receipt = true;

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
            $this->shop_logo = $setting->shop_logo;
            $this->show_logo_receipt = (bool) ($setting->show_logo_receipt ?? true);
        }
    }

    public function removeLogo()
    {
        $setting = Setting::first();
        if ($setting && $setting->shop_logo) {
            Storage::disk('public')->delete($setting->shop_logo);
            $setting->update(['shop_logo' => null]);
        }

        $this->shop_logo = null;
        $this->new_logo = null;

        session()->flash('success', 'Logo cafe berhasil dihapus.');
    }

    protected $messages = [
        'shop_name.required' => 'Nama cafe wajib diisi',
        'address.required' => 'Alamat cafe wajib diisi',
        'phone.required' => 'Nomor telepon wajib diisi',
        'new_logo.image' => 'File yang dipilih harus berupa format gambar (PNG, JPG, JPEG, WebP).',
        'new_logo.max' => 'Ukuran file gambar maksimal 5MB.',
    ];

    public function cancelNewLogo()
    {
        $this->new_logo = null;
        $this->reset('new_logo');
    }

    public function update()
    {
        $this->validate([
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'wifi_name' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'new_logo' => 'nullable|image|max:5120',
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
            'show_logo_receipt' => (bool) $this->show_logo_receipt,
        ];

        // Handle upload logo baru
        if ($this->new_logo) {
            // Hapus logo lama jika ada
            if ($setting && $setting->shop_logo) {
                Storage::disk('public')->delete($setting->shop_logo);
            }

            $logoPath = $this->new_logo->store('logos', 'public');
            $fullStoredPath = Storage::disk('public')->path($logoPath);
            self::trimAndOptimizeLogo($fullStoredPath);

            $data['shop_logo'] = $logoPath;
            $this->shop_logo = $logoPath;
            $this->new_logo = null;
        } else {
            $data['shop_logo'] = $this->shop_logo;
        }

        if (!$setting) {
            Setting::create($data);
        } else {
            $setting->update($data);
        }

        session()->flash('success', 'Pengaturan toko, logo & struk berhasil disimpan.');
    }

    /**
     * Potong (auto-trim) area transparan/putih berlebih di sekeliling logo agar tidak ada jarak kosong di struk
     */
    public static function trimAndOptimizeLogo(string $filePath): void
    {
        if (!file_exists($filePath) || !extension_loaded('gd')) {
            return;
        }

        try {
            $content = file_get_contents($filePath);
            if (!$content) return;

            $img = @imagecreatefromstring($content);
            if (!$img) return;

            $w = imagesx($img);
            $h = imagesy($img);
            if ($w <= 0 || $h <= 0) {
                imagedestroy($img);
                return;
            }

            $minX = $w; $minY = $h; $maxX = 0; $maxY = 0;
            $hasContent = false;

            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    $rgba = imagecolorat($img, $x, $y);
                    $colors = imagecolorsforindex($img, $rgba);
                    $isTransparent = (isset($colors['alpha']) && $colors['alpha'] > 80);
                    $isWhite = ($colors['red'] > 245 && $colors['green'] > 245 && $colors['blue'] > 245);

                    if (!$isTransparent && !$isWhite) {
                        $hasContent = true;
                        if ($x < $minX) $minX = $x;
                        if ($x > $maxX) $maxX = $x;
                        if ($y < $minY) $minY = $y;
                        if ($y > $maxY) $maxY = $y;
                    }
                }
            }

            if (!$hasContent) {
                imagedestroy($img);
                return;
            }

            // Margin padding kecil (6px) agar tidak terpotong pas di tepi
            $pad = 6;
            $cropX = max(0, $minX - $pad);
            $cropY = max(0, $minY - $pad);
            $cropW = min($w - $cropX, ($maxX - $minX + 1) + ($pad * 2));
            $cropH = min($h - $cropY, ($maxY - $minY + 1) + ($pad * 2));

            $cropped = imagecreatetruecolor($cropW, $cropH);
            imagealphablending($cropped, false);
            imagesavealpha($cropped, true);
            $transparent = imagecolorallocatealpha($cropped, 255, 255, 255, 127);
            imagefilledrectangle($cropped, 0, 0, $cropW, $cropH, $transparent);

            imagecopy($cropped, $img, 0, 0, $cropX, $cropY, $cropW, $cropH);
            imagedestroy($img);

            imagepng($cropped, $filePath);
            imagedestroy($cropped);
        } catch (\Throwable $e) {
            // Ignore error gracefully
        }
    }

    public function render()
    {
        return view('livewire.settings.setting-index');
    }
}