<?php
/**
 * GamPress-Ext Combines Loader.
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
    require dirname( __FILE__ ) . '/classes/class-gp-combines-compontent.php';
}

function gp_setup_combines() {
    gampress()->combines = new GP_Combines_Component();
}
add_action( 'gp_setup_components', 'gp_setup_combines', 6 );