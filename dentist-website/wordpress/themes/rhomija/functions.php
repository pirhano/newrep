<?php
/**
 * Rhomija Dentaire theme bootstrap.
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

define( 'RH_VERSION', '1.0.0' );
define( 'RH_DIR', get_template_directory() );
define( 'RH_URI', get_template_directory_uri() );

require RH_DIR . '/inc/helpers.php';
require RH_DIR . '/inc/icons.php';
require RH_DIR . '/inc/setup.php';
require RH_DIR . '/inc/customizer.php';
require RH_DIR . '/inc/seo.php';
require RH_DIR . '/inc/content.php';
require RH_DIR . '/inc/installer.php';
