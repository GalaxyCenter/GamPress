<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/19
 * Time: 17:05
 */

function gp_votes_ajax_add_vote() {
    $user_id    = gp_loggedin_user_id();
    $item_id    = $_POST['item_id'];
    $type       = $_POST['type'];

    if ( $user_id == 0) {
      ajax_die( 1, '您未登录', '' );
    } else if ( !gp_votes_user_liked( $user_id, $item_id ) ) {
        $vote_id = gp_votes_add_vote( array(
            'user_id'       => $user_id,
            'item_id'       => $item_id,
            'type'          => $type
        ) );

        ajax_die( 0, '点赞成功', '' );
    } else {
        ajax_die( 2, '您已经点过赞', '' );
    }
}
add_action( 'wp_ajax_add_vote', 'gp_votes_ajax_add_vote' );