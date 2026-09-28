# Theater Auw — neue Website

Die neue `/theater`-Seite für [stvauw.ch](https://stvauw.ch/theater/), gebaut auf dem Claude-Design-System *Theater Auw* und ausgeliefert als installierbares WordPress-Plugin.

**Die eine Idee:** eine Seite, vier Saison-Phasen. Sie wechseln über das Jahr — nach Kalender geplant oder von Hand im Backend geschaltet — und ändern dabei *Zustand und Betonung*, nie die visuelle Sprache.

| Phase | Wann | Akzent |
|---|---|---|
| **Vorbereitung** | vor dem Theater — wo wir jetzt stehen | neutral, Countdown auf die Premiere |
| **Vorverkauf** | Verkauf läuft (oder Countdown darauf) | rot, dominanter Ticket-CTA |
| **Spielzeit** | während der Aufführungen | Gold-Spotlight, «Heute Vorstellung» |
| **Danke** | nach der Derniere | grün, leise, Rückblick |

Tickets laufen vollständig über **Ticketleo**. Die Seite verkauft nie selbst — jeder CTA ist ein Handoff.

---

## Verzeichnisse

```
design-system/        importiertes Claude-Design-Projekt (Referenz)
plugin/theater-auw/   das WordPress-Plugin — die Quelle
preview/              Vorschau + Tests, rendert die ECHTEN Templates
build/                build.sh → dist/theater-auw-<version>.zip
docs/INSTALL-de.md    Anleitung für die Admin-Person
```

Die Vorschau bindet die Templates aus `plugin/theater-auw/public/views/` direkt ein und ersetzt nur die WordPress-Funktionen durch Attrappen (`preview/wp-stubs.php`). Es gibt also **keinen zweiten Nachbau**, der mit der Zeit von der Live-Seite abweichen könnte.

---

## Befehle

```bash
php preview/test-phase.php
```
Prüft die Phasenlogik: Schwellen, manuelle Übersteuerung, abgeleitete Zustände, Countdown — und die Zeitumstellung Ende Oktober, die mitten in die Saison fällt.

```bash
php preview/test-seiten.php
```
Prüft die Shortcode-Vorschläge an den echten Seitentiteln von stvauw.ch — «Anmeldung Sponsoring» bekommt keinen Galerie-Vorschlag, «Sponsoren» schon.

```bash
php preview/test-galerie.php
```
Prüft die Sponsorenwand: jede Kachel verlinkt ihr eigenes Bild gross, Porträts und Gruppenfotos bleiben Kacheln, und ein Bild ohne Adresse bekommt keinen toten Link.

```bash
php preview/render.php
```
Rendert `preview/{vorbereitung,vorverkauf,spielzeit,danke}.html`, jede zu einem Zeitpunkt, an dem sie auch wirklich gälte.

```bash
php preview/hostile.php > preview/hostile.html
```
Rendert die Seite in einen Container, der sich wie ein sehr meinungsstarkes Theme verhält (Georgia, 720px, `content-box`, `!important`-Resets). Die Abnahmehürde für die CSS-Abschottung.

```bash
php build/inline.php
```
Baut zwei eigenständige Dateien: `theater-auw-vorschau.html` (alle vier Phasen mit Umschalter, zum Anschauen) und `theater-auw-inline.html` (die Rückfallebene für einen HTML-Block).

```bash
./build/build.sh
```
Prüft PHP-Syntax und Schweizer Rechtschreibung, packt `build/dist/theater-auw-<version>.zip` und verifiziert die ZIP-Struktur.

Kein npm, kein Build-Toolchain. Das hier ist die Website eines Dorfvereins — sie muss in fünf Jahren noch baubar sein, von wem auch immer sie erbt.

---

## Entscheidungen, die nicht offensichtlich sind

**Serverseitiges PHP statt React.** Das UI-Kit im Design-System ist ein React-Prototyp mit Babel-im-Browser von einem CDN — eine Design-Vorlage, kein Auslieferungsformat. Die Seite ist statischer Inhalt aus rund zehn Optionen; Suchmaschinen und WhatsApp-Vorschauen brauchen Titel, Daten und Ort im HTML. JavaScript bleibt auf Countdown, Sticky-Leiste und den Bilddialog beschränkt, alles davon reiner Komfort.

**Keine Cascade Layers.** Ungelayerte Autoren-Stile schlagen *alle* gelayerten. Das Theme ist mit grosser Sicherheit ungelayert — in einem Layer würden wir jeden Konflikt verlieren. Stattdessen: `all: revert` als Grenze mit `:where()` (null Spezifität) und durchgehend doppelt geklasste Selektoren `.tha.tha …`, was (0,2,x) ergibt und `.entry-content h1` (0,1,1) schlägt.

**Nur Inhalt, kein eigener Header/Footer.** Die Theater-Seite bleibt Teil von stvauw.ch. Der Ticket-Knopf aus dem UI-Kit-Header wandert stattdessen in eine Sticky-Leiste, die erst nach dem Hero erscheint.

**Bilder aus der Mediathek, nicht mitgeliefert.** Die Fotos liegen bereits im WordPress des Vereins. Auswahl über den nativen Media-Picker, Ausgabe über `wp_get_attachment_image()` — damit kommen `srcset` und `loading="lazy"` von WordPress. Ohne Auswahl greifen gestaltete Platzhalter.

**Vergangene Daten pflegen sich selbst.** Eine Vorstellung, deren Datum vorbei ist, zeigt automatisch «Vorbei» und ist nicht mehr buchbar. Niemand muss am Sonntagmorgen Häkchen umstellen.

**Die Anmeldeseite wird umgestaltet, nicht umgebaut.** «Anmeldung Sponsoring» trägt ein echtes Formular, das Anmeldungen entgegennimmt — ein Shortcode-Rahmen würde dem Formular die Theme-Stile nehmen. Das Plugin erkennt die Seite deshalb über ihre Nummer, setzt ihr den dunklen Seitenkopf vor und übernimmt die Theater-Schrift; das Formular selbst bleibt unangetastet. Eine Anmeldefrist (dieses Jahr: 8. Oktober) schliesst beides automatisch: nach dem Stichtag verschwindet der Hinweis auf der Hauptseite, und das Formular wird ausgeblendet. Alles unter «Theater Auw → Sponsoring».

**Kleine Knöpfe, grosse Wirkung verlangen nach einer Frist.** Solange gesammelt wird, steht auf der Hauptseite eine dunkle Sektion «Sponsoren gesucht» mit einem Knopf auf die Anmeldeseite. Sie rechnet mit dem Kalender — niemand muss am Tag nach dem Stichtag daran denken, sie abzuschalten.

**`ß` ist ein Build-Fehler.** `build.sh` bricht ab, wenn im Quelltext ein Eszett auftaucht, und der Sanitizer ersetzt es in allen Backend-Eingaben durch «ss». Schweizer Rechtschreibung, automatisch durchgesetzt.

**Ein Sponsorenlogo darf man gross ansehen.** In der Wand ist ein Logo eine Reihe; ein Klick öffnet es in einem `<dialog>`. Escape, Fokussperre und das Zurückgeben des Fokus bringt der Browser mit — eigenes JavaScript füllt nur Bild und Beschriftung. Der Klick ist ein echter `<a href>` auf das Bild in voller Grösse: ohne JavaScript öffnet er das Bild wie jeder andere Link. Die Grösse des Dialogs kommt aus den Massen der Datei, die im Link stehen — ein 300px-Logo wird nicht auf Kastenbreite gezogen, denn grösser als die Datei wird es nur unscharf. Nur Logo-Gruppen sind anklickbar; Porträts und Gruppenfotos sind in der Kachel schon zu sehen.

---

## Offen

- **Ticketleo-URL 2026.** Steht als Feld im Backend; bis dahin zeigen die Knöpfe «Ticketlink folgt» und sind nicht klickbar. Die 2025er-URL (`reservation.ticketleo.com/event/33310/karriere`) ist als Beispiel im Hilfetext hinterlegt.
- **Fotos.** Über den Mediathek-Picker auszuwählen. `stvauw.ch` war aus der Entwicklungsumgebung nicht erreichbar (Bot-Schutz), die vorhandenen Bild-URLs konnten also nicht ausgelesen werden — über den Picker ist das aber auch nicht nötig.
- **Aktives Theme unbekannt.** Deshalb `Breite` und `Abschottung` als Einstellungen: eine Überraschung ist ein Auswahlfeld, kein neues ZIP.
- **Ticketleo bietet inzwischen ein eigenes WordPress-Plugin** (`ticketleo-events`, v1.1, sehr jung) mit Live-Restplätzen. Bewusst keine Abhängigkeit — die Spieldaten-Sektion ist aber so gebaut, dass dessen Block später danebenstehen kann.
