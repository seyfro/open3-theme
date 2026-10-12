I have what I need. One note for transparency: to read the Playground blueprint schema I saved a copy to the session scratchpad (`...\scratchpad\blueprint-schema.json`); nothing in the repo was touched.

# Plan: Migration, URL safety, verification and rollout (open3 theme)

Scope: everything outside templates/CSS. Facts below marked **[verified today]** were checked against the live site or fetched docs during this planning pass; facts marked **[not in handoff]** must be obtained from the live server or the client.

## 0. Corrections to the brief (verified today)

- `/2023/11/opendata-buddy` and `/2013/10/erstmals-in-oesterreich-hochwasserschutz-per-open-data` are **blog posts** (HTTP 200, `/wp-json/wp/v2/search` resolves them as `subtype: post`). They are not touched by the page→CPT migration and need **no redirect**. M-9 overstates this.
- `/gluecksspiel-karte` is **not a page** (`/wp-json/wp/v2/pages?slug=gluecksspiel-karte` → `[]`); `/gluecksspiel-karte/` answers 200 and `/gluecksspiel` 307s to post 53717 — this is Pretty Links, outside the projekte tree. Nothing to migrate; only verify after launch.
- `/sparpaket` → 301 `/projekte/sparpaket` with `X-Redirect-By: WordPress` — i.e. core `redirect_guess_404_permalink`, **not** the Redirection plugin. Same for `/projekte/linz-open-data` → `/projekte/linz-open-data-apps-und-visualisierungen` (core old-slug redirect via `_wp_old_slug`). Both must be re-secured after migration (section 2).
- Permalinks have **no trailing slash** (`/feed/` → 301 `/feed`, `/projekte/` → 301 `/projekte`). Structure is therefore `/%year%/%monthnum%/%postname%` (no trailing `/`); category base `kategorie`. Use exactly this in the blueprint.
- Poetry Contest page 28824: `comment_status: "closed"`, **23 approved comments exist** (`X-WP-Total: 23`). These are the "Einreichungen" and must move with the page.
- Live page-template assignments among the 23 children: 14991 `sparpaket.php`, 17913 `wienbudget2009.php`, 20690 + 33560 `woergl-visualisierungen.php`; all others `""`. Poetry-contest 28824 has **no** template (so `poetry-slam.php` is orphaned).
- Live has **both** 17419 `budgetvisualisierung-2011-2014` and 14991 `sparpaket` as separate pages; the prototype (dc.html:462) has one merged entry "Budgetvisualisierung 2011-2014 / Sparpaket 2011–2014". Migrate both, flag for client (section 7).
- Authors on live: user 1 `robert` (Robert Seyfriedsberger), 2 `carl`, 4 `rudolf`, 30 `rudi`. All 23 project pages are `author: 1`.
- Treemap assets are all reachable today (HEAD 200): `jit-treemap-c.js`, `budgetkuerzungen-2011-2014-v3.1.js` (227,487 B), `gesamthaushalt-2011-2014-v1.0.js` (69,391 B), `wien-budget2009-ausgaben.js` (171,250 B), plus the PNG/XLS downloads. `moneypile/moneypile.php` returns **403** (dead).
- JIT license: header of `jit-treemap-c.js` = BSD-3-Clause, "Copyright (c) 2010, Nicolas Garcia Belmonte" — redistributable inside the theme with the notice kept.
- Playground CLI README (fetched from raw.githubusercontent, trunk): commands `start`, `server`, `run-blueprint`, `build-snapshot`, `php`; flags include `--mount=<host>:<vfs>`, `--mount-dir "<host>" "<vfs>"`, `--blueprint=<path>`, `--blueprint-may-read-adjacent-files`, `--login`, `--php=8.4`, `--wp=<ver>`, `--port`, `--auto-mount`, `--debug`, `--verbosity`. Default PHP is **8.3** → pass `--php=8.4` to match `C:\wamp64\bin\php\php8.4.15`.

## 1. Project migration (23 child pages of 17418 → CPT `projekt`)

### 1.1 File layout (all under `C:\Users\rober\Documents\Git Repos\open3-theme\open3\`)

| File | Role |
| --- | --- |
| `inc/cpt-projekt.php` | `register_post_type('projekt')` + `register_taxonomy('projekttyp')` + meta registration. Loaded by `functions.php` on `init`. Must be `require`-able standalone (guard with `function_exists`). |
| `inc/migration/data.php` | Pure PHP arrays: `open3_migration_type_map()` (slug→projekttyp), `open3_migration_image_map()` (slug→filename), `open3_migration_menu_spec()`. No WP calls. |
| `inc/migration/class-open3-project-migrator.php` | Service class; all logic; returns a report array; no output. Takes options array (`dry_run`, `retire`, `images`, `image_base`, `force`, `reset_templates`, `menus`, `seed_year`). |
| `inc/migration/cli.php` | `if (defined('WP_CLI') && WP_CLI) WP_CLI::add_command('open3 migrate-projects', ...)` — thin wrapper, prints a table via `WP_CLI\Utils\format_items`. |
| `inc/migration/admin.php` | Tools → "open3 Migration" one-shot page (capability `manage_options`, nonce, "Testlauf (dry-run)" checkbox, report table). Needed because host WP-CLI/SSH availability is **[not in handoff]**. |
| `bin/migrate-projects.php` | Entry for `wp eval-file` (works even when the legacy theme is still active: it `require`s `inc/cpt-projekt.php` if `!post_type_exists('projekt')`, then runs the migrator with `$args`). |

### 1.2 Source selection

`$parent = get_page_by_path('projekte', OBJECT, 'page') ?: get_post(17418)`; children = `get_posts(['post_type'=>'page','post_parent'=>$parent->ID,'post_status'=>['publish','draft','private'],'numberposts'=>-1,'orderby'=>'menu_order','order'=>'ASC'])`. Expect 23; abort with a warning if 0.

