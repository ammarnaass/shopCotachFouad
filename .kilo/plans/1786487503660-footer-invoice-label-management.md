# AN Shop — Footer + Invoice + Order Label Management System

## Status: Plan substantially pre-implemented — finalize the remaining gaps

Inspection of the codebase on 2026-08-11 shows the **majority of the originally proposed plan already exists** in the repository. This document captures the *verified current state* and lists only the **remaining work** required to complete the feature set. The implementation-capable agent should treat the "Remaining Work" section as the only actionable items.

---

## Verified Already Implemented (Do NOT re-create)

### Composer packages
- `barryvdh/laravel-dompdf` ✅
- `picqer/php-barcode-generator` ✅
- `simplesoftwareio/simple-qrcode` ✅

### Migrations (all present, timestamp `2026_08_11_*`)
- `2026_08_11_000001_create_footer_tables.php`
- `2026_08_11_000002_create_invoice_templates_table.php`
- `2026_08_11_000003_create_label_templates_table.php`
- `2026_08_11_000004_create_invoices_table.php`

### Models
- `app/Models/Content/FooterSection.php`, `FooterLink.php`, `FooterSocial.php`
- `app/Models/Documents/InvoiceTemplate.php`, `LabelTemplate.php`, `Invoice.php`
- `Invoice::generateNumber()` used by `InvoiceService` (sequential `INV-YYYY-XXXXXX` confirmed in service code)

### Services (`app/Services/`)
- `Documents/InvoiceService.php` — `getOrCreate`, `resolveTemplate`, `getInvoiceData`, `buildStoreSnapshot` ✅
- `Documents/LabelService.php` ✅
- `Documents/PdfService.php` — invoice/label + bulk invoice/label generation ✅
- `FooterService.php` ✅

### Controllers (`app/Http/Controllers/Admin/`)
- `FooterController.php` ✅ (all section/link/social CRUD + `reorderSections`)
- `InvoiceTemplateController.php` ✅
- `LabelTemplateController.php` ✅
- `PrintingSettingsController.php` ✅
- `OrderPrintController.php` ✅ — already refactored to use `PdfService` + `InvoiceService` + `LabelService`; supports `template_id` query param and `preview`/`print` HTML mode via `documents.layouts.print`

### Seeders
- `DocumentPermissionsSeeder.php` ✅
- `InvoiceTemplateSeeder.php` ✅
- `LabelTemplateSeeder.php` ✅

### Blade — document templates (`resources/views/documents/`)
- `invoices/classic.blade.php`, `modern.blade.php`, `minimal.blade.php`, `thermal.blade.php` ✅
- `labels/classic.blade.php`, `compact.blade.php`, `thermal.blade.php` ✅
- `layouts/print.blade.php`, `bulk-invoices.blade.php`, `bulk-labels.blade.php` ✅

### Blade components (`resources/views/components/documents/`)
- `invoice-header`, `invoice-customer`, `invoice-items`, `invoice-summary`, `invoice-footer` ✅
- `label-header`, `label-customer` ✅
- `barcode.blade.php`, `qr-code.blade.php` ✅

### Admin views
- `admin/footer/index.blade.php` ✅
- `admin/invoices/templates/{index,create,edit}.blade.php` ✅
- `admin/labels/templates/{index,create,edit}.blade.php` ✅
- `admin/settings/printing.blade.php` ✅
- `admin/layout.blade.php` — sidebar links to footer, invoices, order-labels, printing settings already present (lines 134–146)

### Settings
- `SiteSettings::defaults()` already includes all extended store + invoice + printing keys ✅
- `SettingsController` — **Store/social/contact/seo groups only**. The new `store_*` extended fields and `invoice_*` fields are present in `SiteSettings` defaults but are **NOT yet exposed in the Settings UI**. See Remaining Work #1.

