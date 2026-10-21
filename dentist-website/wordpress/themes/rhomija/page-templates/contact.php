<?php
/**
 * Template Name: Contact & accès
 *
 * @package Rhomija
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero' );
	?>
	<section class="section section--tight">
		<div class="container">
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="entry-content entry-content--lead"><?php the_content(); ?></div>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/visit' ); ?>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/cta-band' );
endwhile;

get_footer();
