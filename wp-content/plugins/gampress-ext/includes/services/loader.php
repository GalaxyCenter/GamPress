<?php
/**
 * Created by PhpStorm.
 * User: Texel
 * Date: 2017/2/20
 * Time: 9:10
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-services-compontent.php';
}

function gp_setup_services() {
    gampress()->services = new GP_Services_Component();
}
add_action( 'gp_setup_components', 'gp_setup_services', 6 );