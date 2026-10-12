# open3-theme

Neues WordPress-Theme für [open3.at](https://www.open3.at) – Netzwerk zur Förderung von OpenSociety, OpenGov und OpenData in Österreich. Ersetzt das Artisteer-Theme von 2010 durch ein responsives, block-editor-kompatibles Classic-Theme mit eigenem Inhaltstyp „Projekt“.

- Textdomain `open3`, deutsche Quellstrings, PHP ≥ 8.1, WordPress ≥ 6.4
- Kein Build-Schritt: reines CSS/JS, Archivo als variable WOFF2 selbst gehostet
- Lizenz GPL-2.0-or-later (Theme); JIT BSD-3-Clause, Archivo OFL (siehe `open3/LICENSES/`)

## Struktur

```
open3/                     Theme (wird als „open3-v2“ ausgeliefert)
  inc/                     setup, enqueue, cleanup, nav, template-tags, customizer,
                           cpt-projekt, meta-projekt, explorer, comments, redirects, treemap
  inc/migration/           Open3_Project_Migrator, data.php, cli.php, admin.php
  template-parts/          header/, content/, sidebar/, projekt/
  assets/                  css/, js/, fonts/, img/
  dev/seed.php             Playground-Fixtures (nicht im Dist-Paket)
  languages/open3.pot
dev/                       blueprint.json, blueprint-migration.json, check-urls.sh, lint.ps1, build-zip.sh
docs/plan/                 Planung (00 Plan, 01 Architektur, 02 CSS, 03 Migration/Verifikation)
design_handoff_open3_startseite/   Design-Prototyp und Design-System (Referenz, nicht ausgeliefert)
```

## Lokal ausführen (WordPress Playground)

```powershell
npx @wp-playground/cli@latest server `
  --php=8.4 --wp=latest --port=9400 --login `
  --mount-dir "C:/Users/rober/Documents/Git Repos/open3-theme/open3" "/wordpress/wp-content/themes/open3" `
  --blueprint "C:/Users/rober/Documents/Git Repos/open3-theme/dev/blueprint.json"
```

`dev/blueprint.json` setzt Sprache `de_AT`, Optionen (Tagline, 5 Beiträge/Seite, Permalinks `/%year%/%monthnum%/%postname%`, Kategorie-Basis `kategorie`), installiert Newsletter + Kadence Blocks, aktiviert das Theme und legt über `open3/dev/seed.php` Fixtures an: 5 Kategorien, 62 Beiträge (12 echte + 50 Füllbeiträge, einer mit 3 Kommentaren), 9 Seiten (+ 21 Grundlagen-Kindseiten; `projekte` wird nicht als Seite angelegt, das Archiv gehört dem CPT), 23 Projekte mit Projekttypen, Steckbrief-Meta, Bildern (Download von open3.at, falls Netzwerk) und 23 Einreichungen auf `poetry-contest`, Menüs „Hauptnavigation“/„Footer“.

`dev/blueprint-migration.json` legt stattdessen die Legacy-Struktur (Seite `projekte` + 23 Kindseiten inkl. Page-Templates) an und lässt den Migrator mit `images=remote`, `menus`, `reset_templates` laufen; die Ausgabe enthält die Report-Tabelle und eine Zusammenfassung.

Wird `--mount-dir` von der installierten CLI-Version nicht akzeptiert, unter Git Bash `--mount="/c/Users/.../open3:/wordpress/wp-content/themes/open3"` verwenden.

Hinweise aus der Praxis (Stand September 2026, `@wp-playground/cli` 3.1.x):

- Die CLI braucht **Node 20–25**. Unter Node 26 fehlt dem nativen Modul `fs-ext-extra-prebuilt` der Prebuilt und der Start bricht ab. Abhilfe ohne Systemänderung: `node.exe` aus dem Node-22-Zip (nodejs.org/dist/latest-v22.x) entpacken und damit `node_modules/@wp-playground/cli/wp-playground.js server …` starten (`npm i @wp-playground/cli --ignore-scripts` in einem Arbeitsordner).
- Unter Git Bash vorher `export MSYS_NO_PATHCONV=1`, sonst wird `/wordpress/wp-content/themes/open3` in einen Windows-Pfad umgeschrieben. Dasselbe gilt für `curl`-Aufrufe mit URL-Pfaden.
- `--login` setzt beim ersten Request Cookies und antwortet mit 302 auf dieselbe URL; `curl` daher mit Cookie-Jar (`-c/-b`) oder im Browser testen.
- Die Playground-Site liegt unter `%LOCALAPPDATA%\Temp\node.exe-playground-cli-site-*\wordpress\` (dort auch `wp-content/debug.log`).

## Prüfen

| Was | Befehl |
| --- | --- |
| PHP-Syntax | `pwsh dev/lint.ps1` |
| JSON | `node -e "JSON.parse(require('fs').readFileSync('open3/theme.json','utf8'))"` |
| URL-Matrix | `bash dev/check-urls.sh http://127.0.0.1:9400` (23 Projekt-URLs, Archiv, 301-Redirects, Kategorien, Feeds, 404-Probe) |
| Dist-Zip | `bash dev/build-zip.sh` → `dist/open3-v2.zip` |

## Migration der Projekte

Die 23 Kindseiten der Seite „projekte“ werden **in place** zum Inhaltstyp `projekt` konvertiert (IDs, Kommentare, Meta bleiben; Rollback-Meta `_open3_migrated_from_parent`, `_open3_legacy_template`, `_open3_legacy_menu_order`). Die Elternseite wird zum Entwurf `projekte-legacy`.

```
wp open3 migrate-projekte --dry-run
wp open3 migrate-projekte --images=local --menus --reset-templates
wp open3 migrate-projekte --rollback
```

- `--images=local|remote|skip`: Beitragsbilder aus `/wp-content/uploads/projekt-explorer/` (lokal) oder von `https://www.open3.at/...` (remote); idempotent über Anhang-Meta `_open3_source_file`; `sparpaket` und `budgetvisualisierung-2011-2014` teilen `sparpaket11-14.png`.
- `--menus`: legt „Hauptnavigation“ (Blog, Projekte, Grundlagen, openEvents, openNetwork, Medienspiegel, Über open3) und „Footer“ (+ Newsletter) an und weist `primary`/`footer` zu.
- `--reset-templates`: entfernt `sparpaket.php`, `wienbudget2009.php`, `woergl-visualisierungen.php`, `poetry-slam.php`, `delicious.php`, `open3.php` von allen Seiten.
- Sonderfälle: `sparpaket` → versteckt (`open3_versteckt`), `budgetvisualisierung-2011-2014` → App-Link `/projekte/sparpaket` + Datensatz-Link, `wien-budget-2009-visualisierung` → Datensatz-Link, `poetry-contest` → Kommentare geschlossen, Überschrift „Alle 23 Einreichungen“.

Ohne WP-CLI: Werkzeuge → „open3 Migration“ (gleiche Optionen als Checkboxen, Report-Tabelle, Button „Projekttypen anlegen“).

Weiterleitungen (nur bei 404, `inc/redirects.php`): `/sparpaket` → `/projekte/sparpaket`, `/projekte/linz-open-data` → `/projekte/linz-open-data-apps-und-visualisierungen`.

## Rollout (Kurzfassung)

1. Backup (DB + `wp-content/`), Exporte sichern (Footer-Option `art_footer_content`, Widgets, Page-Templates, Redirection-Regeln).
2. Theme als `open3-v2` hochladen (`dev/build-zip.sh`), Alt-Theme `open3` unangetastet lassen.
3. Theme `open3-v2` aktivieren (der Befehl `wp open3` wird erst vom neuen Theme registriert), dann `wp open3 migrate-projekte --dry-run` prüfen.
4. `wp open3 migrate-projekte --images=local --menus --reset-templates` – Report lesen.
5. Einstellungen → Permalinks → Speichern; Menüs kontrollieren; `dev/check-urls.sh https://www.open3.at`.
6. WP-Paginate deaktivieren; nach 14 Tagen ohne Rollback Alt-Theme und `-legacy`-Entwürfe löschen.

Rollback: zuerst – noch mit aktivem `open3-v2` – `wp open3 migrate-projekte --rollback` (oder Werkzeuge → „open3 Migration“ mit Rollback), dann Alt-Theme aktivieren, Permalinks speichern.

## Offene Punkte

- 6 Projekte ohne Bild (Regierungsprogramm, Grünbuch, Einwände, Hypo, Kremsmünster Haushalt, geocoder-php); Legacy-Bilder sind 222×129 px (unscharf bei 340 px), `opendata-buddy.png` ist quadratisch.
- Zusammenlegung von `sparpaket` und `budgetvisualisierung-2011-2014` (Kundenentscheidung).
- Steckbrief-Felder (Jahr, Datenquelle, Technik, Lizenz, Links) der 23 Projekte sind redaktionell zu füllen.
- Live-Slugs der 21 Grundlagen-Kindseiten liegen nicht im Handoff vor; die Fixtures leiten sie aus den Titeln ab.
- `moneypile.php` (403) nur als Bild; Analytics, FeedBurner, `google-site-verification`, SEO/OG-Tags, Newsletter-Vornamensfeld: Kundenentscheidung.
- Ajax Search Pro kann auf Produktion das Sidebar-Suchformular umgestalten – nur live prüfbar.
