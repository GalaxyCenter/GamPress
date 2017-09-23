<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/9/2
 * Time: 7:45
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-links-component.php';
}

function gp_setup_links() {
    gampress()->links = new GP_Links_Component();
}
add_action( 'gp_setup_components', 'gp_setup_links', 6 );
