<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/11/23
 * Time: 17:56
 */

function gp_messages_ajax_get_messages() {
    $page_index     = isset( $_GET['page_index'] ) ? $_GET['page_index'] : false;
    $page_size = isset( $_GET['page_size'] ) ? $_GET['page_size'] : 20;
    $threads = GP_Messages_Thread::get_current_threads_for_user( array(
        'user_id'      => gp_loggedin_user_id(),
        'box'          => 'inbox',
        'type'         => 'all',
        'limit'        => $page_size,
        'page'         => $page_index,
        'search_terms' => false,
        'meta_query'   => false
    ) );

    $msgs = array();
    foreach ( $threads['threads'] as $thread ) {
          $thread_info = $thread->messages[0];
        $thread_info->read = empty( $thread->unread_count ) ;
        $msgs[] = $thread_info;
    }
    ajax_die( 0, '', $msgs );
}
add_action( 'wp_ajax_get_messages', 'gp_messages_ajax_get_messages' );

function gp_messages_ajax_mark_thread_read() {
    $thread_id     = isset( $_GET['thread_id'] ) ? $_GET['thread_id'] : false;

    gp_messages_mark_thread_read($thread_id);

    ajax_die( 0, '', "" );
}
add_action( 'wp_ajax_mark_thread_read', 'gp_messages_ajax_mark_thread_read' );

function gp_messages_ajax_unread_messages_count() {
    ajax_die( 0, '', gp_get_total_unread_messages_count() );
}
add_action( 'wp_ajax_unread_messages_count', 'gp_messages_ajax_unread_messages_count' );