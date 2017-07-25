<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/16
 * Time: 10:34
 */

defined( 'ABSPATH' ) || exit;

function gp_notifications_add_notification( $args = array() ) {

    $r = gp_parse_args( $args, array(
        'user_id'           => 0,
        'item_id'           => 0,
        'secondary_item_id' => 0,
        'component_name'    => '',
        'component_action'  => '',
        'date_notified'     => gp_core_current_time(),
        'is_new'            => 1,
        'allow_duplicate'   => false,
    ), 'notifications_add_notification' );

    // Check for existing duplicate notifications.
    if ( ! $r['allow_duplicate'] ) {
        // Date_notified, allow_duplicate don't count toward
        // duplicate status.
        $existing = GP_Notifications_Notification::get( array(
            'user_id'           => $r['user_id'],
            'item_id'           => $r['item_id'],
            'secondary_item_id' => $r['secondary_item_id'],
            'component_name'    => $r['component_name'],
            'component_action'  => $r['component_action'],
            'is_new'            => $r['is_new'],
        ) );

        if ( ! empty( $existing ) ) {
            return false;
        }
    }

    // Setup the new notification.
    $notification                    = new GP_Notifications_Notification;
    $notification->user_id           = $r['user_id'];
    $notification->item_id           = $r['item_id'];
    $notification->secondary_item_id = $r['secondary_item_id'];
    $notification->component_name    = $r['component_name'];
    $notification->component_action  = $r['component_action'];
    $notification->date_notified     = $r['date_notified'];
    $notification->is_new            = $r['is_new'];

    // Save the new notification.
    return $notification->save();
}

function gp_notifications_get_all_notifications_for_user( $user_id = 0 ) {
    if ( empty( $user_id ) ) {
        $user_id = ( gp_displayed_user_id() ) ? gp_displayed_user_id() : gp_loggedin_user_id();
    }

    // Get notifications out of the cache, or query if necessary.
    $notifications = wp_cache_get( 'all_for_user_' . $user_id, 'gp_notifications' );
    if ( false === $notifications ) {
        $notifications = GP_Notifications_Notification::get( array(
            'user_id' => $user_id
        ) );
        wp_cache_set( 'all_for_user_' . $user_id, $notifications, 'gp_notifications' );
    }

    return $notifications;
}