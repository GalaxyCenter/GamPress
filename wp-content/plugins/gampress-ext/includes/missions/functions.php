<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/19
 * Time: 18:32
 */

function gp_missions_update_mission( $args = '' ) {
    if ( empty( $args ) )
        return false;

    $defaults = array(
        'id'                => 0,
        'user_id'           => 0,
        'custom_name'       => 0,
        'phone'             => 0,
        'card'              => 0,
        'remark'            => 0,
        'post_time'         => 0,
        'update_time'       => 0,
        'status'            => '',
        'gender'            => 0
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    if ( !empty( $id ) ) {
        $mission = gp_missions_get_mission( $id );
    } else {
        $mission = new GP_Orders_Order();
        $mission->id = $id;
    }

    $mission->id               = $id;
    $mission->user_id          = $user_id;
    $mission->custom_name      = $custom_name;
    $mission->phone            = $phone;
    $mission->card             = $card;
    $mission->remark           = $remark;
    $mission->post_time        = $post_time;
    $mission->update_time      = $update_time;
    $mission->status           = $status;
    $mission->gender           = $gender;

    if ( !$mission->save() )
        return $mission;
    return $mission;
}

function gp_missions_get_mission( $id ) {
    if ( empty( $id ) )
        return false;

    if ( !$mission = wp_cache_get( 'mission_' . $id, 'gp' ) ) {

        $mission = new GP_Missions_Mission( $id );
        wp_cache_set( 'mission_' . $id, $mission, 'gp' );
    }

    return $mission;
}

function gp_missions_get_missions ( $args = '' ) {
    if ( empty( $args ) )
        return false;

    $missions = GP_Missions_Mission::get( $args );
    return $missions;
}