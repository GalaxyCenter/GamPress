<?php
/**
 * Component classes.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage Core
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'GP_Component' ) ) :

/**
 * GamPress Component Class.
 *
 * @since 1.0
 */
class GP_Component {
    /** Variables *************************************************************/
    
    public $name = '';
    public $id = '';
    public $slug = '';
    public $has_directory = false;
    public $path = '';
    public $query = false;
    public $current_id = '';
    public $admin_menu = '';
    public $search_string = '';
    public $root_slug = '';
    public $search_query_arg = 's';
    
    public $meta_tables = array();
    public $global_tables = array();
    
    /** Methods ***************************************************************/
    
    
    /**
     * Component loader.
     *
     *
     */
    public function start( $id = '', $name = '', $path = '', $params = array() ) {
        // Internal identifier of component.
        $this->id   = $id;
        
        // Internal component name.
        $this->name = $name;
        
        // Path for includes.
        $this->path = $path;

        if ( ! empty( $params ) ) {
            // Sets the position for our menu under the WP Toolbar's "My Account" menu.
            if (!empty($params['adminbar_myaccount_order'])) {
                $this->adminbar_myaccount_order = (int)$params['adminbar_myaccount_order'];
            }
        } else {
            // New component menus are added before the settings menu if not set.
            $this->adminbar_myaccount_order = 90;
        }
        
        $this->setup_actions();
    }
    
    public function setup_globals( $args = array() ) {
        $default_root_slug = isset( gampress()->pages->{$this->id}->slug ) ? gampress()->pages->{$this->id}->slug : '';

        $r = wp_parse_args( $args, array(
                    'slug'                  => $this->id,
                    'root_slug'             => $default_root_slug,
                    'has_directory'         => true,
                    'directory_title'       => '',
                    'notification_callback' => '',
                    'search_string'         => '',
                    'global_tables'         => '',
                    'meta_tables'           => '',
                    ) );
        $this->slug                  = apply_filters( 'gp_' . $this->id . '_slug',                  $r['slug']                  );
        $this->root_slug             = apply_filters( 'gp_' . $this->id . '_root_slug',             $r['root_slug']             );
        $this->has_directory         = apply_filters( 'gp_' . $this->id . '_has_directory',         $r['has_directory']         );
        $this->directory_title       = apply_filters( 'gp_' . $this->id . '_directory_title',       $r['directory_title']         );
        $this->search_string         = apply_filters( 'gp_' . $this->id . '_search_string',         $r['search_string']         );
        $this->notification_callback = apply_filters( 'gp_' . $this->id . '_notification_callback', $r['notification_callback'] );
        
        if ( ! empty( $r['global_tables'] ) ) {
            $this->register_global_tables( $r['global_tables'] );
        }
        
        if ( ! empty( $r['meta_tables'] ) ) {
            $this->register_meta_tables( $r['meta_tables'] );
        }
        
        gampress()->loaded_components[$this->slug] = $this->id;
        do_action( 'gp_' . $this->id . '_setup_globals' );
    }
    
    public function includes( $includes = array() ) {
        // Bail if no files to include.
        if ( ! empty( $includes ) ) {
            $slashed_path = trailingslashit( $this->path );
            
            // Loop through files to be included.
            foreach ( (array) $includes as $file ) {
                $paths = array(
                        // Passed with no extension.
                        $this->id . '/' . $this->id . '-' . $file  . '.php',
                        $this->id . '-' . $file . '.php',
                        $this->id . '/' . $file . '.php',
                        
                        // Passed with extension.
                        $file,
                        $this->id . '-' . $file,
                        $this->id . '/' . $file,
                    );
                
                foreach ( $paths as $path ) {
                    if ( @is_file( $slashed_path . $path ) ) {
                        require( $slashed_path . $path );
                        break;
                    }
                }
            }
        }
        
        do_action( 'gp_' . $this->id . '_includes' );
    }
    
