<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/11
 * Time: 15:33
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-sms-component.php';
}

function gp_setup_sms() {
    gampress()->sms = new GP_Sms_Component();
}
add_action( 'gp_setup_components', 'gp_setup_sms', 6 );
