<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/2
 * Time: 14:31
 */

function gp_activities_get_activities( $args = '' ) {
    if ( empty( $args ) )
        return false;

    $key = 'gp_activities_' . join( '_', $args );
    $datas = wp_cache_get( $key );
    if ( empty( $datas ) ) {
        $datas = GP_Activities_Activity::get( $args );

        if ( !empty( $datas['items'] ) )
            wp_cache_set( $key, $datas );
    }
    return $datas;
}

function gp_activities_get_activity( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }

    $key = 'gp_ex_book_' . $id;

    $activity = wp_cache_get( $key );
    if ( empty( $activity ) ) {
        $activity = new GP_Activities_Activity( $id );
        if ( !empty( $activity ) ) wp_cache_set( $key, $activity );
    }

    return $activity;
}

function gp_activities_add( $args = '' ) {
    if ( ! gp_is_active( 'activities' ) ) {
        return false;
    }

    $defaults = array( 'id' => false ,
        'user_id'       => false,
        'component'     => false,
        'type'          => false,
        'content'       => false,
        'item_id'       => false,
        'parent_id'     => false,
        'post_time'     => time() ) ;

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty( $user_id ) || empty( $component ) || empty( $type )
        || empty( $content ) || empty( $item_id ) )
        return false;

    if ( !empty( $id ) ) {
        $activity = gp_activities_get_activity( $id );
    } else {
        $activity = new GP_Activities_Activity();
        $activity->id = $id;
    }

    $activity->user_id      = $user_id;
    $activity->component    = $component;
    $activity->content      = $content;
    $activity->item_id      = $item_id;
    $activity->parent_id    = $parent_id;
    $activity->post_time    = $post_time;
    $activity->type         = $type;

    if ( !$activity->save() )
        return $activity;

    wp_cache_set( 'gp_activity_' . $activity->id, $activity, 'gp_activities' );

    return $activity->id;
}

function gp_activities_get_meta( $activity_id = 0, $meta_key = '', $single = true ) {
    $retval = get_metadata( 'activity', $activity_id, $meta_key, $single );
    return $retval;
}

function gp_activities_update_meta( $activity_id, $meta_key, $meta_value, $prev_value = '' ) {
    $retval = update_metadata( 'activity', $activity_id, $meta_key, $meta_value, $prev_value );
    return $retval;
}

function gp_activities_add_meta( $activity_id, $meta_key, $meta_value, $unique = false ) {
    $retval = add_metadata( 'activity', $activity_id, $meta_key, $meta_value, $unique );
    return $retval;
}

function gp_activities_add_likes( $activity_id, $count = 1 ) {
    global $wpdb;

    $gp = gampress();
    $wpdb->query( $wpdb->prepare( "UPDATE {$gp->activities->table_name_activities} SET likes = likes + %d WHERE id = %d", $count, $activity_id ) );
}

function gp_activities_user_is_liked( $user_id, $activity_id ) {
    $meta_key = 'activity_like_' . $user_id;

    return !empty( gp_activities_get_meta( $activity_id, $meta_key ) );
}
