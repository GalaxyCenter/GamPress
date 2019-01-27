<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 23:34
 */

function gp_orders_update_order( $args = false ) {
    if ( empty( $args ) )
        return false;

    $defaults = array(
        'id'                => 0,
        'order_id'          => 0,
        'product_id'        => 0,
        'item_id'           => 0,
        'user_id'           => 0,
        'price'             => 0,
        'create_time'       => '',
        'pay_time'          => '',
        'quantity'          => '',
        'total_fee'         => 0,
        'status'            => '',
        'age'               => 0,
        'time'              => 0,
        'type'              => GP_Orders_Order::BOOK,
        'ip'                => get_remote_ip(),
        'come_from'         => ''
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    if ( !empty( $order_id ) ) {
        $order = gp_orders_get_order( $order_id );
    } else {
        $order = new GP_Orders_Order();
        $order->id                  = $id;
        $order_id = gp_orders_create_order_id( $user_id );
    }

    $order->order_id            = $order_id;
    $order->product_id          = $product_id;
    $order->item_id             = $item_id;
    $order->user_id             = $user_id;
    $order->price               = $price;
    $order->create_time         = $create_time;
    $order->pay_time            = $pay_time;
    $order->quantity            = $quantity;
    $order->total_fee           = $total_fee;
    $order->status              = $status;
    $order->type                = $type;
    $order->age                 = $age;
    $order->time                = $time;
    $order->ip                  = $ip;
    $order->come_from           = $come_from;

    if ( !$order->save() )
        return $order;

    wp_cache_set( 'order_' . $order_id, $order, 'gampress-ext' );

    return $order_id;
}

function gp_get_orders_top_item( $mondate, $count ) {
    $cache_key = 'orders_top_item_' . $mondate . '_' . $count;

    $items = wp_cache_get( $cache_key, 'gampress-ext' );
    if ( empty( $items ) ) {
        $items = GP_Orders_Order::get_top_item( $mondate, $count );
        $items = apply_filters( 'gp_get_orders_top_item', $items );

        wp_cache_set( $cache_key, $items, 'gampress-ext', 86400 );
    }
    return $items;
}

function gp_get_orders_top_item2( $mondate, $count ) {
    $cache_key = 'orders_top_item2_' . $mondate . '_' . $count;

    $items = wp_cache_get( $cache_key, 'gampress-ext' );
    if ( empty( $items ) ) {
        $items = GP_Orders_Order::get_top_item2( $mondate, $count );
        $items = apply_filters( 'gp_get_orders_top_item', $items );

        wp_cache_set( $cache_key, $items, 'gampress-ext', 86400 );
    }
    return $items;
}

function gp_get_orders_order_status( $order_id ) {
    return GP_Orders_Order::get_order_status( $order_id );
}

function gp_orders_update_order_status( $order_id, $status, $status_old ) {
    if ( empty( $order_id ) || empty( $status ) || empty( $status_old ) )
        return false;

    GP_Orders_Order::update_status( $order_id, $status, $status_old );

    // 更新次数
    if ( $status == GP_ORDER_PAID ) {
        $order = gp_orders_get_order( $order_id );
        $time = GP_Orders_Order::get_user_order_count( $order->user_id, GP_ORDER_PAID, $order->type );
        GP_Orders_Order::update_time( $order_id, $time );
    }
    wp_cache_delete( 'order_' . $order_id, 'gampress-ext' );
}

function gp_orders_update_type( $order_id, $type ) {
    if ( empty( $order_id ) || empty( $type ) )
        return false;

    wp_cache_delete( 'order_' . $order_id, 'gampress-ext' );

    return GP_Orders_Order::gp_orders_update_type( $order_id, $type );
}

function gp_orders_update_pay_time( $order_id, $time ) {
    if ( empty( $order_id ) || empty( $time ) )
        return false;

    wp_cache_delete( 'order_' . $order_id, 'gampress-ext' );

    return GP_Orders_Order::update_pay_time( $order_id, $time );
}

function gp_orders_get_order_count( $user_id, $product_id ) {
    if ( empty( $user_id ) && empty( $product_id ) )
        return 0;

    return GP_Orders_Order::get_order_count( $user_id, $product_id );
}

function gp_orders_get_order( $order_id ) {
    if ( empty( $order_id ) )
        return false;

    if ( !$order = wp_cache_get( 'order_' . $order_id, 'gampress-ext' ) ) {

        $order = new GP_Orders_Order( $order_id );
        wp_cache_set( 'order_' . $order_id, $order, 'gampress-ext' );
    }

    return $order;
}

function gp_orders_get_orders ( $args = '' ) {
    if ( empty( $args ) )
        return false;

    $orders = GP_Orders_Order::get( $args );
    return $orders;
}

function gp_orders_update_ordermeta( $order_id, $meta_key, $meta_value ) {
    if ( empty( $order_id ) )
        return false;

    global $wpdb;

    $gp = gampress();

    $meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

    if ( is_string( $meta_value ) )
        $meta_value = stripslashes( esc_sql( $meta_value ) );

    $meta_value = maybe_serialize( $meta_value );

    $cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$gp->orders->table_meta_name} WHERE order_id = %s AND meta_key = %s", $order_id, $meta_key ) );

    if ( !$cur || empty( $cur ) )
        $wpdb->query( $wpdb->prepare( "INSERT INTO {$gp->orders->table_meta_name} ( order_id, meta_key, meta_value ) VALUES ( %s, %s, %s )", $order_id, $meta_key, $meta_value ) );
    else if ( $cur->meta_value != $meta_value )
        $wpdb->query( $wpdb->prepare( "UPDATE {$gp->orders->table_meta_name} SET meta_value = %s WHERE order_id = %s AND meta_key = %s", $meta_value, $order_id, $meta_key ) );
    else
        return false;

    // Update the cached object and recache
    wp_cache_set( 'gp_ordermeta_' . $order_id . '_' . $meta_key, $meta_value, 'gampress-ext' );

    return true;
}

