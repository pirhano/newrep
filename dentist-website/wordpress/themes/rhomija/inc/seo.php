<?php
/**
 * Local SEO: schema.org Dentist data on every page, plus meta description and
 * social sharing tags when no SEO plugin (All in One SEO, Yoast, Rank Math) handles them.
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

function rh_has_seo_plugin() {
	return defined( 'AIOSEO_VERSION' ) || defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' );
}

function rh_schema() {
	$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', rh_info( 'address' ) ) ) );
	$same  = array_values( array_filter( array( rh_mod( 'facebook' ), rh_mod( 'instagram' ) ) ) );
	$data  = array(
		'@context'           => 'https://schema.org',
		'@type'              => 'Dentist',
		'@id'                => home_url( '/#cabinet' ),
		'name'               => rh_info( 'clinic_name' ),
		'url'                => home_url( '/' ),
		'image'              => RH_URI . '/assets/img/partage-social.jpg',
		'logo'               => RH_URI . '/assets/img/icon-512.png',
		'telephone'          => '+' . rh_intl( rh_info( 'phone' ) ),
		'email'              => rh_info( 'email' ),
		'address'            => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => implode( ', ', $lines ),
			'addressLocality' => rh_info( 'city' ),
			'addressRegion'   => 'Rabat-Salé-Kénitra',
			'addressCountry'  => 'MA',
		),
		'hasMap'             => rh_maps_url(),
		'areaServed'         => array( 'Témara', 'Rabat', 'Harhoura', 'Skhirat' ),
		'medicalSpecialty'   => 'Dentistry',
		'isAcceptingNewPatients' => true,
		'potentialAction'    => array(
			'@type'  => 'ReserveAction',
			'target' => rh_booking_url(),
			'name'   => __( 'Prendre rendez-vous', 'rhomija' ),
		),
	);
	if ( function_exists( 'rb_opening_hours_schema' ) ) {
		$data['openingHoursSpecification'] = rb_opening_hours_schema();
	}
	if ( $same ) {
		$data['sameAs'] = $same;
	}
	$services = rh_services();
	if ( $services ) {
		$data['availableService'] = array_map(
			static function ( $s ) {
				return array(
					'@type' => 'MedicalProcedure',
					'name'  => $s['title'],
					'url'   => $s['url'],
				);
			},
			$services
		);
	}
	return $data;
}

add_action(
	'wp_head',
	static function () {
		echo '<script type="application/ld+json">' . wp_json_encode( rh_schema(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
	},
	20
);

function rh_meta_description() {
	if ( is_front_page() ) {
		return sprintf(
			/* translators: %s: city */
			__( 'Cabinet dentaire du Dr Rhomija Nabil à %s : soins, détartrage, implants, esthétique et urgences. Prise de rendez-vous en ligne, du lundi au samedi.', 'rhomija' ),
			rh_info( 'city' )
		);
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( has_excerpt( $post ) ) {
			return wp_strip_all_tags( get_the_excerpt( $post ) );
		}
		return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
	}
	return get_bloginfo( 'description' );
}

add_action(
	'wp_head',
	static function () {
		if ( rh_has_seo_plugin() ) {
			return;
		}
		$desc  = rh_meta_description();
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
		$image = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : RH_URI . '/assets/img/partage-social.jpg';
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
		printf( '<meta property="og:locale" content="fr_FR">' . "\n" );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	},
	3
);

/**
 * Give All in One SEO a sensible default description and image when a page has none.
 */
add_filter(
	'aioseo_description',
	static function ( $description ) {
		return $description ? $description : rh_meta_description();
	}
);
add_filter(
	'aioseo_facebook_tags',
	static function ( $tags ) {
		if ( empty( $tags['og:image'] ) ) {
			$tags['og:image'] = RH_URI . '/assets/img/partage-social.jpg';
		}
		return $tags;
	}
);
