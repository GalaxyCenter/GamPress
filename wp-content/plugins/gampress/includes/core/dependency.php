<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Activation Actions ********************************************************/

function gp_activation() {
    do_action( 'gp_activation' );
}

function gp_deactivation() {
    do_action( 'gp_deactivation' );
}

function gp_uninstall() {
    do_action( 'gp_uninstall' );
}

function gp_loaded() {
    do_action( 'gp_loaded' );
}

function gp_setup_components() {
    do_action( 'gp_setup_components' );
}

function gp_include() {
    do_action( 'gp_include' );
}

function gp_setup_globals() {
    do_action( 'gp_setup_globals' );
}

function gp_init() {
    do_action( 'gp_init' );
}

function gp_screens() {
    do_action( 'gp_screens' );
}

function gp_setup_nav() {
    do_action( 'gp_setup_nav' );
}

function gp_actions() {
    do_action( 'gp_actions' );
}

function gp_template_redirect() {        
    do_action( 'gp_template_redirect' );
}

function gp_register() {
    do_action( 'gp_register' );
}

function gp_register_post_types() {
    do_action( 'gp_register_post_types' );
}

function gp_setup_current_user() {
    do_action( 'gp_setup_current_user' );
}

function gp_setup_displayed_user() {
    do_action( 'gp_setup_displayed_user' );
}

function gp_head() {
    do_action ( 'gp_head' );
}

function gp_register_theme_directory() {
    do_action( 'gp_register_theme_directory' );
}

function gp_allowed_redirect_hosts( $allowed_hosts ) {
    return apply_filters( 'gp_allowed_redirect_hosts', $allowed_hosts );
}

function gp_register_taxonomies() {
    do_action( 'gp_register_taxonomies' );
}

function gp_register_theme_packages() {
    do_action( 'gp_register_theme_packages' );
}

function gp_setup_theme() {
    do_action( 'gp_setup_theme' );
}

function gp_after_setup_theme() {
    do_action( 'gp_after_setup_theme' );
}

function gp_setup_title() {
    do_action( 'gp_setup_title' );
}