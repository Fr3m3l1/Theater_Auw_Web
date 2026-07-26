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
	   Ticketleo-Handoff-Dialog und Sticky-CTA — pro `.tha`-Wurzel.

	   Alles wird bewusst innerhalb der jeweiligen Wurzel gesucht statt global.
	   Sonst greift ein Klick in der zweiten Instanz auf den Dialog der ersten
	   zu — was auf einer Seite mit mehreren Shortcodes und in der
	   Phasen-Vorschau sofort auffällt.
	   ------------------------------------------------------------------ */

	function initRoot( root ) {

		/* --- Dialog --- */

		var dialog = root.querySelector( '[data-tha-dialog]' );

		if ( dialog ) {
			var goLink = dialog.querySelector( '[data-tha-dialog-go]' );
			var lastFocus = null;

			var openDialog = function ( url ) {
				if ( url && goLink ) {
					goLink.setAttribute( 'href', url );
				}
				lastFocus = document.activeElement;
				dialog.removeAttribute( 'hidden' );
				document.documentElement.style.overflow = 'hidden';
				if ( goLink ) {
					goLink.focus();
				}
			};

			var closeDialog = function () {
				dialog.setAttribute( 'hidden', '' );
				document.documentElement.style.overflow = '';
				if ( lastFocus && lastFocus.focus ) {
					lastFocus.focus();
				}
			};

			Array.prototype.forEach.call(
				dialog.querySelectorAll( '[data-tha-dialog-close]' ),
				function ( btn ) {
					btn.addEventListener( 'click', closeDialog );
				}
			);

			document.addEventListener( 'keydown', function ( e ) {
				if ( 'Escape' === e.key && ! dialog.hasAttribute( 'hidden' ) ) {
					closeDialog();
				}
			} );

			// Ticket-Links abfangen und erst erklären, wohin es geht.
			// Cmd/Ctrl/Shift/Mittelklick bleiben unangetastet — wer in einem
			// neuen Tab öffnen will, soll das können.
			root.addEventListener( 'click', function ( e ) {
				var link = e.target.closest ? e.target.closest( '[data-tha-tickets]' ) : null;
				if ( ! link || ! root.contains( link ) ) {
					return;
				}
				if ( e.metaKey || e.ctrlKey || e.shiftKey || 1 === e.button ) {
					return;
				}
				e.preventDefault();
				openDialog( link.getAttribute( 'href' ) );
			} );
		}

		/* --- Navigation: Menü auf schmalen Viewports auf/zu --- */

		var navToggle = root.querySelector( '[data-tha-nav-toggle]' );
		var navPanel = root.querySelector( '[data-tha-nav-panel]' );

		if ( navToggle && navPanel ) {
			navToggle.addEventListener( 'click', function () {
				var open = navPanel.classList.toggle( 'is-open' );
				navToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );

			// Nach einem Sprung innerhalb der Seite wieder schliessen, sonst
			// verdeckt das offene Menü das angesprungene Ziel.
			navPanel.addEventListener( 'click', function ( e ) {
				var link = e.target.closest ? e.target.closest( 'a' ) : null;
				if ( link && 0 === ( link.getAttribute( 'href' ) || '' ).indexOf( '#' ) ) {
					navPanel.classList.remove( 'is-open' );
					navToggle.setAttribute( 'aria-expanded', 'false' );
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

	if ( document.querySelector( '.tha-nav--sticky' ) ) {
		applyStickyOffset();
		window.addEventListener( 'resize', applyStickyOffset );
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
