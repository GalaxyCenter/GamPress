<?php

/**
 * GamPress Core Functions
 * 
 * ⊙▂⊙
 * 
 * @package core
 * @sugpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_version() {
    echo gp_get_version();
}

function gp_get_version() {
    return gampress()->version;
}

function gp_db_version_raw() {
    echo gp_get_db_version_raw();
}

function gp_get_db_version_raw() {
    return get_option( '_gp_db_version', '' );
}

function gp_db_version() {
    echo gp_get_db_version();
}

function gp_get_db_version() {
    return gampress()->db_version;
}

function gp_admin_url( $path = '', $scheme = 'admin' ) {
    echo gp_get_admin_url( $path, $scheme );
}

function gp_get_admin_url( $path = '', $scheme = 'admin' ) {
    $url = admin_url( $path, $scheme );
    
    return $url;
}

function gp_esc_sql_order( $order = '' ) {
    $order = strtoupper( trim( $order ) );
    return 'DESC' === $order ? 'DESC' : 'ASC';
}

//function gp_get_order_status() {
//    $types = array(GP_ORDER_NORMAL => 'normal');
//    
//    return $types;
//}

function gp_get_root_blog_id() {
    $gp = gampress();
        
    return  (int) $gp->root_blog_id;
}

function gp_core_referrer() {
    $referer = explode( '/', wp_get_referer() );
    unset( $referer[0], $referer[1], $referer[2] );
    return implode( '/', $referer );
}

function gp_core_update_directory_page_ids( $blog_page_ids ) {
	gp_update_option( 'gp-pages', $blog_page_ids );
}

function gp_core_get_directory_page_ids( $status = 'active'  ) {
    $page_ids = gp_get_option( 'gp-pages' );
    
    // Ensure that empty indexes are unset. Should only matter in edge cases
    if ( !empty( $page_ids ) && is_array( $page_ids ) ) {
        foreach( (array) $page_ids as $component_name => $page_id ) {
            if ( empty( $component_name ) || empty( $page_id ) ) {
				unset( $page_ids[ $component_name ] );
			}

			// 'signup' and 'activate' do not have components, but should be whitelisted.
			if ( 'signup' === $component_name || 'activate' === $component_name ) {
				continue;
			}

			// Trashed pages should not appear in results.
			if ( 'trash' == get_post_status( $page_id ) ) {
				unset( $page_ids[ $component_name ] );

			}

			// Remove inactive component pages, if required.
			if ( 'active' === $status && ! gp_is_active( $component_name ) ) {
				unset( $page_ids[ $component_name ] );
			}
        }
    }
    
    return $page_ids;
}

function gp_update_is_directory( $is_directory = false, $component = '' ) {
    global $gp;
    
    if ( empty( $component ) )
        $component = cp_current_component();
    
    $gp->is_directory = apply_filters( 'gp_update_is_directory', $is_directory, $component );
}

function gp_core_get_directory_page_default_titles() {
    $page_default_titles = array(
            'members'       => _x( 'Members',     'Page title for the Members screen.', 'gampress' ),
            'sns'           => _x( 'Sns',         'Page title for the Sns screen.', 'gampress' ),
            'sms'           => _x( 'Sms',         'Page title for the Sms screen.', 'gampress' ),
            'pays'          => _x( 'Pays',        'Page title for the Pays screen.', 'gampress' ),
            'activate'      => _x( 'Activate',    'Page title for the user activation screen.',   'gampress' ),
            'signup'        => _x( 'Signup',      'Page title for the user Signup screen.', 'gampress' ),
            'activities'    => _x( 'Activities',  'Page title for the Activities screen.', 'gampress' ),
            'links'         => _x( 'Links',       'Page title for the Link screen.', 'gampress' ),
            );
            
    return apply_filters( 'gp_core_get_directory_page_default_titles', $page_default_titles );
}

function gp_core_get_directory_pages() {
    global $wpdb;
    
    // Set pages as standard class
    $pages = new stdClass;
    
    // Get pages and IDs
    $page_ids = gp_core_get_directory_page_ids();
    if ( !empty( $page_ids ) ) {
        
        // Always get page data from the root blog, except on multiblog mode, when it comes
        // from the current blog
        $posts_table_name = $wpdb->get_blog_prefix( gp_get_root_blog_id() ) . 'posts';
        $page_ids_sql     = implode( ',', wp_parse_id_list( $page_ids ) );
        $page_names       = $wpdb->get_results( "SELECT ID, post_name, post_parent, post_title FROM {$posts_table_name} WHERE ID IN ({$page_ids_sql}) AND post_status = 'publish' " );
        
        foreach ( (array) $page_ids as $component_id => $page_id ) {
            foreach ( (array) $page_names as $page_name ) {
                if ( $page_name->ID == $page_id ) {
                    if ( !isset( $pages->{$component_id} ) || !is_object( $pages->{$component_id} ) ) {
                        $pages->{$component_id} = new stdClass;
                    }
                    
                    $pages->{$component_id}->name  = $page_name->post_name;
                    $pages->{$component_id}->id    = $page_name->ID;
                    $pages->{$component_id}->title = $page_name->post_title;
                    $slug[]                        = $page_name->post_name;
                    
                    // Get the slug
                    while ( $page_name->post_parent != 0 ) {
                        $parent                 = $wpdb->get_results( $wpdb->prepare( "SELECT post_name, post_parent FROM {$posts_table_name} WHERE ID = %d", $page_name->post_parent ) );
                        $slug[]                 = $parent[0]->post_name;
                        $page_name->post_parent = $parent[0]->post_parent;
                    }
                    
                    $pages->{$component_id}->slug = implode( '/', array_reverse( (array) $slug ) );
                }
                
                unset( $slug );
            }
        }
    }
    
    return $pages;
}

function gp_core_get_site_path() {
    global $current_site;
    
    if ( is_multisite() )
        $site_path = $current_site->path;
    else {
        $site_path = (array) explode( '/', home_url() );
        
        if ( count( $site_path ) < 2 )
            $site_path = '/';
        else {
            // Unset the first three segments (http(s)://domain.com part)
            unset( $site_path[0] );
            unset( $site_path[1] );
            unset( $site_path[2] );
            
            if ( !count( $site_path ) )
                $site_path = '/';
            else
                $site_path = '/' . implode( '/', $site_path ) . '/';
        }
    }
    
    return $site_path;
}

function gp_core_enable_root_profiles() {
    
    $retval = false;
    
    if ( defined( 'GP_ENABLE_ROOT_PROFILES' ) && ( true == GP_ENABLE_ROOT_PROFILES ) )
        $retval = true;
    
    return $retval;
}

function gp_is_root_blog( $blog_id = 0 ) {
    
    // Assume false
    $is_root_blog = false;
    
    // Use current blog if no ID is passed
    if ( empty( $blog_id ) )
        $blog_id = get_current_blog_id();
    
    // Compare to root blog ID
    if ( $blog_id == gp_get_root_blog_id() )
        $is_root_blog = true;
    
    return (bool) $is_root_blog;
}

function gp_core_add_page_mappings( $components, $existing = 'keep' ) {
    if ( empty( $components ) ) {
		return;
	}
    
    // Make sure that the pages are created on the root blog no matter which Dashboard the setup is being run on
    if ( ! gp_is_root_blog() )
        switch_to_blog( gp_get_root_blog_id() );
    
    $pages = gp_core_get_directory_page_ids( 'all' );
    
    // Delete any existing pages
    if ( 'delete' == $existing ) {
        foreach ( (array) $pages as $page_id ) {
            wp_delete_post( $page_id, true );
        }
        
        $pages = array();
    }
    
    $page_titles = gp_core_get_directory_page_default_titles();
    
    $pages_to_create = array();
    foreach ( array_keys( $components ) as $component_name ) {
        if ( ! isset( $pages[ $component_name ] ) && isset( $page_titles[ $component_name ] ) ) {
            $pages_to_create[ $component_name ] = $page_titles[ $component_name ];
        }
    }
    
    // Register and Activate are not components, but need pages when
	// registration is enabled.
	if ( gp_get_signup_allowed() ) {
		foreach ( array( 'signup', 'activate' ) as $slug ) {
			if ( ! isset( $pages[ $slug ] ) ) {
				$pages_to_create[ $slug ] = $page_titles[ $slug ];
			}
		}
	}
    
    // Create the pages
    foreach ( $pages_to_create as $component_name => $page_name ) {
        $exists = get_page_by_path( $component_name );

		// If page already exists, use it.
		if ( ! empty( $exists ) ) {
			$pages[ $component_name ] = $exists->ID;
		} else {
			$pages[ $component_name ] = wp_insert_post( array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_title'     => $page_name,
				'post_type'      => 'page',
			) );
		}
    }
    
    // Save the page mapping
    gp_update_option( 'gp-pages', $pages );
    
    // If we had to switch_to_blog, go back to the original site.
    if ( ! gp_is_root_blog() )
        restore_current_blog();
}

function gp_core_current_time( $gmt = false ) {
    $current_time = current_time( 'mysql', $gmt );
    
    return $current_time;
}

function gp_core_get_root_domain() {
    
    $domain = get_home_url( gp_get_root_blog_id() );
    
    return $domain;
}

function gp_core_redirect( $location = '', $status = 302 ) {
    
    // On some setups, passing the value of wp_get_referer() may result in an
    // empty value for $location, which results in an error. Ensure that we
    // have a valid URL.
    if ( empty( $location ) )
        $location = gp_get_root_domain();
    
    // Make sure we don't call status_header() in gp_core_do_catch_uri() as this
    // conflicts with wp_redirect() and wp_safe_redirect().
    gampress()->no_status_set = true;

    wp_safe_redirect( $location, $status );
    die;
}

function gp_is_username_compatibility_mode() {
    return defined( 'GP_ENABLE_USERNAME_COMPATIBILITY_MODE' ) && GP_ENABLE_USERNAME_COMPATIBILITY_MODE;
}

function gp_core_get_userid_from_nicename( $user_nicename ) {
    global $wpdb;
    
    if ( empty( $user_nicename ) )
        return false;
    
    return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE user_nicename = %s", $user_nicename ) );
}

function gp_do_404( $redirect = 'remove_canonical_direct' ) {
    global $wp_query;
    
    do_action( 'gp_do_404', $redirect );
    
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
    
    if ( 'remove_canonical_direct' === $redirect ) {
        remove_action( 'template_redirect', 'redirect_canonical' );
    }
}

function gp_do_register_theme_directory() {
	// If gp-default exists in another theme directory, bail.
	// This ensures that the version of gp-default in the regular themes
	// directory will always take precedence, as part of a migration away
	// from the version packaged with BuddyPress.
	foreach ( array_values( (array) $GLOBALS['wp_theme_directories'] ) as $directory ) {
		if ( is_dir( $directory . '/gp-default' ) ) {
			return false;
		}
	}

	// If the current theme is gp-default (or a gp-default child), BP
	// should register its directory.
	$register = 'gp-default' === get_stylesheet() || 'gp-default' === get_template();

	// Legacy sites continue to have the theme registered.
	if ( empty( $register ) && ( 1 == get_site_option( '_gp_retain_gp_default' ) ) ) {
		$register = true;
	}

	return apply_filters( 'gp_do_register_theme_directory', $register );
}

function gp_core_setup_message() {

	$gp = gampress();

	if ( empty( $gp->template_message ) && isset( $_COOKIE['gp-message'] ) ) {
		$gp->template_message = stripslashes( $_COOKIE['gp-message'] );
	}

	if ( empty( $gp->template_message_type ) && isset( $_COOKIE['gp-message-type'] ) ) {
		$gp->template_message_type = stripslashes( $_COOKIE['gp-message-type'] );
	}

	add_action( 'template_notices', 'gp_core_render_message' );

    if ( isset( $_COOKIE['gp-message'] ) ) {
        @setcookie( 'gp-message', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
    }

    if ( isset( $_COOKIE['gp-message-type'] ) ) {
        @setcookie( 'gp-message-type', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
    }
}
add_action( 'gp_actions', 'gp_core_setup_message', 5 );

function gp_core_add_message( $message, $type = '' ) {
    
    // Success is the default
    if ( empty( $type ) ) {
        $type = 'success';
    }
    
    // Send the values to the cookie for page reload display
    @setcookie( 'gp-message',      $message, time() + 60 * 60 * 24, COOKIEPATH );
    @setcookie( 'gp-message-type', $type,    time() + 60 * 60 * 24, COOKIEPATH );
    
    $gp = gampress();
    
    /***
     * Send the values to the $gp global so we can still output messages
     * without a page reload
     */
    $gp->template_message      = $message;
    $gp->template_message_type = $type;
}

