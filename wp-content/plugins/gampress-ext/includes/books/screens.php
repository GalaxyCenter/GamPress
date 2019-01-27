<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/2
 * Time: 9:42
 */

function books_screen_index() {
    if ( gp_is_books_component() && ! gp_is_single_item() ) {
        gp_update_is_directory( true, 'books' );
        gp_core_load_template( 'books/index' );
    }
}
add_action( 'gp_screens', 'books_screen_index' );

function books_screen_book_home() {
    if ( gp_is_books_component() && !gp_is_current_action( 'chapters' ) && gp_is_single_item() ) {

        $action = gp_current_action();
        if ( $action == 'pub-activity' && !is_user_logged_in() ) {
            $book = gp_books_get_current_book();
            gp_core_redirect( '/login?redirect=' . gp_get_book_permalink( $book ) . 'pub-activity' );
            return false;
        }
        gp_core_load_template( 'books/single/home' );
    }

    return false;
}
add_action( 'gp_screens', 'books_screen_book_home' );

function books_screen_book_chapter_home() {
    if ( gp_is_books_component() && gp_is_current_action( 'chapters' ) && gp_is_single_item() ) {
        $is_vip_path = $is_simple_path = gp_action_variable( 0 );
        if ( gp_get_books_chapter_vip_slug() == $is_vip_path ) {
            $is_vip_path = true;
            $chapter_id = gp_action_variable(1);
        } else if ( gp_get_books_chapter_simple_slug() == $is_simple_path ) {
            $is_simple_path = true;
            $chapter_id = gp_action_variable(1);
        } else {
            $chapter_id = $is_vip_path;
        }

        $chapter_id = GP_Books_Chapter::chapter_exists( gp_books_get_current_book()->id, $chapter_id, 0 );

        if ( !empty( $chapter_id ) ) {
            $book = gp_books_get_current_book();
            $user_id = gp_loggedin_user_id();
            $chapter = gp_books_get_chapter( $chapter_id );

            gp_books_add_history( $user_id, $book->id, $chapter_id );
            //gp_books_add_bookmark( $user_id, $book->id, $chapter_id );

            $auto_create_order = ( 'true' == gp_books_user_is_auto_create_order( $user_id, $book->id ) ) || ( isset( $_GET['auto_create_order'] ) ? $_GET['auto_create_order'] : false );
            if ( gp_books_user_can_read( $user_id, $book->id, $chapter_id ) ) {
                gp_books_get_chapter_add_log( $chapter );
                if ( $is_simple_path === true ) {
                    gp_core_load_template( 'books/chapters/simple' );
                } else {
                    gp_core_load_template('books/chapters/home');
                }
            } else if ( !$auto_create_order ) {
                if ( $is_vip_path === true ) {
                    $product_fee = gp_get_chapter_coin( $chapter_id );
                    $user_coin = gp_orders_get_total_coin_for_user( $user_id );
                    $user_ticket = gp_orders_get_tickets_total_fee( $user_id );

                    // 当熊币或赠币某一个能支付的时候
                    if ( $product_fee <= $user_coin || $product_fee <= $user_ticket ) {
                        gp_core_load_template( 'books/chapters/charge' );
                    } else {
                        gp_core_load_template( 'books/chapters/charge' );
                    }
                } else {
                    // 如果是收费章节,但是url没有带vip则重定向到正确的地址
                    $chapter = gp_books_get_chapter( $chapter_id );
                    gp_core_redirect( gp_get_chapter_permalink( $chapter ) );
                    return;
                }
            } else {
                $product_fee = gp_get_chapter_coin( $chapter_id );
                $user_coin = gp_orders_get_total_coin_for_user( $user_id );
                $user_ticket = gp_orders_get_tickets_total_fee( $user_id );
                //$total_coin = $user_coin + $user_ticket;

                // 当熊币或赠币某一个能支付的时候
                if ( $product_fee <= $user_coin || $product_fee <= $user_ticket ) {
                    //// 查询是否已经下过订单
                    $order_id = GP_Orders_Order::get_order_id( $user_id, $book->id, $chapter_id );
                    $product_fee = $product_fee / 100;
                    if ( empty( $order_id ) ) {
                        $from = isset( $_GET['from'] ) ? $_GET['from'] : '';

                        $order_id = gp_orders_update_order( array(
                            'order_id'        => $order_id,
                            'product_id'      => $chapter_id,
                            'item_id'         => $book->id,
                            'user_id'         => $user_id,
                            'price'           => $product_fee,
                            'create_time'     => gp_core_current_time(),
                            'quantity'        => 1,
                            'total_fee'       => $product_fee,
                            'status'          => GP_ORDER_SUBMIT,
                            'type'            => GP_Orders_Order::BOOK,
                            'come_from'       => $from ) );

                        $pay = apply_filters( "gp_pays_adaixiong", false );
                        $pay->do_pay( array(
                            'order_id' => $order_id,
                            'product_name' => '支付阅读费用',
                            'product_fee' => $product_fee,
                            'redirect' => ''
                        ) );
                    } else {
                        $order = gp_orders_get_order( $order_id );
                        if ( $order->status != GP_ORDER_PAID ) {
                            $pay = apply_filters( "gp_pays_adaixiong", false );
                            $pay->do_pay( array(
                                'order_id' => $order_id,
                                'product_name' => '支付阅读费用',
                                'product_fee' => $product_fee,
                                'redirect' => ''
                            ) );
                        }
                    }
                    gp_books_get_chapter_add_log( $chapter );
                    gp_core_load_template( 'books/chapters/home' );
                } else {
                    gp_core_load_template( 'books/chapters/charge' );
                }
            }
        }
    }

    return false;
}
add_action( 'gp_screens', 'books_screen_book_chapter_home' );