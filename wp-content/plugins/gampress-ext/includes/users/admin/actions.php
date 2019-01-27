<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/28
 * Time: 11:16
 */

function gp_users_admin_column_add_displayname( $columns ) {
    $columns['display_name'] = __( 'Display Name', 'gampress-ext' );
    $columns['registered'] = __( 'Registered', 'gampress-ext' );
    if ( is_plugin_active( 'books' ) ) {
        $columns['consumption'] = __('Consumption', 'gampress-ext');
        $columns['recharge'] = __('Recharge', 'gampress-ext');
        $columns['last_chapter'] = __('Last Chapter', 'gampress-ext');
    }
    return $columns;
}
add_filter( 'manage_users_columns', 'gp_users_admin_column_add_displayname' );

function gp_users_manage_users_custom_column( $value, $column_name, $user_id ) {
    $value = '';

    $gp = gampress();
    global $wpdb;

    if ( $column_name == 'display_name' ) {
        $value = gp_core_get_user_displayname( $user_id );
    } else if ( $column_name == 'registered' ) {
        $user = get_userdata( $user_id );
        $value = $user->user_registered;
    } else if ( $column_name == 'consumption' ) {
        $value = $wpdb->get_var( $wpdb->prepare( "SELECT sum(price) as price FROM {$gp->orders->table_name} WHERE type = 'book' AND user_id = %d LIMIT 1", $user_id ) );
    } else if ( $column_name == 'recharge' ) {
        $value = $wpdb->get_var( $wpdb->prepare( "SELECT sum(price) as price FROM {$gp->orders->table_name} WHERE type = 'recharge' AND status = 4 AND user_id = %d LIMIT 1", $user_id ) );
    } else if ( $column_name == 'last_chapter' ) {
        $data = $wpdb->get_row( $wpdb->prepare( "SELECT book_id, chapter_id FROM {$gp->books->table_name_logs} WHERE user_id = %d ORDER BY id DESC LIMIT 1", $user_id ) );

        if ( !empty( $data ) ) {
            $book = gp_books_get_book( $data->book_id );
            $chapter = gp_books_get_chapter( $data->chapter_id );

            $value = $book->title . ' No.' . ( $chapter->order + 1) . ' ' . $chapter->title;
        }
    }
    return $value;
}
add_action( 'manage_users_custom_column', 'gp_users_manage_users_custom_column', 10, 3 );