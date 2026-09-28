# Theater Auw — Installation in WordPress

Für jemanden, der **nur wp-admin** hat: kein FTP, kein Serverzugriff. Genau dafür ist das hier gebaut.

Du brauchst: **Administrator-Rechte** und die Datei `theater-auw-1.6.1.zip`.

---

## Warum ein Plugin?

Kurz: es ist der einzige Weg, der ohne Serverzugriff funktioniert **und** dir eine Oberfläche für die Phasen gibt.

| Weg | Warum nicht |
|---|---|
| Dateien in die Mediathek laden | WordPress lässt `.css` und `.js` gar nicht zu. Nur Bilder. |
| Theme-Dateien bearbeiten | Meist gesperrt, und beim nächsten Theme-Update ist alles weg. Ein Tippfehler legt die ganze Website lahm. |
| Child-Theme | Kann dasselbe, hängt aber am Theme. Beim Theme-Wechsel ist die Theater-Seite kaputt. |
| Alles in einen HTML-Block | Geht, aber ohne Backend: die Phase müsste im Code umgestellt werden. Als Notlösung vorbereitet — siehe unten. |
| **Plugin hochladen** | **Eigene Dateien, eigenes Backend, ein Klick zum Deaktivieren, überlebt jeden Theme-Wechsel.** |

Ein Plugin lässt sich in WordPress seit Version 5.5 auch **aktualisieren**, indem man das ZIP einfach nochmal hochlädt. Du brauchst also nie FTP.

---

## Schritt 1 — Plugin installieren

1. **Plugins → Installieren → Plugin hochladen**
2. `theater-auw-1.6.1.zip` wählen → **Jetzt installieren**
3. **Plugin aktivieren**

Danach steht links im Menü **Theater Auw**.

> Falls der Knopf «Plugin hochladen» fehlt: dann hat der Hoster Datei-Änderungen gesperrt. Spring zu «Wenn das Plugin nicht geht» weiter unten.

---

## Schritt 2 — Erst auf einer Entwurfsseite testen

**Noch nicht** die echte Theater-Seite anfassen.

1. **Seiten → Erstellen**, Titel z.B. «Theater Test»
2. Einen Block **Shortcode** einfügen und hineinschreiben:

   ```
   [theater_auw]
   ```

3. **Speichern** (als Entwurf reicht) → **Vorschau**

Jetzt siehst du die Seite mit den echten Daten von 2026. Schau sie dir auch auf dem Handy an.

---

## Schritt 3 — Phasen einstellen

**Theater Auw** im Menü. Ganz oben steht immer, **welche Phase gerade gilt und warum** — damit du nie rätseln musst.

### Die vier Phasen

| Phase | Wann | Was man sieht |
|---|---|---|
| **Vorbereitung** | jetzt, bis der Verkauf startet | Teaser zum Stück, Countdown auf die Premiere, keine Ticket-Knöpfe |
| **Vorverkauf** | ab Verkaufsstart bis Premiere | roter «Plätze reservieren»-Knopf, Spieldaten mit Verfügbarkeit |
| **Spielzeit** | Premiere bis Derniere | Gold-Akzent, «Heute Vorstellung», Restplätze |
| **Danke** | nach der Derniere | «Merci villmal!», Rückblick, alle Daten als «Vorbei» |

### Empfohlen: nach Kalender

Unter **Phasen & Tickets** stehen drei Daten:

- **Vorverkauf ab** — z.B. 15.09.2026, 09:00
- **Spielzeit ab** — der Premierentag, 00:00
- **Danke ab** — der Tag nach der Derniere, 00:00

Einmal im Sommer eintragen, dann macht die Seite den Rest von selbst. Auch mitten in der Nacht, auch wenn niemand am Rechner sitzt.

### Wenn es mal schnell gehen muss

Auf **«Von Hand festlegen»** umstellen und die Phase im Auswahlfeld wählen. Das überschreibt den Kalender, bis du wieder auf «Nach Kalender» zurückstellst.

### Vorschau ohne etwas zu ändern

Oben auf der Einstellungsseite gibt es vier Vorschau-Links (sobald du unter **Darstellung** die Theater-Seite ausgewählt hast). Die zeigen dir jede Phase auf der echten Seite — **nur dir**, solange du angemeldet bist. Besucherinnen und Besucher sehen weiterhin die richtige Phase.

