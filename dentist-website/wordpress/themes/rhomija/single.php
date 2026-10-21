<?php
/**
 * Single article.
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
	?>
	<section class="page-hero page-hero--article">
		<div class="container container--narrow">
			<?php rh_breadcrumbs(); ?>
			<p class="post-card__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
			<h1 class="page-hero__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="page-hero__intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<div class="section section--tight">
		<div class="container container--narrow">
			<article <?php post_class( 'entry' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="entry-thumb"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
				<aside class="entry-note">
					<?php echo rh_icon( 'chat', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<p><?php esc_html_e( 'Ces conseils sont donnés à titre d’information et ne remplacent pas une consultation. Une question sur votre situation ? Prenez rendez-vous.', 'rhomija' ); ?></p>
				</aside>
			</article>
		</div>
	</div>
	<?php
	get_template_part( 'template-parts/cta-band' );
endwhile;

get_footer();
