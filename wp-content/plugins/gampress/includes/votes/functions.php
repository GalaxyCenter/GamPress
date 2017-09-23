<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/19
 * Time: 17:03
 */
function gp_votes_add_vote( $args = '' ) {
    if ( empty( $args ) )
        return false;

    $defaults = array(
        'item_id'           => 0,
        'user_id'           => 0,
        'type'              => GP_Votes_Vote::$VOTE,
        'post_time'         => time()
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    $vote = new GP_Votes_Vote();
    $vote->item_id             = $item_id;
    $vote->user_id             = $user_id;
    $vote->type                = $type;
    $vote->post_time           = $post_time;

    if ( !$vote->save() )
        return $vote;

    do_action( 'gp_votes_add_vote', $vote );

    return $vote->id;
}

function gp_votes_user_liked( $user_id, $item_id ) {
    if ( empty( $user_id ) )
        return false;

    $count = GP_Votes_Vote::user_vote_count( $user_id, $item_id );
    return $count != 0;
}