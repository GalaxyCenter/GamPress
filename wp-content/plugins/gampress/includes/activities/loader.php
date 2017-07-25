<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/2
 * Time: 14:31
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-activities-component.php';
}

function gp_setup_activities() {
    gampress()->activities = new GP_Activities_Component();
}
add_action( 'gp_setup_components', 'gp_setup_activities', 6 );
