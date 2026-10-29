# Pro / Free Separation Audit — Product Excel Import & Export for WooCommerce

Audit date: 2026-10-28
Free plugin: `product-excel-import-export-for-woocommerce` (main file `woo-product-excel-importer.php`, v7.0.6-dev) — this repo.
Pro plugin: `product-excel-import-export-for-woocommerce-pro` (main file `wpeiePro.php`, v12.1.5) — read-only reference.

---

## 1. How the two plugins relate, and what ships in Free

- The two plugins are **independent, standalone codebases**. Neither is generated from or included in the other; no shared files.
- **Pro replaces Free, it does not run alongside it.** Pro's activation hook deactivates Free (`product-excel-import-export-for-woocommerce-pro/includes/wpfactory-wc-peie-pro-init.php:20-28`). Note: that hook targets the legacy path `woo-product-excel-importer/woo_product_importer.php`, which no longer matches the current Free folder/file name — but that is Pro-side behavior and out of scope for this audit.
- **Namespaces do not collide:** Free uses the `wpeieWoo` capability and `woopei_*` AJAX actions/options; Pro uses the `wpeiePro` capability and `wpeiePro_*` AJAX actions/options.
- **What ships in Free (implemented behavior):**
  - Import / update **simple** products (create or update keyed on product title), with fields: post author, name, title, status, content, excerpt, `_sku`, `_weight`, `_regular_price`, `_sale_price`, `_stock`, `_virtual`, plus `product_cat` / `product_tag` terms (`import.php`, `woopei_process()`).
  - Export **simple** products with keyword / limit / offset filtering and a fixed free column set (`export.php`).
  - Import `product_cat` terms (name, description, slug, parent) (`import_categories.php`).
  - Upsell surface: locked/disabled pro fields, a pro popup, "Go Pro" action link, extensions popup, signup notice, deactivation survey.
- Free option IDs used: `woopei_hide_rating`, `woopei_notification`, `woopei_notified` (transients). **None of Pro's option IDs appear anywhere in Free** (verified by grep for `wpeiePro` across Free's own files — zero hits).

## 2. Pro features and how each was identified

Identification sources:
- **(Pro code)** — present in Pro, absent from Free.
- **(Free readme)** — `readme.txt` "PRO VERSION" section and FAQs that say "available in the PRO Version".
- **(Free popup)** — the Go-Pro modal feature list in `woo-product-excel-importer.php:216-244`.
- **(Free locked UI)** — fields/tabs rendered but disabled, readonly, or "Premium Version Only" / "PRO Version" labelled, i.e. the settings-UI-only pattern.
- **(Pro options)** — Pro option IDs for the feature exist; Free has none of them.

