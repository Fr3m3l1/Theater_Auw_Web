#!/usr/bin/env bash
#
# Baut das installierbare ZIP.
#
#   ./build/build.sh
#
# Ergebnis: build/dist/theater-auw-<version>.zip
#
# Bewusst reines bash ohne npm: das hier ist die Website eines Dorfvereins.
# Sie muss in fünf Jahren noch baubar sein, von wem auch immer sie erbt.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC="$ROOT/plugin/theater-auw"
STAGE="$ROOT/build/stage"
DIST="$ROOT/build/dist"

if [[ ! -f "$SRC/theater-auw.php" ]]; then
	echo "FEHLER: $SRC/theater-auw.php nicht gefunden." >&2
	exit 1
fi

VERSION="$(grep -m1 '^ \* Version:' "$SRC/theater-auw.php" | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')"
if [[ -z "$VERSION" ]]; then
	echo "FEHLER: Version konnte nicht aus dem Plugin-Header gelesen werden." >&2
	exit 1
fi

echo "→ Theater Auw $VERSION"

# --- 1. PHP-Syntax prüfen -------------------------------------------------
if command -v php >/dev/null 2>&1; then
	echo "→ PHP-Syntax prüfen"
	while IFS= read -r -d '' f; do
		php -l "$f" >/dev/null || { echo "FEHLER: Syntaxfehler in $f" >&2; exit 1; }
	done < <(find "$SRC" -name '*.php' -print0)
else
	echo "  (php nicht gefunden — Syntaxprüfung übersprungen)"
fi

# --- 2. Verbotene Zeichen -------------------------------------------------
# Schweizer Rechtschreibung: nirgends ein «ß».
echo "→ Schweizer Rechtschreibung prüfen"
if grep -rlP '\x{00DF}' "$SRC" 2>/dev/null | grep -q .; then
	echo "FEHLER: 'ß' gefunden — in der Schweiz immer 'ss':" >&2
	grep -rnP '\x{00DF}' "$SRC" >&2 || true
	exit 1
fi

# --- 2b. Tests ------------------------------------------------------------
if command -v php >/dev/null 2>&1; then
	echo "→ Tests"
	php "$ROOT/preview/test-css.php"      >/dev/null || { echo "FEHLER: CSS-Spezifitätstest fehlgeschlagen. Details: php preview/test-css.php" >&2; exit 1; }
	php "$ROOT/preview/test-phase.php"    >/dev/null || { echo "FEHLER: Phasentest fehlgeschlagen. Details: php preview/test-phase.php" >&2; exit 1; }
	php "$ROOT/preview/test-settings.php" >/dev/null || { echo "FEHLER: Einstellungstest fehlgeschlagen. Details: php preview/test-settings.php" >&2; exit 1; }
	php "$ROOT/preview/test-seiten.php"   >/dev/null || { echo "FEHLER: Test der Shortcode-Vorschlaege fehlgeschlagen. Details: php preview/test-seiten.php" >&2; exit 1; }
fi

# --- 3. Stagen ------------------------------------------------------------
echo "→ Dateien sammeln"
rm -rf "$STAGE"
mkdir -p "$STAGE/theater-auw"

# Der Ordner IM ZIP muss `theater-auw/` heissen, auch wenn die Datei
# theater-auw-1.0.0.zip heisst. Sonst installiert WordPress eine zweite Kopie,
# statt «Aktuelles durch Hochgeladenes ersetzen» anzubieten.
( cd "$SRC" && tar -cf - \
	--exclude='.DS_Store' \
	--exclude='*.map' \
	--exclude='node_modules' \
	--exclude='.git*' \
	. ) | ( cd "$STAGE/theater-auw" && tar -xf - )

# --- 4. Packen ------------------------------------------------------------
mkdir -p "$DIST"
ZIP="$DIST/theater-auw-$VERSION.zip"
rm -f "$ZIP"

echo "→ ZIP packen"
( cd "$STAGE" && zip -qr "$ZIP" theater-auw )

# --- 5. Prüfen ------------------------------------------------------------
echo "→ ZIP prüfen"
# Listing einmal einlesen. Nicht `unzip -l | grep -q` benutzen: grep -q steigt
# beim ersten Treffer aus, unzip bekommt SIGPIPE und `set -o pipefail` wertet
# die ganze Pipeline als Fehler — obwohl alles in Ordnung ist.
LISTING="$(unzip -Z1 "$ZIP")"

TOP="$(printf '%s\n' "$LISTING" | cut -d/ -f1 | sort -u)"
if [[ "$TOP" != "theater-auw" ]]; then
	echo "FEHLER: ZIP hat nicht genau ein Wurzelverzeichnis 'theater-auw':" >&2
	echo "$TOP" >&2
	exit 1
fi

for required in theater-auw/theater-auw.php \
	theater-auw/includes/class-tha-phase.php \
	theater-auw/assets/css/theater-auw.css \
	theater-auw/assets/js/theater-auw.js \
	theater-auw/public/views/page.php; do
	if ! printf '%s\n' "$LISTING" | grep -qx -- "$required"; then
		echo "FEHLER: $required fehlt im ZIP." >&2
		exit 1
	fi
done

rm -rf "$STAGE"

echo
echo "Fertig: $ZIP"
echo "  $(unzip -Z1 "$ZIP" | wc -l | tr -d ' ') Dateien, $(du -h "$ZIP" | cut -f1)"
echo
echo "In WordPress: Plugins → Installieren → Plugin hochladen → diese Datei wählen."
