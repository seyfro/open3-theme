#!/usr/bin/env bash
# Baut dist/open3-v2.zip aus dem committeten Stand von open3/ (Ordnername im Zip: open3-v2/).
# export-ignore aus .gitattributes greift; open3/dev/ wird zusätzlich explizit entfernt.
# Aufruf: dev/build-zip.sh [ref]   (Standard: HEAD)

set -euo pipefail

cd "$(dirname "$0")/.."
REF="${1:-HEAD}"
OUT_DIR="dist"
OUT="$OUT_DIR/open3-v2.zip"
PREFIX="open3-v2"

mkdir -p "$OUT_DIR"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

git archive --format=tar --prefix="$PREFIX/" "$REF:open3" | tar -x -C "$TMP"
rm -rf "$TMP/$PREFIX/dev"

rm -f "$OUT"
if command -v zip >/dev/null 2>&1; then
  (cd "$TMP" && zip -qr "$OLDPWD/$OUT" "$PREFIX")
elif command -v powershell.exe >/dev/null 2>&1; then
  SRC="$TMP/$PREFIX"
  DEST="$PWD/$OUT"
  if command -v cygpath >/dev/null 2>&1; then
    SRC="$(cygpath -w "$SRC")"
    DEST="$(cygpath -w "$DEST")"
  fi
  powershell.exe -NoProfile -Command "Compress-Archive -Path '$SRC' -DestinationPath '$DEST' -Force"
else
  echo "Weder zip noch powershell.exe gefunden." >&2
  exit 1
fi

echo "Erstellt: $OUT ($(du -h "$OUT" | cut -f1))"
