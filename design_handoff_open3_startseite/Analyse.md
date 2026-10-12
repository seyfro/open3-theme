# open3.at – Übergabe-Paket für die WordPress-Theme-Umsetzung

Stand: 8. September 2026. Entwurf: `open3 Neuentwurf.dc.html` (Seiten-Schalter oben, Desktop/Mobil-Umschaltung). Visuelle Grundlage: Design-System „Modernist“ (`_ds/…/styles.css`).

## 1. Ist-Analyse des bestehenden Themes (`uploads/open3`)

| Befund | Konsequenz |
| --- | --- |
| Artisteer 2.4-Export (2010) auf Kubrick-Basis, XHTML 1.0 Transitional, `X-UA-Compatible: IE=EmulateIE7`, eigene `style.ie6.css`/`style.ie7.css`, `swfobject.js` | Komplett ersetzen, nichts übernehmen |
| Fixes 1000px-Layout, kein Viewport-Meta, Ecken/Schatten als 9 Divs + PNG-Sprites pro Box | Fluides Layout, CSS Grid/Flex, keine Bild-Deko |
| Hardcodierte Page-IDs (`is_page('14991')`, `is_page('17913')`) laden Treemap-Scripts für Sparpaket/Wien-Budget im Header | Als Page-Templates bzw. Blöcke mit eigenem Script-Enqueue umsetzen (`wp_enqueue_script`, nur auf diesen Seiten) |
| Veraltete PHP-Funktionen (`get_settings`, `eregi`, `get_magic_quotes_gpc`) | Nicht PHP-8-fähig; Footer-Options-Panel durch Customizer/Block-Theme-Settings ersetzen |
| Open-Graph-Bilder auf Fremddomain (`ihrwebprofi.at`), Piwik über `mapsmarker.com` | OG via SEO-Plugin; Analytics DSGVO-konform selbst hosten oder streichen |
| Projekt-Explorer: 16 Projekte als PHP-String-Array, Zufallsauswahl, defekte Umlaute | Custom Post Type `projekt` + Widget/Block „Projekt-Explorer“ (siehe 4.) |
| Icons als PNG (`postdateicon.png` …), Buttons als Grafik | Lucide-Icons inline SVG, Buttons per CSS |
| Skiplinks, Sitemap, CC-BY-Hinweis, Tagline | Beibehalten |

## 2. Informationsarchitektur (neu)

Hauptnavigation: Blog · Projekte · Grundlagen · openEvents · openNetwork · Medienspiegel · Über open3 · **Newsletter** (Primär-Button).
Sidebar (auf allen Seiten, rechts, Tweak „links“ möglich): Suche · Projekt-Explorer · Neu im Blog · Kategorien · Newsletter-Box.
Footer: Wortmarke + Tagline · Sitemap · Kontakt (E-Mail, Bluesky, RSS) · Lizenz/Impressum/Datenschutz.

Entfernt: „Suche“ und „Impressum“ als Hauptmenüpunkte (Suche in Sidebar, Impressum im Footer), Sitemap-Dump in der Sidebar, Twitter/Facebook/Delicious-Widgets.

## 3. Template-Zuordnung

| Entwurf-Screen | WordPress-Template | Quelle im alten Theme |
| --- | --- | --- |
| Start A (Teaser-Liste, geregelt) / Start B (Aufmacher + Karten) | `home.php` / `index.php` – Teaser statt Volltext (`the_excerpt`, ~40 Wörter), 5–10 Beiträge, Pagination | `index.php` |
| Beitrag | `single.php` – Breadcrumb, Kategorie-Tag, Datum, Autor, Inhalt (max. 66ch), Themen-Tags, Vor/Zurück, Kommentare | `single.php`, `comments.php` |
| Projekte-Übersicht | `archive-projekt.php` – Filter nach Taxonomie `projekttyp`, Raster mit 2px-Rastern | `page.php` + Menü-Unterseiten |
| Projekt-Detail | `single-projekt.php` – Titel, Kurztext, Aktionen, Hero-Bild, Text + Steckbrief-Tabelle, verwandte Projekte | `sparpaket.php`, `wienbudget2009.php`, `woergl-visualisierungen.php`, `poetry-slam.php` |
| Grundlagen | `page-grundlagen.php` – nummeriertes Verzeichnis der Kindseiten (`wp_list_pages child_of`) | `page.php` |
| Über open3 | `page.php` – 3-Zellen-Raster (Society/Government/Data), Text, Services | `page.php` |
| openEvents | `page-openevents.php` – Nächste Termine (Kategorie „Termine“), Kalender-Embed mit Consent, Archiv als Tabelle | `page.php` |
| 404 / Suche | `404.php`, `search.php` – wie Start A ohne Hero | `404.php`, `search.php` |

