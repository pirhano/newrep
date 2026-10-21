<?php
/**
 * Plugin settings: clinic details, opening hours and booking rules.
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

class RB_Settings {

	const OPTION = 'rb_settings';
	const PAGE   = 'rb-settings';

	/** @var array|null */
	private static $cache = null;

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'update_option_' . self::OPTION, array( __CLASS__, 'flush' ) );
	}

	public static function days() {
		return array(
			1 => __( 'Lundi', 'rhomija-booking' ),
			2 => __( 'Mardi', 'rhomija-booking' ),
			3 => __( 'Mercredi', 'rhomija-booking' ),
			4 => __( 'Jeudi', 'rhomija-booking' ),
			5 => __( 'Vendredi', 'rhomija-booking' ),
			6 => __( 'Samedi', 'rhomija-booking' ),
			7 => __( 'Dimanche', 'rhomija-booking' ),
		);
	}

	public static function defaults() {
		return array(
			'clinic_name'      => 'Cabinet Dentaire Dr Rhomija Nabil',
			'phone'            => '08 08 66 54 26',
			'whatsapp'         => '',
			'email'            => 'cabinetrhomija@gmail.com',
			'notify_email'     => 'cabinetrhomija@gmail.com',
			'address'          => "N° 14, 3ᵉ étage, Imm. 5, Résidence Arrayhane\nAvenue Tarik Ibn Ziad",
			'city'             => 'Témara',
			'hours'            => array(
				1 => '09:00-17:00',
				2 => '09:00-17:00',
				3 => '09:00-17:00',
				4 => '09:00-17:00',
				5 => '09:00-17:00',
				6 => '09:00-17:00',
				7 => '',
			),
			'slot_minutes'     => 30,
			'capacity'         => 1,
			'min_notice_hours' => 2,
			'max_days_ahead'   => 60,
			'closed_dates'     => '',
			'services'         => implode(
				"\n",
				array(
					'Première consultation / bilan',
					'Détartrage & nettoyage',
					'Douleur ou urgence',
					'Soin de carie',
					'Dévitalisation',
					'Extraction / dent de sagesse',
					'Implant dentaire',
					'Couronne, bridge ou prothèse',
					'Blanchiment & esthétique',
					'Soins pour enfant',
					'Autre demande',
				)
			),
		);
	}

	public static function all() {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			$saved       = is_array( $saved ) ? $saved : array();
			$defaults    = self::defaults();
			self::$cache = array_merge( $defaults, $saved );
			if ( ! is_array( self::$cache['hours'] ) ) {
				self::$cache['hours'] = $defaults['hours'];
			}
		}
		return self::$cache;
	}

	public static function get( $key ) {
		$all = self::all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	public static function flush() {
		self::$cache = null;
	}

	/**
	 * Services offered in the booking form, as slug => label.
	 *
	 * @return array<string,string>
	 */
	public static function services() {
		$out = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) self::get( 'services' ) ) as $line ) {
			$label = trim( $line );
			if ( '' !== $label ) {
				$out[ sanitize_title( $label ) ] = $label;
			}
		}
		return $out;
	}

	/**
	 * Dates (Y-m-d) the clinic is closed.
	 *
	 * @return string[]
	 */
	public static function closed_dates() {
		preg_match_all( '/\d{4}-\d{2}-\d{2}/', (string) self::get( 'closed_dates' ), $m );
		return array_values( array_unique( $m[0] ) );
	}

	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . RB_Appointments::POST_TYPE,
			__( 'Réglages des rendez-vous', 'rhomija-booking' ),
			__( 'Réglages', 'rhomija-booking' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	public static function register() {
		register_setting(
			self::PAGE,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Accepts "09:00-13:00, 14:30-18:00" and returns it normalised, dropping invalid ranges.
	 */
	public static function sanitize_ranges( $value ) {
		$ranges = array();
		foreach ( explode( ',', (string) $value ) as $part ) {
			if ( preg_match( '/^\s*(\d{1,2})[:hH](\d{2})\s*[-–]\s*(\d{1,2})[:hH](\d{2})\s*$/u', $part, $m ) ) {
				$start = sprintf( '%02d:%02d', $m[1], $m[2] );
				$end   = sprintf( '%02d:%02d', $m[3], $m[4] );
				if ( (int) $m[1] < 24 && (int) $m[3] <= 24 && $start < $end ) {
					$ranges[] = $start . '-' . $end;
				}
			}
		}
		return implode( ', ', $ranges );
	}

	public static function sanitize( $input ) {
		$d   = self::defaults();
		$in  = is_array( $input ) ? $input : array();
		$out = array();

		foreach ( array( 'clinic_name', 'phone', 'whatsapp', 'city' ) as $k ) {
			$out[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : $d[ $k ];
		}
		$out['email']        = isset( $in['email'] ) ? sanitize_email( $in['email'] ) : $d['email'];
		$out['notify_email'] = implode(
			', ',
			array_filter( array_map( 'sanitize_email', explode( ',', isset( $in['notify_email'] ) ? $in['notify_email'] : '' ) ) )
		);
		$out['address']      = isset( $in['address'] ) ? sanitize_textarea_field( $in['address'] ) : $d['address'];
		$out['services']     = isset( $in['services'] ) ? sanitize_textarea_field( $in['services'] ) : $d['services'];
		$out['closed_dates'] = isset( $in['closed_dates'] ) ? sanitize_textarea_field( $in['closed_dates'] ) : '';

		$out['hours'] = array();
		foreach ( array_keys( self::days() ) as $day ) {
			$out['hours'][ $day ] = isset( $in['hours'][ $day ] ) ? self::sanitize_ranges( $in['hours'][ $day ] ) : '';
		}

		$slot                 = isset( $in['slot_minutes'] ) ? absint( $in['slot_minutes'] ) : $d['slot_minutes'];
		$out['slot_minutes']  = in_array( $slot, array( 15, 20, 30, 45, 60 ), true ) ? $slot : 30;
		$out['capacity']      = max( 1, min( 10, isset( $in['capacity'] ) ? absint( $in['capacity'] ) : 1 ) );
		$out['min_notice_hours'] = max( 0, min( 168, isset( $in['min_notice_hours'] ) ? absint( $in['min_notice_hours'] ) : 2 ) );
		$out['max_days_ahead']   = max( 7, min( 365, isset( $in['max_days_ahead'] ) ? absint( $in['max_days_ahead'] ) : 60 ) );

		self::flush();
		return $out;
	}

	private static function field( $key, $label, $type = 'text', $help = '', $attrs = '' ) {
		$v    = self::get( $key );
		$name = self::OPTION . '[' . $key . ']';
		echo '<tr><th scope="row"><label for="rb-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( 'textarea' === $type ) {
			echo '<textarea id="rb-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" class="large-text" ' . ( $attrs ? $attrs : 'rows="6"' ) . '>' . esc_textarea( $v ) . '</textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attrs.
		} else {
			echo '<input id="rb-' . esc_attr( $key ) . '" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $v ) . '" class="regular-text" ' . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attrs.
		}
		if ( $help ) {
			echo '<p class="description">' . wp_kses_post( $help ) . '</p>';
		}
		echo '</td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$hours = self::get( 'hours' );
		?>
		<div class="wrap rb-settings">
			<h1><?php esc_html_e( 'Réglages des rendez-vous', 'rhomija-booking' ); ?></h1>
			<p><?php esc_html_e( 'Ces informations sont utilisées par le formulaire de réservation, les e-mails et l’ensemble du site (en-tête, pied de page, page contact).', 'rhomija-booking' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( self::PAGE ); ?>

				<h2 class="title"><?php esc_html_e( 'Coordonnées du cabinet', 'rhomija-booking' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::field( 'clinic_name', __( 'Nom du cabinet', 'rhomija-booking' ) );
					self::field( 'phone', __( 'Téléphone', 'rhomija-booking' ), 'text', __( 'Format affiché sur le site, ex. 08 08 66 54 26.', 'rhomija-booking' ) );
					self::field( 'whatsapp', __( 'Numéro WhatsApp', 'rhomija-booking' ), 'text', __( 'Ex. 06 12 34 56 78. Laissez vide pour masquer les boutons WhatsApp.', 'rhomija-booking' ) );
					self::field( 'email', __( 'E-mail public', 'rhomija-booking' ), 'email' );
					self::field( 'notify_email', __( 'E-mail(s) de notification', 'rhomija-booking' ), 'text', __( 'Reçoit chaque nouvelle demande de rendez-vous. Plusieurs adresses possibles, séparées par des virgules.', 'rhomija-booking' ) );
					self::field( 'address', __( 'Adresse', 'rhomija-booking' ), 'textarea', '', 'rows="3"' );
					self::field( 'city', __( 'Ville', 'rhomija-booking' ) );
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Horaires d’ouverture', 'rhomija-booking' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Format : 09:00-17:00. Pour une pause déjeuner : 09:00-13:00, 14:30-18:00. Laissez vide si le cabinet est fermé ce jour-là.', 'rhomija-booking' ); ?></p>
				<table class="form-table" role="presentation">
					<?php foreach ( self::days() as $num => $label ) : ?>
						<tr>
							<th scope="row"><label for="rb-hours-<?php echo esc_attr( $num ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td><input id="rb-hours-<?php echo esc_attr( $num ); ?>" type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION . '[hours][' . $num . ']' ); ?>" value="<?php echo esc_attr( isset( $hours[ $num ] ) ? $hours[ $num ] : '' ); ?>" placeholder="<?php esc_attr_e( 'Fermé', 'rhomija-booking' ); ?>"></td>
						</tr>
					<?php endforeach; ?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Règles de réservation', 'rhomija-booking' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="rb-slot_minutes"><?php esc_html_e( 'Durée d’un créneau', 'rhomija-booking' ); ?></label></th>
						<td>
							<select id="rb-slot_minutes" name="<?php echo esc_attr( self::OPTION ); ?>[slot_minutes]">
								<?php foreach ( array( 15, 20, 30, 45, 60 ) as $m ) : ?>
									<option value="<?php echo esc_attr( $m ); ?>" <?php selected( (int) self::get( 'slot_minutes' ), $m ); ?>><?php echo esc_html( $m . ' min' ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<?php
					self::field( 'capacity', __( 'Patients par créneau', 'rhomija-booking' ), 'number', __( 'Nombre de demandes acceptées sur un même créneau (1 = un seul patient).', 'rhomija-booking' ), 'min="1" max="10" style="width:6em"' );
					self::field( 'min_notice_hours', __( 'Délai minimum (heures)', 'rhomija-booking' ), 'number', __( 'Empêche de réserver un créneau qui commence dans moins de X heures.', 'rhomija-booking' ), 'min="0" max="168" style="width:6em"' );
					self::field( 'max_days_ahead', __( 'Réservation jusqu’à (jours)', 'rhomija-booking' ), 'number', '', 'min="7" max="365" style="width:6em"' );
					self::field( 'closed_dates', __( 'Jours de fermeture', 'rhomija-booking' ), 'textarea', __( 'Une date par ligne au format AAAA-MM-JJ (jours fériés, congés…). Un commentaire peut suivre la date : <code>2026-11-18 Fête de l’Indépendance</code>.', 'rhomija-booking' ) );
					self::field( 'services', __( 'Motifs de rendez-vous', 'rhomija-booking' ), 'textarea', __( 'Un motif par ligne, dans l’ordre d’affichage du formulaire.', 'rhomija-booking' ) );
					?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
