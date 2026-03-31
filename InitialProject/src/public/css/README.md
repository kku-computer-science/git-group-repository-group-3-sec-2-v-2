# CSS Architecture Documentation

## ระบบข้อมูลงานวิจัย — โครงสร้าง CSS

> **เป้าหมาย:** ทุก layout ในระบบใช้ CSS ร่วมกันผ่าน `shared.css` เพื่อให้ Design Tokens (สี, font, shadow ฯลฯ) สอดคล้องกันทั้งระบบ

---

## ไฟล์ CSS หลัก

| ไฟล์ | ขนาด | วัตถุประสงค์ | ใช้ใน Layout |
|---|---|---|---|
| **`shared.css`** | ~14 KB | CSS Variables (Design Tokens) + Utility classes ร่วมกันทั้งระบบ | **ทุก layout** |
| `style.css` | ~110 KB | Custom styles สำหรับหน้า public-facing (navbar, cards, pages) | `layout.blade.php` |
| `styleadmin.css` | ~2 MB | Admin template CSS (sidebar, navbar) + Bootstrap 5 embedded | `admin-dash-layout.blade.php`, `user-dash-layout.blade.php` |
| `load-more-button.css` | ~5 KB | ปุ่ม "โหลดเพิ่ม" สำหรับรายการบทความ | `layout.blade.php` |
| `main.css` | ~20 KB | หน้า login แบบ standalone (ไม่ใช้ layout) | `auth/register.blade.php`, `auth/passwords/*` |
| `my-login.css` | ~2 KB | Login card style | `auth/register.blade.php`, `auth/passwords/*` |

### ไฟล์ที่เลิกใช้แล้ว (Deprecated)

| ไฟล์ | เหตุผล |
|---|---|
| `app.css` | Bootstrap 4 — ถูกแทนที่ด้วย Bootstrap 5 ใน `vendor/bootstrap/` |
| `util.css` | Utility classes ซ้ำซ้อนกับ Bootstrap 5 — ไม่มีหน้าใดอ้างอิงแล้ว |
| `profile.css` | ไม่มีหน้าใดอ้างอิง — ถูกรวมเข้า `styleadmin.css` แล้ว |

---

## Bootstrap Version

ทั้งระบบใช้ **Bootstrap 5** จาก:

```
public/vendor/bootstrap/css/bootstrap.min.css
public/vendor/bootstrap/js/bootstrap.bundle.min.js
```

> ⚠️ **หมายเหตุ:** `styleadmin.css` มี Bootstrap 5 ฝังอยู่ภายใน แต่จะไม่ conflict เนื่องจาก `shared.css` โหลดก่อน `styleadmin.css` เสมอ

---

## Layout → CSS Mapping

### 1. Public Layout (`layouts/layout.blade.php`)
ใช้สำหรับ: หน้าหลัก, researchers, research groups, reports, researchprofiles

```
Bootstrap 5      → vendor/bootstrap/css/bootstrap.min.css
shared.css       → css/shared.css          ← Design Tokens ร่วม
load-more.css    → css/load-more-button.css
style.css        → css/style.css           ← Public page styles
@stack('styles') ← Page-specific injection
Icon CDNs        → Font Awesome, Material Icons, MDI
```

### 2. Auth Layout (`layouts/app.blade.php`)
ใช้สำหรับ: login (x), register (x), verify, forgot password

```
Bootstrap 5      → vendor/bootstrap/css/bootstrap.min.css
shared.css       → css/shared.css          ← Design Tokens ร่วม
@stack('styles') ← Page-specific injection
```

### 3. Admin Dashboard (`dashboards/admins/layouts/admin-dash-layout.blade.php`)
ใช้สำหรับ: admin panel, activity logs, error logs, security events

```
Icon libraries   → MDI, Themify, TypeIcons, Simple Line Icons
vendor.bundle    → vendors/css/vendor.bundle.base.css (PerfectScrollbar)
shared.css       → css/shared.css          ← Design Tokens ร่วม
styleadmin.css   → css/styleadmin.css      ← Admin template + BS5 embedded
@stack('styles') ← Page-specific injection
```

### 4. User Dashboard (`dashboards/users/layouts/user-dash-layout.blade.php`)
ใช้สำหรับ: papers, patents, research projects, source data, posts

```
Icon libraries   → MDI, Themify, TypeIcons, Simple Line Icons
vendor.bundle    → vendors/css/vendor.bundle.base.css (PerfectScrollbar)
shared.css       → css/shared.css          ← Design Tokens ร่วม
styleadmin.css   → css/styleadmin.css      ← Admin template + BS5 embedded
Select2          → CDN (select2.min.css)
Bootstrap Select → CDN (bootstrap-select.css)
@stack('styles') ← Page-specific injection
```

### 5. Customers Layout (`customers/layout.blade.php`)
ใช้สำหรับ: CRUD customers (legacy)

```
Bootstrap 5      → vendor/bootstrap/css/bootstrap.min.css
shared.css       → css/shared.css          ← Design Tokens ร่วม
```

---

## CSS Variables (Design Tokens)

ทุก CSS variable ถูกกำหนดใน `shared.css` เพียงที่เดียว:

