<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/9
 * Time: 20:58
 */

function gp_book_admin_backpat_menu() {
    global $_parent_pages;
    remove_menu_page( 'gp-chapters' );

    // These stop people accessing the URL directly.
    unset( $_parent_pages['gp-chapters'] );
}
add_action( gp_core_admin_hook(), 'gp_book_admin_backpat_menu', 999 );

function gp_register_book_taxonomies() {
    register_taxonomy( 'book_library', 'book_library', array( 'public' => false, 'hierarchical' => true ) );
}
add_action( 'gp_register_taxonomies', 'gp_register_book_taxonomies' );


function gp_books_orders_action_create_order( $user_id, $order_id, $item_id ) {
    $auto_create_order = isset( $_POST['auto_create_order'] ) ? $_POST['auto_create_order'] : false;
    gp_books_user_update_auto_create_order( $user_id, $item_id, $auto_create_order );
}
add_action( 'gp_orders_action_create_order', 'gp_books_orders_action_create_order', 10, 3 );

function gp_books_footer() {
    ///GP_Log::TRACE();
}
add_action( 'gp_footer', 'gp_books_footer' );

function gp_books_user_checkin( $user_id ) {
    gp_orders_add_ticket( array(
        'id'                => 0,
        'name'              => '签到赠送',
        'user_id'           => $user_id,
        'fee'               => 50,
        'type'              => 'ticket',
        'expired'           => gp_format_time( time() + ( 86400 * 3 ) ),
        'create_time'       => gp_format_time( time() )
    ) );

//    $data = gp_books_get_book_history( $user_id );
//    $open_id = gp_sns_get_sns_user_id( $user_id );
//    $user = gp_core_get_core_userdata( $user_id );
//    $display_name = $user->display_name;
//
//    $data = array('touser' => $open_id,
//        'template_id' => '0RKea8egRnEYPlvSCYRhODP82J4fvlAxmZXwEX2EBq8',
//        'url' => $data['link'],
//        'data' => array(
//            'first' => array(
//                'value' => '签到50赠币已到账~',
//                'color' => '#173177'
//            ),
//            'keyword1' => array(
//                'value' => $display_name,
//                'color' => '#173177'
//            ),
//            'keyword2' => array(
//                'value' => '50呆熊币',
//                'color' => '#173177'
//            ),
//            'keyword3' => array(
//                'value' => gp_core_current_time(),
//                'color' => '#173177'
//            ),
//            'remark' => array(
//                'value' => '50呆熊币已到账，请明日继续签到哦
//
//点我继续上次阅读》》！',
//                'color' => '#173177'
//            )
//        ) );
//
//    $wechat = new GP_Sns_OAuth_Wechat();
//    $wechat->request_access_token( 'client_credential' );
//    $wechat->send_template_data($data);
}
add_action( 'gp_games_user_checkin', 'gp_books_user_checkin', 10, 1 );

function gp_books_user_checkined( $user_id ) {

    $data = gp_books_get_book_history( $user_id );
    $open_id = gp_sns_get_sns_user_id( $user_id );
    $user = gp_core_get_core_userdata( $user_id );
    $display_name = $user->display_name;

    $data = array('touser' => $open_id,
        'template_id' => '0RKea8egRnEYPlvSCYRhODP82J4fvlAxmZXwEX2EBq8',
        'url' => $data['link'],
        'data' => array(
            'first' => array(
                'value' => '今日已领过签到赠币了~',
                'color' => '#173177'
            ),
            'keyword1' => array(
                'value' => $display_name,
                'color' => '#173177'
            ),
            'keyword2' => array(
                'value' => '50呆熊币',
                'color' => '#173177'
            ),
            'keyword3' => array(
                'value' => gp_core_current_time(),
                'color' => '#173177'
            ),
            'remark' => array(
                'value' => '今日已领过赠币了，请明日继续签到领币哦~
        
点我继续上次阅读》》！',
                'color' => '#173177'
            )
        ) );

    $wechat = new GP_Sns_Wechat_Base();
    $wechat->request_access_token();
    $wechat->send_template_data($data);
}
//add_action( 'gp_games_user_checkined', 'gp_books_user_checkined', 10, 1 );