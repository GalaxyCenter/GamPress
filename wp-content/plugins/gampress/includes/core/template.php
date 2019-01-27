<?php

/**
 * GamPress Core Template Functions
 * 的
 * @package gampressustom
 * @sugpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_get_search_slug() {
    return GP_SEARCH_SLUG;
}

function gp_current_component() {
    $gp = gampress();
    $current_component = !empty( $gp->current_component ) ? $gp->current_component : false;
    return $current_component;
}

function gp_is_current_component( $component ) {
    $gp = gampress();
    
    $is_current_component = false;
    
    if ( empty( $component ) )
        return false;
    
    if ( !empty( $gp->current_component ) ) {
        
        if ( $gp->current_component == $component )
            $is_current_component = true;
    }
    
    return $is_current_component;
}

function gp_is_current_action( $action = '' ) {
    if ( $action == gp_current_action() )
        return true;
    
    return false;
}

function gp_current_action() {
    $gp = gampress();
    
    $current_action = !empty( $gp->current_action ) ? $gp->current_action : '';
    return $current_action;
}

function gp_action_variable_is( $position, $value ) {
    $action_variables = gp_action_variables();
    $action_variable  = isset( $action_variables[ $position ] ) ? $action_variables[ $position ] : $defalt;

    return $action_variable === $value;
}

function gp_current_item() {
    $gp = gampress();
    
    $current_item = !empty( $gp->current_item ) ? $gp->current_item : '';
    return $current_item;
}

function gp_action_variable( $position = 0, $defalt = false ) {
    $action_variables = gp_action_variables();
    $action_variable  = isset( $action_variables[ $position ] ) ? $action_variables[ $position ] : $defalt;
    return $action_variable; 
}

function gp_root_domain() {
    echo gp_get_root_domain();
}
/**
 * Return the "root domain", the URL of the BP root blog.
 *
 * @return string URL of the BP root blog.
 */
function gp_get_root_domain() {
    $gp = gampress();
    
    if ( isset( $gp->root_domain ) && !empty( $gp->root_domain ) ) {
        $domain = $gp->root_domain;
    } else {
        $domain            = gp_core_get_root_domain();
        $gp->root_domain = $domain;
    }
    
    return $domain;
}

function gp_is_user() {
    if ( gp_displayed_user_id() )
        return true;
    
    return false;
}

function gp_loggedin_user_displayname() {
    echo gp_get_loggedin_user_displayname();
}

function gp_get_loggedin_user_displayname() {
    $gp = gampress();

    if ( gp_loggedin_user_id() ) {
        $username = gp_core_get_username( gp_loggedin_user_id(), $gp->loggedin_user->userdata->user_nicename, $gp->loggedin_user->userdata->user_login );
    } else {
        $username = '';
    }
    return $username;
}

function gp_displayed_user_id() {
    $gp = gampress();
    $id = !empty( $gp->displayed_user->ID ) ? $gp->displayed_user->ID : 0;
    
    return (int) $id;
}

function gp_loggedin_user_id() {
    $gp = gampress();
    $id = !empty( $gp->loggedin_user->id ) ? $gp->loggedin_user->id : 0;
    return (int) $id;
}

function gp_is_my_home() {
    if ( is_user_logged_in() && gp_loggedin_user_id() == gp_displayed_user_id() )
        $my_profile = true;
    else
        $my_profile = false;
    
    return $my_profile;
}

function is_gampress() {
    $retval = (bool) ( gp_current_component() || gp_is_user() );
    
    return $retval;
}

function gp_is_user_settings() {
    if ( gp_is_user() && gp_is_settings_component() )
        return true;
    
    return false;
}

function gp_action_variables() {
    $gp = gampress();
    $action_variables = !empty( $gp->action_variables ) ? $gp->action_variables : false;
    return $action_variables;
}

function gp_is_my_profile() {
    if ( is_user_logged_in() && gp_loggedin_user_id() == gp_displayed_user_id() ) {
        $my_profile = true;
    } else {
        $my_profile = false;
    }
    
    return $my_profile;
}

function gp_is_active( $component = '', $feature = '' ) {
    $retval = false;
    
    // Default to the current component if none is passed.
    if ( empty( $component ) ) {
        $component = gp_current_component();
    }
    
    // Is component in either the active or required components arrays.
    if ( isset( gampress()->active_components[ $component ] ) || isset( gampress()->required_components[ $component ] ) ) {
        $retval = true;
        
        // Is feature active?
        if ( ! empty( $feature ) ) {            
            if ( empty( gampress()->$component->features ) || false === in_array( $feature, gampress()->$component->features, true ) ) {
                $retval = false;
            }
        }
    }
    
    return $retval;
}

function gp_is_blog_page() {
    
    $is_blog_page = false;
    
    if ( ! gp_current_component() && ! gp_is_user() ) {
        $is_blog_page = true;
    }
    
    return (bool) $is_blog_page;
}

