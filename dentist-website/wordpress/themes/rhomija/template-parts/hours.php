<?php
/**
 * Opening hours table.
 *
 * @package Rhomija
 */

?>
<table class="hours">
	<caption class="screen-reader-text"><?php esc_html_e( 'Horaires d’ouverture', 'rhomija' ); ?></caption>
	<tbody>
		<?php foreach ( rh_hours() as $rh_days => $rh_time ) : ?>
			<tr<?php echo 'Fermé' === $rh_time ? ' class="is-closed"' : ''; ?>>
				<th scope="row"><?php echo esc_html( $rh_days ); ?></th>
				<td><?php echo esc_html( $rh_time ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
