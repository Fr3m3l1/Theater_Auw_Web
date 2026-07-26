# Theater Auw — Design System

Design system for **Theater Auw**, the amateur theater group of STV Auw (Aargau, Switzerland). Every autumn the group stages a Swiss-German comedy (Lustspiel/Komödie) in Auw — 2026: «s'Jubiläum» von Dieter Gygli, 6 performances (Premiere SA 14.11. bis Derniere SA 28.11.). Around the play: Essen (Küchenteam ab 18.45 Uhr), Tombola, Sponsoren, Rückblick bis 1937.

**Sources**
- Live site (WordPress, to be replaced): https://stvauw.ch/theater/ — content, copy and structure were read from Home, Spieldaten, and Theater-2025 pages on 2026-07-16.
- `uploads/Logo_theaterauw_transparent.png` — the only provided asset (white "theater" + red "auw" brush mark, 768×260, transparent).

**The one big idea: Saison-Phasen.** The site is one product with four states, switched over the year:
1. **Vorbereitung** (pre-theater) — Berichte, Stück-Teaser, Countdown. Neutral badge, paper surfaces.
2. **Vorverkauf** — ticket sale is live. Red badge, dominant "Tickets kaufen" CTA, Spieldaten with availability. **Ticketing runs on Ticketleo** (external): every ticket CTA hands off to Ticketleo (link-out or embed) — the site never sells directly.
3. **Spielzeit** — performances running. Gold "spotlight" accent appears (`--gold-*`, `--spotlight` gradient), "Heute Vorstellung", Restplätze.
4. **Danke** (after) — thank-you, photos, Rückblick, "bis 2027". Green success accent, quiet.

Phases change *state and emphasis*, never the visual language. See `guidelines/brand-phases.html` and the UI kit (`ui_kits/website/index.html`) which implements all four.

