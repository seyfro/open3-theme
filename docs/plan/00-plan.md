# Plan: open3.at – Neues WordPress-Theme nach Design-Handoff (Variante A)

## Kontext

Das Repository `open3-theme` enthält bisher nur das Übergabepaket `design_handoff_open3_startseite/` (README.md = die eigentliche „Readme“, Analyse.md, Prototyp `open3 Neuentwurf.dc.html`, Design-System `_ds/…/styles.css`, altes Theme in `legacy_theme/`). Es gibt keinen Commit und keine Theme-Dateien.

Das Live-Theme `wp-content/themes/open3` ist ein Artisteer-Export von 2010 (XHTML, IE7-Modus, fixe 980 px, PHP-8-inkompatibel: `get_magic_quotes_gpc`, `create_function`, `preg_replace /e`, `&new WP_Query`, `_c()`), Menü über `wp_list_pages` mit hartcodierten Ausschlüssen, Projekt-Explorer als PHP-String-Array, Treemap-Scripts per `is_page('14991')`/`is_page('17913')` im Header. Es wird komplett ersetzt.

Ziel: das finale High-Fidelity-Design pixelgenau als **klassisches WordPress-Theme mit theme.json** nachbauen, Inhalte/URLs erhalten, Projekte als Custom Post Type `projekt`, lokal mit WordPress Playground verifizieren.

## Entscheidungen (bestätigt)

| Frage | Entscheidung |
| --- | --- |
| Theme-Typ | Klassisches Theme (PHP-Templates) + `theme.json` (Palette, Fonts, Spacing, minimale Element-Styles für Editor-Parität) |
| Umfang | **Etappe 1** = Start A + alle Standard-Templates + CPT `projekt` + Projekt-Explorer (deploybar). **Etappe 2** = Spezialseiten nach Prototyp-Screens B–H + Treemap-Templates |
| Projekte | CPT `projekt` (Rewrite-Slug `projekte`, `has_archive`), Taxonomie `projekttyp`, Meta-Felder; Migration der 23 Live-Kindseiten **in place** (`set_post_type`, IDs/Kommentare/Meta bleiben) mit Rollback |
| Test-Umgebung | WordPress Playground CLI (`npx @wp-playground/cli server --php=8.4 --mount-dir … --blueprint …`), Fixtures per `dev/seed.php` |
| Logo | SVG-Logo des Vereins (`C:\Users\rober\Desktop\open3-logo.svg`, bereinigt, viewBox beschnitten) **inline** im Header/Footer, per CSS auf die Design-Tokens umgefärbt („Open“ `--color-text`, „3“ `--color-accent`, „GOVERNMENT DATA SOCIETY“ `--color-neutral-700`); ersetzt Wortmarke + Mikro-Tagline des Entwurfs. Originalfarben per Modifier-Klasse `o3-logo--original` (Grau `#9a9b9d`, Blau `#146fb2`). Customizer-Logo (falls gesetzt) hat Vorrang |

Annahmen: Theme-Ordner `open3/` im Repo-Root (Deploy wahlweise als `open3-v2` neben dem Alt-Theme für Rollback); Textdomain `open3`, deutsche Quellstrings; Archivo selbst gehostet als variable WOFF2 (latin + latin-ext, Gewichte 400–800) via `theme.json` `fontFace`; kein Analytics im Theme; Newsletter-Formulare posten an das live installierte Newsletter-Plugin (`admin-ajax.php?action=tnp&na=s`, Feld `ne`); Hero in `home.php` (Live-Site hat keine statische Startseite); FeedBurner-Link entfällt (native Feeds); Emoji-Scripts, `rsd_link`, `wp_shortlink`, `wp_generator` entfernt.

## Gesicherte Fakten

