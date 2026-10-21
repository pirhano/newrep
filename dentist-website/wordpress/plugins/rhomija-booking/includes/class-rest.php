<?php
/**
 * Public REST API used by the booking form.
 *
 * Endpoints are anonymous on purpose: no nonce, so full-page caching (LiteSpeed on
 * Hostinger) can never serve a stale one. Abuse is limited by a honeypot, a minimum
 * fill time and a per-visitor rate limit.
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

class RB_Rest {

	const NS         = 'rhomija-booking/v1';
	const RATE_LIMIT = 8; // Successful requests per visitor (IP + browser) per hour.

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		register_rest_route(
			self::NS,
			'/month',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'month' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'month' => array(
						'required'          => true,
						'validate_callback' => static function ( $v ) {
							return is_string( $v ) && (bool) preg_match( '/^\d{4}-\d{2}$/', $v );
						},
					),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/slots',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'slots' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'date' => array(
						'required'          => true,
						'validate_callback' => static function ( $v ) {
							return null !== RB_Availability::parse_date( $v );
						},
					),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/next',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'next' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			self::NS,
			'/appointments',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'book' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Availability changes with every booking: never let a cache keep it.
	 */
	private static function fresh( $data, $status = 200 ) {
		do_action( 'litespeed_control_set_nocache', 'rhomija booking availability' );
		$res = new WP_REST_Response( $data, $status );
		$res->header( 'Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0' );
		$res->header( 'X-LiteSpeed-Cache-Control', 'no-cache' );
		return $res;
	}

	private static function error( $code, $message, $status ) {
		return self::fresh(
			array(
				'ok'      => false,
				'code'    => $code,
				'message' => $message,
			),
			$status
		);
	}

	public static function month( WP_REST_Request $req ) {
		list( $first, $last ) = RB_Availability::window();
		return self::fresh(
			array(
				'month' => $req['month'],
				'first' => $first->format( 'Y-m-d' ),
				'last'  => $last->format( 'Y-m-d' ),
				'days'  => RB_Availability::month( $req['month'] ),
			)
		);
	}

	public static function slots( WP_REST_Request $req ) {
		return self::fresh(
			array(
				'date'  => $req['date'],
				'label' => RB_Appointments::format_date( $req['date'] ),
				'slots' => RB_Availability::slots_for_date( $req['date'] ),
			)
		);
	}

	public static function next() {
		$next = RB_Availability::next_available();
		if ( ! $next ) {
			return self::fresh( array( 'date' => null ) );
		}
		$today    = RB_Availability::now()->format( 'Y-m-d' );
		$tomorrow = RB_Availability::now()->modify( '+1 day' )->format( 'Y-m-d' );
		if ( $next['date'] === $today ) {
			$day = __( 'Aujourd’hui', 'rhomija-booking' );
		} elseif ( $next['date'] === $tomorrow ) {
			$day = __( 'Demain', 'rhomija-booking' );
		} else {
			$day = ucfirst( RB_Appointments::format_date( $next['date'] ) );
		}
		return self::fresh(
			$next + array(
				'label' => $day . ' · ' . RB_Appointments::format_time( $next['time'] ),
			)
		);
	}

	private static function visitor_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$fwd = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $fwd[0] );
		}
		// Mobile carriers share public IPs between many customers, so the browser is part of the key.
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return 'rb_rl_' . md5( $ip . '|' . $ua . wp_salt( 'nonce' ) );
	}

	public static function book( WP_REST_Request $req ) {
		$p = $req->get_json_params();
		if ( ! is_array( $p ) ) {
			$p = $req->get_body_params();
		}
		$get = static function ( $k ) use ( $p ) {
			return isset( $p[ $k ] ) && is_scalar( $p[ $k ] ) ? trim( (string) $p[ $k ] ) : '';
		};

		// Bots: filled honeypot or form submitted implausibly fast.
		if ( '' !== $get( 'website' ) || (int) $get( 'elapsed' ) < 3000 ) {
			return self::error( 'rejected', __( 'Votre demande n’a pas pu être envoyée. Merci de nous appeler.', 'rhomija-booking' ), 400 );
		}

		$key  = self::visitor_key();
		$used = (int) get_transient( $key );
		if ( $used >= self::RATE_LIMIT ) {
			return self::error( 'rate_limited', __( 'Trop de demandes envoyées. Merci de nous appeler directement.', 'rhomija-booking' ), 429 );
		}

		$services = RB_Settings::services();
		$data     = array(
			'service'     => $get( 'service' ),
			'date'        => $get( 'date' ),
			'time'        => $get( 'time' ),
			'name'        => sanitize_text_field( $get( 'name' ) ),
			'phone'       => sanitize_text_field( $get( 'phone' ) ),
			'email'       => sanitize_email( $get( 'email' ) ),
			'message'     => sanitize_textarea_field( $get( 'message' ) ),
			'new_patient' => in_array( $get( 'new_patient' ), array( 'yes', 'no' ), true ) ? $get( 'new_patient' ) : '',
			'status'      => 'pending',
			'source'      => 'web',
		);

		$errors = array();
		if ( ! isset( $services[ $data['service'] ] ) ) {
			$errors['service'] = __( 'Choisissez le motif de votre rendez-vous.', 'rhomija-booking' );
		}
		if ( mb_strlen( $data['name'] ) < 2 || mb_strlen( $data['name'] ) > 80 ) {
			$errors['name'] = __( 'Indiquez votre nom et prénom.', 'rhomija-booking' );
		}
		$digits = strlen( rb_phone_intl( $data['phone'] ) );
		if ( $digits < 9 || $digits > 15 ) {
			$errors['phone'] = __( 'Indiquez un numéro de téléphone valide, ex. 06 12 34 56 78.', 'rhomija-booking' );
		}
		if ( '' !== $get( 'email' ) && ! is_email( $data['email'] ) ) {
			$errors['email'] = __( 'Cette adresse e-mail ne semble pas valide.', 'rhomija-booking' );
		}
		if ( mb_strlen( $data['message'] ) > 1000 ) {
			$errors['message'] = __( 'Votre message est trop long (1000 caractères maximum).', 'rhomija-booking' );
		}
		if ( ! in_array( $get( 'consent' ), array( '1', 'true', 'yes', 'on' ), true ) ) {
			$errors['consent'] = __( 'Merci d’accepter l’utilisation de vos données pour la gestion de votre rendez-vous.', 'rhomija-booking' );
		}
		if ( $errors ) {
			return self::fresh(
				array(
					'ok'      => false,
					'code'    => 'invalid',
					'message' => __( 'Merci de vérifier les champs indiqués.', 'rhomija-booking' ),
					'fields'  => $errors,
				),
				422
			);
		}

		if ( ! RB_Availability::is_available( $data['date'], $data['time'] ) ) {
			return self::error( 'slot_taken', __( 'Ce créneau vient d’être pris ou n’est plus disponible. Merci d’en choisir un autre.', 'rhomija-booking' ), 409 );
		}
		if ( RB_Appointments::has_duplicate( $data['phone'], $data['date'] ) ) {
			return self::error( 'duplicate', __( 'Une demande existe déjà à ce numéro pour ce jour-là. Nous vous recontactons très vite.', 'rhomija-booking' ), 409 );
		}

		$id = RB_Appointments::create( $data );
		if ( is_wp_error( $id ) ) {
			return self::error( 'server', __( 'Une erreur est survenue. Merci de réessayer ou de nous appeler.', 'rhomija-booking' ), 500 );
		}
		set_transient( $key, $used + 1, HOUR_IN_SECONDS );

		RB_Notifications::new_request( $id );
		do_action( 'rb_new_appointment', $id );

		$a = RB_Appointments::get( $id );
		return self::fresh(
			array(
				'ok'         => true,
				'reference'  => 'RDV-' . $id,
				'date_label' => ucfirst( RB_Appointments::format_date( $a['date'] ) ),
				'time_label' => RB_Appointments::format_time( $a['time'] ),
				'service'    => $a['service_label'],
				'whatsapp'   => rb_whatsapp_url( RB_Settings::get( 'whatsapp' ), RB_Notifications::whatsapp_patient_text( $a ) ),
				'ics'        => RB_Notifications::ics( $a ),
			),
			201
		);
	}
}
