<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Kategori Pengeluaran (Out / Expense)
            [
                'name' => 'Bahan Baku Darurat',
                'slug' => 'bahan-baku-darurat',
                'description' => 'Pembelian bahan baku mendadak/habis di warung (es batu, susu, sirup, dll)',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Operasional & Utilitas',
                'slug' => 'operasional-utilitas',
                'description' => 'Gas LPG, galon air, sabun cuci, sedotan, kresek, dll',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Iuran, Sampah & Parkir',
                'slug' => 'iuran-sampah-parkir',
                'description' => 'Biaya retribusi sampah harian, uang parkir, atau keamanan lingkungan',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Konsumsi Tim / Kasir',
                'slug' => 'konsumsi-tim-kasir',
                'description' => 'Makan/minum tim kerja saat operasional toko',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Perawatan & Servis Alat',
                'slug' => 'perawatan-servis-alat',
                'description' => 'Servis grinder, mesin espresso, ganti kran, lampu putus, dll',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Gaji & Bonus Karyawan',
                'slug' => 'gaji-bonus-karyawan',
                'description' => 'Pembayaran gaji harian/bulanan atau bonus kasir/barista',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Tempat & Bangunan',
                'slug' => 'sewa-tempat-bangunan',
                'description' => 'Pembayaran sewa ruko / lapak toko',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Bahan Baku Besar (Supplier)',
                'slug' => 'bahan-baku-besar-supplier',
                'description' => 'Restock partai besar (biji kopi karungan, cup sablon, packaging)',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Pengeluaran Lain-lain',
                'slug' => 'pengeluaran-lain-lain',
                'description' => 'Biaya tak terduga lainnya di luar kategori utama',
                'type' => 'expense',
                'is_default' => true,
                'is_active' => true,
            ],

            // Kategori Kas Masuk (In / Cash In)
            [
                'name' => 'Tambah Modal Kembalian',
                'slug' => 'tambah-modal-kembalian',
                'description' => 'Penambahan uang pecahan receh ke laci kasir di tengah shift',
                'type' => 'cash_in',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Setoran Modal Kas',
                'slug' => 'setoran-modal-kas',
                'description' => 'Suntikan modal dana tunai kas toko dari owner',
                'type' => 'cash_in',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Pemasukan Lain-lain',
                'slug' => 'pemasukan-lain-lain',
                'description' => 'Pemasukan tunai di luar penjualan menu (pendapatan sewa meja, titipan, dll)',
                'type' => 'cash_in',
                'is_default' => true,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