**Live-Site (2026-09-08):** WordPress ≥ 6.4, Block-Editor mit Kadence Blocks, REST aktiv. Plugins: Newsletter 9.3.6, Ajax Search Pro, WP-Paginate (wird überflüssig), TaxoPress, Meow Lightbox, Maps Marker Pro, Redirection, Pretty Links, Limit Login Attempts. 226 Beiträge (46 Seiten à 5), Kategorien `news` 147 · `termine` 55 · `studien` 30 · `medienspiegel` 5 · `mehrgrips` 2, Kategorie-Basis `/kategorie/`, Permalinks `/%year%/%monthnum%/%postname%` **ohne** Trailing Slash. Oberste Seiten: newsletter 55348, suche 55121, datenschutzerklaerung 53799, medienspiegel 21441, uber-open3 19121, opennetwork 17850, grundlagen 17841, projekte 17418, openevents 6992, impressum 11. **Projekte = 23 Kindseiten von 17418**, alle Autor 1, keine hat ein Beitragsbild; 4 mit Page-Templates (14991 sparpaket → `sparpaket.php`, 17913 wien-budget-2009-visualisierung → `wienbudget2009.php`, 20690 + 33560 → `woergl-visualisierungen.php`, deren Inhalt aber nur Text ist). 14991 `sparpaket` und 17419 `budgetvisualisierung-2011-2014` (leer) sind zwei Seiten für ein Projekt. Poetry-Contest 28824: Kommentare geschlossen, 23 freigegebene Kommentare = „Einreichungen“. Grundlagen = 21 Kindseiten von 17841 (`menu_order` mit Duplikaten). `/sparpaket` und `/projekte/linz-open-data` werden heute nur durch WordPress' 404-Raten umgeleitet; `/gluecksspiel-karte` ist Pretty Links; `/2023/11/opendata-buddy` und `/2013/10/erstmals-…` sind normale Beiträge. Projektbilder: `/wp-content/uploads/projekt-explorer/` (16 Dateien, 222×129 px; opendata-buddy 129×129; gluecksspiel 230×129). Treemap-Assets heruntergeladen (jit-treemap-c.js BSD-3, budgetkuerzungen v3.1 `init()`→`#infovis`/`#log`, gesamthaushalt v1.0 `init2()`→`#infovis2`/`#log2`, wien-budget2009 `init()`→`#infovis`/`#log`; Bildpräfix `http://www.open3.at/wp-content/uploads/js-treemap/` hartcodiert); `moneypile.php` liefert 403.

**Handoff:** `styles.css` = Token-/Komponentenquelle, nicht als `style.css` nutzbar (kein WP-Header, `@import`, kein `.screen-reader-text`, keine `align*`, `img` ohne `height:auto`, `.nav` widerspricht dem Header-Design). Nur `.input .grayscale .btn* .tag* .field .table .card*` werden im Prototyp genutzt. Prototyp-Laufzeit wird nicht portiert; vier `style-hover`-Werte werden `:hover`-Regeln. Nur zwei JS-Verhalten: mobiles Menü, Projekt-Explorer „Nächstes Projekt“. Sechs Spacing-Tokens. Prosa-Links `#ae1800` unterstrichen (Hover `#dd2b0f`), Nav-Hover `#ec3013`, Listen-Hover `#ae1800`, Projekt-Kachel-Hover `#fff2ef`. Body 15px/1.55; Hero-Absatz 16/28; Excerpt 15/26; Beitragstext 17/28; Sidebar 14/20. Suche/Newsletter sind im Prototyp keine `<form>`. Pagination `Seite | 1 | 2 | 3 | … | 46 | Ältere →`.

**Legacy, beizubehalten:** Skiplinks-Wortlaut („Zur Navigation springen“, „Zum Inhalt springen“, „Zu den Kommentaren springen“, „Zum Kommentarformular springen“) repariert (kein `display:none`, Ziele `#skiplink-navigation` auf `<nav>`, `#skiplink-inhalt` auf `<main>`, `#skiplink-kommentare`, `#skiplink-kommentarformular`); `language_attributes()` (Locale de_AT → „17. Jänner 2025“); Tagline als H1; deutsche UI-Strings; Treemap-DOM-Vertrag; Footer-Option `art_footer_content` vor Theme-Wechsel exportieren (kanonischer Text = README.md:51).

## Zielstruktur `open3/`

