<?php

/**
 * GamPress Core Screen Functions
 * 的
 * @package gampressustom
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function combines_screen_show() {
    if ( gp_is_combines_component() )  {
        
        if ( gp_is_current_action( 'show' ) ) {
            gp_update_is_directory( false, 'combines' );
            
            gp_core_load_template( 'combines/single/home' );
        } else {
            gp_update_is_directory( true, 'combines' );
            
            gp_core_load_template( 'combines/index' );
        } 
        
    } 
}
add_action( 'gp_screens', 'combines_screen_show' );