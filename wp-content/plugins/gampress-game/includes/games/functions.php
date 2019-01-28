<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/21
 * Time: 16:56
 */

/************* activity ********************/
function gp_games_get_activities( $args ) {
    if ( empty( $args ) )
        return false;

    $key = 'gp_game_activities_' . join( '_', $args );
    $group = 'gp_game_activities';

    $datas = wp_cache_get( $key, $group );
    if ( empty( $datas ) ) {
        $datas = GP_Games_Activity::get( $args );

        if ( !empty( $datas['items'] ) )
            wp_cache_set( $key, $datas, $group, 3600 );
    }
    return $datas;
}

function gp_games_get_activity( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }

    $key = 'gp_game_activity_' . $id;
    $group = 'gp_game_activity_' . $id;

    $activity = wp_cache_get( $key, $group );
    if ( empty( $activity ) ) {
        $activity = new GP_Games_Activity( $id );
        if ( !empty( $activity ) ) wp_cache_set( $key, $activity, $group, 3600 );
    }

    return $activity;
}

function gp_games_update_activity( $args ) {
    if ( ! gp_is_active( 'games' ) ) {
        return false;
    }

    $defaults = array( 'id' => false ,
        'name'              => false,
        'parent_id'         => false,
        'owner_id'          => gp_loggedin_user_id(),
        'create_time'       => gp_core_current_time(),
        'start_time'        => false,
        'expired'           => false,
        'status'            => false,
        'type'               => false ) ;

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty( $name ))
        return false;

    if ( !empty( $id ) ) {
        $activity = gp_games_get_activity( $id );
    } else {
        $activity = new GP_Games_Activity();
        $activity->id = 0;
    }

    $activity->parent_id    = $parent_id;
    $activity->owner_id     = $owner_id;
    $activity->name         = $name;
    $activity->create_time  = $create_time;
    $activity->start_time   = $start_time;
    $activity->expired      = $expired;
    $activity->type         = $type;
    $activity->status       = $status;

    if ( !$activity->save() )
        return $activity;

    $group = 'gp_game_activity_' . $id;
    wp_cache_clean( $group );

    wp_cache_set( 'gp_game_activity_' . $activity->id, $activity, $group, 3600 );

    return $activity->id;
}

function gp_games_activities_get_meta( $activity_id = 0, $meta_key = '', $single = true ) {
    add_filter( 'query', 'gp_filter_metaid_column_name' );
    $retval = get_metadata( 'games_activity', $activity_id, $meta_key, $single );
    remove_filter( 'query', 'gp_filter_metaid_column_name' );
    return $retval;
}

function gp_games_activities_update_meta( $activity_id, $meta_key, $meta_value, $prev_value = '' ) {
    add_filter( 'query', 'gp_filter_metaid_column_name' );
    $retval = update_metadata( 'games_activity', $activity_id, $meta_key, $meta_value, $prev_value );
    remove_filter( 'query', 'gp_filter_metaid_column_name' );
    return $retval;
}

function gp_games_activities_add_meta( $activity_id, $meta_key, $meta_value, $unique = false ) {
    add_filter( 'query', 'gp_filter_metaid_column_name' );
    $retval = add_metadata( 'games_activity', $activity_id, $meta_key, $meta_value, $unique );
    remove_filter( 'query', 'gp_filter_metaid_column_name' );
    return $retval;
}

/************* group ********************/
function gp_games_update_group( $args ) {
    if ( ! gp_is_active( 'games' ) ) {
        return false;
    }

    $defaults = array( 'id' => false ,
        'activity_id'       => false,
        'name'              => false,
        'owner_id'          => gp_loggedin_user_id(),
        'date_created'      => gp_core_current_time() ) ;

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty( $name ))
        return false;

    if ( !empty( $id ) ) {
        $group = gp_games_get_group( $id );
    } else {
        $group = new GP_Games_group();
        $group->id = 0;
    }

    $group->activity_id          = $activity_id;
    $group->name                 = $name;
    $group->owner_id             = $owner_id;
    $group->date_created         = $date_created;

    if ( !$group->save() )
        return $group;

    $group_key = 'gp_games_groups_user_group_' . $owner_id;
    wp_cache_clean( $group_key );

    $group_key = 'gp_games_groups_' . $owner_id;
    wp_cache_clean( $group_key );

    wp_cache_set( 'gp_game_group_' . $group->id, $group, $group_key, 3600 );

    return $group->id;
}

/** 获取用户创建 组的数量 */
function gp_games_groups_get_total_count( $user_id, $activity_id ) {
    $key = 'gp_games_groups_user_total_count_' . $user_id . '_' . $activity_id;
    $group = 'gp_games_groups_user_group_' . $user_id;

    $count = wp_cache_get( $key, $group );
    if ( empty( $count ) ) {
        $count = GP_Games_Group::get_count( $user_id, $activity_id );

        if ( !empty( $count ) ) wp_cache_set( $key, $count, $group );
    }
    return $count;
}

