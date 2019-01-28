<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/11/8
 * Time: 19:53
 */

/** 关注公号用户领取券 */
function gp_games_ticket_subscribe() {
    if (!gp_is_games_component() || !gp_is_current_action('ticket' )
        || !gp_action_variable_is(0, 'subscribe' ) )
        return false;

    gp_core_load_template( 'games/ticket' );
}
add_action( 'gp_screens', 'gp_games_ticket_subscribe' );