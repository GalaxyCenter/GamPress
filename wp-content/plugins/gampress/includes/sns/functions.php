<?php
/**
 * GamPress Admin Sns Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage CoreAdministration
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_sns_get_sns_user_id( $user_id = false ) {
    $key_user_id = 'sns_user_id';
    if ( empty( $user_id ) )
        $user_id = gp_loggedin_user_id();
    return get_user_meta( $user_id, $key_user_id, true );
}

function gp_sns_signup_user( $sns_user, $sns_name ) {
    global $wpdb;
    $key_user_id = 'sns_user_id';
    $key_user_avatar = 'sns_user_avatar';
    $key_user_referer = 'referer';
    $user_login = $sns_name . '_' . $sns_user->ID;

    if ( empty( $sns_user->user_name ) ) {
        $display_name = $sns_user->user_name = __( 'Guest', 'gampress' );
    } else {
        $like  = $wpdb->esc_like( $sns_user->user_name ) . '%';

        $display_name = $wpdb->get_var( $wpdb->prepare("SELECT display_name FROM $wpdb->users WHERE display_name LIKE %s ORDER BY ID DESC", $like) );
        if ( empty( $display_name ) ) {
            $display_name = $sns_user->user_name;
        } else {
            $display_name = str_replace( $sns_user->user_name, '', $display_name );
            if ( empty( $display_name ) ) {
                $display_name = $sns_user->user_name . '1';
            } else {
                $idx = (int) $display_name;
                $idx ++;
                $display_name = $sns_user->user_name . $idx;
            }
        }
    }
    $random_password    = wp_generate_password( $length = 12, $include_standard_special_chars = false );

    $new_user = array(
        'user_login'            => $user_login,
        'display_name'          => $display_name,
        'user_nicename'         => $user_login,
        'user_activation_key'   => $sns_user->from,
        'user_pass'             => $random_password,
        'user_registered'       => gp_format_time( time() )
    );
    $user_id = wp_insert_user( $new_user );
    wp_signon( array( 'user_login' => $user_login, 'user_password' => $random_password, 'remember' => true ), false );
    wp_set_current_user( $user_id, $user_login );

    update_user_meta( $user_id, $key_user_id,      $sns_user->ID );
    update_user_meta( $user_id, $key_user_avatar,  $sns_user->avatar );
    update_user_meta( $user_id, $key_user_referer, $sns_name );
    update_user_meta( $user_id, 'unionid', $sns_user->unionid);
    update_user_meta( $user_id, 'last_login',  gp_format_time( time() ) );

    global $wpdb;
    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $sns_user->from ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );

    do_action( 'gp_user_sign_up', $user_id );
    return $user_id;
}