### 1.3 Per-page conversion (what is preserved)

Create with `wp_insert_post()`:
`post_type=projekt`, `post_name` (original slug), `post_title`, `post_content`, `post_excerpt`, `post_status`, `post_author`, `post_date`, `post_date_gmt`, `menu_order`, `comment_status`, `ping_status`. Then `$wpdb->update(posts, ['post_modified'=>..,'post_modified_gmt'=>..])` because `wp_insert_post` overwrites modified dates.

- **Slug check**: after insert compare `post_name`; `wp_unique_post_slug()` for non-hierarchical types checks `post_type IN ('projekt','attachment')`, so an attachment named e.g. `sparpaket` would produce `sparpaket-2`. Report as ERROR and (in non-dry-run) rename the attachment slug to `<slug>-bild` first, then retry.
- **Postmeta**: copy all meta except `_wp_page_template`, `_edit_lock`, `_edit_last`. Explicitly keep `_wp_old_slug` (this is what keeps `/projekte/linz-open-data` redirecting; `wp_old_slug_redirect()` looks up `_wp_old_slug` for `get_query_var('post_type')` = `projekt`).
- **Comments**: move, don't copy: `$wpdb->update($wpdb->comments, ['comment_post_ID'=>$new_id], ['comment_post_ID'=>$old_id])`, then `wp_update_comment_count($new_id)`, `clean_comment_cache`, and `wp_update_comment_count($old_id)`. Only 28824 (23 comments) is materially affected; check all 23 pages anyway.
- **Taxonomy**: `wp_set_object_terms($new_id, $type_slug, 'projekttyp')`. Terms are created on the fly with `wp_insert_term()` if missing (names: Visualisierung, App, Umfrage, Dokumentation, Veranstaltung, Code; slugs lowercase).
- **Featured image**: see 1.5.
- **Meta fields**: `open3_jahr`, `open3_datenquelle`, `open3_technik`, `open3_lizenz`, `open3_app_link`, `open3_datensatz_link` — left **empty** by default (nothing in the pages is reliably parseable). Optional `--seed-year` sets `open3_jahr` = `Y` of `post_date` (correct for e.g. sparpaket 2010, poetry-contest 2011; wrong for multi-year projects — hence opt-in).
- **Poetry contest**: set meta `open3_comments_heading = "Alle 23 Einreichungen"` on the new post for slug `poetry-contest` (see section 4).
- **Bookkeeping**: new post meta `_open3_migrated_from = <old ID>`; old page meta `_open3_migrated_to = <new ID>`, `_open3_legacy_slug = <slug>`.

### 1.4 projekttyp mapping (slug → term), derived from dc.html:458-479

| Live slug (ID) | projekttyp | Prototype line |
| --- | --- | --- |
| regierungsprogramm-2020-2024 (55179) | dokumentation | :458 |
| gruenbuch-projekte (55192) | dokumentation | :459 |
| opendata-buddy (55311) | app | :460 |
| create-camp-gemeinsam-mit-wiener-linien-mit-schwerpunkt-verkehr (37320) | veranstaltung | :461 |
| budgetvisualisierung-2011-2014 (17419) | visualisierung | :462 |
| sparpaket (14991) | visualisierung | (not in prototype; treemap of the same project) |
| einwaende-gegen-die-veroeffentlichung-von-open-data-und-bewaehrte-antworten-darauf (50594) | dokumentation | :463 |
| visualisierung-der-hypo-alpe-adria-banken-staatshilfen (49584) | visualisierung | :464 |
| hochwasserschutz-per-open-data-in-kremsmuenster (50514) | app | :465 |
| wien-budget-2009-visualisierung (17913) | visualisierung | :466 |
| umfrage (8727) | umfrage | :467 |
| woergl-haushalt-1949-2009-visualisierung (20690) | visualisierung | :468 |
| ngo-umfrage (20358) | umfrage | :469 |
| ubahnaufzug-at (18303) | app | :470 |
| datamaps-eu (20868) | app | :471 |
| umfrage-potentiale-von-open-government-fur-osterreichische-nationalratsabgeordnete (23978) | umfrage | :472 |
| toilet-map-vienna (24908) | app | :473 |
| gluecksspiel-opendata-mashup (53713) | app | :474 |
| ozon-info-at-ozon-warnung-am-smartphone (25636) | app | :475 |
| poetry-contest (28824) | veranstaltung | :476 |
| linz-open-data-apps-und-visualisierungen (28759) | app | :477 |
| kremsmuenster-haushalt-2003-2012-visualisierung (33560) | visualisierung | :478 |
| ogd-wien-adressservice-unterstuetzung-fuer-geocoder-php-bibliothek (51554) | code | :479 |

Unknown slug (a 24th page appearing later) → no term, WARN in report.

### 1.5 Featured images: slug → file in `/wp-content/uploads/projekt-explorer/` (from `legacy_theme/projekt-explorer.php`)

| Slug | File (line in projekt-explorer.php) | Note |
| --- | --- | --- |
| opendata-buddy | opendata-buddy.png (:3) | 129×129 square, low-res — flag |
| gluecksspiel-opendata-mashup | gluecksspiel-projekt.png (:6) | 230×129 |
| hochwasserschutz-per-open-data-in-kremsmuenster | pegelstand-kremsmuenster.jpg (:9) | only .jpg |
| create-camp-gemeinsam-mit-wiener-linien-mit-schwerpunkt-verkehr | wlcc.png (:12) | |
| budgetvisualisierung-2011-2014 | sparpaket11-14.png (:15) | |
| sparpaket | sparpaket11-14.png (reuse, flagged `reused`) | same visual project |
| toilet-map-vienna | toiletmap.png (:18) | |
| umfrage-potentiale-von-open-government-fur-osterreichische-nationalratsabgeordnete | umfrage-parlament.png (:21) | |
| datamaps-eu | datamapseu.png (:24) | |
| ubahnaufzug-at | ubahnaufzug.png (:27) | |
| ngo-umfrage | umfrage-ngos.png (:30) | |
| linz-open-data-apps-und-visualisierungen | fz-wlan-linz.png (:33) | legacy link used old slug `linz-open-data` |
| woergl-haushalt-1949-2009-visualisierung | woergl.png (:36) | |
| umfrage | umfrage-datenarten.png (:39) | |
| wien-budget-2009-visualisierung | budget-wien.png (:42) | |
| poetry-contest | poetry-contest.png (:45) | |
| ozon-info-at-ozon-warnung-am-smartphone | ozoninfo.png (:48) | |

