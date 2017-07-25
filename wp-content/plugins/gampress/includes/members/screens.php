<?php
/**
 * GamPress Member Screen Functions.
 *
 * �Ѩy��
 * 
 * @package GamPress
 * @sugpackage Members
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

function gp_members_screen_display_profile() {
    gp_core_load_template( apply_filters( 'gp_members_screen_display_profile', 'members/single/home' ) );
}

function gp_members_screen_index() {
    if ( gp_is_members_directory() ) {
        gp_update_is_directory( true, 'members' );
        
        do_action( 'gp_members_screen_index' );
        
        gp_core_load_template( apply_filters( 'gp_members_screen_index', 'members/index' ) );
    }
}
add_action( 'gp_screens', 'gp_members_screen_index' );

function gp_core_screen_activation() {
    
    // Bail if not viewing the activation page.
    if ( ! gp_is_current_component( 'activate' ) ) {
        return false;
    }
    
    // If the user is already logged in, redirect away from here.
    if ( is_user_logged_in() ) {
        
        // If activation page is also front page, set to members directory to
        // avoid an infinite loop. Otherwise, set to root domain.
        $redirect_to = gp_is_component_front_page( 'activate' )
            ? gp_get_members_directory_permalink()
            : gp_get_root_domain();
        
        // Trailing slash it, as we expect these URL's to be.
        $redirect_to = trailingslashit( $redirect_to );
        
        $redirect_to = apply_filters( 'gp_loggedin_activate_page_redirect_to', $redirect_to );
        
        // Redirect away from the activation page.
        gp_core_redirect( $redirect_to );
    }
    
    // Grab the key (the old way).
    $key = isset( $_GET['key'] ) ? $_GET['key'] : '';
    
    // Grab the key (the new way).
    if ( empty( $key ) ) {
        $key = gp_current_action();
    }
    
    // Get GamPress.
    $gp = gampress();
    
    // We've got a key; let's attempt to activate the signup.
    if ( ! empty( $key ) ) {
        
        $user = apply_filters( 'gp_core_activate_account', gp_core_activate_signup( $key ) );
        
        // If there were errors, add a message and redirect.
        if ( ! empty( $user->errors ) ) {
            gp_core_add_message( $user->get_error_message(), 'error' );
            gp_core_redirect( trailingslashit( gp_get_root_domain() . '/' . $gp->pages->activate->slug ) );
        }
        
        gp_core_add_message( __( 'Your account is now active!', 'gampress' ) );
        $gp->activation_complete = true;
    }
    
    gp_core_load_template( apply_filters( 'gp_core_template_activate', array( 'activate', 'registration/activate' ) ) );
}
add_action( 'gp_screens', 'gp_core_screen_activation' );

function gp_core_screen_signup() {
    if ( ! gp_is_current_component( 'signup' ) || ! gp_is_current_action( 'key' ) )
        return;

    $key = isset( $_SERVER[QUERY_STRING] ) ? $_SERVER[QUERY_STRING] : '';

    $user = apply_filters( 'gp_core_activate_account', gp_core_activate_signup( $key ) );

    // If there were errors, add a message and redirect.
    if ( ! empty( $user->errors ) ) {
        gp_core_add_message( $user->get_error_message(), 'error' );
        gp_core_redirect( trailingslashit( gp_get_root_domain() . '/' . $gp->pages->activate->slug ) );
    }

    gp_core_load_template( apply_filters( 'gp_core_template_register', array( 'signup', 'signup/signup' ) ) );
}
add_action( 'gp_screens', 'gp_core_screen_signup' );

function gp_sns_setup_bp_nav() {
    global $bp;

    $cur_user = wp_get_current_user();
    if ( $cur_user->ID != 0 && $cur_user->user_login == $cur_user->display_name ) {
        bp_core_new_nav_item(
            array(
                'name' => __('Wizard', 'buddypress'),
                'slug' => 'wizard',
                'position' => 60,
                'show_for_displayed_user' => true,
                'screen_function' => 'gp_member_screen_wizard',
                'default_subnav_slug' => 'wizard',
                'item_css_id' => 13
            ));
    }
}
add_action( 'bp_setup_nav', 'gp_sns_setup_bp_nav' );

function gp_member_screen_wizard() {
    gp_core_load_template( 'members/single/wizard' );
}

new GP_Members_Theme_Compat();