function gp_core_render_message() {

	$gp = gampress();

	if ( !empty( $gp->template_message ) ) :
		$type    = ( 'success' === $gp->template_message_type ) ? 'updated' : 'error';
		$content = apply_filters( 'gp_core_render_message_content', $gp->template_message, $type ); ?>

		<div id="message" class="gp-template-notice updated notice is-dismissible <?php echo esc_attr( $type ); ?>">

            <p>
			<?php echo $content; ?>
            </p>

		</div>

	<?php

		do_action( 'gp_core_render_message' );

	endif;
}

function gp_core_get_components( $type = 'all' ) {
	$required_components = array(
		'core' => array(
			'title'       => __( 'GamPress Core', 'gampress' ),
			'description' => __( 'It&#8216;s what makes <del>time travel</del> GamPress possible!', 'gampress' )
		),
		'members' => array(
			'title'       => __( 'GamPress Members', 'gampress' ),
			'description' => __( 'Everything in a GamPress community revolves around its members.', 'gampress' )
		),
	);

	$retired_components = array(
	);

	$optional_components = array(
		'sns' => array(
			'title'       => __( 'Sns', 'gampress' ),
			'description' => __( 'Wechat,Weibo,QQ login', 'gampress' )
		),
        'sms' => array(
            'title'       => __( 'Sms', 'gampress' ),
            'description' => __( 'Sms', 'gampress' )
        ),
        'pays' => array(
            'title'       => __( 'Pays', 'gampress' ),
            'description' => __( 'Alipay', 'gampress' )
        ),
        'activities' => array(
            'title'       => __( 'Activities', 'gampress' ),
            'description' => __( 'Activities', 'gampress' )
        ),
        'votes' => array(
            'title'       => __( 'Votes', 'gampress' ),
            'description' => __( 'Votes', 'gampress' )
        ),
        'links' => array(
            'title'       => __( 'Links', 'gampress' ),
            'description' => __( 'Links', 'gampress' )
        ),
	);

	// Add blogs tracking if multisite.
	if ( is_multisite() ) {
		$optional_components['blogs']['description'] = __( 'Record activity for new sites, posts, and comments across your network.', 'gampress' );
	}

	switch ( $type ) {
		case 'required' :
			$components = $required_components;
			break;
		case 'optional' :
			$components = $optional_components;
			break;
		case 'retired' :
			$components = $retired_components;
			break;
		case 'all' :
		default :
			$components = array_merge( $required_components, $optional_components, $retired_components );
			break;
	}

	return apply_filters( 'gp_core_get_components', $components, $type );
}

