<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/19
 * Time: 18:51
 */

function users_mission_save() {
    if ( !gp_is_my_home() || !gp_is_user_missions() )
        return false;

    if ( IS_GET )
        return false;

    $id = gp_action_variable(0);
    if ( !empty( $id ) ) {
        $mission = gp_get_current_mission();
        if ( $mission->status != 1 )
            return false;
    }

    $user_id = gp_displayed_user_id();
    $args = array(
        'id'                => $id,
        'user_id'           => $user_id,
        'custom_name'       => $_POST['custom_name'],
        'phone'             => $_POST['phone'],
        'remark'            => $_POST['remark'],
        'card'              => $_POST['card'],
        'post_time'         => gp_core_current_time(),
        'update_time'       => gp_core_current_time(),
        'gender'            => $_POST['gender'],
        'status'            => $_POST['status']
    );
    gp_missions_update_mission( $args );
    gp_core_redirect( gp_displayed_user_domain() . 'missions/list' );
}
add_action( 'gp_actions', 'users_mission_save' );