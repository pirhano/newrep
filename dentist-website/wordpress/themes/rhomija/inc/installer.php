<?php
/**
 * One-click installer: Apparence → Installation du site.
 * Creates the pages, service pages, articles and menus, and sets the home page.
 * Safe to run again: existing pages are reused and never overwritten.
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	static function () {
		add_theme_page(
			__( 'Installation du site', 'rhomija' ),
			__( 'Installation du site', 'rhomija' ),
			'manage_options',
			'rh-install',
			'rh_install_page'
		);
	}
);

add_action(
	'admin_notices',
	static function () {
		if ( ! current_user_can( 'manage_options' ) || get_option( 'rh_installed' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && 'appearance_page_rh-install' === $screen->id ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p><strong>%s</strong> %s</p><p><a class="button button-primary" href="%s">%s</a></p></div>',
			esc_html__( 'Thème Rhomija Dentaire activé.', 'rhomija' ),
			esc_html__( 'Il reste une étape : installer les pages du site (accueil, soins, cabinet, rendez-vous, contact…).', 'rhomija' ),
			esc_url( admin_url( 'themes.php?page=rh-install' ) ),
			esc_html__( 'Installer le site', 'rhomija' )
		);
	}
);

add_action( 'admin_post_rh_install', 'rh_handle_install' );

function rh_handle_install() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'rhomija' ) );
	}
	check_admin_referer( 'rh_install' );
	$report = rh_install();
	set_transient( 'rh_install_report', $report, 300 );
	wp_safe_redirect( admin_url( 'themes.php?page=rh-install&done=1' ) );
	exit;
}

function rh_install_page() {
	$report     = get_transient( 'rh_install_report' );
	$plugin_ok  = function_exists( 'rb_get' );
	$installed  = get_option( 'rh_installed' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Installation du site', 'rhomija' ); ?></h1>

		<?php if ( $report && isset( $_GET['done'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Le site est installé.', 'rhomija' ); ?></strong>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'Voir le site', 'rhomija' ); ?></a></p>
				<ul style="list-style:disc;padding-left:20px">
					<?php foreach ( $report as $line ) : ?>
						<li><?php echo esc_html( $line ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( ! $plugin_ok ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'L’extension « Rhomija — Prise de rendez-vous » n’est pas active. Activez-la d’abord (Extensions) pour que la réservation en ligne fonctionne.', 'rhomija' ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Ce bouton crée le contenu de départ du site. Les pages qui existent déjà (même adresse) sont conservées telles quelles, vous pouvez donc le relancer sans risque.', 'rhomija' ); ?></p>
		<ul style="list-style:disc;padding-left:20px">
			<li><?php esc_html_e( 'Pages : Accueil, Nos soins (+ 9 pages de soins), Le cabinet, Prendre rendez-vous, Contact & accès, Conseils dentaires, Mentions légales & confidentialité ;', 'rhomija' ); ?></li>
			<li><?php esc_html_e( '3 articles de conseils pour le référencement ;', 'rhomija' ); ?></li>
			<li><?php esc_html_e( 'Menu principal et menu du pied de page ;', 'rhomija' ); ?></li>
			<li><?php esc_html_e( 'Page d’accueil statique, page des articles, page de confidentialité, permaliens lisibles, fuseau horaire du Maroc, langue française ;', 'rhomija' ); ?></li>
			<li><?php esc_html_e( 'Titre du site : « Cabinet Dentaire Dr Rhomija Nabil » et slogan.', 'rhomija' ); ?></li>
		</ul>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="rh_install">
			<?php wp_nonce_field( 'rh_install' ); ?>
			<?php submit_button( $installed ? __( 'Relancer l’installation', 'rhomija' ) : __( 'Installer le site', 'rhomija' ), 'primary large' ); ?>
		</form>

		<h2><?php esc_html_e( 'Ensuite', 'rhomija' ); ?></h2>
		<ol>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rb_appointment&page=rb-settings' ) ); ?>"><?php esc_html_e( 'Rendez-vous → Réglages', 'rhomija' ); ?></a> : <?php esc_html_e( 'vérifier téléphone, numéro WhatsApp, horaires et jours de fermeture.', 'rhomija' ); ?></li>
			<li><a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=rh_cabinet' ) ); ?>"><?php esc_html_e( 'Personnaliser → Cabinet', 'rhomija' ); ?></a> : <?php esc_html_e( 'lien Google Maps, lien d’avis Google, réseaux sociaux.', 'rhomija' ); ?></li>
			<li><?php esc_html_e( 'Faire une réservation de test et vérifier la réception de l’e-mail.', 'rhomija' ); ?></li>
		</ol>
	</div>
	<?php
}

/**
 * Find a published page or post by slug (and parent for pages).
 */
