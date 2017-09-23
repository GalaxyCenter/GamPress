<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_filter( 'gp_get_template_stack', 'gp_add_template_stack_locations' );

/**
 * 注册初始化相关的action
 * 
 */
add_filter( 'allowed_redirect_hosts' , 'gp_allowed_redirect_hosts' , 10 );

function gp_modify_page_title( $title = '', $sep = '&raquo;', $seplocation = 'right' ) {
    global $paged, $page, $_wp_theme_features;
    
    // Get the BuddyPress title parts.
    $gp_title_parts = gp_get_title_parts( $seplocation );
    
    // If not set, simply return the original title.
    if ( ! $gp_title_parts ) {
        return $title;
    }
    
    // Get the blog name, so we can check if the original $title included it.
    $blogname = get_bloginfo( 'name', 'display' );
    
    /**
     * Are we going to fake 'title-tag' theme functionality?
     *
     * @link https://buddypress.trac.wordpress.org/ticket/6107
     * @see wp_title()
     */
    $title_tag_compatibility = (bool) ( ! empty( $_wp_theme_features['title-tag'] ) || ( $blogname && strstr( $title, $blogname ) ) );
    
    // Append the site title to title parts if theme supports title tag.
    if ( true === $title_tag_compatibility ) {
        $gp_title_parts['site'] = $blogname;
        
        if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() && ! gp_is_single_activity() ) {
            $gp_title_parts['page'] = sprintf( __( 'Page %s', 'buddypress' ), max( $paged, $page ) );
        }
    }
    
    // Pad the separator with 1 space on each side.
    $prefix = str_pad( $sep, strlen( $sep ) + 2, ' ', STR_PAD_BOTH );
    
    // Join the parts together.
    $new_title = join( $prefix, array_filter( $gp_title_parts ) );
    
    // Append the prefix for pre `title-tag` compatibility.
    if ( false === $title_tag_compatibility ) {
        $new_title = $new_title . $prefix;
    }
    
    /**
     * Filters the older 'wp_title' page title for BuddyPress pages.
     *
     * @since 1.5.0
     *
     * @param string $new_title   The BuddyPress page title.
     * @param string $title       The original WordPress page title.
     * @param string $sep         The title parts separator.
     * @param string $seplocation Location of the separator (left or right).
     */
    return apply_filters( 'gp_modify_page_title', $new_title, $title, $sep, $seplocation );
}
add_filter( 'wp_title',             'gp_modify_page_title', 20, 3 );
add_filter( 'gp_modify_page_title', 'wptexturize'                 );
add_filter( 'gp_modify_page_title', 'convert_chars'               );
add_filter( 'gp_modify_page_title', 'esc_html'                    );

function gp_email_set_default_tokens( $tokens, $property_name, $transform, $email ) {
    $tokens['site.admin-email'] = gp_get_option( 'admin_email' );
    $tokens['site.url']         = home_url();

    // These options are escaped with esc_html on the way into the database in sanitize_option().
    $tokens['site.description'] = wp_specialchars_decode( gp_get_option( 'blogdescription' ), ENT_QUOTES );
    $tokens['site.name']        = wp_specialchars_decode( gp_get_option( 'blogname' ), ENT_QUOTES );

    // Default values for tokens set conditionally below.
    $tokens['email.preheader']     = '';
    $tokens['recipient.email']     = '';
    $tokens['recipient.name']      = '';
    $tokens['recipient.username']  = '';


    // Who is the email going to?
    $recipient = $email->get( 'to' );
    if ( $recipient ) {
        $recipient = array_shift( $recipient );
        $user_obj  = $recipient->get_user( 'search-email' );

        $tokens['recipient.email'] = $recipient->get_address();
        $tokens['recipient.name']  = $recipient->get_name();

        if ( ! $user_obj && $tokens['recipient.email'] ) {
            $user_obj = get_user_by( 'email', $tokens['recipient.email'] );
        }

        if ( $user_obj ) {
            $tokens['recipient.username'] = $user_obj->user_login;
            if ( gp_is_active( 'settings' ) && empty( $tokens['unsubscribe'] ) ) {
                $tokens['unsubscribe'] = esc_url( sprintf(
                    '%s%s/notifications/',
                    gp_core_get_user_domain( $user_obj->ID ),
                    gp_get_settings_slug()
                ) );
            }
        }
    }

    // Set default unsubscribe link if not passed.
    if ( empty( $tokens['unsubscribe'] ) ) {
        $tokens['unsubscribe'] = site_url( 'wp-login.php' );
    }

    // Email preheader.
    $post = $email->get_post_object();
    if ( $post ) {
        $tokens['email.preheader'] = sanitize_text_field( get_post_meta( $post->ID, 'gp_email_preheader', true ) );
    }

    return $tokens;
}
add_filter( 'gp_email_get_tokens', 'gp_email_set_default_tokens', 6, 4 );

function gp_filter_metaid_column_name( $q ) {
    /*
     * Replace quoted content with __QUOTE__ to avoid false positives.
     * This regular expression will match nested quotes.
     */
    $quoted_regex = "/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/s";
    preg_match_all( $quoted_regex, $q, $quoted_matches );
    $q = preg_replace( $quoted_regex, '__QUOTE__', $q );

    $q = str_replace( 'meta_id', 'id', $q );

    // Put quoted content back into the string.
    if ( ! empty( $quoted_matches[0] ) ) {
        for ( $i = 0; $i < count( $quoted_matches[0] ); $i++ ) {
            $quote_pos = strpos( $q, '__QUOTE__' );
            $q = substr_replace( $q, $quoted_matches[0][ $i ], $quote_pos, 9 );
        }
    }

    return $q;
}
