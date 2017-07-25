<?php
/**
 * DB schema
 * 系统相关
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_core_set_charset() {
    global $wpdb;
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    // BuddyPress component DB schema
    return !empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : '';
}

function gp_core_install( $active_components = false ) {
    // If no components passed, get all the active components from the main site.
    if ( empty( $active_components ) ) {
        
        $active_components = apply_filters( 'gp_active_components', gp_get_option( 'gp-active-components' ) );
    }
    
    // Install the signups table.
    gp_core_maybe_install_signups();
    
    foreach ( $active_components as $key => $value ) {
        do_action( 'gp_core_install_' . $key );
    }
}

function gp_core_upgrade_signups() {
}

function gp_core_install_signups() {
    $sql             = array();
	$charset_collate = $GLOBALS['wpdb']->get_charset_collate();
    $gp_prefix       = $GLOBALS['wpdb']->base_prefix;

    $sql[] = "CREATE TABLE {$gp_prefix}gp_signups (
                    signup_id bigint(20) NOT NULL auto_increment,
                    user_email varchar(100) NOT NULL default '',
                    registered datetime NOT NULL default '0000-00-00 00:00:00',
                    activated datetime NOT NULL default '0000-00-00 00:00:00',
                    active tinyint(1) NOT NULL default '0',
                    activation_key varchar(50) NOT NULL default '',
                    PRIMARY KEY  (signup_id),
                    KEY activation_key (activation_key),
                    KEY user_email (user_email)
			) {$charset_collate};";
   
    dbDelta( $sql );
}

function gp_core_install_sns() {
}

function gp_core_install_emails() {
    $defaults = array(
            'post_status' => 'publish',
            'post_type'   => gp_get_email_post_type(),
            );
    
    $emails       = gp_email_get_schema();
    $descriptions = gp_email_get_type_schema( 'description' );
    
    // Add these emails to the database.
    foreach ( $emails as $id => $email ) {
        $post_id = wp_insert_post( wp_parse_args( $email, $defaults, 'install_email_' . $id ) );
        if ( ! $post_id ) {
            continue;
        }
        
        $tt_ids = wp_set_object_terms( $post_id, $id, gp_get_email_tax_type() );
        foreach ( $tt_ids as $tt_id ) {
            $term = get_term_by( 'term_taxonomy_id', (int) $tt_id, gp_get_email_tax_type() );
            wp_update_term( (int) $term->term_id, gp_get_email_tax_type(), array(
                        'description' => $descriptions[ $id ],
                        ) );
        }
    }
    
    gp_update_option( 'gp-emails-unsubscribe-salt', base64_encode( wp_generate_password( 64, true, true ) ) );
    
    do_action( 'gp_core_install_emails' );
}