```
style.css (nur Header) · theme.json · screenshot.png · functions.php (Loader)
index.php home.php single.php page.php archive.php search.php 404.php comments.php searchform.php header.php footer.php sidebar.php
single-projekt.php  archive-projekt.php  taxonomy-projekttyp.php            (Etappe 1 minimal / Etappe 2 voll)
single-projekt-sparpaket.php  single-projekt-wien-budget-2009-visualisierung.php   (Etappe 2, Treemaps)
page-grundlagen.php  page-openevents.php  page-uber-open3.php               (Etappe 2)
inc/ setup.php enqueue.php cleanup.php nav.php template-tags.php customizer.php cpt-projekt.php meta-projekt.php
     explorer.php comments.php redirects.php treemap.php migration/{class-open3-project-migrator.php,cli.php,admin.php}
template-parts/ header/{logo,nav}.php content/{hero,teaser,pagination,none,prose}.php
                sidebar/{search,explorer,recent,categories,newsletter}.php
                projekt/{single,card,grid,media,steckbrief,related,treemap-controls}.php
assets/ css/{design-system,theme,editor,treemap}.css  js/main.js  js/treemap/*.js  fonts/Archivo-{latin,latin-ext}.woff2
        img/{logo.svg,favicon.svg,favicon.ico}
languages/open3.pot · LICENSES/ (JIT BSD-3, Archivo OFL) · dev/seed.php (Fixtures, nicht ausgeliefert)
Repo-Root: .gitignore .gitattributes .distignore README.md dev/{blueprint.json,blueprint-migration.json,check-urls.sh,lint.ps1,build-zip.sh} docs/plan/*.md
```

## Etappe 1 – Start A, Standard-Templates, CPT