**No image (report as `MISSING_IMAGE`, 6 + sparpaket if reuse is declined = 7):** regierungsprogramm-2020-2024, gruenbuch-projekte, einwaende-…, visualisierung-der-hypo-alpe-adria-…, kremsmuenster-haushalt-2003-2012-visualisierung, ogd-wien-adressservice-…-geocoder-php-bibliothek.

Import mechanism, `--images=local|remote|skip`:
- `local` (production): `$path = WP_CONTENT_DIR . '/uploads/projekt-explorer/' . $file`; copy into a temp file and `media_handle_sideload(['name'=>$file,'tmp_name'=>$tmp], $new_id)` → gets normal `YYYY/MM` upload path + sizes; `set_post_thumbnail`. Never move the originals (legacy links may still hotlink them).
- `remote` (Playground): `download_url($image_base . $file)` then same sideload; `--image-base` default `https://www.open3.at/wp-content/uploads/projekt-explorer/`. Requires blueprint `features.networking: true`.
- Idempotent: attachment meta `_open3_source_file = <file>`; reuse existing attachment ID if found (so `sparpaket` and `budgetvisualisierung-2011-2014` share one attachment).
- Note for the template side: 222×129 is 1.72:1, close to 16:9; `object-fit: cover` in the `.grayscale` wrapper is enough; the 129×129 square will crop.

### 1.6 Retiring the old pages, and page 17418 vs the CPT archive

`--retire=draft|trash|keep` (default `draft`):
- `draft`: `wp_update_post(['ID'=>$old,'post_status'=>'draft','post_name'=>$slug.'-legacy'])`. Frees the slug in the page namespace (belt and braces; WP does not check cross-type slugs anyway), keeps content restorable, and the old slug is recorded in `_open3_legacy_slug` for rollback.
- `trash`: `wp_trash_post()` — WP itself appends `__trashed` to `post_name`; auto-purged after `EMPTY_TRASH_DAYS` (30). Not default: rollback becomes time-boxed.
- `keep`: leave published (for dry-run comparisons only).
- Parent 17418 `projekte`: also retire (draft, `projekte-legacy`). Reason: the CPT archive rule `projekte/?$` (from `has_archive => 'projekte'`) is inserted *before* the verbose page rules so the archive wins regardless, but leaving a published page with the same path confuses editors and `redirect_canonical`. Its content ("Bisherige Projekte … Idee für ein neues Projekt? … office@open3.at", verified via REST) is carried over as the default of a theme option `open3_projekte_intro` rendered on `archive-projekt.php`.
- Rollback = `wp open3 migrate-projects --rollback`: for every `_open3_migrated_to` pair: move comments back, restore `post_status=publish` + `_open3_legacy_slug`, delete the projekt post (keep attachments), flush. Dry-run supported.

### 1.7 Other flags

- `--dry-run`: full report (slug | action create/update/skip | type | image | warnings) without writes; exits non-zero if any ERROR.
- `--force`: re-sync title/content/excerpt/menu_order/terms onto an already migrated projekt (matched via `_open3_migrated_from`). Default without `--force` = `skip` existing → idempotent.
- `--reset-templates`: `delete_post_meta` `_wp_page_template` on **any** page whose value is one of `woergl-visualisierungen.php, poetry-slam.php, sparpaket.php, wienbudget2009.php, delicious.php, open3.php` (C-8). Which non-project pages carry `delicious.php`/`open3.php` is **[not in handoff]** — the full-page REST fetch timed out today; re-run `curl "https://www.open3.at/wp-json/wp/v2/pages?per_page=100&_fields=id,slug,template"`.
- `--menus`: creates nav menus by name if absent ("Hauptnavigation": Blog→home, Projekte→`post_type_archive` projekt, Grundlagen 17841, openEvents 6992, openNetwork 17850, Medienspiegel 21441, Über open3 19121; "Footer": same + Newsletter 55348) and assigns locations `primary`/`footer` via `set_theme_mod('nav_menu_locations')`. Page IDs resolved by slug (`get_page_by_path`) so the same code works in Playground.
- Always ends with `flush_rewrite_rules()` (and `clean_post_cache`).

### 1.8 Execution paths

- Production with WP-CLI: `wp open3 migrate-projects --dry-run` → `wp open3 migrate-projects --images=local --menus --reset-templates`.
- Production without WP-CLI: Tools → open3 Migration (same options as checkboxes).
- Before theme activation (legacy still active): `wp eval-file wp-content/themes/open3-v2/bin/migrate-projects.php --dry-run` (the entry file self-registers the CPT).
- Playground: blueprint `runPHP` step (section 6).

## 2. URL preservation

