<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 23:35
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_is_orders_component() {
    return gp_is_current_component( 'orders' );
}

function gp_orders_root_slug() {
    echo gp_get_orders_root_slug();
}

function gp_get_orders_root_slug() {
    return gp()->orders->slug;
}

function gp_get_orders_slug() {
    return gp()->orders->slug;
}

function gp_get_order_topic( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    global $wpdb;
    $gp = gp();

    $product_id = $_order->product_id;

    $item_id = $wpdb->get_var( $wpdb->prepare( "SELECT item_id FROM {$gp->database->table_name_products} WHERE id = %s", $product_id ) );

    if ( empty( $item_id ) )
        return false;

    return gp_get_topic( $item_id );
}

function gp_order_create_time( $order ) {
    echo gp_get_order_create_time( $order );
}
function gp_get_order_create_time( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    return $_order->create_time;
}

function gp_order_price( $order ) {
    echo gp_get_order_price( $order );
}
function gp_get_order_price( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    return $_order->price;
}


function gp_order_total_price( $order ) {
    echo gp_get_order_total_price( $order );
}
function gp_get_order_total_price( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    return $_order->price * $_order->quantity;
}

function gp_order_total_fee( $order ) {
    echo gp_get_order_total_fee( $order );
}
function gp_get_order_total_fee( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    return $_order->total_fee;
}

function gp_order_quantity( $order ) {
    echo gp_get_order_quantity( $order );
}
function gp_get_order_quantity( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    return $_order->quantity;
}

function gp_order_pay_time( $order ) {
    echo gp_get_order_pay_time( $order );
}
function gp_get_order_pay_time( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    return $_order->pay_time;
}

function gp_order_order_id( $order ) {
    echo gp_get_order_order_id( $order );
}
function gp_get_order_order_id( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    return $_order->order_id;
}

function gp_status_description( $status ) {
    echo gp_get_status_description( $status );
}
function gp_get_status_description( $status ) {
    $desc = '';

    switch( $status ) {
        case GP_ORDER_NORMAL:
            $desc = __( 'Normal', 'gampress-ext' );
            break;

        case GP_ORDER_LOCKED:
            $desc = __( 'Locked', 'gampress-ext' );
            break;

        case GP_ORDER_SUBMIT:
            $desc = __( 'Submit', 'gampress-ext' );
            break;

        case GP_ORDER_PAID:
            $desc = __( 'Paid', 'gampress-ext' );
            break;

        case GP_ORDER_CONFIRMING:
            $desc = __( 'Confirming', 'gampress-ext' );
            break;

        case GP_ORDER_CONFIRMED:
            $desc = __( 'Confirmed', 'gampress-ext' );
            break;

        case GP_ORDER_COMPLETE:
            $desc = __( 'Complete', 'gampress-ext' );
            break;

        case GP_ORDER_CANCEL:
            $desc = __( 'Cancel', 'gampress-ext' );
            break;

        case GP_ORDER_MODIFY:
            $desc = __( 'Modify', 'gampress-ext' );
            break;

        case GP_ORDER_CANCELED:
            $desc = __( 'Canceled', 'gampress-ext' );
            break;
    }

    return $desc;
}

function gp_order_status( $order ) {
    echo gp_get_order_status( $order );
}
function gp_get_order_status( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    $desc = gp_get_status_description( $_order->status );

    return $desc;
}

function gp_get_order_item( $order ) {
    if ( empty( $order ) )
        return 0;

    if ( $order instanceof GP_Orders_Order || is_object( $order ) ) {
        $_order = $order;
    } else if ( is_numeric( $order ) ) {
        $_order = gp_get_order( $order );
    } else if ( is_array( $order ) ) {
        $_order = array2obj( $order );
    } else {
        return false;
    }

    $topic = gp_get_order_topic( $_order );
    return $topic;
}

function gp_is_orders_directory() {
    if ( ! gp_displayed_user_id() && gp_is_orders_component() && ! gp_current_action() ) {
        return true;
    }
    return false;
}