---

## Schritt 4 — Ticketleo eintragen

Unter **Phasen & Tickets → Event-URL**.

2025 war das:

```
https://reservation.ticketleo.com/event/33310/karriere
```

Für 2026 bekommst du von Ticketleo eine neue Adresse. Bis die drin steht, zeigen die Ticket-Knöpfe «Ticketlink folgt» und sind nicht klickbar — es kann also niemand ins Leere klicken.

**Auf dieser Seite wird nie verkauft.** Jeder Ticket-Knopf ist ein Link und öffnet Ticketleo in einem neuen Tab. Das ist Absicht: keine Zahlungsabwicklung auf der Vereinsseite, keine Daten, um die sich jemand kümmern muss.

Unter **Beschriftung** wählst du zwischen «Plätze reservieren» und «Tickets kaufen» — je nachdem, ob bei Ticketleo nur reserviert und an der Abendkasse gezahlt wird.

---

## Schritt 5 — Bilder

Die Fotos sind schon in deiner Mediathek — du wählst sie nur aus, du musst nichts neu hochladen. Jedes Bild steht dort, wo es hingehört:

| Bild | Wo |
|---|---|
| Logo | **Darstellung**, bei der Navigationsleiste |
| Bild zum Stück | **Stück & Texte** |
| Bild zur Tombola | **Stück & Texte**, unter «Ort und Ablauf» |
| Plakate der Jahrgänge | **Rückblick** — nur nötig, wenn du die Liste von Hand pflegst |
| Porträts, Sponsorenlogos | **Unterseiten**, pro Gruppe |

Wo noch kein Bild gewählt ist, zeigt die Seite einen gestalteten Platzhalter. Nie einen leeren Rahmen, nie ein kaputtes Bild.

Beim **Logo**: ohne Auswahl wird «theater auw» typografisch gesetzt (weiss mit rotem «auw»). Das Logo gehört nur auf dunkle Flächen — auf weissem Grund verschwindet die weisse Schrift.

**Eintritte und Speisekarte gehören nicht mehr dazu.** Für beide gab es einmal ein Feld, in das man die Seite aus dem Programmheft als Scan legte. Heute sind die Eintrittspreise unter **Preise & Infos** als Zeilen erfasst und die Speisekarte unter **Speisekarte** — das lässt sich vorlesen, durchsuchen und auf dem Handy lesen. Ein Scan daneben wäre der zweite Ort für dieselbe Angabe.

---

## Schritt 6 — Auf die echte Seite

Wenn der Entwurf passt:

1. **Seiten → Theater** öffnen
2. Den alten Inhalt **nicht löschen** — markieren, ausschneiden und in eine neue Entwurfsseite «Theater alt» einfügen. So ist er sicher.
3. Auf der Theater-Seite einen **Shortcode**-Block mit `[theater_auw]` einsetzen
4. Falls es das Theme anbietet: rechts unter **Vorlage** eine Variante «Volle Breite» oder «Leere Seite» wählen
5. **Aktualisieren**

WordPress speichert ausserdem automatisch eine Revision — du kommst also immer zum alten Stand zurück.

---

## Die Seite sitzt in einer schmalen Spalte

Das häufigste Problem — und es liegt nicht am Plugin, sondern an der Seitenvorlage. Bei der Divi-Standardvorlage steht rechts eine Blog-Seitenleiste («Neueste Beiträge», «Archiv», «Meta»), und der Seitentitel wird zusätzlich darübergeschrieben. Das Theaterplakat wird dann in rund 740 von 1270 Pixeln gequetscht.

Seit Version 1.0.5 ist das **standardmässig erledigt** — beim Hochladen werden die drei Schalter einmalig eingeschaltet. Nachsehen kannst du sie unter **Theater Auw → Darstellung → Seitenrahmen**:

- **Seitentitel ausblenden** — der Titel steht schon gross im Kopfbereich
- **Seitenleiste ausblenden** — samt Divis Trennstrich, der sonst als feine Linie mitten im Bild stehen bleibt
- **Inhaltsbereich auf volle Breite** — damit der dunkle Kopfbereich randlos läuft

Die Haken wirken **nur auf Seiten mit Theater-Inhalt**. Der Rest der Website behält Titel und Seitenleiste.

