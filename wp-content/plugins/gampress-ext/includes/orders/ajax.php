<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/7
 * Time: 15:17
 */

function gp_orders_ajax_get_order() {
    $order_id = $_POST['order_id'];

    if ( empty( $order_id ) )
        die();

    $order = gp_get_order( $order_id );

    $user_object = get_userdata( $order->user_id );
    $topic = gp_get_order_topic( $order );

    $order->user_name = $user_object->user_login;
    $order->user_email = $user_object->user_email;
    $order->item_name = $topic->post_title;

    echo json_encode( $order );
    die();
}
add_action( 'wp_ajax_get_order', 'gp_orders_ajax_get_order' );

/**
 * 获取当前用户的账单
 */
function gp_orders_ajax_get_coin_bills() {
    $type               = (int) $_POST['type'];
    $page_index         = $_POST['page_index'];
    $page_size          = $_POST['page_size'];

    $datas = gp_orders_coin_get_bills( array(
        'user_id'               => gp_loggedin_user_id(),
        'type'                  => $type,
        'order'                 => 'DESC',
        'orderby'               => 'create_time',
        'search_term'           => false,
        'page'                  => $page_index,
        'per_page'              => $page_size
    ) );

    foreach( $datas['items'] as $item ) {
        $item->type = $type;
        $item->friendly_time = friendly_time( strtotime( $item->create_time ) );
    }
    ajax_die( 0, '', $datas );
}
add_action( 'wp_ajax_get_coin_bills', 'gp_orders_ajax_get_coin_bills' );

function gp_orders_user_sign_up( $user_id ) {
    gp_orders_add_ticket( array(
        'id'                => 0,
        'name'              => '登录红包',
        'user_id'           => $user_id,
        'fee'               => 300,
        'type'              => 'ticket',
        'expired'           => gp_format_time( time() + ( 86400 * 3 ) ),
        'create_time'       => gp_format_time( time() )
    ) );

    /// 11.12 需求要求关闭
    /// gp_core_add_message( __( 'SignUp Message', 'gampress-ext' ), 'success' );
}
//add_action( 'gp_user_sign_up', 'gp_orders_user_sign_up', 10, 2 );