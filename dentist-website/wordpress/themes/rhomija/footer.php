<?php
/**
 * Site footer.
 *
 * @package Rhomija
 */

$rh_services = rh_services();
?>
</main>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<img src="<?php echo esc_url( RH_URI . '/assets/img/logo-horizontal-white.svg' ); ?>" width="474" height="100" alt="<?php echo esc_attr( rh_info( 'clinic_name' ) ); ?>">
			<p><?php esc_html_e( 'Cabinet dentaire à Témara : soins, prévention, esthétique, implants et urgences, dans un cadre moderne et rassurant.', 'rhomija' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( rh_booking_url() ); ?>" data-track="cta_booking"><?php esc_html_e( 'Prendre rendez-vous', 'rhomija' ); ?></a>
			<?php if ( rh_mod( 'facebook' ) || rh_mod( 'instagram' ) ) : ?>
				<p class="social">
					<?php if ( rh_mod( 'facebook' ) ) : ?>
						<a href="<?php echo esc_url( rh_mod( 'facebook' ) ); ?>" target="_blank" rel="noopener"><?php echo rh_icon( 'facebook', 22, 'Facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
					<?php if ( rh_mod( 'instagram' ) ) : ?>
						<a href="<?php echo esc_url( rh_mod( 'instagram' ) ); ?>" target="_blank" rel="noopener"><?php echo rh_icon( 'instagram', 22, 'Instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $rh_services ) : ?>
			<div>
				<h2 class="site-footer__title"><?php esc_html_e( 'Nos soins', 'rhomija' ); ?></h2>
				<ul class="site-footer__links">
					<?php foreach ( $rh_services as $s ) : ?>
						<li><a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Horaires', 'rhomija' ); ?></h2>
			<?php get_template_part( 'template-parts/hours' ); ?>
			<?php if ( rh_mod( 'google_reviews_url' ) ) : ?>
				<a class="review-link" href="<?php echo esc_url( rh_mod( 'google_reviews_url' ) ); ?>" target="_blank" rel="noopener" data-track="review">
					<?php echo rh_icon( 'star', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Donnez votre avis sur Google', 'rhomija' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Nous trouver', 'rhomija' ); ?></h2>
			<ul class="contact-list">
				<li><?php echo rh_icon( 'pin', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<a href="<?php echo esc_url( rh_maps_url() ); ?>" target="_blank" rel="noopener" data-track="directions"><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', rh_address_lines() ) ), array( 'br' => array() ) ); ?></a></li>
				<li><?php echo rh_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<a href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo esc_html( rh_info( 'phone' ) ); ?></a></li>
				<?php if ( rh_whatsapp_url() ) : ?>
					<li><?php echo rh_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="<?php echo esc_url( rh_whatsapp_url() ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo esc_html( rh_info( 'whatsapp' ) ); ?></a></li>
				<?php endif; ?>
				<li><?php echo rh_icon( 'mail', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<a href="mailto:<?php echo esc_attr( rh_info( 'email' ) ); ?>"><?php echo esc_html( rh_info( 'email' ) ); ?></a></li>
			</ul>
		</div>
	</div>

	<div class="container site-footer__bottom">
		<p>© <?php echo esc_html( wp_date( 'Y' ) . ' ' . rh_info( 'clinic_name' ) ); ?></p>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'footer-menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
