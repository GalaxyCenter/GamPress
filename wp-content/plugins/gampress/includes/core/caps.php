<?php

// Exit if accessed directly 嗯 
if ( !defined( 'ABSPATH' ) ) exit;

function gp_loggedin_user_can( $capability, $blog_id = 0 ) {
    
    // Use root blog if no ID passed
    if ( empty( $blog_id ) )
        $blog_id = gp_get_root_blog_id();
    
    $retval = current_user_can_for_blog( $blog_id, $capability );
    
    return $retval;
}

function gp_current_user_can( $capability, $args = array() ) {
    // Backward compatibility for older $blog_id parameter.
    if ( is_int( $args ) ) {
        $site_id = $args;
        $args = array();
        $args['site_id'] = $site_id;
        
        // New format for second parameter.
    } elseif ( is_array( $args ) && isset( $args['blog_id'] ) ) {
        // Get the blog ID if set, but don't pass along to `current_user_can_for_blog()`.
        $args['site_id'] = (int) $args['blog_id'];
        unset( $args['blog_id'] );
    }
    
    // Cast $args as an array.
    $args = (array) $args;
    
    // Use root blog if no ID passed.
    if ( empty( $args['site_id'] ) ) {
        $args['site_id'] = gp_get_root_blog_id();
    }
    
    /** This filter is documented in /bp-core/bp-core-template.php */
    $current_user_id = apply_filters( 'gp_loggedin_user_id', get_current_user_id() );
    
    // Call bp_user_can().
    $retval = gp_user_can( $current_user_id, $capability, $args );
    
    return $retval;
}

function gp_user_can( $user_id, $capability, $args = array() ) {
    $site_id = gp_get_root_blog_id();
    
    // Get the site ID if set, but don't pass along to user_can().
    if ( isset( $args['site_id'] ) ) {
        $site_id = (int) $args['site_id'];
        unset( $args['site_id'] );
    }
    
    $switched = is_multisite() ? switch_to_blog( $site_id ) : false;
    $retval   = call_user_func_array( 'user_can', array( $user_id, $capability, $args ) );
    
    $retval = (bool) apply_filters( 'gp_user_can', $retval, $user_id, $capability, $site_id, $args );
    
    if ( $switched ) {
        restore_current_blog();
    }
    
    return $retval;
}

function _gp_enforce_gp_moderate_cap_for_admins( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
    
    // Bail if not checking the 'bp_moderate' cap.
    if ( 'gp_moderate' !== $cap ) {
        return $caps;
    }
    
    // Bail if BuddyPress is not network activated.
    if ( gp_is_network_activated() ) {
        return $caps;
    }
    
    // Never trust inactive users.
    //if ( gp_is_user_inactive( $user_id ) ) {
    //    return $caps;
    //}
    
    // Only users that can 'manage_options' on this site can 'bp_moderate'.
    return array( 'manage_options' );
}
add_filter( 'map_meta_cap', '_gp_enforce_gp_moderate_cap_for_admins', 10, 4 );