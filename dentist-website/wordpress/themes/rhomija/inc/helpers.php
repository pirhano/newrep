<?php
/**
 * Template helpers. Clinic details come from the booking plugin settings
 * (Rendez-vous → Réglages) so they are edited in a single place.
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clinic detail: clinic_name, phone, whatsapp, email, address, city.
 */
function rh_info( $key ) {
	if ( function_exists( 'rb_get' ) ) {
		return (string) rb_get( $key );
	}
	$fallback = array(
		'clinic_name' => 'Cabinet Dentaire Dr Rhomija Nabil',
		'phone'       => '08 08 66 54 26',
		'whatsapp'    => '',
		'email'       => 'cabinetrhomija@gmail.com',
		'address'     => "N° 14, 3ᵉ étage, Imm. 5, Résidence Arrayhane\nAvenue Tarik Ibn Ziad",
		'city'        => 'Témara',
	);
	return isset( $fallback[ $key ] ) ? $fallback[ $key ] : '';
}

function rh_intl( $phone ) {
	if ( function_exists( 'rb_phone_intl' ) ) {
		return rb_phone_intl( $phone );
	}
	$d = preg_replace( '/\D+/', '', (string) $phone );
	return ( 0 === strpos( $d, '0' ) && 10 === strlen( $d ) ) ? '212' . substr( $d, 1 ) : $d;
}

function rh_phone_href() {
	return 'tel:+' . rh_intl( rh_info( 'phone' ) );
}

function rh_whatsapp_url( $text = '' ) {
	$n = rh_intl( rh_info( 'whatsapp' ) );
	if ( '' === $n ) {
		return '';
	}
	if ( '' === $text ) {
		$text = __( 'Bonjour, je souhaite prendre rendez-vous au cabinet dentaire.', 'rhomija' );
	}
	return 'https://wa.me/' . $n . '?text=' . rawurlencode( $text );
}

/**
 * @return string[]
 */
function rh_address_lines() {
	$lines   = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', rh_info( 'address' ) ) ) );
	$lines[] = rh_info( 'city' );
	return array_values( array_filter( $lines ) );
}

function rh_address_inline() {
	return implode( ', ', rh_address_lines() );
}

/**
 * @return array<string,string> e.g. [ 'Lundi – Samedi' => '09:00 – 17:00', 'Dimanche' => 'Fermé' ]
 */
function rh_hours() {
	if ( function_exists( 'rb_opening_hours_display' ) ) {
		return rb_opening_hours_display();
	}
	return array(
		__( 'Lundi – Samedi', 'rhomija' ) => '09:00 – 17:00',
		__( 'Dimanche', 'rhomija' )       => __( 'Fermé', 'rhomija' ),
	);
}

/**
 * Short form for the top bar: "Lun – Sam · 09:00 – 17:00".
 */
function rh_hours_short() {
	$hours = rh_hours();
	foreach ( $hours as $days => $time ) {
		if ( __( 'Fermé', 'rhomija' ) !== $time && 'Fermé' !== $time ) {
			$short = preg_replace( '/\b(Lun|Mar|Mer|Jeu|Ven|Sam|Dim)[a-zé]*/u', '$1', $days );
			return $short . ' · ' . $time;
		}
	}
	return '';
}

/**
 * Theme option (Apparence → Personnaliser → Cabinet).
 */
