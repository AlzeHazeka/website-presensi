# DEPLOYMENT NOTE — WEBSITE PRESENSI (HYBRID SPATIE PERMISSION)

## STATUS IMPLEMENTASI

Sistem sudah dimodernisasi menggunakan:

- Laravel + Inertia + Vue
- Hybrid Role System:
  - Legacy enum role (`users.role`)
  - Spatie Permission

Pendekatan yang digunakan:

# SAFE HYBRID MIGRATION

Artinya:
- sistem lama tetap berjalan
- database lama tetap kompatibel
- Spatie ditambahkan sebagai layer baru
- rollout bisa dilakukan bertahap

---

# IMPORTANT SAFETY NOTES

## JANGAN lakukan:

```bash
php artisan migrate:fresh
```

atau:

```bash
php artisan db:wipe
```

atau reset database lainnya.

Karena database production sudah aktif digunakan.

---

# YANG AMAN DILAKUKAN

Deployment hanya membutuhkan:

## 1. Upload / Deploy code terbaru

Contoh:
- git pull
- upload project
- redeploy coolify
- dll

---

## 2. Jalankan migration BARU SAJA

```bash
php artisan migrate
```

AMAN karena migration saat ini:
- hanya menambah table/kolom baru
- tidak menghapus data lama
- tidak reset users table

---

# MIGRATION YANG AKAN TERJALAN

Contoh:
- tabel Spatie Permission
- metadata presensi
- enum role "Super Admin"
- dll

Semua bersifat:

# additive migration

bukan destructive migration.

---

# 3. Seed role & permission

Jalankan:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Tujuan:
- membuat roles
- membuat permissions
- assign permission dasar

---

# ROLE YANG DIBUAT

- Super Admin
- Admin
- Karyawan

---

# PERMISSION YANG DIBUAT

Permission sekarang dikelola terpusat di:

- `app/Support/Permissions.php`

Contoh (ringkas):

- users.*
- profile.*
- password.reset
- presensi.*
- izin.*
- lembur.*
- report.* (view + export)
- payroll.* (view + manage)
- roles.manage / permissions.manage / system.manage

---

# 4. MAPPING USER EXISTING KE SPATIE ROLE

## KENAPA INI DIPERLUKAN?

User lama:
sudah punya:

```php
users.role
```

Tetapi belum punya relasi Spatie di:
- `model_has_roles`

---

# SOLUSI

Jalankan sync role existing.

## Contoh sementara via Tinker

```bash
php artisan tinker
```

Lalu:

```php
App\Models\User::chunk(100, function ($users) {
    foreach ($users as $user) {
        if ($user->role) {
            $user->syncRoles([$user->role]);
        }
    }
});
```

---

# 5. RESET PERMISSION CACHE (PENTING)

Jika Anda:
- menambah permission baru
- mengubah assignment role → permission
- mengubah role user

Reset cache Spatie:

```bash
php artisan permission:cache-reset
```

Atau (opsional):

```bash
php artisan optimize:clear
```

---

# HASILNYA

User lama otomatis akan:
- mendapatkan role Spatie
- tetap mempertahankan enum lama

---

# SISTEM ROLE SEKARANG

## PRIMARY
Spatie Permission

## FALLBACK / COMPATIBILITY
users.role enum

---

# KOMPATIBILITAS PRODUCTION

## AMAN KARENA:

✅ users table tidak direset  
✅ data user lama tetap ada  
✅ login lama tetap jalan  
✅ auth lama tetap hidup  
✅ enum role lama tetap ada  
✅ middleware hybrid  
✅ fallback legacy tetap aktif  
✅ rollout bertahap memungkinkan  

---

# REKOMENDASI DEPLOYMENT

## SEBELUM DEPLOY

### Backup database production terlebih dahulu

Minimal:
- export SQL backup
- snapshot hosting/VPS bila ada

---

# URUTAN DEPLOYMENT YANG DIREKOMENDASIKAN

## STEP 1
Deploy code terbaru

---

## STEP 2

```bash
php artisan migrate
```

---

## STEP 3

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Untuk environment local/dev, `DatabaseSeeder` juga akan membuat akun testing:

- `superadmin@test.com` / `password`
- `admin@test.com` / `password`
- `karyawan@test.com` / `password`

---

## STEP 4

Sync user existing ke Spatie role.

---

## STEP 5

Reset cache Laravel:

```bash
php artisan optimize:clear
php artisan permission:cache-reset
```

---

# OPTIONAL CHECK

## Cek apakah role berhasil masuk:

```bash
php artisan tinker
```

```php
App\Models\User::first()->getRoleNames();
```

Harus muncul contoh:

```php
["Super Admin"]
```

atau:

```php
["Admin"]
```

---

# HAL YANG MASIH MENGGUNAKAN LEGACY ENUM

Saat ini beberapa area masih hybrid/fallback:
- middleware lama
- sidebar fallback
- beberapa conditional UI

Ini normal.

Karena project menggunakan:

# gradual migration strategy

---

# REKOMENDASI SELANJUTNYA

Setelah production stabil:

## PHASE BERIKUTNYA

- stabilisasi UI
- reusable components
- dashboard modernization
- operational analytics
- baru granular RBAC lebih dalam

---

# CURRENT SYSTEM STATUS

## SUDAH STABIL:
- Presensi
- Lembur
- Izin
- Riwayat
- Rekap
- Manual Input
- User Management
- Hybrid Permission
- Responsive Sidebar
- Operational UI

---

# CURRENT MIGRATION STRATEGY

SAFE + HYBRID + BACKWARD COMPATIBLE

Bukan rewrite total.