function gp_games_groups_get_groups( $args = '' ) {
    $defaults = array(
        'activity_id'        => false,
        'order'              => 'DESC',
        'orderby'            => 'date_created',
        'owner_id'           => null,
        'per_page'           => 20,
        'page'               => 1,
    );

    $r = gp_parse_args( $args, $defaults, 'gp_games_groups_get_groups' );

    $key = 'gp_games_groups_' . join( '_', $args );
    $group = 'gp_games_groups_' . $args['owner_id'];

    $datas = wp_cache_get( $key, $group );
    if ( empty( $datas ) ) {
        $datas = GP_Games_Group::get( array(
            'activity_id'        => $r['activity_id'],
            'owner_id'           => $r['owner_id'],
            'per_page'           => $r['per_page'],
            'page'               => $r['page'],
            'order'              => $r['order'],
            'orderby'            => $r['orderby'],
        ) );

        if ( !empty( $datas['items'] ) ) wp_cache_set( $key, $datas, $group );
    }

    return $datas;
}

function gp_games_groups_get_group( $id ) {
    if ( ! is_numeric( $id ) || empty( $id ) ) {
        return false;
    }

    $key = 'gp_game_group_' . $id;
    $group_key = 'gp_game_group_' . $id;

    $group = wp_cache_get( $key, $group_key );
    if ( empty( $group ) ) {
        $group = new GP_Games_Group( $id );
        if ( !empty( $group ) ) wp_cache_set( $key, $group, $group_key, 3600 );
    }

    return $group;
}
/******************** Group Member ********************/

/**
 * 获取用户加入组的数量
 * @param $activity_id
 * @param $user_id
 */
function gp_games_groups_get_user_join_count( $user_id, $activity_id ) {
    $key = 'gp_games_groups_user_join_count_' . $user_id . '_' . $activity_id;
    $group = 'gp_games_groups_user_' . $user_id;

    $count = wp_cache_get( $key, $group );
    if ( empty( $count ) ) {
        $count = GP_Games_Group_Members::get_count_for_user( $user_id, $activity_id );

        if ( !empty( $count ) ) wp_cache_set( $key, $count, $group );
    }
    return $count;
}

/**
 * 获取该组的用户数量
 * @param $group_id
 * @return mixed
 */
function gp_games_groups_get_total_member_count( $group_id ) {
    $key = 'gp_games_group_members_' . $group_id;
    $group = 'gp_games_group_' . $group_id;

    $count = wp_cache_get( $key, $group );
    if ( empty( $count ) ) {
        $count = GP_Games_Group_Members::get_total_member_count( $group_id );

        if ( !empty( $count ) ) wp_cache_set( $key, $count, $group );
    }
    return $count;
}

/**
 * 判断用户是否存在该组
 * @param $group_id
 * @param $user_id
 */
function gp_games_groups_is_user_member( $activity_id, $group_id, $user_id ) {
    $key = 'gp_games_groups_is_user_member_' . $activity_id . '_' . $group_id . '_' . $user_id;
    $group = 'gp_games_group_' . $group_id;

    $val = wp_cache_get( $key, $group );
    if ( empty( $val ) ) {
        $val = GP_Games_Group_Members::is_user_member( $activity_id, $group_id, $user_id  );

        if ( !empty( $val ) ) wp_cache_set( $key, $val, $group );
    }
    return $val;
}

/**
 * 判断用户是否该组管理员
 * @param $user_id
 * @param $group_id
 * @return bool
 */
function gp_games_groups_is_user_admin( $user_id, $group_id ) {
    $is_admin = false;

    return $is_admin;
}

/**
 * 将用户加入指定组
 * @param $group_id
 * @param int $user_id
 */
function gp_games_groups_join_group( $activity_id, $group_id, $user_id, $inviter_id ) {
    $new_member                = new GP_Games_Group_Members();
    $new_member->activity_id   = $activity_id;
    $new_member->group_id      = $group_id;
    $new_member->user_id       = $user_id;
    $new_member->inviter_id    = $inviter_id;
    $new_member->is_admin      = 0;
    $new_member->date_modified = gp_core_current_time();

    if ( !$new_member->save() )
        return false;

    $group = 'gp_games_groups_user_' . $user_id;
    wp_cache_clean( $group );

    $group = 'gp_games_group_' . $group_id;
    wp_cache_clean( $group );

    return true;
}

/**
 * 获取用户加入的组
 * @param $user_id
 * @param array $args
 */
function gp_games_get_user_groups( $user_id, $args = array() ) {
}

function gp_games_get_group_members( $args = array() ) {
    $key = 'gp_games_group_members_' . join( '_', $args );
    $group = 'gp_games_group_' . $args['group_id'];

    $datas = wp_cache_get( $key, $group );
    if ( empty( $datas ) ) {
        $datas = GP_Games_Group_Members::get( $args );

        if ( !empty( $datas['items'] ) ) wp_cache_set( $key, $datas, $group );
    }
    return $datas;
}

// 签到
function gp_games_user_checkin( $user_id ) {
    if ( empty( $user_id ) )
        return false;

    global $wpdb;
    $id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ds_gp_checkins WHERE user_id = %d AND to_days(now()) - to_days(last_date) = 1', $user_id, gp_format_time( time() ) ) );
    if ( is_numeric( $id ) ) {
        $wpdb->query( $wpdb->prepare( "UPDATE ds_gp_checkins SET last_date = now() WHERE id = %d", $id ) );
    } else {
        $wpdb->query( $wpdb->prepare( 'INSERT INTO ds_gp_checkins ( user_id, first_date, last_date ) values( %d, now(), now() )', $user_id ) );
    }

    do_action( 'gp_games_user_checkin', $user_id );
}

function gp_games_user_is_checkin( $user_id ) {
    global $wpdb;

    $id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ds_gp_checkins WHERE user_id = %d AND to_days(last_date) = to_days(%s)', $user_id, gp_core_current_time() ) );

    return is_numeric( $id );
}