# Handoff: open3.at – Startseite (Variante A) als WordPress-Theme

## Overview
Neue responsive Startseite für open3.at (Netzwerk zur Förderung von OpenSociety, OpenGov und OpenData in Österreich). Ersetzt das Artisteer-Theme von 2010 (fixe 1000px, XHTML, IE7-Modus). Variante A: Tagline-Hero, geregelte Teaser-Liste der neuesten Blogbeiträge, Sidebar rechts (Suche, Projekt-Explorer, Neu im Blog, Kategorien, Newsletter), Footer mit Sitemap/Kontakt/Lizenz.

## About the Design Files
Die Dateien in diesem Paket sind **Design-Referenzen in HTML** – ein klickbarer Prototyp, der Aussehen und Verhalten zeigt, kein Produktionscode. Aufgabe: dieses Design als **WordPress-Theme** nachbauen (klassisches Theme mit `home.php`/`index.php`, `header.php`, `sidebar.php`, `footer.php` oder Block-Theme mit `theme.json` + Templates). Bestehende WordPress-APIs verwenden (`the_excerpt`, `wp_nav_menu`, `get_search_form`, `wp_list_categories`, `paginate_links`, `comment_form`). Das alte Theme liegt zur Referenz in `legacy_theme/`.

- `open3 Neuentwurf.dc.html` – der Prototyp (Seiten-Schalter oben ist Review-Chrome, nicht Teil des Designs; Screen „Start A“ ist Gegenstand dieses Handoffs). Öffnen im Browser; benötigt `support.js`, `image-slot.js` und `_ds/`.
- `_ds/modernist-…/styles.css` – Token-Stylesheet und Komponentenklassen (`.btn`, `.tag`, `.card`, `.input`, `.field`, `.table`). Kann 1:1 als Theme-Basis-CSS übernommen werden.

## Fidelity
**High-fidelity.** Farben, Typografie, Abstände, Linienstärken und Zustände sind final. Pixelgenau nachbauen; Werte aus `styles.css` als CSS-Variablen übernehmen. Bilder sind Platzhalter (16:9 bzw. 4:3) – im Theme Beitragsbilder/Projektbilder aus der Mediathek, immer in Graustufen.

## Screens / Views

### 1. Header / Navigation
- **Layout**: volle Breite, `border-bottom: 2px solid var(--color-divider)`, `padding: 14px clamp(16px,4vw,48px)`, Flex, `align-items:center`, `gap:24px`, `flex-wrap:wrap`. Seiten-Container max. 1280px, zentriert, Hintergrund `#f3f2f2`.
- **Wortmarke** (links, `margin-right:auto`, Link zur Startseite): „Open3“ – Archivo 800, 26px, `letter-spacing:-0.02em`, `line-height:1`; die „3“ in `#ec3013`. Daneben (baseline-aligned, `gap:10px`): „Government · Data · Society“, 10px, `letter-spacing:0.12em`, Versalien, `#605d5d`.
- **Navigation** (Desktop): Links Blog · Projekte · Grundlagen · openEvents · openNetwork · Medienspiegel · Über open3. 14px, Archivo 400, Farbe Text, `padding:4px 0`, `gap:20px`. Aktiv: Farbe `#ec3013` + `border-bottom:2px solid #ec3013`. Hover: Farbe `#ec3013`.
- **Primär-Button** „Newsletter“: `.btn.btn-primary` (Fill `#ec3013`, Text `#f3f2f2`, Radius 0, Label linksbündig). Hover `#dd2b0f`, Active `#ae1800`.
- **Mobil** (<~900px): Navigation und Button ausblenden, stattdessen `.btn.btn-secondary` „Menü“ / „Schließen“. Geöffnet: vertikale Liste unter dem Header, jeder Eintrag `padding:14px 16px`, 16px, `border-bottom:1px solid var(--color-divider)`; darunter Primär-Button „Newsletter abonnieren“ mit `margin:12px 16px`. Ganzes Menü mit `border-bottom:2px`.