Der sauberere Weg, falls dein Theme ihn anbietet: die Seitenvorlage auf «Volle Breite» stellen — bei Divi in den Seiten-Einstellungen unter «Seitenlayout». Dann brauchst du höchstens noch den Haken beim Seitentitel.

---

## Wenn es nicht so aussieht wie im Entwurf

Unter **Theater Auw → Darstellung**:

**Der Inhalt ist zu schmal / hat weisse Ränder**
→ Erst die Seitenvorlage «Volle Breite» des Themes probieren. Gibt es die nicht: **Breite** auf «Volle Fensterbreite erzwingen».

**Schriften oder Abstände sehen falsch aus**
→ **Abschottung** auf «Stark». Das setzt sich gegen dominante Themes durch.

**Die Seite ist ganz ohne Gestaltung**
→ Du benutzt vermutlich einen Page-Builder (Elementor, Divi, WPBakery). Dann findet das Plugin den Shortcode nicht im Seitentext. Unter **Darstellung → Theater-Seite** die Seite auswählen, dann lädt es seine Stile trotzdem.

**Die Phase wechselt nicht, obwohl das Datum erreicht ist**
→ Ein Caching-Plugin liefert die alte Version aus. Das Plugin leert den Cache beim Speichern und einmal pro Nacht selbst; im Zweifel im Caching-Plugin einmal «Cache leeren» drücken.

---

## Wenn das Plugin nicht geht

Falls «Plugin hochladen» fehlt oder der Hoster blockiert, liegt eine Notlösung bereit:

`build/dist/theater-auw-inline.html`

Das ist die ganze Seite in einer Datei. Inhalt kopieren, auf der Theater-Seite einen Block **Individuelles HTML** einfügen, hineinkleben, speichern.

Damit funktioniert die Seite — **aber**:

- Die Phase ist fest verdrahtet. Zum Umschalten muss der Block getauscht werden.
- Es braucht Administrator-Rechte. Als Redakteur entfernt WordPress `<style>` und `<script>` beim Speichern, und es bleibt nur Text übrig.
- Keine Einstellungsseite, keine Mediathek-Bilder.

Es ist wirklich die Notlösung. Der bessere Weg, wenn das Plugin blockiert ist: den Hoster fragen, ob er das ZIP einspielt — Hostpoint, Cyon, Infomaniak und Metanet machen das auf Anfrage.

---

## Später aktualisieren

Neue Version bekommen → **Plugins → Installieren → Plugin hochladen** → dieselbe Prozedur. WordPress erkennt, dass es das Plugin schon gibt, zeigt einen Vergleich und fragt, ob es ersetzen soll. **Ja.**

Deine Einstellungen — Spieldaten, Texte, Ticketlink, Bilder — bleiben erhalten. Die liegen in der Datenbank, nicht im Plugin.

---

## Notfall

Etwas ist kaputt und du weisst nicht was?

**Plugins → Theater Auw → Deaktivieren.**

Die Seite ist sofort wieder so, wie sie vorher war. Nichts geht verloren, die Einstellungen bleiben stehen. Beim nächsten Aktivieren ist alles wieder da.

---

## Die Navigationsleiste

Unter **Darstellung → Mitscrollen** stehen zwei Haken übereinander.

Der erste lässt die Leiste beim Scrollen oben stehen. Der zweite — **«Ganz oben, an der Bildschirmkante»** — entscheidet, *wo* oben.

Ohne den zweiten Haken hält die Theater-Leiste Abstand. Über ihr hängen die graue Vereinsleiste und die Kopfzeile des Themes, beide fest verankert; ohne Abstand verschwände sie beim Scrollen dahinter. Mit dem Haken scrollen jene weg und die Theater-Leiste klebt an der Oberkante.

**Am Handy lohnt sich der Haken meist.** Dort ist der Platz oben knapp, und zwei Leisten übereinander fressen den halben Bildschirm. Die Admin-Leiste von WordPress bleibt in jedem Fall frei — sonst käme man als Angemeldete nicht mehr aus der Seite heraus.

---

## Die Unterseiten

Schauspieler, Hinter den Kulissen, OK-Team und Sponsoren bleiben **eigene Seiten** — sie verschwinden nicht in der Hauptseite.

