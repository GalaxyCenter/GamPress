<?php
/**
 * GamPress Video Screens.
 * 
 * ⊙▂⊙
 *
 * Handlers for member screens that aren't handled elsewhere.
 *
 * @package GamPress
 * @subpackage SnsScreens
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function videos_screen_show() {
    if ( gp_is_videos_component() )  {

        if ( gp_is_current_action( 'show' ) ) {
            gp_update_is_directory( true, 'videos' );
            
            gp_core_load_template( 'videos/single/home' );
        } else {
            gp_update_is_directory( true, 'videos' );
            
            gp_core_load_template( 'videos/index' );
        } 
        
    } 
}
add_action( 'gp_screens', 'videos_screen_show' );