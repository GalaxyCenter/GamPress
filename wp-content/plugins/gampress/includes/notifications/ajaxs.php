<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/7/14
 * Time: 9:12
 */

function gp_ajax_notifications_get_notifications() {
    ajax_die( 0, '', false );
}
add_action( 'wp_ajax_nopriv_get_notifications', 'gp_ajax_notifications_get_notifications' );
add_action( 'wp_ajax_get_notifications', 'gp_ajax_notifications_get_notifications' );