# Implementation plan — WordPress architecture & correctness layer (open3 theme)

Scope: PHP/structure layer for `C:\Users\rober\Documents\Git Repos\open3-theme\open3\` (directory does not exist yet; repo has an unborn `main`, remote `origin` = github.com/seyfro/open3-theme). Toolchain verified: PHP 8.4.15 CLI at `C:\wamp64\bin\php\php8.4.15\php.exe`, Node 26.4.0, npx 11.17.0.

Findings from this pass that change the plan (verified against live REST API on 2026-09-08):

- F-1 `sparpaket` (14991, template sparpaket.php, menu_order 0, title "Sparpaket") and `budgetvisualisierung-2011-2014` (17419, no template, menu_order 3, **empty content**) are two live child pages for ONE design project. 14991 carries the treemap + copy; 17419 is an empty stub. Consequence: migrate both as `projekt`, hide 14991 from grid/explorer with a boolean meta (see 3.4) and set 17419's App-Link to `/projekte/sparpaket/`.
- F-2 Wörgl (20690) and Kremsmünster (33560) contain only commented-out Google-Gadget `<script>`s plus the note "leider hat Google die Google Gadgets eingestellt" — nothing to embed. Their `single-projekt-{slug}.php` files stay thin delegates (no scripts, no full-width).
- F-3 Treemap data files: `budgetkuerzungen-2011-2014-v3.1.js` defines global `init()`, writes to `#log`, inline JSON; `gesamthaushalt-2011-2014-v1.0.js` defines `init2()`, `injectInto:'infovis2'`, `$jit.id('r-sq'|'r-st'|'r-sd'|'back2')`, `#log2`, and hardcodes the image prefix `http://www.open3.at/wp-content/uploads/js-treemap/` (plain http → mixed content). Both bind the SAME radio ids, so de-duplicating the Sparpaket ids requires patching the re-hosted `gesamthaushalt` file (3.6). `wien-budget2009-ausgaben.js` defines `init()`, `#log`, inline JSON (~200 KB). `jit-treemap-c.js` = JIT 2.0.0b (BSD), references `G_vmlCanvasManager` only behind a canvas-support check → excanvas can be dropped safely.
- F-4 `/2023/11/opendata-buddy` (post 55303) and `/2013/10/erstmals-in-oesterreich-hochwasserschutz-per-open-data` (post 50510) are real blog posts — do NOT redirect them. `gluecksspiel-karte`, `ogdwien-finanzdaten`, `linz-open-data` do not exist as pages (REST returns `[]`) — they are already 404/plugin-redirected; the theme redirect map below only fires on `is_404()`, so it can never hijack a real post.

## 0. Decisions (each with the one reason)

