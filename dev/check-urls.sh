#!/usr/bin/env bash
# URL-Matrix nach docs/plan/03-migration-verifikation.md §2.
# Aufruf: dev/check-urls.sh <base-url>   (Standard: http://127.0.0.1:9400)
# Exit-Code 1, wenn eine Pflichtprüfung fehlschlägt. Zeilen mit „info“ zählen nicht.

set -u

BASE="${1:-http://127.0.0.1:9400}"
BASE="${BASE%/}"
FAIL=0
PASS=0

check() {
  local path="$1" expect="$2" location="${3:-}" mode="${4:-strict}"
  local out code redir status
  out=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' --max-time 20 "$BASE$path" 2>/dev/null) || out="000 "
  code="${out%% *}"
  redir="${out#* }"
  status="OK"
  if [ "$code" != "$expect" ]; then
    status="FAIL"
  elif [ -n "$location" ]; then
    case "$redir" in
      *"$location"|*"$location/") ;;
      *) status="FAIL" ;;
    esac
  fi
  if [ "$status" = "FAIL" ] && [ "$mode" = "info" ]; then
    status="info"
  fi
  if [ "$status" = "FAIL" ]; then
    FAIL=$((FAIL + 1))
  elif [ "$status" = "OK" ]; then
    PASS=$((PASS + 1))
  fi
  printf '%-5s %s %-90s -> %s %s\n' "$status" "$code" "$path" "${expect}${location:+ → $location}" "${redir:+[$redir]}"
}

PROJECTS=(
  regierungsprogramm-2020-2024
  gruenbuch-projekte
  opendata-buddy
  create-camp-gemeinsam-mit-wiener-linien-mit-schwerpunkt-verkehr
  budgetvisualisierung-2011-2014
  sparpaket
  einwaende-gegen-die-veroeffentlichung-von-open-data-und-bewaehrte-antworten-darauf
  visualisierung-der-hypo-alpe-adria-banken-staatshilfen
  hochwasserschutz-per-open-data-in-kremsmuenster
  wien-budget-2009-visualisierung
  umfrage
  woergl-haushalt-1949-2009-visualisierung
  ngo-umfrage
  ubahnaufzug-at
  datamaps-eu
  umfrage-potentiale-von-open-government-fur-osterreichische-nationalratsabgeordnete
  toilet-map-vienna
  gluecksspiel-opendata-mashup
  ozon-info-at-ozon-warnung-am-smartphone
  poetry-contest
  linz-open-data-apps-und-visualisierungen
  kremsmuenster-haushalt-2003-2012-visualisierung
  ogd-wien-adressservice-unterstuetzung-fuer-geocoder-php-bibliothek
)

echo "Basis: $BASE"
echo

echo "# Projekte (23)"
for slug in "${PROJECTS[@]}"; do
  check "/projekte/$slug" 200
done
check "/projekte" 200
check "/projekttyp/app" 200

echo
echo "# Weiterleitungen"
check "/sparpaket" 301 "/projekte/sparpaket"
check "/projekte/linz-open-data" 301 "/projekte/linz-open-data-apps-und-visualisierungen"

echo
echo "# Kategorien und Feeds"
for cat in news termine studien medienspiegel mehrgrips; do
  check "/kategorie/$cat" 200
done
check "/feed" 200
check "/kategorie/news/feed" 200

echo
echo "# Blog und Seiten"
check "/" 200
check "/page/2" 200
check "/suche" 200
check "/impressum" 200
check "/grundlagen" 200
check "/2023/11/opendata-buddy" 200
check "/2013/10/erstmals-in-oesterreich-hochwasserschutz-per-open-data" 200

echo
echo "# Nur Information (Produktion: Pretty Links / 46 Seiten)"
check "/gluecksspiel-karte" 200 "" info
check "/page/46" 200 "" info

echo
echo "# 404-Probe"
check "/diese-seite-gibt-es-nicht-$(date +%s)" 404

echo
echo "Bestanden: $PASS, fehlgeschlagen: $FAIL"
if [ "$FAIL" -gt 0 ]; then
  exit 1
fi
exit 0
