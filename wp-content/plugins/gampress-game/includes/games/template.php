<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 14:27
 */

function gp_games_activity_name( $activity ) {
    echo gp_games_get_activity_name( $activity );
}
    function gp_games_get_activity_name( $activity ) {
        return $activity->name;
    }

function gp_games_activity_start_time( $activity ) {
    echo gp_games_get_activity_start_time( $activity );
}
    function gp_games_get_activity_start_time( $activity ) {
        return $activity->start_time;
    }

function gp_games_activity_expired( $activity ) {
    echo gp_games_get_activity_expired( $activity );
}
    function gp_games_get_activity_expired( $activity ) {
        return $activity->expired;
    }

function gp_games_activity_status( $activity ) {
    echo gp_games_get_activity_status( $activity );
}
    function gp_games_get_activity_status( $activity ) {
        return $activity->status;
    }

function gp_games_activity_type( $activity ) {
    echo gp_games_get_activity_type( $activity );
}
    function gp_games_get_activity_type( $activity ) {
        return $activity->type;
    }


function gp_is_games_component() {
    return (bool) gp_is_current_component( 'games' );
}