function gp_is_multiblog_mode() {

	// Setup some default values.
	$retval         = false;
	$is_multisite   = is_multisite();
	$network_active = gp_is_network_activated();
	$is_multiblog   = defined( 'GP_ENABLE_MULTIBLOG' ) && GP_ENABLE_MULTIBLOG;

	// Multisite, Network Activated, and Specifically Multiblog.
	if ( $is_multisite && $network_active && $is_multiblog ) {
		$retval = true;

	// Multisite, but not network activated.
	} elseif ( $is_multisite && ! $network_active ) {
		$retval = true;
	}

	return $retval;
}

function gp_is_network_activated() {

	// Default to is_multisite().
	$retval  = is_multisite();

	// Check the sitewide plugins array.
	$base    = gampress()->basename;
	$plugins = get_site_option( 'active_sitewide_plugins' );

	// Override is_multisite() if not network activated.
	if ( ! is_array( $plugins ) || ! isset( $plugins[ $base ] ) ) {
		$retval = false;
	}

	return (bool) $retval;
}

function gp_core_do_network_admin() {

	// Default.
	$retval = gp_is_network_activated();

	if ( gp_is_multiblog_mode() ) {
		$retval = false;
	}

	return $retval;
}

function gp_core_admin_hook() {
	$hook = gp_core_do_network_admin() ? 'network_admin_menu' : 'admin_menu';

	return apply_filters( 'gp_core_admin_hook', $hook );
}

