/**
 * Theater Auw — Frontend.
 *
 * Bewusst klein und ohne Abhängigkeiten. Alles hier ist Komfort: ohne
 * JavaScript bleibt die Seite vollständig benutzbar, die Ticket-Buttons sind
 * echte Links auf Ticketleo.
 */
( function () {
	'use strict';

	var roots = document.querySelectorAll( '.tha' );
	if ( ! roots.length ) {
		return;
	}

	// Markiert, dass JS läuft — schaltet den Countdown von Text auf Ziffern um.
	Array.prototype.forEach.call( roots, function ( root ) {
		root.classList.add( 'tha-js' );
	} );

	/* ------------------------------------------------------------------
	   Page-Builder-Spalten aufbrechen

	   Steht der Shortcode in einem Divi-, Elementor- oder WPBakery-Modul,
	   sitzt unser Inhalt in einer Spalte mit eigener Maximalbreite — der
	   dunkle Kopfbereich hört dann mitten auf der Seite auf. Welche Klassen
	   diese Spalten haben, ist von Baukasten zu Baukasten anders; deshalb wird
	   nicht geraten, sondern die Ahnenkette markiert und im CSS geöffnet.

	   Nur wenn «Inhaltsbereich auf volle Breite» eingeschaltet ist.
	   ------------------------------------------------------------------ */

	if ( document.body.classList.contains( 'tha-full-content' ) ) {
		Array.prototype.forEach.call( roots, function ( root ) {
			var p = root.parentElement;
			var guard = 0;

			// Bis zum Inhaltsbereich hoch — den behandelt das CSS bereits.
			while ( p && p !== document.body && guard < 12 ) {
				if ( p.classList.contains( 'entry-content' ) ||
					p.classList.contains( 'container' ) ||
					p.id === 'left-area' ) {
					break;
				}
				p.classList.add( 'tha-host' );
				p = p.parentElement;
				guard++;
			}
		} );
	}

	/* ------------------------------------------------------------------
	   Countdown
	   Der Server liefert das Ziel als UTC-Epoch in Millisekunden. Hier wird
	   nur subtrahiert — keine Zeitzonenmathematik im Browser.
	   ------------------------------------------------------------------ */

	function initCountdown( el ) {
		var target = parseInt( el.getAttribute( 'data-target' ), 10 );
		if ( ! target ) {
			return;
		}

		var out = {
			d: el.querySelector( '[data-cd="d"]' ),
			h: el.querySelector( '[data-cd="h"]' ),
			m: el.querySelector( '[data-cd="m"]' )
		};

		function tick() {
			var diff = target - Date.now();

			if ( diff <= 0 ) {
				// Ziel erreicht: Ziffern verstecken, Fallback-Text wieder zeigen.
				el.classList.add( 'is-done' );
				var units = el.querySelector( '.tha-countdown__units' );
				if ( units ) {
					units.style.display = 'none';
				}
				var fb = el.querySelector( '.tha-countdown__fallback' );
				if ( fb ) {
					fb.style.display = 'inline';
					fb.textContent = 'jetzt';
				}
				return false;
			}

			var totalMin = Math.floor( diff / 60000 );
			var days = Math.floor( totalMin / 1440 );
			var hours = Math.floor( ( totalMin % 1440 ) / 60 );
			var mins = totalMin % 60;

			if ( out.d ) {
				out.d.textContent = String( days );
			}
			if ( out.h ) {
				out.h.textContent = String( hours );
			}
			if ( out.m ) {
				out.m.textContent = String( mins );
			}
			return true;
		}

		if ( tick() ) {
			// Minutengenau reicht — eine Sekundenanzeige wäre hier Unruhe ohne Nutzen.
			setInterval( tick, 30000 );
		}
	}

	Array.prototype.forEach.call(
		document.querySelectorAll( '[data-tha-countdown]' ),
		initCountdown
	);

	/* ------------------------------------------------------------------
	   Logos gross ansehen

	   Der Dialog ist ein `<dialog>` — Escape, Fokussperre und das Zurückgeben
	   des Fokus bringt der Browser mit. Hier wird nur gefüllt, geöffnet und
	   geschlossen.

	   Zuerst kommt die Kachel ins Bild, die längst im Browser liegt; erst
	   wenn die grosse Fassung geladen ist, wird getauscht. Sonst starrt man
	   nach dem Klick auf einen leeren Rahmen, bis das Foto da ist.

	   Fehlt `showModal()` (alter Browser), geschieht nichts — und der Link
	   öffnet das Bild wie jeder andere Link.
	   ------------------------------------------------------------------ */

	document.addEventListener( 'click', function ( event ) {
		var link = event.target && event.target.closest
			? event.target.closest( '[data-tha-zoom]' )
			: null;

		if ( ! link ) {
			return;
		}

		var ziel   = link.getAttribute( 'href' );
		var umfeld = link.closest( '.tha-wrap' ) || document;
		var dialog = umfeld.querySelector( '[data-tha-lightbox]' );

		if ( ! ziel || ! dialog || typeof dialog.showModal !== 'function' ) {
			return; // Der Link tut, was er sagt.
		}

		var bild   = dialog.querySelector( '[data-tha-lightbox-img]' );
		var kachel = link.querySelector( 'img' );
		var name   = dialog.querySelector( '[data-tha-lightbox-name]' );
		var role   = dialog.querySelector( '[data-tha-lightbox-role]' );
		var note   = dialog.querySelector( '[data-tha-lightbox-caption]' );

		if ( ! bild ) {
			return;
		}

		event.preventDefault();

		// Die Grösse des Bildes steht im Link — damit hat der Dialog gleich
		// beim ersten Bildaufbau seine richtige Grösse und springt nachher
		// nicht. Ein 300px breites Logo wird nicht auf Kastenbreite gezogen.
		// Der Server schreibt die Masse nur mit, wenn es wirklich ein Bild
		// ist; fehlen sie, gilt die Grundeinstellung des Stylesheets.
		var breit = parseInt( link.getAttribute( 'data-tha-w' ), 10 );
		var hoch  = parseInt( link.getAttribute( 'data-tha-h' ), 10 );

		if ( breit > 0 && hoch > 0 ) {
			dialog.style.setProperty( '--tha-zoom-px', breit + 'px' );
			dialog.style.setProperty( '--tha-zoom-ratio', breit + ' / ' + hoch );
		}

		bild.alt = kachel && kachel.alt ? kachel.alt : '';

		if ( kachel ) {
			bild.src = kachel.currentSrc || kachel.src;
		}

		if ( name ) {
			name.textContent = link.getAttribute( 'data-tha-name' ) || '';
		}
		if ( role ) {
			role.textContent = link.getAttribute( 'data-tha-role' ) || '';
		}
		if ( note ) {
			note.hidden = ( ! name || '' === name.textContent ) && ( ! role || '' === role.textContent );
		}

		// Merken, was gerade gezeigt werden soll: klickt jemand während des
		// Ladens ein zweites Logo an, darf das erste die Anzeige nicht mehr
		// überschreiben.
		dialog.setAttribute( 'data-tha-ziel', ziel );

		var gross = new Image();
		gross.onload = function () {
			if ( dialog.open && dialog.getAttribute( 'data-tha-ziel' ) === ziel ) {
				bild.src = ziel;
			}
		};
		gross.src = ziel;

		if ( ! dialog.open ) {
			dialog.showModal();
		}
	} );

	Array.prototype.forEach.call(
		document.querySelectorAll( '[data-tha-lightbox]' ),
		function ( dialog ) {
			var knopf = dialog.querySelector( '[data-tha-lightbox-close]' );

			if ( knopf ) {
				knopf.addEventListener( 'click', function () {
					dialog.close();
				} );
			}

			// Nur ein Klick auf den Rand trifft das `<dialog>` selbst; ein
			// Klick auf das Bild trifft das Bild.
			dialog.addEventListener( 'click', function ( event ) {
				if ( event.target === dialog ) {
					dialog.close();
				}
			} );

			// Nach dem Schliessen das grosse Bild aus dem Speicher werfen —
			// eine Sponsorenwand hat schnell dreissig davon.
			dialog.addEventListener( 'close', function () {
				var bild = dialog.querySelector( '[data-tha-lightbox-img]' );
				dialog.removeAttribute( 'data-tha-ziel' );
				dialog.style.removeProperty( '--tha-zoom-px' );
				dialog.style.removeProperty( '--tha-zoom-ratio' );
				if ( bild ) {
					bild.removeAttribute( 'src' );
				}
			} );
		}
	);

	/* ------------------------------------------------------------------
	   Pro `.tha`-Wurzel.

	   Alles wird bewusst innerhalb der jeweiligen Wurzel gesucht statt global.
	   Sonst greift ein Klick in der zweiten Instanz auf die Leiste der ersten
	   zu — was auf einer Seite mit mehreren Shortcodes und in der
	   Phasen-Vorschau sofort auffällt.
	   ------------------------------------------------------------------ */

	function initRoot( root ) {

		/* --- Navigation: Menü auf schmalen Viewports auf/zu --- */

		var navToggle = root.querySelector( '[data-tha-nav-toggle]' );
		var navPanel = root.querySelector( '[data-tha-nav-panel]' );
		var navBar = root.querySelector( '.tha-nav' );
		var navBackdrop = root.querySelector( '[data-tha-nav-backdrop]' );

		if ( navToggle && navPanel ) {

			var setNav = function ( open ) {
				navPanel.classList.toggle( 'is-open', open );
				navToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				if ( navBar ) {
					navBar.classList.toggle( 'tha-nav--offen', open );
				}
			};

			var istOffen = function () {
				return navPanel.classList.contains( 'is-open' );
			};

			navToggle.addEventListener( 'click', function () {
				setNav( ! istOffen() );
			} );

			// Daneben tippen schliesst — so wie in jeder App.
			if ( navBackdrop ) {
				navBackdrop.addEventListener( 'click', function () {
					setNav( false );
				} );

			}

			// Wischen und Radeln über dem offenen Menü bewegt die Seite
			// dahinter nicht. Stillgestellt wird sie NICHT über
			// `overflow: hidden` auf <html> — das nähme der klebenden Leiste
			// den Kasten, an dem sie klebt, und sie verschwände nach oben aus
			// dem Bild.
			//
			// Durchgelassen wird nur, was das Blatt selbst scrollt: passt die
			// Liste ganz hinein, gibt es dort nichts zu scrollen, und ohne
			// diese Bremse scrollte stattdessen die Seite darunter weiter.
			if ( navBar ) {
				var haltAn = function ( e ) {
					if ( ! istOffen() ) {
						return;
					}
					var imBlatt = navPanel.contains( e.target );
					var scrollbar = navPanel.scrollHeight > navPanel.clientHeight + 1;
					if ( ! imBlatt || ! scrollbar ) {
						e.preventDefault();
					}
				};
				navBar.addEventListener( 'wheel', haltAn, { passive: false } );
				navBar.addEventListener( 'touchmove', haltAn, { passive: false } );
			}

			document.addEventListener( 'keydown', function ( e ) {
				if ( 'Escape' === e.key && istOffen() ) {
					setNav( false );
					navToggle.focus();
				}
			} );

			// Jeder Menüpunkt schliesst. Bei einem Sprung innerhalb der Seite
			// verdeckt das offene Menü sonst genau das angesprungene Ziel; bei
			// einem echten Wechsel bleibt es beim Zurückblättern sonst offen
			// stehen, weil der Browser die Seite aus dem Cache holt.
			navPanel.addEventListener( 'click', function ( e ) {
				var link = e.target.closest ? e.target.closest( 'a' ) : null;
				if ( link ) {
					setNav( false );
				}
			} );

			// Wird das Fenster breit genug, gibt es kein Blatt mehr — dann darf
			// auch die Seite nicht mehr festgehalten werden.
			window.addEventListener( 'resize', function () {
				if ( window.innerWidth > 860 && istOffen() ) {
					setNav( false );
				}
			} );
		}

		/* --- Sticky CTA --- */

		var sticky = root.querySelector( '[data-tha-sticky]' );
		var hero = root.querySelector( '.tha-hero' );

		if ( sticky && hero && 'IntersectionObserver' in window ) {
			new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting ) {
							sticky.setAttribute( 'hidden', '' );
						} else {
							sticky.removeAttribute( 'hidden' );
						}
					} );
				},
				{ rootMargin: '-80px 0px 0px 0px' }
			).observe( hero );
		}
	}

	Array.prototype.forEach.call( roots, initRoot );

	/* --- Wie hoch hängt der feste Leistenstapel über der Seite? -----------
	 *
	 * Die klebende Navigation braucht einen Abstand von oben, sonst rutscht
	 * sie beim Scrollen unter die Admin-Leiste von WordPress, die graue
	 * Vereinsleiste und Divis Kopfzeile. Deren z-index liegt bei 100000, der
	 * unsere bei 50 — sie gewinnen immer.
	 *
	 * Raten hilft nicht: die Admin-Leiste sehen nur Angemeldete, und Divi
	 * schrumpft seine Kopfzeile beim Scrollen. Also gemessen statt geraten —
	 * alle fest verankerten Elemente, die oben am Viewport kleben, und davon
	 * die tiefste Unterkante.
	 */

	function stickyOffset() {
		var unten = 0;
		var breite = window.innerWidth;
		var alle = document.body ? document.body.querySelectorAll( '*' ) : [];

		// «Ganz oben» eingeschaltet: die Leisten des Themes zaehlen nicht mehr,
		// sie scrollen weg und die Theater-Leiste liegt darueber. Nur die
		// Admin-Leiste von WordPress bleibt — sie zu verdecken hiesse, dass
		// Angemeldete nicht mehr aus der Seite herauskommen. Fuer Besucher gibt
		// es sie nicht, und am Handy scrollt sie mit; dann ist es glatt 0.
		if ( document.querySelector( '.tha-nav--ganzoben' ) ) {
			var adminbar = document.getElementById( 'wpadminbar' );
			if ( ! adminbar ) {
				return 0;
			}
			var abcs = window.getComputedStyle( adminbar );
			if ( 'fixed' !== abcs.position || 'none' === abcs.display ) {
				return 0;
			}
			return Math.max( 0, Math.round( adminbar.getBoundingClientRect().bottom ) );
		}

		Array.prototype.forEach.call( alle, function ( el ) {
			// Nichts aus unserem eigenen Baum — sonst misst sich die
			// Navigation an sich selbst.
			if ( el.closest && el.closest( '.tha' ) ) {
				return;
			}
			var cs = window.getComputedStyle( el );
			if ( 'fixed' !== cs.position || 'none' === cs.display || 'hidden' === cs.visibility ) {
				return;
			}
			var r = el.getBoundingClientRect();
			if ( r.height <= 0 || r.bottom <= 0 ) {
				return;
			}
			// Die Leisten sitzen GESTAFFELT: Admin-Leiste 0–32, darunter die
			// Vereinsleiste 32–67, darunter Divis Kopfzeile. Nur die oberste
			// zu messen greift zu kurz — deshalb alles, was im oberen Streifen
			// beginnt, und davon die tiefste Unterkante.
			if ( r.top < 0 || r.top > 160 ) {
				return;
			}
			// Eine Leiste geht über die ganze Breite. Ein schwebender Knopf,
			// ein Chat-Fenster oder der «nach oben»-Pfeil nicht — die dürfen
			// die Navigation nicht nach unten schieben.
			if ( r.width < breite * 0.6 ) {
				return;
			}
			if ( r.bottom > unten ) {
				unten = r.bottom;
			}
		} );

		return Math.round( unten );
	}

	function applyStickyOffset() {
		document.documentElement.style.setProperty( '--tha-sticky-top', stickyOffset() + 'px' );
	}

	/* --- Klebt die Leiste überhaupt? -------------------------------------
	 *
	 * `position: sticky` klebt nur innerhalb des nächsten Vorfahren, der
	 * scrollt. Sitzt irgendwo darüber ein `overflow: hidden` — und Divi setzt
	 * genau das auf schmalen Viewports, um waagrechtes Scrollen zu
	 * unterbinden —, dann klebt die Leiste an einem Kasten, der selbst nie
	 * scrollt. Sie sieht aus wie normal mitgescrollt: am Handy war sie oben
	 * weg, am Desktop blieb sie stehen.
	 *
	 * Dagegen hilft kein CSS, nur Nachsehen: findet sich so ein Vorfahre,
	 * wird die Leiste `fixed` und ein Platzhalter hält ihre Höhe frei, damit
	 * der Inhalt nicht darunter springt.
	 */
	function stickyGeklemmt( el ) {
		var p = el.parentElement;
		while ( p && p !== document.body && p !== document.documentElement ) {
			var cs = window.getComputedStyle( p );
			var ov = cs.overflow + ' ' + cs.overflowX + ' ' + cs.overflowY;
			if ( ov.indexOf( 'hidden' ) !== -1 || ov.indexOf( 'clip' ) !== -1 ) {
				return true;
			}
			p = p.parentElement;
		}
		return false;
	}

	/* --- Hat die Leiste ueberhaupt Weg zum Kleben? ------------------------
	 *
	 * Der zweite Grund, aus dem `sticky` nichts tut — und der auf stvauw.ch
	 * zutrifft: eine klebende Leiste bewegt sich NUR innerhalb des Kastens
	 * ihres Elternteils. Steht sie in Divis Theme-Builder-Kopfbereich, ist
	 * dieser Kasten genau so hoch wie sie selbst (gemessen: 65 px, Unterkante
	 * bei 97 px) — waehrend die Seite 4719 px lang ist. Weg zum Kleben: null.
	 * Sie scrollt mit, als stuende `position: static` da, und kein Blick auf
	 * `overflow` haette das gezeigt.
	 *
	 * Faustregel: geht die Seite nach dem Elternteil noch nennenswert weiter,
	 * hoert das Kleben lange vor dem Seitenende auf. Dann ist `fixed` das
	 * ehrlichere Mittel.
	 */
	function stickyOhneWeg( nav ) {
		var eltern = nav.parentElement;
		if ( ! eltern ) {
			return false;
		}
		var unten = eltern.getBoundingClientRect().bottom + window.pageYOffset;
		var seite = Math.max(
			document.body ? document.body.scrollHeight : 0,
			document.documentElement.scrollHeight
		);
		return ( seite - unten ) > 200;
	}

	function pruefeSticky() {
		var navs = document.querySelectorAll( '.tha-nav--sticky' );

		Array.prototype.forEach.call( navs, function ( nav ) {
			var platzhalter = nav.previousElementSibling;
			if ( ! platzhalter || ! platzhalter.hasAttribute( 'data-tha-nav-platz' ) ) {
				platzhalter = document.createElement( 'div' );
				platzhalter.setAttribute( 'data-tha-nav-platz', '' );
				platzhalter.hidden = true;
				nav.parentNode.insertBefore( platzhalter, nav );
			}

			// Zum Messen kurz zurück in den Normalzustand — sonst misst man
			// die eigene Reparatur.
			nav.classList.remove( 'tha-nav--fest' );
			platzhalter.hidden = true;

			if ( ! stickyGeklemmt( nav ) && ! stickyOhneWeg( nav ) ) {
				return;
			}

			platzhalter.style.height = nav.offsetHeight + 'px';
			platzhalter.hidden = false;
			nav.classList.add( 'tha-nav--fest' );
		} );
	}

	if ( document.querySelector( '.tha-nav--sticky' ) ) {
		applyStickyOffset();
		pruefeSticky();
		window.addEventListener( 'resize', function () {
			applyStickyOffset();
			// Der klemmende Vorfahre ist oft eine Regel aus einer Media Query:
			// am Handy da, am Desktop nicht. Also bei jeder Breitenänderung neu.
			pruefeSticky();
		} );
		// Divi tauscht seine Kopfzeile beim Scrollen aus. Nicht bei jedem
		// Scrollschritt nachmessen — einmal, wenn es zur Ruhe gekommen ist.
		var timer = null;
		window.addEventListener(
			'scroll',
			function () {
				if ( timer ) {
					clearTimeout( timer );
				}
				timer = setTimeout( applyStickyOffset, 120 );
			},
			{ passive: true }
		);
	}
}() );
