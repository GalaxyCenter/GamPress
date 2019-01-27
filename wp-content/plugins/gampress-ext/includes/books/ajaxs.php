<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/6
 * Time: 21:23
 */

function gp_books_ajax_get_books() {
    $search_terms   = isset( $_POST['search_terms'] ) ? $_POST['search_terms'] : false;
    $term_name      = isset( $_POST['term_name'] ) ? $_POST['term_name'] : false;
    $order_by       = isset( $_POST['order_by'] ) ? $_POST['order_by'] : false;
    $words_query    = isset( $_POST['words_query'] ) ? $_POST['words_query'] : false;
    $page_index     = isset( $_POST['page_index'] ) ? $_POST['page_index'] : false;
    $page_size      = isset( $_POST['page_size'] ) ? $_POST['page_size'] : false;
    $charge_type    = isset( $_POST['charge_type'] ) ? $_POST['charge_type'] : false;
    $author_id        = isset( $_POST['author_id'] ) ? $_POST['author_id'] : false;

    if ( empty( $term_name ) ) {
        $term_id = false;
    } else {
        $term = get_term_by( 'name', $term_name, 'book_library' );
        $term_id = $term->term_id;
    }

    if ( $words_query == 'words-g30w' )  {
        $words_query = array( 'value' => 300000, 'compare' => '>=' );
    } elseif ( $words_query == 'words-g50w' )  {
        $words_query = array( 'value' => 500000, 'compare' => '>=' );
    } elseif ( $words_query == 'words-g100w' )  {
        $words_query = array( 'value' => 1000000, 'compare' => '>=' );
    } elseif ( $words_query == 'words-l30w' )  {
        $words_query = array( 'value' => 300000, 'compare' => '<=' );
    }

    $datas = gp_books_get_books( array(
        'search_terms'   => $search_terms,
        'orderby'        => $order_by,
        'order'          => 'DESC',
        'status'         => GP_BOOK_SERIATING | GP_BOOK_FINISH,
        'term_ids'       => $term_id,
        'author_id'      => $author_id,
        'words_query'    => $words_query,
        'charge_type'    => $charge_type,
        'page'           => $page_index,
        'per_page'       => $page_size ) );

    foreach ( $datas['items'] as $item ) {
        $item->link = gp_get_book_permalink( $item );
        $item->status_text = gp_get_book_status( $item, true );
        $item->description = mb_substr( gp_get_book_description( $item ), 0, 26 );
        $item->cover = gp_get_book_cover( $item );

        if ( !empty( $search_terms ) ) {
            $item->title =  preg_replace("/{$search_terms}/i", "<em class='font-red'>{$search_terms}</em>", $item->title );
            $item->author = preg_replace("/{$search_terms}/i", "<em class='font-red'>{$search_terms}</em>", $item->author );
        }
    }
    ajax_die( 0, '', $datas );
}
add_action( 'wp_ajax_nopriv_get_books', 'gp_books_ajax_get_books' );
add_action( 'wp_ajax_get_books', 'gp_books_ajax_get_books' );

//function gp_books_ajax_get_chapter() {
//    $book_name = $_POST['book'];
//    $book_id = GP_Books_Book::book_exists( $book_name );
//    $book = gp_books_get_book( $book_id );
//
//    $order = $_POST['order'];
//    $chapter = gp_books_get_chapter_by_order( $book_id, $order );
//    $user_id = gp_loggedin_user_id();
//
//    if ( $chapter->is_charge && $user_id == 0 ) {
//        ajax_die( 1, '收费章节,需要登录才能阅读', '/login?redirect=' . gp_get_chapter_permalink( $chapter ) );
//        return;
//    }
//
//    gp_books_add_history( gp_loggedin_user_id(), $book->id, $chapter_id );
//
//    $auto_create_order = get_user_meta( $user_id, 'auto_create_order', true ) || isset( $_GET['auto_create_order'] ) ? $_GET['auto_create_order'] : false;
//
//    if ( gp_books_user_can_read( $user_id, $book->id, $chapter_id ) ) {
//        // 可以阅读,返回章节数据
//        ajax_die( 0, '', $chapter );
//    } else if ( !$auto_create_order ) {
//        // 设置了不自动扣费
//        ajax_die( 2, '收费章节,需要支付费用才能阅读', '' );
//    } else {
//        $product_fee = 0.15;
//        $total_coin = gp_orders_get_total_coin_for_user($user_id);
//
//        if ($product_fee <= $total_coin) {
//            $order_id = gp_orders_update_order(array(
//                'order_id' => 0,
//                'product_id' => $chapter_id,
//                'item_id' => $book->id,
//                'user_id' => $user_id,
//                'price' => $product_fee,
//                'create_time' => gp_core_current_time(),
//                'quantity' => 1,
//                'total_fee' => $product_fee,
//                'status' => GP_ORDER_SUBMIT));
//
//            $pay = apply_filters("gp_pays_adaixiong", false);
//            $pay->do_pay($order_id, "支付阅读费用", $product_fee, '');
//
//            // 扣费成功, 返回章节数据
//            ajax_die( 0, '', $chapter );
//        } else {
//            ajax_die( 3, '余额不足,需要充值', '' );
//        }
//    }
//}
//add_action( 'wp_ajax_nopriv_get_chapter', 'gp_books_ajax_get_chapter' );
//add_action( 'wp_ajax_get_chapter', 'gp_books_ajax_get_chapter' );

