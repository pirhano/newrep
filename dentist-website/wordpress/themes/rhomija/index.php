<?php
/**
 * Blog index, archives and search results.
 *
 * @package Rhomija
 */

get_header();

if ( is_home() && ! is_front_page() ) {
	$rh_title = get_the_title( (int) get_option( 'page_for_posts' ) );
	$rh_intro = __( 'Nos conseils pour garder des dents et des gencives en bonne santé, et mieux comprendre vos soins.', 'rhomija' );
} elseif ( is_search() ) {
	/* translators: %s: search query */
	$rh_title = sprintf( __( 'Résultats pour « %s »', 'rhomija' ), get_search_query() );
	$rh_intro = '';
} else {
	$rh_title = wp_strip_all_tags( get_the_archive_title() );
	$rh_intro = wp_strip_all_tags( get_the_archive_description() );
}
get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'title' => $rh_title,
		'intro' => $rh_intro,
		'cta'   => false,
	)
);
?>
<section class="section section--tight">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/post-card' );
				}
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => __( 'Précédent', 'rhomija' ),
					'next_text' => __( 'Suivant', 'rhomija' ),
				)
			);
			?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun contenu pour le moment.', 'rhomija' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