| URL(s) | Mechanism |
| --- | --- |
| `/projekte/{slug}` × 23 (list in 1.4) | CPT rewrite: `register_post_type('projekt', ['rewrite'=>['slug'=>'projekte','with_front'=>false,'feeds'=>false], 'has_archive'=>'projekte', 'hierarchical'=>false, 'publicly_queryable'=>true, 'show_in_rest'=>true, 'supports'=>['title','editor','excerpt','thumbnail','comments','author','revisions','page-attributes']])`. `page-attributes` keeps `menu_order` editable (archive default order: `menu_order ASC, date DESC`). |
| `/projekte` | `has_archive => 'projekte'` + `archive-projekt.php`. |
| `/projekte/linz-open-data` (legacy explorer link, projekt-explorer.php:32) | `_wp_old_slug` copied (1.3) → core `wp_old_slug_redirect`. Plus explicit entry in the theme map below. |
| `/sparpaket` (og:url in legacy header.php:51) | Theme map → 301 `/projekte/sparpaket`. Core's 404 guess would still find the CPT post, but do not rely on it (`strict_redirect_guess_404_permalink` may be filtered by plugins). |
| `/gluecksspiel-karte`, `/gluecksspiel`, `/ogdwien-finanzdaten` | Pretty Links (live, untouched). Verify only. |
| `/2023/11/opendata-buddy`, `/2013/10/erstmals-…`, all 226 `/%year%/%monthnum%/%postname%` posts | Untouched (posts). Verify permalink option unchanged. |
| `/kategorie/{news,termine,studien,medienspiegel,mehrgrips}` (+ `/feed`) | `category_base = kategorie` unchanged; no theme involvement. |
| `/feed`, `/comments/feed`, `/feed/rss2`, `/kategorie/*/feed`, FeedBurner `http://feeds.feedburner.com/open3at-OpenStream` (header.php:8) | Core; `add_theme_support('automatic-feed-links')`; keep the FeedBurner `<link rel="alternate">` only if the client confirms FeedBurner still syncs **[not in handoff]** — otherwise point the sidebar/footer "RSS-Feed" at `get_feed_link()`. |
| `/author/robert` etc., date archives, `/?s=`, `/suche`, `/newsletter`, 10 top-level pages, 21 Grundlagen children | Core; pages unaffected. |
| `/projekte/{slug}/feed` | Disabled via `feeds=>false` (no comment feeds for projects; the legacy `post_comments_feed_link` in comments.php:88 is dropped). |

Theme-side map `inc/redirects.php` (chosen over Redirection rules so that it ships with the theme and is version-controlled; Redirection stays for editorial redirects):

```php
add_action('template_redirect', function () {
    if (!is_404()) return;                       // never override real content
    $map = [
        '/sparpaket'            => '/projekte/sparpaket',
        '/projekte/linz-open-data' => '/projekte/linz-open-data-apps-und-visualisierungen',
    ];
    $path = untrailingslashit(wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (isset($map[$path])) { wp_safe_redirect(home_url($map[$path]), 301); exit; }
}, 1);
```

Flush: `after_switch_theme` → `open3_register_projekt(); flush_rewrite_rules();` and `switch_theme` → `flush_rewrite_rules()`. The migrator also flushes. On production additionally do Settings → Permalinks → Save once after activation (covers the case where the theme was activated by renaming directories, where `after_switch_theme` does not fire).

Verification script `dev/check-urls.sh <base>` (curl status + `Location` for: the 23 project URLs, `/projekte`, `/projekte/page/2` (if >N per page), `/sparpaket`, `/projekte/linz-open-data`, 5 category URLs, `/feed`, `/kategorie/news/feed`, `/`, `/page/2`, `/page/46`, `/suche`, `/impressum`, `/gluecksspiel-karte`, `/2023/11/opendata-buddy`, a nonsense URL → expect 404). Expected: 200 everywhere except the two 301s and the 404 probe.

## 3. Treemap templates (Stage 2)

### 3.1 Assets to fetch (not in repo) → `open3/assets/js/treemap/`

| Download URL (all HEAD 200 today) | Target file |
| --- | --- |
| `https://www.open3.at/wp-content/uploads/js-treemap/jit-treemap-c.js` | `jit-treemap-c.js` (BSD-3; keep header; add `LICENSES/JIT-BSD-3-Clause.txt`) |
| `https://www.open3.at/wp-content/uploads/js-treemap/budgetkuerzungen-2011-2014-v3.1.js` | `sparpaket-kuerzungen.js` (defines `init()`, targets `#infovis`/`#log`/`#back`) |
| `https://www.open3.at/wp-content/uploads/js-treemap/gesamthaushalt-2011-2014-v1.0.js` | `sparpaket-gesamthaushalt.js` (defines `init2()`, targets `#infovis2`/`#log2`/`#back2`) |
| `https://www.open3.at/wp-content/uploads/js-wienbudget/wien-budget2009-ausgaben.js` | `wienbudget2009.js` (defines `init()`) |
| `excanvas.js` | **not** copied (IE<9 shim) |

Data files are CC-BY content by open3 itself (page 17913 content states CC BY 3.0 AT) — no licensing issue. **Must inspect after download** (not possible now): (a) which DOM ids each `init` touches (`getElementById('infovis'|'log'|'back'|'r-sq'…)`), (b) whether `TM.Squarified({... width: 950, height: 600 ...})` hardcodes dimensions — if so, patch to `width: document.getElementById('infovis').offsetWidth` so the fluid CSS works, and re-init on `resize` (debounced) or accept fixed height 600 + fluid width. Keep patches minimal and mark with `/* open3 patch */`.

### 3.2 Templates and enqueue

