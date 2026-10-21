<?php
/**
 * Booking call-to-action band. Args: title, text, service_booking.
 *
 * @package Rhomija
 */

$rh_args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'title'           => __( 'Prêt à prendre soin de votre sourire ?', 'rhomija' ),
		'text'            => __( 'Réservez en ligne en moins d’une minute, ou appelez-nous : nous trouvons ensemble le créneau qui vous convient.', 'rhomija' ),
		'service_booking' => '',
	)
);
?>
<section class="cta-band">
	<div class="container cta-band__inner">
		<div>
			<h2 class="cta-band__title"><?php echo esc_html( $rh_args['title'] ); ?></h2>
			<p class="cta-band__text"><?php echo esc_html( $rh_args['text'] ); ?></p>
		</div>
		<div class="btn-row">
			<a class="btn btn--white btn--lg" href="<?php echo esc_url( rh_booking_url( $rh_args['service_booking'] ) ); ?>" data-track="cta_booking"><?php echo rh_icon( 'calendar', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Prendre rendez-vous', 'rhomija' ); ?></a>
			<a class="btn btn--outline-white btn--lg" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo rh_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rh_info( 'phone' ) ); ?></a>
		</div>
	</div>
</section>
