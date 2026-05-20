# Global UI Color Guide

## CV. Irfan Putera Sejahtera — Internal System Design Language

> Design direction:
> Modern Corporate Internal System
> Clean • Cool • Professional • Stable • Readable

---

# 1. CORE COLOR PHILOSOPHY

UI menggunakan pendekatan:

- dominan putih / clean surface
- cool corporate blue sebagai identitas
- navy/slate untuk depth & premium feel
- accent seperlunya, tidak terlalu colorful

Target mood:

- profesional
- sejuk
- modern
- nyaman dipakai harian
- tidak melelahkan mata
- cocok untuk sistem operasional perusahaan AC

---

# 2. PRIMARY COLOR SYSTEM

## A. Primary Corporate Blue

Digunakan untuk:

- primary button
- active state
- selected navigation
- link utama
- focus ring
- interactive highlight

### Main

- Tailwind: `sky-500`
- Hex: `#0EA5E9`

### Hover

- Tailwind: `sky-400`
- Hex: `#38BDF8`

### Active / Deep

- Tailwind: `sky-600`
- Hex: `#0284C7`

### Soft Background

- Tailwind: `sky-500/10`
- Untuk:
    - badge
    - active row
    - soft highlight

---

## B. Dark Corporate Surface

Digunakan untuk:

- sidebar
- auth background
- overlay
- dark section
- dashboard hero

### Main Dark

- Tailwind: `slate-950`
- Hex: `#020617`

### Secondary Dark

- Tailwind: `slate-900`
- Hex: `#0F172A`

### Accent Dark Blue

- Tailwind: `blue-950`
- Hex: `#172554`

---

## C. Main Surface (Clean White)

Digunakan untuk:

- page content
- table
- card utama
- form area

### Primary Surface

- `white`
- `slate-50`

### Secondary Surface

- `slate-100`

PENTING:
Mayoritas halaman CRUD/dashboard tetap dominan terang/putih agar:

- readability tinggi
- nyaman dipakai lama
- data mudah dibaca

---

# 3. TYPOGRAPHY COLORS

## Primary Text

- `text-slate-800`

Untuk:

- heading
- important content
- table content

---

## Secondary Text

- `text-slate-600`

Untuk:

- description
- helper text
- metadata

---

## Text on Dark Surface

- `text-slate-100`
- `text-slate-200`

Untuk:

- auth page
- sidebar
- overlay section

---

# 4. BORDER & DIVIDER SYSTEM

## Standard Border

- `border-slate-200`

## Soft Border

- `border-white/10`
- `ring-white/15`

## Table Divider

- `divide-slate-100`

PENTING:
Hindari border hitam keras.
Gunakan border soft dan clean.

---

# 5. BUTTON SYSTEM

## Primary Button

- BG: `bg-sky-500`
- Hover: `hover:bg-sky-400`
- Active: `active:bg-sky-600`
- Text: `text-white`

Digunakan untuk:

- Save
- Login
- Submit
- Confirm

---

## Secondary Button

- BG: `bg-white`
- Border: `border-slate-200`
- Text: `text-slate-700`
- Hover: `hover:bg-slate-50`

Digunakan untuk:

- Cancel
- Back
- Neutral action

---

## Danger Button

- BG: `bg-rose-500`
- Hover: `hover:bg-rose-400`

---

# 6. INPUT SYSTEM

## Standard Input

- BG: `bg-white`
- Border: `border-slate-300`
- Text: `text-slate-800`
- Placeholder: `placeholder:text-slate-400`

## Focus State

- `focus:ring-2`
- `focus:ring-sky-400/70`
- `focus:border-sky-400`

PENTING:
Input harus clean dan readable.
Jangan terlalu gelap untuk halaman CRUD.

---

# 7. CARD SYSTEM

## Standard Card

- BG: `bg-white`
- Border: `border border-slate-200`
- Shadow: `shadow-sm`
- Radius: `rounded-2xl`

---

## Glass Card (Auth Only)

Digunakan hanya untuk:

- login
- auth page
- special hero section

### Style

- `bg-white/10`
- `backdrop-blur`
- `ring-1 ring-white/15`
- `shadow-2xl shadow-blue-950/40`

PENTING:
Glassmorphism jangan dipakai di seluruh app.
Hanya untuk area showcase/auth.

---

# 8. SIDEBAR STYLE

## Sidebar

- BG: `bg-slate-950`
- Text: `text-slate-200`

## Active Menu

- BG: `bg-sky-500/15`
- Text: `text-sky-300`

## Hover Menu

- BG: `hover:bg-white/5`

---

# 9. STATUS COLORS

## Success

- `emerald`

## Error

- `rose`

## Warning

- `amber`

## Info

- `sky`

PENTING:
Gunakan semantic color secara konsisten.
Jangan random.

---

# 10. GLOBAL UI RULES

## DO

- clean spacing
- soft shadow
- subtle border
- rounded modern UI
- readable typography
- consistent hover/focus state

## DON'T

- terlalu colorful
- gradient random
- neon startup style
- hard black border
- terlalu banyak glass effect
- terlalu banyak warna accent

---

# 11. FINAL VISUAL DIRECTION

Project ini menggunakan style:

"Modern Cool Corporate Internal System"

Karakter:

- clean
- efficient
- professional
- cool blue corporate
- nyaman untuk dashboard operasional harian
- readable untuk data-heavy application

Bukan:

- flashy startup
- gaming UI
- colorful SaaS
- cyberpunk/neon UI
