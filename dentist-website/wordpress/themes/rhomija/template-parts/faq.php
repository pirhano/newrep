<?php
/**
 * Frequently asked questions (accordion using <details>).
 *
 * @package Rhomija
 */

?>
<div class="faq">
	<?php foreach ( rh_faq() as $rh_i => $rh_qa ) : ?>
		<details class="faq__item"<?php echo 0 === $rh_i ? ' open' : ''; ?>>
			<summary class="faq__q"><?php echo esc_html( $rh_qa[0] ); ?><span class="faq__chev"><?php echo rh_icon( 'chevron', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></summary>
			<div class="faq__a"><p><?php echo esc_html( $rh_qa[1] ); ?></p></div>
		</details>
	<?php endforeach; ?>
</div>