function gp_orders_ajax_update_ordermeta() {
    $order_id = $_POST['order_id'];
    $meta_key = $_POST['meta_key'];
    $meta_value = $_POST['meta_value'];

    gp_update_ordermeta( $order_id, $meta_key, $meta_value );
    die();
}

function gp_orders_delete_ordermeta( $order_id, $meta_key = false ) {
    if ( empty( $order_id ) )
        return false;

    global $wpdb;
    $gp = gampress();

    $meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

    if ( is_array( $meta_value ) || is_object( $meta_value ) )
        $meta_value = serialize($meta_value);

    $meta_value = trim( $meta_value );

    if ( !$meta_key )
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$gp->orders->table_meta_name} WHERE order_id = %s", $order_id ) );
    else if ( $meta_value )
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$gp->orders->table_meta_name} WHERE order_id = %s AND meta_key = %s AND meta_value = %s", $order_id, $meta_key, $meta_value ) );
    else
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$gp->orders->table_meta_name} WHERE order_id = %s AND meta_key = %s", $order_id, $meta_key ) );

    // Delete the cached object
    wp_cache_delete( 'gp_ordermeta_' . $order_id . '_' . $meta_key, 'gampress-ext' );

}

function gp_orders_ordermeta( $order_id, $meta_key = '' ) {
    echo gp_get_ordermeta( $order_id, $meta_key );
}

function gp_orders_get_ordermeta( $order_id, $meta_key = '' ) {
    if ( empty( $order_id ) )
        return false;

    global $wpdb;
    $gp = gampress();

    $metas = wp_cache_get( 'gp_ordermeta_' . $order_id . '_' . $meta_key, 'gampress-ext' );
    if ( false === $metas ) {
        if ( !empty( $meta_key ) ) {
            $meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );
            $metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM {$gp->orders->table_meta_name} WHERE order_id = %s AND meta_key = %s", $order_id, $meta_key ) );
        } else {
            $metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM {$gp->orders->table_meta_name} WHERE order_id = %s", $order_id ) );
        }

        wp_cache_set( 'gp_ordermeta_' . $order_id . '_' . $meta_key, $metas, 'gampress-ext' );
    }

    if ( empty( $metas ) ) {
        if ( empty( $meta_key ) )
            return array();
        else
            return '';
    }

    $metas = array_map( 'maybe_unserialize', (array) $metas );

    if ( 1 == count( $metas ) )
        return $metas[0];
    else
        return $metas;
}

