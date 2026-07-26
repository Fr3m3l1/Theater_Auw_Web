<?php
/**
 * Spezifitäts-Test für theater-auw.css.
 *
 * Hintergrund: die Abgrenzung gegen fremde Themes setzt `all: revert` auf
 * alle Elemente unter `.tha`. Wenn dieser Reset eine HÖHERE Spezifität hat als
 * die eigenen Komponenten-Regeln, löscht er sie — Hintergründe, Abstände,
 * Layout verschwinden, übrig bleibt nackter Text. Genau das ist am 25.07.2026
 * auf michaelscheidegger.ch passiert.
 *
 * Der Test rechnet die Spezifität jedes Selektors aus und stellt sicher, dass
 * JEDE Regel den Reset schlägt.
 *
 * Aufruf:  php preview/test-css.php
 */

$css_file = dirname( __DIR__ ) . '/plugin/theater-auw/assets/css/theater-auw.css';
$css      = file_get_contents( $css_file );

if ( false === $css ) {
	fwrite( STDERR, "theater-auw.css nicht lesbar\n" );
	exit( 1 );
}

/**
 * Spezifität eines Selektors als [ids, classes, elements].
 *
 * `:where(...)` steuert per Definition nichts bei — der Inhalt wird deshalb
 * vor der Zählung entfernt. Genau darauf beruht die Reparatur.
 */
function specificity( $selector ) {
	$s = trim( $selector );

	// :where(...) trägt null bei — komplett entfernen (auch verschachtelt).
	do {
		$before = $s;
		$s      = preg_replace( '/:where\((?:[^()]|\([^()]*\))*\)/', ' ', $s );
	} while ( $s !== $before );

	// :not()/:is() zählen wie ihr stärkstes Argument — hier reicht: Klammern
	// auflösen und den Inhalt normal mitzählen.
	$s = preg_replace( '/:(?:not|is|has)\(/', ' ', $s );
	$s = str_replace( array( '(', ')' ), ' ', $s );

	$ids = preg_match_all( '/#[\w-]+/', $s );
	$s   = preg_replace( '/#[\w-]+/', ' ', $s );

	// Klassen, Attribute und Pseudo-Klassen
	$classes = preg_match_all( '/\.[\w-]+/', $s )
		+ preg_match_all( '/\[[^\]]+\]/', $s )
		+ preg_match_all( '/:(?!:)[\w-]+/', $s );
	$s = preg_replace( array( '/\.[\w-]+/', '/\[[^\]]+\]/', '/:(?!:)[\w-]+/' ), ' ', $s );

	// Elemente und Pseudo-Elemente
	$elements = preg_match_all( '/(?<![\w-])[a-zA-Z][\w-]*/', $s )
		+ preg_match_all( '/::[\w-]+/', $s );

	return array( $ids, $classes, $elements );
}

function spec_cmp( $a, $b ) {
	for ( $i = 0; $i < 3; $i++ ) {
		if ( $a[ $i ] !== $b[ $i ] ) {
			return $a[ $i ] <=> $b[ $i ];
		}
	}
	return 0;
}

function spec_str( $s ) {
	return '(' . implode( ',', $s ) . ')';
}

/**
 * Selektorliste an den Kommas der OBERSTEN Ebene zerlegen.
 *
 * Ein simples explode(',') zerreisst `:where(h1, h2, …)` mitten drin und
 * misst dann Unsinn — der Test hätte sich damit fast selbst ausgetrickst.
 */
function split_selectors( $list ) {
	$out   = array();
	$buf   = '';
	$depth = 0;
	$len   = strlen( $list );

	for ( $i = 0; $i < $len; $i++ ) {
		$c = $list[ $i ];
		if ( '(' === $c ) {
			$depth++;
		} elseif ( ')' === $c ) {
			$depth--;
		}
		if ( ',' === $c && 0 === $depth ) {
			$out[] = $buf;
			$buf   = '';
			continue;
		}
		$buf .= $c;
	}
	$out[] = $buf;

	return $out;
}

