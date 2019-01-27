<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/2
 * Time: 9:42
 */

function gp_books_get_books( $args = '' ) {
    if ( empty( $args ) )
        return false;

    $key = 'gp_ex_books_' . json_encode( $args ); //join( '_', $args );
    $group = 'gp_ex_books';

    $datas = wp_cache_get( $key, $group );
    if ( empty( $datas ) ) {
        $datas = GP_Books_Book::get( $args );

        if ( !empty( $datas['items'] ) )
            wp_cache_set( $key, $datas, $group );
    }
    return $datas;
}

function gp_books_get_book( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }

    $key = 'gp_ex_book_' . $id;
    $group = 'gp_ex_book_group_' . $id;

    $book = wp_cache_get( $key, $group );
    if ( empty( $book ) ) {
        $book = new GP_Books_Book( $id );
        if ( !empty( $book ) ) wp_cache_set( $key, $book, $group, 3600 );
    }

    return $book;
}

function gp_books_update_book( $args ) {
    if ( ! gp_is_active( 'books' ) ) {
        return false;
    }

    $defaults = array( 'id' => false ,
        'title'             => false,
        'author'            => false,
        'author_id'         => false,
        'description'       => false,
        'summary'           => false,
        'tags'              => false,
        'refer'             => false,
        'chapter_type'      => false,
        'charge_type'       => false,
        'point'             => false,
        'charge_order'      => false,
        'cover'             => false,
        'bookmarks'         => false,
        'status'            => false ) ;

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty( $title ) || empty( $description ) )
        return false;

    if ( !empty( $id ) ) {
        $book = gp_books_get_book( $id );
    } else {
        $book = new GP_Books_Book();
        $book->id = $id;
    }

    $book->id           = $id;
    $book->title        = $title;
    $book->author       = $author;
    $book->author_id    = $author_id;
    $book->description  = $description;
    $book->summary      = $summary;
    $book->chapter_type = $chapter_type;
    $book->refer        = $refer;
    $book->tags         = $tags;
    $book->charge_type  = $charge_type;
    $book->point        = $point;
    $book->charge_order = $charge_order;
    $book->cover        = $cover;
    $book->status       = $status;
    $book->bookmarks    = $bookmarks;

    if ( !$book->save() )
        return $book;

    if ( !empty( $author_id ) ) {
        $user = new WP_User( $author_id );
        $user->set_role( 'author' );
    }
    $group = 'gp_ex_book_group_' . $id;
    wp_cache_clean( $group );
    wp_cache_clean( 'gp_ex_books' );

    wp_cache_set( 'gp_book_' . $book->id, $book, $group, 3600 );

    return $book->id;
}

function gp_books_book_show($id ) {
    GP_Books_Book::reset_status( $id, GP_BOOK_HIDE );
    $group = 'gp_ex_book_group_' . $id;
    wp_cache_clean( $group );

    $group = 'gp_ex_books';
    wp_cache_clean( $group );
}

function gp_books_book_hide($id ) {
    GP_Books_Book::update_status( $id, GP_BOOK_HIDE );
    $group = 'gp_ex_book_group_' . $id;
    wp_cache_clean( $group );

    $group = 'gp_ex_books';
    wp_cache_clean( $group );
}

function gp_books_book_finish( $id ) {
    GP_Books_Book::update_status( $id, GP_BOOK_FINISH );
    $group = 'gp_ex_book_group_' . $id;
    wp_cache_clean( $group );

    $group = 'gp_ex_books';
    wp_cache_clean( $group );
}

function gp_books_book_seriating( $id ) {
    GP_Books_Book::update_status( $id, GP_BOOK_SERIATING );
    $group = 'gp_ex_book_group_' . $id;
    wp_cache_clean( $group );

    $group = 'gp_ex_books';
    wp_cache_clean( $group );
}