function gp_core_get_table_prefix() {
	global $wpdb;

	return apply_filters( 'gp_core_get_table_prefix', $wpdb->base_prefix );
}

function gp_esc_like( $text ) {
	global $wpdb;

	if ( method_exists( $wpdb, 'esc_like' ) ) {
		return $wpdb->esc_like( $text );
	} else {
		return addcslashes( $text, '_%\\' );
	}
}

function gp_core_define() {
}
add_action( 'gp_setup_globals', 'gp_core_define', 11 );

function _gp_strip_spans_from_title( $title_part = '' ) {
	$title = $title_part;
	$span = strpos( $title, '<span' );
	if ( false !== $span ) {
		$title = substr( $title, 0, $span - 1 );
	}
	return trim( $title );
}

function gp_send_email( $email_type, $to, $args = array() ) {
	static $is_default_wpmail = null;
	static $wp_html_emails    = null;

	// Has wp_mail() been filtered to send HTML emails?
	if ( is_null( $wp_html_emails ) ) {
		/** This filter is documented in wp-includes/pluggable.php */
		$wp_html_emails = apply_filters( 'wp_mail_content_type', 'text/plain' ) === 'text/html';
	}

	// Since wp_mail() is a pluggable function, has it been re-defined by another plugin?
	if ( is_null( $is_default_wpmail ) ) {
		try {
			$mirror            = new ReflectionFunction( 'wp_mail' );
			$is_default_wpmail = substr( $mirror->getFileName(), -strlen( 'pluggable.php' ) ) === 'pluggable.php';
		} catch ( Exception $e ) {
			$is_default_wpmail = true;
		}
	}

	$args = wp_parse_args( $args, array(
		'tokens' => array(),
	), 'send_email' );


	/*
	 * Build the email.
	 */

	$email = gp_get_email( $email_type );
	if ( is_wp_error( $email ) ) {
		return $email;
	}

	// From, subject, content are set automatically.
	$email->set_to( $to );
	$email->set_tokens( $args['tokens'] );

	$status = $email->validate();
	if ( is_wp_error( $status ) ) {
		return $status;
	}

	$must_use_wpmail = apply_filters( 'gp_email_use_wp_mail', $wp_html_emails || ! $is_default_wpmail );

	if ( $must_use_wpmail ) {
		$to = $email->get( 'to' );

		return wp_mail(
			array_shift( $to )->get_address(),
			$email->get( 'subject', 'replace-tokens' ),
			$email->get( 'content_plaintext', 'replace-tokens' )
		);
	}


	/*
	 * Send the email.
	 */

	$delivery_class = apply_filters( 'gp_send_email_delivery_class', 'GP_PHPMailer', $email_type, $to, $args );
	if ( ! class_exists( $delivery_class ) ) {
		return new WP_Error( 'missing_class', __CLASS__, $this );
	}

	$delivery = new $delivery_class();
	$status   = $delivery->gp_email( $email );

	if ( is_wp_error( $status ) ) {

		do_action( 'gp_send_email_failure', $status, $email );

	} else {

		do_action( 'gp_send_email_success', $status, $email );
	}

	return $status;
}