1. **Basis:** `style.css`-Header (Text Domain open3, Requires PHP 8.1, GPL-2.0-or-later); `theme.json` v2 (Palette-Slugs aus Tokens, `fontFace` Archivo 400–800 latin/latin-ext, `spacingSizes` 4/8/12/16/24/32, contentSize 66ch, wideSize 800px, keine Gradients/Duotone/Custom Colors, Elemente link/heading/button); `functions.php` lädt `inc/*.php`.
2. **inc/setup.php:** title-tag, post-thumbnails (post, projekt), html5, automatic-feed-links, responsive-embeds, editor-styles (`design-system.css` + `editor.css`), custom-logo, align-wide; Bildgrößen `open3-teaser` 640×360 und `open3-wide` 1280×720 (beide 16:9, crop); `register_nav_menus(primary, footer)`; `content_width` 800; Textdomain.
3. **inc/enqueue.php:** `open3-design-system` → `open3-theme` (deps wp-block-library); `main.js` defer; `comment-reply`; no-js-Klasse per Inline-Script; `wp_dequeue_style('classic-theme-styles')`.
4. **inc/cleanup.php:** Emoji/rsd/shortlink/generator entfernen; `excerpt_length` 40, `excerpt_more` ''; `body_class` (`o3-sidebar-left`, `has-hero`, `no-sidebar`); Archiv-Titel-Präfix leer; Suche über post/page/projekt.
5. **inc/nav.php:** `wp_nav_menu` mit `items_wrap` `<ul class="o3-nav__list">`, Filter `nav_menu_css_class`/`nav_menu_link_attributes` (Klassen `o3-nav__item`/`o3-nav__link`, `aria-current="page"`), „Blog“ aktiv auf Beiträgen/Kategorien, „Projekte“ aktiv auf projekt/projekttyp; Fallback rendert die 7 Design-Einträge aus Slugs; Footer-Sitemap = Footer-Menü, sonst Primärmenü + „Newsletter“.
6. **header.php:** `<html <?php language_attributes(); ?> class="no-js">`, Skiplinks, `<header class="o3-header">` mit Inline-SVG-Logo (`template-parts/header/logo.php`), `<nav id="skiplink-navigation" class="o3-nav" aria-label="Hauptnavigation">`, Newsletter-Button (`.btn.btn-primary.o3-header__cta`), Toggle `<button class="btn btn-secondary o3-menu-toggle" aria-expanded aria-controls="skiplink-navigation">Menü</button>`, mobiler CTA „Newsletter abonnieren“ im Nav; `<div class="o3-layout"><main id="skiplink-inhalt" class="o3-main" tabindex="-1">`.
7. **home.php / index.php:** Hero nur `is_home() && !is_paged()` (H1 = `bloginfo('description')`, Lede aus Customizer mit README-Default), Listenkopf „Blog · Neueste Beiträge“ + RSS-Link, Teaser-Loop (`template-parts/content/teaser.php`: `<time>` `j. F Y`, erste Kategorie als `.tag.tag-accent`, H2, Excerpt, „Weiterlesen →“ Ghost-Button, Autor), Pagination (`paginate_links type=array, mid_size 2, end_size 1, next_text 'Ältere →', prev_text '← Neuere'` + Kicker „Seite“).
8. **sidebar.php:** Blöcke Suche (`searchform.php` mit `<form role="search">`, Label „Suche“, `.input` + `.btn.btn-secondary`), Projekt-Explorer (serverseitig 1 zufälliges Projekt + JSON-Insel aller sichtbaren Projekte; Bild `open3-teaser` im `.grayscale`-Wrapper 16:9 mit 2px-Rahmen; CSS-Platzhalter ohne Bild), Neu im Blog (8 neueste, Spalte `m/y`), Kategorien (eigene Schleife `get_categories orderby=count`), Newsletter-Box (Form an Newsletter-Plugin, Fallback-Link zur Newsletter-Seite).
9. **footer.php:** 4 Spalten (Logo + Tagline · Sitemap · Kontakt office@open3.at/Bluesky/RSS · Lizenz CC BY 3.0 AT + Impressum/Datenschutz aus `get_privacy_policy_url()`).
10. **single.php:** Breadcrumb `Blog / Kategorie-Tag / Datum`, H1 `clamp(30px,3.6vw,44px)`, Byline `Autor: … · N Min. Lesezeit` (200 Wörter/Min.), `.entry-content` 66ch 17/28, Kategorie/Themen-Zeile + „Teilen auf Bluesky“ (`https://bsky.app/intent/compose?text=`), Vor/Zurück-Raster mit Leerzuständen, `comments_template()`.
11. **comments.php + inc/comments.php:** Liste zuerst (`#skiplink-kommentare`, Callback ohne Avatar), dann `comment_form()` (`#skiplink-kommentarformular`, Felder Name/E-Mail im 2-Spalten-Grid + Kommentar, `.input`/`.field`, Submit `.btn.btn-primary` „Kommentar absenden“, keine URL); Spamschutz: Honeypot-Feld + Mindestzeit 4 s in `preprocess_comment`; Überschrift „Einreichungen“ bei `poetry-contest`.
12. **page.php / archive.php / search.php / 404.php:** Start A ohne Hero; Kicker je Kontext (`Kategorie · Name · Anzahl`, `Suche`, `Fehler 404`); Suchtitel „Suchergebnisse für „…“ (n Treffer)“; Leerzustand mit Suchformular.
13. **inc/cpt-projekt.php:** `projekttyp` (hierarchisch, Slug `projekttyp`, 6 Terme geseedet) vor `projekt` (Slug `projekte`, `has_archive 'projekte'`, `with_front false`, `feeds false`, supports title/editor/excerpt/thumbnail/page-attributes/comments/revisions/author, `show_in_rest`); `pre_get_posts` für Archiv (alle, `menu_order ASC, date DESC`, `open3_versteckt` ausgeschlossen); Flush bei `after_switch_theme`.
14. **inc/meta-projekt.php:** `register_post_meta` + Meta-Box „Steckbrief“: `open3_jahr`, `open3_datenquelle`, `open3_technik`, `open3_lizenz`, `open3_app_url`, `open3_datensatz_url`, `open3_versteckt` (bool), `open3_comments_heading`; Nonce/Caps/Sanitize.
15. **single-projekt.php (Etappe-1-Minimum):** Breadcrumb `Projekte / Typ`, H1, Excerpt, Aktionsbuttons (App-Link primär, Datensatz sekundär), Hero-Medium, Text + Steckbrief-Tabelle (Jahr/Datenquelle/Technik/Lizenz/Kontakt), Kommentare nur wenn vorhanden. `archive-projekt.php`/`taxonomy-projekttyp.php` minimal (Kicker, H1, Raster) – in Etappe 2 nach Prototyp verfeinert.
16. **inc/customizer.php:** `open3_sidebar_position` (rechts|links), `open3_hero_text`, `open3_projekte_intro_title/_text`, `open3_contact_email`, `open3_bluesky_url`.
17. **inc/redirects.php:** nur bei `is_404()`: `/sparpaket` → `/projekte/sparpaket`, `/projekte/linz-open-data` → `/projekte/linz-open-data-apps-und-visualisierungen`.
18. **CSS:** `design-system.css` = Kopie von `styles.css` mit: `@import` raus, `--color-divider: rgba(32,30,29,.4)` und rgba-Schatten (Custom Properties kennen keinen Fallback), Duplikate (Z. 66-71) raus, `text-wrap:pretty`, `a{color:var(--color-accent-700);text-decoration:underline}` + `a:hover{color:var(--color-accent-600)}`, `img{height:auto}`, rgba-Fallback-Zeile vor jedem `color-mix()`, `.input::placeholder`, `.nav*`/`.dialog*`/`.seg*` entfernt. `theme.css` = vollständige Chrome-/Start-A-Regeln aus der CSS-Spezifikation (Präfix `o3-`, ein Breakpoint 900 px für Spalten und Menü, `--o3-pad: clamp(16px,4vw,48px)`, Skiplinks, Media-Wrapper/Platzhalter, WP-Klassen `.screen-reader-text`/`align*`/`wp-block-*`/Kommentarformular, Print). `editor.css` für Editor-Parität.
19. **JS `main.js`:** Menü-Toggle (aria-expanded, Label Menü/Schließen, Escape, Schließen bei Navigation, Reset bei Resize), Explorer-Zyklus aus `#o3-explorer-data` (Bild/Platzhalter, Typ, Titel, Links; kein innerHTML; Button disabled bei < 2).
20. **Migration `inc/migration/`:** `Open3_Project_Migrator` (Optionen `dry_run`, `images local|remote|skip`, `menus`, `reset_templates`, `rollback`): alle Kindseiten von `projekte` → `set_post_type('projekt')`, `post_parent 0`, `menu_order` nach Prototyp-Reihenfolge, `_wp_page_template` merken + löschen, `projekttyp` nach Slug-Mapping (22 aus Prototyp + `sparpaket` → visualisierung), Beitragsbilder aus `/uploads/projekt-explorer/` (16 Dateien; `sparpaket` teilt `sparpaket11-14.png`), Sonderfälle (14991 `open3_versteckt`, 17419 App-Link → `/projekte/sparpaket`, 28824 Kommentare geschlossen + Überschrift „Alle 23 Einreichungen“), Elternseite 17418 → Entwurf `projekte-legacy`, Menüs „Hauptnavigation“/„Footer“ anlegen, Flush; Report-Tabelle; WP-CLI `wp open3 migrate-projekte` + Admin-Seite Werkzeuge → „open3 Migration“ (Nonce, manage_options).
21. **Repo/Dev:** `.gitignore`, `.gitattributes` (export-ignore für Handoff/dev/docs), `README.md`, `dev/blueprint.json` (de_AT, Optionen, Newsletter + Kadence installieren, Theme aktivieren, `dev/seed.php` ausführen), `dev/seed.php` (Kategorien, 12 echte + 50 Füll-Beiträge, 10 Seiten, 21 Grundlagen-Kindseiten, 23 Projekte mit Termen/Bildern, 23 Kommentare auf poetry-contest), `dev/blueprint-migration.json` (Legacy-Seiten seeden, Migrator laufen lassen), `dev/check-urls.sh`, `dev/lint.ps1`.

