# Overall System Verification Checklist

Tujuan dokumen ini: verifikasi menyeluruh bahwa modernisasi Laravel + Vue 3 + Inertia **tidak mengubah workflow lama**, dan sistem siap masuk fase UI polish / optimisasi / fitur baru.

Catatan penting:
- Jangan ubah schema/migration lama secara destruktif.
- Fokus pada “behavior sama”, bukan “implementasi sama”.

## A. Auth & Session
- Login via **email** berhasil → redirect ke `/dashboard`
- Login via **username** berhasil → redirect ke `/dashboard`
- User `status = tidak aktif` tidak bisa login (muncul error di field login)
- Logout berhasil (session berakhir, kembali ke halaman login/welcome)
- Forgot password:
  - request link reset password
  - email masuk di Mailpit
  - link reset valid → update password berhasil → bisa login dengan password baru
- Confirm password:
  - halaman confirm muncul saat dibutuhkan
  - password salah menampilkan error
- 2FA:
  - enable 2FA menghasilkan QR + setup key
  - recovery codes muncul & dapat regenerate
  - disable 2FA kembali normal
- Browser sessions:
  - logout other browser sessions meminta password dan berhasil

## B. Access Control (Backend)
- Login sebagai role non-admin:
  - akses `/admin/*` → **403**
  - akses `/data-user/*` → **403**
- Login sebagai admin:
  - akses `/admin/*` → **OK**
  - akses `/data-user/*` → **OK**

## C. Master → User Management
- List user tampil dan pagination berjalan
- Create user:
  - validasi required (nama/email/username/password)
  - user baru status default `aktif` (atau sesuai input)
  - user baru bisa login
- Edit user:
  - update data user berjalan
  - update status aktif/nonaktif berjalan
- Reset password user:
  - password berubah
  - session admin tidak berubah jadi user target

## D. Presensi (User)
- Presensi masuk:
  - sukses, status berubah, tidak double submit
- Presensi keluar:
  - sukses, status berubah, tidak double submit
- Riwayat presensi:
  - data tampil sesuai range/expectation
- Perhitungan gaji / hitung-gaji:
  - output sesuai behavior lama

## E. Presensi (Admin)
- By date:
  - filter tanggal bekerja
- By user:
  - filter user bekerja
- Edit presensi:
  - update presensi tersimpan dan sesuai
- Presensi manual:
  - create presensi manual bekerja
- Rekap presensi:
  - data rekap konsisten

## F. Export / PDF
- Export Excel:
  - file ter-download
  - kolom dan isi sesuai
- Export PDF:
  - file ter-download
  - layout dan data sesuai

## G. Navigation & Flash Messages
- Sidebar/nav link bekerja tanpa full reload (Inertia)
- Flash message success/error tampil konsisten setelah:
  - create/update/delete user
  - presensi masuk/keluar
  - update profile/password

## H. Responsive & Basic Accessibility
- Auth pages nyaman di mobile (layout tidak overflow)
- Focus ring jelas pada input & button
- Label terhubung ke input (for/id)

## I. Dev Workflow (Sail + Vite)
- `npm run dev` berjalan normal
- `npm run build` sukses
- `./vendor/bin/sail up` (jika pakai Docker Desktop WSL integration) service normal:
  - app, mysql, phpmyadmin, redis, mailpit

