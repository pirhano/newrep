<?php
/**
 * E-mail notifications and WhatsApp message templates.
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

class RB_Notifications {

	public static function init() {
		add_action( 'rb_status_changed', array( __CLASS__, 'on_status_changed' ), 10, 3 );
	}

	private static function address_line() {
		return trim( preg_replace( '/\s*\n\s*/', ', ', (string) RB_Settings::get( 'address' ) ) . ', ' . RB_Settings::get( 'city' ), ', ' );
	}

	/**
	 * Message the clinic sends the patient on WhatsApp to confirm.
	 */
	public static function whatsapp_confirm_text( array $a ) {
		return sprintf(
			/* translators: 1: patient name, 2: clinic, 3: date, 4: time, 5: address */
			__( 'Bonjour %1$s, le %2$s confirme votre rendez-vous le %3$s à %4$s. Adresse : %5$s. Merci de nous prévenir en cas d’empêchement. À bientôt !', 'rhomija-booking' ),
			$a['name'],
			RB_Settings::get( 'clinic_name' ),
			RB_Appointments::format_date( $a['date'] ),
			RB_Appointments::format_time( $a['time'] ),
			self::address_line()
		);
	}

	/**
	 * Message the patient can send the clinic on WhatsApp after booking.
	 */
	public static function whatsapp_patient_text( array $a ) {
		return sprintf(
			/* translators: 1: date, 2: time, 3: service, 4: name */
			__( 'Bonjour, je viens de demander un rendez-vous en ligne le %1$s à %2$s (%3$s). Nom : %4$s.', 'rhomija-booking' ),
			RB_Appointments::format_date( $a['date'] ),
			RB_Appointments::format_time( $a['time'] ),
			$a['service_label'],
			$a['name']
		);
	}

	private static function rows( array $a ) {
		$rows = array(
			__( 'Date', 'rhomija-booking' )      => RB_Appointments::format_date( $a['date'] ),
			__( 'Heure', 'rhomija-booking' )     => RB_Appointments::format_time( $a['time'] ),
			__( 'Motif', 'rhomija-booking' )     => $a['service_label'],
			__( 'Patient', 'rhomija-booking' )   => $a['name'],
			__( 'Téléphone', 'rhomija-booking' ) => $a['phone'],
		);
		if ( $a['email'] ) {
			$rows[ __( 'E-mail', 'rhomija-booking' ) ] = $a['email'];
		}
		if ( $a['new_patient'] ) {
			$rows[ __( 'Nouveau patient', 'rhomija-booking' ) ] = 'yes' === $a['new_patient'] ? __( 'Oui', 'rhomija-booking' ) : __( 'Non', 'rhomija-booking' );
		}
		if ( $a['message'] ) {
			$rows[ __( 'Message', 'rhomija-booking' ) ] = $a['message'];
		}
		return $rows;
	}

	/**
	 * Wrap content in a simple, mobile-friendly HTML e-mail.
	 */
	private static function html( $heading, $intro, array $rows, $buttons = array(), $outro = '' ) {
		$table = '';
		foreach ( $rows as $label => $value ) {
			$table .= '<tr><td style="padding:8px 12px;color:#5b6775;white-space:nowrap;vertical-align:top">' . esc_html( $label ) . '</td>'
				. '<td style="padding:8px 12px;color:#14212e;font-weight:600">' . nl2br( esc_html( $value ) ) . '</td></tr>';
		}
		$btns = '';
		foreach ( $buttons as $label => $url ) {
			$btns .= '<a href="' . esc_url( $url ) . '" style="display:inline-block;margin:4px 8px 4px 0;padding:12px 18px;border-radius:999px;background:#1c6db5;color:#fff;text-decoration:none;font-weight:600">' . esc_html( $label ) . '</a>';
		}
		return '<!doctype html><html><head><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light"></head><body style="margin:0;background:#f3f6f9;font-family:Arial,Helvetica,sans-serif">'
			. '<div style="max-width:560px;margin:0 auto;padding:24px">'
			. '<div style="background:#fff;border-radius:16px;padding:28px;border:1px solid #e3e9ef">'
			. '<p style="margin:0 0 6px;color:#0f766e;font-size:12px;letter-spacing:.12em;text-transform:uppercase;font-weight:700">' . esc_html( RB_Settings::get( 'clinic_name' ) ) . '</p>'
			. '<h1 style="margin:0 0 14px;font-size:22px;color:#14212e">' . esc_html( $heading ) . '</h1>'
			. '<p style="margin:0 0 18px;color:#3a4756;line-height:1.55">' . wp_kses_post( $intro ) . '</p>'
			. '<table style="width:100%;border-collapse:collapse;background:#f7fafc;border-radius:12px">' . $table . '</table>'
			. ( $btns ? '<p style="margin:20px 0 0">' . $btns . '</p>' : '' )
			. ( $outro ? '<p style="margin:20px 0 0;color:#3a4756;line-height:1.55">' . wp_kses_post( $outro ) . '</p>' : '' )
			. '</div>'
			. '<p style="text-align:center;color:#5b6775;font-size:12px;margin:16px 0 0">' . esc_html( self::address_line() ) . ' · ' . esc_html( RB_Settings::get( 'phone' ) ) . '</p>'
			. '</div></body></html>';
	}

	private static function send( $to, $subject, $html, $reply_to = '', $attachments = array() ) {
		if ( ! $to ) {
			return false;
		}
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		if ( $reply_to && is_email( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}
		return wp_mail( $to, $subject, $html, $headers, $attachments );
	}

	public static function new_request( $id ) {
		$a = RB_Appointments::get( $id );
		if ( ! $a ) {
			return;
		}

		// To the clinic.
		$buttons = array(
			__( 'Voir dans l’administration', 'rhomija-booking' ) => admin_url( 'post.php?action=edit&post=' . $id ),
			__( 'Appeler le patient', 'rhomija-booking' )         => 'tel:+' . rb_phone_intl( $a['phone'] ),
		);
		$wa = rb_whatsapp_url( $a['phone'], self::whatsapp_confirm_text( $a ) );
		if ( $wa ) {
			$buttons[ __( 'Confirmer par WhatsApp', 'rhomija-booking' ) ] = $wa;
		}
		self::send(
			RB_Settings::get( 'notify_email' ) ? RB_Settings::get( 'notify_email' ) : get_option( 'admin_email' ),
			sprintf(
				/* translators: 1: name, 2: date, 3: time */
				__( 'Nouvelle demande de RDV — %1$s — %2$s à %3$s', 'rhomija-booking' ),
				$a['name'],
				RB_Appointments::format_date( $a['date'] ),
				RB_Appointments::format_time( $a['time'] )
			),
			self::html(
				__( 'Nouvelle demande de rendez-vous', 'rhomija-booking' ),
				__( 'Une demande vient d’être envoyée depuis le site. Pensez à la confirmer auprès du patient.', 'rhomija-booking' ),
				self::rows( $a ),
				$buttons
			),
			$a['email']
		);

		// Acknowledgement to the patient.
		if ( $a['email'] ) {
			self::send(
				$a['email'],
				__( 'Votre demande de rendez-vous a bien été reçue', 'rhomija-booking' ),
				self::html(
					__( 'Demande bien reçue', 'rhomija-booking' ),
					sprintf(
						/* translators: %s: patient name */
						__( 'Bonjour %s, merci pour votre demande. Le cabinet vous contacte rapidement pour confirmer votre rendez-vous.', 'rhomija-booking' ),
						esc_html( $a['name'] )
					),
					self::rows( $a ),
					array(),
					sprintf(
						/* translators: %s: phone */
						__( 'Besoin de modifier ou d’annuler ? Appelez-nous au %s.', 'rhomija-booking' ),
						esc_html( RB_Settings::get( 'phone' ) )
					)
				),
				RB_Settings::get( 'email' )
			);
		}
	}

	public static function on_status_changed( $id, $status, $old ) {
		$a = RB_Appointments::get( $id );
		if ( ! $a || ! $a['email'] ) {
			return;
		}
		if ( 'confirmed' === $status ) {
			$ics = self::ics_file( $a );
			self::send(
				$a['email'],
				sprintf(
					/* translators: 1: date, 2: time */
					__( 'Rendez-vous confirmé — %1$s à %2$s', 'rhomija-booking' ),
					RB_Appointments::format_date( $a['date'] ),
					RB_Appointments::format_time( $a['time'] )
				),
				self::html(
					__( 'Votre rendez-vous est confirmé', 'rhomija-booking' ),
					sprintf(
						/* translators: %s: patient name */
						__( 'Bonjour %s, nous avons le plaisir de confirmer votre rendez-vous. Merci d’arriver 5 minutes en avance.', 'rhomija-booking' ),
						esc_html( $a['name'] )
					),
					self::rows( $a ) + array( __( 'Adresse', 'rhomija-booking' ) => self::address_line() ),
					array(),
					sprintf(
						/* translators: %s: phone */
						__( 'En cas d’empêchement, merci de nous prévenir au %s.', 'rhomija-booking' ),
						esc_html( RB_Settings::get( 'phone' ) )
					)
				),
				RB_Settings::get( 'email' ),
				$ics ? array( $ics ) : array()
			);
			if ( $ics ) {
				wp_delete_file( $ics );
			}
		} elseif ( 'cancelled' === $status && in_array( $old, RB_Appointments::active_statuses(), true ) ) {
			self::send(
				$a['email'],
				__( 'Votre rendez-vous a été annulé', 'rhomija-booking' ),
				self::html(
					__( 'Rendez-vous annulé', 'rhomija-booking' ),
					sprintf(
						/* translators: 1: patient name, 2: phone */
						__( 'Bonjour %1$s, votre rendez-vous ci-dessous a été annulé. Pour en fixer un nouveau, réservez en ligne ou appelez-nous au %2$s.', 'rhomija-booking' ),
						esc_html( $a['name'] ),
						esc_html( RB_Settings::get( 'phone' ) )
					),
					self::rows( $a ),
					array( __( 'Reprendre rendez-vous', 'rhomija-booking' ) => home_url( '/' ) )
				),
				RB_Settings::get( 'email' )
			);
		}
	}

	/**
	 * Calendar invite for the confirmation e-mail.
	 */
	public static function ics( array $a ) {
		$tz    = wp_timezone();
		$start = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $a['date'] . ' ' . $a['time'], $tz );
		if ( ! $start ) {
			return '';
		}
		$end  = $start->modify( '+' . (int) RB_Settings::get( 'slot_minutes' ) . ' minutes' );
		$utc  = new DateTimeZone( 'UTC' );
		$esc  = static function ( $s ) {
			return addcslashes( (string) $s, ",;\\" );
		};
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		return implode(
			"\r\n",
			array(
				'BEGIN:VCALENDAR',
				'VERSION:2.0',
				'PRODID:-//Rhomija//Rendez-vous//FR',
				'METHOD:PUBLISH',
				'BEGIN:VEVENT',
				'UID:rdv-' . $a['id'] . '@' . $host,
				'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
				'DTSTART:' . $start->setTimezone( $utc )->format( 'Ymd\THis\Z' ),
				'DTEND:' . $end->setTimezone( $utc )->format( 'Ymd\THis\Z' ),
				'SUMMARY:' . $esc( __( 'Rendez-vous dentaire', 'rhomija-booking' ) . ' — ' . RB_Settings::get( 'clinic_name' ) ),
				'LOCATION:' . $esc( self::address_line() ),
				'DESCRIPTION:' . $esc( $a['service_label'] . ' · ' . RB_Settings::get( 'phone' ) ),
				'END:VEVENT',
				'END:VCALENDAR',
			)
		) . "\r\n";
	}

	private static function ics_file( array $a ) {
		$body = self::ics( $a );
		if ( ! $body ) {
			return '';
		}
		$path = trailingslashit( get_temp_dir() ) . 'rendez-vous-' . (int) $a['id'] . '.ics';
		return false !== file_put_contents( $path, $body ) ? $path : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
}

RB_Notifications::init();
