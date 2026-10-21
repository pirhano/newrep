<?php
/**
 * Template Name: Le cabinet
 *
 * @package Rhomija
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero' );
	?>
	<section class="section section--tight">
		<div class="container split split--top">
			<figure class="photo-frame">
				<?php echo rh_img( 'bureau', __( 'Bureau de consultation du cabinet', 'rhomija' ), '(min-width: 960px) 45vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</figure>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</div>
	</section>

	<section class="section section--soft">
		<div class="container">
			<header class="section-head section-head--center">
				<p class="eyebrow"><?php esc_html_e( 'Visite', 'rhomija' ); ?></p>
				<h2 class="section-title"><?php esc_html_e( 'Découvrez nos locaux', 'rhomija' ); ?></h2>
			</header>
			<div class="gallery">
				<figure><?php echo rh_img( 'accueil', __( 'Accueil et salle d’attente', 'rhomija' ), '(min-width: 960px) 33vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><figcaption><?php esc_html_e( 'Accueil et salle d’attente', 'rhomija' ); ?></figcaption></figure>
				<figure><?php echo rh_img( 'salle-de-soins', __( 'Salle de soins', 'rhomija' ), '(min-width: 960px) 33vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><figcaption><?php esc_html_e( 'Salle de soins', 'rhomija' ); ?></figcaption></figure>
				<figure><?php echo rh_img( 'sterilisation', __( 'Salle de stérilisation', 'rhomija' ), '(min-width: 960px) 33vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><figcaption><?php esc_html_e( 'Salle de stérilisation', 'rhomija' ); ?></figcaption></figure>
				<figure><?php echo rh_img( 'panoramique', __( 'Radiographie panoramique', 'rhomija' ), '(min-width: 960px) 33vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><figcaption><?php esc_html_e( 'Radiographie panoramique', 'rhomija' ); ?></figcaption></figure>
				<figure><?php echo rh_img( 'bureau', __( 'Bureau de consultation', 'rhomija' ), '(min-width: 960px) 33vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><figcaption><?php esc_html_e( 'Bureau de consultation', 'rhomija' ); ?></figcaption></figure>
			</div>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/cta-band' );
endwhile;

get_footer();
