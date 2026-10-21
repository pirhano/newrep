<?php
/**
 * Site header.
 *
 * @package Rhomija
 */

$rh_wa = rh_whatsapp_url();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="color-scheme" content="light only">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenu"><?php esc_html_e( 'Aller au contenu', 'rhomija' ); ?></a>

<?php if ( rh_mod( 'announcement' ) ) : ?>
	<div class="announcement" role="note"><?php echo esc_html( rh_mod( 'announcement' ) ); ?></div>
<?php endif; ?>

<div class="topbar">
	<div class="container topbar__inner">
		<a class="topbar__item" href="<?php echo esc_url( rh_maps_url() ); ?>" target="_blank" rel="noopener" data-track="directions">
			<?php echo rh_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html( implode( ', ', array_slice( rh_address_lines(), -2 ) ) ); ?></span>
		</a>
		<span class="topbar__item">
			<?php echo rh_icon( 'clock', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html( rh_hours_short() ); ?></span>
		</span>
		<a class="topbar__item topbar__item--end" href="mailto:<?php echo esc_attr( rh_info( 'email' ) ); ?>">
			<?php echo rh_icon( 'mail', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html( rh_info( 'email' ) ); ?></span>
		</a>
	</div>
</div>

<header class="site-header" data-header>
	<div class="container site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand__logo', 'alt' => get_bloginfo( 'name' ) ) ); ?>
			<?php else : ?>
				<img class="brand__logo" src="<?php echo esc_url( RH_URI . '/assets/img/logo-horizontal.svg' ); ?>" width="474" height="100" alt="<?php echo esc_attr( rh_info( 'clinic_name' ) ); ?>">
			<?php endif; ?>
		</a>

		<nav class="nav" id="menu" aria-label="<?php esc_attr_e( 'Menu principal', 'rhomija' ); ?>" data-nav>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'rh_menu_fallback',
				)
			);
			?>
			<div class="nav__mobile-cta">
				<a class="btn btn--primary btn--block" href="<?php echo esc_url( rh_booking_url() ); ?>" data-track="cta_booking"><?php echo rh_icon( 'calendar', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Prendre rendez-vous', 'rhomija' ); ?></a>
				<a class="btn btn--ghost btn--block" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo rh_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rh_info( 'phone' ) ); ?></a>
			</div>
		</nav>

		<div class="site-header__actions">
			<a class="header-phone" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call">
				<span class="header-phone__icon"><?php echo rh_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="header-phone__text"><small><?php esc_html_e( 'Appelez-nous', 'rhomija' ); ?></small><?php echo esc_html( rh_info( 'phone' ) ); ?></span>
			</a>
			<a class="btn btn--primary header-cta" href="<?php echo esc_url( rh_booking_url() ); ?>" data-track="cta_booking"><?php esc_html_e( 'Prendre rendez-vous', 'rhomija' ); ?></a>
			<button class="nav-toggle" type="button" aria-controls="menu" aria-expanded="false" data-nav-toggle>
				<span class="nav-toggle__open"><?php echo rh_icon( 'menu', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="nav-toggle__close"><?php echo rh_icon( 'close', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'rhomija' ); ?></span>
			</button>
		</div>
	</div>
</header>

<div class="mobile-bar" aria-label="<?php esc_attr_e( 'Contact rapide', 'rhomija' ); ?>">
	<a href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo rh_icon( 'phone', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Appeler', 'rhomija' ); ?></span></a>
	<?php if ( $rh_wa ) : ?>
		<a href="<?php echo esc_url( $rh_wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo rh_icon( 'whatsapp', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>WhatsApp</span></a>
	<?php else : ?>
		<a href="<?php echo esc_url( rh_maps_url() ); ?>" target="_blank" rel="noopener" data-track="directions"><?php echo rh_icon( 'directions', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Itinéraire', 'rhomija' ); ?></span></a>
	<?php endif; ?>
	<a class="mobile-bar__cta" href="<?php echo esc_url( rh_booking_url() ); ?>" data-track="cta_booking"><?php echo rh_icon( 'calendar', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Rendez-vous', 'rhomija' ); ?></span></a>
</div>

<main id="contenu" class="site-main" tabindex="-1">
