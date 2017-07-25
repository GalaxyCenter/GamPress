<?php
/**
 * GamPress Groups Actions.
 * 
 * ⊙▂⊙
 *
 * @package GamPress
 * @subpackage GroupsActions
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function sns_action_oauth() {
    if ( !gp_is_sns_component() || ! gp_is_current_action( 'oauth' ) )
        return false;

    $callback = isset( $_GET['callback'] ) ? $_GET['callback'] : '';
    $name = gp_action_variable(0);

    // 微薄不支持 回调有变参所以 需要吧callback 写入cookie
    if ( !empty( $callback ) )
        @setcookie( 'redirect', $callback, time() + 60 * 60 * 24, COOKIEPATH );

    if ( empty( $name ) ) {
        gp_do_404();
        return false;
    }
    $oauth = apply_filters( "gp_sns_oauth_{$name}", false );
    $oauth->request_authorize_code( $callback );
}
add_action( 'gp_actions', 'sns_action_oauth' );

function sns_action_subscribe_login() {
    if ( !gp_is_sns_component() || ! gp_is_current_action( 'subscribe_login' ) )
        return false;

    $sns = new GP_Sns_Wechat_Subscribe();
    $sns->openid = $_GET['openid'];
    $sns->request_access_token();
    $sns_user = $sns->get_user_info();

    $callback = sns_autologin( 'weichat', $sns_user );
    gp_core_redirect( $callback );
}
add_action( 'gp_actions', 'sns_action_subscribe_login' );

function sns_action_oauth_callback() {
    if ( !gp_is_sns_component() || ! gp_is_current_action( 'oauth_callback' ) )
        return false;

    $name = gp_action_variable(0);
    if ( empty( $name ) ) {
        gp_do_404();
        return false;
    }
    $oauth = apply_filters( "gp_sns_oauth_{$name}", false );
    $oauth->code = $_GET['code'];
    $oauth->request_access_token();

    $callback = isset( $_GET['callback'] ) ? $_GET['callback'] : '';
    $sns_user = $oauth->get_user_info();

    $callback = sns_autologin( $name, $sns_user, $callback  );
    if ( empty( $callback ) ) {
        if ( isset( $_COOKIE['redirect'] ) ) {
            $callback = stripslashes( $_COOKIE['redirect'] );
            @setcookie( 'redirect', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
        }

        if ( empty( $callback ) ) {
            $callback = gp_core_get_user_domain( $sns_user->user_id );
        }

    }
    gp_core_redirect( $callback );
}
add_action( 'gp_actions', 'sns_action_oauth_callback' );

function sns_autologin( $sns_name, &$sns_user, $redirect = false ) {
    $key_user_id = 'sns_user_id';
    $key_user_avatar = 'sns_user_avatar';
    $key_user_referer = 'referer';

    $user_id = false;
    if( is_user_logged_in() ) {
        $cur_user = wp_get_current_user();

        update_user_meta( $cur_user->ID , $key_user_id, $sns_user->user_id );

        if ( !empty( $sns_user->avatar ) )
            update_user_meta( $cur_user->ID , $key_user_avatar, $sns_user->avatar );

        $user_id = $cur_user->ID;
    } else {
        $user_login = $sns_name . '_' . $sns_user->ID;
        $oauth_user = get_user_by( 'login', $user_login );

        if( empty( $oauth_user ) ) {
            global $wpdb;

            $like  = $wpdb->esc_like( $sns_user->user_name ) . '%';

            $display_name = $wpdb->get_var( $wpdb->prepare("SELECT display_name FROM $wpdb->users WHERE display_name LIKE %s ORDER BY ID DESC", $like) );
            $display_name = str_replace( $sns_user->user_name, '', $display_name );
            if ( empty( $display_name ) ) {
                $display_name = $sns_user->user_name . '1';
            } else {
                $idx = (int) $display_name;
                $idx ++;
                $display_name = $sns_user->user_name . $idx;
            }
            $random_password    = wp_generate_password( $length = 12, $include_standard_special_chars = false );

            $new_user = array(
                'user_login'     => $user_login,
                'display_name'   => $display_name,
                'user_nicename'  => $display_name,
                'user_pass'      => $random_password
            );
            $user_id = wp_insert_user( $new_user );
            wp_signon( array( 'user_login' => $user_login, 'user_password' => $random_password ), false );

            update_user_meta( $user_id, $key_user_id,      $sns_user->ID );
            update_user_meta( $user_id, $key_user_avatar,  $sns_user->avatar );
            update_user_meta( $user_id, $key_user_referer, $sns_name );

            wp_set_auth_cookie( $user_id, true );
            do_action( 'gp_user_sign_up', $user_id );
        } else {
            wp_set_auth_cookie( $oauth_user->ID, true );

            $user_id = $oauth_user->ID;
        }
    }
    $sns_user->user_id = $user_id;
    $redirect = apply_filters( 'sns_autologin', $redirect, $user_id );
    return $redirect;
}

// 暂时取消微信自动登录
//function sns_action_autologin() {
//    if ( is_weixin_browser() && !is_user_logged_in() ) {
//        $cur_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//        $cur_url = urlencode( $cur_url );
//        $oauth_url = gp_core_get_root_domain() . '/sns/oauth/wechat?callback=' . $cur_url;
//        gp_core_redirect( $oauth_url );
//    }
//}
//add_action( 'gp_actions', 'sns_action_autologin' );