- `single-projekt-sparpaket.php`, `single-projekt-wien-budget-2009-visualisierung.php` (WP ≥4.4 hierarchy `single-{post_type}-{slug}.php`). Both call `get_header()`, render the Projekt-Detail chrome (title, actions, Steckbrief) and the treemap block instead of the hero image, then `the_content()`, `comments_template()` guard, `get_footer()`.
- Enqueue in `inc/enqueue.php`: `if (is_singular('projekt') && in_array(get_post_field('post_name'), ['sparpaket','wien-budget-2009-visualisierung'], true))` → `wp_enqueue_style('open3-treemap', .../assets/css/treemap.css)`, `wp_enqueue_script('open3-jit', …, [], $ver, true)`, then the data file(s) with dependency `open3-jit`, then `wp_add_inline_script($handle, 'document.addEventListener("DOMContentLoaded",function(){init();init2();});')` (Sparpaket) / `…{init();}…` (Wien). Scripts in footer (`in_footer=true`) so the DOM exists; no `body onload` (legacy header.php:99).
- DOM contract per page (from sparpaket.php:40-80, wienbudget2009.php:40-56), Sparpaket de-duplicated:
  - Block A (Gesamthaushalt, `init2`): `<h2 id="hoch2">`, `<div id="id-list-2">` with radios `name="layout2"` ids `r2-sq|r2-st|r2-sd` values `left|top|bottom` (checked `left`), `<a id="back2" href="#hoch2" class="btn btn-secondary">Eine Ebene höher anzeigen</a>`, `<div id="container-2"><div id="center-container-2"><div id="infovis2"></div></div><div id="log2"></div></div>`, `<div id="container-bottom-2">` links.
  - Block B (Kürzungen, `init`): `<h2 id="hoch">`, `<div id="id-list">` radios `name="layout"` ids `r-sq|r-st|r-sd`, `<a id="back" href="#hoch">`, `<div id="container"><div id="center-container"><div id="infovis"></div></div><div id="log"></div></div>`.
  - Because the second data file (`init2`) currently binds to the *first* radios (duplicate ids), the patched copy must query `layout2`/`r2-*` — that's the one intended JS edit besides sizing.
  - Wien 2009: single block identical to Block B.
- Copy kept in template (escaped, https): datenkatalog links (sparpaket.php:56, :82; wienbudget2009.php:56), downloads `gesamthaushalt-2011-2014-v1.1.png/.xls`, `treemap-screenshot-v3.png`, `wien-budget2009-ausgaben.png`; intro sentences (sparpaket.php:30, :64; wienbudget2009.php:32 — `/ogdwien-finanzdaten` link keeps working via Pretty Links).
- **moneypile**: `moneypile.php` is 403 → dead. Render `moneypile.png` (885×407, still 200) as a plain `<img>` under "Alternative Darstellung" without the link; tell client.
- CSS `assets/css/treemap.css`, ported from legacy style.css:1418-1593 with: `#container, #container-2 {width:100%; max-width:950px; margin:0 auto; position:relative}`, `#center-container* {position:static; width:100%; background:#1a1a1a; color:#ccc}`, `#infovis, #infovis2 {position:relative; width:100%; height:600px; overflow:hidden}`, keep `.node {color:#fff; font-size:9px; overflow:hidden; cursor:pointer}`, `.tip {color:#fff; text-align:center; width:200px; background:#000; opacity:.9; font-size:10px; padding:7px}`, `#log, #log2 {position:absolute; top:10px; font-weight:bold; color:#23A4FF}`. Drop `.button/.white` gradients (style.css:1551-1593) and `#left-container/#right-container` (never used, width 0).
- Woergl (20690) and Kremsmuenster (33560): **confirmed plain content** — `woergl-visualisierungen.php` has no visualisation markup or scripts (Google-Docs embeds are in `post_content`). They use the default `single-projekt.php`; migration drops `_wp_page_template`. `poetry-slam.php` is a byte-identical duplicate (verified `diff`) and is not assigned to any page.
- Legacy per-page OG meta (header.php:13-62) is dropped; no SEO plugin was observed live **[not in handoff whether one exists]** — flag for client; Stage 1 may emit minimal `og:title/og:url/og:image` from the featured image.

## 4. Poetry Contest (28824)

Minimal safe option: migrate as `projekt` (type `veranstaltung`), **keep comments closed**, move the 23 comments (1.3), show them as a read-only list titled by post meta `open3_comments_heading` ("Alle 23 Einreichungen"; fallback "Kommentare"). `single-projekt.php` calls `comments_template()` when `comments_open() || get_comments_number()`; since `comments_open()` is false, `comment_form()` renders nothing, so none of the legacy branches (comments.php:34-38 "Beitrag einreichen", :58 "Youtube-Link", :62 "Maximal 2011 Zeichen", :126-127 "Bisherige Einreichungen") are needed. Drop the global `maxlength="2011"` (comments.php:63). If the client ever reopens submissions, add a `comment_form_defaults` filter keyed on that post — not now. Trackback list (comments.php:103-116) is dropped site-wide (`ping_status` closed is the modern default; pingbacks still display through `wp_list_comments` if any exist).

## 5. Legacy DB content to export before switching