// --- CSS grob zerlegen: Selektorlisten vor jedem { ------------------------

$stripped = preg_replace( '!/\*.*?\*/!s', '', $css );

// @keyframes-Blöcke entfernen — deren "Selektoren" (0%, from, to) sind keine.
$stripped = preg_replace( '/@keyframes[^{]*\{(?:[^{}]|\{[^{}]*\})*\}/s', '', $stripped );

// Regelblöcke als (Selektorliste, Rumpf) einlesen. Der Reset wird über den
// RUMPF erkannt (`all: revert`), nicht über den Selektor — sonst würde der
// «stark»-Notausgang mitgezählt und die Messlatte wäre falsch.
preg_match_all( '/([^{}@]+)\{([^{}]*)\}/', $stripped, $m, PREG_SET_ORDER );

$reset_spec = null;
$rules      = array();

foreach ( $m as $block ) {
	$is_reset = ( false !== strpos( $block[2], 'all: revert' ) );

	foreach ( split_selectors( $block[1] ) as $sel ) {
		$sel = trim( preg_replace( '/\s+/', ' ', $sel ) );
		if ( '' === $sel || 0 === strpos( $sel, '@' ) ) {
			continue;
		}
		$rules[] = $sel;

		if ( $is_reset ) {
			$spec = specificity( $sel );
			if ( null === $reset_spec || spec_cmp( $spec, $reset_spec ) > 0 ) {
				$reset_spec = $spec;
			}
		}
	}
}

if ( null === $reset_spec ) {
	fwrite( STDERR, "FEHLER: Der Reset-Selektor (:where(h1, …)) wurde nicht gefunden.\n" );
	exit( 1 );
}

echo "Reset-Selektor hat Spezifität " . spec_str( $reset_spec ) . "\n";
echo "Jede Komponenten-Regel muss diese Spezifität schlagen.\n\n";

$fails = 0;
$ok    = 0;

foreach ( $rules as $sel ) {
	// Den Reset selbst und die Token-/Wurzel-Blöcke nicht gegen sich prüfen.
	if ( false !== strpos( $sel, ':where(' ) ) {
		continue;
	}
	// Nur Regeln prüfen, die tatsächlich Elemente INNERHALB der Wurzel treffen.
	if ( false === strpos( $sel, '.tha-' ) ) {
		continue;
	}
	// Regeln, die am `body` ansetzen, gestalten den RAHMEN des Themes —
	// Seitentitel ausblenden, Seitenleiste weg, Theater-Typografie. Sie
	// stehen ausserhalb der Wurzel, der Reset kann sie gar nicht treffen.
	if ( 0 === strpos( $sel, 'body' ) ) {
		continue;
	}

	$spec = specificity( $sel );
	if ( spec_cmp( $spec, $reset_spec ) <= 0 ) {
		printf( "FAIL  %-58s %s  wird vom Reset überschrieben\n", $sel, spec_str( $spec ) );
		$fails++;
	} else {
		$ok++;
	}
}

// ---------------------------------------------------------------------
// Zweite Pruefung: Listen gegen Theme-Regeln MIT ID.
//
// Divi setzt `#left-area ul { padding: 0 0 23px 1em }`. Eine ID wiegt mehr
// als jede Zahl von Klassen — `.tha.tha .tha-nav__list` verliert dagegen.
// Deshalb muss JEDE Regel, die einer <ul> oder <ol> Innen- oder Aussenmass
// gibt, mindestens ID-Staerke haben.
//
// Welche Klassen ueberhaupt Listen sind, wird nicht geraten, sondern aus den
// Vorlagen gelesen. Kommt morgen eine neue Liste dazu, faellt sie hier auf.
// ---------------------------------------------------------------------

