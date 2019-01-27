<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/12
 * Time: 23:29
 */

function gp_orders_get_order_actions( $args = array(), $output = 'names', $operator = 'and' ) {
    $actions = array(
        __( 'Payed',    'gampress-ext' ) => 4,
    );
    return $actions;
}

function gp_orders_get_order_types() {
    $types = array(
        __( 'Book',    'gampress-ext' ) => GP_Orders_Order::BOOK,
        __( 'Book Coin',    'gampress-ext' ) => GP_Orders_Order::COIN,
        __( 'Book Ticket',    'gampress-ext' ) => GP_Orders_Order::TICKET,
        __( 'Recharge',    'gampress-ext' ) => GP_Orders_Order::RECHARGE,
    );
    return $types;
}