### 2. Hero (nur Startseite)
- `padding:40px 0 24px`, `border-bottom:2px solid var(--color-divider)`.
- **H1** (Tagline aus `bloginfo('description')`): „Netzwerk zur Förderung von OpenSociety, OpenGov und OpenData in Österreich“ – Archivo 800, `font-size:clamp(30px,4vw,46px)`, `line-height:1.06`, `letter-spacing:-0.02em`, `max-width:22ch`, `margin:0 0 14px`.
- **Absatz**: „Open3 ist ein offenes, gemeinnütziges Netzwerk. Seit 2010 setzen wir anwendungsorientierte Projekte um, sammeln Grundlagenwissen und vernetzen Politik, Verwaltung, Wirtschaft und Bevölkerung.“ – 16px/28px, `#444141`, `max-width:58ch`.

### 3. Beitragsliste
- Kopfzeile `padding:28px 0 0`, Flex space-between, baseline: Kicker „Blog · Neueste Beiträge“ (12px, `letter-spacing:0.08em`, Versalien, `#ae1800`) und Link „RSS-Feed“ (13px).
- **Teaser** (`<article>`), 5 pro Seite (Einstellung „Beiträge pro Seite“): `padding:28px 0`, zwischen Teasern `border-bottom:2px solid var(--color-divider)` (letzter ohne).
  - Meta-Zeile: Flex, `gap:12px`, 12px, `#605d5d`, `font-feature-settings:"tnum"`: Datum („15. Juli 2025“, `get_the_date('j. F Y')`) + Kategorie als `.tag.tag-accent` (Fill `#fff2ef`, Text `#7c1405`, 11px, `padding:3px 10px`).
  - **H2** Titel (Link, Farbe Text, keine Unterstreichung): Archivo 800, `clamp(20px,2.2vw,26px)`, `line-height:1.15`, `letter-spacing:-0.01em`, `margin:10px 0 8px`, `max-width:34ch`.
  - Excerpt: 15px/26px, `#444141`, `max-width:62ch`, `margin:0 0 12px`, ca. 40 Wörter (`excerpt_length`).
  - Aktionszeile: Flex `gap:16px`, `.btn.btn-ghost` „Weiterlesen →“ (Text `#ec3013`, Hover Tint 10 %), daneben Autorname 12px `#605d5d`.
- **Pagination**: `padding:28px 0 48px`, Flex `gap:4px`, `tnum`. Label „Seite“ (12px Kicker-Stil, `#605d5d`, `margin-right:12px`), aktuelle Seite als Block `padding:6px 12px`, Fill `#ec3013`, Text `#f3f2f2`, 13px; andere Seiten Links 13px `padding:6px 12px`; Ellipse „…“ `#7d7979`; „Ältere →“. Mit `paginate_links()` erzeugen.