function gp_books_book_recommend( $id, $term_slug = false ) {
    $book = gp_books_get_book( $id );
    $post_type = gp_get_book_recommend_post_type();

    $postarr = array(
        'post_parent'               => $id,
        'post_title'                => gp_get_book_title( $book ),
        'post_status'               => 'publish',
        'post_type'                 => $post_type,
    );

    if ( !empty( $term_slug ) ) {
        $term = get_term_by( 'slug', $term_slug, gp_get_book_recommend_post_taxonomy() );
        $postarr['tax_input'] = array( gp_get_book_recommend_post_taxonomy() => array( $term->term_id ) );
    }
    wp_insert_post( $postarr );

    // 将所有内容下线
    global $wpdb;
    $wpdb->query( $wpdb->prepare("update ds_posts p 
                        join ds_term_relationships tr on tr.object_id = p.ID
                        join ds_terms t on t.term_id = tr.term_taxonomy_id
                        set p.post_status='Private' 
                        where t.`name` = %s", $post_type) );
    $counts = array('首页焦点图' => 1, '重磅推荐' => 5, '热门精选' => 4, '免费推荐' => 8, '大神佳作' => 4, '金牌全本' => 8);
    $count = $counts[$post_type];

    $args = array( 'post_type' => gp_get_book_recommend_post_type(),
        'posts_per_page' => $count,
        'tax_query' => array(
            array(
                'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                'terms'    => $post_type,
                'field'    => 'name'
            )
        ) );
    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) {
        $wpdb->query( $wpdb->prepare("update ds_posts p set p.post_status='publish' where p.ID = %d", $loop->post->ID ) );
    }
}

function gp_books_book_free( $id ) {
    gp_books_add_free( $id );
}

function gp_books_book_unfree( $id ) {
    gp_books_remove_free( $id );
}

/**
 *
 *
 * Chapter
 *
 *
 *
 */
function gp_books_get_first_chapter( $book_id ) {
    $key = 'gp_ex_book_chapter_first_' . $book_id;
    $group = 'gp_ex_book_group_' . $book_id;

    $chapter = wp_cache_get( $key, $group );
    if ( empty( $chapter ) ) {
        $datas = gp_books_get_chapters( array(
            'book_id'        => $book_id,
            'orderby'        => 'id',
            'order'          => 'ASC',
            'status'         => GP_CHAPTER_NORMAL,
            'page'           => 1,
            'per_page'       => 2 ) );

        if ( empty( $datas['items'] ) )
            return false;

        $chapter = $datas['items'][0];
        if ( $chapter->parent_id == 0 && $chapter->words == 0 ) {
            $chapter = $datas['items'][1];
        }
        wp_cache_set( $key, $chapter, $group, 500 );
    }
    return $chapter;
}

function gp_books_get_last_chapter( $book_id, $status = GP_CHAPTER_NORMAL ) {
    $key = 'gp_ex_book_chapter_last_' . $book_id . '_' . $status;
    $group = 'gp_ex_book_group_' . $book_id;

    $chapter = wp_cache_get( $key, $group );
    if ( empty( $chapter ) ) {
        $datas = gp_books_get_chapters( array(
            'book_id'        => $book_id,
            'orderby'        => 'id',
            'order'          => 'DESC',
            'status'         => $status,
            'page'           => 1,
            'per_page'       => 1 ) );

        if ( empty( $datas['items'] ) )
            return false;

        $chapter = $datas['items'][0];
        wp_cache_set( $key, $chapter, $group, 500 );
    }

    return $chapter;
}

function gp_books_get_volumes( $book_id ) {
    $key = 'gp_ex_books_volumes_' . $book_id;
    $group = 'gp_ex_book_group_' . $book_id;

    $volumes = wp_cache_get( $key, $group );

    if ( empty( $volumes ) ) {
        $volumes = GP_Books_Chapter::get_volumes( $book_id );
        if ( !empty( $volumes ) ) wp_cache_set( $key, $volumes, $group, 3600 );
    }
    return $volumes;
}

function gp_books_get_chapters( $args ) {
    if ( empty( $args ) )
        return false;

    $key = 'gp_ex_chapters_' . join( '_', $args );
    $group = 'gp_ex_book_group_' . $args['book_id'];

    $datas = wp_cache_get( $key, $group );
    if ( empty( $datas ) ) {
        $datas = GP_Books_Chapter::get( $args );

        if ( !empty( $datas['items'] ) )
            wp_cache_set( $key, $datas, $group, 500 );
    }
    return $datas;
}

function gp_books_get_chapter_by_order( $book_id, $order ) {
    $key = 'gp_ex_chapter_' . $book_id . '_' . $order;
    $group = 'gp_ex_book_group_' . $book_id;

    $chapter = wp_cache_get( $key, $group );
    if ( empty( $chapter ) ) {
        $chapter_id = GP_Books_Chapter::chapter_order_exists( $book_id, $order );
        $chapter = gp_books_get_chapter( $chapter_id );
        if ( !empty( $chapter ) ) wp_cache_set( $key, $chapter, $group, 500 );
    }

    return $chapter;
}

function gp_books_get_chapter( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }

    $key = 'gp_ex_chapter_' . $id;
    $chapter = wp_cache_get( $key );
    if ( empty( $combine ) ) {
        $chapter = new GP_Books_Chapter( $id );
        if ( !empty( $chapter ) ) wp_cache_set( $key, $chapter );
    }

    return $chapter;
}