function gp_get_email( $email_type ) {
	$switched = false;

	// Switch to the root blog, where the email posts live.
	if ( ! gp_is_root_blog() ) {
		switch_to_blog( gp_get_root_blog_id() );
		$switched = true;
	}

	$args = array(
		'no_found_rows'    => true,
		'numberposts'      => 1,
		'post_status'      => 'publish',
		'post_type'        => gp_get_email_post_type(),
		'suppress_filters' => false,

		'tax_query'        => array(
			array(
				'field'    => 'slug',
				'taxonomy' => gp_get_email_tax_type(),
				'terms'    => $email_type,
			)
		),
	);

	$args = apply_filters( 'gp_get_email_args', $args, $email_type );
	$post = get_posts( $args );
	if ( ! $post ) {
		if ( $switched ) {
			restore_current_blog();
		}

		return new WP_Error( 'missing_email', __FUNCTION__, array( $email_type, $args ) );
	}
	$post  = apply_filters( 'gp_get_email_post', $post[0], $email_type, $args, $post );
	$email = new GP_Email( $email_type );


	/*
	 * Set some email properties for convenience.
	 */

	// Post object (sets subject, content, template).
	$email->set_post_object( $post );

	$retval = apply_filters( 'gp_get_email', $email, $email_type, $args, $post );

	if ( $switched ) {
		restore_current_blog();
	}

	return $retval;
}

function gp_email_tax_type() {
	echo gp_get_email_tax_type();
}

	function gp_get_email_tax_type() {
		return apply_filters( 'gp_get_email_tax_type', gampress()->email_taxonomy_type );
	}
    