function rh_find( $slug, $type = 'page', $parent = 0 ) {
	$args = array(
		'name'           => $slug,
		'post_type'      => $type,
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);
	if ( 'page' === $type ) {
		$args['post_parent'] = $parent;
	}
	$ids = get_posts( $args );
	return $ids ? (int) $ids[0] : 0;
}

function rh_create_post( array $args, &$report, $label ) {
	$id = wp_insert_post(
		wp_parse_args(
			$args,
			array(
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		$report[] = $label . ' : ' . $id->get_error_message();
		return 0;
	}
	update_post_meta( $id, '_rh_installed', RH_VERSION );
	$report[] = sprintf( /* translators: %s: title */ __( 'Créé : %s', 'rhomija' ), $label );
	return $id;
}

/**
 * @return string[] Human-readable report.
 */
function rh_install() {
	$report = array();
	$pages  = get_option( 'rh_pages', array() );
	$author = get_current_user_id() ? get_current_user_id() : 1;

	// Pages.
	foreach ( rh_default_pages() as $key => $p ) {
		$id = rh_find( $p['slug'] );
		if ( $id ) {
			$report[] = sprintf( /* translators: %s: title */ __( 'Déjà présent, conservé : %s', 'rhomija' ), get_the_title( $id ) );
		} else {
			$id = rh_create_post(
				array(
					'post_type'    => 'page',
					'post_title'   => $p['title'],
					'post_name'    => $p['slug'],
					'post_content' => rh_to_blocks( rh_fill_tokens( $p['content'] ) ),
					'post_excerpt' => $p['excerpt'],
					'post_author'  => $author,
				),
				$report,
				$p['title']
			);
			if ( $id && $p['template'] ) {
				update_post_meta( $id, '_wp_page_template', $p['template'] );
			}
		}
		if ( $id ) {
			$pages[ $key ] = $id;
		}
	}
	update_option( 'rh_pages', $pages );
	add_post_type_support( 'page', 'excerpt' );

	// Service pages under "Nos soins".
	$order = 0;
	foreach ( rh_default_services() as $s ) {
		++$order;
		if ( empty( $pages['soins'] ) ) {
			break;
		}
		$id = rh_find( $s['slug'], 'page', $pages['soins'] );
		if ( $id ) {
			$report[] = sprintf( /* translators: %s: title */ __( 'Déjà présent, conservé : %s', 'rhomija' ), get_the_title( $id ) );
			continue;
		}
		$id = rh_create_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $s['title'],
				'post_name'    => $s['slug'],
				'post_parent'  => $pages['soins'],
				'menu_order'   => $order,
				'post_content' => rh_to_blocks( rh_fill_tokens( $s['content'] ) ),
				'post_excerpt' => $s['excerpt'],
				'post_author'  => $author,
			),
			$report,
			$s['title']
		);
		if ( $id ) {
			update_post_meta( $id, '_wp_page_template', 'page-templates/soin.php' );
			update_post_meta( $id, '_rh_icon', $s['icon'] );
			update_post_meta( $id, '_rh_booking', $s['booking'] );
		}
	}

	// Articles.
	$cat = term_exists( 'conseils-dentaires', 'category' );
	if ( ! $cat ) {
		$cat = wp_insert_term( 'Conseils dentaires', 'category', array( 'slug' => 'conseils-dentaires' ) );
	}
	$cat_id = is_array( $cat ) ? (int) $cat['term_id'] : 0;
	foreach ( rh_default_posts() as $i => $p ) {
		if ( rh_find( $p['slug'], 'post' ) ) {
			continue;
		}
		rh_create_post(
			array(
				'post_type'     => 'post',
				'post_title'    => $p['title'],
				'post_name'     => $p['slug'],
				'post_content'  => rh_to_blocks( rh_fill_tokens( $p['content'] ) ),
				'post_excerpt'  => $p['excerpt'],
				'post_author'   => $author,
				'post_category' => $cat_id ? array( $cat_id ) : array(),
				'post_date'     => wp_date( 'Y-m-d H:i:s', time() - ( 3 - $i ) * DAY_IN_SECONDS ),
			),
			$report,
			$p['title']
		);
	}

	// Reading settings.
	if ( ! empty( $pages['accueil'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['accueil'] );
	}
	if ( ! empty( $pages['conseils'] ) ) {
		update_option( 'page_for_posts', $pages['conseils'] );
	}
	if ( ! empty( $pages['mentions'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $pages['mentions'] );
	}
	update_option( 'blogname', 'Cabinet Dentaire Dr Rhomija Nabil' );
	update_option( 'blogdescription', 'Dentiste à Témara — soins, esthétique, implants et urgences' );
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	update_option( 'timezone_string', 'Africa/Casablanca' );
	if ( 'fr_FR' !== get_option( 'WPLANG' ) ) {
		if ( ! in_array( 'fr_FR', get_available_languages(), true ) ) {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			wp_download_language_pack( 'fr_FR' );
		}
		if ( in_array( 'fr_FR', get_available_languages(), true ) ) {
			update_option( 'WPLANG', 'fr_FR' );
			$report[] = __( 'Langue du site : français.', 'rhomija' );
		} else {
			$report[] = __( 'Impossible d’installer le français : choisissez « Français » dans Réglages → Général → Langue du site.', 'rhomija' );
		}
	}
	$report[] = __( 'Réglages : page d’accueil, page des articles, confidentialité, titre du site, fuseau horaire Africa/Casablanca.', 'rhomija' );

	rh_install_menus( $pages, $report );

	flush_rewrite_rules();
	update_option( 'rh_installed', RH_VERSION );
	return $report;
}

function rh_install_menus( array $pages, array &$report ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	$primary = wp_get_nav_menu_object( 'Menu principal' );
	if ( ! $primary ) {
		$menu_id = wp_create_nav_menu( 'Menu principal' );
		if ( ! is_wp_error( $menu_id ) ) {
			$add_page = static function ( $page_id, $parent = 0, $title = '' ) use ( $menu_id ) {
				return wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $title ? $title : get_the_title( $page_id ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page_id,
						'menu-item-type'      => 'post_type',
						'menu-item-parent-id' => $parent,
						'menu-item-status'    => 'publish',
					)
				);
			};
			if ( ! empty( $pages['accueil'] ) ) {
				$add_page( $pages['accueil'], 0, 'Accueil' );
			}
			if ( ! empty( $pages['soins'] ) ) {
				$soins_item = $add_page( $pages['soins'], 0, 'Soins' );
				foreach ( get_pages( array( 'parent' => $pages['soins'], 'sort_column' => 'menu_order' ) ) as $child ) {
					$add_page( $child->ID, $soins_item );
				}
			}
			foreach ( array( 'cabinet' => 'Le cabinet', 'conseils' => 'Conseils', 'contact' => 'Contact' ) as $key => $title ) {
				if ( ! empty( $pages[ $key ] ) ) {
					$add_page( $pages[ $key ], 0, $title );
				}
			}
			$locations['primary'] = $menu_id;
			$report[]             = __( 'Créé : Menu principal', 'rhomija' );
		}
	} elseif ( empty( $locations['primary'] ) ) {
		$locations['primary'] = $primary->term_id;
	}

	$footer = wp_get_nav_menu_object( 'Pied de page' );
	if ( ! $footer ) {
		$menu_id = wp_create_nav_menu( 'Pied de page' );
		if ( ! is_wp_error( $menu_id ) ) {
			foreach ( array( 'rendez-vous', 'contact', 'mentions' ) as $key ) {
				if ( ! empty( $pages[ $key ] ) ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-object'    => 'page',
							'menu-item-object-id' => $pages[ $key ],
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
			$locations['footer'] = $menu_id;
			$report[]            = __( 'Créé : menu du pied de page', 'rhomija' );
		}
	} elseif ( empty( $locations['footer'] ) ) {
		$locations['footer'] = $footer->term_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

// Pages get an excerpt box: it is used as the intro and meta description of each page.
add_action(
	'init',
	static function () {
		add_post_type_support( 'page', 'excerpt' );
	}
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'rhomija install',
		static function () {
			foreach ( rh_install() as $line ) {
				WP_CLI::log( $line );
			}
			WP_CLI::success( 'Site installé.' );
		}
	);
}