function rh_mod( $key ) {
	$defaults = array(
		'hero_title'         => __( 'Votre sourire, entre de bonnes mains à Témara', 'rhomija' ),
		'hero_text'          => __( 'Soins dentaires, esthétique, implants et urgences dans un cabinet moderne et chaleureux. Choisissez votre créneau en ligne : nous vous confirmons rapidement votre rendez-vous.', 'rhomija' ),
		'maps_url'           => '',
		'facebook'           => '',
		'instagram'          => '',
		'google_reviews_url' => '',
		'announcement'       => '',
	);
	return get_theme_mod( 'rh_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

function rh_maps_url() {
	$url = rh_mod( 'maps_url' );
	return $url ? $url : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'Cabinet Dentaire Dr Rhomija Nabil, Résidence Arrayhane, Avenue Tarik Ibn Ziad, Témara' );
}

function rh_maps_embed_src() {
	return 'https://www.google.com/maps?output=embed&q=' . rawurlencode( 'Résidence Arrayhane, Avenue Tarik Ibn Ziad, Témara, Maroc' );
}

/**
 * IDs of the pages created by the installer, keyed by role (accueil, soins, cabinet, rendez-vous, contact, conseils, mentions).
 */
function rh_page_id( $key ) {
	$pages = get_option( 'rh_pages', array() );
	return isset( $pages[ $key ] ) && get_post_status( $pages[ $key ] ) ? (int) $pages[ $key ] : 0;
}

function rh_page_url( $key, $fallback = '' ) {
	$id = rh_page_id( $key );
	return $id ? get_permalink( $id ) : ( $fallback ? $fallback : home_url( '/' ) );
}

/**
 * Booking page URL, optionally pre-selecting a reason (booking service slug).
 */
function rh_booking_url( $service = '' ) {
	$url = rh_page_url( 'rendez-vous', home_url( '/#rendez-vous' ) );
	return $service ? add_query_arg( 'soin', $service, $url ) : $url;
}

/**
 * Responsive <img> for a bundled photo (assets/img/{name}.webp + {name}-800.webp).
 */
function rh_img( $name, $alt, $sizes = '100vw', $attrs = array() ) {
	$dims = array(
		'accueil'        => array( 1600, 1200 ),
		'bureau'         => array( 1600, 1200 ),
		'sterilisation'  => array( 1600, 1200 ),
		'panoramique'    => array( 1200, 1600 ),
		'salle-de-soins' => array( 1600, 1200 ),
	);
	$wh   = isset( $dims[ $name ] ) ? $dims[ $name ] : array( 1600, 1200 );
	$base = RH_URI . '/assets/img/' . $name;
	$html = sprintf(
		'<img src="%1$s.webp" srcset="%1$s-800.webp 800w, %1$s.webp %2$dw" sizes="%3$s" width="%2$d" height="%4$d" alt="%5$s"',
		esc_url( $base ),
		$wh[0],
		esc_attr( $sizes ),
		$wh[1],
		esc_attr( $alt )
	);
	$attrs = wp_parse_args( $attrs, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	foreach ( $attrs as $k => $v ) {
		if ( false !== $v ) {
			$html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
		}
	}
	return $html . '>';
}

/**
 * Service pages (children of the "Soins" page), each with icon, short text and booking reason.
 *
 * @return array<int,array{title:string,url:string,excerpt:string,icon:string,booking:string,id:int}>
 */
function rh_services() {
	$parent = rh_page_id( 'soins' );
	$out    = array();
	if ( $parent ) {
		$pages = get_pages(
			array(
				'parent'      => $parent,
				'sort_column' => 'menu_order,post_title',
			)
		);
		foreach ( $pages as $p ) {
			$out[] = array(
				'id'      => $p->ID,
				'title'   => get_the_title( $p ),
				'url'     => get_permalink( $p ),
				'excerpt' => has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_trim_words( wp_strip_all_tags( $p->post_content ), 22 ),
				'icon'    => get_post_meta( $p->ID, '_rh_icon', true ) ? get_post_meta( $p->ID, '_rh_icon', true ) : 'tooth',
				'booking' => (string) get_post_meta( $p->ID, '_rh_booking', true ),
			);
		}
	}
	return $out;
}

/**
 * Is the current page a service page (child of "Soins")?
 */
function rh_is_service( $post = null ) {
	$post = get_post( $post );
	return $post && 'page' === $post->post_type && rh_page_id( 'soins' ) && (int) $post->post_parent === rh_page_id( 'soins' );
}

/**
 * Was this page built with Elementor? Then the theme steps aside and renders it full width.
 */
function rh_is_elementor( $id = null ) {
	$id = $id ? $id : get_the_ID();
	if ( ! $id || ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	$doc = \Elementor\Plugin::$instance->documents->get( $id );
	return $doc && $doc->is_built_with_elementor();
}