| Item | How to fetch (pick what the host allows) | Lands in |
| --- | --- | --- |
| `art_footer_content` option (footer.php:7) | `wp option get art_footer_content`; or Artisteer "Theme Options" admin page (functions.php:205 `art_add_admin`) copy the HTML textarea; or phpMyAdmin `wp_options`. Not exposed via REST. | Archive only (`docs/legacy-export/footer.html`). Canonical new wording = README.md:51; footer is hardcoded in `footer.php`. |
| Sidebar 1 widgets (`sidebars_widgets`, `widget_*` options) | `wp option get sidebars_widgets`, `wp widget list sidebar-1 --format=json`; or authenticated REST `GET /wp-json/wp/v2/sidebars/sidebar-1` + `/wp/v2/widgets?sidebar=sidebar-1` with an application password; or screenshots of Appearance → Widgets. | Reference only. The design sidebar is fixed (Suche, Explorer, Neu im Blog, Kategorien, Newsletter); **no dynamic sidebar in Stage 1**. If a widget contains content the client wants (e.g. Twitter/Delicious boxes are to be removed per Analyse.md:24), decide per item. |
| Page-template assignments | Public REST: `curl "https://www.open3.at/wp-json/wp/v2/pages?per_page=100&_fields=id,slug,template"` (re-run; timed out today). Known: 14991, 17913, 20690, 33560. | `--reset-templates` list (1.7). |
| Menu | None to export (legacy uses `wp_list_pages` exclude=14991,53799, functions.php:398). | `--menus` creates them. |
| Redirection plugin rules | Tools → Redirection → Import/Export → JSON. **[not in handoff]** which rules exist. | Keep plugin; archive JSON in `docs/legacy-export/`. |
| Site options to confirm | `wp option get blogdescription posts_per_page permalink_structure category_base date_format timezone_string` or `curl https://www.open3.at/wp-json/?_fields=name,description` (timed out today). | Blueprint values (section 6). README.md:26 tagline text must equal `blogdescription`; otherwise the hero shows the DB value. |
| Newsletter plugin form config | Already known: `admin-ajax.php?action=tnp&na=s`, fields `ne`, `nn`, `nlang`. | `sidebar` newsletter box POSTs there. |
| `google-site-verification` token (header.php:12), FeedBurner URL (header.php:8), Piwik idsite 3 at mapsmarker.com (footer.php:28-36) | From legacy files. | Token: keep via Customizer field or `wp_head` echo; Piwik: **drop** (client decision on analytics). |

## 6. Local verification with WordPress Playground CLI

### 6.1 Commands (Windows / PowerShell; Git Bash equivalents identical except quoting)

```
npx @wp-playground/cli@latest server `
  --php=8.4 --wp=latest --port=9400 --login `
  --mount-dir "C:/Users/rober/Documents/Git Repos/open3-theme/open3" "/wordpress/wp-content/themes/open3" `
  --blueprint "C:/Users/rober/Documents/Git Repos/open3-theme/dev/blueprint.json"
```

- Use `--mount-dir "<host>" "<vfs>"` (two-argument form) rather than `--mount=<host>:<vfs>`: the single-argument form splits on `:` and a Windows path starting with `C:` is ambiguous. **Verify at runtime**; if `--mount-dir` is rejected by the installed version, fall back to `--mount="/c/Users/.../open3:/wordpress/wp-content/themes/open3"` under Git Bash.
- `server` (not `start`) so each run is a clean, reproducible site; mounts are applied before blueprint steps, so `activateTheme` can reference the mounted `open3`. Add `--debug` on boot failures.
- `--blueprint-may-read-adjacent-files` is only needed if the blueprint uses `bundled` resources; the plan avoids that by keeping the seed script inside the mounted theme (`open3/dev/seed.php`, excluded from dist).
- `npx @wp-playground/cli@latest run-blueprint --blueprint dev/blueprint.json` for headless CI-style checks (no server) — useful only with `runPHP` assertions.

### 6.2 `dev/blueprint.json` (v1 schema; property names verified against the schema)

```json
{
  "$schema": "https://playground.wordpress.net/blueprint-schema.json",
  "landingPage": "/",
  "preferredVersions": { "php": "8.4", "wp": "latest" },
  "features": { "networking": true },
  "login": true,
  "steps": [
    { "step": "setSiteLanguage", "language": "de_AT" },
    { "step": "setSiteOptions", "options": {
        "blogname": "open3.at",
        "blogdescription": "Netzwerk zur Förderung von OpenSociety, OpenGov und OpenData in Österreich",
        "posts_per_page": "5",
        "permalink_structure": "/%year%/%monthnum%/%postname%",
        "category_base": "kategorie",
        "timezone_string": "Europe/Vienna",
        "date_format": "j. F Y",
        "show_on_front": "posts",
        "default_comment_status": "open",
        "comment_moderation": "1"
    } },
    { "step": "installPlugin", "pluginData": { "resource": "wordpress.org/plugins", "slug": "newsletter" }, "options": { "activate": true } },
    { "step": "installPlugin", "pluginData": { "resource": "wordpress.org/plugins", "slug": "kadence-blocks" }, "options": { "activate": true } },
    { "step": "activateTheme", "themeFolderName": "open3" },
    { "step": "runPHP", "code": "<?php require '/wordpress/wp-load.php'; require '/wordpress/wp-content/themes/open3/dev/seed.php'; open3_seed_fixtures(); flush_rewrite_rules();" }
  ]
}
```

- `de_AT` exists as a WordPress locale (language pack download needs networking); fall back to `de_DE` if the pack fails.
- wordpress.org slugs verified: `newsletter`, `kadence-blocks`, `redirection`, `wp-paginate`, `simple-tags` (200); `ajax-search-pro` is commercial (404) — not installable; note that its live JS may restyle `form[role=search]` — verify on production only.
- `siteOptions` values are strings; permalink change requires the `flush_rewrite_rules()` at the end.

### 6.3 `open3/dev/seed.php` (fixtures, idempotent by slug)

- Categories news/termine/studien/medienspiegel/mehrgrips (names as live, IDs irrelevant).
- 12 posts: the 5 from dc.html:443-447 (titles/excerpt/dates) + 3 from :450-452 + 4 more German titles spread across Termine/Studien/Medienspiegel, dates 2023-2025, author "Robert Seyfriedsberger"; plus **50 filler posts** ("Beitrag 13 … 62") so pagination reaches ≥10 pages (page 2 and ellipsis rendering); mark filler with meta `_open3_fixture`.
- Pages: 10 top-level (slugs from live facts, `newsletter` content = `[newsletter_form]`), `projekte` parent **not** created as a page (the CPT owns `/projekte`), 21 Grundlagen children with the live `menu_order` values.
- 23 `projekt` posts: titles from dc.html:458-479 (+ "Bundesfinanzrahmen 2011-2014 … (Sparpaket)"), slugs from 1.4, terms from 1.4, `menu_order` from live, dates from live. Images: try `download_url()` of the 16 live files (networking); on failure generate 640×360 grey PNGs with GD (`imagecreatetruecolor`; Playground's PHP has GD — verify; else skip and log).
- Menus via the migrator's `--menus` code path (reuse, do not duplicate).
- 23 comments on `poetry-contest` (short German placeholders) to exercise the read-only list.
- Optionally run `Open3_Project_Migrator` itself in Playground: create the 23 legacy **pages** under a `projekte` page first (`--seed-legacy-pages`), then `runPHP` the migrator with `images=remote` — this is the only way to test the real migration end-to-end locally. Recommended as a second blueprint `dev/blueprint-migration.json`.

### 6.4 Checks

| Check | Command / method |
| --- | --- |
| PHP lint | PowerShell: `Get-ChildItem -Recurse -Filter *.php open3 \| % { & "C:\wamp64\bin\php\php8.4.15\php.exe" -l $_.FullName }`; Git Bash: `find open3 -name '*.php' -print0 \| xargs -0 -n1 "C:/wamp64/bin/php/php8.4.15/php.exe" -l`. Fail on any "Errors parsing". |
| URL matrix | `dev/check-urls.sh http://127.0.0.1:9400` (section 2). |
| Screenshots 1280 / 390 | `npx playwright install chromium` once; `npx playwright screenshot --viewport-size=1280,900 --full-page http://127.0.0.1:9400/ dev/shots/home-1280.png`; same with `--viewport-size=390,844`. Pages: `/`, `/page/2`, one single post, `/kategorie/news`, `/?s=daten`, `/nicht-da` (404), `/projekte`, `/projekte/sparpaket` (treemap, Stage 2), `/projekte/poetry-contest`, `/grundlagen`, `/uber-open3`, `/openevents`. Compare against the prototype at the same widths (open dc.html in a browser). |
| HTML validation | Nu API without Java: `curl -s http://127.0.0.1:9400/ -o dev/shots/home.html && curl -s -H "Content-Type: text/html; charset=utf-8" --data-binary @dev/shots/home.html "https://validator.w3.org/nu/?out=json"`. Expect: no duplicate ids (Sparpaket), `lang="de-AT"`, one `<main>`, `<h1>` once. Alternative offline: `npx html-validate dev/shots/*.html`. |
| Accessibility | `npx @axe-core/cli http://127.0.0.1:9400/ --tags wcag2a,wcag2aa` (needs Chrome). Manual: Tab from page top → first focus = "Zur Navigation springen" (visible on focus), second = "Zum Inhalt springen"; targets `#skiplink-navigation` on `<nav>`, `#skiplink-inhalt` on `<main>` (M-10); at 390px: Tab to "Menü", Enter opens (`aria-expanded="true"`), Escape closes and returns focus. Contrast list: `#7d7979` on `#f3f2f2` = 3.4:1 (ellipsis/empty state — M-12 decision), `#605d5d` on `#f3f2f2` ≈ 5.6:1 OK, `#f3f2f2` on `#ec3013` ≈ 4.0:1 (large/bold text only — buttons at 13-14px 800 weight are borderline; flag). |
| Pagination | `/page/2` shows "← Neuere" and "Ältere →", current page filled; `/page/46` in production. |
| Explorer JS | Click "Nächstes Projekt" 24 times → cycles through all 23 and wraps; no network request in DevTools (server-side JSON island, C-10). |
| Newsletter form | Submitting in Playground posts to `admin-ajax.php?action=tnp&na=s` and the Newsletter plugin answers (requires plugin active in blueprint). |
| Treemap (Stage 2) | `/projekte/sparpaket`: both treemaps render, radios switch layout independently, "Eine Ebene höher anzeigen" works per block, no console errors, resize to 390px keeps it inside the viewport. |
| Migration | Run `dev/blueprint-migration.json`; assert in `runPHP`: 23 `projekt` posts, slugs unchanged, 16(+1) thumbnails, 23 comments on poetry-contest, old pages draft with `-legacy` slugs, `/projekte/linz-open-data` → 301. |