### Bilder auswählen

**Theater Auw → Unterseiten.** Dort sind vier Gruppen vorbereitet. Pro Gruppe wählst du die Bilder aus der Mediathek — mehr nicht.

**Die Namen musst du nicht erfassen.** Sie stehen bereits in den Bildern und werden übernommen: zuerst die Bildunterschrift, sonst der Alternativtext, sonst der Titel. Auf eurer Seite steht dort schon «Hanna Gehrig» beziehungsweise «Aargauische Kantonalbank».

Soll neben dem Namen eine Rolle stehen, schreib die Bildunterschrift in der Mediathek so:

```
Franz Troxler | Grilleur
```

Geändert wird eine Beschriftung am besten in der Mediathek — dort, wo sie hingehört, und sie wirkt dann überall.

### Ein einzelnes Bild ändern

Jedes gewählte Bild bekommt eine eigene Karte. Was daran geht:

- **Bild austauschen:** auf das Bild klicken, neues wählen. Die Beschriftung bleibt stehen — ein besseres Foto derselben Person heisst nicht, den Namen neu zu tippen.
- **Verschieben:** am gepunkteten Griff links ziehen. Bei den Schauspielern ist die Reihenfolge redaktionell; bei den Sponsoren nimmst du besser «Alphabetisch nach Name».
- **Entfernen:** «Entfernen» unter der Karte. Nur diese eine Karte, der Rest bleibt.

Daneben stehen drei Felder: **Name**, die Zeile darunter und (aufklappbar über «Text») ein längerer **Text oder ein Zitat**. Der graue Text darin ist das, was ohne Zutun aus dem Bild kommt.

Die mittlere Zeile heisst je nach Gruppe anders, ist aber dasselbe Feld: bei den Schauspielern die **Rolle**, bei den Sponsoren der **Ort**. Auf der Seite steht sie unter dem Namen.

**Du füllst nur aus, was anders sein soll.** Stimmt bloss der Firmenname nicht, tippst du den Firmennamen — Rolle und Text kommen weiter aus der Mediathek. Ein Feld wieder leeren stellt die automatische Beschriftung her. Das Bild selbst bleibt dabei unverändert; es kann anderswo noch verwendet werden.

Einen senkrechten Strich musst du im Backend nirgends mehr tippen — der gilt nur noch für die Bildunterschriften in der Mediathek.

Kein Feld ist je nach Gruppe versteckt: was auf der Seite erscheinen kann, lässt sich hier auch ändern.

### Logos gross ansehen

Auf der Sponsorenseite ist jedes Logo anklickbar: ein Klick öffnet es gross über der Seite, mit dem Firmennamen darunter. Escape oder ein Klick neben das Bild schliesst es wieder.

Gezeigt wird das Bild in **voller Grösse** aus der Mediathek — also genau das, was du hochgeladen hast. Ein Logo, das nur 300 Pixel breit ist, wird nicht aufgeblasen; es würde dabei nur unscharf. Willst du es grösser, lade eine grössere Datei hoch.

Bei den Schauspielern, beim OK-Team und bei den Kulissen ist nichts anklickbar — dort ist das Bild in der Kachel schon zu sehen.

### Auf die Seite setzen

Auf der Seite «Die Schauspieler» einen Shortcode-Block einfügen:

```
[theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"]
Hier kann ganz normaler Text stehen — er erscheint unter der Galerie.
[/theater_auw_seite]
```

Das gibt der Unterseite dieselbe Navigation und dieselbe Bildsprache wie der Hauptseite, aber einen kompakteren Kopf — eine Unterseite soll nicht so laut auftreten wie die Startseite.

Analog für die anderen: `gruppe="kulissen"`, `gruppe="ok"`, `gruppe="sponsoren"`.

Brauchst du nur die Galerie, ohne Kopf und Navigation — etwa weil die Seite schon einen eigenen Aufbau hat:

```
[theater_auw_gruppe key="sponsoren"]
```

### Darstellung

Pro Gruppe einstellbar:

- **Personen** — Porträts im Hochformat, Name gross darunter
- **Logos** — auf weisser Karte, nicht beschnitten, Firmenname klein darunter
- **Bilder** — Querformat, für Gruppenfotos wie das OK-Team

