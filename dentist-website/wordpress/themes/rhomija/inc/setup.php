<?php
/**
 * Theme supports, menus, assets.
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'rhomija', RH_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor.css' );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 104,
				'width'       => 400,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
		add_theme_support(
			'editor-color-palette',
			array(
				array( 'name' => __( 'Bleu', 'rhomija' ), 'slug' => 'blue', 'color' => '#1c6db5' ),
				array( 'name' => __( 'Turquoise', 'rhomija' ), 'slug' => 'teal', 'color' => '#1fa39a' ),
				array( 'name' => __( 'Encre', 'rhomija' ), 'slug' => 'ink', 'color' => '#14212e' ),
				array( 'name' => __( 'Fond clair', 'rhomija' ), 'slug' => 'soft', 'color' => '#f4f8fb' ),
				array( 'name' => __( 'Blanc', 'rhomija' ), 'slug' => 'white', 'color' => '#ffffff' ),
			)
		);
		register_nav_menus(
			array(
				'primary' => __( 'Menu principal', 'rhomija' ),
				'footer'  => __( 'Menu du pied de page', 'rhomija' ),
			)
		);
		add_image_size( 'rh-card', 800, 500, true );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$css = RH_DIR . '/assets/css/main.css';
		$js  = RH_DIR . '/assets/js/main.js';
		wp_enqueue_style( 'rhomija', RH_URI . '/assets/css/main.css', array(), RH_VERSION . '.' . filemtime( $css ) );
		wp_enqueue_script( 'rhomija', RH_URI . '/assets/js/main.js', array(), RH_VERSION . '.' . filemtime( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script(
			'rhomija',
			'RH',
			array(
				'nextSlotApi' => function_exists( 'rb_get' ) ? esc_url_raw( rest_url( 'rhomija-booking/v1/next' ) ) : '',
			)
		);
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		// The booking form is styled by the theme's tokens; load its stylesheet early on pages that use it.
		if ( is_front_page() || ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'rhomija_booking' ) ) ) {
			wp_enqueue_style( 'rhomija-booking' );
		}
	}
);

add_action(
	'wp_head',
	static function () {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( RH_URI . '/assets/fonts/montserrat-latin.woff2' ) );
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( RH_URI . '/assets/fonts/inter-latin.woff2' ) );
		echo '<meta name="theme-color" content="#1c6db5">' . "\n";
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( RH_URI . '/assets/img/logo-mark.svg' ) );
			printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( RH_URI . '/assets/img/favicon-32.png' ) );
			printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( RH_URI . '/assets/img/apple-touch-icon.png' ) );
		}
	},
	2
);

// Emoji scripts are not needed on this site.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( rh_is_service() ) {
			$classes[] = 'is-service';
		}
		if ( is_singular() && rh_is_elementor() ) {
			$classes[] = 'is-elementor';
		}
		return $classes;
	}
);

add_filter(
	'excerpt_more',
	static function () {
		return '…';
	}
);

add_filter(
	'excerpt_length',
	static function () {
		return 26;
	}
);

/**
 * Add a dropdown toggle button after parent menu items (handled in main.js).
 */
add_filter(
	'walker_nav_menu_start_el',
	static function ( $output, $item, $depth, $args ) {
		if ( isset( $args->theme_location ) && 'primary' === $args->theme_location && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			$output .= '<button class="submenu-toggle" type="button" aria-expanded="false" aria-label="' . esc_attr( sprintf( /* translators: %s: menu item */ __( 'Afficher le sous-menu %s', 'rhomija' ), wp_strip_all_tags( $item->title ) ) ) . '">' . rh_icon( 'chevron', 18 ) . '</button>';
		}
		return $output;
	},
	10,
	4
);

/**
 * Menu shown before the installer has created the real one.
 */
function rh_menu_fallback() {
	$items = array(
		home_url( '/' )                    => __( 'Accueil', 'rhomija' ),
		rh_page_url( 'soins' )             => __( 'Soins', 'rhomija' ),
		rh_page_url( 'cabinet' )           => __( 'Le cabinet', 'rhomija' ),
		rh_page_url( 'conseils' )          => __( 'Conseils', 'rhomija' ),
		rh_page_url( 'contact' )           => __( 'Contact', 'rhomija' ),
	);
	echo '<ul class="menu">';
	foreach ( $items as $url => $label ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

function rh_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$crumbs = array( array( home_url( '/' ), __( 'Accueil', 'rhomija' ) ) );
	if ( is_singular( 'post' ) || ( is_home() && ! is_front_page() ) || is_category() || is_tag() ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog && ! is_home() ) {
			$crumbs[] = array( get_permalink( $blog ), get_the_title( $blog ) );
		}
	}
	if ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $anc ) {
			$crumbs[] = array( get_permalink( $anc ), get_the_title( $anc ) );
		}
	}
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Fil d’Ariane', 'rhomija' ) . '"><ol>';
	foreach ( $crumbs as $c ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $c[0] ), esc_html( $c[1] ) );
	}
	$current = is_home() ? get_the_title( (int) get_option( 'page_for_posts' ) ) : ( is_singular() ? get_the_title() : wp_strip_all_tags( get_the_archive_title() ) );
	if ( is_search() ) {
		$current = __( 'Recherche', 'rhomija' );
	} elseif ( is_404() ) {
		$current = __( 'Page introuvable', 'rhomija' );
	}
	printf( '<li aria-current="page">%s</li>', esc_html( $current ) );
	echo '</ol></nav>';
}

/**
 * If the booking plugin is inactive, the [rhomija_booking] shortcode shows the phone number
 * instead of raw shortcode text.
 */
add_action(
	'init',
	static function () {
		if ( shortcode_exists( 'rhomija_booking' ) ) {
			return;
		}
		add_shortcode(
			'rhomija_booking',
			static function () {
				return '<p class="booking-fallback">' . esc_html__( 'La réservation en ligne est momentanément indisponible. Appelez-nous pour prendre rendez-vous :', 'rhomija' )
					. ' <a href="' . esc_attr( rh_phone_href() ) . '" data-track="call"><strong>' . esc_html( rh_info( 'phone' ) ) . '</strong></a></p>';
			}
		);
	},
	20
);
