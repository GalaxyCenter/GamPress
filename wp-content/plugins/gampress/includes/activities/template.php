<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/2
 * Time: 14:31
 */

function gp_activities_activity_content( $activity ) {
    echo gp_get_activities_activity_content( $activity );
}

    function gp_get_activities_activity_content( $activity ) {
        return $activity->content;
    }

function gp_activities_activity_likes( $activity ) {
    echo gp_get_activities_activity_likes( $activity );
}

    function gp_get_activities_activity_likes( $activity ) {
        return $activity->likes;
    }

function gp_activities_activity_author( $activity ) {
    echo gp_get_activities_activity_author( $activity );
}

    function gp_get_activities_activity_author( $activity ) {
        return gp_core_get_user_displayname( $activity->user_id );
    }

function gp_activities_activity_post_time( $activity ) {
    echo gp_get_activities_activity_post_time( $activity );
}

    function gp_get_activities_activity_post_time( $activity ) {
        return gp_format_time( $activity->post_time );
    }