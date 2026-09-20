<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class ResetTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-transactions 
                            {--force : Paksa eksekusi tanpa konfirmasi interaktif}
                            {--keep-announcements : Pertahankan data pengumuman kasir}
                            {--keep-tokens : Pertahankan personal access token API}
                            {--reset-stocks : Reset semua angka stok bahan baku ke 0 untuk siap opname fisik}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan semua riwayat transaksi, detail penjualan, shift kasir, mutasi stok, dan session testing tanpa menyentuh data produk, user, dan master data lainnya.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->newLine();
        $this->output->block('🚀 PERSIAPAN PRODUCTION: PEMBERSIHAN DATA TRANSAKSI & MUTASI', 'INFO', 'fg=white;bg=blue', ' ', true);
        $this->newLine();

        // 1. Tampilkan Data Master yang DIJAMIN AMAN
        $this->components->info('🛡️  DATA MASTER YANG AMAN (DIPERTAHANKAN):');
        
        $masterTables = [
            'users' => 'Users / Kasir / Admin',
            'categories' => 'Kategori Menu',
            'products' => 'Produk / Menu & Harga',
            'ingredients' => 'Master Bahan Baku & Satuan',
            'product_ingredients' => 'Resep / Komposisi Menu (BOM)',
            'expense_categories' => 'Kategori Pengeluaran & Arus Kas',
            'settings' => 'Pengaturan Cafe / Struk / Logo',
        ];

        $masterDataCount = [];
        foreach ($masterTables as $table => $label) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $masterDataCount[] = [$label, "<fg=green>{$count} data</>"];
            }
        }
        $this->table(['Nama Data Master', 'Jumlah Saat Ini'], $masterDataCount);

        $this->newLine();

        // 2. Tampilkan Data Transaksional yang AKAN DIKOSONGKAN
        $this->components->warn('🗑️  DATA YANG AKAN DIKOSONGKAN & DI-RESET AUTO-INCREMENT KE 1:');

        $tablesToWipe = [
            'cash_movements' => 'Riwayat Arus Kas & Pengeluaran (Cash Flow)',
            'transaction_details' => 'Detail Item Penjualan POS',
            'transactions' => 'Transaksi Penjualan (POS & Online)',
            'cashier_shifts' => 'Riwayat Shift Kasir',
            'stock_mutations' => 'Riwayat Mutasi Stok Bahan Baku (POS, Restock, Opname, Rusak)',
        ];

        if (!$this->option('keep-tokens')) {
            $tablesToWipe['personal_access_tokens'] = 'API Personal Access Tokens';
        }

        if (!$this->option('keep-announcements')) {
            $tablesToWipe['announcements'] = 'Pengumuman Kasir';
        }

        // Job & Session tables
        $tablesToWipe['failed_jobs'] = 'Antrean Job Gagal';
        $tablesToWipe['jobs'] = 'Antrean Job';
        $tablesToWipe['job_batches'] = 'Batch Job';
        $tablesToWipe['sessions'] = 'Sesi Login / Sessions';
        $tablesToWipe['password_reset_tokens'] = 'Token Reset Password';

        $wipeDataCount = [];
        foreach ($tablesToWipe as $table => $label) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $wipeDataCount[] = [$label, "<fg=yellow>{$count} data</>"];
            }
        }
        $this->table(['Tabel yang akan Direset', 'Jumlah Data Saat Ini'], $wipeDataCount);

        if ($this->option('reset-stocks')) {
            $this->components->warn('⚠️  Opsi --reset-stocks aktif: Kolom stok di tabel ingredients akan di-set ke 0.');
        } else {
            $this->components->info('ℹ️  Angka stok bahan saat ini DIPERTAHANKAN. (Gunakan --reset-stocks jika ingin di-set ke 0).');
        }

        $this->newLine();

        // 3. Konfirmasi Keamanan
        if (!$this->option('force')) {
            $confirmed = $this->confirm('⚠️  Apakah Anda yakin ingin MENGOSONGKAN data transaksi & mutasi di atas?', false);
            if (!$confirmed) {
                $this->components->warn('Operasi dibatalkan. Tidak ada data yang diubah.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->components->info('Sedang melakukan pembersihan data...');

        // 4. Eksekusi TRUNCATE dengan Disable Foreign Key Constraints
        Schema::disableForeignKeyConstraints();

        $clearedCount = 0;
        foreach ($tablesToWipe as $table => $label) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("  <fg=green>✔</> Berhasil mengosongkan <fg=cyan>{$table}</> ({$label}) & reset ID ke 1");
                $clearedCount++;
            }
        }

        if ($this->option('reset-stocks') && Schema::hasTable('ingredients')) {
            DB::table('ingredients')->update(['stock' => 0]);
            $this->line("  <fg=green>✔</> Berhasil me-reset semua stok bahan baku ke <fg=cyan>0</>");
        }

        Schema::enableForeignKeyConstraints();

        // 5. Bersihkan Cache & Optimize
        $this->newLine();
        $this->components->info('Membersihkan cache aplikasi & config...');
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            $this->line('  <fg=green>✔</> Cache & Config dibersihkan');
        } catch (\Throwable $e) {
            $this->line('  <fg=yellow>!</> Gagal membersihkan cache: ' . $e->getMessage());
        }

        $this->newLine();
        $this->output->success('✨ SEMUA DATA TRANSAKSI & MUTASI BERHASIL DIKOSONGKAN DENGAN AMAN!');
        $this->line('Semua data Master (Produk, Resep, Bahan Baku, Kategori, User, Setting) tetap terjaga 100%.');
        $this->line('Aplikasi siap digunakan untuk transaksi baru di Production.');
        $this->newLine();

        return Command::SUCCESS;
    }
}