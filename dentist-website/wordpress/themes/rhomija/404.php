<?php
/**
 * Not found.
 *
 * @package Rhomija
 */

get_header();
get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'title' => __( 'Page introuvable', 'rhomija' ),
		'intro' => __( 'Cette page n’existe pas ou a été déplacée. Vous pouvez revenir à l’accueil ou prendre rendez-vous directement.', 'rhomija' ),
	)
);
?>
<section class="section section--tight">
	<div class="container">
		<div class="service-grid service-grid--compact">
			<?php
			foreach ( array_slice( rh_services(), 0, 3 ) as $rh_s ) {
				get_template_part( 'template-parts/service-card', null, $rh_s );
			}
			?>
		</div>
	</div>
</section>
<?php
get_footer();
