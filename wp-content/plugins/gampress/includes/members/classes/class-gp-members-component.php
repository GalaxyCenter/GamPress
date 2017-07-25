<?php
/**
 * GamPress Members Loader.
 *
 * �Ѩy��
 * 
 * @package GamPress
 * @subpackage Members
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the GamPress Members Component.
 *
 * @since 1.0
 */
class GP_Members_Component extends GP_Component {
    public $types = array();
    
    public function __construct() {
        parent::start(
                'members',
                __( 'Members', 'gampress' ),
                gampress()->includes_dir,
                array(
                    'adminbar_myaccount_order' => 20
                    )
                );
    }
    
    public function includes( $includes = array() ) {
        $includes = array(
                'actions',
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
        if ( ! defined( 'GP_MEMBERS_SLUG' ) ) {
            define( 'GP_MEMBERS_SLUG', $this->id );
        }
        
        // Global tables for activity component.
        $global_tables = array(
                'table_name'            => $gp->table_prefix . 'gp_members',
                'table_name_meta'       => $gp->table_prefix . 'gp_members_meta',
                'table_name_signups'    => $gp->table_prefix . 'gp_signups', // Signups is a global WordPress table.
                );
        
        // Metadata tables for groups component.
        $meta_tables = array(
                'members' => $gp->table_prefix . 'gp_members_meta',
                );
        
        // Fetch the default directory title.
        $default_directory_titles = gp_core_get_directory_page_default_titles();
        $default_directory_title  = $default_directory_titles[$this->id];
        
        // All globals for activity component.
        // Note that global_tables is included in this array.
        $args = array(
                'slug'                  => GP_MEMBERS_SLUG,
                'root_slug'             => isset( $gp->pages->members->slug ) ? $gp->pages->members->slug : GP_MEMBERS_SLUG,
                'has_directory'         => true,
                'directory_title'       => isset( $gp->pages->members->title ) ? $gp->pages->members->title : $default_directory_title,
                'notification_callback' => 'gp_members_format_notifications',
                'search_string'         => __( 'Search Members...', 'gampress' ),
                'global_tables'         => $global_tables,
                'meta_tables'           => $meta_tables,
                );
        
        parent::setup_globals( $args );

        /** Logged in user ***************************************************
         */

        // The core userdata of the user who is currently logged in.
        $gp->loggedin_user->userdata       = gp_core_get_core_userdata( gp_loggedin_user_id() );

        // Fetch the full name for the logged in user.
        $gp->loggedin_user->fullname       = isset( $gp->loggedin_user->userdata->display_name ) ? $gp->loggedin_user->userdata->display_name : '';

        // Hits the DB on single WP installs so get this separately.
        $gp->loggedin_user->is_super_admin = $gp->loggedin_user->is_site_admin = is_super_admin( gp_loggedin_user_id() );

        // The domain for the user currently logged in. eg: http://example.com/members/andy.
        $gp->loggedin_user->domain         = gp_core_get_user_domain( gp_loggedin_user_id() );

        /** Displayed user ***************************************************
         */

        // The core userdata of the user who is currently being displayed.
        $gp->displayed_user->userdata = gp_core_get_core_userdata( gp_displayed_user_id() );

        // Fetch the full name displayed user.
        $gp->displayed_user->fullname = isset( $gp->displayed_user->userdata->display_name ) ? $gp->displayed_user->userdata->display_name : '';

        // The domain for the user currently being displayed.
        $gp->displayed_user->domain   = gp_core_get_user_domain( gp_displayed_user_id() );
        
        // Initialize the nav for the members component.
        $this->nav = new GP_Core_Nav();

        /** Signup ***********************************************************
		 */

        $gp->signup = new stdClass;
    }
    
    public function setup_actions() {
        parent::setup_actions();
    }
    
    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
        
        // Don't set up navigation if there's no member.
		if ( ! is_user_logged_in() && ! gp_is_user() ) {
			return;
        }
        
        $is_xprofile_active = gp_is_active( 'xprofile' );
        
        // Bail if XProfile component is active and there's no custom front page for the user.
        if ( ! gp_displayed_user_has_front_template() && $is_xprofile_active ) {
            return;
        }
        
        // Determine user to use.
        if ( gp_displayed_user_domain() ) {
            $user_domain = gp_displayed_user_domain();
        } elseif ( gp_loggedin_user_domain() ) {
            $user_domain = gp_loggedin_user_domain();
        } else {
            return;
        }
        
        // Set slug to profile in case the xProfile component is not active
		$slug = 'profile';//gp_get_profile_slug();

		// Defaults to empty navs
		$this->main_nav = array();
		$this->sub_nav  = array();

		if ( ! $is_xprofile_active ) {
			$this->main_nav = array(
				'name'                => _x( 'Profile', 'Member profile main navigation', 'gampress' ),
				'slug'                => $slug,
				'position'            => 20,
				'screen_function'     => 'gp_members_screen_display_profile',
                    'default_subnav_slug' => 'public',
 
                    );
        }

        /**
         * Setup the subnav items for the member profile.
         *
         * This is required in case there's a custom front or in case the xprofile component
         * is not active.
         */
        $this->sub_nav = array(
                'name'            => _x( 'View', 'Member profile view', 'gampress' ),
                'slug'            => 'public',
                'parent_url'      => trailingslashit( $user_domain . $slug ),
                'parent_slug'     => $slug,
                'screen_function' => 'gp_members_screen_display_profile',
                'position'        => 10
                );
        
        /**
         * If there's a front template the members component nav
         * will be there to display the user's front page.
         */
        if ( gp_displayed_user_has_front_template() ) {
            $main_nav = array(
                    'name'                => _x( 'Home', 'Member Home page', 'gampress' ),
                    'slug'                => 'front',
                    'position'            => 5,
                    'screen_function'     => 'gp_members_screen_display_profile',
                    'default_subnav_slug' => 'public',
                    );
            
            // We need a dummy subnav for the front page to load.
            $front_subnav = $this->sub_nav;
            $front_subnav['parent_slug'] = 'front';
            
            // In case the subnav is displayed in the front template
            $front_subnav['parent_url'] = trailingslashit( $user_domain . 'front' );
            
            // Set the subnav
            $sub_nav[] = $front_subnav;
            
            /**
             * If the profile component is not active, we need to create a new
             * nav to display the WordPress profile.
             */
            if ( ! $is_xprofile_active ) {
                add_action( 'gp_members_setup_nav', array( $this, 'setup_profile_nav' ) );
            }
            
            /**
             * If there's no front template and xProfile is not active, the members
             * component nav will be there to display the WordPress profile
             */
        } else {
            $main_nav  = $this->main_nav;
            $sub_nav[] = $this->sub_nav;
        }
        
        
        parent::setup_nav( $main_nav, $sub_nav );
    }
    
    public function setup_title() {        
        parent::setup_title();
    }
    
    public function setup_cache_groups() {
        parent::setup_cache_groups();
    }
}