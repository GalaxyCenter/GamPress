<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/30
 * Time: 14:53
 */

if ( !defined( 'ABSPATH' ) ) exit;

class GP_Books_Pays_Adaixiong {

    public function __construct() {
    }

    public function do_pay( $args ) {
        // 判断剩余金币是否可以支付
        $user_id = gp_loggedin_user_id();
        // 如果用户没登录，重定向
        if ( empty( $user_id ) ) {
            gp_core_redirect( gp_loggedin_user_domain() . 'recharge' );
            return;
        }

        $user_coin = gp_orders_get_total_coin_for_user( $user_id );
        $user_ticket = gp_orders_get_tickets_total_fee( $user_id );
        $total_coin = $user_coin + $user_ticket;

        $order = gp_orders_get_order( $args['order_id'] );
        $chapter = gp_books_get_chapter( $order->product_id );
        $book = gp_books_get_book( $order->item_id );

        $product_fee = $args['product_fee'] * 100;
        // 如果商品价格 大于 总金币+券的数量, 则提示充值
        if ( $product_fee > $total_coin ) {
            gp_core_redirect( gp_loggedin_user_domain() . 'recharge' );
            return;
        }

        if ( $user_ticket >= $product_fee ) { // 熊币大于等于商品费用，使用熊币支付
            $cb_fee = $product_fee;
            $tickets = gp_orders_get_tickets( $user_id );
            foreach( $tickets as $ticket ) {
                $used_fee = gp_orders_get_ticket_total_coin_for_user( $user_id, $ticket->id );
                $remaining = $ticket->fee - abs( $used_fee ); // 剩余的金币

                // 当该券剩余金币为0,则用下一个券
                if ( $remaining <= 0 )
                    continue;

                if ($cb_fee <= 0)
                    break;

                $desc = sprintf( __( 'Ticket《%1$s》%2$s - %3$s', "gampress-ext" ), $book->title, ( $chapter->order + 1 ), $chapter->title );

                if ( $cb_fee >= $remaining ) {
                    gp_orders_coin_add_bill(array(
                        'id' => 0,
                        'user_id' => $user_id,
                        'type' => GP_Orders_Coin_Bill::TICKET,
                        'fee' => -$remaining,
                        'order_id' => $args['order_id'],
                        'item_id'   => $ticket->id,
                        'description' => $desc . "$remaining/总$cb_fee"
                    ));
                    $cb_fee -= $remaining;
                } else {
                    gp_orders_coin_add_bill(array(
                        'id' => 0,
                        'user_id' => $user_id,
                        'type' => GP_Orders_Coin_Bill::TICKET,
                        'fee' => -$cb_fee,
                        'order_id' => $args['order_id'],
                        'item_id'   => $ticket->id,
                        'description' => $desc
                    ));

                    break;
                }
            }
            gp_orders_update_type( $args['order_id'], GP_Orders_Order::TICKET );
        } else if ( $user_coin >= $product_fee ) { // 金币大于等于商品费用，使用金币
            $desc = sprintf( __( 'Pay《%1$s》%2$s - %3$s', "gampress-ext" ), $book->title, ( $chapter->order + 1 ), $chapter->title );

            gp_orders_coin_add_bill(array(
                'id' => 0,
                'user_id' => $user_id,
                'type' => GP_Orders_Coin_Bill::PAY,
                'fee' => -$product_fee,
                'order_id' => $args['order_id'],
                'item_id'  => 0,
                'description' => $desc
            ));

            gp_orders_update_type( $args['order_id'], GP_Orders_Order::COIN );
        } else {
            gp_core_redirect( gp_loggedin_user_domain() . 'recharge' );
            return;
        }

        // 更新订单状态
        gp_orders_update_order_status( $args['order_id'], GP_ORDER_PAID, GP_ORDER_SUBMIT );
        gp_orders_update_pay_time( $args['order_id'], gp_core_current_time() );

        // 记录支付信息
        $first_pay_time = get_user_meta( $user_id, 'first_pay_time', true );
        if ( empty( $first_pay_time ) )
            update_user_meta( $user_id, 'first_pay_time', gp_core_current_time() );

        if ( !empty( $args['redirect'] ) )
            gp_core_redirect( $args['redirect'] );
    }
}