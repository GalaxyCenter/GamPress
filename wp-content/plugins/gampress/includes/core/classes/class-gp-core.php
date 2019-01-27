<?php
/**
 * GamPress Core Loader.
 *
 * ⊙▂⊙
 * 
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package GamPress
 * @sugpackage Core
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Creates the Core component.
 *
 * @since 1.5.0
 */
class GP_Core extends GP_Component {
    public function __construct() {
        parent::start(
                'core',
                __( 'GamPress Core', 'gampress' ),
                gampress()->includes_dir
                );
        
        $this->bootstrap();
    }
    
    private function bootstrap() {
        $gp = gampress();
        
        do_action( 'gp_core_loaded' );

        $gp->optional_components = apply_filters( 'gp_optional_components', array( 'sns', 'sms', 'pays', 'activities', 'votes', 'links', 'messages' ) );
        $gp->required_components = apply_filters( 'gp_required_components', array( 'members' ) );
        
        if ( $active_components = gp_get_option( 'gp-active-components' ) ) {
            $gp->active_components      = apply_filters( 'gp_active_components', $active_components );
            $gp->deactivated_components = apply_filters( 'gp_deactivated_components', array_values( array_diff( array_values( array_merge( $gp->optional_components, $gp->required_components ) ), array_keys( $gp->active_components ) ) ) );
        } else {
            $gp->deactivated_components = array();
            $active_components     = array_fill_keys( array_values( array_merge( $gp->optional_components, $gp->required_components ) ), '1' );
            $gp->active_components = apply_filters( 'gp_active_components', $gp->active_components );
        }
        
        // Loop through optional components.
        foreach( $gp->optional_components as $component ) {
            $includes_dir = apply_filters( 'gp_' . $component . '_includes_dir' , $gp->includes_dir );
            
            if ( gp_is_active( $component ) && file_exists( $includes_dir . '/' . $component . '/loader.php' ) ) {
                include( $includes_dir . '/' . $component . '/loader.php' );
            }
        }
        
        // Loop through required components.
        foreach( $gp->required_components as $component ) {
            $includes_dir = apply_filters( 'gp_' . $component . '_includes_dir' , $gp->includes_dir );
            
            if ( file_exists( $includes_dir . '/' . $component . '/loader.php' ) ) {
                include( $includes_dir . '/' . $component . '/loader.php' );
            }
        }
        
        $gp->required_components[] = 'core';
        do_action( 'gp_core_components_included' );
    }
    
    public function includes( $includes = array() ) {
        if ( ! is_admin() ) {
            return;
        }
        
        $includes = array(
                'admin'
                );
        
        parent::includes( $includes );
    }
    
    public function setup_globals( $args = array() ) {
        $gp = gampress();
        
        /** Database *********************************************************
        		 */
        
        // Get the base database prefix.
        if ( empty( $gp->table_prefix ) ) {
            $gp->table_prefix = gp_core_get_table_prefix();
        }
        
        // The domain for the root of the site where the main blog resides.
        if ( empty( $gp->root_domain ) ) {
            $gp->root_domain = gp_core_get_root_domain();
        }
        
        // Fetches all of the core GamPress settings in one fell swoop.
        if ( empty( $gp->site_options ) ) {
            $gp->site_options = gp_core_get_root_options();
        }
        
        // The names of the core WordPress pages used to display GamPress content.
        if ( empty( $gp->pages ) ) {
            $gp->pages = gp_core_get_directory_pages();
        }
        
        /** Basic current user data ******************************************
         */
        
        // Logged in user is the 'current_user'.
        $current_user            = wp_get_current_user();
        
        // The user ID of the user who is currently logged in.
        $gp->loggedin_user       = new stdClass;
        $gp->loggedin_user->id   = isset( $current_user->ID ) ? $current_user->ID : 0;
        
        do_action( 'gp_core_setup_globals' );
    }
    
    public function setup_cache_groups() {
        
        // Global groups.
        wp_cache_add_global_groups( array(
                    'gp'
                    ) );
        
        parent::setup_cache_groups();
    }
    
    public function register_post_types() {
        if ( gp_is_root_blog() && ! is_network_admin() ) {
			register_post_type(
				gp_get_email_post_type(),
				apply_filters( 'gp_register_email_post_type', array(
					'description'       => _x( 'GamPress emails', 'email post type description', 'gampress' ),
					'labels'            => gp_get_email_post_type_labels(),
					'menu_icon'         => 'dashicons-email',
					'public'            => false,
					'publicly_queryable' => gp_current_user_can( 'gp_moderate' ),
					'query_var'         => false,
					'rewrite'           => false,
					'show_in_admin_bar' => false,
					'show_ui'           => gp_current_user_can( 'gp_moderate' ),
					'supports'          => gp_get_email_post_type_supports(),
				) )
			);
		}
        
        parent::register_post_types();
    }
}