| # | Pro feature | How identified | Certainty |
|---|---|---|---|
| 1 | Import / update **Variable and Affiliate/External products** (with attributes) | Pro code (`wpeiePro_products.php`); Free popup `woo-product-excel-importer.php:231`; Free readme:34,47+ | Certain |
| 2 | **Product attributes** import | Locked in Free mapping `import.php:139`; Pro readme | Certain |
| 3 | **Featured image** import | Locked `import.php:120`; Free readme FAQ:123; Pro readme description | Certain |
| 4 | **Product image gallery** import | Locked `import.php:121`; Free readme FAQ:123 | Certain |
| 5 | **Downloadable** fields (downloadable flag, URL, limit, expiry), **purchase note**, **upsell/cross-sell IDs**, **taxable**, **tax class**, backorders, sold individually, featured, shipping class | Locked `import.php:124-129`; Pro readme "FIELDS SUPPORTED" | Certain |
| 6 | **Update by ID or SKU** (Free updates by title only) | Free readme FAQ:127 "available in PRO"; readme:109 "Update is based on Title"; Pro popup wording | Certain (but see B2 — Free's JS alert contradicts the readme) |
| 7 | **Export filters:** SKU, regular/sale price with comparison selectors, taxonomy choice, "only in stock" | Disabled + "PRO Version" in `export.php:54-55,70-114`; Pro changelog 11.4 | Certain |
| 8 | **More export fields** (attributes, images, downloadable, etc.) | "More in PRO Version" `export.php:140,164` | Certain |
| 9 | **Delete Products** and **Delete Categories** by Excel | Locked premium tabs `woo-product-excel-importer.php:183-184`; Pro tabs `deleteProduct`/`deleteCategories` in `wpeiePro_main.php` | Certain |
| 10 | **Save import / export mapping templates** | Free popup `woo-product-excel-importer.php:241`; Pro options `wpeiePro_save_mapping`, `wpeiePro_save_export_mapping`, `export_mapping_message` | Certain |
| 11 | **Scheduled (cron) import / export** from Excel URL or Google Sheets | Free popup `woo-product-excel-importer.php:242`; Pro options `wpeiePro_autoimport`, `wpeiePro_importUrl`, `wpeiePro_importFrequency`, `wpeiePro_importTime`, `wpeiePro_cronRun`, `wpeiePro_export_cronRun`, `wpeiePro_exportDate`, `wpeiePro_exportTime`; Pro deactivation hook clears `wpeiePro_updateWithCron`/`wpeiePro_exportWithCron` | Certain |
| 12 | **WPML product translations** import | Free popup:234; Free readme FAQ:145 | Certain |
| 13 | **ACF / manually defined custom fields + YOAST SEO fields** import/export | Free popup:235-236; Pro options `wpeieProshowCustomFields`, `wpeieProExtraFields`, `wpeiePro_acf_gal` | Certain |
| 14 | **Category images** and **attribute terms** import | Free category screen links `import_categories.php:26-27`; taxonomy dropdown options disabled "in Pro" | Certain |
| 15 | **Subscription products** import | Free popup:232 | Certain |
| 16 | **Role selection for plugin access in settings** (grant `wpeiePro`/access to other roles in the UI) | Pro option `wpeiePro_usr_access` + Pro settings tab; Free readme "OTHER FEATURES":29 | **Uncertain** (see B3) |

## 3. Verdicts

### Problem A — Not confirmed

For every pro feature, Free contains at most the locked settings UI. The server-side handlers (`woopei_process()`, `woopei_exportProducts()`, `WooImportCategories::importCategories()`) implement only the Free feature set and ignore the disabled/locked inputs (e.g., the export handler only applies keyword/limit/offset and never reads SKU/price/taxonomy parameters). No working pro behavior was found in Free — only the ambiguous leftovers listed below.

### Problem B — Partially confirmed

"Free depends on Pro" is **not confirmed**: no Free file calls or requires a Pro-only class, function, constant, or file, and every AJAX action Free's JS invokes is defined in Free. But "Free promises what it can't deliver" is **confirmed**: the readme description advertises image import as a Free capability, and Free's import JS dangles update-by-SKU/ID, both of which are Pro-only.

---

## Problem A — findings

Verdict: **Not confirmed.** No pro behavior is implemented in Free. The following items are the only pro-flavored code found, and all are ambiguous/dead rather than working behavior.

### A1. Vestigial image/gallery variables in the import handler
- **Feature:** Featured image & product image gallery import (pro).
- **Files:** `import.php:182-183` — `$images = array(); $gallery_images = array();`.
- **What the code does:** Declares two arrays that are **never used anywhere else** in `woopei_process()`. There is no media/attachment code in Free (`wp_insert_attachment`, `media_handle_*` etc. are absent), and the IMAGE / GALLERY mapping inputs are readonly "Premium Version Only" (`import.php:120-121`). Nothing is imported; nothing breaks.
- **Flag:** ambiguous/dead code — leftovers of the Pro image feature, not behavior. Harmless but misleading.

### A2. Free exports fields its own readme lists as Pro-only
- **Feature:** Export of `_width`, `_length`, `_height`, `_stock_status`, `_virtual`; import of `_virtual`.
- **Files:** `export.php:168` (form `$cols`), `export.php:258` and `export.php:337` (`$post_meta` arrays actually exported); `import.php:123` (`_virtual` import field).
- **What the code does:** Free fully implements and exposes these as normal (non-premium) fields, yet Free's own readme lists "length, width, height, ... virtual, ... stock status" under the **PRO VERSION** "FIELDS SUPPORTED" bullet (`readme.txt:62`).
- **Flag:** ambiguous — shared with a free feature. Either the Free implementation over-delivers pro fields, or the readme's PRO field list is stale. No locking exists for these fields in the Free UI, so they work in Free today.

### A3. Free JS references pro-only update modes in import validation
- **Feature:** Update by SKU / product ID (pro).
- **Files:** `js/woo-importer.js:115` (accepts `input[name='ID']` or `input[name='_sku']` as sufficient to submit) and `js/woo-importer.js:159` (alert: "Title Selection, SKU or Product ID (for update from export file) is Mandatory.").
- **What the code does:** Only a client-side gate and an alert string. The Free mapping form renders no `ID` input, and `woopei_process()` keys create/update exclusively on `post_exists( $title )` (`import.php:224-258`). No SKU/ID update behavior exists server-side.
- **Flag:** ambiguous — no pro behavior is executed, but the UI text misleads (see also B2, where the user-visible consequence is reported).

### A4. Dead pro-UI references in Free assets
- **Files:** `js/woo-importer.js:43` (`$(".premium_msg").slideDown('slow')`) and `js/woo-importer.js:251` (`$("#selectTaxonomy").hide()`); CSS rule `.premium_msg` at `css/woo-importer.css:144`.
- **What the code does:** Targets elements that do not exist anywhere in Free's markup (both exist in Pro). No-ops; the locked tabs still work because a second handler opens the pro popup (`js/woo-importer.js:348-351`).
- **Flag:** ambiguous dead code copied from Pro. No user-visible effect.

## Problem B — findings

Verdict: **Partially confirmed.** No technical dependency on Pro; two real "looks available but isn't" issues plus one unclear and one cosmetic.

### B1. Readme advertises product image import as part of Free
- **Feature:** Featured image / gallery import (pro).
- **File:** `readme.txt:16` — "Unlimited import/updates of Simple products along with their **images** is possible as the process works in batched to avoid PHP memory limits."
- **What it promises vs. reality:** Free's own UI locks IMAGE and PRODUCT IMAGE GALLERY as "Premium Version Only" (`import.php:120-121`), Free has no image-import code, and the same readme's FAQ (`readme.txt:123`) correctly says gallery/featured images are Pro. A Free user following the description cannot import images.
- **Advertised as free?** Yes — the readme Description section. Nothing breaks technically; it is an advertising/readme inconsistency.

### B2. Import UI dangles update-by-SKU/ID, which Free does not implement
- **Feature:** Update by SKU / product ID (pro).
- **Files:** `js/woo-importer.js:115` (SKU or ID accepted as a valid update key) and `js/woo-importer.js:159` (alert: "Title Selection, SKU or Product ID (for update from export file) is Mandatory."). Server-side update key is title only: `import.php:224-258` (`post_exists( $title )`).
- **What breaks in Free:** A user who maps only `_sku` (or believes an ID column works) can submit the form; `woopei_process()` then reads a missing `post_data['post_title']` (PHP warning under WP's usual error handling) and creates a product with an empty title instead of updating by SKU.
- **Advertised as free?** Not in the readme — the readme is correct ("Update is based on Title", `readme.txt:109`; SKU update marked Pro at `readme.txt:127`). The misleading text exists only in the in-app JS. No Pro dependency; misleads users about Free capability.

### B3. "OTHER FEATURES: define user role to use the plugin apart from admin" — unclear
- **File:** `readme.txt:29-30` vs. Free implementation `woo-product-excel-importer.php:46-47` (adds `wpeieWoo` capability to the administrator role only). Free has **no settings UI** to grant the capability to other roles; Pro has one (`wpeiePro_usr_access`).
- **Why unclear:** the readme's own FAQ (`readme.txt:148`) clarifies the capability can be assigned "programmatically or with a plugin like 'User Role Editor'", so the promise is technically deliverable without Pro. Flagged only because the "OTHER FEATURES" bullet reads like an in-plugin feature.
- **Advertised as free?** Yes (readme). No fatal risk either way.

### B4. (Reference) No Free→Pro dependency found
- Free contains **no** calls to Pro-only classes, functions, constants, or files (zero `wpeiePro` references in Free's own PHP/JS/CSS). All AJAX actions Free uses (`woopei_process`, `woopei_exportProducts`, `woopei_extensions`, `woopei_signup`, `woopei_push_not`, `woopei_hide_rating`) are defined in Free. Free runs standalone; this is reported as "no finding" for the dependency half of Problem B.

---

## 4. Decisions & resolutions (2026-10-29)

Reviewed with the plugin owner. Applied changes:

- **A1** — resolved. Removed the unused `$images` / `$gallery_images` variables from `woopei_process()` (`import.php`).
- **A2** — resolved: **keep as Free fields**. No code change; `readme.txt` "FIELDS SUPPORTED" bullet no longer lists `length, width, height`, `virtual`, `stock status` (and the duplicated `purchase note`) as Pro-only.
- **A3** — resolved via B2. Import JS validation now accepts only the mapped TITLE field; SKU/Product-ID update references removed from `js/woo-importer.js`.
- **A4** — resolved. Removed the dead `.premium_msg` and `#selectTaxonomy` references from `js/woo-importer.js` and the `.premium_msg` rule from `css/woo-importer.css`.
- **B1** — resolved. `readme.txt` description no longer claims image import; images remain Pro-only (locked in the Free UI).
- **B2** — resolved. JS requires TITLE only, and `woopei_process()` gained a server-side guard: rows with no mapped TITLE column or an empty TITLE cell are skipped with a warning instead of creating empty-title products. **Behavior note for existing Free users:** imports that previously relied on SKU/ID-only mapping created broken empty-title products; those rows are now skipped with a warning. Title-based import/update behavior is unchanged.
- **B3** — **left as is** by decision (the readme FAQ already explains the external `wpeieWoo` capability setup).
- **B4** — no finding; nothing to do.
- **Pro plugin: unchanged** — none of the fixes required changes in Pro.