## 7. Rollout

**Directory name**: deploy as `open3-v2` on production, keep the live `open3` untouched until sign-off. Rationale: rollback is then a one-click theme switch with no file operations; renaming the active theme's folder makes WP fall back to the default theme mid-request (`validate_current_theme`), and activating by rename skips `after_switch_theme` (no rewrite flush). The repo folder stays `open3/`; the packaging script emits `open3-v2/`. If the client insists on the `open3` name later: rename folders in one step and `wp option update template open3 && wp option update stylesheet open3`, then Permalinks → Save.

**Order of operations on production**

1. Full backup (DB dump + `wp-content/`), note WP/PHP versions (**[not in handoff]**; theme requires PHP ≥8.1, tested 8.4).
2. Exports from section 5 (footer option, widgets, page templates, Redirection JSON, site options).
3. Upload `open3-v2` (zip via Appearance → Themes → Upload, or SFTP).
4. Dry-run migration while legacy is still active: `wp eval-file wp-content/themes/open3-v2/bin/migrate-projects.php --dry-run` (or skip if no CLI; the admin page exists only after activation).
5. Activate `open3-v2` (CPT registered, rewrite flushed). Short window where `/projekte/*` pages still resolve as pages (CPT rule takes precedence only for existing projekt posts — page fallback still serves the old pages, so no 404s in between).
6. Run migration for real (`--images=local --menus --reset-templates`), read the report; fix any `ERROR` rows.
7. Appearance → Menus: confirm "Hauptnavigation"/"Footer" assigned; Settings → Permalinks → Save.
8. `dev/check-urls.sh https://www.open3.at`; spot-check the 4 special project pages, Poetry list, newsletter submit, search, feed in a reader.
9. Plugins: deactivate **WP-Paginate** (theme uses `paginate_links()`); keep Newsletter, Ajax Search Pro (verify it does not hijack the sidebar form), TaxoPress, Meow Lightbox, Maps Marker Pro, Kadence Blocks, Redirection, Pretty Links, Limit Login Attempts. Remove Facebook SDK remnants (theme no longer outputs them).
10. After 14 days without rollback: delete the legacy `open3` theme, delete `-legacy` draft pages (`wp post delete $(wp post list --post_type=page --post_status=draft --meta_key=_open3_migrated_to --format=ids)`), optionally `--retire=trash` semantics.