    public function setup_actions() {
        // Setup globals.
        add_action( 'gp_setup_globals',          array( $this, 'setup_globals'          ), 10 );
        
        // Set up canonical stack.
        add_action( 'gp_setup_canonical_stack',  array( $this, 'setup_canonical_stack'  ), 10 );
        
        // Include required files. Called early to ensure that BP core
        // components are loaded before plugins that hook their loader functions
        // to gp_include with the default priority of 10. This is for backwards
        // compatibility; henceforth, plugins should register themselves by
        // extending this base class.
        add_action( 'gp_include',                array( $this, 'includes'               ), 8 );
        
        // Setup navigation.
        add_action( 'gp_setup_nav',              array( $this, 'setup_nav'              ), 10 );
        
        // Setup WP Toolbar menus.
        add_action( 'gp_setup_admin_bar',        array( $this, 'setup_admin_bar'        ), $this->adminbar_myaccount_order );
        
        // Setup component title.
        add_action( 'gp_setup_title',            array( $this, 'setup_title'            ), 10 );
        
        // Setup cache groups.
        add_action( 'gp_setup_cache_groups',     array( $this, 'setup_cache_groups'     ), 10 );
        
        // Register post types.
        add_action( 'gp_register_post_types',    array( $this, 'register_post_types'    ), 10 );
        
        // Register taxonomies.
        add_action( 'gp_register_taxonomies',    array( $this, 'register_taxonomies'    ), 10 );
        
        // Add the rewrite tags.
        add_action( 'gp_add_rewrite_tags',       array( $this, 'add_rewrite_tags'       ), 10 );
        
        // Add the rewrite rules.
        add_action( 'gp_add_rewrite_rules',      array( $this, 'add_rewrite_rules'      ), 10 );
        
        // Add the permalink structure.
        add_action( 'gp_add_permastructs',       array( $this, 'add_permastructs'       ), 10 );
        
        // Allow components to parse the main query.
        add_action( 'gp_parse_query',            array( $this, 'parse_query'            ), 10 );
        
        // Generate rewrite rules.
        add_action( 'gp_generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ), 10 );

        do_action( 'gp_' . $this->id . '_setup_actions' );
    }
    
    public function setup_canonical_stack() {}
    
    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
//        if ( !empty( $main_nav ) ) {
//            gp_core_new_nav_item( $main_nav, 'members' );
//
//            // Sub nav items are not required.
//            if ( !empty( $sub_nav ) ) {
//                foreach( (array) $sub_nav as $nav ) {
//                    gp_core_new_subnav_item( $nav, 'members' );
//                }
//            }
//        }
        do_action( 'gp_' . $this->id . '_setup_nav' );
    }
    
    public function setup_admin_bar( $wp_admin_nav = array() ) {
        // Bail if this is an ajax request.
        if ( defined( 'DOING_AJAX' ) ) {
            return;
        }
        
        // Do not proceed if BP_USE_WP_ADMIN_BAR constant is not set or is false.
        if ( ! bp_use_wp_admin_bar() ) {
            return;
        }
        
        $wp_admin_nav = apply_filters( 'gp_' . $this->id . '_admin_nav', $wp_admin_nav );
        if ( !empty( $wp_admin_nav ) ) {
            // Fill in position if one wasn't passed for backpat.
            $pos = 0;
            $not_set_pos = 1;
            foreach( $wp_admin_nav as $key => $nav ) {
                if ( ! isset( $nav['position'] ) ) {
                    $wp_admin_nav[$key]['position'] = $pos + $not_set_pos;
                    
                    if ( 9 !== $not_set_pos ) {
                        ++$not_set_pos;
                    }
                } else {
                    $pos = $nav['position'];
                    
                    // Reset not set pos to 1
                    if ( $pos % 10 === 0 ) {
                        $not_set_pos = 1;
                    }
                }
            }
            
            // Sort admin nav by position.
            $wp_admin_nav = gp_sort_by_key( $wp_admin_nav, 'position', 'num' );
            
            // Set this objects menus.
            $this->admin_menu = $wp_admin_nav;
            
            // Define the WordPress global.
            global $wp_admin_bar;
            
            // Add each admin menu.
            foreach( $this->admin_menu as $admin_menu ) {
                $wp_admin_bar->add_menu( $admin_menu );
            }
        }
        
        do_action( 'gp_' . $this->id . '_setup_admin_bar' );
    }
    
    public function setup_title() {
        do_action(  'gp_' . $this->id . '_setup_title' );
    }
    
    public function setup_cache_groups() {
        do_action( 'gp_' . $this->id . '_setup_cache_groups' );
    }
    
    public function register_global_tables( $tables = array() ) {
        $tables = apply_filters( 'gp_' . $this->id . '_global_tables', $tables );
        
        // Add to the BuddyPress global object.
        if ( !empty( $tables ) && is_array( $tables ) ) {
            foreach ( $tables as $global_name => $table_name ) {
                $this->$global_name = $table_name;
            }
            
            // Keep a record of the metadata tables in the component.
            $this->global_tables = $tables;
        }
    }
    
    public function register_meta_tables( $tables = array() ) {
        global $wpdb;
        
        $tables = apply_filters( 'gp_' . $this->id . '_meta_tables', $tables );
        
        if ( !empty( $tables ) && is_array( $tables ) ) {
            foreach( $tables as $meta_prefix => $table_name ) {
                $wpdb->{$meta_prefix . 'meta'} = $table_name;
            }
            
            // Keep a record of the metadata tables in the component.
            $this->meta_tables = $tables;
        }
        
        do_action( 'gp_' . $this->id . '_register_meta_tables' );
    }
    
    public function register_post_types() {
        do_action( 'gp_' . $this->id . '_register_post_types' );
    }
    
    public function register_taxonomies() {
        do_action( 'gp_' . $this->id . '_register_taxonomies' );
    }
    
    public function add_rewrite_tags() {
        do_action( 'gp_' . $this->id . '_add_rewrite_tags' );
    }
    
    public function add_rewrite_rules() {
        do_action( 'gp_' . $this->id . '_add_rewrite_rules' );
    }
    
    public function add_permastructs() {
        do_action( 'gp_' . $this->id . '_add_permastructs' );
    }
    
    public function parse_query( $query ) {
        do_action_ref_array( 'gp_' . $this->id . '_parse_query', array( &$query ) );
    }
    
    public function generate_rewrite_rules() {
        do_action( 'gp_' . $this->id . '_generate_rewrite_rules' );
    }
}

endif;