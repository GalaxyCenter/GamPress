<?php
/**
 * Created by PhpStorm.
 * User: bourne
 * Date: 2017/4/2
 * Time: 下午9:25
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

function gp_books_get_book_actions( $args = array(), $output = 'names', $operator = 'and' ) {
    $actions = array(
        __( 'Normal',    'gampress-ext' ) => 1 | 2,
        __( 'Seriating',    'gampress-ext' ) => 1,
        __( 'Finish',       'gampress-ext' ) => 2,
        __( 'Hide',     'gampress-ext' ) => 4,
        __( 'Freeing',      'gampress-ext' ) => 'free',
    );

    return $actions;
}

function gp_books_get_book_words_query_actions( $args = array(), $output = 'names', $operator = 'and' ) {
    $actions = array(
        __( 'Words < 30w',  'gampress-ext' ) => 'words-l30w',
        __( 'Words > 30w',  'gampress-ext' ) => 'words-g30w',
        __( 'Words > 50w',  'gampress-ext' ) => 'words-g50w',
        __( 'Words > 100w',  'gampress-ext' ) => 'words-g100w',
    );

    return $actions;
}

function gp_books_get_chapter_filters( $args = array(), $output = 'names', $operator = 'and' ) {
    $filters = array(
        __( 'NORMAL',    'gampress-ext' ) => GP_CHAPTER_NORMAL,
        __( 'UNAPPROVED',       'gampress-ext' ) => GP_CHAPTER_UNAPPROVED,
    );

    return $filters;
}

function gp_books_admin_import_book( $content, $book_id = 0 ) {
    $datas = explode('####', $content);
    if (count($datas) == 0)
        return;

    if ( empty( $book_id ) ) {
        $order = 0;
    } else {
        $last_chapter = gp_books_get_last_chapter( $book_id, GP_CHAPTER_ALL );
        if ( !empty( $last_chapter ) )
            $order = $last_chapter->order;
        else
            $order = 0;
    }
    $diff = 0;
    for ($i = 0; $i < count($datas); $i++) {
        $cnt = $datas[$i];
        if ( empty( $cnt ) )
            continue;

        if (mb_substr($cnt, 0, 2) == '##') {
            $title = mb_substr($cnt, 2);
            $title = str_replace( "\r\n", '', $title );

            $book_id = GP_Books_Book::book_exists( $title ) ;
            if ( empty( $book_id ) ) {
                $book = new GP_Books_Book();
                $book->title = $title;
                $book->author_id = 0;
                $book->author= $title;
                $book->summary = $title;
                $book->description = $title;
                $book->refer = '';
                $book->status = 0;
                $book->words = 0;
                $book->charge_type = 0;
                $book->point = 0;
                $book->level = 0;
                $book->tags = '';
                $book->cover = '';
                $book->chapter_type = 0;
                $book->post_time = time();
                $book->save();
                $book_id = $book->id;
            }
            $diff = 1;
        } else {
            if ( empty( $book_id ) ) {
                return new WP_Error( 'book not found', __( 'Book Not Found', 'gampress-ext' ) );
            }

            $pos = mb_strpos( $cnt, "\r\n" );
            $title = mb_substr($cnt, 0, $pos);
            $body = mb_substr($cnt, $pos + 2);
            $body = gp_books_chapter_format_body( $body );

            if ( !GP_Books_Chapter::chapter_exists( $book_id, $title ) ) {
                $book = gp_books_get_book( $book_id );
                $chapter = new GP_Books_Chapter();
                $chapter->book_id = $book_id;
                $chapter->title = $title;
                $chapter->body = $body;
                $chapter->words = get_words( $body );
                $chapter->refer = 'adaixiong_' . $book_id;
                $chapter->order = $i + $order - $diff;
                $chapter->is_charge = ( $chapter->order + 1) >= $book->charge_order ? 1 : 0;
                $chapter->status = GP_CHAPTER_NORMAL;
                $chapter->post_time = $chapter->approved_time = $chapter->update_time = time();
                $chapter->save();

                $group = 'gp_ex_book_group_' . $book_id;
                wp_cache_clean( $group );

                $group = 'gp_ex_book_group_0';
                wp_cache_clean( $group );

                wp_cache_delete( 'gp_ex_book_chapter_last_' . $book_id );
            }
        }
    }

    return $book_id;
}

function gp_books_admin_update_words( $book_id ) {
    global $wpdb;
    $gp = gampress();

    $words = (int)$wpdb->get_var( $wpdb->prepare( "SELECT sum(words) FROM {$gp->books->table_name_book_chapter} WHERE book_id = %s AND status = 0", $book_id ) );

    $sql = $wpdb->prepare( "UPDATE {$gp->books->table_name_book} SET words = %d, status = 1 WHERE id = %d", $words, $book_id );
    $wpdb->query( $sql );
    $group = 'gp_ex_book_group_' . $book_id;
    wp_cache_clean( $group );
}

function gp_books_admin_get_refer_api_url( $refer ) {
    if ( empty( $refer ) )
        return '';

    $info = explode( '_',$refer );
    return 'http://book.tianya.cn/book3g/outreadapi/tybasic.jsp?comefrom=126881098&op=getchaplist&bookid=' . $info[1];
}

/** chapters */