function gp_books_update_chapter( $args ) {
    if ( ! gp_is_active( 'books' ) ) {
        return false;
    }

    $defaults = array( 'id' => false,
        'title'             => false,
        'body'              => false,
        'is_charge'         => false,
        'approved_time'     => false,
        'refer'             => false,
        'order'             => false,
        'book_id'           => false,
        'parent_id'         => false) ;

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty( $title ) || empty( $body ) )
        return false;

    if ( !empty( $id ) ) {
        $chapter = gp_books_get_chapter( $id );
    } else {
        $chapter = new GP_Books_Chapter();
        $chapter->id = $id;
    }
    $body = gp_books_chapter_format_body( $body );
    $chapter->id             = $id;
    $chapter->title          = $title;
    $chapter->body           = $body;
    $chapter->is_charge      = $is_charge;
    $chapter->words          = get_words( $body );
    $chapter->refer          = empty( $refer ) ? $chapter->refer : $refer;
    $chapter->update_time    = time();
    $chapter->approved_time  = empty( $approved_time ) ? $chapter->approved_time : $approved_time;
    $chapter->order          = empty( $order ) ? $chapter->order : $order;
    $chapter->parent_id      = empty( $parent_id ) ? $chapter->parent_id : $parent_id;

    if ( !$chapter->save() )
        return $chapter;

    wp_cache_set( 'gp_chapter_' . $chapter->id, $chapter, 'gp_chapters' );

    $group = 'gp_ex_book_group_' . $chapter->book_id;
    wp_cache_clean( $group );

    $group = 'gp_ex_book_group_0';
    wp_cache_clean( $group );

    return $chapter->id;
}

function gp_books_chapter_format_body( $body ) {
    $body = preg_replace("/　| |	/", "", $body);
    $body = "　　" . preg_replace("/\r\n|\n/", "\r\n　　", $body);

    return $body;
}
/** Book Meta ****************************************************************/

function gp_books_delete_bookmeta( $book_id, $meta_key = false, $meta_value = false, $delete_all = false ) {
    global $wpdb;
    $gp    = gampress();

    // Legacy - if no meta_key is passed, delete all for the item.
    if ( empty( $meta_key ) ) {
        $keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$gp->books->table_name_bookmeta} WHERE book_id = %d", $book_id ) );

        // With no meta_key, ignore $delete_all.
        $delete_all = false;
    } else {
        $keys = array( $meta_key );
    }

    $retval = true;
    foreach ( $keys as $key ) {
        $retval = delete_metadata( 'book', $book_id, $key, $meta_value, $delete_all );
    }

    return $retval;
}

function gp_books_bookmeta( $book_id, $meta_key = '', $single = true ) {
     echo gp_books_get_bookmeta( $book_id, $meta_key, $single );
}

