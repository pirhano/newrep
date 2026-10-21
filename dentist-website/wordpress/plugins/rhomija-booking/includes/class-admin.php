<?php
/**
 * Back-office: appointment list, quick actions, edit screen and dashboard widget.
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

class RB_Admin {

	public static function init() {
		$pt = RB_Appointments::POST_TYPE;
		add_filter( "manage_{$pt}_posts_columns", array( __CLASS__, 'columns' ) );
		add_action( "manage_{$pt}_posts_custom_column", array( __CLASS__, 'column' ), 10, 2 );
		add_filter( "manage_edit-{$pt}_sortable_columns", array( __CLASS__, 'sortable' ) );
		add_filter( 'list_table_primary_column', array( __CLASS__, 'primary_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_filter( "views_edit-{$pt}", array( __CLASS__, 'views' ) );
		add_filter( "bulk_actions-edit-{$pt}", array( __CLASS__, 'bulk_actions' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'list_query' ) );
		add_action( 'admin_post_rb_set_status', array( __CLASS__, 'handle_status' ) );
		add_action( 'add_meta_boxes_' . $pt, array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . $pt, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'menu_badge' ), 99 );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'dashboard' ) );
		add_filter( 'post_updated_messages', array( __CLASS__, 'messages' ) );
	}

	private static function is_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && RB_Appointments::POST_TYPE === $screen->post_type;
	}

	public static function assets() {
		$screen = get_current_screen();
		if ( self::is_screen() || ( $screen && 'dashboard' === $screen->id ) ) {
			wp_enqueue_style( 'rhomija-booking-admin', RB_URL . 'assets/admin.css', array(), RB_VERSION );
		}
	}

	/* ---------- List table ---------- */

	public static function columns() {
		return array(
			'cb'          => '<input type="checkbox">',
			'rb_when'     => __( 'Date & heure', 'rhomija-booking' ),
			'rb_patient'  => __( 'Patient', 'rhomija-booking' ),
			'rb_phone'    => __( 'Téléphone', 'rhomija-booking' ),
			'rb_service'  => __( 'Motif', 'rhomija-booking' ),
			'rb_status'   => __( 'Statut', 'rhomija-booking' ),
			'rb_received' => __( 'Reçu le', 'rhomija-booking' ),
		);
	}

	public static function sortable( $cols ) {
		$cols['rb_when']     = 'rb_start';
		$cols['rb_received'] = 'date';
		return $cols;
	}

	public static function primary_column( $default, $screen ) {
		return 'edit-' . RB_Appointments::POST_TYPE === $screen ? 'rb_when' : $default;
	}

	public static function status_badge( $status ) {
		$all = RB_Appointments::statuses();
		$key = isset( $all[ $status ] ) ? $status : 'pending';
		return '<span class="rb-badge rb-badge--' . esc_attr( $key ) . '">' . esc_html( $all[ $key ] ) . '</span>';
	}

	public static function column( $col, $id ) {
		$a = RB_Appointments::get( $id );
		if ( ! $a ) {
			return;
		}
		switch ( $col ) {
			case 'rb_when':
				$today = RB_Availability::now()->format( 'Y-m-d' );
				$flag  = $a['date'] === $today ? ' <span class="rb-today">' . esc_html__( 'Aujourd’hui', 'rhomija-booking' ) . '</span>' : '';
				printf(
					'<strong><a class="row-title" href="%s">%s · %s</a></strong>%s',
					esc_url( get_edit_post_link( $id ) ),
					esc_html( ucfirst( RB_Appointments::format_date( $a['date'] ) ) ),
					esc_html( RB_Appointments::format_time( $a['time'] ) ),
					$flag // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above.
				);
				break;
			case 'rb_patient':
				echo esc_html( $a['name'] );
				if ( 'yes' === $a['new_patient'] ) {
					echo ' <span class="rb-new">' . esc_html__( 'Nouveau', 'rhomija-booking' ) . '</span>';
				}
				if ( $a['message'] ) {
					echo '<br><span class="rb-msg" title="' . esc_attr( $a['message'] ) . '">' . esc_html( wp_trim_words( $a['message'], 12 ) ) . '</span>';
				}
				break;
			case 'rb_phone':
				printf( '<a href="tel:+%s">%s</a>', esc_attr( rb_phone_intl( $a['phone'] ) ), esc_html( $a['phone'] ) );
				if ( $a['email'] ) {
					printf( '<br><a href="mailto:%1$s">%1$s</a>', esc_html( $a['email'] ) );
				}
				break;
			case 'rb_service':
				echo esc_html( $a['service_label'] );
				break;
			case 'rb_status':
				echo self::status_badge( $a['status'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in method.
				if ( 'admin' === $a['source'] ) {
					echo '<br><small>' . esc_html__( 'saisi au cabinet', 'rhomija-booking' ) . '</small>';
				}
				break;
			case 'rb_received':
				echo esc_html( wp_date( 'j M Y, H:i', $a['created'] ) );
				break;
		}
	}

	private static function status_url( $id, $status ) {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=rb_set_status&id=' . (int) $id . '&status=' . $status ),
			'rb_status_' . $id
		);
	}

	public static function row_actions( $actions, $post ) {
		if ( RB_Appointments::POST_TYPE !== $post->post_type ) {
			return $actions;
		}
		$a   = RB_Appointments::get( $post->ID );
		$out = array();
		if ( 'confirmed' !== $a['status'] ) {
			$out['rb_confirm'] = '<a href="' . esc_url( self::status_url( $post->ID, 'confirmed' ) ) . '">' . esc_html__( 'Confirmer', 'rhomija-booking' ) . '</a>';
		}
		if ( in_array( $a['status'], RB_Appointments::active_statuses(), true ) ) {
			$out['rb_done']   = '<a href="' . esc_url( self::status_url( $post->ID, 'done' ) ) . '">' . esc_html__( 'Honoré', 'rhomija-booking' ) . '</a>';
			$out['rb_cancel'] = '<a class="rb-danger" href="' . esc_url( self::status_url( $post->ID, 'cancelled' ) ) . '">' . esc_html__( 'Annuler', 'rhomija-booking' ) . '</a>';
		}
		$wa = rb_whatsapp_url( $a['phone'], RB_Notifications::whatsapp_confirm_text( $a ) );
		if ( $wa ) {
			$out['rb_wa'] = '<a href="' . esc_url( $wa ) . '" target="_blank" rel="noopener">WhatsApp</a>';
		}
		$out['edit'] = isset( $actions['edit'] ) ? $actions['edit'] : '';
		if ( isset( $actions['trash'] ) ) {
			$out['trash'] = $actions['trash'];
		}
		return array_filter( $out );
	}

	public static function bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	private static function count_view( $view ) {
		$q = new WP_Query( self::view_args( $view ) + array( 'post_type' => RB_Appointments::POST_TYPE, 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 1 ) );
		return (int) $q->found_posts;
	}

	private static function view_args( $view ) {
		$today = RB_Availability::now()->format( 'Y-m-d' );
		switch ( $view ) {
			case 'upcoming':
				return array(
					'meta_query' => array(
						array( 'key' => '_rb_date', 'value' => $today, 'compare' => '>=' ),
						array( 'key' => '_rb_status', 'value' => RB_Appointments::active_statuses(), 'compare' => 'IN' ),
					),
				);
			case 'pending':
				return array(
					'meta_query' => array(
						array( 'key' => '_rb_date', 'value' => $today, 'compare' => '>=' ),
						array( 'key' => '_rb_status', 'value' => 'pending' ),
					),
				);
			case 'today':
				return array(
					'meta_query' => array(
						array( 'key' => '_rb_date', 'value' => $today ),
					),
				);
			case 'past':
				return array(
					'meta_query' => array(
						array( 'key' => '_rb_date', 'value' => $today, 'compare' => '<' ),
					),
				);
			case 'cancelled':
				return array(
					'meta_query' => array(
						array( 'key' => '_rb_status', 'value' => 'cancelled' ),
					),
				);
		}
		return array();
	}

	private static function current_view() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
		$view = isset( $_GET['rb_view'] ) ? sanitize_key( $_GET['rb_view'] ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' === $view && empty( $_GET['post_status'] ) && empty( $_GET['s'] ) ) {
			$view = 'upcoming';
		}
		return $view;
	}

	public static function views( $views ) {
		$base    = admin_url( 'edit.php?post_type=' . RB_Appointments::POST_TYPE );
		$current = self::current_view();
		$labels  = array(
			'upcoming'  => __( 'À venir', 'rhomija-booking' ),
			'pending'   => __( 'À confirmer', 'rhomija-booking' ),
			'today'     => __( 'Aujourd’hui', 'rhomija-booking' ),
			'past'      => __( 'Passés', 'rhomija-booking' ),
			'cancelled' => __( 'Annulés', 'rhomija-booking' ),
			'all'       => __( 'Tous', 'rhomija-booking' ),
		);
		$out = array();
		foreach ( $labels as $key => $label ) {
			$count       = 'all' === $key ? (int) wp_count_posts( RB_Appointments::POST_TYPE )->publish : self::count_view( $key );
			$out[ $key ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( add_query_arg( 'rb_view', $key, $base ) ),
				$current === $key ? ' class="current" aria-current="page"' : '',
				esc_html( $label ),
				$count
			);
		}
		if ( isset( $views['trash'] ) ) {
			$out['trash'] = $views['trash'];
		}
		return $out;
	}

	public static function list_query( WP_Query $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || RB_Appointments::POST_TYPE !== $q->get( 'post_type' ) ) {
			return;
		}
		global $pagenow;
		if ( 'edit.php' !== $pagenow ) {
			return;
		}
		$view = self::current_view();
		foreach ( self::view_args( $view ) as $k => $v ) {
			$q->set( $k, $v );
		}
		$orderby = $q->get( 'orderby' );
		if ( ! $orderby || 'rb_start' === $orderby ) {
			$q->set( 'meta_key', '_rb_start' );
			$q->set( 'orderby', 'meta_value' );
			if ( ! $q->get( 'order' ) || ! isset( $_GET['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$q->set( 'order', in_array( $view, array( 'past', 'cancelled', 'all' ), true ) ? 'DESC' : 'ASC' );
			}
		}
	}

	public static function handle_status() {
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		check_admin_referer( 'rb_status_' . $id );
		if ( ! current_user_can( 'edit_post', $id ) || ! RB_Appointments::get( $id ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'rhomija-booking' ) );
		}
		RB_Appointments::set_status( $id, $status );
		$back = wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=' . RB_Appointments::POST_TYPE );
		wp_safe_redirect( add_query_arg( 'rb_updated', $status, remove_query_arg( 'rb_updated', $back ) ) );
		exit;
	}

	public static function notices() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['rb_updated'] ) ) {
			$all = RB_Appointments::statuses();
			$st  = sanitize_key( $_GET['rb_updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $all[ $st ] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( /* translators: %s: status */ __( 'Rendez-vous marqué « %s ».', 'rhomija-booking' ), $all[ $st ] ) ) . '</p></div>';
			}
		}
		$warn = get_transient( 'rb_conflict_' . get_current_user_id() );
		if ( $warn ) {
			delete_transient( 'rb_conflict_' . get_current_user_id() );
			echo '<div class="notice notice-warning"><p>' . esc_html( $warn ) . '</p></div>';
		}
	}

	public static function menu_badge() {
		global $menu;
		$count = self::count_view( 'pending' );
		if ( ! $count || ! is_array( $menu ) ) {
			return;
		}
		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=' . RB_Appointments::POST_TYPE === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $count . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}
	}

	public static function messages( $messages ) {
		$messages[ RB_Appointments::POST_TYPE ] = array_fill( 0, 11, __( 'Rendez-vous enregistré.', 'rhomija-booking' ) );
		$messages[ RB_Appointments::POST_TYPE ][0] = '';
		return $messages;
	}

	/* ---------- Edit screen ---------- */

	public static function meta_boxes() {
		remove_meta_box( 'slugdiv', RB_Appointments::POST_TYPE, 'normal' );
		add_meta_box( 'rb_details', __( 'Détails du rendez-vous', 'rhomija-booking' ), array( __CLASS__, 'details_box' ), RB_Appointments::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'rb_contact', __( 'Contacter le patient', 'rhomija-booking' ), array( __CLASS__, 'contact_box' ), RB_Appointments::POST_TYPE, 'side', 'high' );
	}

	public static function details_box( $post ) {
		$a        = RB_Appointments::get( $post->ID );
		$services = RB_Settings::services();
		if ( '' === $a['date'] ) {
			$a['date']   = RB_Availability::now()->format( 'Y-m-d' );
			$a['status'] = 'confirmed';
		}
		wp_nonce_field( 'rb_save_' . $post->ID, 'rb_nonce' );
		?>
		<div class="rb-admin-grid">
			<p><label for="rb_date"><?php esc_html_e( 'Date', 'rhomija-booking' ); ?></label>
				<input type="date" id="rb_date" name="rb[date]" value="<?php echo esc_attr( $a['date'] ); ?>" required></p>
			<p><label for="rb_time"><?php esc_html_e( 'Heure', 'rhomija-booking' ); ?></label>
				<input type="time" id="rb_time" name="rb[time]" value="<?php echo esc_attr( $a['time'] ); ?>" step="300" required></p>
			<p><label for="rb_status"><?php esc_html_e( 'Statut', 'rhomija-booking' ); ?></label>
				<select id="rb_status" name="rb[status]">
					<?php foreach ( RB_Appointments::statuses() as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $a['status'], $k ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select></p>
			<p><label for="rb_service"><?php esc_html_e( 'Motif', 'rhomija-booking' ); ?></label>
				<select id="rb_service" name="rb[service]">
					<?php if ( $a['service'] && ! isset( $services[ $a['service'] ] ) ) : ?>
						<option value="<?php echo esc_attr( $a['service'] ); ?>" selected><?php echo esc_html( $a['service'] ); ?></option>
					<?php endif; ?>
					<?php foreach ( $services as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $a['service'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select></p>
			<p><label for="rb_name"><?php esc_html_e( 'Nom du patient', 'rhomija-booking' ); ?></label>
				<input type="text" id="rb_name" name="rb[name]" value="<?php echo esc_attr( $a['name'] ); ?>" required></p>
			<p><label for="rb_phone"><?php esc_html_e( 'Téléphone', 'rhomija-booking' ); ?></label>
				<input type="tel" id="rb_phone" name="rb[phone]" value="<?php echo esc_attr( $a['phone'] ); ?>"></p>
			<p><label for="rb_email"><?php esc_html_e( 'E-mail', 'rhomija-booking' ); ?></label>
				<input type="email" id="rb_email" name="rb[email]" value="<?php echo esc_attr( $a['email'] ); ?>"></p>
			<p><label for="rb_new"><?php esc_html_e( 'Nouveau patient', 'rhomija-booking' ); ?></label>
				<select id="rb_new" name="rb[new_patient]">
					<option value=""><?php esc_html_e( '—', 'rhomija-booking' ); ?></option>
					<option value="yes" <?php selected( $a['new_patient'], 'yes' ); ?>><?php esc_html_e( 'Oui', 'rhomija-booking' ); ?></option>
					<option value="no" <?php selected( $a['new_patient'], 'no' ); ?>><?php esc_html_e( 'Non', 'rhomija-booking' ); ?></option>
				</select></p>
			<p class="rb-admin-full"><label for="rb_message"><?php esc_html_e( 'Message / notes', 'rhomija-booking' ); ?></label>
				<textarea id="rb_message" name="rb[message]" rows="4"><?php echo esc_textarea( $a['message'] ); ?></textarea></p>
		</div>
		<p class="description"><?php esc_html_e( 'Passer le statut à « Confirmé » envoie automatiquement un e-mail de confirmation (avec invitation agenda) si le patient a indiqué son e-mail.', 'rhomija-booking' ); ?></p>
		<?php
	}

	public static function contact_box( $post ) {
		$a = RB_Appointments::get( $post->ID );
		if ( ! $a['phone'] ) {
			echo '<p>' . esc_html__( 'Enregistrez d’abord le numéro du patient.', 'rhomija-booking' ) . '</p>';
			return;
		}
		echo '<p><a class="button button-secondary rb-wide" href="tel:+' . esc_attr( rb_phone_intl( $a['phone'] ) ) . '">' . esc_html__( 'Appeler', 'rhomija-booking' ) . ' ' . esc_html( $a['phone'] ) . '</a></p>';
		$wa = rb_whatsapp_url( $a['phone'], RB_Notifications::whatsapp_confirm_text( $a ) );
		if ( $wa ) {
			echo '<p><a class="button button-primary rb-wide rb-wa" target="_blank" rel="noopener" href="' . esc_url( $wa ) . '">' . esc_html__( 'Confirmer sur WhatsApp', 'rhomija-booking' ) . '</a></p>';
			echo '<p class="description">' . esc_html__( 'Ouvre WhatsApp avec un message de confirmation pré-rempli.', 'rhomija-booking' ) . '</p>';
		}
	}

	public static function save( $id, $post ) {
		if ( ! isset( $_POST['rb_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['rb_nonce'] ), 'rb_save_' . $id ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$in = isset( $_POST['rb'] ) && is_array( $_POST['rb'] ) ? wp_unslash( $_POST['rb'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.

		$clean = array();
		foreach ( RB_Appointments::FIELDS as $key => $type ) {
			$short = substr( $key, 4 );
			if ( isset( $in[ $short ] ) && 'status' !== $short ) {
				$clean[ $short ] = RB_Appointments::sanitize( $type, $in[ $short ] );
			}
		}
		if ( ! get_post_meta( $id, '_rb_source', true ) ) {
			$clean['source'] = 'admin';
		}
		RB_Appointments::save_meta( $id, $clean );
		$status = isset( $in['status'] ) ? RB_Appointments::sanitize( 'status', $in['status'] ) : 'pending';

		if ( in_array( $status, RB_Appointments::active_statuses(), true ) && ! empty( $clean['date'] ) && ! empty( $clean['time'] ) ) {
			$counts = RB_Availability::booked_counts( $clean['date'], $clean['date'], $id );
			$taken  = isset( $counts[ $clean['date'] . ' ' . $clean['time'] ] ) ? $counts[ $clean['date'] . ' ' . $clean['time'] ] : 0;
			if ( $taken >= (int) RB_Settings::get( 'capacity' ) ) {
				set_transient( 'rb_conflict_' . get_current_user_id(), __( 'Attention : un autre rendez-vous occupe déjà ce créneau.', 'rhomija-booking' ), 60 );
			}
		}

		RB_Appointments::set_status( $id, $status );

		$a = RB_Appointments::get( $id );
		remove_action( 'save_post_' . RB_Appointments::POST_TYPE, array( __CLASS__, 'save' ), 10 );
		wp_update_post(
			array(
				'ID'           => $id,
				'post_title'   => RB_Appointments::title_for( $a ),
				'post_content' => RB_Appointments::search_blob( $a ),
			)
		);
		add_action( 'save_post_' . RB_Appointments::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
	}

	/* ---------- Dashboard ---------- */

	public static function dashboard() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		wp_add_dashboard_widget( 'rb_upcoming', __( 'Prochains rendez-vous', 'rhomija-booking' ), array( __CLASS__, 'dashboard_widget' ) );

		// The clinic team opens the dashboard for this: show it first.
		global $wp_meta_boxes;
		$normal = isset( $wp_meta_boxes['dashboard']['normal']['core'] ) ? $wp_meta_boxes['dashboard']['normal']['core'] : array();
		if ( isset( $normal['rb_upcoming'] ) ) {
			$wp_meta_boxes['dashboard']['normal']['core'] = array( 'rb_upcoming' => $normal['rb_upcoming'] ) + $normal; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	public static function dashboard_widget() {
		$q       = new WP_Query(
			self::view_args( 'upcoming' ) + array(
				'post_type'      => RB_Appointments::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 8,
				'meta_key'       => '_rb_start',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'fields'         => 'ids',
			)
		);
		$pending = self::count_view( 'pending' );
		$list    = admin_url( 'edit.php?post_type=' . RB_Appointments::POST_TYPE );
		if ( $pending ) {
			printf(
				'<p class="rb-dash-alert"><a href="%s">%s</a></p>',
				esc_url( add_query_arg( 'rb_view', 'pending', $list ) ),
				esc_html( sprintf( /* translators: %d: count */ _n( '%d demande à confirmer', '%d demandes à confirmer', $pending, 'rhomija-booking' ), $pending ) )
			);
		}
		if ( ! $q->posts ) {
			echo '<p>' . esc_html__( 'Aucun rendez-vous à venir.', 'rhomija-booking' ) . '</p>';
		} else {
			echo '<ul class="rb-dash">';
			foreach ( $q->posts as $id ) {
				$a   = RB_Appointments::get( $id );
				$day = RB_Availability::parse_date( $a['date'] );
				printf(
					'<li><a href="%s"><strong>%s · %s</strong> — %s</a> <span>%s</span> %s</li>',
					esc_url( get_edit_post_link( $id ) ),
					esc_html( $day ? ucfirst( wp_date( 'D j M', $day->getTimestamp() ) ) : '' ),
					esc_html( RB_Appointments::format_time( $a['time'] ) ),
					esc_html( $a['name'] ),
					esc_html( $a['service_label'] ),
					self::status_badge( $a['status'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in method.
				);
			}
			echo '</ul>';
		}
		printf(
			'<p><a class="button" href="%s">%s</a> <a class="button" href="%s">%s</a></p>',
			esc_url( $list ),
			esc_html__( 'Tous les rendez-vous', 'rhomija-booking' ),
			esc_url( admin_url( 'post-new.php?post_type=' . RB_Appointments::POST_TYPE ) ),
			esc_html__( 'Ajouter un rendez-vous', 'rhomija-booking' )
		);
	}
}