function gp_orders_ajax_get_ordermeta() {
    $order_id = $_POST['order_id'];
    $meta_key = $_POST['meta_key'];

    $meta_value = gp_get_ordermeta( $order_id, $meta_key );
    $data = array(
        'order_id'   => $order_id,
        'meta_key'   => $meta_key,
        'meta_value' => $meta_value );

    echo json_encode( $data );
    die();
}

function gp_orders_get_order_status_types() {
    $types = array(
        __( 'Normal', 'gampress-ext' )        => GP_ORDER_NORMAL,
        __( 'Locked', 'gampress-ext' )        => GP_ORDER_LOCKED,
        __( 'Submit', 'gampress-ext' )        => GP_ORDER_SUBMIT,
        __( 'Paid', 'gampress-ext' )          => GP_ORDER_PAID,
        __( 'Confirming', 'gampress-ext' )    => GP_ORDER_CONFIRMING,
        __( 'Confirmed', 'gampress-ext' )     => GP_ORDER_CONFIRMED,
        __( 'Complete', 'gampress-ext' )      => GP_ORDER_COMPLETE,
        __( 'Cancel', 'gampress-ext' )        => GP_ORDER_CANCEL,
        __( 'Modify', 'gampress-ext' )        => GP_ORDER_MODIFY,
        __( 'Canceled', 'gampress-ext' )      => GP_ORDER_CANCELED
    );

    return $types;
}

function gp_orders_create_order_id( $user_id ) {
    $user_suffix = sprintf("%04d", $user_id);
    if ( strlen( $user_suffix ) > 4  )
        $user_suffix = substr( $user_suffix, strlen( $user_suffix ) - 4, 4 );

    $time_suffix = get_time();
    $time_suffix = substr( $time_suffix, strlen( $time_suffix ) - 3, 3 );

    $rand_suffix = mt_rand( 100, 999 );

    $redis = new Redis();
    $redis->connect('127.0.0.1',6379);
    $id_suffix = $redis->incr('gp_orders');
    //$id_suffix = sprintf("1%05d", $id_suffix);
    $id_suffix = $id_suffix + 10000;

    return $id_suffix . $time_suffix . $rand_suffix . $user_suffix;
}

/**
 *
 * Coin Bill
 *
 */
