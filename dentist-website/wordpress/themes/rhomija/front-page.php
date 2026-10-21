<?php
/**
 * Home page.
 *
 * @package Rhomija
 */

get_header();

$rh_services = rh_services();
if ( ! $rh_services ) {
	// Before the installer runs, show the default list (links go to the booking form).
	foreach ( rh_default_services() as $rh_d ) {
		$rh_services[] = array(
			'title'   => $rh_d['title'],
			'url'     => rh_booking_url( $rh_d['booking'] ),
			'excerpt' => $rh_d['excerpt'],
			'icon'    => $rh_d['icon'],
		);
	}
}
$rh_has_booking = shortcode_exists( 'rhomija_booking' );
?>

<section class="hero">
	<div class="container hero__inner">
		<div class="hero__content">
			<p class="eyebrow"><?php echo rh_icon( 'tooth', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( sprintf( /* translators: %s: city */ __( 'Cabinet dentaire à %s', 'rhomija' ), rh_info( 'city' ) ) ); ?></p>
			<h1 class="hero__title"><?php echo esc_html( rh_mod( 'hero_title' ) ); ?></h1>
			<p class="hero__text"><?php echo esc_html( rh_mod( 'hero_text' ) ); ?></p>
			<div class="btn-row">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $rh_has_booking ? '#rendez-vous' : rh_booking_url() ); ?>" data-track="cta_booking"><?php echo rh_icon( 'calendar', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Prendre rendez-vous', 'rhomija' ); ?></a>
				<a class="btn btn--ghost btn--lg" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo rh_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rh_info( 'phone' ) ); ?></a>
			</div>
			<ul class="hero__trust">
				<li><?php echo rh_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Radiographie panoramique sur place', 'rhomija' ); ?></li>
				<li><?php echo rh_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Salle de stérilisation dédiée', 'rhomija' ); ?></li>
				<li><?php echo rh_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Devis clair avant les soins', 'rhomija' ); ?></li>
			</ul>
		</div>
		<div class="hero__media">
			<div class="hero__photo">
				<?php echo rh_img( 'accueil', __( 'Accueil du cabinet dentaire du Dr Rhomija Nabil à Témara', 'rhomija' ), '(min-width: 960px) 46vw, 100vw', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="hero__badge">
				<?php echo rh_icon( 'clock', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( rh_hours_short() ); ?></span>
			</div>
			<a class="next-slot" href="<?php echo esc_url( $rh_has_booking ? '#rendez-vous' : rh_booking_url() ); ?>" data-next-slot hidden>
				<span class="next-slot__icon"><?php echo rh_icon( 'calendar', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span>
					<small><?php esc_html_e( 'Prochain créneau disponible', 'rhomija' ); ?></small>
					<strong data-next-slot-label></strong>
				</span>
				<span class="next-slot__go"><?php echo rh_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
		</div>
	</div>
</section>

<section class="highlights" aria-label="<?php esc_attr_e( 'Informations pratiques', 'rhomija' ); ?>">
	<div class="container highlights__grid">
		<div class="highlight">
			<span class="highlight__icon"><?php echo rh_icon( 'calendar', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div><strong><?php esc_html_e( 'RDV en ligne 24h/24', 'rhomija' ); ?></strong><span><?php esc_html_e( 'Choisissez votre créneau en 1 minute', 'rhomija' ); ?></span></div>
		</div>
		<div class="highlight">
			<span class="highlight__icon"><?php echo rh_icon( 'alert', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div><strong><?php esc_html_e( 'Urgences dentaires', 'rhomija' ); ?></strong><span><?php esc_html_e( 'Appelez : nous faisons le maximum', 'rhomija' ); ?></span></div>
		</div>
		<div class="highlight">
			<span class="highlight__icon"><?php echo rh_icon( 'shield', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div><strong><?php esc_html_e( 'Hygiène rigoureuse', 'rhomija' ); ?></strong><span><?php esc_html_e( 'Instruments stérilisés sur place', 'rhomija' ); ?></span></div>
		</div>
		<div class="highlight">
			<span class="highlight__icon"><?php echo rh_icon( 'pin', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div><strong><?php echo esc_html( sprintf( /* translators: %s: city */ __( 'Centre de %s', 'rhomija' ), rh_info( 'city' ) ) ); ?></strong><span><?php esc_html_e( 'Avenue Tarik Ibn Ziad', 'rhomija' ); ?></span></div>
		</div>
	</div>
</section>

<section class="section" id="soins">
	<div class="container">
		<header class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Nos soins', 'rhomija' ); ?></p>
			<h2 class="section-title"><?php esc_html_e( 'Des soins complets pour toute la famille', 'rhomija' ); ?></h2>
			<p class="section-lead"><?php esc_html_e( 'De la prévention aux traitements plus complexes, chaque soin commence par un examen et des explications claires.', 'rhomija' ); ?></p>
		</header>
		<div class="service-grid">
			<?php
			foreach ( $rh_services as $rh_s ) {
				get_template_part( 'template-parts/service-card', null, $rh_s );
			}
			?>
		</div>
	</div>
</section>

<section class="section section--soft">
	<div class="container split">
		<div class="mosaic">
			<figure class="mosaic__main"><?php echo rh_img( 'salle-de-soins', __( 'Salle de soins équipée d’un fauteuil dentaire moderne', 'rhomija' ), '(min-width: 960px) 34vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
			<figure class="mosaic__a"><?php echo rh_img( 'sterilisation', __( 'Salle de stérilisation vitrée du cabinet', 'rhomija' ), '(min-width: 960px) 17vw, 50vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
			<figure class="mosaic__b"><?php echo rh_img( 'panoramique', __( 'Appareil de radiographie panoramique', 'rhomija' ), '(min-width: 960px) 17vw, 50vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
		</div>
		<div class="split__content">
			<p class="eyebrow"><?php esc_html_e( 'Le cabinet', 'rhomija' ); ?></p>
			<h2 class="section-title"><?php esc_html_e( 'Un cabinet moderne, pensé pour votre confort et votre sécurité', 'rhomija' ); ?></h2>
			<p><?php esc_html_e( 'Le cabinet vous accueille dans un espace lumineux et entièrement équipé, au 3ᵉ étage de la Résidence Arrayhane. Ici, on prend le temps de vous écouter et de vous expliquer chaque étape.', 'rhomija' ); ?></p>
			<ul class="feature-list">
				<li><span><?php echo rh_icon( 'chair', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><div><strong><?php esc_html_e( 'Équipement moderne', 'rhomija' ); ?></strong><?php esc_html_e( 'Une salle de soins confortable et un matériel récent.', 'rhomija' ); ?></div></li>
				<li><span><?php echo rh_icon( 'xray', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><div><strong><?php esc_html_e( 'Radiographie panoramique sur place', 'rhomija' ); ?></strong><?php esc_html_e( 'Un diagnostic complet sans vous déplacer ailleurs.', 'rhomija' ); ?></div></li>
				<li><span><?php echo rh_icon( 'shield', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><div><strong><?php esc_html_e( 'Stérilisation dédiée', 'rhomija' ); ?></strong><?php esc_html_e( 'Une salle vitrée réservée au nettoyage et à la stérilisation des instruments.', 'rhomija' ); ?></div></li>
				<li><span><?php echo rh_icon( 'chat', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><div><strong><?php esc_html_e( 'Des explications claires', 'rhomija' ); ?></strong><?php esc_html_e( 'Un plan de traitement et un devis détaillé avant de commencer.', 'rhomija' ); ?></div></li>
			</ul>
			<a class="btn btn--ghost" href="<?php echo esc_url( rh_page_url( 'cabinet' ) ); ?>"><?php esc_html_e( 'Découvrir le cabinet', 'rhomija' ); ?> <?php echo rh_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
	</div>
</section>

<section class="section section--ink">
	<div class="container">
		<header class="section-head section-head--center">
			<p class="eyebrow eyebrow--light"><?php esc_html_e( 'Simple et rapide', 'rhomija' ); ?></p>
			<h2 class="section-title"><?php esc_html_e( 'Votre rendez-vous en 3 étapes', 'rhomija' ); ?></h2>
		</header>
		<ol class="steps">
			<li class="step">
				<span class="step__num">1</span>
				<h3><?php esc_html_e( 'Choisissez votre créneau', 'rhomija' ); ?></h3>
				<p><?php esc_html_e( 'Indiquez le motif de votre visite et sélectionnez un horaire libre, à toute heure, depuis votre téléphone.', 'rhomija' ); ?></p>
			</li>
			<li class="step">
				<span class="step__num">2</span>
				<h3><?php esc_html_e( 'Nous vous confirmons', 'rhomija' ); ?></h3>
				<p><?php esc_html_e( 'Le cabinet reçoit votre demande immédiatement et vous confirme le rendez-vous par téléphone ou WhatsApp.', 'rhomija' ); ?></p>
			</li>
			<li class="step">
				<span class="step__num">3</span>
				<h3><?php esc_html_e( 'Vous êtes reçu(e) à l’heure', 'rhomija' ); ?></h3>
				<p><?php esc_html_e( 'Le jour J, présentez-vous à l’accueil 5 minutes avant. Nous nous occupons du reste.', 'rhomija' ); ?></p>
			</li>
		</ol>
	</div>
</section>

<?php if ( $rh_has_booking ) : ?>
	<section class="section booking-section" id="rendez-vous">
		<div class="container booking-layout">
			<div class="booking-layout__intro">
				<p class="eyebrow"><?php esc_html_e( 'Rendez-vous en ligne', 'rhomija' ); ?></p>
				<h2 class="section-title"><?php esc_html_e( 'Réservez votre consultation', 'rhomija' ); ?></h2>
				<p><?php esc_html_e( 'Les créneaux affichés sont libres en temps réel. Votre demande est transmise directement au cabinet.', 'rhomija' ); ?></p>
				<?php get_template_part( 'template-parts/hours' ); ?>
				<p class="booking-layout__urgent">
					<?php echo rh_icon( 'alert', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'Douleur forte ou urgence ?', 'rhomija' ); ?> <a href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo esc_html( sprintf( /* translators: %s: phone */ __( 'Appelez le %s', 'rhomija' ), rh_info( 'phone' ) ) ); ?></a></span>
				</p>
			</div>
			<div class="card booking-layout__form">
				<?php echo do_shortcode( '[rhomija_booking]' ); ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section section--soft">
	<div class="container split split--faq">
		<header class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Questions fréquentes', 'rhomija' ); ?></p>
			<h2 class="section-title"><?php esc_html_e( 'Vous vous posez des questions ?', 'rhomija' ); ?></h2>
			<p class="section-lead"><?php esc_html_e( 'Voici les réponses aux questions que l’on nous pose le plus souvent. Pour le reste, appelez-nous, nous serons ravis de vous répondre.', 'rhomija' ); ?></p>
			<a class="btn btn--ghost" href="<?php echo esc_attr( rh_phone_href() ); ?>" data-track="call"><?php echo rh_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rh_info( 'phone' ) ); ?></a>
		</header>
		<?php get_template_part( 'template-parts/faq' ); ?>
	</div>
</section>

<?php
$rh_query = array(
	'posts_per_page'      => 3,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
// Only the clinic's advice articles, not WordPress's sample post.
if ( term_exists( 'conseils-dentaires', 'category' ) ) {
	$rh_query['category_name'] = 'conseils-dentaires';
}
$rh_posts = new WP_Query( $rh_query );
if ( $rh_posts->have_posts() ) :
	?>
	<section class="section">
		<div class="container">
			<header class="section-head section-head--row">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Conseils dentaires', 'rhomija' ); ?></p>
					<h2 class="section-title"><?php esc_html_e( 'Prendre soin de ses dents, au quotidien', 'rhomija' ); ?></h2>
				</div>
				<?php if ( rh_page_id( 'conseils' ) ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( rh_page_url( 'conseils' ) ); ?>"><?php esc_html_e( 'Tous les conseils', 'rhomija' ); ?> <?php echo rh_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php endif; ?>
			</header>
			<div class="post-grid">
				<?php
				while ( $rh_posts->have_posts() ) {
					$rh_posts->the_post();
					get_template_part( 'template-parts/post-card' );
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section section--soft" id="acces">
	<div class="container">
		<header class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Accès', 'rhomija' ); ?></p>
			<h2 class="section-title"><?php echo esc_html( sprintf( /* translators: %s: city */ __( 'Nous trouver à %s', 'rhomija' ), rh_info( 'city' ) ) ); ?></h2>
		</header>
		<?php get_template_part( 'template-parts/visit' ); ?>
	</div>
</section>

<?php
get_footer();