// `sub-menu` steht in keiner Vorlage: die Liste baut WordPress selbst, wenn
// ein Menuepunkt Unterpunkte hat. Sie ist trotzdem eine <ul> unter `.tha` und
// faellt damit unter dieselbe Regel.
$listen = array( 'sub-menu' => true );

foreach ( glob( dirname( __DIR__ ) . '/plugin/theater-auw/public/views/*.php' ) as $view ) {
	if ( preg_match_all( '/<(?:ul|ol)\s+class="([^"]+)"/i', file_get_contents( $view ), $m ) ) {
		foreach ( $m[1] as $klassen ) {
			foreach ( preg_split( '/\s+/', trim( $klassen ) ) as $k ) {
				if ( preg_match( '/^tha-[a-z0-9_-]+$/', $k ) ) {
					$listen[ $k ] = true;
				}
			}
		}
	}
}

echo "\nListen-Regeln gegen Theme-IDs:\n\n";
printf( "Aus den Vorlagen gelesen: %s\n\n", implode( ', ', array_keys( $listen ) ) );

$id_fails = 0;
$id_ok     = 0;

// Die Regeln noch einmal MIT Rumpf einlesen — der erste Durchgang kennt
// nur die Selektoren.
$css_text = file_get_contents( dirname( __DIR__ ) . '/plugin/theater-auw/assets/css/theater-auw.css' );
$css_text = preg_replace( '!/\*.*?\*/!s', '', $css_text );

if ( preg_match_all( '/([^{}]+)\{([^{}]*)\}/s', $css_text, $bloecke, PREG_SET_ORDER ) ) {
	foreach ( $bloecke as $block ) {
		$rumpf = $block[2];
		// Nicht nur Innen- und Aussenmass: die Grundregel von `.tha-people`
		// traegt selbst eine ID und setzt ausserdem das Raster. Jede spaetere
		// Regel, die eines dieser Masse anfasst, muss deshalb ebenfalls
		// ID-Staerke haben — sonst steht sie da und tut nichts. Genau so
		// standen die Sponsoren am Handy in fuenf Spalten: die Regeln fuer
		// schmale Viewports waren geschrieben, aber zu schwach.
		if ( ! preg_match( '/(^|[;\s])(padding|margin|gap|grid-template-columns|grid-template|display)\b/', $rumpf ) ) {
			continue;
		}
		foreach ( split_selectors( trim( $block[1] ) ) as $sel ) {
			$sel = trim( $sel );
			if ( '' === $sel || 0 === strpos( $sel, '@' ) ) {
				continue;
			}
			// Trifft der Selektor eine unserer Listen als LETZTES Glied?
			$treffer = false;
			foreach ( array_keys( $listen ) as $klasse ) {
				// Auch die Spielarten: `.tha-people--logos` ist dieselbe Liste
				// wie `.tha-people` und verliert genauso gegen `#left-area ul`.
				if ( preg_match( '/\.' . preg_quote( $klasse, '/' ) . '(--[a-z0-9-]+)?\s*$/', $sel ) ) {
					$treffer = true;
					break;
				}
			}
			if ( ! $treffer ) {
				continue;
			}
			$spec = specificity( $sel );
			if ( $spec[0] < 1 ) {
				printf( "FAIL  %-58s %s  verliert gegen #left-area ul\n", $sel, spec_str( $spec ) );
				$id_fails++;
			} else {
				printf( "ok    %-58s %s\n", $sel, spec_str( $spec ) );
				$id_ok++;
			}
		}
	}
}

$fails += $id_fails;

printf( "\n%d Regeln geprüft, %d in Ordnung, %d zu schwach\n", $ok + $id_ok + $fails, $ok + $id_ok, $fails );

if ( $fails > 0 ) {
	echo "\nJede dieser Regeln braucht das doppelte Wurzel-Präfix `.tha.tha `.\n";
	exit( 1 );
}

echo "Alle Komponenten-Regeln schlagen den Reset.\n";
exit( 0 );
