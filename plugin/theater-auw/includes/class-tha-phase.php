<?php
/**
 * Phasenauflösung.
 *
 * Eine Seite, vier Zustände. Die Phase ergibt sich entweder aus dem Kalender
 * (Standard) oder aus der manuellen Auswahl im Backend. Zusätzlich gibt es
 * abgeleitete Unterzustände, die die Emphase steuern — nie das Layout.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Phase {

	/**
	 * Aktuell gültige Phase.
	 */
	public static function current() {
		$settings = Tha_Settings::get();

		// Vorschau künftiger Phasen — nur für die Redaktion, ändert nichts an
		// dem, was Besucherinnen und Besucher sehen.
		$preview = self::preview_request();
		if ( $preview ) {
			return apply_filters( 'theater_auw_phase', $preview, $settings );
		}

		if ( 'manual' === $settings['phase_mode'] ) {
			return apply_filters( 'theater_auw_phase', $settings['phase_manual'], $settings );
		}

		$phase = self::from_schedule( $settings, tha_now() );

		return apply_filters( 'theater_auw_phase', $phase, $settings );
	}

	/**
	 * Reine Funktion — von der Zeit abhängig, sonst von nichts. Genau so wird
	 * sie auch getestet.
	 *
	 * @param array             $settings Options-Array.
	 * @param DateTimeImmutable $now      Zeitpunkt.
	 * @return string
	 */
	public static function from_schedule( array $settings, DateTimeImmutable $now ) {
		$schedule = isset( $settings['phase_schedule'] ) ? $settings['phase_schedule'] : array();

		// Von hinten nach vorne prüfen: die späteste erreichte Schwelle gewinnt.
		$steps = array(
			'danke'      => isset( $schedule['danke_start'] ) ? $schedule['danke_start'] : '',
			'spielzeit'  => isset( $schedule['spielzeit_start'] ) ? $schedule['spielzeit_start'] : '',
			'vorverkauf' => isset( $schedule['vorverkauf_start'] ) ? $schedule['vorverkauf_start'] : '',
		);

		foreach ( $steps as $phase => $raw ) {
			$dt = tha_parse_datetime( $raw );
			if ( $dt && $now >= $dt ) {
				return $phase;
			}
		}

		return 'vorbereitung';
	}

	/**
	 * Vorschau-Parameter `?tha_phase=spielzeit` — nur mit manage_options.
	 */
	public static function preview_request() {
		if ( ! isset( $_GET['tha_phase'] ) || ! current_user_can( 'manage_options' ) ) {
			return '';
		}
		$phase = sanitize_key( wp_unslash( $_GET['tha_phase'] ) );
		return in_array( $phase, Tha_Settings::PHASES, true ) ? $phase : '';
	}

	/**
	 * Läuft der Verkauf schon, oder zeigen wir nur den Countdown darauf?
	 */
	public static function sale_is_live( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		$start    = tha_parse_datetime( $settings['ticket_sale_start'] );
		if ( ! $start ) {
			return true; // Kein Startdatum gesetzt → Verkauf gilt als offen.
		}
		return tha_now() >= $start;
	}

	/**
	 * Menschenlesbares Label für den Ticket-Button — die 2025er-URL zeigt auf
	 * `reservation.ticketleo.com`, also ist «reservieren» der Normalfall.
	 */
	public static function cta_label( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		return 'kaufen' === $settings['ticket_cta_mode']
			? __( 'Tickets kaufen', 'theater-auw' )
			: __( 'Plätze reservieren', 'theater-auw' );
	}

	/**
	 * Alle Vorstellungen der laufenden Saison, angereichert um den effektiven
	 * Status. Vergangene Daten werden automatisch als «vorbei» markiert — die
	 * Admin-Person muss das nicht nachführen.
	 */
	public static function shows( $phase = null, array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		$phase    = $phase ? $phase : self::current();
		$today    = tha_now()->format( 'Y-m-d' );

		$shows = array();
		foreach ( $settings['dates'] as $row ) {
			$row['is_today'] = ( $row['date'] === $today );
			$row['is_past']  = ( $row['date'] < $today );

			$status = $row['status'];
			if ( 'danke' === $phase ) {
				$status = 'vorbei';
			} elseif ( $row['is_past'] ) {
				$status = 'vorbei';
			}
			$row['effective_status'] = $status;

			// Buchbar ist eine Vorstellung nur im Verkauf/Spielbetrieb, wenn sie
			// weder vorbei noch ausverkauft ist und eine Ticket-URL existiert.
			$row['bookable'] = in_array( $phase, array( 'vorverkauf', 'spielzeit' ), true )
				&& ! in_array( $status, array( 'vorbei', 'ausverkauft' ), true )
				&& ( 'spielzeit' === $phase || self::sale_is_live( $settings ) )
				&& '' !== tha_ticket_url( $row );

			$shows[] = $row;
		}
		return $shows;
	}

	/**
	 * Nächste noch nicht gespielte Vorstellung.
	 */
	public static function next_show( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		$today    = tha_now()->format( 'Y-m-d' );
		foreach ( $settings['dates'] as $row ) {
			if ( $row['date'] >= $today ) {
				return $row;
			}
		}
		return null;
	}

	/**
	 * Vorstellung von heute, falls es eine gibt.
	 */
	public static function show_today( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		$today    = tha_now()->format( 'Y-m-d' );
		foreach ( $settings['dates'] as $row ) {
			if ( $row['date'] === $today ) {
				return $row;
			}
		}
		return null;
	}

	/**
	 * Premierendatum — die erste Vorstellung der Liste.
	 */
	public static function premiere( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		return isset( $settings['dates'][0] ) ? $settings['dates'][0] : null;
	}

	/**
	 * Countdown-Ziel je Phase.
	 *
	 * Gibt `ts` als UTC-Epoch in Millisekunden zurück, serverseitig gerechnet.
	 * Das JavaScript macht dann nur noch `ts - Date.now()` — keine Zeitzonen-
	 * mathematik im Browser, korrekt auch wenn WordPress auf UTC steht.
	 * `fallback` wird ins Markup gerendert und bleibt ohne JS stehen.
	 *
	 * @return array|null
	 */
	public static function countdown( $phase = null, array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		$phase    = $phase ? $phase : self::current();
		$now      = tha_now();

		$target = null;
		$label  = '';
		$zero   = ''; // Formulierung für «das ist heute» — sonst stünde da «noch 0 Tage».

		if ( 'vorbereitung' === $phase ) {
			$premiere = self::premiere( $settings );
			if ( $premiere ) {
				$target = tha_parse_datetime( $premiere['date'] . ' ' . str_replace( '.', ':', $premiere['time'] ) );
				$label  = __( 'bis zur Premiere', 'theater-auw' );
				$zero   = __( 'Heute ist Premiere', 'theater-auw' );
			}
		} elseif ( 'vorverkauf' === $phase ) {
			if ( ! self::sale_is_live( $settings ) ) {
				$target = tha_parse_datetime( $settings['ticket_sale_start'] );
				$label  = __( 'bis zum Vorverkauf', 'theater-auw' );
				$zero   = __( 'Der Vorverkauf startet heute', 'theater-auw' );
			} else {
				$premiere = self::premiere( $settings );
				if ( $premiere ) {
					$target = tha_parse_datetime( $premiere['date'] . ' ' . str_replace( '.', ':', $premiere['time'] ) );
					$label  = __( 'bis zur Premiere', 'theater-auw' );
					$zero   = __( 'Heute ist Premiere', 'theater-auw' );
				}
			}
		} elseif ( 'spielzeit' === $phase ) {
			$today = self::show_today( $settings );
			if ( $today ) {
				$target = tha_parse_datetime( $today['date'] . ' ' . str_replace( '.', ':', $today['time'] ) );
				$label  = __( 'bis zur Vorstellung', 'theater-auw' );
				$zero   = sprintf(
					/* translators: %s: Uhrzeit, z.B. «20.00 Uhr» */
					__( 'Heute um %s', 'theater-auw' ),
					tha_time_label( $today['time'] )
				);
			} else {
				$next = self::next_show( $settings );
				if ( $next ) {
					$target = tha_parse_datetime( $next['date'] . ' ' . str_replace( '.', ':', $next['time'] ) );
					$label  = __( 'bis zur nächsten Vorstellung', 'theater-auw' );
				}
			}
		}
		// «Danke» bekommt bewusst keinen Countdown — in einem Abschied tickt
		// keine Uhr.

		if ( ! $target || $target <= $now ) {
			return null;
		}

		$days = (int) $now->setTime( 0, 0 )->diff( $target->setTime( 0, 0 ) )->format( '%a' );

		// «noch 0 Tage bis zur Vorstellung» wäre technisch korrekt und menschlich
		// falsch. Am Tag selbst trägt der Satz sich allein, das Label entfällt.
		if ( 0 === $days && $zero ) {
			$fallback = $zero;
			$label    = '';
		} else {
			$fallback = sprintf(
				/* translators: %d: Anzahl Tage */
				_n( 'noch %d Tag', 'noch %d Tage', $days, 'theater-auw' ),
				$days
			);
		}

		return array(
			'ts'       => $target->getTimestamp() * 1000,
			'label'    => $label,
			'days'     => $days,
			'fallback' => $fallback,
		);
	}
}