function gp_email_post_type() {
	echo gp_get_email_post_type();
}

	function gp_get_email_post_type() {
		return apply_filters( 'gp_get_email_post_type', gampress()->email_post_type );
	}
    
function gp_email_get_schema() {
	return array(
		 
		'core-user-registration' => array(
			/* translators: do not remove {} brackets or translate its contents. */
			'post_title'   => __( '[{{{site.name}}}] Activate your account', 'gampress' ),
			/* translators: do not remove {} brackets or translate its contents. */
			'post_content' => __( "Thanks for registering!\n\nTo complete the activation of your account, go to the following link: <a href=\"{{{activate.url}}}\">{{{activate.url}}}</a>", 'gampress' ),
			/* translators: do not remove {} brackets or translate its contents. */
			'post_excerpt' => __( "Thanks for registering!\n\nTo complete the activation of your account, go to the following link: {{{activate.url}}}", 'gampress' ),
		)
		 
	);
}

function gp_email_get_type_schema( $field = 'description' ) {
	$core_user_registration = array(
		'description'	=> __( 'Recipient has registered for an account.', 'gampress' ),
		'unsubscribe'	=> false,
	);

	$types = array(
		'core-user-registration'             => $core_user_registration
	);

	if ( $field !== 'all' ) {
		return wp_list_pluck( $types, $field );
	} else {
		return $types;
	}
}

function gp_get_email_tax_type_labels() {
	return apply_filters( 'gp_get_email_tax_type_labels', array(
		'add_new_item'          => _x( 'New Email Situation', 'email type taxonomy label', 'gampress' ),
		'all_items'             => _x( 'All Email Situations', 'email type taxonomy label', 'gampress' ),
		'edit_item'             => _x( 'Edit Email Situations', 'email type taxonomy label', 'gampress' ),
		'items_list'            => _x( 'Email list', 'email type taxonomy label', 'gampress' ),
		'items_list_navigation' => _x( 'Email list navigation', 'email type taxonomy label', 'gampress' ),
		'menu_name'             => _x( 'Situations', 'email type taxonomy label', 'gampress' ),
		'name'                  => _x( 'Situation', 'email type taxonomy name', 'gampress' ),
		'new_item_name'         => _x( 'New email situation name', 'email type taxonomy label', 'gampress' ),
		'not_found'             => _x( 'No email situations found.', 'email type taxonomy label', 'gampress' ),
		'no_terms'              => _x( 'No email situations', 'email type taxonomy label', 'gampress' ),
		'popular_items'         => _x( 'Popular Email Situation', 'email type taxonomy label', 'gampress' ),
		'search_items'          => _x( 'Search Emails', 'email type taxonomy label', 'gampress' ),
		'singular_name'         => _x( 'Email', 'email type taxonomy singular name', 'gampress' ),
		'update_item'           => _x( 'Update Email Situation', 'email type taxonomy label', 'gampress' ),
		'view_item'             => _x( 'View Email Situation', 'email type taxonomy label', 'gampress' ),
	) );
}

