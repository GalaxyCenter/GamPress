<?php
/**
 * Plugin Name: GamPress
 * Plugin URI: https://github.com/kuibobo/GamPress
 * Description: Gam插件测试版
 * Version: 1.0.0
 * Author: Bourne Jiang
 * Author URI: http://weibo.com/texel
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GamPress' ) ) :
    
    /**
     * Main GamPress Class
     *
     */
    final class GamPress {
        
        public $required_components = array();
        public $loaded_components = array();
        public $active_components = array();
        public $do_autoload = false;
        public $do_nav_backcompat = false;
        public $options = array();
        public $cache = array();

        /** Singleton *************************************************************/
        
        /**
         * 构造一个单态实例
         *
         * @return 返回GamPress实例
         *
         */
        public static function instance() {
            
            static $instance = null;
            
            if ( null === $instance ) {
                $instance = new GamPress();
                
                $instance->constants();
                $instance->setup_globals();
                $instance->legacy_constants();
                $instance->includes();
                $instance->setup_actions();
            }
            
            return $instance;
        }
        
        private function __construct() { /* Do nothing here */ }
        
        /**
         * 设置常量
         *
         *
         */
        private function constants() {
            
            // Place your custom code (actions/filters) in a file called
            // '/plugins/gp-custom.php' and it will be loaded before anything else.
            if ( file_exists( WP_PLUGIN_DIR . '/gp-custom.php' ) ) {
                require( WP_PLUGIN_DIR . '/gp-custom.php' );
            }
            
            // Path and URL
            if ( ! defined( 'GP_PLUGIN_DIR' ) ) {
                define( 'GP_PLUGIN_DIR', trailingslashit( WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) )  );
            }
            
            if ( ! defined( 'GP_PLUGIN_URL' ) ) {
                define( 'GP_PLUGIN_URL', trailingslashit( plugins_url( basename( dirname( __FILE__ ) ) ) ) );
            }
            
            if ( ! defined( 'GP_SOURCE_SUBDIRECTORY' ) ) {
                define( 'GP_SOURCE_SUBDIRECTORY', '' );
            }
            
            if ( !defined( 'GP_ROOT_BLOG' ) ) {
                $root_blog_id = get_current_blog_id();
                
                define( 'GP_ROOT_BLOG', $root_blog_id );
            }
            
            if ( !defined( 'GP_SEARCH_SLUG' ) )
                define( 'GP_SEARCH_SLUG', 'search' );
            
            if ( !defined( 'GP_DATE_BASE' ) )
                define( 'GP_DATE_BASE', date("Y-m-d", 0) );
        }
        
        /**
         * 设置类的全局变量
         *
         *
         */
        private function setup_globals() {
            /** Versions **********************************************************/
            
            $this->version    = '0.0.13';
            $this->db_version = '002';
            
            /** Components **/
            $this->current_component = '';
            $this->current_item = '';
            $this->current_action = '';
            
            $this->is_single_item = false;
            
            /** Database **********************************************************/
            if ( empty( $this->table_prefix ) ) {
                global $wpdb;
                
                $this->table_prefix = $wpdb->base_prefix;
            }
            $this->root_blog_id = (int) GP_ROOT_BLOG;
            
            /** Paths *************************************************************/
            // GamPress root directory
            $this->file         = constant( 'GP_PLUGIN_DIR' ) . 'loader.php';
            $this->basename     = basename( constant( 'GP_PLUGIN_DIR' ) ) . '/loader.php';
            $this->plugin_dir   = trailingslashit( constant( 'GP_PLUGIN_DIR' ) . constant( 'GP_SOURCE_SUBDIRECTORY' ) );
            $this->plugin_url   = trailingslashit( constant( 'GP_PLUGIN_URL' ) . constant( 'GP_SOURCE_SUBDIRECTORY' ) );

            // Languages
            $this->lang_dir       = $this->plugin_dir . 'languages';
            
            // Includes
            $this->includes_dir = trailingslashit( $this->plugin_dir . 'includes'  );
            $this->includes_url = trailingslashit( $this->plugin_url . 'includes'  );
            
            // Templates (theme compatibility)
            $this->themes_dir   = trailingslashit( $this->plugin_dir . 'templates' );
            $this->themes_url   = trailingslashit( $this->plugin_url . 'templates' );
            
            // Themes (for bp-default)
            $this->old_themes_dir = $this->plugin_dir . 'themes';
            $this->old_themes_url = $this->plugin_url . 'themes';
            
            /** Theme Compat ******************************************************/
            
            $this->theme_compat   = new stdClass(); // Base theme compatibility class
            $this->filters        = new stdClass(); // Used when adding/removing filters
            
            /** Users *************************************************************/
            
            $this->loggedin_user   = new stdClass(); //new WP_User(); // Currently logged in user
            $this->displayed_user  = new stdClass(); //new WP_User(); // Currently displayed user
            
            /** Post types and taxonomies *****************************************/
            $this->email_post_type     = apply_filters( 'gp_email_post_type', 'gp-email' );
            $this->email_taxonomy_type = apply_filters( 'gp_email_tax_type', 'gp-email-type' );
            
            /** Navigation backward compatibility *********************************/
            if ( interface_exists( 'ArrayAccess', false ) ) {
                // gp_nav and gp_options_nav compatibility depends on SPL.
                $this->do_nav_backcompat = true;
            }
        }
        
        private function legacy_constants() {
            
            // Define the GamPress version
            if ( ! defined( 'GP_VERSION' ) ) {
                define( 'GP_VERSION', $this->version );
            }
            
            // Define the database version
            if ( ! defined( 'GP_DB_VERSION' ) ) {
                define( 'GP_DB_VERSION', $this->db_version );
            }
        }
        
        /**
         * 加载插件用到的所有文件
         *
         *
         */
        private function includes() {
            if ( function_exists( 'spl_autoload_register' ) ) {
                spl_autoload_register( array( $this, 'autoload' ) );
                $this->do_autoload = true;
            }
            
            // Load the WP abstraction file so GamPress can run on all WordPress setups.
            require( $this->includes_dir . 'core/wpabstraction.php' );
            
            // Setup the versions (after we include multisite abstraction above)
            $this->versions();
            
            /** Update/Install ****************************************************/
            // Theme compatibility
            require( $this->includes_dir . 'core/template-loader.php'     );
            require( $this->includes_dir . 'core/theme-compatibility.php' );
            
            // Require all of the GamPress core libraries
            require( $this->includes_dir . 'core/taxonomy.php'           );
            require( $this->includes_dir . 'core/actions.php'            );
            require( $this->includes_dir . 'core/filters.php'            );
            require( $this->includes_dir . 'core/caps.php'               );
            require( $this->includes_dir . 'core/dependency.php'         );
            require( $this->includes_dir . 'core/template.php'           );
            require( $this->includes_dir . 'core/options.php'            );
            require( $this->includes_dir . 'core/functions.php'          );
            require( $this->includes_dir . 'core/catchuri.php'           );
            require( $this->includes_dir . 'core/update.php'             );
            require( $this->includes_dir . 'core/loader.php'             );
            
            if ( ! $this->do_autoload ) {
                require( $this->includes_dir . 'core/classes.php' );
            }
            GP_Log::Init();
        }
        
        /**
         * 设置默认钩子
         *
         *
         */
        private function setup_actions() {
            // Add actions to plugin activation and deactivation hooks
            add_action( 'activate_'   . $this->basename, 'gp_activation'   );
            add_action( 'deactivate_' . $this->basename, 'gp_deactivation' );
            
            // If GamPress is being deactivated, do not add any actions
            if ( gp_is_deactivation( $this->basename ) ) {
                return;
            }

            // Array of GamPress core actions
            $actions = array(
                    'setup_theme',
                    'setup_current_user',        // Setup currently logged in user
                    'setup_displayed_user',
                    'activation',
                    'register_theme_directory',
                    'register_theme_packages'
                    );
            
            // Add the actions
            foreach ( $actions as $class_action )  {
                if ( method_exists( $this, $class_action ) ) {
                    add_action( 'gp_' . $class_action, array( $this, $class_action ), 5 );
                }
            }
            do_action_ref_array( 'gp_after_setup_actions', array( &$this ) );
        }
        
        /**
	     * Private method to align the active and database versions.
	     *
	     */
        private function versions() {
            // Get the possible DB versions (boy is this gross)
		    $versions               = array();
		    $versions['1.6-single'] = get_blog_option( $this->root_blog_id, '_gp_db_version' );

		    // 1.6-single exists, so trust it
		    if ( !empty( $versions['1.6-single'] ) ) {
			    $this->db_version_raw = (int) $versions['1.6-single'];

		    // If no 1.6-single exists, use the max of the others
		    } else {
			    $versions['1.2']        = get_site_option(                      'gp-core-db-version' );
			    $versions['1.5-multi']  = get_site_option(                           'gp-db-version' );
			    $versions['1.6-multi']  = get_site_option(                          '_gp_db_version' );
			    $versions['1.5-single'] = get_blog_option( $this->root_blog_id,      'gp-db-version' );

			    // Remove empty array items
			    $versions             = array_filter( $versions );
			    $this->db_version_raw = (int) ( !empty( $versions ) ) ? (int) max( $versions ) : 0;
		    }
        }
        
        /** Public Methods ********************************************************/
        
        public function autoload( $class ) {
            $class_parts = explode( '_', strtolower( $class ) );
            
            if ( 'gp' !== $class_parts[0] ) {
                return;
            }
            
            $components = apply_filters( 'gp_autoload_components', array(
                                                'core',
                                                'members',
                                                'sns',
                                                'sms',
                                                'pays',
                                                'activities',
                                                'votes',
                                                'links',
                                                'messages'
                                                ));
                    
            $irregular_map = array(
                    'GP_Admin'                     => 'core',
                    'GP_Component'                 => 'core',
                    'GP_Email'                     => 'core',
                    'GP_Email_Recipient'           => 'core',
                    'GP_Email_Delivery'            => 'core',
                    'GP_PHPMailer'                 => 'core',
                    'GP_Theme_Compat'              => 'core',
                    'GP_Log'                       => 'core',
                    'GP_FileLog'                   => 'core',

                    'GP_Signup'                    => 'members',
                    );
                    
            $component = null;
            
            if ( isset( $irregular_map[ $class ] ) ) {
                $component = $irregular_map[ $class ];
                
                // Next chunk is usually the component name.
            } elseif ( in_array( $class_parts[1], $components, true ) ) {
                $component = $class_parts[1];
            }
            
            if ( ! $component ) {
                return;
            }
            
            // Sanitize class name.
            $class = strtolower( str_replace( '_', '-', $class ) );
            
            $includes_dir = apply_filters( "gp_{$component}_includes_dir" , $this->includes_dir );
            $path = $includes_dir . "/{$component}/classes/class-{$class}.php";
            
            // Sanity check.
            if ( ! file_exists( $path ) ) {
                return;
            }
            
            /*
             * Sanity check 2 - Check if component is active before loading class.
             * Skip if PHPUnit is running, or GamPress is installing for the first time.
             */
            if (
                ! in_array( $component, array( 'core', 'members' ), true ) &&
                    ! gp_is_active( $component ) &&
                    ! function_exists( 'tests_add_filter' )
                ) {
                return;
            }
            
            require $path;
        }
        
        public function metabox_add() {
        }
        
        public function setup_current_user() {
        }
        
        public function setup_displayed_user() {
        }
        
        public function activation() {
        }
        
        public function register_theme_directory() {
            if ( ! gp_do_register_theme_directory() ) {
                return;
            }
                   
            register_theme_directory( $this->old_themes_dir );
        }
        
        public function register_theme_packages() {
            // Register the default theme compatibility package
            gp_register_theme_package( array(
                        'id'      => 'legacy',
                        'name'    => __( 'GamPress Default', 'gampress' ),
                        'version' => gp_get_version(),
                        'dir'     => trailingslashit( $this->themes_dir . '/legacy' ),
                        'url'     => trailingslashit( $this->themes_url . '/legacy' )
                        ) );
                                   
            // Register the basic theme stack. This is really dope.
            gp_register_template_stack( 'get_stylesheet_directory', 10 );
            gp_register_template_stack( 'get_template_directory',   12 );
            gp_register_template_stack( 'gp_get_theme_compat_dir',  14 );
        }

        public function setup_theme() {

            // Bail if something already has this under control
            if ( ! empty( $this->theme_compat->theme ) ) {
                return;
            }

            // Setup the theme package to use for compatibility
            gp_setup_theme_compat( gp_get_theme_package_id() );
        }
    }
    
    /**
     * 获得一个插件实例
     *
     * @return 返回插件 实例
     *
     */
    function gampress() {
        return GamPress::instance();
    }
    
    if ( defined( 'GAMPRESS_LATE_LOAD' ) ) {
        add_action( 'plugins_loaded', 'gampress', (int) GAMPRESS_LATE_LOAD );
        
        // "And now here's something we hope you'll really like!"
    } else {
        $GLOBALS['gp'] = gampress();
    }
endif;