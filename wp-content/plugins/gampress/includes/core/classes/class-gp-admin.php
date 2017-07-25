<?php

/**
 * Main GamPress Admin Class
 * ⊙▂⊙
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GP_Admin' ) ) :
    
class GP_Admin {
    
    public function __construct() {
        $this->setup_globals();
        $this->includes();
        $this->setup_actions();
    }
    
    private function setup_globals() {
        $gp = gampress();
        $this->admin_dir  = trailingslashit( $gp->includes_dir . 'core/admin'  ); // Admin path
        $this->admin_url  = trailingslashit( $gp->includes_url . 'core/admin'  ); // Admin url
        
        $this->images_url = trailingslashit( $this->admin_url   . 'images' ); // Admin images URL
        $this->styles_url = trailingslashit( $this->admin_url   . 'styles' ); // Admin styles URL
        $this->css_url    = trailingslashit( $this->admin_url   . 'css'    ); // Admin css URL
        $this->js_url     = trailingslashit( $this->admin_url   . 'js'     ); // Admin js URL
        
        // Main settings page
        $this->settings_page = gp_core_do_network_admin() ? 'settings.php' : 'options-general.php';
        // Main capability.
	    $this->capability = gp_core_do_network_admin() ? 'manage_network_options' : 'manage_options';
    }
    
    private function includes() {
        if ( ! gampress()->do_autoload ) {
            require( $this->admin_dir . 'gp-core-admin-classes.php'    );
        }
        
        require( $this->admin_dir . 'actions.php'   );
        require( $this->admin_dir . 'functions.php' );
        require( $this->admin_dir . 'components.php' );
        require( $this->admin_dir . 'slugs.php' );

        if ( gp_is_active( 'sns' ) )
            require( $this->admin_dir . 'sns.php' );

        if ( gp_is_active( 'sms' ) )
            require( $this->admin_dir . 'sms.php' );

        if ( gp_is_active( 'pays' ) )
            require( $this->admin_dir . 'pays.php' );

        require( $this->admin_dir . 'settings.php' );
    }
    
    private function setup_actions() {
    
        /** General Actions ***************************************************/
        add_action( 'gp_admin_head',            array( $this, 'admin_head'  ), 999 );
        add_action( 'admin_menu',               array( $this, 'site_admin_menus' ), 5 );
        add_action( gp_core_admin_hook(),       array( $this, 'admin_menus' ), 5 );
        
        add_action( 'gp_admin_enqueue_scripts', array( $this, 'admin_register_styles' ), 1 );
        add_action( 'gp_admin_enqueue_scripts', array( $this, 'admin_register_scripts' ), 1 );
        add_action( 'gp_admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'admin_bar_menu', array( $this, 'admin_bar_about_link' ), 15 );
        
        add_filter( 'plugin_action_links',          array( $this, 'modify_plugin_action_links' ), 10, 2 );// 在插件页面设置链接
        add_filter( 'gp_admin_footer_text',         array( $this, 'admin_footer_text'          )     ); 
        
        // Add settings.
		add_action( 'gp_register_admin_settings', array( $this, 'register_admin_settings' ) );

    }
    
    
    /**
     * 设置admin的菜单
     *
     * @return mixed This is the return value description
     *
     */
    public function admin_menus() {   
        // Bail if user cannot moderate.
        if ( ! gp_current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // About.
        add_dashboard_page(
                __( 'Welcome to GamPress',  'gampress' ),
                __( 'Welcome to GamPress',  'gampress' ),
                'manage_options',
                'gp-about',
                array( $this, 'about_screen' )
                ); 
        // Credits.
		add_dashboard_page(
			__( 'Welcome to GamPress',  'gampress' ),
			__( 'Welcome to GamPress',  'gampress' ),
			'manage_options',
			'gp-credits',
			array( $this, 'credits_screen' )
		);
           
        $hooks = array();
        // Changed in BP 1.6 . See gp_core_admin_backpat_menu().
		$hooks[] = add_menu_page(
			__( 'GamPress', 'gampress' ),
			__( 'GamPress', 'gampress' ),
			$this->capability,
			'gp-general-settings',
			'gp_core_admin_backpat_menu',
			'div'
		);

		$hooks[] = add_submenu_page(
			'gp-general-settings',
			__( 'GamPress Help', 'gampress' ),
			__( 'Help', 'gampress' ),
			$this->capability,
			'gp-general-settings',
			'gp_core_admin_backpat_page'
		);
        
        $hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'GamPress Pages', 'gampress' ),
			__( 'GamPress Pages', 'gampress' ),
			$this->capability,
			'gp-page-settings',
			'gp_core_admin_slugs_settings'
		);

        $hooks[] = add_submenu_page(
            'gp-settings',
            __( 'GamPress Options', 'gampress' ),
            __( 'GamPress Options', 'gampress' ),
            $this->capability,
            'gp-settings',
            'gp_core_admin_settings'
        );

		// Add the option pages.
		$hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'GamPress Components', 'gampress' ),
			__( 'GamPress', 'gampress' ),
			$this->capability,
			'gp-components',
			'gp_core_admin_components_settings'
		);

        if ( gp_is_active( 'sns' ) ) {
            $hooks[] = add_submenu_page(
                $this->settings_page,
                __('GamPress Sns', 'gampress'),
                __('GamPress Sns', 'gampress'),
                $this->capability,
                'gp-sns-settings',
                'gp_core_admin_sns_settings'
            );
        }

        if ( gp_is_active( 'sms' ) ) {
            $hooks[] = add_submenu_page(
                $this->settings_page,
                __('GamPress Sms', 'gampress'),
                __('GamPress Sms', 'gampress'),
                $this->capability,
                'gp-sms-settings',
                'gp_core_admin_sms_settings'
            );
        }

        if ( gp_is_active( 'pays' ) ) {
            $hooks[] = add_submenu_page(
                $this->settings_page,
                __('GamPress Pays', 'gampress'),
                __('GamPress Pays', 'gampress'),
                $this->capability,
                'gp-pays-settings',
                'gp_core_admin_pays_settings'

            );
        }

		// For consistency with non-Multisite, we add a Tools menu in
		// the Network Admin as a home for our Tools panel.
		if ( is_multisite() && gp_core_do_network_admin() ) {
			$tools_parent = 'network-tools';

			$hooks[] = add_menu_page(
				__( 'Tools', 'gampress' ),
				__( 'Tools', 'gampress' ),
				$this->capability,
				$tools_parent,
				'gp_core_tools_top_level_item',
				'',
				24 // Just above Settings.
			);

			$hooks[] = add_submenu_page(
				$tools_parent,
				__( 'Available Tools', 'gampress' ),
				__( 'Available Tools', 'gampress' ),
				$this->capability,
				'available-tools',
				'gp_core_admin_available_tools_page'
			);
		} else {
			$tools_parent = 'tools.php';
		}

		$hooks[] = add_submenu_page(
			$tools_parent,
			__( 'GamPress Tools', 'gampress' ),
			__( 'GamPress', 'gampress' ),
			$this->capability,
			'gp-tools',
			'gp_core_admin_tools'
		);

		// For network-wide configs, add a link to (the root site's) Emails screen.
		if ( is_network_admin() && gp_is_network_activated() ) {
			$email_labels = gp_get_email_post_type_labels();
			$email_url    = get_admin_url( gp_get_root_blog_id(), 'edit.php?post_type=' . gp_get_email_post_type() );

			$hooks[] = add_menu_page(
				$email_labels['name'],
				$email_labels['menu_name'],
				$this->capability,
				'',
				'',
				'dashicons-email',
				26
			);

			// Hack: change the link to point to the root site's admin, not the network admin.
			$GLOBALS['menu'][26][2] = esc_url_raw( $email_url );
		}

		foreach( $hooks as $hook ) {
			add_action( "admin_head-$hook", 'gp_core_modify_admin_menu_highlight' );
		}
    }
    
    public function site_admin_menus() {
        if ( ! gp_current_user_can( 'manage_options' ) ) {
            return;
        }
    }
    
    public function admin_head() {
        // Settings pages.
        remove_submenu_page( $this->settings_page, 'gp-page-settings' );
		remove_submenu_page( $this->settings_page, 'gp-sns-settings' );
        remove_submenu_page( $this->settings_page, 'gp-sms-settings' );
        remove_submenu_page( $this->settings_page, 'gp-pays-settings' );

		// Network Admin Tools.
		remove_submenu_page( 'network-tools', 'network-tools' );

		// About and Credits pages.
		remove_submenu_page( 'index.php', 'gp-about'   );
		remove_submenu_page( 'index.php', 'gp-credits' );
    }
    
    public function admin_footer_text() {
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style( 'gp-admin-common-css' );
    }
    
    public function enqueue_styles() {
        $version = gp_get_version();
        
        wp_enqueue_style('gp-admin', $this->css_url . 'gp_admin.css', array(), $version);
        wp_enqueue_style('ui-lightness', $this->css_url . 'ui-lightness.css', array(), $version);
    }
    
    public function admin_register_styles() {
    }
    
    public function admin_register_scripts() {
    }

    public function admin_bar_about_link( $wp_admin_bar ) {
        if ( is_user_logged_in() ) {
            $wp_admin_bar->add_menu( array(
                        'parent' => 'wp-logo',
                        'id'     => 'gp-about',
                        'title'  => esc_html__( 'About GamPress', 'gampress' ),
                        'href'   => add_query_arg( array( 'page' => 'gp-about' ), gp_get_admin_url( 'index.php' ) ),
                        ) );
        }
    }
    
    public function modify_plugin_action_links( $links, $file ) {
        // Return normal links if not GamPress.
        if ( plugin_basename( gampress()->basename ) != $file ) {
            return $links;
        }
        
        // Add a few links to the existing links array.
        return array_merge( $links, array(
                    'settings' => '<a href="' . esc_url( add_query_arg( array( 'page' => 'gp-components' ), gp_get_admin_url( $this->settings_page ) ) ) . '">' . esc_html__( 'Settings', 'gampress' ) . '</a>',
                    'about'    => '<a href="' . esc_url( add_query_arg( array( 'page' => 'gp-about'      ), gp_get_admin_url( 'index.php'          ) ) ) . '">' . esc_html__( 'About',    'gampress' ) . '</a>'
                    ) );
    }
    
    public function register_admin_settings() {

        /* Main Section ******************************************************/

        // Add the main section.
        add_settings_section( 'gp_main', __( 'Main Settings', 'gampress' ), 'gp_admin_setting_callback_main_section', 'gampress' );

        add_settings_field( 'page-keywords', __( 'Page Keywords', 'gampress' ), 'gp_admin_setting_callback_page_keywords', 'gampress', 'gp_main' );
        register_setting( 'gampress', 'page-keywords', '' );

        add_settings_field( 'page-description', __( 'Page Description', 'gampress' ), 'gp_admin_setting_callback_page_description', 'gampress', 'gp_main' );
        register_setting( 'gampress', 'page-description', '' );

    }
    
    /** About *****************************************************************/
    public function about_screen() {
		$embedded_activity = '';

		if ( version_compare( $GLOBALS['wp_version'], '4.5', '>=' ) ) {
			$embedded_activity = wp_oembed_get( 'https://gampress.org/members/djpaul/activity/573821/' );
		}
	?>
        <div class="wrap about-wrap">
            <?php self::welcome_text(); ?>
            
            <?php self::tab_navigation( __METHOD__ ); ?>
            
            <?php if ( self::is_new_install() ) : ?>
            
            <?php endif;?>
        </div>
    <?php
    }
    
    public function welcome_text() {
        // Switch welcome text based on whether this is a new installation or not.
		$welcome_text = ( self::is_new_install() )
			? __( 'Thank you for installing GamPress! GamPress helps site builders and WordPress developers add community features to their websites, with user profile fields, activity streams, messaging, and notifications.', 'gampress' )
			: __( 'Thank you for updating! GamPress %s has many new features that you will enjoy.', 'gampress' );

		?>

		<h1><?php printf( esc_html__( 'Welcome to GamPress %s', 'gampress' ), self::display_version() ); ?></h1>

		<div class="about-text">
			<?php
			if ( self::is_new_install() ) {
				echo $welcome_text;
			} else {
				printf( $welcome_text, self::display_version() );
			}
			?>
		</div>

		<div class="gp-badge"></div>

		<?php
    }
    
    public static function display_version() {
        // Use static variable to prevent recalculations.
		static $display = '';

		// Only calculate on first run.
		if ( '' === $display ) {

			// Get current version.
			$version = gp_get_version();

			// Check for prerelease hyphen.
			$pre     = strpos( $version, '-' );

			// Strip prerelease suffix.
			$display = ( false !== $pre )
				? substr( $version, 0, $pre )
				: $version;
		}

		// Done!
		return $display;
    }
    
    public static function is_new_install() {
		return (bool) isset( $_GET['is_new_install'] );
	}
    
    public static function tab_navigation( $tab = 'whats_new' ) {
	?>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( 'GP_Admin::about_screen' === $tab ) : ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( gp_get_admin_url( add_query_arg( array( 'page' => 'gp-about' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'What&#8217;s New', 'gampress' ); ?>
			</a><a class="nav-tab <?php if ( 'GP_Admin::credits_screen' === $tab ) : ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( gp_get_admin_url( add_query_arg( array( 'page' => 'gp-credits' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Credits', 'gampress' ); ?>
			</a>
		</h2>

	<?php
	}
}
    
endif;