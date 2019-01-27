<?php
/**
 * GamPress Sns Loader.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage Sns
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the GamPress Sns Component.
 *
 * @since 1.0
 */
class GP_Sns_Component extends GP_Component {
    public $types = array();
    
    public function __construct() {
        parent::start(
                'sns',
                __( 'Sns', 'gampress' ),
                gampress()->includes_dir,
                array(
                    'adminbar_myaccount_order' => 20
                    )
                );
    }
    
    public function includes( $includes = array() ) {
        $includes = array(
            'actions',
            'filters',
            'functions',
			'screens',
            'template'
		);
        
        if ( ! gampress()->do_autoload ) {
            $includes[] = 'classes';
        }
        
        if ( is_admin() ) {
            $includes[] = 'admin';
        }
        
        parent::includes( $includes );
    }
    
    public function setup_globals( $args = array() ) {
        global $wpdb;
        
        $gp = gampress();
        
        // Define a slug, if necessary.
		if ( ! defined( 'GP_SNS_SLUG' ) ) {
            define( 'GP_SNS_SLUG', $this->id );
		}

		// Global tables for activity component.
		$global_tables = array(
			'table_name'      => $gp->table_prefix . 'gp_sns',
			'table_name_meta' => $gp->table_prefix . 'gp_sns_meta',
		);

		// Metadata tables for groups component.
		$meta_tables = array(
			'sns' => $gp->table_prefix . 'gp_sns_meta',
		);

		// Fetch the default directory title.
		$default_directory_titles = gp_core_get_directory_page_default_titles();
		$default_directory_title  = $default_directory_titles[$this->id];

		// All globals for activity component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'                  => GP_SNS_SLUG,
			'root_slug'             => isset( $gp->pages->sns->slug ) ? $gp->pages->sns->slug : GP_SNS_SLUG,
			'has_directory'         => true,
			'directory_title'       => isset( $gp->pages->sns->title ) ? $gp->pages->sns->title : $default_directory_title,
			'notification_callback' => 'gp_sns_format_notifications',
			'search_string'         => __( 'Search Sns...', 'gampress' ),
			'global_tables'         => $global_tables,
			'meta_tables'           => $meta_tables,
		);

        parent::setup_globals( $args );
        
    }
    
    public function setup_actions() {
        add_action( 'gp_allowed_redirect_hosts',          array( $this, 'allowed_redirect_hosts'          ), 10 );
        
        parent::setup_actions();
    }
    
    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
        
        // Stop if there is no user displayed or logged in.
		if ( ! is_user_logged_in() && ! gp_displayed_user_id() ) {
			return;
		}

		$slug          = gp_get_sns_slug();
        
		// Add 'Activity' to the main navigation.
		$main_nav = array(
			'name'                => _x( 'Sns', 'Sns screen nav', 'gampress' ),
			'slug'                => $slug,
			'position'            => 10,
			'screen_function'     => 'gp_sns_screen_index',
			'item_css_id'         => $this->id
		);
        
        parent::setup_nav( $main_nav, $sub_nav );
    }
   
    public function setup_title() {        
        parent::setup_title();
    }
    
    public function setup_cache_groups() {
        parent::setup_cache_groups();
    }
    
    public function allowed_redirect_hosts( $allowed_hosts ) {
        $allowed_hosts[] = 'open.weixin.qq.com';
        $allowed_hosts[] = 'api.weibo.com';
        $allowed_hosts[] = 'graph.qq.com';
        $allowed_hosts[] = 'test.adaixiong.com';
        return $allowed_hosts;
    }
}