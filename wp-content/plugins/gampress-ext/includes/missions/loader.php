<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 12:00
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-missions-component.php';
}

function gp_setup_missions() {
    gampress()->missions = new GP_Missions_Component();
}
add_action( 'gp_setup_components', 'gp_setup_missions', 6 );