function gp_is_single_item() {
    $gp     = gampress();
    $retval = false;
    
    if ( isset( $gp->is_single_item ) ) {
        $retval = $gp->is_single_item;
    }
    
    return (bool) $retval;
}

function gp_is_directory() {
    $gp     = gampress();
    $retval = false;
    
    if ( isset( $gp->is_directory ) ) {
        $retval = $gp->is_directory;
    }
    
    return (bool) apply_filters( 'gp_is_directory', $retval );
}

function gp_is_register_page() {
    return (bool) gp_is_current_component( 'register' );
}

function gp_is_activation_page() {
    return (bool) gp_is_current_component( 'activate' );
}

function gp_is_create_blog() {
    return (bool) ( gp_is_blogs_component() && gp_is_current_action( 'create' ) );
}

function gp_is_blogs_component() {
    return (bool) ( is_multisite() && gp_is_current_component( 'blogs' ) );
}

function gp_get_directory_title( $component = '' ) {
    $title = '';
    
    // Use the string provided by the component.
    if ( ! empty( gampress()->{$component}->directory_title ) ) {
        $title = gampress()->{$component}->directory_title;
        
        // If none is found, concatenate.
    } elseif ( isset( gampress()->{$component}->name ) ) {
        $title = sprintf( __( '%s Directory', 'gampress' ), gampress()->{$component}->name );
    }

    return apply_filters( 'gp_get_directory_title', $title, $component );
}

function gp_get_title_parts( $seplocation = 'right' ) {
    $gp = gampress();
    
    // Defaults to an empty array.
    $gp_title_parts = array();
    
    // If this is not a BP page, return the empty array.
    if ( gp_is_blog_page() ) {
        return $gp_title_parts;
    }
    
    // If this is a 404, return the empty array.
    if ( is_404() ) {
        return $gp_title_parts;
    }
    
    // If this is the front page of the site, return the empty array.
    if ( is_front_page() || is_home() ) {
        return $gp_title_parts;
    }
    
    // Return the empty array if not a BuddyPress page.
    if ( ! is_gampress() ) {
        return $gp_title_parts;
    }
    
    // Now we can build the BP Title Parts
    // Is there a displayed user, and do they have a name?
    $displayed_user_name = false;//gp_get_displayed_user_fullname();
    
    // Displayed user.
    if ( ! empty( $displayed_user_name ) && ! is_404() ) {
        
        // Get the component's ID to try and get its name.
        $component_id = $component_name = gp_current_component();
        
        // Set empty subnav name.
        $component_subnav_name = '';
        
        if ( ! empty( $gp->members->nav ) ) {
            $primary_nav_item = $gp->members->nav->get_primary( array( 'slug' => $component_id ), false );
            $primary_nav_item = reset( $primary_nav_item );
        }
        
        // Use the component nav name.
        if ( ! empty( $primary_nav_item->name ) ) {
            $component_name = _gp_strip_spans_from_title( $primary_nav_item->name );
            
            // Fall back on the component ID.
        } elseif ( ! empty( $gp->{$component_id}->id ) ) {
            $component_name = ucwords( $gp->{$component_id}->id );
        }
        
        if ( ! empty( $gp->members->nav ) ) {
            $secondary_nav_item = $gp->members->nav->get_secondary( array(
                        'parent_slug' => $component_id,
                        'slug'        => gp_current_action()
                        ), false );
            
            if ( $secondary_nav_item ) {
                $secondary_nav_item = reset( $secondary_nav_item );
            }
        }
        
        // Append action name if we're on a member component sub-page.
        if ( ! empty( $secondary_nav_item->name ) && ! empty( $gp->canonical_stack['action'] ) ) {
            $component_subnav_name = $secondary_nav_item->name;
        }
        
        // If on the user profile's landing page, just use the fullname.
        if ( gp_is_current_component( $gp->default_component ) && ( gp_get_requested_url() === gp_displayed_user_domain() ) ) {
            $gp_title_parts[] = $displayed_user_name;
            
            // Use component name on member pages.
        } else {
            $gp_title_parts = array_merge( $gp_title_parts, array_map( 'strip_tags', array(
                            $displayed_user_name,
                            $component_name,
                            ) ) );
            
            // If we have a subnav name, add it separately for localization.
            if ( ! empty( $component_subnav_name ) ) {
                $gp_title_parts[] = strip_tags( $component_subnav_name );
            }
        }
        
        // A single item from a component other than Members.
    } elseif ( gp_is_single_item() ) {
        $component_id = gp_current_component();
        $secondary_nav_item = $gp->{$component_id}->current_item;

        if ( ! empty( $secondary_nav_item->title ) ) {
            $single_item_subnav = $secondary_nav_item->title;
        }
        
        $gp_title_parts = array( $gp->gp_options_title, $single_item_subnav );
        
        // An index or directory.
    } elseif ( gp_is_directory() ) {
        $current_component = gp_current_component();

        $gp_title_parts = array( _x( 'Directory', 'component directory title', 'gampress' ) );

        if ( ! empty( $current_component ) ) {
            $gp_title_parts = array( gp_get_directory_title( $current_component ) );
        }
        
        // Sign up page.
    } elseif ( gp_is_register_page() ) {
        $gp_title_parts = array( __( 'Create an Account', 'gampress' ) );
        
        // Activation page.
    } elseif ( gp_is_activation_page() ) {
        $gp_title_parts = array( __( 'Activate Your Account', 'gampress' ) );
        
    } elseif ( gp_is_create_blog() ) {
        $gp_title_parts = array( __( 'Create a Site', 'gampress' ) );
    }
    
    // Strip spans.
    $gp_title_parts = array_map( '_gp_strip_spans_from_title', $gp_title_parts );
    
    // Sep on right, so reverse the order.
    if ( 'right' === $seplocation ) {
        $gp_title_parts = array_reverse( $gp_title_parts );
    }
    
    return (array) apply_filters( 'gp_get_title_parts', $gp_title_parts, $seplocation );
}

