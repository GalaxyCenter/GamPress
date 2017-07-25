<?php

/**
 * GamPress Core Actions
 *
 * ⊙▂⊙
 * 
 * @package gampress
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * 管理后台注册初始化相关的action
 * 
 */
add_action( 'admin_menu',               'gp_admin_menu'                  );
add_action( 'admin_init',               'gp_admin_init'                  );
add_action( 'admin_head',               'gp_admin_head'                  );
add_action( 'menu_order',               'gp_admin_menu_order'            );
add_action( 'custom_menu_order',        'gp_admin_custom_menu_order'     );
add_filter( 'admin_footer_text',        'gp_admin_footer_text'           );

// Hook on to admin_init
add_action( 'gp_admin_init',  'gp_setup_updater',          1000);
add_action( 'gp_admin_init',  'gp_register_admin_settings'      );

// Add a new separator.
add_action( 'gp_admin_menu', 'gp_admin_separator' );

function gp_admin_menu() {
    do_action( 'gp_admin_menu' );
}

function gp_admin_init() {
    do_action( 'gp_admin_init' );
}

function gp_admin_head() {
    do_action( 'gp_admin_head' );
}

function gp_admin_footer_text( $txt ) {
    return apply_filters( 'gp_admin_footer_text', $txt );
}

function gp_register_admin_settings() {
    do_action( 'gp_register_admin_settings' );
}
