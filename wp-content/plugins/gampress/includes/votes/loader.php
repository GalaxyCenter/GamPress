<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/19
 * Time: 17:21
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-votes-component.php';
}

function gp_setup_vote() {
    gampress()->votes = new GP_Votes_Component();
}
add_action( 'gp_setup_components', 'gp_setup_vote', 6 );