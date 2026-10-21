<?php
/**
 * Service card. Args: a service array from rh_services().
 *
 * @package Rhomija
 */

$rh_s = isset( $args ) ? $args : array();
if ( empty( $rh_s['url'] ) ) {
	return;
}
?>
<article class="service-card<?php echo 'alert' === $rh_s['icon'] ? ' service-card--urgent' : ''; ?>">
	<span class="service-card__icon"><?php echo rh_icon( $rh_s['icon'], 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<h3 class="service-card__title"><a href="<?php echo esc_url( $rh_s['url'] ); ?>"><?php echo esc_html( $rh_s['title'] ); ?></a></h3>
	<p class="service-card__text"><?php echo esc_html( $rh_s['excerpt'] ); ?></p>
	<span class="service-card__more" aria-hidden="true"><?php esc_html_e( 'En savoir plus', 'rhomija' ); ?> <?php echo rh_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
</article>
