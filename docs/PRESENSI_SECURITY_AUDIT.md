# Presensi Security Audit (User Nakal / Anti-Cheat)

Dokumen ini adalah audit **mekanisme presensi** saat ini (Vue + Inertia) untuk menjawab:

- Apakah waktu & lokasi diambil saat tombol presensi ditekan?  
- Apa celah jika user memakai **fake GPS** atau **mengubah jam device**?
- Rekomendasi hardening yang **tidak mengubah flow bisnis utama**.

> Scope: audit & identifikasi + rekomendasi aman.  
> Tidak melakukan rewrite arsitektur atau perubahan DB destruktif.

---

## 1) Implementasi Saat Ini (Fakta dari Kode)

### A. Frontend (Browser)
File: `resources/js/Pages/Presensi/Index.vue`

- Saat user menekan tombol:
  - Browser memanggil `navigator.geolocation.getCurrentPosition(...)`.
  - Jika sukses:
    - `lokasi` dibuat dari `latitude, longitude`.
  - Data dikirim via `fetch()` ke endpoint:
    - `POST /presensi/masuk` atau `POST /presensi/keluar`
    - Body JSON: `{ lokasi, latitude, longitude, accuracy }`

✅ Jadi: **lokasi diambil pada saat tombol ditekan**, namun:

- **Lokasi** berasal dari **browser/OS location API** (bukan “GPS hardware terverifikasi”).
- **Waktu presensi** tidak lagi dikirim dari frontend.

### B. Backend (Server)
File: `app/Http/Controllers/PresensiController.php`

- `presensiMasuk()` menyimpan:
  - `jam_masuk = now()` (**server time authoritative**)
  - `lokasi_masuk = lat,lng` + metadata (lat/lng/accuracy/ip/ua)
- `presensiKeluar()` menyimpan:
  - `jam_keluar = now()` (**server time authoritative**)
  - `lokasi_keluar = lat,lng` + metadata (lat/lng/accuracy/ip/ua)

⚠️ Lokasi tetap berasal dari client, sehingga spoofing GPS masih mungkin, namun:

- backend melakukan validasi range `latitude/longitude`
- endpoint presensi memakai throttle limiter

---

## 2) Threat Model: Cara User Nakal Bisa Curang

### A. Fake GPS / Spoofing Lokasi
**Mudah dilakukan** karena:

- Browser location bisa diset manual via:
  - DevTools (Sensors / Location override)
  - Extension spoof location
  - Aplikasi “mock location” di Android
  - VPN + geolocation spoof (tergantung konfigurasi device)
- Endpoint presensi hanya butuh JSON `lokasi`, sehingga user bisa:
  - Mengirim request manual (Postman/cURL) dengan lokasi bebas
  - Mengubah payload di DevTools Network

**Dampak:** user bisa presensi dari luar kantor seolah-olah di kantor.

### B. Mengubah Jam Device (Clock Tampering)
Saat ini **server time** dipakai sebagai sumber kebenaran.

✅ Risiko “ubah jam device” untuk memalsukan jam presensi **sudah turun drastis**, karena jam resmi berasal dari server.

### C. Replay / Automation
Tanpa hardening tambahan:

- User bisa menembak endpoint presensi berulang (meski ada guard “sudah presensi masuk” dan “sudah presensi keluar”, tetap bisa spam/abuse).
- Jika suatu saat ada bug race condition, bisa membuat data ganda.

### D. Credential Sharing (Bukan GPS/Time, tapi human cheat)
User berbagi akun: orang lain presensi atas nama dia.

**Dampak:** presensi tidak merepresentasikan orang sebenarnya.

---

## 3) Kesimpulan Audit (Sekarang)

### Reliability
- **Lokasi:** *low reliability* (mudah spoof)
- **Waktu:** *low reliability* (mudah dimanipulasi jam device / payload)

### Root Cause
Server **tidak membuat server-time authoritative** dan **tidak memverifikasi lokasi**.

