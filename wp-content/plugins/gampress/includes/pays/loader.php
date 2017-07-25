<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 11:09
 */

defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-sns-component.php';
}

function gp_setup_pays() {
    gampress()->pays = new GP_Pays_Component();
}
add_action( 'gp_setup_components', 'gp_setup_pays', 6 );