function gp_books_get_bookmeta( $book_id, $meta_key = '', $single = true ) {
    $retval = get_metadata( 'book', $book_id, $meta_key, $single );
    return $retval;
}

function gp_books_update_bookmeta( $book_id, $meta_key, $meta_value, $prev_value = '' ) {
    $retval = update_metadata( 'book', $book_id, $meta_key, $meta_value, $prev_value );
    return $retval;
}

function gp_books_add_bookmeta( $book_id, $meta_key, $meta_value, $unique = false ) {
    $retval = add_metadata( 'book', $book_id, $meta_key, $meta_value, $unique );
    return $retval;
}

/**
 *
 *
 * Bookmarks
 *
 *
 *
 */
function gp_books_get_bookmark( $id ) {
    return new GP_Books_Bookmark( $id );
}

/**
 * 获取被加入收藏的图书的数量
 * @param $book_id
 */
function gp_books_get_bookmark_count( $book_id ) {
    $key = 'gp_ex_bookmarks_count_' . GP_Books_Bookmark::BOOKMARKS . '_' . $book_id;
    $group = 'gp_ex_group';

    $count = wp_cache_get( $key, $group );
    if ( empty( $count ) ) {
        $count = GP_Books_Bookmark::bookmark_count( $book_id, GP_Books_Bookmark::BOOKMARKS );
        $book = gp_books_get_book( $book_id );
        $count = $book->bookmarks + $count;
        wp_cache_set( $key, $count, $group, 3600 );
    }
    return $count;
}

/**
 * 获取阅读记录数
 * @param $book_id
 */
function gp_books_get_history_count( $book_id ) {
    $key = 'gp_ex_bookmarks_count_' . GP_Books_Bookmark::HISTORY;
    $group = 'gp_ex_group';

    $count = wp_cache_get( $key, $group );
    if ( empty( $count ) ) {
        $count = GP_Books_Bookmark::bookmark_count( $book_id, GP_Books_Bookmark::HISTORY );
        wp_cache_set( $key, $count, $group, 3600 );
    }
    return $count;
}

function gp_books_get_bookmarks( $args ) {
    if ( empty( $args ) )
        return false;

    $key = 'gp_ex_bookmarks_' . join( '_', $args );
    $group = 'gp_ex_bookmarks_group_' . $args['user_id'] . $args['type'];
    $datas = wp_cache_get( $key, $group );
    if ( empty( $datas ) ) {
        $datas = GP_Books_Bookmark::get( $args );

        if ( !empty( $datas['items'] ) )
            wp_cache_set( $key, $datas, $group, 3600 );
    }
    return $datas;
}

/**
 * 主动记录(阅读历史)
 * @param $user_id
 * @param $book_id
 * @param $chapter_id
 */
function gp_books_add_history( $user_id, $book_id, $chapter_id ) {
    if ( empty( $user_id ) || empty( $book_id ) )
        return false;

    $bm = new GP_Books_Bookmark();
    $bm->user_id = $user_id;
    $bm->type = GP_Books_Bookmark::HISTORY;
    $bm->book_id = $book_id;
    $bm->chapter_id = $chapter_id;
    $bm->post_time = time();

    $bm2 = GP_Books_Bookmark::exists( $user_id, GP_Books_Bookmark::HISTORY, $book_id );
    if ( !empty( $bm2 ) ) {
        $bm->id = $bm2->id;
    }
    $bm->save();

    $group = 'gp_ex_bookmarks_group_' . $user_id . GP_Books_Bookmark::HISTORY;
    wp_cache_clean( $group );
}

/**
 * 被动记录(追书)
 * @param $user_id
 * @param $book_id
 * @param $chapter_id
 */
