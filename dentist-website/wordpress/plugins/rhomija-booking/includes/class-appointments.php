<?php
/**
 * Appointment storage: a private custom post type with meta fields.
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

class RB_Appointments {

	const POST_TYPE = 'rb_appointment';

	/** Meta keys and their sanitizers. */
	const FIELDS = array(
		'_rb_date'        => 'date',
		'_rb_time'        => 'time',
		'_rb_service'     => 'text',
		'_rb_name'        => 'text',
		'_rb_phone'       => 'text',
		'_rb_email'       => 'email',
		'_rb_message'     => 'textarea',
		'_rb_new_patient' => 'text',
		'_rb_status'      => 'status',
		'_rb_source'      => 'text',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	public static function statuses() {
		return array(
			'pending'   => __( 'À confirmer', 'rhomija-booking' ),
			'confirmed' => __( 'Confirmé', 'rhomija-booking' ),
			'done'      => __( 'Honoré', 'rhomija-booking' ),
			'no_show'   => __( 'Absent', 'rhomija-booking' ),
			'cancelled' => __( 'Annulé', 'rhomija-booking' ),
		);
	}

	/** Statuses that occupy a slot. */
	public static function active_statuses() {
		return array( 'pending', 'confirmed' );
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Rendez-vous', 'rhomija-booking' ),
					'singular_name'      => __( 'Rendez-vous', 'rhomija-booking' ),
					'menu_name'          => __( 'Rendez-vous', 'rhomija-booking' ),
					'all_items'          => __( 'Tous les rendez-vous', 'rhomija-booking' ),
					'add_new'            => __( 'Ajouter', 'rhomija-booking' ),
					'add_new_item'       => __( 'Ajouter un rendez-vous', 'rhomija-booking' ),
					'edit_item'          => __( 'Modifier le rendez-vous', 'rhomija-booking' ),
					'new_item'           => __( 'Nouveau rendez-vous', 'rhomija-booking' ),
					'search_items'       => __( 'Rechercher un patient', 'rhomija-booking' ),
					'not_found'          => __( 'Aucun rendez-vous.', 'rhomija-booking' ),
					'not_found_in_trash' => __( 'Aucun rendez-vous dans la corbeille.', 'rhomija-booking' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'menu_position'       => 3,
				'menu_icon'           => 'dashicons-calendar-alt',
				'supports'            => false,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'query_var'           => false,
			)
		);
	}

	public static function sanitize( $type, $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		switch ( $type ) {
			case 'date':
				return RB_Availability::parse_date( $value ) ? $value : '';
			case 'time':
				return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
			case 'email':
				return sanitize_email( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'status':
				return array_key_exists( $value, self::statuses() ) ? $value : 'pending';
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Read an appointment as a plain array.
	 *
	 * @return array<string,mixed>|null
	 */
	public static function get( $id ) {
		$post = get_post( $id );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return null;
		}
		$out = array(
			'id'      => $post->ID,
			'created' => get_post_time( 'U', true, $post ),
		);
		foreach ( array_keys( self::FIELDS ) as $key ) {
			$out[ substr( $key, 4 ) ] = get_post_meta( $post->ID, $key, true );
		}
		$out['service_label'] = self::service_label( $out['service'] );
		return $out;
	}

	public static function service_label( $slug ) {
		$services = RB_Settings::services();
		return isset( $services[ $slug ] ) ? $services[ $slug ] : (string) $slug;
	}

	public static function title_for( array $data ) {
		return trim( $data['name'] . ' — ' . $data['date'] . ' ' . $data['time'] );
	}

	/**
	 * Hidden post content so the admin search also matches phone numbers and e-mails.
	 */
	public static function search_blob( array $data ) {
		return trim( $data['name'] . ' ' . $data['phone'] . ' ' . rb_phone_intl( $data['phone'] ) . ' ' . $data['email'] );
	}

	/**
	 * Create an appointment. $data keys: date, time, service, name, phone, email, message, new_patient, status, source.
	 *
	 * @return int|WP_Error
	 */
	public static function create( array $data ) {
		$clean = array();
		foreach ( self::FIELDS as $key => $type ) {
			$short           = substr( $key, 4 );
			$clean[ $short ] = self::sanitize( $type, isset( $data[ $short ] ) ? $data[ $short ] : '' );
		}
		$id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => self::title_for( $clean ),
				'post_content' => self::search_blob( $clean ),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		self::save_meta( $id, $clean );
		return $id;
	}

	public static function save_meta( $id, array $clean ) {
		foreach ( self::FIELDS as $key => $type ) {
			$short = substr( $key, 4 );
			if ( array_key_exists( $short, $clean ) ) {
				update_post_meta( $id, $key, self::sanitize( $type, $clean[ $short ] ) );
			}
		}
		if ( isset( $clean['date'], $clean['time'] ) ) {
			update_post_meta( $id, '_rb_start', $clean['date'] . ' ' . $clean['time'] );
		}
	}

	public static function set_status( $id, $status ) {
		$status = self::sanitize( 'status', $status );
		$old    = get_post_meta( $id, '_rb_status', true );
		update_post_meta( $id, '_rb_status', $status );
		if ( $old !== $status ) {
			do_action( 'rb_status_changed', $id, $status, $old );
		}
	}

	/**
	 * Has this phone number already an active request on this date?
	 */
	public static function has_duplicate( $phone, $ymd ) {
		$intl = rb_phone_intl( $phone );
		$ids  = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 20,
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => '_rb_date',
						'value' => $ymd,
					),
					array(
						'key'     => '_rb_status',
						'value'   => self::active_statuses(),
						'compare' => 'IN',
					),
				),
			)
		);
		foreach ( $ids as $id ) {
			if ( rb_phone_intl( get_post_meta( $id, '_rb_phone', true ) ) === $intl ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * "mardi 22 octobre 2026"
	 */
	public static function format_date( $ymd ) {
		$d = RB_Availability::parse_date( $ymd );
		return $d ? wp_date( 'l j F Y', $d->getTimestamp(), wp_timezone() ) : (string) $ymd;
	}

	public static function format_time( $hm ) {
		return str_replace( ':', 'h', (string) $hm );
	}
}
