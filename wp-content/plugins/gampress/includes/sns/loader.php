<?php
/**
 * GamPress Sns Loader
 * 
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage SnsCore
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-sns-component.php';
}

function gp_setup_sns() {
    gampress()->sns = new GP_Sns_Component();
}
add_action( 'gp_setup_components', 'gp_setup_sns', 6 );
