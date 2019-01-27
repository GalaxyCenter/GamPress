<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 23:33
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-orders-component.php';
}

function gp_setup_orders() {
    gampress()->orders = new GP_Orders_Component();
}
add_action( 'gp_setup_components', 'gp_setup_orders', 6 );