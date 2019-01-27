<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/21
 * Time: 22:50
 */

function orders_screen_index() {
    if ( gp_is_orders_component() && gp_is_current_action( 'cashier' ) ) {
        gp_core_load_template( 'orders/cashier' );
    }
}
add_action( 'gp_screens', 'orders_screen_index' );