function gp_orders_coin_add_bill( $args = false ) {
    if ( empty( $args ) )
        return false;

    $defaults = array(
        'id'                => 0,
        'user_id'           => 0,
        'type'              => 0,
        'fee'               => 0,
        'tran_no'           => 0,
        'order_id'          => 0,
        'item_id'           => 0,
        'description'       => '',
        'create_time'       => gp_format_time( time() )
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    if ( GP_Orders_Coin_Bill::exists2( $user_id, $order_id, $item_id ) ) {
        return false;
    }

    // redis 原子操作确保没脏数据
    $key = $user_id . $order_id . $item_id;
    $redis = new Redis();
    $redis->connect('127.0.0.1',6379);
    if ( $redis->get( $key ) == "1" )
        return false;

    $redis->set( $key, "1" );

    $bill = new GP_Orders_Coin_Bill();
    $bill->user_id          = $user_id;
    $bill->type             = $type;
    $bill->fee              = $fee;
    $bill->tran_no          = gp_orders_coin_create_tran_no( $user_id );
    $bill->order_id         = $order_id;
    $bill->item_id          = $item_id;
    $bill->description      = $description;
    $bill->create_time      = $create_time;

    GP_Log::INFO( 'order,gp_orders_coin_add_bill,' . json_encode( $bill ) );

    if ( !$bill->save() )
        return $bill;

    return $bill->tran_no;
}

function gp_orders_coin_create_tran_no( $user_id ) {
    $user_suffix = sprintf("%04d", $user_id);
    if ( strlen( $user_suffix ) > 4 )
        $user_suffix = substr( $user_suffix, strlen( $user_suffix ) - 4, 4 );

    $time_suffix = get_time();
    $time_suffix = substr( $time_suffix, strlen( $time_suffix ) - 3, 3 );

    $rand_suffix = mt_rand( 100, 999 );

    $redis = new Redis();
    $redis->connect('127.0.0.1',6379);
    $id_suffix = $redis->incr('gp_orders_bill');
    //$id_suffix = sprintf("1%05d", $id_suffix);
    $id_suffix = $id_suffix + 10000;

    return $id_suffix . $time_suffix . $user_suffix . $rand_suffix;
}

function gp_orders_coin_get_bills ( $args = '' ) {
    if ( empty( $args ) )
        return false;

    GP_Log::INFO( 'order,gp_orders_coin_get_bills,' . json_encode( $args ) );
    $datas = GP_Orders_Coin_Bill::get( $args );
    return $datas;
}

function gp_orders_total_coin_for_user( $user_id ) {
    echo gp_orders_get_total_coin_for_user( $user_id );
}

    function gp_orders_get_total_coin_for_user( $user_id ) {
        return GP_Orders_Coin_Bill::get_total_coin_for_user( $user_id );
    }

    function gp_orders_get_ticket_total_coin_for_user( $user_id, $tran_no = false ) {
        return GP_Orders_Coin_Bill::get_ticket_total_coin_for_user( $user_id, $tran_no );
    }

/**
 * Tickets
 */
function gp_orders_get_tickets( $user_id ) {
    return GP_Orders_Tickets::get( $user_id );
}

function gp_orders_tickets_verify( $id, $user_id ) {
    return GP_Orders_Tickets::verify( $id, $user_id );
}

function gp_orders_get_tickets_total_fee( $user_id ) {
    $tickets = gp_orders_get_tickets( $user_id );
    $total = 0;

    // 取出所有有效的券, 然后计算被使用的量,将剩余的求和就是 能用的红包量
    foreach( $tickets as $ticket ) {
        $used_fee = gp_orders_get_ticket_total_coin_for_user( $user_id, $ticket->id );
        $total += $ticket->fee - abs( $used_fee );
    }
    return $total;
}

function gp_orders_create_ticket_id( $user_id ) {
    $user_suffix = sprintf("%06d", $user_id);
    if ( strlen( $user_suffix ) > 4 )
        $user_suffix = substr( $user_suffix, strlen( $user_suffix ) - 6, 6 );

    $time_suffix = get_time();
    $time_suffix = substr( $time_suffix, strlen( $time_suffix ) - 4, 4 );

    $rand_suffix = mt_rand( 100, 999 );

    $redis = new Redis();
    $redis->connect('127.0.0.1',6379);
    $id_suffix = $redis->incr('gp_tickets');
    $id_suffix = sprintf("1%04d", $id_suffix);

    return $id_suffix . $user_suffix . $time_suffix . $rand_suffix;
}

function gp_orders_add_ticket( $args = false ) {
    $defaults = array(
        'id'                => 0,
        'name'              => 0,
        'user_id'           => 0,
        'fee'               => 0,
        'type'              => 0,
        'expired'           => 0,
        'create_time'       => gp_format_time( time() )
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    $ticket = new GP_Orders_Tickets();

    $ticket->id               = gp_orders_create_ticket_id( $user_id );
    $ticket->name             = $name;
    $ticket->user_id          = $user_id;
    $ticket->fee              = $fee;
    $ticket->type             = $type;
    $ticket->expired          = $expired;
    $ticket->create_time      = $create_time;

    return $ticket->save();
}