**Rollback**: activate `open3`, run `--rollback` (restores pages, moves comments back), Permalinks → Save, re-activate WP-Paginate.

**Open with the client (not decidable from the handoff)**
- SVG logo (README.md:77); favicon redo.
- 6–7 project images missing (list in 1.5); `opendata-buddy.png` is 129×129 (blurry at 16:9 card size); whether `sparpaket` may reuse `sparpaket11-14.png`; whether `sparpaket` and `budgetvisualisierung-2011-2014` should be merged.
- Newsletter box: include the first-name field `nn` or e-mail only (design shows e-mail only).
- Analytics: Piwik on `mapsmarker.com` idsite 3 (footer.php:28-36) — drop, or self-hosted Matomo with consent.
- English posts: category "English" vs language switch (Analyse.md:79).
- Content for the Steckbrief fields (Jahr/Datenquelle/Technik/Lizenz/App-Link/Datensatz-Link) of all 23 projects — editorial work after migration (script leaves them empty).
- `moneypile.php` is dead (403) → image only.
- Poetry Contest: keep closed (recommended) or reopen submissions.
- FeedBurner feed still in use? `google-site-verification` still needed?
- Hosting access: SSH/WP-CLI availability, PHP version, ability to rename theme folders.
- SEO/OG: no SEO plugin visible live; decide plugin vs minimal theme OG tags.
- Redirection rule set (export) and whether any rule targets `/projekte/*`.

## 8. Repo hygiene

- Layout: `open3/` (theme), `design_handoff_open3_startseite/` (committed as reference, never shipped), `dev/` at repo root for blueprints/scripts that are not theme files (`blueprint.json`, `blueprint-migration.json`, `check-urls.sh`, `shots/` ignored), `docs/legacy-export/` (gitignored — may contain live data), `open3/dev/seed.php` (inside the mount so Playground can `require` it; excluded from dist).
- `.gitignore`: `node_modules/`, `dev/shots/`, `docs/legacy-export/`, `dist/`, `*.zip`, `.DS_Store`, `Thumbs.db`, `.vscode/`, `.idea/`, `*.log`, `.wordpress-playground/`.
- Packaging without a build step: `.gitattributes` with `design_handoff_open3_startseite/ export-ignore`, `dev/ export-ignore`, `open3/dev/ export-ignore`, `docs/ export-ignore`, `.gitattributes export-ignore`, `.gitignore export-ignore`; then `git archive --format=zip --prefix=open3-v2/ -o dist/open3-v2.zip HEAD:open3` (`dev/build-zip.sh`). A `.distignore` is only needed if `wp dist-archive` is used; keep one mirroring the export-ignore list (`design_handoff_open3_startseite`, `dev`, `docs`, `.git*`, `node_modules`, `*.zip`, `README.md`) for that case.
- No npm build: plain CSS (`assets/css/design-system.css`, `theme.css`, `treemap.css`), plain JS (`assets/js/theme.js`, `explorer.js`, `treemap/*`), fonts as `assets/fonts/archivo-400.woff2`, `archivo-800.woff2` (OFL; source files **[not in handoff]** — take from the Archivo GitHub release or Google Fonts API, subset latin + latin-ext for ä/ö/ü/ß). Versioning via `filemtime()` in `wp_enqueue_*`.
- Licences: theme `GPL-2.0-or-later` in `style.css` header; `open3/LICENSES/` with JIT BSD-3 and Archivo OFL notices.
- Initial commits (executed only when the user asks): (1) `chore: repo skeleton` — `.gitignore`, `.gitattributes`, `.distignore`, `README.md`, `design_handoff_open3_startseite/`; (2) `feat(theme): Stage 1 scaffold` — `style.css` header, `theme.json`, `functions.php`, `inc/`, `assets/`; (3) `feat(migration): projekt CPT, migrator, CLI, admin page, redirects`; (4) `chore(dev): Playground blueprint, seed, url checks`. Author from git config: Robert Seyfriedsberger <robert@seyfriedsberger.net>; trailer `Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>`.

## Risks worth naming

1. `--mount` colon parsing on Windows (mitigated by `--mount-dir`; verify first run).
2. JIT data files may hardcode `width: 950` → treemap not fluid until patched; cannot be confirmed before download.
3. Attachment slug collisions producing `-2` project slugs (detected by the migrator).
4. `de_AT` language pack availability in Playground (fallback `de_DE`).
5. Ajax Search Pro on production may replace the sidebar search form markup — only testable live.
6. Migration on a host without WP-CLI depends on the admin one-shot page and PHP `max_execution_time` (16 sideloads + 23 inserts — well under 30 s, but run image import as a second pass if it times out: `--images=skip` then `--images=local --force`).

### Critical Files for Implementation
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\inc\migration\class-open3-project-migrator.php (to create — all migration logic, rollback, reports)
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\inc\cpt-projekt.php (to create — CPT/taxonomy/meta registration; rewrite slug `projekte`, `has_archive`)
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\inc\redirects.php (to create — 404-only redirect map + activation flush hooks)
- C:\Users\rober\Documents\Git Repos\open3-theme\dev\blueprint.json (to create — Playground verification environment)
- C:\Users\rober\Documents\Git Repos\open3-theme\design_handoff_open3_startseite\legacy_theme\projekt-explorer.php (source of the slug→image map and legacy URLs)