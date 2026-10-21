<?php
/**
 * Customizer: Apparence → Personnaliser → « Cabinet ».
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp ) {
		$wp->add_section(
			'rh_cabinet',
			array(
				'title'       => __( 'Cabinet : textes & liens', 'rhomija' ),
				'priority'    => 30,
				'description' => sprintf(
					/* translators: %s: settings link */
					__( 'Téléphone, WhatsApp, adresse et horaires se modifient dans <a href="%s" target="_blank">Rendez-vous → Réglages</a>.', 'rhomija' ),
					esc_url( admin_url( 'edit.php?post_type=rb_appointment&page=rb-settings' ) )
				),
			)
		);

		$fields = array(
			'announcement'       => array( __( 'Bandeau d’annonce (facultatif)', 'rhomija' ), 'text', __( 'Ex. « Cabinet fermé le 18 novembre ». Laissez vide pour le masquer.', 'rhomija' ) ),
			'hero_title'         => array( __( 'Titre principal (accueil)', 'rhomija' ), 'text', '' ),
			'hero_text'          => array( __( 'Texte d’introduction (accueil)', 'rhomija' ), 'textarea', '' ),
			'maps_url'           => array( __( 'Lien Google Maps du cabinet', 'rhomija' ), 'url', __( 'Collez le lien « Partager » de la fiche Google Maps pour un itinéraire précis.', 'rhomija' ) ),
			'google_reviews_url' => array( __( 'Lien « Laisser un avis Google »', 'rhomija' ), 'url', __( 'Depuis votre fiche Google Business Profile → « Demander des avis ».', 'rhomija' ) ),
			'facebook'           => array( __( 'Page Facebook', 'rhomija' ), 'url', '' ),
			'instagram'          => array( __( 'Compte Instagram', 'rhomija' ), 'url', '' ),
		);
		foreach ( $fields as $key => $f ) {
			$wp->add_setting(
				'rh_' . $key,
				array(
					'default'           => rh_mod( $key ),
					'sanitize_callback' => 'url' === $f[1] ? 'esc_url_raw' : ( 'textarea' === $f[1] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
					'transport'         => 'refresh',
				)
			);
			$wp->add_control(
				'rh_' . $key,
				array(
					'label'       => $f[0],
					'section'     => 'rh_cabinet',
					'type'        => $f[1],
					'description' => $f[2],
				)
			);
		}
	}
);
