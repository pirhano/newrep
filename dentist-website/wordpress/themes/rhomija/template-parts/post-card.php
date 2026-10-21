<?php
/**
 * Article card (inside the loop).
 *
 * @package Rhomija
 */

?>
<article <?php post_class( 'post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'rh-card' ); ?></a>
	<?php endif; ?>
	<div class="post-card__body">
		<p class="post-card__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		<h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="post-card__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<span class="post-card__more" aria-hidden="true"><?php esc_html_e( 'Lire l’article', 'rhomija' ); ?> <?php echo rh_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</div>
</article>