### 4. Sidebar (`<aside>`)
- Desktop: `flex:1 1 260px`, `max-width:340px`, `border-left:2px solid var(--color-divider)`, `padding-left:40px`; Main `flex:1 1 560px`, `padding-right:40px`. Blöcke als Flex-Column, `gap:24px`; jeder Block ab dem zweiten `border-top:2px solid var(--color-divider)`, `padding-top:16px`. Mobil: unter dem Inhalt, volle Breite, `border-top:2px`.
- **Suche** (`padding-top:40px`): Label „Suche“ (12px Kicker-Stil, `#605d5d`), darunter Flex `gap:8px`: `.input` (Placeholder „Beiträge, Projekte, Grundlagen …“, `flex:1`) + `.btn.btn-secondary` „Suchen“. Mit `get_search_form()` (Filter für Markup).
- **Projekt-Explorer**: Kopf Flex space-between: Kicker „Projekt-Explorer“ (`#ae1800`) und Link „alle anzeigen“ (12px). Bild 16:9, `border:2px solid var(--color-divider)`, `.grayscale` (Filter `grayscale(1) contrast(1.08)`), verlinkt. Darunter Typ (10px, `letter-spacing:0.1em`, Versalien, `#ae1800`, `margin-top:12px`), Titel (Archivo 800, 16px/1.25, `margin-top:4px`, Link, Farbe Text), `.btn.btn-ghost` „Nächstes Projekt →“ (`margin-top:8px`). Datenquelle: Custom Post Type `projekt` (zufälliger Eintrag; „Nächstes“ per JS ohne Reload, z. B. REST `/wp-json/wp/v2/projekt?per_page=1&orderby=rand`). Zu ersetzen: `legacy_theme/projekt-explorer.php` (hartcodiertes Array).
- **Neu im Blog**: Kicker, dann 8 Einträge: Grid `52px minmax(0,1fr)`, `gap:10px`, `padding:9px 0`, `border-bottom:1px solid var(--color-divider)`, 14px/20px; linke Spalte Monat/Jahr „07/25“ (12px, `#605d5d`, `tnum`). Hover Farbe `#ae1800`.
- **Kategorien**: Kicker, dann Einträge Flex space-between, `padding:8px 0`, `border-bottom:1px`, 14px; Zähler rechts `#605d5d`, `tnum`. Inhalt: News 147 · Termine 55 · Studien/Materialien 30 · Medienspiegel 5 · mehrGRIPS 2 (`wp_list_categories show_count`).
- **Newsletter-Box**: Fill `#ec3013`, Text `#f3f2f2`, `padding:20px`, `margin-bottom:48px`. Titel „Newsletter“ Archivo 800 22px/1.1, Text „Neue Beiträge, Termine und Projekte per E-Mail.“ 14px/22px, `.input` (Hintergrund `#f3f2f2`, Placeholder „E-Mail-Adresse“, `margin-bottom:8px`), Button „Abonnieren“: Ghost-Variante mit `color:#f3f2f2; border:1px solid #f3f2f2; width:100%; justify-content:flex-start`.

### 5. Footer
- `border-top:2px solid var(--color-divider)`, `padding:32px clamp(16px,4vw,48px) 40px`, Grid `repeat(auto-fit, minmax(min(100%,200px),1fr))`, `gap:28px`, 13px/22px.
- Spalte 1: Wortmarke (20px) + Tagline (`#444141`, `max-width:32ch`).
- Spalte 2 „Sitemap“ (Kicker 11px `#605d5d`): Blog, Projekte, Grundlagen, openEvents, openNetwork, Medienspiegel, Über open3, Newsletter (Links Farbe Text, keine Unterstreichung, `gap:2px`).
- Spalte 3 „Kontakt“: office@open3.at, Bluesky @seyfro.at, RSS-Feed.
- Spalte 4 „Lizenz“: „open3.at-Inhalte stehen, wenn nicht anders angegeben, unter der Creative Commons Namensnennung 3.0 Österreich Lizenz.“ (Link auf den Lizenznamen), darunter Impressum · Datenschutzerklärung (`gap:12px`, `margin-top:8px`).

## Interactions & Behavior
- Alle Links normale Seitennavigation (im Prototyp per Screen-Wechsel simuliert).
- Hover: Nav-Links → `#ec3013`; Listen-Links → `#ae1800`; Buttons laut `styles.css` (`.btn-primary:hover #dd2b0f`, `.btn-ghost:hover` 10 % Akzent-Tint, `.btn-secondary:hover` 7 % Ink-Tint). Keine Transitions nötig; falls, `120ms ease`.
- Fokus: `:focus-visible { outline:2px solid #ec3013; outline-offset:2px }` überall, nie Browser-Standard.
- Mobiles Menü: Toggle-Button, `aria-expanded`, Menü schließt beim Navigieren; Escape schließt.
- Projekt-Explorer „Nächstes Projekt“: zyklisch nächstes Projekt laden, Bild/Typ/Titel/Links tauschen, ohne Seiten-Reload.
- Formulare: Suche → `/?s=`; Newsletter → bestehender Newsletter-Dienst (Endpoint offen), HTML5-`type=email` + `required`.
- Skiplinks („Zur Navigation springen“, „Zum Inhalt springen“) aus dem alten Theme beibehalten, visuell versteckt bis Fokus.
- Responsiv: Container max. 1280px; Zweispaltigkeit bricht, sobald Main <560px oder Sidebar <260px Platz haben (≈ <900px). Grids mit `auto-fit/auto-fill`. Touch-Ziele ≥44px.