function gp_books_admin_update_charge_order( $book_id, $charge_order ) {
    global $wpdb;
    $gp = gampress();

    $wpdb->query( "UPDATE {$gp->books->table_name_book_chapter} SET `is_charge` = 0 WHERE book_id = {$book_id}" );
    $charge_order--;
    $wpdb->query( "UPDATE {$gp->books->table_name_book_chapter} SET `is_charge` = 1 WHERE book_id = {$book_id} AND `order` >= {$charge_order}" );

    $group = 'gp_ex_book_group_' . $book_id;
    wp_cache_clean( $group );

    $group = 'gp_ex_book_group_0';
    wp_cache_clean( $group );
}


function gp_books_chapter_set_charge( $id, $is_charge )  {
    global $wpdb;
    $gp = gampress();

    $wpdb->query( "UPDATE {$gp->books->table_name_book_chapter} SET `is_charge` = {$is_charge} WHERE id = {$id}" );
    $chapter = gp_books_get_chapter( $id );
    $group = 'gp_ex_book_group_' . $chapter->book_id;
    wp_cache_clean( $group );

    $group = 'gp_ex_book_group_0';
    wp_cache_clean( $group );
}

function gp_books_chapter_update_status( $id, $status )  {
    global $wpdb;
    $gp = gampress();

    $wpdb->query( "UPDATE {$gp->books->table_name_book_chapter} SET `status` = {$status} WHERE id = {$id}" );

    $chapter = gp_books_get_chapter( $id );
    $group = 'gp_ex_book_group_' . $chapter->book_id;
    wp_cache_clean( $group );

    $group = 'gp_ex_book_group_0';
    wp_cache_clean( $group );

    $chapter = gp_books_get_chapter( $id );
    gp_books_admin_update_words( $chapter->book_id );
}

function gp_books_chapter_update_body( $id, $body )  {
    global $wpdb;
    $gp = gampress();

    $sql = $wpdb->prepare( "UPDATE {$gp->books->table_name_book_chapter} SET `body` = %s WHERE id = %d", $body, $id );
    $wpdb->query( $sql );

    $chapter = gp_books_get_chapter( $id );
    $group = 'gp_ex_book_group_' . $chapter->book_id;
    wp_cache_clean( $group );

    $chapter = gp_books_get_chapter( $id );
    gp_books_admin_update_words( $chapter->book_id );
}

/*** books free ***/
function gp_books_get_books_free_actions( $args = array(), $output = 'names', $operator = 'and' ) {
    $actions = array(
        __('Seriating', 'gampress-ext') => 1,
        __('Finish', 'gampress-ext') => 2);
    return $actions;
}

function gp_books_book_price( $book_id ) {
    global $wpdb;
    $gp = gampress();

    $words = (int) $wpdb->get_var( "SELECT sum(words) FROM {$gp->books->table_name_book_chapter} WHERE book_id = {$book_id} AND is_charge = 1" );
    $coin = gp_books_word2coin( $words );
    return $coin / 100;

}