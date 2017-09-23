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
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    $name = gp_action_variable(0);

    // 微薄不支持 回调有变参所以 需要吧callback 写入cookie
    if ( !empty( $callback ) )
        @setcookie( 'redirect', $callback, time() + 60 * 60 * 24, COOKIEPATH );

    if ( !empty( $tab ) )
        @setcookie( 'tab', $tab, time() + 60 * 60 * 24, COOKIEPATH );

    if ( empty( $name ) ) {
        gp_do_404();
        return false;
    }
    $oauth = apply_filters( "gp_sns_oauth_{$name}", false );
    $oauth->request_authorize_code( urlencode( $callback ) );
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

    if ( isset( $_COOKIE['from'] ) ) {
        $sns_user->from = $_COOKIE['from'];
    }

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

        update_user_meta( $cur_user->ID , $key_user_id, $sns_user->ID );

        if ( !empty( $sns_user->avatar ) )
            update_user_meta( $cur_user->ID , $key_user_avatar, $sns_user->avatar );

        $user_id = $cur_user->ID;
    } else {
        $user_login = $sns_name . '_' . $sns_user->ID;
        $oauth_user = get_user_by( 'login', $user_login );

        if( empty( $oauth_user ) ) {
            global $wpdb;

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
                'user_pass'             => $random_password
            );
            $user_id = wp_insert_user( $new_user );
            wp_signon( array( 'user_login' => $user_login, 'user_password' => $random_password, 'remember' => true ), false );
            wp_set_current_user( $oauth_user->ID, $user_login );

            update_user_meta( $user_id, $key_user_id,      $sns_user->ID );
            update_user_meta( $user_id, $key_user_avatar,  $sns_user->avatar );
            update_user_meta( $user_id, $key_user_referer, $sns_name );
            update_user_meta( $user_id, 'last_login',  gp_format_time( time() ) );

            global $wpdb;
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => $sns_user->from ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );


            do_action( 'gp_user_sign_up', $user_id );
        } else {
            wp_set_auth_cookie( $oauth_user->ID, true );
            wp_set_current_user( $oauth_user->ID, $user_login );

            $name_updated = gp_users_get_meta( $oauth_user->ID, 'name_updated', false );
            if ( !$name_updated && !empty( $sns_user->user_name ) ) {
                update_user_meta( $oauth_user->ID, $key_user_id, $sns_user->ID );
                update_user_meta( $oauth_user->ID, $key_user_avatar,  $sns_user->avatar );

                $args = array(
                    'ID' => $oauth_user->ID,
                    'display_name' => $sns_user->user_name,
                    'fullname' => $sns_user->user_name
                );
                wp_update_user( $args );
                wp_cache_delete( 'gp_user_username_' . $oauth_user->ID, 'gp' );
                wp_cache_delete( 'gp_core_userdata_' . $oauth_user->ID, 'gp' );
            }
            update_user_meta( $oauth_user->ID, 'last_login',  gp_format_time( time() ) );
            $user_id = $oauth_user->ID;
        }
    }
    $sns_user->user_id = $user_id;
    $redirect = apply_filters( 'sns_autologin_redirect', $redirect, $user_id );
    return $redirect;
}

function sns_action_oauth_is_subscribe() {
    if ( !gp_is_sns_component() || ! gp_is_current_action( 'wechat' ) || ! gp_action_variable_is(0, 'is_subscribe' ) )
        return false;

    $name = gp_current_action();
    if ( empty( $name ) ) {
        gp_do_404();
        return false;
    }
    $wechat = apply_filters( "gp_sns_oauth_{$name}", false );
    $is_subscribe = $wechat->is_subscribe( gp_sns_get_sns_user_id() );
    ajax_die( 0, '', array( 'is_subscribe' => $is_subscribe ) );
}
add_action( 'gp_actions', 'sns_action_oauth_is_subscribe' );

function sns_action_wechat_js_config() {
    if ( !gp_is_sns_component() || ! gp_is_current_action( 'wx_js_cfg' ) )
        return false;

    $link = $_GET['link'];

    $cnf = gp_get_sns_wechat_config( $link );
    ajax_die( 0, '', $cnf );
}
add_action( 'gp_actions', 'sns_action_wechat_js_config' );

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

function gp_sns_wechat_authorize_screens() {
    // 微信浏览器 不登陆 后台设置 部授权登录 来源不是本站的情况下 自动进行不授权登录
    if ( is_weixin_browser() && !is_user_logged_in() && gp_sns_wechat_is_unauthorize_login() && !http_referer_domain_is( gp_get_root_domain() ) ) {
        $baseUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $wechat = new GP_Sns_OAuth_Wechat();
        $wechat->request_authorize_code( $baseUrl, 'snsapi_base' );
        exit();
    }
}
add_action( 'gp_screens', 'gp_sns_wechat_authorize_screens', 1 );

function gp_sns_wechat_subscribe() {
    if ( !gp_is_sns_component() || ! gp_is_current_action( 'wechat_subscribe' ) )
        return false;

    $ws = new GP_Sns_Wechat_Subscribe();
    if ( !isset( $_GET['echostr'] ) ) {
        $val = $ws->process();
    } else {
        $val = $ws->check();
    }
    status_header(200);
    die( $val );
}
add_action( 'gp_actions', 'gp_sns_wechat_subscribe', 1 );