### Routes (`routes/web.php`)
- `admin.footer.*` ✅
- `admin.invoices.templates.*` + preview/default ✅
- `admin.order-labels.templates.*` + preview/default ✅
- `admin.settings.printing` + `settings.printing.update` ✅
- Existing order print routes (`admin.orders.invoice`, `admin.orders.label`, `admin.orders.bulkInvoice`, `admin.orders.bulkLabel`) preserved ✅

### Ledi integration in order views
- `admin/orders/show.blade.php` (lines 104–140) — invoice + label dropdowns with Print/Preview and Download PDF exist, but **no template selector**. See Remaining Work #2.
- `admin/orders/index.blade.php` — bulk print invoices/labels already submits to `admin.orders.bulkInvoice` / `admin.orders.bulkLabel`, but **no template selector in the bulk bar**. See Remaining Work #2.

---

## Remaining Work

### 1. Expose extended store + invoice info in Settings UI
**Problem:** `SiteSettings::defaults()` holds `store_wilaya`, `store_commune`, `store_postal_code`, `store_website`, `store_phone_secondary` and `invoice_business_name`, `invoice_legal_name`, `invoice_rc`, `invoice_nif`, `invoice_nis`, `invoice_phone`, `invoice_address`, `invoice_email`, `invoice_notes`. These values feed `InvoiceService::buildStoreSnapshot()` and `getInvoiceData()`, so they must be editable.

**Tasks:**
- **[MODIFY]** `app/Http/Controllers/Admin/SettingsController.php`
  - Add a `store_extended` group to `$defaults` array with keys: `store_wilaya`, `store_commune`, `store_postal_code`, `store_website`, `store_phone_secondary`.
  - Add an `invoice_info` group to `$defaults` array with keys: `invoice_business_name`, `invoice_legal_name`, `invoice_rc`, `invoice_nif`, `invoice_nis`, `invoice_phone`, `invoice_address`, `invoice_email`, `invoice_notes`.
  - In `update()`, ensure these keys are persisted via `$this->settings->set($key, $value, $group)`. Note existing loop skips keys ending `_file` and empty image keys — text fields are fine.
  - Note: the `UpdateSettingsRequest` form request may need new keys added to its validation rules. Inspect `app/Http/Requests/Admin/UpdateSettingsRequest.php` and add the new keys (all `nullable|string|max:500` except `invoice_notes` which can be `nullable|string|max:2000`).
- **[MODIFY]** `resources/views/admin/settings/index.blade.php`
  - Add two new tab sections (`#store_extended`, `#invoice_info`) following the existing tab pattern.
  - `store_extended`: wilaya, commune, postal_code, website, phone_secondary text inputs.
  - `invoice_info`: business_name, legal_name, rc, nif, nis, phone, address, email, notes (notes as textarea).
  - Each section's Save button submits with `group=store_extended` / `group=invoice_info`.
  - RTL Arabic labels.

**Validation:** Save the form, confirm rows appear in the `settings` table, and that `site('invoice_nif')` returns the saved value after cache flush.

---

### 2. Add template selector to print controls in order views
**Problem:** `OrderPrintController` accepts `template_id`, but the UI never sends it. Users cannot choose a template.

**Tasks:**
- **[MODIFY]** `resources/views/admin/orders/show.blade.php` (lines 104–140)
  - In the Invoice dropdown add a `<select name="invoice_template_id">` populated from `App\Models\Documents\InvoiceTemplate::where('status', true)->ordered()->get()` (pass it from the controller showing the order, or query inline via `@php`).
  - Same for Label dropdown with `App\Models\Documents\LabelTemplate::where('status', true)->get()`.
  - Append `?template_id=` to the Print/Preview and Download PDF links based on the selected option (use a small Alpine `x-data` binding to update the `href`).