function gp_books_add_bookmark( $user_id, $book_id, $chapter_id ) {
    if ( empty( $user_id ) || empty( $book_id ) )
        return false;

    $bm = new GP_Books_Bookmark();
    $bm->user_id = $user_id;
    $bm->type = GP_Books_Bookmark::BOOKMARKS;
    $bm->book_id = $book_id;
    $bm->chapter_id = $chapter_id;
    $bm->post_time = time();
    $bm2 = GP_Books_Bookmark::exists( $user_id, GP_Books_Bookmark::BOOKMARKS, $book_id );
    if ( !empty( $bm2 ) ) {
        $bm->id = $bm2->id;
    }
    $bm->save();

    $group = 'gp_ex_bookmarks_group_' . $user_id . GP_Books_Bookmark::BOOKMARKS;
    wp_cache_clean( $group );
}

function gp_books_user_is_mark( $user_id, $book_id ) {
    if ( $user_id == 0 || $book_id == 0 )
        return false;
    return GP_Books_Bookmark::exists( $user_id, GP_Books_Bookmark::BOOKMARKS, $book_id );
}

function gp_books_user_is_readed( $user_id, $book_id ) {
    if ( $user_id == 0 || $book_id == 0 )
        return false;
    return GP_Books_Bookmark::exists( $user_id, GP_Books_Bookmark::HISTORY, $book_id );
}

function gp_books_get_top_bookmark( $mondate, $count ) {
    $cache_key = 'books_top_bookmark_' . $mondate . '_' . $count;

    $items = wp_cache_get( $cache_key, 'books' );
    if ( empty( $items ) ) {
        $items = GP_Books_Bookmark::get_top(GP_Books_Bookmark::BOOKMARKS, $mondate, $count);
        foreach ($items as $item) {
            $item->book = gp_books_get_book($item->book_id);
        }

        wp_cache_set( $cache_key, $items, 'books', 86400 );
    }
    return $items;
}

function gp_books_user_can_read( $user_id, $book_id, $chapter_id ) {
    if ( gp_books_is_free( $book_id ) )
        return true;

    $book = gp_books_get_book( $book_id );
    if ( !empty( $book->author_id ) && $book->author_id == $user_id )
        return true;

    $chapter = gp_books_get_chapter( $chapter_id );
    if ( !$chapter->is_charge )
        return true;

    if ( $user_id == 0 )
        return false;

//    $datas = gp_orders_get_orders( array(
//        'user_id'           => $user_id,
//        'product_id'        => $chapter_id,
//        'item_id'           => $book_id,
//        'status'            => GP_ORDER_PAID
//    ) );
//
//    return is_array( $datas ) && !empty( $datas['total'] );

    $order_id = GP_Orders_Order::get_order_id( $user_id, $book_id, $chapter_id, GP_ORDER_PAID );
    return !empty( $order_id );
}

/**
 *
 * Book log
 *
 */
function gp_books_add_log( $user_id, $book_id, $chapter_id, $from = '' ) {
    if ( !GP_Books_log::exists( $user_id, $book_id, $chapter_id ) ) {
        $log = new GP_Books_log();
        $log->user_id = $user_id;
        $log->book_id = $book_id;
        $log->chapter_id = $chapter_id;
        $log->create_time = gp_format_time( time() );
        $log->from = $from;

        $log->save();
    }
}

function gp_books_get_top_views( $mondate, $count ) {
    $cache_key = 'books_top_views_' . $mondate . '_' . $count;

    $items = wp_cache_get( $cache_key, 'books' );
    if ( empty( $items ) ) {
        $items = GP_Books_log::get_top( $mondate, $count );
        foreach ( $items as $item ) {
            $item->book = gp_books_get_book( $item->book_id );
        }

        wp_cache_set( $cache_key, $items, 'books', 86400 );
    }

    return $items;
}