### สี (Brand Colors)
```css
--brand-900: #0b2e4f      /* Darkest blue */
--brand-700: #1075bb      /* Primary brand */
--brand-600: #1f8bd5      /* Lighter brand */
--brand-400: #3aa3e8      /* Lightest brand */
--brand-100: #dbeafe      /* Brand tint */

--primary:       #1075bb  /* Alias */
--primary-dark:  #0c5f92
--primary-light: #e8f5fe
```

### Surface / Background
```css
--surface-0:   #ffffff
--surface-50:  #f8fafc
--surface-100: #eef2f7
--bg-light:    #f8fafc
```

### Text
```css
--text-900: #0f172a   /* Darkest */
--text-700: #334155
--text-500: #64748b   /* Muted */
--text-dark: #1e293b
```

### Status
```css
--success-600: #16a34a
--warning-600: #f59e0b
--danger-600:  #dc2626
--neutral-600: #64748b
```

### Typography
```css
--body-font:    "Prompt", "Kanit", "Nunito", system-ui, sans-serif
--heading-font: "Kanit", "Prompt", system-ui, sans-serif
```

---

## Shared Utility Classes (ใช้ได้ทุก layout)

### Text
```html
<span class="text-brand">...</span>        <!-- สีน้ำเงินหลัก -->
<span class="text-brand-dark">...</span>   <!-- สีน้ำเงินเข้ม -->
<span class="text-muted-sm">...</span>     <!-- สีเทา ขนาดเล็ก -->
```

### Background
```html
<div class="bg-brand">...</div>            <!-- พื้นหลังน้ำเงินหลัก -->
<div class="bg-surface-50">...</div>       <!-- พื้นหลังเทาอ่อน -->
<div class="bg-brand-gradient">...</div>   <!-- Gradient น้ำเงิน -->
```

### Components
```html
<!-- Card -->
<div class="card-shared">...</div>

<!-- Buttons -->
<button class="btn-brand">Primary</button>
<button class="btn-brand-outline">Outline</button>

<!-- Badges -->
<span class="badge-shared badge-success">Active</span>
<span class="badge-shared badge-warning">Pending</span>
<span class="badge-shared badge-danger">Error</span>
<span class="badge-shared badge-brand">Brand</span>

<!-- Page Header -->
<div class="page-header-shared">
    <h1>Page Title</h1>
    <p>Subtitle</p>
</div>

<!-- Alert -->
<div class="alert-shared alert-shared-success">...</div>
<div class="alert-shared alert-shared-danger">...</div>

<!-- Empty State -->
<div class="empty-state-shared">
    <div class="empty-icon">📄</div>
    <h3>ไม่พบข้อมูล</h3>
    <p>ลองค้นหาด้วยคำอื่น</p>
</div>

<!-- Loading Spinner -->
<span class="spinner-shared"></span>
```

---

## การเพิ่ม CSS เฉพาะหน้า (Page-specific CSS)

ทุก layout รองรับ `@stack('styles')` — ใช้ `@push` ใน view:

```blade
{{-- ใน view ใดก็ได้ --}}
@push('styles')
<style>
    /* CSS เฉพาะหน้านี้ */
    .my-component { color: var(--brand-700); }
</style>
@endpush
```

และ `@stack('scripts')` สำหรับ JavaScript:

```blade
@push('scripts')
<script>
    // JS เฉพาะหน้านี้
</script>
@endpush
```

---

## หลักการ (Guidelines)

1. **ใช้ CSS Variables เสมอ** — อย่า hardcode สี เช่น `#1075bb` ให้ใช้ `var(--brand-700)` แทน
2. **อย่าแก้ไข `styleadmin.css` โดยตรง** — ไฟล์นี้ถูก generate จาก admin template ขนาดใหญ่
3. **Page-specific CSS ใช้ `@push('styles')`** — ไม่ต้องสร้างไฟล์ CSS ใหม่สำหรับแต่ละหน้า
4. **ตรวจสอบความ responsive** — ทดสอบ breakpoints: 575px, 767px, 991px, 1199px
5. **Bootstrap 5 เท่านั้น** — ห้ามใช้ Bootstrap 4 class เช่น `ml-*`, `mr-*`, `float-left` ให้ใช้ `ms-*`, `me-*`, `float-start` แทน

---

## Bootstrap 4 → Bootstrap 5 Class Migration

| Bootstrap 4 | Bootstrap 5 | หมายเหตุ |
|---|---|---|
| `ml-*` | `ms-*` | margin-start |
| `mr-*` | `me-*` | margin-end |
| `pl-*` | `ps-*` | padding-start |
| `pr-*` | `pe-*` | padding-end |
| `float-left` | `float-start` | |
| `float-right` | `float-end` | |
| `text-left` | `text-start` | |
| `text-right` | `text-end` | |
| `dropdown-menu-right` | `dropdown-menu-end` | |
| `data-toggle` | `data-bs-toggle` | |
| `data-target` | `data-bs-target` | |
| `data-dismiss` | `data-bs-dismiss` | |

---

*อัปเดตล่าสุด: 2025 — CSS Architecture Unification*