---

## 4) Rekomendasi Hardening (Bertahap, Minim Risiko)

### Level 0 — Paling Aman (Tanpa ubah UX)
1) **Validasi format input** di backend:
   - `waktu` harus format `HH:mm:ss`
   - `lokasi` harus format `lat, lng` dengan range valid:
     - lat: `-90..90`
     - lng: `-180..180`
2) **Normalisasi penyimpanan**:
   - Jangan simpan string mentah ke kolom `dateTime` tanpa parsing.
3) **Rate limit** endpoint presensi:
   - Misal `throttle:10,1` (10 req/menit) agar sulit abuse.

> Benefit: menutup “payload ngawur”, mengurangi spam, dan membuat data lebih rapi.

### Level 1 — Direkomendasikan (Server Time Authoritative)
1) Jadikan **server time** sebagai jam resmi presensi:
   - `jam_masuk/jam_keluar` = `now()` (timezone server/app)
2) Simpan `waktu` dari client hanya untuk referensi audit (opsional):
   - via **kolom baru** (`client_time_masuk/client_time_keluar`) *atau*
   - via tabel audit log terpisah

> Benefit: user **tidak bisa** curang dengan mengubah jam device.

### Level 2 — Geofencing (Anti Fake GPS “yang paling masuk akal di web”)
1) Tentukan koordinat kantor + radius (mis. 100–300 meter).
2) Backend memverifikasi `lokasi` berada dalam radius:
   - jika di luar radius: tolak atau tandai “suspicious”.
3) Frontend kirim `accuracy` dari geolocation:
   - `position.coords.accuracy` (meter)
   - jika accuracy terlalu buruk (mis. > 200m): minta user ulang.

> Catatan penting: geofencing **tidak membuat spoof GPS hilang**, tapi menaikkan biaya curang dan menurunkan kasus “asal presensi dari jauh”.

### Level 3 — Anti Cheat “Enterprise” (Opsional, tergantung kebutuhan)
Jika butuh benar-benar kuat:

- Wajibkan presensi hanya saat terhubung:
  - VPN kantor / IP range internal
- Foto selfie / snapshot (dengan consent)
- Mobile app dengan attestation (paling kuat, tapi ini sudah proyek lain)

---

## 5) Deteksi Perilaku Mencurigakan (Heuristik)

Tanpa memblok total, sistem bisa **menandai**:

- Lokasi masuk vs keluar beda jauh (mis. > 5–10 km) dalam waktu singkat.
- Lokasi di luar radius kantor.
- Presensi pada jam ekstrem (mis. 02:00) yang tidak sesuai aturan.
- Presensi berulang gagal karena “GPS tidak diizinkan”.
- Banyak user login dari device/user-agent yang sama (indikasi akun dipakai bersama).

Implementasi terbaik untuk ini biasanya via:

- tabel audit log terpisah (`presensi_audits`) + dashboard admin “suspicious activity”.

---

## 6) Rekomendasi Prioritas (Quick Wins)

**Wajib (paling berdampak):**

1) Server-time authoritative untuk `jam_masuk/jam_keluar`
2) Validasi format `lokasi` + parse lat/lng

**Opsional tapi bagus:**

3) Geofencing radius kantor
4) Simpan `accuracy`, `ip`, `user_agent` sebagai audit trail

---

## 7) Pertanyaan yang Perlu Dijawab Sebelum Hardening Level 1–2

Untuk implementasi aman, saya butuh jawaban ini:

1) Apakah `jam_masuk/jam_keluar` **harus** mengikuti jam device user, atau boleh dijadikan jam resmi server (timezone `Asia/Jakarta`)?
2) Apakah presensi harus dibatasi hanya di area kantor?
   - kalau ya: minta titik koordinat kantor + radius yang diinginkan
3) Apakah ada shift/jam kerja fleksibel yang perlu dipertimbangkan?