function gp_books_get_chapter_add_log( $chapter ) {
    $user_id = gp_loggedin_user_id();
    if ( empty( $user_id ) ) {
        $user_id = isset( $_COOKIE['guest_id'] ) ? $_COOKIE['guest_id'] : '';
        if ( empty( $user_id ) ) {
            $gp = gampress();

            $user_id = isset( $gp->cache['guest_id'] ) ? $gp->cache['guest_id'] : uniqid( 'guest_' );
            $gp->cache['guest_id'] = $user_id;
        }

        @setcookie( 'guest_id', $user_id, time() + 60 * 60 * 24, COOKIEPATH );
    }

    $from = '';
    $from_key = $chapter->book_id . '_from';
    if ( isset( $_GET['from'] ) ) {
        $from = $_GET['from'];
        @setcookie( $from_key, $from, time() + 60000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
    } else if ( isset( $_COOKIE[ $from_key ] ) ) {
        $from = isset( $_COOKIE[ $from_key ] );
    }
    gp_books_add_log( $user_id, $chapter->book_id, $chapter->id, $from );

    return $chapter;
}
/**
 *
 * BookFree
 *
 */
function gp_books_is_free( $book_id ) {
    $free = gp_books_get_current_free();
    if ( empty( $free ) )
        return false;

    $bids = explode( ',', $free->book_ids );
    return in_array( $book_id, $bids );
}

function gp_books_get_frees( $args = '' ) {
    if ( empty( $args ) )
        return false;

    $key = 'gp_ex_bookfrees_' . join( '_', $args );
    $group = 'gp_ex_bookfrees';

    $datas = wp_cache_get( $key, $group );
    if ( empty( $datas ) ) {
        $datas = GP_Books_Book_Free::get( $args );

        if ( !empty( $datas['items'] ) )
            wp_cache_set( $key, $datas, $group, 3600 );
    }
    return $datas;
}

function gp_books_get_free( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }

    $key = 'gp_ex_book_fee_' . $id;

    $free = wp_cache_get( $key, 'gp_ex_bookfrees' );
    if ( empty( $free ) ) {
        $free = new GP_Books_Book_Free( $id );
        if ( !empty( $id ) ) wp_cache_set( $key, $free, 'gp_ex_bookfrees', 3600 );
    }

    return $free;
}

function gp_books_get_current_free() {
    $cur_date = date("Y-m-d");
    $cache_key = 'gp_ex_book_fee_' . $cur_date;
    $free = wp_cache_get( $cache_key );
    if ( empty( $free ) ) {
        $free = GP_Books_Book_Free::get_by_date( $cur_date );

        wp_cache_set( $cache_key, $free, 'gp_ex_bookfrees', 7200 );
    }
    return $free;
}

function gp_books_update_book_free( $args ) {

    $defaults = array( 'id' => false ,
        'name'              => false,
        'book_ids'          => false,
        'start_time'        => false,
        'end_time'          => false ) ;

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty( $name ) )
        return false;

    if ( !empty( $id ) ) {
        $free = gp_books_get_free( $id );
    } else {
        $free = new GP_Books_Book_Free();
        $free->id = $id;
    }

    $free->id          = $id;
    $free->name        = $name;
    $free->book_ids    = $book_ids;
    $free->start_time  = $start_time;
    $free->end_time    = $end_time;


    if ( !$free->save() )
        return $free;

    $key = 'gp_ex_book_fee_' . $free->id;
    wp_cache_clean( 'gp_ex_bookfrees' );
    wp_cache_set( $key, $free, 'gp_ex_bookfrees', 3600 );
    return $free->id;
}

function gp_books_user_is_auto_create_order( $user_id, $book_id ) {
    $meta_key = 'auto_create_order_' . $book_id;
    return gp_users_get_meta( $user_id, $meta_key, -1, true );
}

function gp_books_user_update_auto_create_order( $user_id, $book_id, $value ) {
    $meta_key = 'auto_create_order_' . $book_id;
    update_user_meta( $user_id, $meta_key, $value );
}

function gp_books_word2coin( $words ) {
    return ceil( $words / 1000 * 5 );
}

/**
 * 猜你喜欢
 * @param $user_id
 */