- **[MODIFY]** `resources/views/admin/orders/index.blade.php`
  - In the bulk bar (`#bulkBar`), add two selects (invoice template id + label template id) and include a hidden `template_id` input that the bulk action reads before submitting. Wire the existing JS (around line 217–252) to set `bulkForm.template_id.value` then submit to `admin.orders.bulkInvoice` / `admin.orders.bulkLabel`.
  - Ensure the bulk `<form>` includes the `template_id` hidden input and that `OrderPrintController@bulkInvoice`/`bulkLabel` read `$request->input('template_id')` (already does).

**Validation:** Open an order, change template, click Print Preview → URL contains `template_id`, preview renders the chosen template. Bulk-select 2 orders → print invoices with a non-default template → PDF uses that template.

---

### 3. Add missing `links.reorder` route + controller method
**Problem:** Plan specifies `Route::post('/sections/{section}/links/reorder', …)` and `FooterController::reorderLinks()`. Inspection shows `reorderSections()` exists but `reorderLinks()` does not, and the route is absent. If the footer admin UI has drag-sort for links, this is needed; otherwise it can be marked out of scope.

**Task:** Confirm whether the footer index view (`admin/footer/index.blade.php`) implements drag-to-sort for links.
- If yes → add `FooterController::reorderLinks(Request $request)` (mirror `reorderSections`) and register `Route::post('/links/reorder', [FooterController::class, 'reorderLinks'])->name('links.reorder')` inside the `footer.` group in `routes/web.php`.
- If no drag-sort UI for links exists → mark this item **out of scope** and stop. (link sort uses `sort_order` field set manually in the create/edit form.)

**Validation (if implemented):** Reorder links, confirm `sort_order` updated in DB, footer cache flushed.

---

## Out of Scope (explicitly)

- The pre-existing `admin/orders/invoice-pdf.blade.php`, `customer-label-pdf.blade.php`, `bulk-invoice-pdf.blade.php`, `bulk-label-pdf.blade.php` are **legacy hardcoded templates** no longer referenced by `OrderPrintController`. They are kept for historical/debugging purposes only. Do **NOT** regenerate or refactor them; removal is a separate decision and out of scope here.
- Permission seeding content is already handled by `DocumentPermissionsSeeder.php`; do not re-seed.
- Default template seeding is handled by `InvoiceTemplateSeeder.php` / `LabelTemplateSeeder.php`; do not re-seed.

---

## Final Validation Plan

Run after the three remaining items are implemented:

```bash
php artisan migrate:status
php artisan route:list | findstr /R "footer invoice order-label printing"
php artisan config:clear
php artisan cache:clear
```

Manual:
- [ ] `/ar/admin/settings#store_extended` → save wilaya/commune/postal → appears in an invoice PDF.
- [ ] `/ar/admin/settings#invoice_info` → save NIF/NIS/RC → appears in invoice PDF header block.
- [ ] Order show → invoice dropdown → select template → Print Preview renders selected template → Download PDF uses selected template.
- [ ] Orders index → select 2 orders → choose invoice template in bulk bar → bulk PDF uses template.
- [ ] Footer admin → create section + links + socials → confirm footer renders on storefront (depends on storefront footer partial — verify it consumes `FooterService::getSections()`).
- [ ] `/ar/admin/settings/printing` → set default invoice/label template + paper sizes → bypassing `template_id` in URL uses the default.
- [ ] Invoice number persists as `INV-2026-000001` (sequential, no duplicates) — confirmed by generating two invoices on the same order family and inspecting `invoices.invoice_number`.

---

## Notes for the implementation agent

- Do **not** recreate any file listed in the "Already Implemented" section.
- The `SiteSettings` cache (key `site_settings`, 10 min TTL) is flushed by `SettingsService::flush()` which is already called at the end of `SettingsController::update()`. No extra cache handling needed for new settings groups.
- `Invoice` rows are append-only (`order_id` unique). Do **not** touch the `orders` table.
- All new Blade must be RTL Arabic and Tailwind-consistent with the existing admin views (`bg-surface-container-low`, `text-on-surface`, `border-outline-variant`, `font-label-md`).
- No comments should be added to code unless explicitly requested.