## 4. Datenmodell

- **CPT `projekt`**: Titel, Kurztext (Excerpt), Beitragsbild (16:9), Felder: Jahr, Datenquelle, Technik, Lizenz, Link zur Anwendung, Link zum Datensatz. Taxonomie `projekttyp`: Visualisierung, App, Umfrage, Dokumentation, Veranstaltung, Code.
- **Projekt-Explorer**: Block/Widget, zeigt ein zufälliges `projekt` (Bild 16:9, Typ, Titel, „Nächstes Projekt“ per JS ohne Reload, „alle anzeigen“). Ersetzt `projekt-explorer.php`.
- **Termine**: weiterhin Kategorie „Termine“; optional Felder Datum/Ort/Anmeldelink für die openEvents-Liste.
- **Redirects**: alte Projekt-Slugs `/projekte/...` bleiben erhalten (CPT-Rewrite `projekte`).

## 5. Design-Tokens (aus `styles.css`)

- Farben: Grund `#f3f2f2`, Fläche `#eae9e9`, Text `#201e1d`, Akzent `#ec3013` (Hover `#dd2b0f`, Pressed/Kleintext `#ae1800`), Divider `rgba(32,30,29,.4)`; Neutral-Ramp 100–900.
- Schrift: Archivo (400/600/800) für alles. Headings 800, Letter-Spacing −0.02em, Zeilenhöhe 1.06 (Display) / 1.15 (H2).
- Body 17px/28px, Fließtext max. 66ch. Kicker 12px, 0.08em, Versalien, `#ae1800`.
- Abstände: 4/8/12/16/24/32px; Sektionen 28px-Rhythmus. Radius 0. Trennlinien 2px (Sektionen), 1px (Listen).
- Bilder: alle Fotos in `.grayscale` (filter grayscale + contrast 1.08).
- Zustände: Hover Akzent-Ramp, `:focus-visible` 2px Akzent-Outline, Disabled 45 % Opazität.

## 6. Responsive Verhalten

- Container max. 1280px, Seitenrand `clamp(16px, 4vw, 48px)`.
- Zweispaltig (Main ≥560px, Sidebar 260–340px) bis ca. 900px; darunter Sidebar unter dem Inhalt, Navigation als „Menü“-Button mit vertikaler Liste.
- Raster: `repeat(auto-fill, minmax(230px, 1fr))` (Projekte), `minmax(250px,1fr)` (Karten), `minmax(200px,1fr)` (Footer).
- Touch-Ziele ≥44px (Menü-Einträge 14px Padding, Buttons 36px + Umgebung).

## 7. Umsetzungs-Checkliste

1. Neues Theme (klassisch oder Block-Theme) mit `theme.json` aus Tokens in 5.
2. `styles.css` des Design-Systems als Basis; keine Bild-Sprites.
3. CPT `projekt` + Taxonomie + Import der 23 Projekte (Bilder aus `wp-content/uploads/projekt-explorer/`).
4. Treemap-Seiten: Scripts per `wp_enqueue_script` nur im jeweiligen Template; `excanvas.js` entfernen.
5. Skiplinks, `lang="de-AT"`, Landmark-Elemente (`header/nav/main/aside/footer`), Kontrast ≥4.5:1.
6. Kommentar-Formular auf `comment_form()`; Spamschutz.
7. Newsletter-Formular (Sidebar + Nav-Button) an bestehenden Newsletter-Dienst anbinden.
8. OG/Twitter-Cards via SEO-Plugin, statisches `og:image` = neues quadratisches Logo.
9. Analytics DSGVO-konform (Matomo self-hosted mit Consent) oder entfallen.
10. Altes Theme, `style.ie*.css`, `swfobject.js`, Delicious/Twitter-Widgets löschen.

## 8. Offen / zu klären

- Logo: aktuell als Wortmarke „Open3“ mit rotem „3“ nachgesetzt; Original-Logodatei (SVG) wäre besser.
- Beitrags- und Projektbilder: Platzhalter im Entwurf per Drag-and-drop befüllbar.
- Zweisprachigkeit (einzelne Beiträge sind englisch): Kategorie „English“ oder Sprachumschalter?
- openNetwork und Medienspiegel: eigene Templates oder Standard-Seite?
