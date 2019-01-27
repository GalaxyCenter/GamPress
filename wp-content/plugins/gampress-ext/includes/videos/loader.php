<?php
/**
 * GamPress-Ext Videos Loader.
 *
 * ⊙▂⊙
 * 
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package GamPress-Ext
 * @subpackage Topics
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-videos-compontent.php';
}

function gp_setup_videos() {
    gampress()->videos = new GP_Videos_Component();
}
add_action( 'gp_setup_components', 'gp_setup_videos', 6 );