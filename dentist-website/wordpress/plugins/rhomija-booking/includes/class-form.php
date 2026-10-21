<?php
/**
 * Front-end booking form: [rhomija_booking] shortcode.
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

class RB_Form {

	public static function init() {
		add_shortcode( 'rhomija_booking', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function register_assets() {
		wp_register_style( 'rhomija-booking', RB_URL . 'assets/booking.css', array(), RB_VERSION );
		wp_register_script( 'rhomija-booking', RB_URL . 'assets/booking.js', array(), RB_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	private static function config() {
		return array(
			'api'       => esc_url_raw( rest_url( RB_Rest::NS ) ),
			'today'     => RB_Availability::now()->format( 'Y-m-d' ),
			'months'    => array( 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' ),
			'weekdays'  => array( 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.', 'dim.' ),
			'phone'     => RB_Settings::get( 'phone' ),
			'phoneHref' => 'tel:+' . rb_phone_intl( RB_Settings::get( 'phone' ) ),
			'i18n'      => array(
				'loading'    => __( 'Chargement des disponibilités…', 'rhomija-booking' ),
				'noSlots'    => __( 'Aucun créneau libre ce jour-là. Essayez une autre date.', 'rhomija-booking' ),
				'pickDay'    => __( 'Sélectionnez un jour dans le calendrier.', 'rhomija-booking' ),
				'slotsFor'   => __( 'Créneaux disponibles le', 'rhomija-booking' ),
				'morning'    => __( 'Matin', 'rhomija-booking' ),
				'afternoon'  => __( 'Après-midi', 'rhomija-booking' ),
				'network'    => __( 'Connexion impossible. Vérifiez votre connexion internet ou appelez-nous.', 'rhomija-booking' ),
				'sending'    => __( 'Envoi en cours…', 'rhomija-booking' ),
				'free'       => __( 'créneaux libres', 'rhomija-booking' ),
				'full'       => __( 'complet', 'rhomija-booking' ),
				'closed'     => __( 'fermé', 'rhomija-booking' ),
			),
		);
	}

	public static function render( $atts = array() ) {
		$atts = shortcode_atts( array( 'title' => '' ), $atts, 'rhomija_booking' );

		wp_enqueue_style( 'rhomija-booking' );
		wp_enqueue_script( 'rhomija-booking' );
		wp_add_inline_script( 'rhomija-booking', 'window.RB_CONFIG = ' . wp_json_encode( self::config() ) . ';', 'before' );

		$phone       = RB_Settings::get( 'phone' );
		$phone_href  = 'tel:+' . rb_phone_intl( $phone );
		$privacy_url = get_privacy_policy_url();
		$uid         = wp_unique_id( 'rb-' );

		ob_start();
		?>
		<div class="rb" data-rb id="<?php echo esc_attr( $uid ); ?>">
			<?php if ( $atts['title'] ) : ?>
				<h2 class="rb-heading"><?php echo esc_html( $atts['title'] ); ?></h2>
			<?php endif; ?>

			<ol class="rb-progress" aria-label="<?php esc_attr_e( 'Étapes de la réservation', 'rhomija-booking' ); ?>">
				<li class="is-current" data-progress="1"><span>1</span> <?php esc_html_e( 'Motif', 'rhomija-booking' ); ?></li>
				<li data-progress="2"><span>2</span> <?php esc_html_e( 'Date & heure', 'rhomija-booking' ); ?></li>
				<li data-progress="3"><span>3</span> <?php esc_html_e( 'Coordonnées', 'rhomija-booking' ); ?></li>
			</ol>

			<noscript>
				<p class="rb-alert"><?php
					printf(
						/* translators: %s: phone link */
						esc_html__( 'La réservation en ligne nécessite JavaScript. Vous pouvez aussi nous appeler au %s.', 'rhomija-booking' ),
						'<a href="' . esc_attr( $phone_href ) . '">' . esc_html( $phone ) . '</a>'
					);
				?></p>
			</noscript>

			<form class="rb-form" novalidate>
				<section class="rb-step" data-step="1" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s1">
					<h3 class="rb-title" id="<?php echo esc_attr( $uid ); ?>-s1" tabindex="-1"><?php esc_html_e( 'Quel est le motif de votre visite ?', 'rhomija-booking' ); ?></h3>
					<fieldset class="rb-chips">
						<legend class="rb-sr"><?php esc_html_e( 'Motif du rendez-vous', 'rhomija-booking' ); ?></legend>
						<?php foreach ( RB_Settings::services() as $slug => $label ) : ?>
							<label class="rb-chip">
								<input type="radio" name="service" value="<?php echo esc_attr( $slug ); ?>" data-label="<?php echo esc_attr( $label ); ?>">
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>
					<div class="rb-actions">
						<button type="button" class="rb-btn rb-btn--primary" data-next disabled><?php esc_html_e( 'Choisir une date', 'rhomija-booking' ); ?> <span aria-hidden="true">→</span></button>
					</div>
				</section>

				<section class="rb-step" data-step="2" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s2" hidden>
					<h3 class="rb-title" id="<?php echo esc_attr( $uid ); ?>-s2" tabindex="-1"><?php esc_html_e( 'Choisissez une date et un horaire', 'rhomija-booking' ); ?></h3>
					<div class="rb-when">
						<div class="rb-cal" data-cal>
							<div class="rb-cal__head">
								<button type="button" class="rb-cal__nav" data-cal-prev aria-label="<?php esc_attr_e( 'Mois précédent', 'rhomija-booking' ); ?>">‹</button>
								<p class="rb-cal__month" data-cal-label aria-live="polite"></p>
								<button type="button" class="rb-cal__nav" data-cal-next aria-label="<?php esc_attr_e( 'Mois suivant', 'rhomija-booking' ); ?>">›</button>
							</div>
							<div class="rb-cal__grid" data-cal-grid role="grid"></div>
							<p class="rb-cal__legend"><span class="rb-dot"></span> <?php esc_html_e( 'Jours avec des créneaux libres', 'rhomija-booking' ); ?></p>
						</div>
						<div class="rb-times" data-times aria-live="polite">
							<p class="rb-muted"><?php esc_html_e( 'Sélectionnez un jour dans le calendrier.', 'rhomija-booking' ); ?></p>
						</div>
					</div>
					<div class="rb-actions">
						<button type="button" class="rb-btn rb-btn--ghost" data-back><span aria-hidden="true">←</span> <?php esc_html_e( 'Retour', 'rhomija-booking' ); ?></button>
						<button type="button" class="rb-btn rb-btn--primary" data-next disabled><?php esc_html_e( 'Continuer', 'rhomija-booking' ); ?> <span aria-hidden="true">→</span></button>
					</div>
				</section>

				<section class="rb-step" data-step="3" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s3" hidden>
					<h3 class="rb-title" id="<?php echo esc_attr( $uid ); ?>-s3" tabindex="-1"><?php esc_html_e( 'Vos coordonnées', 'rhomija-booking' ); ?></h3>
					<div class="rb-summary" data-summary></div>
					<div class="rb-fields">
						<p class="rb-field">
							<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'Nom et prénom', 'rhomija-booking' ); ?> <abbr title="<?php esc_attr_e( 'obligatoire', 'rhomija-booking' ); ?>">*</abbr></label>
							<input id="<?php echo esc_attr( $uid ); ?>-name" name="name" type="text" autocomplete="name" required maxlength="80">
						</p>
						<p class="rb-field">
							<label for="<?php echo esc_attr( $uid ); ?>-phone"><?php esc_html_e( 'Téléphone', 'rhomija-booking' ); ?> <abbr title="<?php esc_attr_e( 'obligatoire', 'rhomija-booking' ); ?>">*</abbr></label>
							<input id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required placeholder="06 12 34 56 78">
						</p>
						<p class="rb-field">
							<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'E-mail', 'rhomija-booking' ); ?> <span class="rb-optional"><?php esc_html_e( '(facultatif, pour recevoir la confirmation)', 'rhomija-booking' ); ?></span></label>
							<input id="<?php echo esc_attr( $uid ); ?>-email" name="email" type="email" autocomplete="email">
						</p>
						<fieldset class="rb-field rb-inline">
							<legend><?php esc_html_e( 'Êtes-vous déjà patient du cabinet ?', 'rhomija-booking' ); ?></legend>
							<label><input type="radio" name="new_patient" value="no"> <?php esc_html_e( 'Oui', 'rhomija-booking' ); ?></label>
							<label><input type="radio" name="new_patient" value="yes"> <?php esc_html_e( 'Non, première visite', 'rhomija-booking' ); ?></label>
						</fieldset>
						<p class="rb-field rb-field--full">
							<label for="<?php echo esc_attr( $uid ); ?>-message"><?php esc_html_e( 'Message', 'rhomija-booking' ); ?> <span class="rb-optional"><?php esc_html_e( '(facultatif)', 'rhomija-booking' ); ?></span></label>
							<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="3" maxlength="1000" placeholder="<?php esc_attr_e( 'Ex. douleur depuis 2 jours en bas à droite…', 'rhomija-booking' ); ?>"></textarea>
						</p>
						<p class="rb-hp" aria-hidden="true">
							<label for="<?php echo esc_attr( $uid ); ?>-website">Website</label>
							<input id="<?php echo esc_attr( $uid ); ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
						</p>
						<p class="rb-field rb-field--full rb-consent">
							<label>
								<input type="checkbox" name="consent" value="1" required>
								<span><?php
								if ( $privacy_url ) {
									printf(
										/* translators: %s: privacy policy link */
										wp_kses( __( 'J’accepte que mes données soient utilisées uniquement pour la gestion de mon rendez-vous, conformément à la loi 09-08 et à la <a href="%s" target="_blank" rel="noopener">politique de confidentialité</a>.', 'rhomija-booking' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ),
										esc_url( $privacy_url )
									);
								} else {
									esc_html_e( 'J’accepte que mes données soient utilisées uniquement pour la gestion de mon rendez-vous, conformément à la loi 09-08.', 'rhomija-booking' );
								}
								?></span>
							</label>
						</p>
					</div>
					<div class="rb-actions">
						<button type="button" class="rb-btn rb-btn--ghost" data-back><span aria-hidden="true">←</span> <?php esc_html_e( 'Retour', 'rhomija-booking' ); ?></button>
						<button type="submit" class="rb-btn rb-btn--primary" data-submit><?php esc_html_e( 'Envoyer ma demande', 'rhomija-booking' ); ?></button>
					</div>
				</section>

				<section class="rb-step rb-done" data-step="done" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s4" hidden>
					<div class="rb-done__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="34" height="34"><path d="M5 12.5l4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</div>
					<h3 class="rb-title" id="<?php echo esc_attr( $uid ); ?>-s4" tabindex="-1"><?php esc_html_e( 'Demande envoyée, merci !', 'rhomija-booking' ); ?></h3>
					<p class="rb-done__lead"><?php esc_html_e( 'Notre équipe vous contacte rapidement par téléphone ou WhatsApp pour confirmer votre rendez-vous.', 'rhomija-booking' ); ?></p>
					<div class="rb-summary" data-done-summary></div>
					<div class="rb-actions rb-actions--center" data-done-actions></div>
				</section>

				<p class="rb-alert" role="alert" data-error hidden></p>
			</form>

			<p class="rb-help">
				<?php esc_html_e( 'Une urgence ou une question ?', 'rhomija-booking' ); ?>
				<a href="<?php echo esc_attr( $phone_href ); ?>" data-track="call"><?php echo esc_html( sprintf( /* translators: %s: phone */ __( 'Appelez le %s', 'rhomija-booking' ), $phone ) ); ?></a>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
