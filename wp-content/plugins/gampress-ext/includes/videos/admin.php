<?php
/**
 * GamPress-Ext Videos Admin.
 *
 * ⊙▂⊙
 * 
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package GamPress-Ext
 * @sugpackage Videos
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_video_add_admin_menu() {
    
    // Add our screen.
    $hook = add_menu_page(
            _x( 'Video', 'Admin Dashbord SWA page title', 'gampress' ),
            _x( 'Video', 'Admin Dashbord SWA menu', 'gampress' ),
            'gp_moderate',
            'gp-video',
            'gp_video_admin',
            'div'
            );
    
    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_video_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_video_add_admin_menu' );

function gp_video_admin_load() {
}

function gp_video_admin() {
    // Decide whether to load the index or edit screen.
    $doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
    
    // Display the single activity edit screen.
    if ( 'edit' == $doaction && ! empty( $_GET['aid'] ) )
        gp_activity_admin_edit();
    
    // Otherwise, display the Activity index screen.
    else
        gp_activity_admin_index();
}

function gp_activity_admin_edit() {
}

function gp_activity_admin_index() {
    echo 'Simple Page';
}

function gp_groups_admin_menu_order( $custom_menus = array() ) {
    array_push( $custom_menus, 'gp-video' );
    return $custom_menus;
}
add_filter( 'gp_admin_menu_order', 'gp_groups_admin_menu_order' );
