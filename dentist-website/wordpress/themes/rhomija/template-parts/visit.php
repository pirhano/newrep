<?php
/**
 * Address, contact details, hours and map.
 *
 * @package Rhomija
 */

$rh_wa = rh_whatsapp_url();
?>
<div class="visit">
	<div class="visit__info">
		<ul class="info-cards">
			<li class="info-card">
				<span class="info-card__icon"><?php echo rh_icon( 'pin', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<div>
					<h3><?php esc_html_e( 'Adresse', 'rhomija' ); ?></h3>
					<p><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', rh_address_lines() ) ), array( 'br' => array() ) ); ?></p>
					<a class="link-arrow" href="<?php echo esc_url( rh_maps_url() ); ?>" target="_blank" rel="noopener" data-track="directions"><?php esc_html_e( 'Itinéraire Google Maps', 'rhomija' ); ?> <?php echo rh_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
			</li>
			<li class="info-card">
				<span class="info-card__icon"><?php echo rh_icon( 'phone', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<div>
					<h3><?php esc_html_e( 'Téléphone', 'rhomija' ); ?></h3>
					<p><a class="big-link" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo esc_html( rh_info( 'phone' ) ); ?></a></p>
					<?php if ( $rh_wa ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( $rh_wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php esc_html_e( 'Écrire sur WhatsApp', 'rhomija' ); ?> <?php echo rh_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
				</div>
			</li>
			<li class="info-card">
				<span class="info-card__icon"><?php echo rh_icon( 'mail', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<div>
					<h3><?php esc_html_e( 'E-mail', 'rhomija' ); ?></h3>
					<p><a href="mailto:<?php echo esc_attr( rh_info( 'email' ) ); ?>"><?php echo esc_html( rh_info( 'email' ) ); ?></a></p>
				</div>
			</li>
			<li class="info-card">
				<span class="info-card__icon"><?php echo rh_icon( 'clock', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<div>
					<h3><?php esc_html_e( 'Horaires', 'rhomija' ); ?></h3>
					<?php get_template_part( 'template-parts/hours' ); ?>
				</div>
			</li>
		</ul>
	</div>
	<div class="visit__map">
		<iframe title="<?php esc_attr_e( 'Plan d’accès au cabinet', 'rhomija' ); ?>" src="<?php echo esc_url( rh_maps_embed_src() ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
	</div>
</div>