Dazu die Spaltenzahl und wahlweise ein dunkler Hintergrund.

Neue Gruppen legst du mit «Gruppe hinzufügen» an — für ein neues Jahr oder eine neue Seite.

---

## Die anderen Seiten umstellen

**Wichtig zu verstehen:** Das Plugin gestaltet nur Seiten, auf denen einer seiner Shortcodes steht. Alle anderen bleiben unverändert im bisherigen Design — auch wenn sie im Menü direkt daneben stehen. Wer im Menü auf «Rückblick» klickt, landet sonst wieder im alten Layout.

**Theater Auw → Übersicht** zeigt dir genau das: welche Seite schon umgestellt ist, welche noch nicht, und was auf die fehlenden gehört. Der Shortcode steht dort zum Kopieren bereit — anklicken, kopieren, auf der Seite in einen Shortcode-Block einfügen.

### Die Rückblick-Seiten

Die drei bestehenden Jahrgangsseiten bleiben erhalten, sie bekommen nur das neue Design:

```
[theater_auw_rueckblick von="2008" bis="2026" titel="Rückblick 2026 – 2008"]
[theater_auw_rueckblick von="1987" bis="2007" titel="Rückblick 2007 – 1987"]
[theater_auw_rueckblick von="1937" bis="1975" titel="Rückblick 1975 – 1937"]
```

Die Jahrgänge kommen aus demselben Portfolio wie auf der Hauptseite — es wird nur der Zeitraum eingegrenzt. Neue Jahrgänge erscheinen automatisch in der richtigen Seite.

### Die Personenseiten

```
[theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"][/theater_auw_seite]
[theater_auw_seite titel="Hinter den Kulissen" gruppe="kulissen"][/theater_auw_seite]
[theater_auw_seite titel="Organisation Komitee" gruppe="ok"][/theater_auw_seite]
[theater_auw_seite titel="Sponsoren" gruppe="sponsoren"][/theater_auw_seite]
```

Damit diese Seiten etwas zeigen, musst du unter **Theater Auw → Unterseiten** zuerst pro Gruppe die Bilder aus der Mediathek auswählen. Die Namen stehen bereits in den Bildern und werden übernommen.

### Speisekarte und Updates

Diese zwei Seiten waren die letzten im alten Design — und zwar aus einem Grund, der sich nicht mit einem Schalter lösen liess: ihr Inhalt liegt in Divi-Modulen. Dort sieht das Plugin nichts von seinem Shortcode, und eine Theater-Navigationsleiste über fremder Schrift macht aus einer Seite noch keine Seite im Theater-Design.

Seit 1.6.1 haben beide einen **eigenen Seitentyp**, wie die Personenseiten. Der Inhalt wird dafür einmal ins Backend übertragen:

**Theater Auw → Speisekarte.** Die Karte ist **schon erfasst** — 28 Zeilen aus der Speise- und Getränkekarte 2025 (Stand 16.10.2025), inklusive Lieferantenzeile. Du musst nur durchgehen, was sich für 2026 ändert.

Pro Zeile: Karte (Speisen oder Getränke), Gang, Bezeichnung, Beschreibung, Preis. Zeilen mit demselben Gang werden auf der Seite zusammengefasst — «Warme Küche» steht einmal da, die Gerichte darunter. Ein Gericht ohne Preis ist erlaubt. Wird ein Getränk in zwei Grössen ausgeschenkt, stehen beide Masse in der Beschreibung und beide Beträge in derselben Reihenfolge im Preis: «1 dl / 7 dl» und «Fr. 7.– / 36.–».

**Theater Auw → Updates.** Pro Eintrag: Datum, Überschrift, Text, wahlweise ein Bild und ein Link. Die Reihenfolge pflegst du nicht — das Neueste steht immer oben, Einträge ohne Datum am Ende.

Danach auf der jeweiligen Seite:

```
[theater_auw_karte]
[theater_auw_updates]
```

Und die alten Divi-Module mit der abgetippten Karte beziehungsweise den alten Meldungen entfernen — sonst steht beides zweimal da. Vorher wie immer: den alten Inhalt in eine Entwurfsseite sichern.

Beide Shortcodes lassen sich auch umschliessend verwenden, wenn zusätzlicher Text unter die Karte gehört:

### Sponsoring-Anmeldung