| # | Decision | Reason |
|---|---|---|
| D-1 | Sidebar = hard-coded template parts, **no** `register_sidebar()` | All five blocks are fixed by the design and query-driven; a widget area would need five custom widgets to reproduce identical markup and would let editors break the layout. |
| D-2 | Archivo self-hosted via `theme.json` `fontFace` (WP 6.4 prints `@font-face` in `wp_head` and the editor) — no `fonts.css` | One source of truth for front-end + editor, zero extra requests. |
| D-3 | `theme.json` = `settings` only (palette, fonts, spacing, disable custom colors); **no** `styles` block | Avoids global-styles CSS fighting `design-system.css`; editor parity comes from `editor.css`. |
| D-4 | Projekt meta = PHP `add_meta_box` + `register_post_meta(show_in_rest)` | No JS build step, works in Playground, and REST/JSON island still read the meta. |
| D-5 | Taxonomy `projekttyp` rewrite slug `projekttyp` (not `projekte/typ`) | The CPT's own attachment rule `projekte/[^/]+/([^/]+)/?$` would swallow a nested slug. |
| D-6 | `projekttyp` hierarchical = true (checkbox UI) | Prevents free-text typos; the term set is closed (6 terms). |
| D-7 | Migration converts pages **in place** (`set_post_type()`), keeps IDs | Preserves comments (Poetry Contest's 23 submissions), revisions, attachments, dates, and URLs for free. |
| D-8 | Page 17418 `projekte` → `draft` after migration | The CPT archive rule (`extra_rules_top`) already wins over page rules; drafting removes an orphan that would still answer `?page_id=17418`. |
| D-9 | Projekt filter chips = server-side links (archive + `taxonomy-projekttyp.php`), no JS filter | Crawlable, works without JS, one grid template part for both. |
| D-10 | Explorer = server-rendered item + JSON island of all published visible projects (23) shuffled in PHP; no REST in Stage 1 | Zero round-trips, deterministic cycling as in the prototype; 23 rows is trivially small. |
| D-11 | Redirect map lives in the theme (`inc/redirects.php`, only on `is_404()`) | Version-controlled and testable in Playground; the Redirection plugin can mirror it. |
| D-12 | German source strings, text domain `open3`, ship `languages/open3.pot` only | Monolingual site; German-as-source removes the `.mo` dependency and the de_AT-vs-de_DE catalogue question. Site locale must be **de_AT** so `get_the_date('j. F Y')` yields "17. Jänner 2025" as in the prototype (dc.html:445). |
| D-13 | Native `/feed/` links everywhere; drop the FeedBurner `<link rel=alternate>` | FeedBurner is unmaintained Google infrastructure; `automatic-feed-links` gives correct RSS/comment feeds. |
| D-14 | Remove emoji scripts/styles, `rsd_link`, `wp_shortlink_wp_head`, `wp_generator` | Emoji loads twemoji from s.w.org (external request, DSGVO); the rest is dead XML-RPC/discovery cruft. Keep `wp-embed`. |
| D-15 | Hero only on `is_home() && !is_paged()` | The tagline hero is the Start-A page-1 design; repeating it on 46 pages only pushes the list down. |
| D-16 | Categories block = custom loop over `get_categories(orderby=count)` | Design needs name/count in separate spans; `wp_list_categories` inlines "(147)" and would need regex. |
| D-17 | Custom `Walker_Nav_Menu` subclass that emits bare `<a>` (no `ul/li`) | The design (dc.html:52-56, 412-416) is a flex row/column of anchors; core adds `aria-current="page"` itself since WP 5.3. |
| D-18 | Customizer (not options page) for: sidebar position, hero lede, Projekte-intro texts, contact e-mail, Bluesky URL | Sanitised, previewable, no hand-rolled admin page (legacy `art_admin` was nonce-less). |
| D-19 | Comments: `comment_form()` + honeypot + min-time check in `preprocess_comment`; no Gravatar | Core nonce-less comment posting is normal; honeypot/time are the cheapest server-side Spamschutz and Gravatar is an external request. |
| D-20 | Treemap pages: full width (`no-sidebar` body class) | JIT canvases are 950 px wide; legacy sparpaket.php:9 also forced `width:100%`. |

## 1. File tree of `open3/`

```
open3/
  style.css                       header only (Theme Name: open3, Text Domain: open3, Domain Path: /languages,
                                  Requires at least: 6.4, Tested up to: 6.8, Requires PHP: 8.1, License GPL-2.0-or-later)
  theme.json                      settings only: palette (tokens), Archivo fontFace 400/800, spacingSizes 4/8/12/16/24/32, custom colors off
  screenshot.png                  1200x900 of Start A
  functions.php                   thin loader: constants (OPEN3_VERSION, OPEN3_DIR, OPEN3_URI), require inc/*.php
  index.php                       fallback loop = home without hero
  home.php                        Start A: hero (page 1) + teaser loop + pagination
  single.php                      Beitrag (breadcrumb, meta, reading time, prose, tag row, Bluesky, prev/next, comments)
  page.php                        generic page (also openNetwork, Medienspiegel, Impressum, Datenschutz, Newsletter)
  archive.php                     category/tag/author/date archives = Start A without hero, kicker per context
  search.php                      results header "Suchergebnisse für „%s“ (%d Treffer)" + teaser loop
  404.php                         Start A shell without hero: kicker "Fehler 404", H1, text, search form
  comments.php                    list first (#skiplink-kommentare), pings, form last (#skiplink-kommentarformular)
  searchform.php                  <form role="search" method="get" action="home_url('/')"> label "Suche", .input, .btn-secondary
  header.php                      doctype, wp_head, skip links, <header>: wordmark, <nav id="skiplink-navigation">, Newsletter button, mobile toggle
  footer.php                      <footer>: wordmark+tagline, Sitemap (footer menu → primary fallback + Newsletter), Kontakt, Lizenz; wp_footer
  sidebar.php                     <aside>: get_template_part for search, explorer, recent, categories, newsletter
  archive-projekt.php             Stage 2: Projekte-Übersicht (intro, chips, grid)
  taxonomy-projekttyp.php         Stage 2: same as archive-projekt.php with active chip
  single-projekt.php              Stage 2: Projekt-Detail (breadcrumb, H1, excerpt, actions, hero media, prose, Steckbrief, related)
  single-projekt-sparpaket.php    Stage 2: two treemaps (#infovis2 / #infovis) + moneypile + the_content
  single-projekt-wien-budget-2009-visualisierung.php   Stage 2: one treemap (#infovis) + the_content
  single-projekt-woergl-haushalt-1949-2009-visualisierung.php        Stage 2: 3-line delegate to template-parts/projekt/single
  single-projekt-kremsmuenster-haushalt-2003-2012-visualisierung.php Stage 2: 3-line delegate (see F-2)
  page-grundlagen.php             Stage 2: numbered child-page index (get_pages child_of, menu_order)
  page-openevents.php             Stage 2: upcoming Termine (meta), consent-gated calendar, past-events table
  page-uber-open3.php             Stage 2: 3-cell grid + prose + services + "Auf dem Laufenden bleiben" (page slug uber-open3)
  inc/
    setup.php                     after_setup_theme: supports, image sizes, nav menus, textdomain, content_width
    enqueue.php                   wp_enqueue_scripts: design-system.css, theme.css, main.js (defer), comment-reply, treemap conditionals; editor styles
    cleanup.php                   remove emoji/rsd/shortlink/generator; body_class; excerpt_length/more; archive_title filter
    nav.php                       class Open3_Nav_Walker; fallback callback; nav_menu_css_class (Blog active on posts, Projekte on projekt)
    template-tags.php             open3_reading_time(), open3_post_meta(), open3_pagination(), open3_media(), open3_bluesky_url(), open3_kicker()
    customizer.php                settings: open3_sidebar_position, open3_hero_text, open3_projekte_intro_*, open3_contact_email, open3_bluesky_url
    cpt-projekt.php               register_taxonomy projekttyp, register_post_type projekt, term seeding, pre_get_posts, search post types
    meta-projekt.php              register_post_meta (7 keys) + meta box + save handler (nonce, caps, sanitize)
    explorer.php                  open3_explorer_items() (WP_Query + shuffle) and JSON island renderer
    comments.php                  comment callback, comment_form args builder, honeypot fields + preprocess_comment check, heading swap for poetry-contest
    treemap.php                   slug→handles map, conditional enqueue + inline boot, treemap.css, body class no-sidebar
    redirects.php                 template_redirect on is_404(): legacy path map → 301
    cli.php                       WP_CLI::add_command('open3', Open3_CLI) when defined (migrate-projekte, setup-menus, seed-terms)
  template-parts/
    header/brand.php              wordmark "Open<span>3</span>" + micro-label (custom-logo if set)
    header/nav.php                wp_nav_menu primary via Open3_Nav_Walker + mobile "Newsletter abonnieren" button
    content/hero.php              H1 = bloginfo('description'), lede = Customizer text
    content/teaser.php            <article> meta row, H2, excerpt, actions
    content/pagination.php        "Seite" kicker + paginate_links(type=array)
    content/none.php              empty state + search form
    content/prose.php             the_content in .prose + wp_link_pages
    sidebar/search.php            get_search_form()
    sidebar/explorer.php          explorer markup + JSON island
    sidebar/recent.php            8 newest posts, "m/y" column
    sidebar/categories.php        custom category loop
    sidebar/newsletter.php        form → admin-ajax.php?action=tnp&na=s (graceful fallback link)
    projekt/single.php            body of single-projekt.php (reused by the delegates)
    projekt/card.php              grid tile (media 16/9, type, title)
    projekt/grid.php              chips + grid wrapper (archive + taxonomy)
    projekt/media.php             .grayscale wrapper: featured image (open3-wide) or CSS placeholder
    projekt/steckbrief.php        .table rows Jahr/Datenquelle/Technik/Lizenz/Kontakt
    projekt/related.php           3 related cards
    projekt/treemap-controls.php  #id-list block parameterised by $args['suffix'] ('' or '2')
  assets/
    css/design-system.css         ported styles.css (tokens + used components; @import removed; rgba fallback before color-mix)
    css/theme.css                 all layout/page CSS (other perspective)
    css/editor.css                editor parity (imports design-system tokens + prose rules)
    css/treemap.css               port of legacy style.css:1418-1593, fluid #container
    js/main.js                    mobile menu toggle (aria-expanded, Escape), explorer cycling; no jQuery
    js/treemap/jit-treemap-c.js   re-hosted JIT 2.0.0b (BSD notice kept)
    js/treemap/budgetkuerzungen-2011-2014-v3.1.js
    js/treemap/gesamthaushalt-2011-2014-v1.0.js   patched: r-sq→r-sq2 etc., image prefix → window.o3TreemapBase
    js/treemap/wien-budget2009-ausgaben.js
    js/treemap/img/               node images referenced by the data files (copied from uploads/js-treemap/)
    fonts/archivo-400.woff2, archivo-800.woff2   (latin subset; covers ä/ö/ü/ß)
    img/favicon.ico, favicon.svg  (site icon should be set via Customizer > Site Identity; file is the fallback)
  languages/open3.pot
  tools/
    migrate-projekte.php          WP-CLI/eval-file migration (idempotent, --dry-run)
    setup-menus.php               creates "Hauptnavigation" (7 items) and assigns to primary
    playground/blueprint.json     activate theme, de_AT, permalinks, posts_per_page 5, fixture import, run migration
    playground/fixture.xml        WXR: sample posts, pages (projekte + 3 children, grundlagen + 3 children), categories
    lint.ps1                      php -l over all PHP files
repo root:
  .gitignore                      node_modules/, .playground/, *.log, .DS_Store, Thumbs.db, /open3/languages/*.mo
  .distignore                     design_handoff_open3_startseite/, .git/, tools/playground/, *.ps1, .gitignore
  README.md                       how to run Playground, lint, migrate
```

## 2. `functions.php` / inc modules

`functions.php` (no closing tag, `defined('ABSPATH') || exit;`): define `OPEN3_VERSION` (from `wp_get_theme()->get('Version')`), then `foreach ([setup, enqueue, cleanup, nav, template-tags, customizer, cpt-projekt, meta-projekt, explorer, comments, treemap, redirects, cli] as $m) require OPEN3_DIR.'/inc/'.$m.'.php';`.

### inc/setup.php — `open3_setup()` on `after_setup_theme`
- `load_theme_textdomain('open3', get_template_directory().'/languages')`
- `add_theme_support('title-tag')`, `'automatic-feed-links'`, `'html5'` (`['search-form','comment-form','comment-list','gallery','caption','style','script']`), `'responsive-embeds'`, `'editor-styles'` + `add_editor_style('assets/css/editor.css')`, `'custom-logo'` (`['height'=>26,'width'=>120,'flex-width'=>true,'unlink-homepage-logo'=>true]`; brand.php renders the text wordmark when no logo is set), `'post-thumbnails'` for `['post','projekt']`.
- Image sizes: `add_image_size('open3-wide', 800, 450, true)` and `add_image_size('open3-wide-2x', 1600, 900, true)` — same 16:9 ratio so `wp_get_attachment_image()` builds a proper srcset (WP only mixes same-ratio sizes). Sidebar/tile/hero all use 16:9. Do **not** register 4:3 sizes: only the unselected Start-B lead (dc.html:117) uses 4/3; add later if Start B is adopted. Add `image_size_names_choose` filter so editors see "Breit 16:9".
- `register_nav_menus(['primary' => 'Hauptnavigation', 'footer' => 'Footer-Sitemap'])`.
- `$GLOBALS['content_width'] = 800`.
- Legacy explorer images are 222×129 — they cannot fill a 340 px sidebar slot sharply; flag to client to re-screenshot.

### inc/enqueue.php — `open3_enqueue()` on `wp_enqueue_scripts`
- `wp_enqueue_style('open3-design-system', OPEN3_URI.'/assets/css/design-system.css', [], OPEN3_VERSION)`; `wp_enqueue_style('open3-theme', …/theme.css, ['open3-design-system'], OPEN3_VERSION)`. Fonts come from theme.json (`wp_print_font_faces`, WP 6.4+). Version = `OPEN3_VERSION` (filemtime only in `WP_DEBUG`).
- `wp_enqueue_script('open3-main', …/assets/js/main.js, [], OPEN3_VERSION, ['strategy'=>'defer','in_footer'=>true])`.
- `if (is_singular() && comments_open() && get_option('thread_comments')) wp_enqueue_script('comment-reply');`
- `wp_dequeue_style('classic-theme-styles')` (WP injects it for classic themes; its `.wp-block-button__link` rules are harmless but unneeded).
- Treemap handles: registered here, enqueued from `inc/treemap.php`.

### inc/cleanup.php
- `remove_action('wp_head','print_emoji_detection_script',7)`, `('wp_print_styles','print_emoji_styles')`, `('admin_print_scripts','print_emoji_detection_script')`, `('admin_print_styles','print_emoji_styles')`, `add_filter('emoji_svg_url','__return_false')`; `remove_action('wp_head','rsd_link')`, `('wp_head','wp_shortlink_wp_head')`, `('wp_head','wp_generator')`. Do not touch `wp-embed`, `rest_output_link_wp_head`, feeds.
- `add_filter('excerpt_length', fn() => 40, 999)`; `add_filter('excerpt_more', fn() => '', 999)` (M-7; "Weiterlesen →" is a separate ghost button in teaser.php).
- `body_class`: `sidebar-right|sidebar-left` (Customizer), `no-sidebar` when `open3_is_treemap_template()` or `is_404()`? (404 keeps the sidebar per README:37 "wie Start A ohne Hero" → sidebar stays), `has-hero` on `is_home() && !is_paged()`.
- `get_the_archive_title` filter: strip the "Kategorie:"/"Schlagwort:" prefix (use `get_the_archive_title_prefix` → `''`), because the kicker carries the context.
- `pre_get_posts` (front, main query): `is_search()` → `post_type = ['post','page','projekt']` (placeholder promises "Beiträge, Projekte, Grundlagen").

### inc/nav.php
- `class Open3_Nav_Walker extends Walker_Nav_Menu` overriding `start_lvl/end_lvl` (no-ops), `start_el` (emit `<a class="nav-link {classes}" href … aria-current>` using `apply_filters('nav_menu_link_attributes', …)` so core's `aria-current="page"` survives), `end_el` (nothing). Depth forced to 1 via `wp_nav_menu(['depth'=>1])`. No dynamic properties (PHP 8.4): declare `public $tree_type` etc. are inherited; add nothing.
- `open3_nav_fallback($args)`: renders the design's 7 items from an ordered slug list `['home','projekt-archive','grundlagen','openevents','opennetwork','medienspiegel','uber-open3']`, resolving via `get_page_by_path()` and `get_post_type_archive_link('projekt')`, skipping missing pages; marks active via the same conditions as the filter below.
- `nav_menu_css_class` filter: add `current-menu-item` to the home item when `is_home()||is_singular('post')||is_category()||is_tag()||is_date()||is_author()` (dc.html:527 keeps "Blog" active on posts), and to the `post_type_archive` projekt item when `is_singular('projekt')||is_tax('projekttyp')`. `nav_menu_link_attributes` filter: when the item has `current-menu-item`, ensure `aria-current="page"` exists (core already does; the filter is defensive for the fallback renderer).
- Header nav markup: one `<nav id="skiplink-navigation" class="site-nav" aria-label="Hauptnavigation">` rendered once; CSS switches desktop row vs. mobile column; toggle `<button class="btn btn-secondary nav-toggle" aria-expanded="false" aria-controls="skiplink-navigation">Menü</button>` (label swaps to "Schließen" in JS). The mobile-only "Newsletter abonnieren" primary button is the last child of the nav.
- Newsletter button href: `open3_newsletter_url()` = permalink of `get_page_by_path('newsletter')` or `home_url('/newsletter/')`.

### Skip links (header.php, right after `wp_body_open()`)
```php
<ul class="skip-links">
  <li><a class="skip-link" href="#skiplink-navigation">Zur Navigation springen</a></li>
  <li><a class="skip-link" href="#skiplink-inhalt">Zum Inhalt springen</a></li>
  <?php if (is_singular() && get_comments_number()) : ?><li><a class="skip-link" href="#skiplink-kommentare">Zu den Kommentaren springen</a></li><?php endif; ?>
  <?php if (is_singular() && comments_open()) : ?><li><a class="skip-link" href="#skiplink-kommentarformular">Zum Kommentarformular springen</a></li><?php endif; ?>
</ul>
```
No `display:none` (SC-2/M-10); `<main id="skiplink-inhalt" tabindex="-1">`; visually-hidden-until-focus CSS is the other perspective's job.

### `<html>` / `<head>`
`<html <?php language_attributes(); ?>>` (yields `lang="de-AT"` once the site language is Deutsch (Österreich)), `<meta charset>`, `<meta name="viewport" content="width=device-width, initial-scale=1">`, `wp_head()`. No OG tags in the theme (Analyse.md:71: SEO plugin). No `google-site-verification` meta (client decides; Search Console can verify via DNS).

### Security baseline
- Every echo escaped: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` for `the_excerpt`-derived HTML, `wp_json_encode` with `JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES` for the JSON island.
- Meta box: `wp_nonce_field('open3_projekt_meta','open3_projekt_meta_nonce')`, `current_user_can('edit_post',$post_id)`, skip on autosave/revision.
- No REST usage in Stage 1 → no nonce needed; if the optional REST fallback is added it is a public GET to `/wp-json/wp/v2/projekt?per_page=1&orderby=rand&_embed` (no nonce, no auth).
- Customizer: `sanitize_callback` on every setting; `capability => 'edit_theme_options'`.

## 3. CPT `projekt`, taxonomy `projekttyp`, meta

### 3.1 Registration (inc/cpt-projekt.php, `init` priority 5, taxonomy BEFORE post type)
```php
register_taxonomy('projekttyp', ['projekt'], [
  'labels' => ['name'=>'Projekttypen','singular_name'=>'Projekttyp','menu_name'=>'Projekttypen','all_items'=>'Alle Projekttypen','edit_item'=>'Projekttyp bearbeiten','add_new_item'=>'Neuen Projekttyp anlegen','search_items'=>'Projekttypen suchen','not_found'=>'Keine Projekttypen gefunden'],
  'hierarchical' => true, 'public' => true, 'show_in_rest' => true, 'show_admin_column' => true,
  'rewrite' => ['slug' => 'projekttyp', 'with_front' => false, 'hierarchical' => false],
]);
register_post_type('projekt', [
  'labels' => ['name'=>'Projekte','singular_name'=>'Projekt','menu_name'=>'Projekte','add_new'=>'Neues Projekt','add_new_item'=>'Neues Projekt anlegen','edit_item'=>'Projekt bearbeiten','new_item'=>'Neues Projekt','view_item'=>'Projekt ansehen','view_items'=>'Projekte ansehen','search_items'=>'Projekte suchen','not_found'=>'Keine Projekte gefunden','not_found_in_trash'=>'Keine Projekte im Papierkorb','all_items'=>'Alle Projekte','archives'=>'Projekt-Archiv','featured_image'=>'Projektbild (16:9)','set_featured_image'=>'Projektbild festlegen','remove_featured_image'=>'Projektbild entfernen','use_featured_image'=>'Als Projektbild verwenden','item_published'=>'Projekt veröffentlicht.','item_updated'=>'Projekt aktualisiert.'],
  'description' => 'Anwendungsorientierte Projekte von open3',
  'public' => true, 'show_in_rest' => true, 'rest_base' => 'projekt',
  'has_archive' => 'projekte',
  'rewrite' => ['slug' => 'projekte', 'with_front' => false, 'feeds' => false],
  'menu_position' => 20, 'menu_icon' => 'dashicons-portfolio',
  'supports' => ['title','editor','excerpt','thumbnail','page-attributes','comments','revisions','author'],
  'hierarchical' => false, 'taxonomies' => ['projekttyp'],
  'template' => [['core/paragraph', ['placeholder' => 'Worum es geht …']]],
]);
```
`comments` support is required: page 28824 (Poetry Contest) carries 23 comment-based submissions (G-1) that survive D-7. `page-attributes` gives `menu_order` for the grid order.

### 3.2 Term seeding
`open3_seed_projekttyp()` inserts `Visualisierung, App, Umfrage, Dokumentation, Veranstaltung, Code` (slugs `visualisierung, app, umfrage, dokumentation, veranstaltung, code`) with `term_exists()` guards; run on `after_switch_theme` and on `admin_init` once (option `open3_terms_seeded = 1`); the migration re-runs it. Filter chips are rendered from `get_terms(['taxonomy'=>'projekttyp','hide_empty'=>true])` (G-3), so "Code" appears as soon as geocoder-php is assigned.

### 3.3 Query rules (`pre_get_posts`, main query, front end)
- `is_post_type_archive('projekt') || is_tax('projekttyp')`: `posts_per_page => -1`, `orderby => ['menu_order'=>'ASC','date'=>'DESC']`, `meta_query` excluding `o3_versteckt = '1'` (`relation OR: NOT EXISTS / != '1'`).
- Explorer and "Verwandte Projekte" use the same exclusion via a shared helper `open3_projekt_visible_meta_query()`.

### 3.4 Meta (inc/meta-projekt.php)
`register_post_meta('projekt', $key, ['type'=>'string','single'=>true,'show_in_rest'=>true,'sanitize_callback'=>…,'auth_callback'=>fn()=>current_user_can('edit_posts'),'default'=>''])` for:

| key | label | sanitize | rendered |
|---|---|---|---|
| `o3_jahr` | Jahr | `sanitize_text_field` | Steckbrief row |
| `o3_datenquelle` | Datenquelle | `sanitize_text_field` | Steckbrief row |
| `o3_technik` | Technik | `sanitize_text_field` | Steckbrief row |
| `o3_lizenz` | Lizenz | `sanitize_text_field` | Steckbrief row |
| `o3_app_url` | Link zur Anwendung | `sanitize_url` | `.btn-primary` "Interaktive Grafik öffnen"/"Anwendung öffnen" (label = meta `o3_app_label`? no — fixed label "Anwendung öffnen"; treemap projects get "Interaktive Grafik öffnen" when the URL is internal to `/projekte/`) |
| `o3_datensatz_url` | Link zum Datensatz | `sanitize_url` | `.btn-secondary` "Datensatz" |
| `o3_versteckt` | Nicht in Übersicht/Explorer listen | `rest_sanitize_boolean` (type boolean) | query exclusion (F-1) |

Kontakt row is constant `office@open3.at` (M-8; Customizer `open3_contact_email`). Meta box `add_meta_box('open3-projekt-steckbrief','Steckbrief','open3_projekt_meta_box','projekt','normal','high')` — six inputs + one checkbox; save on `save_post_projekt`. Empty Steckbrief rows are skipped in `steckbrief.php`.

### 3.5 Slug conflict: page 17418 `projekte` vs. CPT archive
CPT/taxonomy permastruct rules are merged into `$wp_rewrite->extra_rules_top` and evaluated before page rules, so after `flush_rewrite_rules()`: `/projekte/` → `index.php?post_type=projekt` (archive), `/projekte/<slug>/` → `index.php?projekt=<slug>`. The published page 17418 would be shadowed but still reachable by ID and would confuse `get_page_by_path('projekte')`; migration sets it to `draft` (D-8) — its intro copy is replaced by the design's fixed archive copy (Customizer defaults `open3_projekte_intro_title` = "Anwendungsorientierte Projekte seit 2010", `_text` = the dc.html:214 paragraph). Any child page not converted would 404 (its path `projekte/<slug>` never reaches page rules) — the migration therefore converts ALL children of 17418, not a whitelist. Menu items of type `page` pointing to 17418 must be replaced by a `post_type_archive` item (setup-menus.php).

### 3.6 Template hierarchy for the four special slugs
WP resolves singular CPT views as `single-projekt-{post_name}.php` → `single-projekt.php` → `single.php` (WP ≥ 4.4, `get_single_template()` uses the urldecoded `post_name`). Slugs after in-place conversion are unchanged: `sparpaket`, `wien-budget-2009-visualisierung`, `woergl-haushalt-1949-2009-visualisierung`, `kremsmuenster-haushalt-2003-2012-visualisierung` → the four files listed in §1. Renaming a slug in the editor silently drops the template; document this in the meta box description and guard with `open3_treemap_map()` keyed by slug in `inc/treemap.php`.

inc/treemap.php:
```php
function open3_treemap_map(): array { return [
  'sparpaket' => ['jit','treemap-budgetkuerzungen','treemap-gesamthaushalt'],
  'wien-budget-2009-visualisierung' => ['jit','treemap-wien2009'],
]; }
```
`wp_enqueue_scripts`: if `is_singular('projekt')` and slug in map → enqueue `open3-jit` (in_footer, no defer), the data handles depending on `open3-jit`, `open3-treemap` CSS, and `wp_add_inline_script($lastHandle, 'function o3TreemapBoot(){init();init2();}…readyState…', 'after')` (`init()` only for Wien). `wp_add_inline_script('open3-jit', 'window.o3TreemapBase="'.esc_url(OPEN3_URI.'/assets/js/treemap/img/').'";', 'before')`. Inline "after" scripts make WP downgrade `defer` anyway, so enqueue these in the footer without a strategy. Body class `no-sidebar`. Patches to the re-hosted `gesamthaushalt-2011-2014-v1.0.js`: `'r-sq'|'r-st'|'r-sd'` → `'r-sq2'|'r-st2'|'r-sd2'` and `'http://www.open3.at/wp-content/uploads/js-treemap/'` → `window.o3TreemapBase` (F-3). `treemap-controls.php` renders `id="id-list{suffix}"`, radios `id="r-sq{suffix}" name="layout{suffix}"`, anchor `id="back{suffix}" href="#hoch{suffix}"`; sparpaket renders suffix `2` first (infovis2/log2/hoch2, as legacy sparpaket.php:40-54) then suffix `''` (infovis/log/hoch, sparpaket.php:66-80); `#container`/`#container-bottom` become classes `.treemap` / `.treemap-links` (nothing in the JS targets them — verify with grep after download). Drop excanvas and the IE conditional comments. Keep the outbound data links verbatim (sparpaket.php:56, :82; wienbudget2009.php:32, :56), upgrade `http://www.open3.at` to `https://`. The moneypile block (sparpaket.php:57-61) stays as an image link to `/wp-content/uploads/js-treemap/moneypile/moneypile.php` (raw PHP outside the theme — flag as client decision).

## 4. Projekt-Explorer

`open3_explorer_items(): array` (inc/explorer.php): `get_posts(['post_type'=>'projekt','post_status'=>'publish','posts_per_page'=>-1,'no_found_rows'=>true,'update_post_term_cache'=>true,'meta_query'=>open3_projekt_visible_meta_query()])`, then `shuffle()`; map each to
`['id'=>ID,'title'=>get_the_title(),'type'=>first projekttyp name or '','url'=>get_permalink(),'image'=> ['src','srcset','sizes','alt','width','height'] from wp_get_attachment_image_src($tid,'open3-wide') + wp_get_attachment_image_srcset/sizes, or null]`. Cache nothing (23 rows). Server renders `$items[0]`.

Markup contract (template-parts/sidebar/explorer.php):
```html
<section class="sb-block explorer" data-explorer aria-live="polite">
  <div class="sb-head"><span class="kicker kicker-accent">Projekt-Explorer</span><a href="{archive}" title="alle open3.at-Projekte anzeigen">alle anzeigen</a></div>
  <a class="grayscale media media-16x9 media-bordered" href="{url}" data-explorer-link>
    <img src srcset sizes="(min-width: 900px) 340px, 100vw" alt="" width height loading="lazy" decoding="async" data-explorer-img>
    <span class="media-placeholder" hidden data-explorer-placeholder><span data-explorer-placeholder-text>{title}</span></span>
  </a>
  <span class="explorer-type" data-explorer-type>{type}</span>
  <a class="explorer-title" href="{url}" data-explorer-title>{title}</a>
  <button type="button" class="btn btn-ghost" data-explorer-next>Nächstes Projekt →</button>
</section>
<script type="application/json" id="open3-explorer-data">[…]</script>
```
main.js: parse `#open3-explorer-data`, `idx = 0`; on click `idx = (idx+1) % items.length`; set `textContent` for type/title/placeholder, `href` on both links, `src/srcset/alt` on img; when `image === null` set `img.hidden = true; placeholder.hidden = false` (and reverse). Never use innerHTML. Placeholder look (image-slot.js:291-337, replicated in CSS): `background: rgba(127,127,127,.08); border: 1.5px dashed currentColor` at 35 % opacity, centred 13px/1.3 caption, radius 0; wrapper keeps `aspect-ratio:16/9; border:2px solid var(--color-divider)` (M-13). Same `projekt/media.php` part is used by the grid tile and the detail hero (`$args['size'] => 'open3-wide' | 'open3-wide-2x'`, `$args['bordered']`). REST fallback: optional Stage 2; not needed because the island holds all items.

## 5. Sidebar template parts (sidebar.php → `<aside class="sidebar">`)

1. `sidebar/search.php`: `get_search_form()` → searchform.php: `<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"><label class="kicker kicker-muted" for="s-{uniqid}">Suche</label><div class="search-row"><input id class="input" type="search" name="s" value="<?php echo get_search_query(); ?>" placeholder="Beiträge, Projekte, Grundlagen …"><button type="submit" class="btn btn-secondary">Suchen</button></div></form>` (G-6; `get_search_query()` is pre-escaped).
2. `sidebar/explorer.php`: §4.
3. `sidebar/recent.php`: `get_posts(['posts_per_page'=>8,'ignore_sticky_posts'=>true,'no_found_rows'=>true])`; each `<a class="recent-item" href><span class="recent-date">{get_the_date('m/y')}</span><span>{title}</span></a>`. Duplication with the 5 teasers on page 1 is by design (C-7) — keep.
4. `sidebar/categories.php`: `get_categories(['orderby'=>'count','order'=>'DESC','hide_empty'=>true])` (matches News 147 → mehrGRIPS 2); `<a class="cat-item" href="{get_category_link}"><span>{name}</span><span class="cat-count">{count}</span></a>` (D-16).
5. `sidebar/newsletter.php`: if `class_exists('Newsletter')`: `<form class="nl-box" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php?action=tnp&na=s')); ?>"><span class="nl-title">Newsletter</span><span class="nl-text">Neue Beiträge, Termine und Projekte per E-Mail.</span><input type="hidden" name="nlang" value=""><label class="screen-reader-text" for="nl-ne">E-Mail-Adresse</label><input id="nl-ne" class="input" type="email" name="ne" required placeholder="E-Mail-Adresse"><button type="submit" class="btn btn-ghost nl-submit">Abonnieren</button></form>` (field names from the live form: `ne`, optional `nn`, `nlang`); else the same box with an `<a class="btn btn-ghost nl-submit" href="{open3_newsletter_url()}">Abonnieren</a>`. Note: the Newsletter plugin's own antibot may require its JS/nonce; verify in Stage 1 on the live site and fall back to rendering `do_shortcode('[newsletter_form]')` wrapped in the box if the raw POST is rejected.

Block order and `sb-block` borders are CSS; `sidebar.php` also carries `order` swap classes from `open3_sidebar_position()` (C-9).

## 6. Templates

**home.php**: `get_header(); ?><main …>` → `if (!is_paged()) get_template_part('template-parts/content/hero')` (H1 = `bloginfo('description')` escaped via `get_bloginfo('description','display')`, lede = `get_theme_mod('open3_hero_text', $default)`), list head `<span class="kicker kicker-accent">Blog · Neueste Beiträge</span><a href="{get_bloginfo('rss2_url')}">RSS-Feed</a>`, loop of `content/teaser`, `content/pagination`, `get_sidebar(); get_footer();`. `posts_per_page` stays the WP setting (5; blueprint sets it). No static front page → no `front-page.php` (M-16).

**content/teaser.php**: `<article id="post-{ID}" <?php post_class('teaser'); ?>>` meta row `<time datetime="{get_the_date('c')}">{get_the_date('j. F Y')}</time>` + first category as `<a class="tag tag-accent" href>`; `<h2 class="teaser-title"><a href>{title}</a></h2>`; `<p class="teaser-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>` (excerpt is plain text after `wp_trim_words`; manual excerpts pass through `wp_strip_all_tags` via `esc_html` — acceptable); actions `<a class="btn btn-ghost" href>Weiterlesen →</a><span class="teaser-author">{get_the_author()}</span>`.

**content/pagination.php** — `open3_pagination()`:
```php
$links = paginate_links(['type'=>'array','mid_size'=>2,'end_size'=>1,'prev_text'=>'← Neuere','next_text'=>'Ältere →','before_page_number'=>'<span class="screen-reader-text">Seite </span>']);
```
Page 1 of 46 → `1 2 3 … 46 Ältere →` (mid_size 2 yields exactly the design's three leading numbers); page 1 has no prev automatically. Wrap: `<nav class="pagination" aria-label="Seitennavigation"><span class="kicker kicker-muted">Seite</span>…implode…</nav>`. Core emits `<span aria-current="page" class="page-numbers current">`, `<span class="page-numbers dots">…</span>`, `<a class="next page-numbers">` — CSS hooks for the filled current block and the 6px-4px dots (G-9, M-6).

**index.php**: home.php without the hero (shared via `get_template_part('template-parts/content/loop')` if you prefer one loop file; keep both files tiny).

**single.php**: `<article id post_class>`: breadcrumb row `<a href="{home_url('/')}">Blog</a><span>/</span>{tag-accent category}<time>{j. F Y}</time>`; `<h1 class="post-title">`; byline `Autor: <a href="{get_author_posts_url}" rel="author">{name}</a> · {open3_reading_time()}` where `open3_reading_time()` = `max(1, (int) ceil(str_word_count(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content')))) / 200))` → `sprintf('%d Min. Lesezeit', $n)`; `content/prose.php` (`the_content()` inside `.prose`, `wp_link_pages`); tag row: `Kategorie` + tag-accent, `Themen` + `get_the_tags()` as `tag-neutral` links, spacer, `<a class="btn btn-ghost" href="{open3_bluesky_url()}" rel="noopener" target="_blank">Teilen auf Bluesky</a>` with `https://bsky.app/intent/compose?text=` . rawurlencode(title . ' ' . permalink); prev/next grid: `get_previous_post()` → "← Vorheriger Beitrag" + title, else `<span class="adjacent-empty">Kein älterer Beitrag</span>`; `get_next_post()` → "Nächster Beitrag →" else "Noch kein neuerer Beitrag" (dc.html:194, colour `--color-neutral-600`; M-12 accepted); `comments_template()`.

**page.php**: kicker = parent page title if any, `<h1>`, `content/prose.php`, `comments_template()` only if `comments_open()||get_comments_number()`.

**archive.php**: kicker via `open3_kicker()`: category → `Kategorie · {name} · {count}`; tag → `Thema · {name}`; author → `Autor`; date → `Archiv`; `<h1>{the_archive_title()}</h1>` (prefix stripped), optional `the_archive_description()`, teaser loop, pagination, `content/none.php` when empty.

**search.php**: kicker `Suche`; `<h1><?php printf(esc_html__('Suchergebnisse für „%1$s“ (%2$d Treffer)','open3'), get_search_query(), (int) $wp_query->found_posts); ?></h1>`; teaser loop (projekt/page results use the same teaser part; the category chip is omitted for non-posts); pagination; empty → `content/none.php` (text + `get_search_form()`).

**404.php**: kicker `Fehler 404`, `<h1>Seite nicht gefunden</h1>`, `<p>` + `get_search_form()` + link "Zur Startseite"; sidebar + footer as usual; WP already sends the 404 status.

**comments.php** (G-1 order fixed):
```
<?php if (post_password_required()) return; ?>
<section id="skiplink-kommentare" class="comments">
  if (have_comments()):
    <h2>{heading}</h2>   // "Kommentare" or "Einreichungen" for slug poetry-contest, with count
    <ol class="comment-list"><?php wp_list_comments(['type'=>'comment','callback'=>'open3_comment','avatar_size'=>0,'style'=>'ol']); ?></ol>
    the_comments_navigation()
    pings: if (get_comments(['post_id'=>ID,'type'=>'pings','count'=>true])) <h3>Links zum Artikel</h3><ol class="ping-list"><?php wp_list_comments(['type'=>'pings','short_ping'=>true]); ?></ol>
  elseif (comments_open()): <p class="comments-empty">Noch keine Kommentare.</p>
  endif;
  if (comments_open()): <div id="skiplink-kommentarformular"><?php comment_form(open3_comment_form_args()); ?></div>
  elseif (have_comments()): <p>Die Kommentarfunktion ist für diesen Beitrag geschlossen.</p>
</section>
```
`open3_comment_form_args()`: `title_reply => 'Kommentar hinzufügen'`, `title_reply_to => 'Antwort auf %s'`, `title_reply_before => '<h2 id="reply-title" class="comment-form-title">'`, `comment_notes_before => '<p class="comment-notes">E-Mail-Adresse wird nicht veröffentlicht.</p>'`, `comment_notes_after => ''`, `fields => ['author' => '<div class="field"><label for="author">Name</label><input id="author" class="input" name="author" type="text" value="…" placeholder="Ihr Name" required></div>', 'email' => '<div class="field"><label for="email">E-Mail</label><input id="email" class="input" name="email" type="email" value="…" placeholder="name@beispiel.at" required></div>']` (no URL field, matching dc.html:200-201; wrap both in a `<div class="comment-fields">` via `comment_form_before_fields`/`after_fields` actions), `comment_field => '<div class="field field-comment"><label for="comment">Kommentar</label><textarea id="comment" class="input" name="comment" rows="5" placeholder="Ihr Kommentar" required></textarea></div>'`, `label_submit => 'Kommentar absenden'`, `class_submit => 'btn btn-primary'`, `submit_button => '<button type="submit" name="%1$s" id="%2$s" class="%3$s">%4$s</button>'`, `submit_field => '<div class="form-submit">%1$s %2$s</div>'`, `logged_in_as` German, `cancel_reply_link => 'Abbrechen'`, `format => 'html5'`. Spamschutz (inc/comments.php): `comment_form_after_fields` prints `<div class="o3-hp" aria-hidden="true"><label for="o3_website">Website</label><input id="o3_website" name="o3_website" type="text" tabindex="-1" autocomplete="off"></div>` (CSS-hidden) plus `<input type="hidden" name="o3_ts" value="{time()}">`; `preprocess_comment` filter: if `$_POST['o3_website'] !== ''` or `time() - (int)$_POST['o3_ts'] < 4` → `wp_die('Kommentar abgelehnt.', 403)`; only for `comment_type === 'comment'` (pings bypass). Recommend Antispam Bee (DSGVO-friendly) as plugin-level second layer. `open3_comment($comment,$args,$depth)`: `<li id="comment-{ID}" <?php comment_class('comment', $comment); ?>><article id="div-comment-{ID}"><header class="comment-meta"><b class="comment-author">{get_comment_author_link}</b><time datetime>{get_comment_date('j. F Y')} · {get_comment_time('H:i')} Uhr</time>{edit_comment_link}</header>{moderation notice}<div class="comment-body">{comment_text}</div>{comment_reply_link(array_merge($args,['depth'=>$depth,'max_depth'=>$args['max_depth'],'before'=>'<div class="reply">','after'=>'</div>']))}</article>` — no closing `</li>` (core closes it). Poetry-contest branch: `is_singular('projekt') && get_post_field('post_name') === 'poetry-contest'` → heading `Einreichungen (%d)`; comments stay closed on that project (set by migration).

**Stage 2 templates (brief contracts)**
- `archive-projekt.php` / `taxonomy-projekttyp.php`: kicker `Projekte · {found_posts}`; H1/lede from Customizer defaults; `projekt/grid.php` renders chips (`<a class="tag tag-accent|tag-neutral" href>` for "Alle" + each term; `aria-current="page"` on active) then the 2px-gap grid of `projekt/card.php` (`<a class="projekt-card" href>` media + type + title).
- `single-projekt.php` → `projekt/single.php`: breadcrumb `Projekte / {type}`; H1 (max 24ch); excerpt (`has_excerpt()` else nothing); actions from `o3_app_url` / `o3_datensatz_url`; hero `projekt/media.php` size `open3-wide-2x` + `<p class="figcaption">{post_thumbnail_caption}</p>`; two-column: `.prose` (`the_content`) + `projekt/steckbrief.php`; `projekt/related.php` = 3 visible projects sharing a `projekttyp` (`orderby rand`, exclude current), fallback any; `comments_template()` when `have_comments()`.
- `page-grundlagen.php`: kicker `Grundlagen · {n} Beiträge`; H1 = `the_title()`; lede = `has_excerpt()` ? excerpt : the_content stripped; list from `get_pages(['child_of'=>$id,'parent'=>$id,'sort_column'=>'menu_order,post_title'])`, numbers `sprintf('%02d',$i+1)`. Live `menu_order` has duplicates (0,0,1,1,1…); `tools/migrate-projekte.php` gets an optional `--renumber-grundlagen` that assigns the prototype order (dc.html:481).
- `page-openevents.php`: register post meta on `post`: `o3_termin_datum` (Y-m-d\TH:i), `o3_termin_ort`, `o3_termin_link` with a "Termin" meta box; upcoming = `WP_Query(category_name=termine, meta_key=o3_termin_datum, meta_value>=now, orderby meta_value ASC)`; calendar = consent button with `data-src` → JS injects the iframe; past = category `termine` posts (10, `orderby date DESC`) in `.table` + `<a class="btn btn-ghost" href="{category link}">Alle {count} Einträge anzeigen</a>`.
- `page-uber-open3.php`: static 3-cell grid (copy from dc.html:301-303), `.prose` `the_content()`, services list (static array; `openLinks` links to `/grundlagen/openlinks/`), "Auf dem Laufenden bleiben" with feed/newsletter/Bluesky/mailto links.
- openNetwork/Medienspiegel: `page.php` (G-4).

**footer.php**: col 1 brand (20px) + `get_bloginfo('description','display')`; col 2 `Sitemap`: `wp_nav_menu(['theme_location'=>'footer','fallback_cb'=>'open3_footer_fallback','walker'=>new Open3_Nav_Walker,'container'=>false,'items_wrap'=>'%3$s','depth'=>1])` where `open3_footer_fallback` renders the primary menu (or its fallback) and appends `<a href="{open3_newsletter_url()}">Newsletter</a>` — appended only when no footer menu is assigned; col 3 `Kontakt`: `<a href="mailto:{email}">{email}</a>`, `<a href="{bluesky}" rel="me noopener">Bluesky @seyfro.at</a>`, `<a href="{rss2_url}">RSS-Feed</a>`; col 4 `Lizenz`: README.md:51 sentence with `<a href="http://creativecommons.org/licenses/by/3.0/at/deed.de" rel="license">`, then `<a href="{get_page_by_path('impressum')}">Impressum</a>` and `<a href="{get_privacy_policy_url() ?: home_url('/datenschutzerklaerung/')}">Datenschutzerklärung</a>`. No analytics (Piwik dropped; SC-10). Before deactivating the old theme, export `get_option('art_footer_content')` and the sidebar widget options (M-14).

## 7. i18n

Text domain `open3`, `Domain Path: /languages`, `load_theme_textdomain` in setup. All UI strings German source wrapped in `__()/_e()/esc_html__()/_n()/_x()` (never `_c()`, SC-4). Ship `languages/open3.pot` generated with `npx @wordpress/i18n make-pot ./open3 ./open3/languages/open3.pot --domain=open3 --exclude=tools,assets` (wp-cli i18n via npm is not available; `@wordpress/i18n`'s CLI is not a make-pot; use `npx wp-pot-cli --src 'open3/**/*.php' --dest-file open3/languages/open3.pot --domain open3` instead). Blueprint sets site language `de_AT`; document in README that WP's "Jänner" comes from the core locale, not the theme.

## 8. Coding standards, PHP 8.4, lint

- Prefix everything `open3_` (functions, hooks, handles, meta keys use `o3_` as decided); classes `Open3_*`; constants `OPEN3_*`.
- No closing `?>` in PHP-only files; templates use `<?php … ?>` blocks with `defined('ABSPATH') || exit;` in inc files.
- PHP 8.4: typed signatures with nullable types written `?string` (implicit-nullable defaults are deprecated in 8.4); no dynamic properties (the walker declares nothing new; any helper class uses declared props or `#[\AllowDynamicProperties]` is NOT used); no `extract()`, `create_function`, `each`; `str_contains`/`str_starts_with` allowed (PHP 8+); `mb_*` for German text.
- Escaping: `esc_html__` for text, `esc_attr` in attributes, `esc_url` in href/src/action, `wp_kses_post` for stored HTML (`the_content` already filtered), `wp_json_encode` with hex flags for `<script type="application/json">`.
- Lint: `tools/lint.ps1` → `Get-ChildItem open3 -Recurse -Filter *.php | ForEach-Object { & 'C:\wamp64\bin\php\php8.4.15\php.exe' -l $_.FullName }` and fail on any "Errors parsing". Optional WPCS: `composer require --dev wp-coding-standards/wpcs dealerdirect/phpcodesniffer-composer-installer` + `phpcs --standard=WordPress open3` (only if Composer is installed; not verified on this machine).
- Runtime deprecation check: blueprint enables `WP_DEBUG` + `WP_DEBUG_LOG`; inspect `wp-content/debug.log` after browsing all templates.

## 9. Migration script (`tools/migrate-projekte.php`, `wp open3 migrate-projekte [--dry-run] [--parent=17418] [--renumber-grundlagen]`)

1. Seed `projekttyp` terms. 2. `$children = get_pages(['parent'=>$parent,'post_status'=>['publish','draft','private'],'sort_column'=>'menu_order','number'=>0])` — ALL children (3.5). 3. Per child: skip if `post_type === 'projekt'`; `set_post_type($id,'projekt')`; `wp_update_post(['ID'=>$id,'post_parent'=>0,'menu_order'=>$order])` where `$order` = position in the prototype list (dc.html:458-479) or 99; `delete_post_meta($id,'_wp_page_template')` (C-8); `wp_set_object_terms($id, $typeSlug, 'projekttyp')` from the slug→type map (22 from the prototype + `sparpaket`→visualisierung); featured image: slug→file map for the 16 legacy files (`opendata-buddy.png, gluecksspiel-projekt.png, pegelstand-kremsmuenster.jpg, wlcc.png, sparpaket11-14.png, toiletmap.png, umfrage-parlament.png, datamapseu.png, ubahnaufzug.png, umfrage-ngos.png, fz-wlan-linz.png, woergl.png, umfrage-datenarten.png, budget-wien.png, poetry-contest.png, ozoninfo.png`) → if `file_exists(wp_upload_dir()['basedir'].'/projekt-explorer/'.$file)` create an attachment in place (`wp_insert_attachment` + `wp_generate_attachment_metadata`), else `media_sideload_image('https://www.open3.at/wp-content/uploads/projekt-explorer/'.$file, $id, null, 'id')`; `set_post_thumbnail`; skip if a thumbnail already exists. 4. Specials: 14991 → `o3_versteckt=1`; 17419 → `o3_app_url = home_url('/projekte/sparpaket/')`, `o3_datensatz_url = 'http://offener.datenkatalog.at/package/bundesbudget-2011-2014'`; 17913 → `o3_datensatz_url = '…/wien-budget-2009-ausgaben'`; 28824 → `comment_status = closed`. 5. `wp_update_post(['ID'=>$parent,'post_status'=>'draft'])`. 6. `flush_rewrite_rules()`. 7. Report table. Idempotent; `--dry-run` prints actions only. `tools/setup-menus.php` (`wp open3 setup-menus`): create "Hauptnavigation" if missing with items Blog (custom `home_url('/')`), Projekte (`post_type_archive`), Grundlagen/openEvents/openNetwork/Medienspiegel/Über open3 (pages by slug), assign to `primary`. Redirect map (inc/redirects.php): `'/sparpaket' => '/projekte/sparpaket/'`, `'/gluecksspiel-karte' => '/projekte/gluecksspiel-opendata-mashup/'`, `'/projekte/linz-open-data' => '/projekte/linz-open-data-apps-und-visualisierungen/'`; `ogdwien-finanzdaten` has no known target (leave to the Redirection plugin).

## 10. Local verification (WordPress Playground CLI)

`npx @wp-playground/cli@latest server --mount-before-install=./open3:/wordpress/wp-content/themes/open3 --blueprint=./tools/playground/blueprint.json` (flag names per current CLI; fall back to `--mount`). Blueprint steps: `setSiteOptions` (`blogname`, `blogdescription` = tagline, `posts_per_page` 5, `permalink_structure` `/%year%/%monthnum%/%postname%/`, `category_base` `kategorie`, `WPLANG`), `setSiteLanguage de_AT`, `activateTheme open3`, `importWxr tools/playground/fixture.xml`, `defineWpConfigConsts WP_DEBUG/WP_DEBUG_LOG`, `wp-cli` step `wp open3 migrate-projekte` then `wp open3 setup-menus`. Then browse: `/`, `/page/2/`, a post with comments, `/kategorie/news/`, `/?s=open`, `/nonexistent`, `/projekte/`, `/projekttyp/app/`, `/projekte/sparpaket/`, `/projekte/wien-budget-2009-visualisierung/`, `/grundlagen/`, and confirm `debug.log` is empty of theme notices. Treemap JS must be downloaded from the live site into `assets/js/treemap/` before Stage 2 (files are not in the repo).

## Stage sequencing

Stage 1 (deployable): style.css, theme.json, functions.php, inc/{setup,enqueue,cleanup,nav,template-tags,customizer,cpt-projekt,meta-projekt,explorer,comments,redirects,cli}, header/footer/sidebar, home/index/single/page/archive/search/404/comments/searchform, all template-parts except `projekt/*` beyond `media.php`, assets, tools/migrate + blueprint. `single-projekt.php` gets a minimal Stage-1 version (title, excerpt, prose, Steckbrief) so migrated projects never 404. Stage 2: archive-projekt, taxonomy-projekttyp, full single-projekt + related, the four slug templates + inc/treemap.php + treemap assets, page-grundlagen, page-openevents (+ Termin meta), page-uber-open3.

## Risks / open points

- Newsletter plugin may reject a raw POST without its `nlang`/antibot token → verify live; fallback is `[newsletter_form]` (5.5).
- Treemap image paths inside the data files are unknown until downloaded (F-3); the `o3TreemapBase` patch is the mechanism, the file list is TBD.
- Slug renames in the editor drop `single-projekt-{slug}.php` silently (3.6) — document.
- Legacy explorer PNGs are 222×129; the 800/1600 sizes will not be generated, srcset degrades to the original (visible blur at 340 px).
- `moneypile.php` under uploads is executable PHP outside the theme — client decision.
- README:20/40 nav item "Über open3" page slug is `uber-open3` (no umlaut) → template file `page-uber-open3.php`.

### Critical Files for Implementation
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\inc\cpt-projekt.php
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\inc\setup.php
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\inc\explorer.php
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\comments.php
- C:\Users\rober\Documents\Git Repos\open3-theme\open3\tools\migrate-projekte.php

Reference sources these depend on: C:\Users\rober\Documents\Git Repos\open3-theme\design_handoff_open3_startseite\open3 Neuentwurf.dc.html (lines 46-110, 355-432), C:\Users\rober\Documents\Git Repos\open3-theme\design_handoff_open3_startseite\legacy_theme\sparpaket.php (lines 40-82), C:\Users\rober\Documents\Git Repos\open3-theme\design_handoff_open3_startseite\legacy_theme\comments.php.