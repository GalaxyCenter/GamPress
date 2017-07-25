<?php
/**
 * GamPress Member Loader
 * 
 * กัจyกั
 * 
 * @package GamPress
 * @subpackage MembersCore
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-members-component.php';
}

function gp_setup_members() {
    gampress()->members = new GP_Members_Component();
}
add_action( 'gp_setup_components', 'gp_setup_members', 6 );