function gp_is_component_front_page( $component = '' ) {
    global $current_blog;
    
    $gp = gampress();
    
    // Default to the current component if none is passed.
    if ( empty( $component ) ) {
        $component = gp_current_component();
    }
    
    // Get the path for the current blog/site.
    $path = is_main_site()
        ? gp_core_get_site_path()
        : $current_blog->path;
    
    // Get the front page variables.
    $show_on_front = get_option( 'show_on_front' );
    $page_on_front = get_option( 'page_on_front' );
    
    if ( ( 'page' !== $show_on_front ) || empty( $component ) || empty( $gp->pages->{$component} ) || ( $_SERVER['REQUEST_URI'] !== $path ) ) {
        return false;
    }
    
    /**
     * Filters whether or not the specified BuddyPress component directory is set to be the front page.
     *
     * @since 1.5.0
     *
     * @param bool   $value     Whether or not the specified component directory is set as front page.
     * @param string $component Current component being checked.
     */
    return (bool) apply_filters( 'gp_is_component_front_page', ( $gp->pages->{$component}->id == $page_on_front ), $component );
}

function gp_keywords() {
    echo gp_get_keywords();
}
    function gp_get_keywords() {
        $keywords = gp_get_page_keywords();

        $gp = gampress();
        if ( is_home() ) {

        } else if ( gp_is_directory() ) {

        } else if ( gp_is_single_item() ) {
            $component_id = gp_current_component();
            $secondary_nav_item = $gp->{$component_id}->current_item;

            $keywords .= $secondary_nav_item->title . ',' . $secondary_nav_item->tags;
        }

        return $keywords;
    }

function gp_description() {
    echo gp_get_description();
}

    function gp_get_description() {
        $desc = gp_get_page_description();

        $gp = gampress();
        if ( is_home() ) {

        } else if ( gp_is_directory() ) {


        } else if ( gp_is_single_item() ) {
            $component_id = gp_current_component();
            $secondary_nav_item = $gp->{$component_id}->current_item;

            $temp = strip_tags( $secondary_nav_item->description );
            $temp = str_replace(array("\r\n", "\r", "\n"), "", $temp);
            $temp = mb_substr( $temp, 0, 250 );

            if ( empty($desc) )
                $desc = $temp;
            else
                $desc = $temp . $desc;
        }

        return $desc;
    }

function gp_format_time( $time = '', $exclude_time = false, $gmt = false ) {

    // Bail if time is empty or not numeric
    // @todo We should output something smarter here.
    if ( empty( $time ) || ! is_numeric( $time ) ) {
        return false;
    }

    // Get GMT offset from root blog.
    if ( true === $gmt ) {

        // Use Timezone string if set.
        $timezone_string = gp_get_option( 'timezone_string' );
        if ( ! empty( $timezone_string ) ) {
            $timezone_object = timezone_open( $timezone_string );
            $datetime_object = date_create( "@{$time}" );
            $timezone_offset = timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS;

            // Fall back on less reliable gmt_offset.
        } else {
            $timezone_offset = gp_get_option( 'gmt_offset' );
        }

        // Calculate time based on the offset.
        $calculated_time = $time + ( $timezone_offset * HOUR_IN_SECONDS );

        // No localizing, so just use the time that was submitted.
    } else {
        $calculated_time = $time;
    }

    // Formatted date: "March 18, 2014".
    $formatted_date = date_i18n( gp_get_option( 'date_format' ), $calculated_time, $gmt );

    // Should we show the time also?
    if ( true !== $exclude_time ) {

        // Formatted time: "2:00 pm".
        $formatted_time = date_i18n( gp_get_option( 'time_format' ), $calculated_time, $gmt );

        // Return string formatted with date and time.
        $formatted_date = sprintf( esc_html__( '%1$s %2$s', 'gampress' ), $formatted_date, $formatted_time );
    }

    return apply_filters( 'gp_format_time', $formatted_date );
}