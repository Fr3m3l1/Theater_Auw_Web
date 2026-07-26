/**
 * Backend: Media-Picker und wiederholbare Zeilen. jQuery, weil wp.media
 * ohnehin darauf aufsetzt.
 */
( function ( $ ) {
	'use strict';

	/* --- Media-Picker ---------------------------------------------- */

	$( document ).on( 'click', '[data-tha-media-pick]', function ( e ) {
		e.preventDefault();

		var $wrap = $( this ).closest( '[data-tha-media]' );
		var frame = wp.media( {
			title: THA_ADMIN.chooseImage,
			button: { text: THA_ADMIN.useImage },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var att = frame.state().get( 'selection' ).first().toJSON();
			var url = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;

			$wrap.find( 'input[type="hidden"]' ).val( att.id );
			$wrap.find( '.tha-media-field__preview' )
				.html( $( '<img>' ).attr( 'src', url ).attr( 'alt', '' ) );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '[data-tha-media-clear]', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '[data-tha-media]' );
		$wrap.find( 'input[type="hidden"]' ).val( 0 );
		$wrap.find( '.tha-media-field__preview' ).empty();
	} );

	/* --- Wiederholbare Zeilen --------------------------------------- */

	$( document ).on( 'click', '[data-tha-repeat-add]', function ( e ) {
		e.preventDefault();

		var $table = $( this ).closest( 'p' ).prev( '[data-tha-repeat]' );
		if ( ! $table.length ) {
			$table = $( this ).closest( 'form' ).find( '[data-tha-repeat]' ).last();
		}

		var $body = $table.find( '[data-tha-repeat-body]' );
		var $rows = $body.find( '[data-tha-repeat-row]' );
		var $last = $rows.last();

		if ( ! $last.length ) {
			return;
		}

		var $new = $last.clone();
		var index = $rows.length;

		// Namen neu indizieren und Werte leeren.
		//
		// Textfelder gehören mit in die Auswahl: die Update-Zeilen haben eines,
		// und ohne Neuindizierung trüge die neue Zeile denselben Namen wie die
		// vorherige — der Text der einen würde den der anderen überschreiben.
		$new.find( 'input, select, textarea' ).each( function () {
			var name = $( this ).attr( 'name' );
			if ( name ) {
				$( this ).attr( 'name', name.replace( /\[(\d+)\]/, '[' + index + ']' ) );
			}
			if ( 'hidden' === this.type ) {
				$( this ).val( 0 );
			} else if ( 'SELECT' === this.tagName ) {
				this.selectedIndex = 0;
			} else {
				$( this ).val( '' );
			}
		} );
		$new.find( '.tha-media-field__preview' ).empty();
		// Eine neue Gruppe startet ohne Bilder. Die Karten der Vorlage stehen
		// sonst da, als wären sie schon gewählt — ihre IDs sind aber beim
		// Leeren der versteckten Felder längst weg.
		$new.find( '[data-tha-gallery-cards]' ).empty();

		$body.append( $new );
		$( document ).trigger( 'tha:row-added', [ $new ] );
	} );

	$( document ).on( 'click', '[data-tha-repeat-drop]', function ( e ) {
		e.preventDefault();
		var $row = $( this ).closest( '[data-tha-repeat-row]' );
		if ( $row.siblings( '[data-tha-repeat-row]' ).length < 1 ) {
			$row.find( 'input' ).val( '' );
			return;
		}
		if ( window.confirm( THA_ADMIN.confirmDrop ) ) {
			$row.remove();
		}
	} );

}( jQuery ) );

/**
 * Die Bilder einer Gruppe — Schauspieler, Team, Sponsoren.
 *
 * Jede Karte steht für sich: ihr Bild lässt sich einzeln austauschen, sie lässt
 * sich einzeln entfernen und an ihren Platz ziehen. Vorher führte jede
 * Änderung durch die grosse Mediathek-Auswahl, und die baute anschliessend
 * ALLE Karten neu auf. Ein Sponsorenlogo tauschen hiess: Mediathek öffnen, das
 * alte suchen, abwählen, das neue anklicken.
 *
 * Die Reihenfolge ist redaktionell, nicht zufällig — bei den Schauspielern
 * steht die Hauptrolle gerne vorne. Sie steckt in der Reihenfolge der Karten
 * im DOM und wird daraus in das versteckte `ids`-Feld geschrieben.
 */
( function ( $ ) {
	'use strict';

	/* --- Hilfsmittel ------------------------------------------------ */

	function cards( $wrap ) {
		return $wrap.find( '[data-tha-gallery-cards]' );
	}

	/**
	 * Die Bild-IDs aus der Reihenfolge der Karten neu schreiben.
	 *
	 * Nach jedem Zug, Tausch, Entfernen und Hinzufügen. Damit ist das
	 * versteckte Feld immer das Abbild dessen, was man sieht.
	 */
	function syncIds( $wrap ) {
		var ids = [];
		cards( $wrap ).find( '[data-tha-card]' ).each( function () {
			var id = $( this ).attr( 'data-id' );
			if ( id ) {
				ids.push( id );
			}
		} );
		$wrap.find( '[data-tha-gallery-ids]' ).val( ids.join( ',' ) );
	}

	function idsOf( $wrap ) {
		return String( $wrap.find( '[data-tha-gallery-ids]' ).val() || '' )
			.split( ',' ).filter( Boolean );
	}

	function thumbUrl( a ) {
		return ( a.sizes && a.sizes.thumbnail ) ? a.sizes.thumbnail.url : a.url;
	}

	/**
	 * Wie das mittlere Feld heisst, solange nichts drinsteht.
	 *
	 * Bei den Sponsoren steht dort der Ort — «Amrein Metzgerei», darunter
	 * «Auw». Es ist dasselbe Feld wie die Rolle bei den Schauspielern, und die
	 * Ausgabe zeigt es bei beiden. Ausgeblendet wird es nirgends: was auf der
	 * Seite erscheint, muss sich hier ändern lassen.
	 */
	function roleLabel( $wrap ) {
		return 'logos' === $wrap.attr( 'data-type' ) ? THA_ADMIN.fieldPlace : THA_ADMIN.fieldRole;
	}

	/**
	 * Beschriftung aus dem Bild in Name, Rolle und Text zerlegen.
	 *
	 * Nur für die grauen Platzhalter. Massgeblich ist `Tha_Groups::split_label()`
	 * in PHP — nach dem Speichern rechnet ohnehin die Serverseite. Deshalb hier
	 * nur die beiden Schreibweisen, die in der Mediathek wirklich vorkommen.
	 */
	function splitLabel( raw ) {
		raw = $.trim( String( raw || '' ) );

		if ( /^(DSC|IMG|P\d|_MG)[\s_-]*\d+$/i.test( raw ) ) {
			raw = '';
		}
		if ( ! raw ) {
			return { name: '', role: '', text: '' };
		}

		// «Sohlenberger alias Markus Ineichen» — Rolle vorn, Mensch hinten.
		var alias = raw.match( /^(.+?)\s+alias\s+(.+)$/i );
		if ( alias ) {
			return { name: $.trim( alias[2] ), role: $.trim( alias[1] ), text: '' };
		}

		var parts = raw.split( /\s*[|–—]\s*/ );
		return {
			name: $.trim( parts[0] || '' ),
			role: $.trim( parts[1] || '' ),
			// Ein Strich mitten im Zitat darf nichts abschneiden.
			text: $.trim( parts.slice( 2 ).join( ' | ' ) )
		};
	}

	/**
	 * Eine Karte bauen. Muss dasselbe Markup ergeben wie `tab-gruppen.php`.
	 */
	function buildCard( a, $wrap ) {
		var base = $wrap.data( 'name-base' );
		var auto = splitLabel( a.caption || a.alt || a.title || '' );

		var field = function ( key, fallback ) {
			return $( '<input type="text">' )
				.attr( 'class', 'tha-card__f tha-card__f--' + key + ( auto[ key ] ? ' tha-card__f--auto' : '' ) )
				.attr( 'name', base + '[labels][' + a.id + '][' + key + ']' )
				.attr( 'data-auto', auto[ key ] )
				.attr( 'placeholder', auto[ key ] || fallback );
		};

		var $tools = $( '<div class="tha-card__tools">' )
			.append(
				$( '<label class="tha-card__wide">' )
					.append( $( '<input type="checkbox" value="1">' ).attr( 'name', base + '[wide][' + a.id + ']' ) )
					.append( document.createTextNode( ' ' + THA_ADMIN.wide ) )
			)
			.append( $( '<button type="button" class="button-link tha-card__more" data-tha-card-more>' ).text( THA_ADMIN.textField ) )
			.append( $( '<button type="button" class="button-link tha-card__drop" data-tha-card-drop>' ).text( THA_ADMIN.removeImage ) );

		return $( '<div class="tha-card" data-tha-card>' )
			.toggleClass( 'tha-card--text', !! auto.text )
			.attr( 'data-id', a.id )
			.append( $( '<span class="tha-card__grip" data-tha-card-grip aria-hidden="true">' ).attr( 'title', THA_ADMIN.dragHint ) )
			.append(
				$( '<button type="button" class="tha-card__thumb" data-tha-card-swap>' )
					.attr( 'title', THA_ADMIN.replaceImage )
					.append( $( '<img>' ).attr( { src: thumbUrl( a ), alt: '' } ) )
			)
			.append(
				$( '<div class="tha-card__fields">' )
					.append( field( 'name', THA_ADMIN.fieldName ) )
					.append( field( 'role', roleLabel( $wrap ) ) )
					.append( field( 'text', THA_ADMIN.fieldText ) )
					.append( $tools )
			);
	}

	/* --- Bilder hinzufügen ------------------------------------------ */

	$( document ).on( 'click', '[data-tha-gallery-pick]', function ( e ) {
		e.preventDefault();

		var $wrap = $( this ).closest( '[data-tha-gallery]' );

		var frame = wp.media( {
			title: THA_ADMIN.chooseImages,
			button: { text: THA_ADMIN.useImages },
			library: { type: 'image' },
			multiple: 'add'
		} );

		frame.on( 'select', function () {
			var vorhanden = idsOf( $wrap );
			var $cards = cards( $wrap );

			// Nur wirklich Neues anhängen. Bestehende Karten bleiben, wie sie
			// sind — samt eingetippter Beschriftung und Reihenfolge.
			frame.state().get( 'selection' ).each( function ( att ) {
				var a = att.toJSON();
				if ( vorhanden.indexOf( String( a.id ) ) === -1 ) {
					$cards.append( buildCard( a, $wrap ) );
				}
			} );

			syncIds( $wrap );
		} );

		frame.open();
	} );

	/* --- Ein einzelnes Bild austauschen ----------------------------- */

	$( document ).on( 'click', '[data-tha-card-swap]', function ( e ) {
		e.preventDefault();

		var $card = $( this ).closest( '[data-tha-card]' );
		var $wrap = $card.closest( '[data-tha-gallery]' );
		var base = $wrap.data( 'name-base' );
		var alt = String( $card.attr( 'data-id' ) || '' );

		var frame = wp.media( {
			title: THA_ADMIN.replaceImage,
			button: { text: THA_ADMIN.useImage },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var a = frame.state().get( 'selection' ).first().toJSON();
			var neu = String( a.id );

			if ( neu === alt ) {
				return;
			}
			// Dasselbe Bild zweimal in einer Gruppe gäbe zwei Karten, die sich
			// dieselben Felder teilen — die zweite überschriebe die erste.
			if ( idsOf( $wrap ).indexOf( neu ) !== -1 ) {
				window.alert( THA_ADMIN.alreadyInGroup );
				return;
			}

			var auto = splitLabel( a.caption || a.alt || a.title || '' );

			$card.attr( 'data-id', neu );
			$card.find( '.tha-card__thumb' )
				.empty()
				.append( $( '<img>' ).attr( { src: thumbUrl( a ), alt: '' } ) );

			// Die Feldnamen tragen die Bild-ID, also müssen sie mitwandern.
			// Die eingetippten Werte bleiben: wer ein besseres Foto derselben
			// Person einsetzt, will ihren Namen nicht neu schreiben.
			var leer = {
				name: THA_ADMIN.fieldName,
				role: roleLabel( $wrap ),
				text: THA_ADMIN.fieldText
			};
			$card.find( '.tha-card__f' ).each( function () {
				var key = ( this.className.match( /tha-card__f--(name|role|text)/ ) || [] )[1];
				if ( ! key ) {
					return;
				}
				this.name = base + '[labels][' + neu + '][' + key + ']';
				this.placeholder = auto[ key ] || leer[ key ];
				$( this ).attr( 'data-auto', auto[ key ] )
					.toggleClass( 'tha-card__f--auto', !! auto[ key ] );
			} );
			$card.find( '.tha-card__wide input' ).attr( 'name', base + '[wide][' + neu + ']' );

			syncIds( $wrap );
		} );

		frame.open();
	} );

	/* --- Entfernen, Text ein-/ausklappen ---------------------------- */

	$( document ).on( 'click', '[data-tha-card-drop]', function ( e ) {
		e.preventDefault();
		var $card = $( this ).closest( '[data-tha-card]' );
		var $wrap = $card.closest( '[data-tha-gallery]' );
		$card.remove();
		syncIds( $wrap );
	} );

	$( document ).on( 'click', '[data-tha-card-more]', function ( e ) {
		e.preventDefault();
		var $card = $( this ).closest( '[data-tha-card]' );
		$card.toggleClass( 'tha-card--text' );
		if ( $card.hasClass( 'tha-card--text' ) ) {
			$card.find( '.tha-card__f--text' ).trigger( 'focus' );
		}
	} );

	$( document ).on( 'click', '[data-tha-gallery-clear]', function ( e ) {
		e.preventDefault();
		if ( ! window.confirm( THA_ADMIN.confirmClear ) ) {
			return;
		}
		var $wrap = $( this ).closest( '[data-tha-gallery]' );
		cards( $wrap ).empty();
		syncIds( $wrap );
	} );

	/* --- Darstellung der Gruppe ------------------------------------- */

	// Bei Sponsorenlogos heisst das mittlere Feld «Ort» statt «Rolle». Nur die
	// Beschriftung wechselt — das Feld bleibt, und was drinsteht, bleibt auch.
	$( document ).on( 'change', '[data-tha-type]', function () {
		var $wrap = $( this ).closest( '[data-tha-repeat-row]' ).find( '[data-tha-gallery]' );
		$wrap.attr( 'data-type', this.value );
		$wrap.find( '.tha-card__f--role' ).each( function () {
			this.placeholder = this.getAttribute( 'data-auto' ) || roleLabel( $wrap );
		} );
	} );

	/* --- Reihenfolge ziehen ----------------------------------------- */

	function initSortable( $scope ) {
		if ( ! $.fn.sortable ) {
			return;
		}
		$scope.find( '[data-tha-gallery-cards]' ).addBack( '[data-tha-gallery-cards]' ).each( function () {
			var $cards = $( this );
			if ( $cards.data( 'ui-sortable' ) ) {
				return;
			}
			$cards.sortable( {
				handle: '[data-tha-card-grip]',
				items: '> [data-tha-card]',
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				placeholder: 'tha-card__placeholder',
				update: function () {
					syncIds( $( this ).closest( '[data-tha-gallery]' ) );
				}
			} );
		} );
	}

	// Eine frisch hinzugefügte Gruppe ist ein Klon — der bringt die
	// Sortierbarkeit nicht mit.
	$( document ).on( 'tha:row-added', function ( e, $row ) {
		initSortable( $row );
	} );

	$( function () {
		initSortable( $( document ) );

		// Sicherheitsnetz: was im DOM steht, gilt — auch wenn eine Karte auf
		// einem Weg entstanden ist, an den hier niemand gedacht hat.
		$( 'form' ).on( 'submit', function () {
			$( this ).find( '[data-tha-gallery]' ).each( function () {
				syncIds( $( this ) );
			} );
		} );
	} );

}( jQuery ) );
