<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/15
 * Time: 21:06
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-sms-component.php';
}

function gp_setup_messages() {
    gampress()->messages = new GP_Sms_Component();
}
add_action( 'gp_setup_components', 'gp_setup_messages', 6 );