Die Seite "Anmeldung Sponsoring" ist eine Formularseite — sie nimmt Anmeldungen zu den Premierenabend- und Spenderpaketen entgegen. Deshalb wird sie nicht in einen Shortcode eingeschlossen (das würde dem Formular seine Theme-Stile nehmen), sondern von aussen gestaltet: das Plugin setzt ihr den dunklen Seitenkopf vor, übernimmt die Theater-Schrift und blendet das Formular nach Ablauf der Frist automatisch aus.

**Theater Auw → Sponsoring.** Dort steht alles, was diese Seite betrifft:

- **Anmeldung** — der Haken zeigt den Hinweis "Sponsoren gesucht" auf der Hauptseite.
- **Anmeldeseite** — welche Seite den Seitenkopf tragen soll (üblicherweise "Anmeldung Sponsoring").
- **Titel, Kicker, Vorspann** — die Texte des Seitenkopfs.
- **Anmeldefrist** — das Datum, bis zu dem man sich anmelden kann. Wer bis zu diesem Tag anmeldet, ist dabei; am Tag danach ist die Anmeldung zu: der Hinweis auf der Hauptseite verschwindet und das Formular wird ausgeblendet, ganz ohne Zutun. Ohne Datum bleibt die Anmeldung offen.

Der Inhalt der Seite selbst — Paketpreise, Menü, Bankverbindung, Formular — bleibt unberührt und wird wie bisher im Seiten-Editor gepflegt.

Für einen Auszug der neuesten Meldungen auf einer anderen Seite:

```
[theater_auw_updates anzahl="3"]
```

---

## Shortcode auf einer Divi-Builder-Seite einfügen

Manche Seiten — «Die Schauspieler» zum Beispiel — sind mit dem Divi Builder gebaut. Dort gibt es kein normales Textfeld, in das man den Shortcode schreiben könnte. So geht es trotzdem:

### Der einfache Weg: den Builder deaktivieren

Wenn die Seite ohnehin komplett neu werden soll:

1. Seite öffnen → oben rechts **«Zurück zum Standard-Editor»** (bei Divi: *Use Default Editor* / *Standard-Editor verwenden*)
2. Der alte Aufbau bleibt als Divi-Code im Text stehen — **markieren und löschen**, oder vorher in eine Entwurfsseite sichern
3. Shortcode einfügen, speichern

### Der Weg im Builder

Wenn du den Builder behalten willst:

1. **Divi Builder aktivieren** (Visual Builder oder Backend Builder)
2. In der Zeile, wo der Inhalt hin soll, auf **⊕ Modul hinzufügen**
3. Modul **«Text»** wählen (oder **«Code»**, wenn du sichergehen willst, dass nichts umformatiert wird)
4. Den Shortcode in das Feld einfügen — nur den Shortcode, sonst nichts
5. Speichern

Die alten Module darüber und darunter kannst du löschen oder stehen lassen.

**Tipp:** Ist eine Zeile ausgewählt, findest du unter *Zeilen-Einstellungen → Design → Grösse* die Optionen «Diese Zeile auf volle Breite» und «Eigene Breite verwenden». Nötig ist das nicht — das Plugin bricht seit 1.0.7 selbst aus schmalen Baukasten-Spalten aus.

### Warum die Seite leer aussieht

Steht der Shortcode drin und es kommt trotzdem fast nichts, fehlen meist die Bilder. Unter **Theater Auw → Unterseiten** pro Gruppe die Bilder aus der Mediathek wählen. Als angemeldeter Administrator siehst du auf der Seite dann einen gelben Hinweis, welche Gruppe noch leer ist — Besucherinnen und Besucher sehen ihn nicht.

---

## Gruppenbild

Jede Gruppe kann zusätzlich ein **Gruppenbild** haben — das Ensemble- oder Teamfoto, das alle zusammen zeigt. Es steht breit über den Einzelporträts und wird nicht beschnitten.

**Theater Auw → Unterseiten → [Gruppe] → Gruppenbild.** Bild aus der Mediathek wählen, optional eine Bildunterschrift dazu.

Eine Gruppe darf auch **nur** aus dem Gruppenbild bestehen, ganz ohne Einzelporträts — praktisch für das OK-Team, von dem es oft nur ein gemeinsames Foto gibt.
