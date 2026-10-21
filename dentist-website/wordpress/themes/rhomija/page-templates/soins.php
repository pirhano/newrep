<?php
/**
 * Template Name: Liste des soins
 *
 * @package Rhomija
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero' );
	?>
	<section class="section">
		<div class="container">
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="entry-content entry-content--lead"><?php the_content(); ?></div>
			<?php endif; ?>
			<div class="service-grid">
				<?php
				foreach ( rh_services() as $rh_s ) {
					get_template_part( 'template-parts/service-card', null, $rh_s );
				}
				?>
			</div>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/cta-band' );
endwhile;

get_footer();