## Etappe 2 – Spezialseiten (Screens B–H) und Treemaps

- **archive-projekt.php / taxonomy-projekttyp.php:** Kicker `Projekte · n`, H1/Lede (Customizer), Filter-Chips als Links aus `get_terms` (inkl. Code), Raster `repeat(auto-fill,minmax(min(100%,230px),1fr))`, `gap:2px`, Divider-Hintergrund, Kachel-Hover `#fff2ef`.
- **single-projekt.php voll:** wie Etappe 1 + Bildunterschrift, zweispaltig Text/Steckbrief `minmax(min(100%,260px),1fr)`, „Verwandte Projekte“ (3 Karten gleicher Typ).
- **Treemaps:** `assets/js/treemap/` (4 Dateien; `gesamthaushalt` gepatcht: Radio-IDs `r-sq2/r-st2/r-sd2`, Bildpräfix → `window.o3TreemapBase`, `http://` → `https://`), `treemap.css` aus Legacy `style.css:1418-1593` fluid (`max-width:950px; width:100%`), `inc/treemap.php` enqueued nur für Slugs `sparpaket`/`wien-budget-2009-visualisierung` (Footer, Inline-Boot `DOMContentLoaded → init()/init2()`), `template-parts/projekt/treemap-controls.php` mit Suffix, Body-Klasse `no-sidebar`, Datenlinks/Downloads aus Legacy-Templates (https), moneypile als Bild ohne Link. Wörgl/Kremsmünster brauchen kein Sondertemplate.
- **page-grundlagen.php:** nummeriertes Verzeichnis der Kindseiten (`%02d`), Raster `minmax(min(100%,280px),1fr)`, Hover `#ae1800`; optional `--renumber-grundlagen` im Migrator.
- **page-uber-open3.php:** 3-Zellen-Raster (1 Open Society · 2 Open Government · 3 Open Data), Prosa, Services-Liste, „Auf dem Laufenden bleiben“.
- **page-openevents.php:** Termin-Meta auf Beiträgen (`open3_termin_datum/_ort/_link`), nächste Termine (Kategorie termine, Datum ≥ heute), Consent-Box für Kalender-Embed, Tabelle vergangener Termine (10) + „Alle n Einträge anzeigen“.
- openNetwork/Medienspiegel: `page.php`.

