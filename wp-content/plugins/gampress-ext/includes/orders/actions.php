<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/25
 * Time: 20:25
 */

function gp_orders_action_create_order() {
    if ( !gp_is_orders_component() || !gp_is_current_action( 'create' ) )
        return false;

    if ( !is_user_logged_in() )
        throw new Exception( __( 'You are not logged in.', 'gampress' ) );

    $form_names = array(
        'product_id'          => array( 'required' => false, 'error' => '' ),
        'item_id'             => array( 'required' => false, 'error' => '' ),
        'price'               => array( 'required' => true, 'error' => __( 'The price can\'t be empty', 'gampress-ext' ) ),
        'quantity'            => array( 'required' => true, 'error' => __( 'The quantity can\'t be empty', 'gampress-ext' ) ),
        'total_fee'           => array( 'required' => true, 'error' => __( 'The total_fee can\'t be empty', 'gampress-ext' ) ),

        'pay_module'          => array( 'required' => true, 'error' => __( 'The pay_module can\'t be empty', 'gampress-ext' ) ),
        'product_name'        => array( 'required' => true, 'error' => __( 'The product_name can\'t be empty', 'gampress-ext' ) ),
        'product_description' => array( 'required' => true, 'error' => __( 'The product_description can\'t be empty', 'gampress-ext' ) ),
        'product_type'        => array( 'required' => true, 'error' => __( 'The product_type can\'t be empty', 'gampress-ext' ) ),

        'from'                => array( 'required' => false ),
        'redirect'            => array( 'required' => false ),
        'auto_create_order'   => array( 'required' => false )
    );
    $form_values = get_request_values( $form_names );
    if ( !empty( $form_values['error'] ) ) {

    } else {
        $user_id = gp_loggedin_user_id();
        gp_books_user_update_auto_create_order( $user_id, $form_values['values']['item_id'], $form_values['values']['auto_create_order'] );

        // 当是阅读类型的订单的时候，处理重复
        if ( 'book' == $form_values['values']['product_type'] ) {
            $order_id = GP_Orders_Order::get_order_id( $user_id, $form_values['values']['item_id'], $form_values['values']['product_id'] );
            if ( !empty( $order_id ) ) { // 如果已经存在订单则重定向到作品页面
                $chapter = gp_books_get_chapter( $form_values['values']['product_id'] );
                gp_core_redirect( gp_get_chapter_permalink( $chapter ) );
                return;
            }
        }

        $order_id = gp_orders_update_order( array(
            'order_id'        => 0,
            'product_id'      => $form_values['values']['product_id'],
            'item_id'         => $form_values['values']['item_id'],
            'user_id'         => $user_id,
            'price'           => $form_values['values']['price'],
            'create_time'     => gp_core_current_time(),
            'quantity'        => $form_values['values']['quantity'],
            'total_fee'       => $form_values['values']['total_fee'],
            'status'          => GP_ORDER_SUBMIT,
            'type'            => $form_values['values']['product_type'],
            'come_from'       => $form_values['values']['from'],
            ) );
    }

    $product_url = 'NOT_SHOW';
    $redirect = urlencode($form_values['values']['redirect']);
    $pay_url = gp_get_pays_directory_permalink() . "create/{$form_values['values']['pay_module']}?order_id={$order_id}&product_name={$form_values['values']['product_name']}&product_fee={$form_values['values']['total_fee']}&product_url={$product_url}&product_description={$form_values['values']['product_description']}&redirect={$redirect}";

    $user_id = gp_loggedin_user_id();
    do_action( 'gp_orders_action_create_order', $user_id, $order_id, $form_values['values']['item_id'] );

    gp_core_redirect( $pay_url );
}
add_action( 'gp_actions', 'gp_orders_action_create_order' );

/**
 * gp_orders_pays_success 会出现多次调用情况, 一种是第三方后台回调, 一种是url跳转
 * @param $order_id
 * @param $total_fee
 * @param $notify_time
 */
function gp_orders_pays_success( $order_id, $total_fee, $notify_time ) {
    $status = gp_get_orders_order_status( $order_id );
    GP_Log::INFO("gp_orders_pays_success-" . $order_id . '-'. $total_fee . '-'. $notify_time . '-' . $status );
    if ( $status == GP_ORDER_PAID )
        return;

    // 生成一条熊币账单, 如果已存在改订单,说明已经调用过该函数
    $order = gp_orders_get_order( $order_id );
    if ( !GP_Orders_Coin_Bill::exists( $order->user_id, $order_id ) ) {
        GP_Log::INFO("gp_orders_pays_success-" . $order_id . '-' . $total_fee . '-coinbill');
        $cb_fee = $total_fee * 100;
        $tran_no = gp_orders_coin_add_bill(array(
            'id'            => 0,
            'user_id'       => $order->user_id,
            'type'          => GP_Orders_Coin_Bill::RECHARGE,
            'fee'           => $cb_fee,
            'order_id'      => $order_id,
            'description'   => '充值熊币'
        ));

        $fee = 0;
        if ( $total_fee == 10 ) {
            $fee = 0;
        } else if ( $total_fee == 30 ) {
            $fee = 300;
        } else if ( $total_fee == 50 ) {
            $fee = 900;
        } else if ( $total_fee == 100 ) {
            $fee = 2500;
        } else if ( $total_fee == 200 ) {
            $fee = 6500;
        } else if ( $total_fee == 300 ) {
            $fee = 13000;
        } else if ( $total_fee == 500 ) {
            $fee = 33000;
        }
        if ( $fee != 0 ) {
            GP_Log::INFO("gp_orders_pays_success-" . $order_id . '-' . $fee . '-ticket');
            gp_orders_add_ticket( array(
                'id'                => 0,
                'name'              => '充值红包',
                'user_id'           => $order->user_id,
                'fee'               => $fee,
                'type'              => 'ticket',
                'expired'           => gp_format_time( time() + ( 86400 * 30 ) ),
                'create_time'       => gp_format_time( time() )
            ) );
        }
    }
    gp_orders_update_order_status( $order_id, GP_ORDER_PAID, GP_ORDER_SUBMIT );
    gp_orders_update_pay_time( $order_id, $notify_time );

    GP_Log::INFO("gp_orders_pays_success-" . $order_id . '-ok');
    do_action( 'gp_orders_pays_success', $order_id, $total_fee, $notify_time );
}
add_action( 'gp_pays_success', 'gp_orders_pays_success', 10, 3);