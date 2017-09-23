<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/2
 * Time: 14:30
 */

function gp_activities_add_vote( $vote ) {
    gp_activities_add_likes( $vote->item_id, 1 );

    $act = gp_activities_get_activity( $vote->item_id );
    $group = 'gp_activities_' . $act->item_id;
    wp_cache_clean( $group );
}
add_action( 'gp_votes_add_vote', 'gp_activities_add_vote', 10, 1 );