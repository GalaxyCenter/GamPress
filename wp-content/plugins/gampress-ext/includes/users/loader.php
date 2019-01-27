<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 18:23
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-users-compontent.php';
}

function gp_setup_users() {
    gampress()->users = new GP_Users_Component();
}
add_action( 'gp_setup_components', 'gp_setup_users', 6 );