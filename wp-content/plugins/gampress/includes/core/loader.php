<?php
/**
 * GamPress Core Loader.
 *
 * ⊙▂⊙
 * 
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package GamPress
 * @subpackage Core
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-component.php';
    require dirname( __FILE__ ) . '/classes/class-core.php';
}

/**
 * Set up the gp-core component.
 *
 * @since 1.0
 */
function gp_setup_core() {
    gampress()->core = new GP_Core();
}
add_action( 'gp_loaded', 'gp_setup_core', 0 );