## CONTENT FUNDAMENTALS
- **Language:** German (Swiss spelling — always «ss», never the Eszett). Play synopses in Standard German; the plays themselves are Swiss-German (titles like «s'Jubiläum» stay dialect).
- **Voice:** warm, personal, village-club herzlich. Always **per Du** («…dich kulinarisch zu verwöhnen», «wünschen wir dir das nötige Glück»). Never Sie, never corporate.
- **Sign-off:** «Herzliche Grüsse Theater Auw». Thanks are explicit and named («Ein herzliches Dankeschön an alle Helferinnen und Helfer»).
- **Titles in guillemets:** «s'Jubiläum», «Karriere». Dates as `SA 14.11.`, times as `20.00 Uhr`. Premiere/Derniere are called out.
- **Casing:** sentence case in body; headings render uppercase via the display font (write them normally).
- **No emoji, no exclamation-mark marketing.** People are named with pride (Regisseurin Christine Jauner, Grilleur Franz Troxler) — keep that.

## VISUAL FOUNDATIONS
- **Colors:** one brand red sampled from the logo (`--red-600 #e02424`, hover 700, press 800) on two worlds: warm **paper** neutrals (`--paper-*`) for reading, and warm near-black **stage** darks (`--ink-950/900`) for hero/moment sections. Secondary **spotlight gold** (`--gold-*`) is reserved for the Spielzeit phase. Semantic green/amber for status only. Max: red is *the* accent — never decorate with it.
- **Type:** display = **Big Shoulders** 600/700, always uppercase, tight leading (.96–1.15) — theater-poster energy. Body/UI = **Libre Franklin** 400–600, 15.5/1.55 (Franklin Gothic print heritage — deliberately not a default UI sans). Caps-captions use `.09em` tracking. Scale: poster(clamp→104) / 56 / 40 / 28 / 21 / 19 / 16 / 14 / 12.5.
- **Spacing:** 4px base (`--sp-1…24`), containers 1120 / 720 narrow. Generous section padding (sp-16–sp-24).
- **Backgrounds:** flat paper or flat ink; the only gradient allowed is `--spotlight` (subtle gold radial, Spielzeit heroes) and scrims over photos. Photography: warm candid ensemble shots, full-bleed on stage sections with dark scrim; no stock-photo look.
- **Radii:** 6 (inputs/buttons) / 10 (rows, small panels) / 16 (cards) / pill (badges).
- **Shadows:** soft neutral (`--shadow-card`, `--shadow-raised`); dark sections use `--shadow-stage`. No colored glows.
- **Borders:** 1px `--border-subtle` on paper, `--border-onstage` (16% white) on ink.
- **Motion:** fast and modest — 120ms hover, 220ms toggles, `--ease-out`; fade/translate 8–12px on section reveal. No bounces, no parallax.
- **Hover:** buttons darken (600→700); quiet elements gain `--surface-sunken` background; links darken. **Press:** darken further (800) + 1px translate-down.
- **Focus:** 3px red ring at 35% (`--focus-ring`).
- **Transparency/blur:** none, except photo scrims and the dialog scrim (`rgba(21,17,18,.62)`).
- **Cards:** white, 16px radius, subtle border + `--shadow-card`; `stage` variant on dark. No colored left-border accents.

## ICONOGRAPHY
- The source site has **no icon system** (image-based nav, no icon font). No emoji anywhere.
- This system uses **Lucide** (CDN or copied SVG, stroke 2, `stroke="currentColor"`, 16–20px) for functional icons only — menu, close, chevron, calendar, ticket, mail. Decorative icons: none; imagery does that job. **Flagged substitution:** Lucide is our choice, not sourced from the brand.
- Unicode: only typographic (·, –, «»). The ✕/checkmark in components are inline Lucide-style SVGs.
- Logo: `assets/logo.png` (white+red, transparent) — **dark surfaces only**, never on paper (white wordmark disappears). Min width ~120px. No other brand marks were provided; where a mark is impossible (light surface), set "Theater Auw" in Barlow Condensed 700 uppercase with red "Auw".

## Index
- `styles.css` → `tokens/` (fonts, colors, typography, spacing, effects)
- `assets/logo.png` — the provided logo (only asset)
- `guidelines/` — specimen cards: colors (red/darks/neutrals/semantic), type (display/body/scale), spacing, radius+shadows, brand (logo/phases/tone)
- `components/`
  - actions: **Button**, **IconButton**
  - forms: **Input**, **Select**, **Checkbox**, **Radio**, **Switch**
  - display: **Card**, **Badge**, **Tag**, **ShowDateRow**
  - navigation: **Tabs**
  - overlay: **Dialog**, **Toast**, **Tooltip**
- `ui_kits/website/` — the modern site, all four Saison-Phasen, interactive
- `SKILL.md` — agent skill entry point

**Intentional additions** (no component source existed; standard set + these theater-specific ones): `ShowDateRow` — the Spieldaten list is the core repeated object across three phases. `Badge` gold tone + `--spotlight` — carries the Spielzeit phase.

**Caveats:** fonts load from Google Fonts CDN (no licensed binaries provided — Big Shoulders + Libre Franklin are matched to the poster-logo vibe, not sourced). Ticketleo event URL is a placeholder (ticketleo.com) — set the real event link in `ui_kits/website/TicketFlow.jsx`. Photography from the site was not copied (low-res WordPress uploads); use `<image-slot>`/placeholders and ask for originals.

---

## Note on this local copy

Imported from the Claude Design project `6e655802-11c3-49e3-b0f3-ad02fb462c2c` on 2026-07-25 as the design reference for the production build in `../plugin/theater-auw/`.

The React sources under `components/` and `ui_kits/` are the **design vocabulary**, not the shipped code — the production page is server-rendered PHP. See `../README.md` for why.

Not copied in this import: `_ds_bundle.js` (generated prototype bundle) and the binary assets under `assets/` and `uploads/`. The logo lives in the club's WordPress media library and is selected there via the plugin's settings.
