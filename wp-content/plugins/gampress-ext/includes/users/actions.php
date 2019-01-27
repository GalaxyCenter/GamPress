<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 18:21
 */

function gp_users_profile_save() {
//    if ( !gp_is_my_home() || !gp_is_user_profile() )
//        return false;
//
//    if ( $_SERVER['REQUEST_METHOD'] == 'GET' )
//        return false;
//
//    $user_id = gp_loggedin_user_id();
//
//    if ( get_user_meta( $user_id,  'name_updated') === true ) {
//        gp_core_redirect( gp_displayed_user_domain() . 'profile' );
//    }
//    $args = array(
//        'ID' => $user_id,
//        'display_name' => $_POST['nice_name']
//    );
//    wp_update_user( $args );
//    //update_user_meta( $user_id, 'phone', $_POST['phone'] );
//    update_user_meta( $user_id, 'name_updated', true );
//    gp_core_redirect( gp_displayed_user_domain() . 'profile' );


//    $user_id = gp_loggedin_user_id();
//    $name = isset( $_POST['name'] ) ? $_POST['name'] : '';
//    if ( !empty( $name ) ) {
//        $args = array(
//            'ID' => $user_id,
//            'display_name' => $name,
//            'fullname' => $name
//        );
//        wp_update_user( $args );
//        wp_cache_delete( 'gp_user_username_' . $user_id, 'gp' );
//        wp_cache_delete( 'gp_core_userdata_' . $user_id, 'gp' );
//        update_user_meta( $user_id, 'name_updated', true );
//    }
//
//    do_action( 'gp_users_profile_save' );
//    ajax_die( 0, 'success', false );
}
//add_action( 'gp_actions', 'gp_users_profile_save' );