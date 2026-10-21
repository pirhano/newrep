<?php
/**
 * Plugin Name:       Rhomija — Prise de rendez-vous
 * Description:       Réservation de rendez-vous en ligne pour le Cabinet Dentaire Dr Rhomija Nabil : créneaux disponibles, demandes de RDV, confirmation par l'équipe, notifications e-mail et WhatsApp.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Cabinet Dentaire Dr Rhomija Nabil
 * License:           GPL-2.0-or-later
 * Text Domain:       rhomija-booking
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

define( 'RB_VERSION', '1.0.0' );
define( 'RB_FILE', __FILE__ );
define( 'RB_DIR', plugin_dir_path( __FILE__ ) );
define( 'RB_URL', plugin_dir_url( __FILE__ ) );

require_once RB_DIR . 'includes/class-settings.php';
require_once RB_DIR . 'includes/class-availability.php';
require_once RB_DIR . 'includes/class-appointments.php';
require_once RB_DIR . 'includes/class-notifications.php';
require_once RB_DIR . 'includes/class-rest.php';
require_once RB_DIR . 'includes/class-form.php';
require_once RB_DIR . 'includes/class-admin.php';

RB_Settings::init();
RB_Appointments::init();
RB_Rest::init();
RB_Form::init();
RB_Admin::init();

register_activation_hook( __FILE__, 'rb_activate' );

/**
 * Activation: register the post type so rewrite rules are correct and make sure
 * the site runs on Moroccan time, which every slot calculation depends on.
 * (French WordPress installs default to Europe/Paris, which is wrong for Témara.)
 */
function rb_activate() {
	RB_Appointments::register_post_type();
	flush_rewrite_rules();

	if ( 0 !== strpos( (string) get_option( 'timezone_string' ), 'Africa/' ) ) {
		update_option( 'timezone_string', 'Africa/Casablanca' );
	}

	if ( false === get_option( RB_Settings::OPTION ) ) {
		add_option( RB_Settings::OPTION, RB_Settings::defaults() );
	}
}

/**
 * Public helpers used by the theme to display the clinic's details, so they are
 * edited in one place (Rendez-vous → Réglages).
 */
function rb_get( $key ) {
	return RB_Settings::get( $key );
}

/**
 * Opening hours grouped for display, e.g. [ 'Lundi – Samedi' => '09:00 – 17:00', 'Dimanche' => 'Fermé' ].
 *
 * @return array<string,string>
 */
function rb_opening_hours_display() {
	return RB_Availability::hours_display();
}

/**
 * Opening hours in schema.org OpeningHoursSpecification form.
 *
 * @return array<int,array<string,mixed>>
 */
function rb_opening_hours_schema() {
	return RB_Availability::hours_schema();
}

/**
 * Normalise a phone number to international digits (Morocco by default): "06 12 34 56 78" → "212612345678".
 */
function rb_phone_intl( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	if ( '' === $digits ) {
		return '';
	}
	if ( 0 === strpos( $digits, '00' ) ) {
		return substr( $digits, 2 );
	}
	if ( 0 === strpos( $digits, '0' ) && 10 === strlen( $digits ) ) {
		return '212' . substr( $digits, 1 );
	}
	return $digits;
}

/**
 * wa.me link for a number, with an optional pre-filled message.
 */
function rb_whatsapp_url( $phone, $text = '' ) {
	$intl = rb_phone_intl( $phone );
	if ( '' === $intl ) {
		return '';
	}
	$url = 'https://wa.me/' . $intl;
	return '' === $text ? $url : $url . '?text=' . rawurlencode( $text );
}
