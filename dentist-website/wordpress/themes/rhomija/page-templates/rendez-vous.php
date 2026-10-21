<?php
/**
 * Template Name: Prise de rendez-vous
 *
 * @package Rhomija
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero', null, array( 'cta' => false ) );
	?>
	<section class="section section--tight booking-section">
		<div class="container booking-layout booking-layout--page">
			<div class="card booking-layout__form">
				<div class="entry-content entry-content--booking">
					<?php the_content(); ?>
				</div>
			</div>
			<aside class="booking-layout__intro">
				<div class="card aside-card">
					<h2 class="aside-card__title"><?php esc_html_e( 'Bon à savoir', 'rhomija' ); ?></h2>
					<ul class="check-list">
						<li><?php echo rh_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Les créneaux affichés sont libres en temps réel.', 'rhomija' ); ?></li>
						<li><?php echo rh_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Nous vous contactons pour confirmer le rendez-vous.', 'rhomija' ); ?></li>
						<li><?php echo rh_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Pensez à apporter vos radiographies récentes et votre ordonnance en cours.', 'rhomija' ); ?></li>
					</ul>
					<div class="aside-card__hours">
						<h3><?php esc_html_e( 'Horaires', 'rhomija' ); ?></h3>
						<?php get_template_part( 'template-parts/hours' ); ?>
					</div>
					<p class="booking-layout__urgent">
						<?php echo rh_icon( 'alert', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Urgence ?', 'rhomija' ); ?> <a href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo esc_html( sprintf( /* translators: %s: phone */ __( 'Appelez le %s', 'rhomija' ), rh_info( 'phone' ) ) ); ?></a></span>
					</p>
				</div>
			</aside>
		</div>
	</section>
	<?php
endwhile;

get_footer();
