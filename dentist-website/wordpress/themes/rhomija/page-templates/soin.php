<?php
/**
 * Template Name: Page de soin
 *
 * @package Rhomija
 */

get_header();

while ( have_posts() ) :
	the_post();
	$rh_booking = (string) get_post_meta( get_the_ID(), '_rh_booking', true );
	$rh_icon    = (string) get_post_meta( get_the_ID(), '_rh_icon', true );
	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'service_booking' => $rh_booking,
			'icon'            => $rh_icon ? $rh_icon : 'tooth',
		)
	);
	?>
	<div class="section section--tight">
		<div class="container with-aside">
			<article <?php post_class( 'entry' ); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<aside class="aside">
				<div class="card aside-card">
					<h2 class="aside-card__title"><?php esc_html_e( 'Prendre rendez-vous', 'rhomija' ); ?></h2>
					<p><?php esc_html_e( 'Choisissez un créneau libre en ligne, nous vous confirmons rapidement.', 'rhomija' ); ?></p>
					<a class="btn btn--primary btn--block" href="<?php echo esc_url( rh_booking_url( $rh_booking ) ); ?>" data-track="cta_booking"><?php echo rh_icon( 'calendar', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Réserver en ligne', 'rhomija' ); ?></a>
					<a class="btn btn--ghost btn--block" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo rh_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rh_info( 'phone' ) ); ?></a>
					<?php if ( rh_whatsapp_url() ) : ?>
						<a class="btn btn--whatsapp btn--block" href="<?php echo esc_url( rh_whatsapp_url() ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo rh_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> WhatsApp</a>
					<?php endif; ?>
					<div class="aside-card__hours">
						<h3><?php esc_html_e( 'Horaires', 'rhomija' ); ?></h3>
						<?php get_template_part( 'template-parts/hours' ); ?>
					</div>
				</div>
			</aside>
		</div>
	</div>

	<?php
	$rh_others = array_filter(
		rh_services(),
		static function ( $s ) {
			return get_the_ID() !== $s['id'];
		}
	);
	if ( $rh_others ) :
		?>
		<section class="section section--soft">
			<div class="container">
				<header class="section-head">
					<h2 class="section-title"><?php esc_html_e( 'Nos autres soins', 'rhomija' ); ?></h2>
				</header>
				<div class="service-grid service-grid--compact">
					<?php
					foreach ( array_slice( $rh_others, 0, 3 ) as $rh_s ) {
						get_template_part( 'template-parts/service-card', null, $rh_s );
					}
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
	get_template_part( 'template-parts/cta-band', null, array( 'service_booking' => $rh_booking ) );
endwhile;

get_footer();
