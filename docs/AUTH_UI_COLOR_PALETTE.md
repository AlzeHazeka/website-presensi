# Auth UI Color Palette

Dokumen ini mencatat kombinasi warna yang dipakai untuk area **Auth** (Login/Forgot/Reset/Confirm) setelah tuning UI berbasis Vue 3 + Inertia + Tailwind.

Referensi utama:

- `docs/GLOBAL_COLOR_PALETTE.md`

> Design direction:
> Modern corporate internal system • cool blue • clean • professional • comfortable for daily use  
> Glassmorphism hanya untuk area Auth (ringan, tidak berlebihan).

---

## 1) Background Auth (Dark + Cool Blue)

- Base background: `bg-slate-950` (`#020617`)
- Gradient overlay:
  - `from-slate-950/80`
  - `via-slate-900/70`
  - `to-blue-950/70`
- Optional background image (subtle): `opacity-25`
- Blur overlay: `backdrop-blur-[2px]`

Tujuan: depth yang tenang + identitas “cool blue”, tetap readable.

---

## 2) Auth Container / Card (Glass)

- Card surface: `bg-white/10`
- Card ring: `ring-1 ring-white/15`
- Shadow: `shadow-2xl shadow-blue-950/40`
- Rounding: `rounded-3xl`
- Backdrop blur: `backdrop-blur`

Tujuan: glass card ringan, elegan, tidak “flashy”.

---

## 3) Auth Typography

- Title/Heading: `text-white`
- Subtext: `text-slate-200/85` sampai `text-slate-200/90`
- Meta text: `text-slate-200/70`

---

## 4) Primary Action (Login / Submit)

- Primary button background: `bg-sky-500` (`#0EA5E9`)
- Hover: `hover:bg-sky-400` (`#38BDF8`)
- Active: `active:bg-sky-600` (`#0284C7`)
- Text: `text-white`
- Focus ring: `focus-visible:ring-sky-500`

---

## 5) Links (Forgot Password / Helper)

- Link: `text-sky-200`
- Hover: `hover:text-white`
- Underline: `underline underline-offset-4`

---

## 6) Form Elements (Input/Label/Error)

Khusus Auth (dark glass background):

- Label: `text-slate-100/90`
- Input bg: `bg-white/10`
- Input border: `border-white/10`
- Placeholder: `placeholder:text-slate-200/50`
- Focus border/ring: `focus:border-sky-400/60` + `focus:ring-sky-400/30`
- Error: `text-rose-200` / `text-rose-300` (kontras aman di background gelap)

---

## 7) Accent Panel (Left Branding section)

- Accent gradient: `from-sky-500/10` → `to-blue-950/25`
- Info box: `bg-white/5 ring-1 ring-white/10`

---

## Catatan Implementasi

- Auth layout split (Branding kiri + Form kanan) ada di `resources/js/Layouts/GuestLayout.vue`.
- Halaman Login: `resources/js/Pages/Auth/Login.vue`.