function gp_books_ajax_get_user_bookmark() {
    $page_index         = $_POST['page_index'];
    $page_size          = $_POST['page_size'];
    $type               = $_POST['type'];
    $order_by           = $_POST['order_by'];

    $datas = gp_books_get_bookmarks( array(
        'user_id'       => gp_loggedin_user_id(),
        'type'          => $type,
        'orderby'       => $order_by,
        'page'          => $page_index,
        'per_page'      => $page_size
    ) );

    if ( $page_index == 1 && ( empty( $datas ) || empty( $datas['items'] ) ) ) {
        $args = array( 'post_type' => gp_get_book_recommend_post_type(),
            'posts_per_page' => 6,
            'tax_query' => array(
                array(
                    'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                    'terms'    => '免费推荐',
                    'field'    => 'name'
                )
            ) );
        $loop = new WP_Query( $args );
        $datas['items'] = array();
        while ( $loop->have_posts() ) {
            $loop->the_post();
            $item = new stdClass();
            $item->rid = 1;
            $item->book = gp_books_get_book($loop->post->post_parent);
            $datas['items'][] = $item;
        }
    }

    foreach ( $datas['items'] as $item ) {
        if ( $item->rid == 1) {
            $item->book->link = gp_get_book_permalink( $item->book );
        } else {
            if ( empty( $item->chapter_id ) ) {
                $item->book->link = gp_get_book_permalink( $item->book );
            } else {
                $chapter = gp_books_get_chapter( $item->chapter_id );
                $item->book->link = gp_get_chapter_permalink( $chapter );
            }

        }
        $item->book->cover = gp_get_book_cover( $item->book );
    }

    ajax_die( 0, '', $datas );
}
add_action( 'wp_ajax_get_user_bookmark', 'gp_books_ajax_get_user_bookmark' );

function gp_books_ajax_add_bookmark() {
    $book_id = $_POST['book_id'];
    $user_id = gp_loggedin_user_id();
    $chapter_id = 0;

    gp_books_add_bookmark( $user_id, $book_id, $chapter_id );
    ajax_die( 0, '成功追书', '' );
}
add_action( 'wp_ajax_add_bookmark', 'gp_books_ajax_add_bookmark' );

function gp_books_ajax_auto_create_order() {
    $auto_create_order = $_POST['auto_pay'];
    $book_id = $_POST['book_id'];
    $user_id = gp_loggedin_user_id();

    gp_books_user_update_auto_create_order( $user_id, $book_id, $auto_create_order );
    ajax_die( 0, '', '' );
}
add_action( 'wp_ajax_update_auto_create_order', 'gp_books_ajax_auto_create_order' );

function gp_books_ajax_get_bookmark_count() {
    $book_id = $_POST['book_id'];
    $count = gp_books_get_bookmark_count( $book_id );
    ajax_die( 0, '', $count );
}
add_action( 'wp_ajax_nopriv_get_bookmark_count', 'gp_books_ajax_get_bookmark_count' );
add_action( 'wp_ajax_get_bookmark_count', 'gp_books_ajax_get_bookmark_count' );