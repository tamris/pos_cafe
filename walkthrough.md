# Walkthrough: Soft Delete & Proteksi Data Pengguna / Kasir (User Management)

Telah diimplementasikan fitur **Soft Delete**, **Status Akun Aktif / Non-aktif (`is_active`)**, serta **Proteksi Data Historis Transaksi & Shift** pada manajemen pengguna (`/users`) untuk mencegah kehilangan data keuangan akibat kesalahan (*human error*).

---

## 🚀 Ringkasan Fitur yang Diimplementasikan

### 1. Perlindungan Database & Soft Delete User
* **Migrasi Database**: Menambahkan kolom `is_active` (boolean, default: `true`) dan kolom `deleted_at` (`softDeletes`) pada tabel `users`.
* **Model `User`**: Menggunakan trait `SoftDeletes` dan scope helper `scopeActive()`.
* **Model `Transaction` & `CashierShift`**: Relasi `user()` menggunakan `->withTrashed()`. Saat melihat riwayat transaksi, detail shift, atau mencetak struk kasir lama, nama kasir **tetap utuh 100%** meskipun akun kasir tersebut sudah diarsipkan atau dihapus.

### 2. Status Akun (`is_active: Aktif / Non-Aktif`) & Proteksi Login
* **Quick Toggle Switch**: Di tabel pengguna, admin dapat langsung menonaktifkan akun kasir yang resign / cuti dengan 1 klik.
* **Proteksi Login**: Kasir yang berstatus **Non-Aktif** otomatis diblokir saat mencoba login, dengan notifikasi yang jelas: *"Akun Anda telah dinonaktifkan oleh Administrator. Silakan hubungi pemilik cafe."*
* **Self-Lockout Guard**: Admin tidak dapat menonaktifkan akunnya sendiri yang sedang login. Sistem juga mencegah penonaktifan jika akun tersebut adalah satu-satunya admin aktif.

### 3. Tab Filter & Arsip Pengguna (Restore / Force Delete)
Di halaman **Manajemen Pengguna** (`/users`), tersedia 4 Tab Filter:
1. **Semua Pengguna**: Menampilkan seluruh data pengguna.
2. **Aktif (Bisa Login)**: Pengguna yang memiliki akses aktif ke sistem POS.
3. **Non-Aktif (Dibekukan)**: Pengguna yang sedang dibekukan hak loginnya.
4. **Arsip**: Pengguna yang telah di-*soft delete*.
   * **Tombol Pulihkan (Restore)**: Mengembalikan akun kasir ke daftar aktif.
   * **Tombol Hapus Permanen**: Hanya diizinkan jika akun belum pernah memiliki riwayat transaksi penjualan atau shift kasir sama sekali.

---

## 🛠️ File yang Diperbarui

| Komponen | File | Deskripsi |
| :--- | :--- | :--- |
| **Database Migration** | [`2026_08_16_000002_add_is_active_and_soft_deletes_to_users_table.php`](file:///d:/Antigravity/pos-inventory/database/migrations/2026_08_16_000002_add_is_active_and_soft_deletes_to_users_table.php) | Menambahkan kolom `is_active` dan `deleted_at` pada tabel `users` |
| **Eloquent Models** | [`User.php`](file:///d:/Antigravity/pos-inventory/app/Models/User.php) | Trait `SoftDeletes`, fillable `is_active`, `scopeActive` |
| | [`Transaction.php`](file:///d:/Antigravity/pos-inventory/app/Models/Transaction.php) | Relasi `user()` dengan `->withTrashed()` |
| | [`CashierShift.php`](file:///d:/Antigravity/pos-inventory/app/Models/CashierShift.php) | Relasi `user()` dengan `->withTrashed()` |
| **Autentikasi Login** | [`Login.php`](file:///d:/Antigravity/pos-inventory/app/Livewire/Auth/Login.php) | Pengecekan status `is_active = true` saat login |
| **Livewire Component** | [`UserIndex.php`](file:///d:/Antigravity/pos-inventory/app/Livewire/Users/UserIndex.php) | Tabs filter, `toggleStatus()`, `delete()`, `restore()`, `forceDelete()`, self-lockout check |
| **Blade View** | [`user-index.blade.php`](file:///d:/Antigravity/pos-inventory/resources/views/livewire/users/user-index.blade.php) | Tabs status, toggle switch status akses, modal form dengan status is_active |

---

## 🧪 Hasil Verifikasi & Pengujian
- [x] **Migrasi Berhasil**: Kolom `is_active` dan `deleted_at` terpasang di database.
- [x] **Tes Soft Delete & Restore**: Dibuat kasir uji coba -> diarsipkan -> dipulihkan -> berhasil.
- [x] **Integritas Relasi**: Relasi `Transaction` & `CashierShift` ke `User` aman dengan `withTrashed()`.
- [x] **Keamanan Login**: Kasir non-aktif diblokir dari login dan mendapatkan notifikasi yang sesuai.