## Migration, URLs, Rollout

- URLs bleiben durch CPT-Rewrite: `/projekte`, `/projekte/{slug}` ×23, `/kategorie/*`, Feeds, Beiträge, Seiten. Theme-Redirects: `/sparpaket`, `/projekte/linz-open-data`. Pretty Links (`/gluecksspiel-karte`, `/ogdwien-finanzdaten`) unverändert. Nach Aktivierung: Permalinks speichern.
- Produktion: Backup → Exporte (`art_footer_content`, Widgets, Page-Templates, Redirection-Regeln) → Theme hochladen (`open3-v2`) → Dry-Run → aktivieren → Migration (`--images=local --menus --reset-templates`) → Menüs prüfen → `check-urls.sh` → WP-Paginate deaktivieren → nach 14 Tagen Alt-Theme + Legacy-Entwürfe löschen. Rollback: Alt-Theme aktivieren + `--rollback`.

## Verifikation

1. `dev/lint.ps1`: `php -l` (PHP 8.4.15) über alle PHP-Dateien.
2. Playground: `npx @wp-playground/cli@latest server --php=8.4 --wp=latest --port=9400 --login --mount-dir "<repo>/open3" "/wordpress/wp-content/themes/open3" --blueprint dev/blueprint.json`; `WP_DEBUG_LOG` prüfen.
3. Playwright-Screenshots 1280/390 für `/`, `/page/2`, Beitrag, `/kategorie/news`, `/?s=daten`, 404, `/projekte`, `/projekte/sparpaket`, `/projekte/poetry-contest`, `/grundlagen`, `/uber-open3`, `/openevents`; Vergleich mit Prototyp (Header 14px-Padding, 2px/1px-Linien, Teaser-Rhythmus 28px, Pagination, Footer-Raster).
4. `dev/check-urls.sh`: Status/Location-Matrix (23 Projekt-URLs, Archiv, Redirects, Kategorien, Feeds, 404-Probe).
5. A11y: Skiplinks sichtbar bei Fokus, Tab-Reihenfolge, Menü-Toggle per Tastatur/Escape, `aria-current`, axe-core; HTML-Validierung (keine doppelten IDs auf Sparpaket, `lang="de-AT"`).
6. Explorer: 24 Klicks zyklisch ohne Netzwerkanfrage; Newsletter-POST an Plugin; Migration im `blueprint-migration.json` (23 projekt, Slugs, 16+1 Thumbnails, 23 Kommentare, Redirect 301).

