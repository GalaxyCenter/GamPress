<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/2
 * Time: 9:42
 */

defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-books-component.php';
}

function gp_setup_books() {
    gampress()->books = new GP_Books_Component();
}
add_action( 'gp_setup_components', 'gp_setup_books', 6 );