function gp_get_email_post_type_labels() {
	return apply_filters( 'gp_get_email_post_type_labels', array(
		'add_new'               => _x( 'Add New', 'email post type label', 'gampress' ),
		'add_new_item'          => _x( 'Add a New Email', 'email post type label', 'gampress' ),
		'all_items'             => _x( 'All Emails', 'email post type label', 'gampress' ),
		'edit_item'             => _x( 'Edit Email', 'email post type label', 'gampress' ),
		'filter_items_list'     => _x( 'Filter email list', 'email post type label', 'gampress' ),
		'items_list'            => _x( 'Email list', 'email post type label', 'gampress' ),
		'items_list_navigation' => _x( 'Email list navigation', 'email post type label', 'gampress' ),
		'menu_name'             => _x( 'Emails', 'email post type name', 'gampress' ),
		'name'                  => _x( 'BuddyPress Emails', 'email post type label', 'gampress' ),
		'new_item'              => _x( 'New Email', 'email post type label', 'gampress' ),
		'not_found'             => _x( 'No emails found', 'email post type label', 'gampress' ),
		'not_found_in_trash'    => _x( 'No emails found in Trash', 'email post type label', 'gampress' ),
		'search_items'          => _x( 'Search Emails', 'email post type label', 'gampress' ),
		'singular_name'         => _x( 'Email', 'email post type singular name', 'gampress' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this email', 'email post type label', 'gampress' ),
		'view_item'             => _x( 'View Email', 'email post type label', 'gampress' ),
	) );
}

function gp_get_email_post_type_supports() {

	return apply_filters( 'gp_get_email_post_type_supports', array(
		'custom-fields',
		'editor',
		'excerpt',
		'revisions',
		'title',
	) );
}

function gp_email_get_template( WP_Post $object ) {
	$single = "single-{$object->post_type}";

	return apply_filters( 'gp_email_get_template', array(
		"{$single}-{$object->post_name}.php",
		"{$single}.php",
		"assets/emails/{$single}.php",
	), $object );
}

function gp_core_replace_tokens_in_text( $text, $tokens ) {
    $unescaped = array();
    $escaped   = array();

    foreach ( $tokens as $token => $value ) {
        if ( ! is_string( $value ) && is_callable( $value ) ) {
            $value = call_user_func( $value );
        }

        // Tokens could be objects or arrays.
        if ( ! is_scalar( $value ) ) {
            continue;
        }

        $unescaped[ '{{{' . $token . '}}}' ] = $value;
        $escaped[ '{{' . $token . '}}' ]     = esc_html( $value );
    }

    $text = strtr( $text, $unescaped );  // Do first.
    $text = strtr( $text, $escaped );

    return apply_filters( 'gp_core_replace_tokens_in_text', $text, $tokens );
}

function gp_core_load_gampress_textdomain() {
    $domain = 'gampress';


    $mofile_custom = sprintf( '%s-%s.mo', $domain, apply_filters( 'gampress_locale', get_locale() ) );

    $locations = apply_filters( 'gampress_locale_locations', array(
        trailingslashit( gampress()->lang_dir  ),
        trailingslashit( WP_LANG_DIR . '/' . $domain  ),
        trailingslashit( WP_LANG_DIR ),
    ) );

    // Try custom locations in WP_LANG_DIR.
    foreach ( $locations as $location ) {
        if ( load_textdomain( $domain, $location . $mofile_custom ) ) {
            return true;
        }
    }

    // Default to WP and glotpress.
    return load_plugin_textdomain( $domain );
}
add_action( 'gp_core_loaded', 'gp_core_load_gampress_textdomain' );

function gp_page_keywords() {
    echo gp_get_page_keywords();
}

function gp_get_page_keywords() {
    return gp_get_option( 'page-keywords' );
}

function gp_page_description() {
    echo gp_get_page_description();
}

function gp_get_page_description() {
    return gp_get_option( 'page-description' );
}

function gp_get_metadata($meta_type, $object_id, $meta_key = '', $default_value = false, $single = false) {
    if ( ! $meta_type || ! is_numeric( $object_id ) ) {
        return false;
    }

    $object_id = absint( $object_id );
    if ( ! $object_id ) {
        return false;
    }

    $check = apply_filters( "get_{$meta_type}_metadata", null, $object_id, $meta_key, $single );
    if ( null !== $check ) {
        if ( $single && is_array( $check ) )
            return $check[0];
        else
            return $check;
    }

    $meta_cache = wp_cache_get($object_id, $meta_type . '_meta');

    if ( !$meta_cache ) {
        $meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
        $meta_cache = $meta_cache[$object_id];
    }

    if ( ! $meta_key ) {
        return $meta_cache;
    }

    if ( isset($meta_cache[$meta_key]) ) {
        if ( $single )
            return maybe_unserialize( $meta_cache[$meta_key][0] );
        else
            return array_map('maybe_unserialize', $meta_cache[$meta_key]);
    }

    if ($single)
        return $default_value;
    else
        return (array) $default_value;
}

function gp_parse_args( $args, $defaults = array(), $filter_key = '' ) {
    if ( is_object( $args ) ) {
        $r = get_object_vars( $args );
    } elseif ( is_array( $args ) ) {
        $r =& $args;
    } else {
        wp_parse_str( $args, $r );
    }

    if ( !empty( $filter_key ) ) {
        $r = apply_filters( 'gp_before_' . $filter_key . '_parse_args', $r );
    }

    // Parse.
    if ( is_array( $defaults ) && !empty( $defaults ) ) {
        $r = array_merge( $defaults, $r );
    }

    if ( !empty( $filter_key ) ) {
        $r = apply_filters( 'gp_after_' . $filter_key . '_parse_args', $r );
    }

    return $r;
}