## Offene Punkte für den Auftraggeber

- Bilder für 6 Projekte ohne Legacy-Bild (Regierungsprogramm 2020-2024, Grünbuch, Einwände, Hypo Alpe Adria, Kremsmünster Haushalt, geocoder-php); opendata-buddy.png ist 129×129 (unscharf); Legacy-Bilder 222×129 für 340px-Sidebar zu klein → neue Screenshots empfohlen.
- `sparpaket` (14991) und `budgetvisualisierung-2011-2014` (17419) zusammenlegen?
- Newsletter-Box: nur E-Mail (Design) oder auch Vorname (`nn`).
- Analytics (Matomo self-hosted mit Consent) – nicht im Theme.
- „Neu im Blog“ zeigt auf Seite 1 die fünf Teaser doppelt (Design-Vorgabe; optional Offset 5).
- Englische Beiträge: Kategorie „English“ oder Sprachumschalter.
- Kontrast 3,4:1 für `#7d7979` (Ellipse, Leerzustand) und 3,8:1 für Akzent-Labels 14px (aktiver Nav-Link, Ghost-/Primär-Buttons) – akzeptieren oder `--color-accent-700` für Ghost-Buttons.
- Poetry Contest: Einreichungen geschlossen lassen (empfohlen) oder wieder öffnen.
- FeedBurner / google-site-verification noch in Verwendung? SEO-/OG-Plugin? Hosting: SSH/WP-CLI, PHP-Version.
- Steckbrief-Felder der 23 Projekte redaktionell nachtragen (Migration lässt sie leer).

## Umsetzungsstand (2026-09-08)

**Etappe 1 und Etappe 2 sind umgesetzt** (`open3/`, 58 PHP-Dateien, Assets, Migrator, Fixtures, Blueprints) und in WordPress Playground (PHP 8.4, WP latest, de_AT) verifiziert:

- Start A, Seite 2, Kategorie, Suche (mit/ohne Treffer), 404, Beitrag (Kommentare + Formular inkl. Honeypot/Zeitprüfung), Seite, Projekte-Archiv + Typ-Filter, Projekt-Detail mit Steckbrief und „Verwandte Projekte“, Sparpaket/Wien-Budget mit funktionierenden JIT-Treemaps (Layout-Wechsel, „Eine Ebene höher“), Grundlagen-Verzeichnis, Über open3, openEvents (kommende Termine, Consent-Box, Tabelle) – jeweils 1280 und 390 px, ohne horizontalen Überlauf, ohne JS-Fehler, ohne Theme-Notices im Debug-Log.
- Mobiles Menü (aria-expanded, Escape, Fokusrückgabe), Skiplinks (ausgeloggt: erster Tab „Zur Navigation springen“), Explorer-Zyklus (22 Einträge, umlaufend), Archivo als variable Schrift (400–800) geladen.
- Migration Ende-zu-Ende über `dev/blueprint-migration.json`: 23 Kindseiten → `projekt` in place, 17 Beitragsbilder, Poetry Contest mit 23 Einreichungen (geschlossen), Reihenfolge/Typen, Elternseite als Entwurf, `/sparpaket` und `/projekte/linz-open-data` → 301.
- wp-admin: Migrationsseite (Werkzeuge → open3 Migration, Testlauf), Steckbrief-Metabox, Termin-Metabox, Customizer-Sektion.

**Bekannte Punkte:** Playground-CLI 3.1 braucht Node 20–25 (siehe README); die Migrations-Testinstanz verlor nach dem Blueprint-Lauf die SQLite-Verbindung (Playground-intern, Frontend war zuvor korrekt); Legacy-Projektbilder (222×129) sind für die 340-px-Sidebar zu klein (unscharf); Kalender-URL für openEvents muss im Customizer hinterlegt werden; noch kein Git-Commit.
