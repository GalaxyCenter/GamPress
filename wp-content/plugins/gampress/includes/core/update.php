<?php

/**
 * GamPress Topic Functions
 * 
 * ⊙▂⊙
 * 
 * 更新模块
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_is_install() {
    return ! gp_get_db_version_raw();
}

function gp_is_update() {
    $raw    = (int) gp_get_db_version_raw();
    $cur    = (int) gp_get_db_version();
    $retval = (bool) ( $raw < $cur );
    
    return $retval;
}

function gp_version_bump() {
    update_option( '_gp_db_version', gp_get_db_version() );
}

function gp_setup_updater() {
    
    if ( ! gp_is_update() )
        return;
    
    gp_version_updater(); 
    gp_version_bump();
}

function gp_version_updater() {
    $default_components = apply_filters( 'gp_new_install_default_components', array(
                'members'       => 1,
                'xprofile'      => 1
                ) );
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    require_once( gampress()->includes_dir . '/core/admin/schema.php' );
    gp_register_taxonomies();
    
    // Install
    if ( gp_is_install() ) {        
        gp_core_install( $default_components );
        gp_update_option( 'gp-active-components', $default_components );
        gp_core_add_page_mappings( $default_components, 'delete' );
        gp_core_install_emails();
        // Upgrades
    } else {
        gp_core_add_page_mappings( $default_components, 'delete' );
        gp_update_to_1_2();
    }
    
    gp_version_bump();
}

function gp_update_to_1_2() {
    gp_core_upgrade();
}

function gp_core_upgrade() {
    gp_core_install_emails();
}

function gp_is_deactivation( $basename = '' ) {
    $gp     = gampress();
    $action = false;
    
    if ( ! empty( $_REQUEST['action'] ) && ( '-1' != $_REQUEST['action'] ) ) {
        $action = $_REQUEST['action'];
    } elseif ( ! empty( $_REQUEST['action2'] ) && ( '-1' != $_REQUEST['action2'] ) ) {
        $action = $_REQUEST['action2'];
    }
    
    // Bail if not deactivating.
    if ( empty( $action ) || !in_array( $action, array( 'deactivate', 'deactivate-selected' ) ) ) {
        return false;
    }
    
    // The plugin(s) being deactivated.
    if ( 'deactivate' == $action ) {
        $plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
    } else {
        $plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
    }
    
    // Set basename if empty.
    if ( empty( $basename ) && !empty( $gp->basename ) ) {
        $basename = $gp->basename;
    }
    
    // Bail if no basename.
    if ( empty( $basename ) ) {
        return false;
    }
    
    return in_array( $basename, $plugins );
}

function gp_core_maybe_install_signups() {
    global $wpdb;
    
    // The table to run queries against.
    $signups_table = $wpdb->base_prefix . 'gp_signups';
    
    // Suppress errors because users shouldn't see what happens next.
    $old_suppress  = $wpdb->suppress_errors();
    
    // Never use gp_core_get_table_prefix() for any global users tables.
    $table_exists  = (bool) $wpdb->get_results( "DESCRIBE {$signups_table};" );
    
    // Table already exists, so maybe upgrade instead?
    if ( true === $table_exists ) {
        
        // Look for the 'signup_id' column.
        $column_exists = $wpdb->query( "SHOW COLUMNS FROM {$signups_table} LIKE 'signup_id'" );
        
        // 'signup_id' column doesn't exist, so run the upgrade
        if ( empty( $column_exists ) ) {
            gp_core_upgrade_signups();
        }
        
        // Table does not exist, and we are a single site, so install the multisite
        // signups table using WordPress core's database schema.
    } elseif ( ! is_multisite() ) {
        gp_core_install_signups();
    }
    
    // Restore previous error suppression setting.
    $wpdb->suppress_errors( $old_suppress );
}