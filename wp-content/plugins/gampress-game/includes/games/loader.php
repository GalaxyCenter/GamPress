<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 11:13
 */
if ( !defined( 'ABSPATH' ) ) exit;

if ( ! gampress()->do_autoload ) {
    require dirname(__FILE__) . '/classes/class-gp-games-component.php';
}

function gp_setup_games() {
    gampress()->games = new GP_Games_Component();
}
add_action( 'gp_setup_components', 'gp_setup_games', 6 );