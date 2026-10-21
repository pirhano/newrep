<?php
/**
 * Inline SVG icons (24×24, stroke based, inherit currentColor).
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

function rh_icon_paths() {
	return array(
		'tooth'      => '<path d="M12 6.2c-1.3-1.5-3.8-2-4.9-.3-1 1.6-.5 4 .3 5.9.5 1.2.9 2.8 1.2 4.4.3 1.3 1.4 1.5 1.8.3l.7-2.6c.2-1 1.6-1 1.8 0l.7 2.6c.4 1.2 1.5 1 1.8-.3.3-1.6.7-3.2 1.2-4.4.8-1.9 1.3-4.3.3-5.9-1.1-1.7-3.6-1.2-4.9.3Z"/>',
		'sparkle'    => '<path d="M12 3.5l1.6 4.4 4.4 1.6-4.4 1.6L12 15.5l-1.6-4.4L6 9.5l4.4-1.6L12 3.5Z"/><path d="M18.5 15l.7 1.8 1.8.7-1.8.7-.7 1.8-.7-1.8-1.8-.7 1.8-.7.7-1.8Z"/>',
		'implant'    => '<path d="M8 4.5c1.2-1 2.6-.9 4-.1 1.4-.8 2.8-.9 4 .1 1.2 1.1.9 3-.1 4.5H8.1C7.1 7.5 6.8 5.6 8 4.5Z"/><path d="M9 11.5h6M9.5 14h5M10 16.5h4M10.6 19h2.8M12 9v12"/>',
		'crown'      => '<path d="M4 8l3.5 3L12 5l4.5 6L20 8l-1.5 9h-13L4 8Z"/><path d="M5.5 20h13"/>',
		'child'      => '<circle cx="12" cy="7" r="3"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/><path d="M10 7h.01M14 7h.01"/>',
		'alert'      => '<path d="M13 2.5L5 13.5h6l-1 8 8-11h-6l1-8Z"/>',
		'drop'       => '<path d="M12 3.5c3 3.6 5.5 6.6 5.5 9.6a5.5 5.5 0 0 1-11 0c0-3 2.5-6 5.5-9.6Z"/><path d="M9.5 13.5a2.5 2.5 0 0 0 2.5 2.5"/>',
		'xray'       => '<rect x="3.5" y="5" width="17" height="14" rx="2.5"/><path d="M7 15c1-2.5 2-4 3-4s1.2 2 2 2 1-2 2-2 2 1.5 3 4"/>',
		'extraction' => '<path d="M12 9.2c-1-1.1-2.9-1.5-3.7-.2-.8 1.2-.4 3 .2 4.4.4.9.7 2.1.9 3.3.2 1 1 1.1 1.3.2l.5-2c.2-.7 1.2-.7 1.4 0l.5 2c.3.9 1.1.8 1.3-.2.2-1.2.5-2.4.9-3.3.6-1.4 1-3.2.2-4.4-.8-1.3-2.7-.9-3.7.2Z"/><path d="M12 2.5v3M9.5 4.5 12 2.5l2.5 2"/>',
		'shield'     => '<path d="M12 3l7.5 3v5.5c0 4.5-3.2 8.2-7.5 9.5-4.3-1.3-7.5-5-7.5-9.5V6L12 3Z"/><path d="m8.8 12.2 2.2 2.2 4.3-4.6"/>',
		'chair'      => '<path d="M6 4.5c1.5 0 2.5 1 2.8 2.5l1 5.5h6.7a2 2 0 0 1 2 2v1H9.8a2.5 2.5 0 0 1-2.4-1.9L5.2 6.8C5 5.6 5 4.5 6 4.5Z"/><path d="M10 15.5 8.5 20.5M16 15.5l1.5 5M7 20.5h12"/>',
		'chat'       => '<path d="M20.5 12a8 8 0 0 1-11.8 7L4 20.5l1.5-4.6A8 8 0 1 1 20.5 12Z"/><path d="M8.5 10.5h7M8.5 13.5h4.5"/>',
		'phone'      => '<path d="M6.6 3.5h2.6l1.4 4-1.9 1.3a11 11 0 0 0 6.5 6.5l1.3-1.9 4 1.4v2.6a2 2 0 0 1-2.1 2A16.5 16.5 0 0 1 4.6 5.6a2 2 0 0 1 2-2.1Z"/>',
		'whatsapp'   => '<path d="M20.5 11.8a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.4-4.2a8.5 8.5 0 1 1 15.6-4.5Z"/><path d="M9 8.2c.2-.4.5-.5.8-.5h.5c.2 0 .4.1.5.4l.7 1.6c.1.2 0 .5-.1.7l-.5.6c.6 1.2 1.6 2.1 2.8 2.7l.6-.6c.2-.2.4-.2.7-.1l1.6.7c.2.1.4.3.4.5v.5c0 .3-.1.6-.5.8-.6.4-1.4.6-2.2.4-2.6-.7-4.8-2.9-5.5-5.5-.2-.8 0-1.6.4-2.2Z"/>',
		'clock'      => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
		'pin'        => '<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.5"/>',
		'mail'       => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4 7 8 6 8-6"/>',
		'calendar'   => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/><path d="m9 15 2 2 4-4"/>',
		'check'      => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'menu'       => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'      => '<path d="M6 6l12 12M18 6 6 18"/>',
		'star'       => '<path d="m12 3.8 2.5 5.1 5.6.8-4 4 1 5.5L12 16.6l-5.1 2.6 1-5.5-4-4 5.6-.8L12 3.8Z"/>',
		'directions' => '<path d="M12 2.8 21.2 12 12 21.2 2.8 12 12 2.8Z"/><path d="M9.5 14v-2.5a1.5 1.5 0 0 1 1.5-1.5h3.5M13 8l1.8 2-1.8 2"/>',
		'facebook'   => '<path d="M14 8.5h2.5V5H14a3.5 3.5 0 0 0-3.5 3.5V11H8v3.5h2.5V21H14v-6.5h2.5L17 11h-3V9a.5.5 0 0 1 .5-.5Z"/>',
		'instagram'  => '<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.5"/><path d="M16.8 7.2h.01"/>',
		'chevron'    => '<path d="m6 9 6 6 6-6"/>',
	);
}

/**
 * Echo-ready SVG markup for an icon.
 */
function rh_icon( $name, $size = 24, $label = '' ) {
	$paths = rh_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'tooth';
	}
	$a11y = $label ? 'role="img" aria-label="' . esc_attr( $label ) . '"' : 'aria-hidden="true" focusable="false"';
	return '<svg class="icon icon-' . esc_attr( $name ) . '" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" ' . $a11y . '>' . $paths[ $name ] . '</svg>';
}

/**
 * Allowed SVG tags for wp_kses when icons are passed through filters.
 */
function rh_kses_svg() {
	$common = array( 'd' => true, 'cx' => true, 'cy' => true, 'r' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true );
	return array(
		'svg'    => array( 'class' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true, 'focusable' => true, 'role' => true, 'aria-label' => true ),
		'path'   => $common,
		'circle' => $common,
		'rect'   => $common,
	);
}
