<?php
/**
 * Default page. Pages built with Elementor are rendered full width without the title band.
 *
 * @package Rhomija
 */

get_header();

while ( have_posts() ) :
	the_post();
	if ( rh_is_elementor() ) :
		the_content();
		continue;
	endif;
	get_template_part( 'template-parts/page-hero', null, array( 'cta' => false ) );
	?>
	<div class="section section--tight">
		<div class="container container--narrow">
			<article <?php post_class( 'entry' ); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		</div>
	</div>
	<?php
endwhile;

get_footer();
