# UI Kit — theaterauw Website

Modern replacement for the WordPress site at stvauw.ch/theater. One page, four seasonal phases (switcher pinned top-right, persisted in localStorage):

- **Vorbereitung** — teaser hero, countdown, Berichte
- **Vorverkauf** — red CTA hero, Spieldaten with Tickets buttons + availability
- **Spielzeit** — gold spotlight hero, "heute Vorstellung", Restplätze
- **Danke** — Merci hero, dates marked "Vorbei"

Files: `Site.jsx` (header/footer/placeholder), `Hero.jsx` (4 phase heroes), `Sections.jsx` (Spieldaten incl. real 2026/2027 dates, Stück, Weiteres, Rückblick), `TicketFlow.jsx` (Ticketleo handoff dialog — no on-site checkout; set the real event URL there). Photography is placeholder-only — originals needed.
