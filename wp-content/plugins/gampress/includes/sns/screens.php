<?php
/**
 * GamPress Member Screens.
 * 
 * ⊙▂⊙
 *
 * Handlers for member screens that aren't handled elsewhere.
 *
 * @package GamPress
 * @subpackage SnsScreens
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function sns_subscribe_token() {

    if ( !gp_is_sns_component() || !gp_is_current_action( 'sub_token_callback' ) )
        return false;


    header( 'HTTP/1.1 200 OK', true );
    define( 'DOING_AJAX', true );


    $gws = new GP_Sns_Wechat_Subscribe();
    $body = '';
    if ( !isset( $_GET['echostr'] ) ) {
        $body = $gws->process();
    } else {
        $gws->token = gp_get_option( 'gp_sns_app_token', '' );
        $body = $gws->check();
    }
    wp_die( $body );
}
add_action( 'gp_screens', 'sns_subscribe_token' );