function gp_books_auto_recommend( $user_id ) {
//    $datas = gp_books_get_bookmarks( array(
//        'user_id'       => $user_id,
//        'type'          => 'history',
//        'page_index'    => 1,
//        'page_size'     => 6
//    ) );
//
//    if ( !empty( $datas ) && !empty( $datas['items'] ) ) {
//        global $wpdb;
//
//        $gp = gampress();
//
//        $book_id = $datas['items'][0]->book_id;
//        $term_ids = array();
//
//        $terms = gp_get_object_terms( $book_id, 'book_library' );
//        foreach ( $terms as $term ) {
//            if ( $term->parent != 0 )
//                $term_ids[] = $term->term_id;
//        }
//        $term_ids = join( ',', $term_ids );
//
//        $cache_key = 'books_auto_remcommend_' . $user_id . '_' . $term_ids;
//        $items = wp_cache_get( $cache_key, 'orders' );
//        if ( !empty( $term_ids ) ) {
//            $items = $wpdb->get_results( "SELECT b.*
//                                            FROM {$gp->books->table_name_book} b
//                                            JOIN {$wpdb->term_relationships} r ON r.object_id = b.id
//                                            JOIN `ds_gp_orders` o ON o.`item_id` = b.id
//                                            WHERE r.term_taxonomy_id IN ({$term_ids})
//                                            AND o.`type` = 'book'
//                                            AND TO_DAYS( NOW( ) ) - TO_DAYS(o.`create_time`)  <= 1
//                                            GROUP BY o.`item_id`
//                                            ORDER BY price desc
//                                            LIMIT 12" );
//
//            if ( count( $items ) > 0 )
//                wp_cache_set( $cache_key, $items, 'orders' );
//        }
//    }
//
//    if ( count( $items ) != 12 ) {
//        $args = array( 'post_type' => gp_get_book_recommend_post_type(),
//            'posts_per_page' => 12 - count( $items ),
//            'tax_query' => array(
//                array(
//                    'taxonomy' => gp_get_book_recommend_post_taxonomy(),
//                    'terms'    => '猜你喜欢',
//                    'field'    => 'name'
//                )
//            ) );
//        $loop = new WP_Query( $args );
//        while ( $loop->have_posts() ) {
//            $loop->the_post();
//            $items[] = gp_books_get_book( $loop->post->post_parent );
//        }
//    }
//    return $items;

    $datas = gp_books_get_bookmarks( array(
        'user_id'       => $user_id,
        'type'          => 'history',
        'page_index'    => 1,
        'page_size'     => 1
    ) );
    $term_name = '';
    if ( !empty( $datas ) && !empty( $datas['items'] ) ) {
        global $wpdb;

        $book_id = $datas['items'][0]->book_id;
        $terms = gp_get_object_terms( $book_id, 'book_library' );
        foreach ( $terms as $term ) {
            if ( $term->parent == 0 )
                $term_name = $term->name;
        }
    }

    if ( empty( $term_name ) ) {
        $terms = '猜你喜欢-女';
    } else if ( '女频' == $term_name ) {
        $terms = '猜你喜欢-女';
    } else {
        $terms = '猜你喜欢-男';
    }

    $args = array( 'post_type' => gp_get_book_recommend_post_type(),
        'posts_per_page' => 12,
        'tax_query' => array(
            array(
                'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                'terms'    => $terms,
                'field'    => 'name'
            )
        ) );
    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) {
        $loop->the_post();
        $items[] = gp_books_get_book( $loop->post->post_parent );
    }
    return $items;
}

function gp_books_get_book_history( $user_id ) {
    $datas = gp_books_get_bookmarks( array(
        'user_id'       => $user_id,
        'type'          => 'history',
        'page_index'    => 1,
        'page_size'     => 1
    ) );

    if ( count( $datas['items'] ) > 0 ) {
        $item = $datas['items'][0];
        $title = gp_get_book_title( $item->book );

        $next_chapter = gp_books_get_chapter_by_order( $item->book->id, $item->chapter->order + 1 );
        $link = gp_get_chapter_permalink( $next_chapter );
        $order_text = gp_get_chapter_order( $next_chapter, true );

                return array( 'link' => $link, 'title' => $title, 'order_text' => $order_text );
    } else {
        return array();
    }
}