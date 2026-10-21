<?php
/**
 * Page title band. Args: title, intro, cta (bool), service_booking (string), icon (string).
 *
 * @package Rhomija
 */

$rh_args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'title'           => get_the_title(),
		'intro'           => has_excerpt() ? get_the_excerpt() : '',
		'cta'             => true,
		'service_booking' => '',
		'icon'            => '',
	)
);
?>
<section class="page-hero">
	<div class="container">
		<?php rh_breadcrumbs(); ?>
		<div class="page-hero__inner">
			<?php if ( $rh_args['icon'] ) : ?>
				<span class="page-hero__icon"><?php echo rh_icon( $rh_args['icon'], 34 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
			<h1 class="page-hero__title"><?php echo esc_html( $rh_args['title'] ); ?></h1>
			<?php if ( $rh_args['intro'] ) : ?>
				<p class="page-hero__intro"><?php echo esc_html( $rh_args['intro'] ); ?></p>
			<?php endif; ?>
			<?php if ( $rh_args['cta'] ) : ?>
				<div class="btn-row">
					<a class="btn btn--primary btn--lg" href="<?php echo esc_url( rh_booking_url( $rh_args['service_booking'] ) ); ?>" data-track="cta_booking"><?php echo rh_icon( 'calendar', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Prendre rendez-vous', 'rhomija' ); ?></a>
					<a class="btn btn--ghost btn--lg" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo rh_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rh_info( 'phone' ) ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