## State Management
- Keine Client-State-Verwaltung außer: `menuOpen` (mobil), aktuelles Explorer-Projekt (Index/ID).
- Serverseitig: Hauptquery (5 Beiträge, Seite n), Sidebar-Queries (8 neueste Titel, Kategorien mit Zähler, zufälliges `projekt`).
- Datenmodell neu: CPT `projekt` (Titel, Excerpt, Beitragsbild 16:9, Felder Jahr/Datenquelle/Technik/Lizenz/App-Link/Datensatz-Link), Taxonomie `projekttyp` (Visualisierung, App, Umfrage, Dokumentation, Veranstaltung, Code). Rewrite-Slug `projekte`, damit bestehende URLs `/projekte/…` erhalten bleiben.

## Design Tokens (aus `_ds/…/styles.css`)
- Farben: `--color-bg #f3f2f2`, `--color-surface #eae9e9`, `--color-text #201e1d`, `--color-accent #ec3013`, `--color-divider rgba(32,30,29,.4)`; Akzent-Ramp 100 `#fff2ef`, 200 `#ffe0d9`, 600 `#dd2b0f`, 700 `#ae1800`, 800 `#7c1405`; Neutral 600 `#7d7979`, 700 `#605d5d`, 800 `#444141`, 900 `#2d2b2b`.
- Schrift: Archivo (Google Fonts, 400/600/800) für Headings und Body. Headings 800.
- Typo-Skala: H1 `clamp(30px,4vw,46px)`/1.06; H2 Teaser `clamp(20px,2.2vw,26px)`/1.15; Body 16–17px/28px; Excerpt 15px/26px; Sidebar 14px/20px; Meta 12–13px; Kicker 12px 0.08em Versalien; Mikro-Label 10–11px 0.08–0.12em.
- Abstände: 4/8/12/16/24/32px (`--space-1…8`), Sektionsrhythmus 28px, Seitenrand `clamp(16px,4vw,48px)`, Spaltenabstand 40px.
- Linien: 2px zwischen Sektionen/Blöcken, 1px innerhalb von Listen. Radius 0 überall. Schatten keine (nur `--shadow-lg` am Mobil-Rahmen des Prototyps).
- Bilder: alle durch `.grayscale`.

## Assets
- Icons: Lucide (inline SVG), im Startscreen nur Pfeile „→“ als Text.
- Wortmarke als Text gesetzt; Original-Logo (`legacy_theme/images/header980neu.png`, „Open3 Government Data Society“) nur als Referenz – SVG-Logo vom Verein anfordern.
- Bilder: Platzhalter; Projektbilder existieren unter `wp-content/uploads/projekt-explorer/*.png` (Namen in `legacy_theme/projekt-explorer.php`).
- Favicon: `legacy_theme/images/favicon.ico` (neu erstellen).

## Files
- `open3 Neuentwurf.dc.html` – Prototyp (Screen „Start A“; weitere Screens B–H als Kontext).
- `support.js`, `image-slot.js` – Laufzeit für den Prototyp.
- `_ds/modernist-51a53f58-2579-44dc-819d-d5872d5348a3/styles.css` – Tokens + Komponentenklassen (übernehmen).
- `Analyse.md` – Ist-Analyse des alten Themes, IA, Template-Zuordnung aller Seiten, Checkliste.
- `legacy_theme/` – das bisherige WordPress-Theme (Referenz für Template-Hierarchie, Skiplinks, Piwik, Treemap-Einbindung).
