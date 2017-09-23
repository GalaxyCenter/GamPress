<?php

/**
 * GamPress Url 重写模块
 * 
 * ⊙▂⊙
 * 
 * @package gampressustom
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_core_set_uri_globals() {
    global $wp_rewrite;
    
    $gp = gampress();
    
    // Define local variables
    $root_profile = $match   = false;
    $key_slugs    = $matches = $uri_chunks = array();
    
    // Fetch all the WP page names for each component
    if ( empty( $gp->pages ) )
        $gp->pages = gp_core_get_directory_pages();
    
    // Ajax or not?
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX || strpos( $_SERVER['REQUEST_URI'], 'wp-load.php' ) )
        $path = gp_core_referrer();
    else
        $path = esc_url( $_SERVER['REQUEST_URI'] );
    
    // Take GET variables off the URL to avoid problems
    $path = strtok( $path, '?' );
    
    // Fetch current URI and explode each part separated by '/' into an array
    $gp_uri = explode( '/', $path );
    
    // Loop and remove empties
    foreach ( (array) $gp_uri as $key => $uri_chunk ) {
        if ( empty( $gp_uri[$key] ) ) {
            unset( $gp_uri[$key] );
        } else {
            $gp_uri[$key] = urldecode( $gp_uri[$key] );
        }
    }
    
    // Get site path items
    $paths = explode( '/', gp_core_get_site_path() );
    
    // Take empties off the end of path
    if ( empty( $paths[count( $paths ) - 1] ) )
        array_pop( $paths );
    
    // Take empties off the start of path
    if ( empty( $paths[0] ) )
        array_shift( $paths );
    
    // Reset indexes
    $gp_uri = array_values( $gp_uri );
    $paths  = array_values( $paths );
    
    // Unset URI indices if they intersect with the paths
    foreach ( (array) $gp_uri as $key => $uri_chunk ) {
        if ( isset( $paths[$key] ) && $uri_chunk == $paths[$key] ) {
            unset( $gp_uri[$key] );
        }
    }
    
    // Reset the keys by merging with an empty array
    $gp_uri = array_merge( array(), $gp_uri );
    
    // If a component is set to the front page, force its name into $gp_uri
    // so that $current_component is populated (unless a specific WP post is being requested
    // via a URL parameter, usually signifying Preview mode)
    if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) && empty( $gp_uri ) && empty( $_GET['p'] ) && empty( $_GET['page_id'] ) ) {
        $post = get_post( get_option( 'page_on_front' ) );
        if ( !empty( $post ) ) {
            $gp_uri[0] = $post->post_name;
        }
    }
    
    // Keep the unfiltered URI safe
    $gp->unfiltered_uri = $gp_uri;
    
    // Don't use $gp_unfiltered_uri, this is only for backpat with old plugins. Use $gp->unfiltered_uri.
    $GLOBALS['gp_unfiltered_uri'] = &$gp->unfiltered_uri;
    
    // Get slugs of pages into array
    foreach ( (array) $gp->pages as $page_key => $gp_page )
        $key_slugs[$page_key] = trailingslashit( '/' . $gp_page->slug );
    
    // Bail if keyslugs are empty, as BP is not setup correct
    if ( empty( $key_slugs ) )
        return;
    
    // Loop through page slugs and look for exact match to path
    foreach ( $key_slugs as $key => $slug ) {
        if ( $slug == $path ) {
            $match      = $gp->pages->{$key};
            $match->key = $key;
            $matches[]  = 1;
            break;
        }
    }
    
    // No exact match, so look for partials
    if ( empty( $match ) ) {
        
        // Loop through each page in the $gp->pages global
        foreach ( (array) $gp->pages as $page_key => $gp_page ) {
            
            // Look for a match (check members first)
            if ( in_array( $gp_page->name, (array) $gp_uri ) ) {
                
                // Match found, now match the slug to make sure.
                $uri_chunks = explode( '/', $gp_page->slug );
                
                // Loop through uri_chunks
                foreach ( (array) $uri_chunks as $key => $uri_chunk ) {
                    
                    // Make sure chunk is in the correct position
                    if ( !empty( $gp_uri[$key] ) && ( $gp_uri[$key] == $uri_chunk ) ) {
                        $matches[] = 1;
                        
                        // No match
                    } else {
                        $matches[] = 0;
                    }
                }
                
                // Have a match
                if ( !in_array( 0, (array) $matches ) ) {
                    $match      = $gp_page;
                    $match->key = $page_key;
                    break;
                };
                
                // Unset matches
                unset( $matches );
            }
            
            // Unset uri chunks
            unset( $uri_chunks );
        }
    }
    
    // URLs with gp_ENABLE_ROOT_PROFILES enabled won't be caught above
    if ( empty( $matches ) && gp_core_enable_root_profiles() ) {
        
        // Switch field based on compat
        $field = gp_is_username_compatibility_mode() ? 'login' : 'slug';
        
        // Make sure there's a user corresponding to $gp_uri[0]
        if ( !empty( $gp->pages->members ) && !empty( $gp_uri[0] ) && $root_profile = get_user_by( $field, $gp_uri[0] ) ) {
            
            // Force BP to recognize that this is a members page
            $matches[]  = 1;
            $match      = $gp->pages->members;
            $match->key = 'members';
        }
    }
    
    // Search doesn't have an associated page, so we check for it separately
    if ( !empty( $gp_uri[0] ) && ( gp_get_search_slug() == $gp_uri[0] ) ) {
        $matches[]   = 1;
        $match       = new stdClass;
        $match->key  = 'search';
        $match->slug = gp_get_search_slug();
    }
    
    // This is not a xc page, so just return.
    if ( empty( $matches ) )
        return false;
    
    $wp_rewrite->use_verbose_page_rules = false;
    
    // Find the offset. With $root_profile set, we fudge the offset down so later parsing works
    $slug       = !empty ( $match ) ? explode( '/', $match->slug ) : '';
    $uri_offset = empty( $root_profile ) ? 0 : -1;
    
    // Rejig the offset
    if ( !empty( $slug ) && ( 1 < count( $slug ) ) ) {
        array_pop( $slug );
        $uri_offset = count( $slug );
    }
    
    // Global the unfiltered offset to use in gp_core_load_template().
    // To avoid PHP warnings in gp_core_load_template(), it must always be >= 0
    $gp->unfiltered_uri_offset = $uri_offset >= 0 ? $uri_offset : 0;
    
    // We have an exact match
    if ( isset( $match->key ) ) {
        
        // Set current component to matched key
        $gp->current_component = $match->key;
        
        // If members component, do more work to find the actual component
        if ( gp_get_members_slug() == $match->key ) {
            
            // Viewing a specific user
            if ( !empty( $gp_uri[$uri_offset + 1] ) ) {
                
                // Switch the displayed_user based on compatbility mode
                if ( gp_is_username_compatibility_mode() ) {
                    $gp->displayed_user->ID = (int) gp_core_get_userid( urldecode( $gp_uri[$uri_offset + 1] ) );
                } else {
                    $gp->displayed_user->ID = (int) gp_core_get_userid_from_nicename( urldecode( $gp_uri[$uri_offset + 1] ) );
                }
                
                if ( !gp_displayed_user_id() ) {
                    
                    // Prevent components from loading their templates
                    $gp->current_component = '';
                    
                    gp_do_404();
                    return;
                }
                
                // If the displayed user is marked as a spammer, 404 (unless logged-
                // in user is a super admin)
                if ( gp_displayed_user_id() && gp_is_user_spammer( gp_displayed_user_id() ) ) {
                    if ( gp_loggedin_user_can( 'gp_moderate' ) ) {
                        gp_core_add_message( __( 'This user has been marked as a spammer. Only site admins can view this profile.', 'xc' ), 'warning' );
                    } else {
                        gp_do_404();
                        return;
                    }
                }
                
                // Bump the offset
                if ( isset( $gp_uri[$uri_offset + 2] ) ) {
                    $gp_uri                = array_merge( array(), array_slice( $gp_uri, $uri_offset + 2 ) );
                    $gp->current_component = $gp_uri[0];
                    
                    // No component, so default will be picked later
                } else {
                    $gp_uri                = array_merge( array(), array_slice( $gp_uri, $uri_offset + 2 ) );
                    $gp->current_component = '';
                }
                
                // Reset the offset
                $uri_offset = 0;
            }
        }
    }
    
    // Set the current action
    $gp->current_action = isset( $gp_uri[ $uri_offset + 1 ] ) ? $gp_uri[ $uri_offset + 1 ] : '';
    
    // Slice the rest of the $gp_uri array and reset offset
    $gp_uri      = array_slice( $gp_uri, $uri_offset + 2 );
    $uri_offset  = 0;
    
    // Set the entire URI as the action variables, we will unset the current_component and action in a second
    $gp->action_variables = $gp_uri;
    
    // Reset the keys by merging with an empty array
    $gp->action_variables = array_merge( array(), $gp->action_variables );
}


/**
 * Load a specific template file with fallback support.
 *
 * Example:
 *   gp_core_load_template( 'order/index' );
 * Loads:
 *   wp-content/themes/[activated_theme]/order/index.php
 *
 * @param array $templates Array of templates to attempt to load.
 * @return bool|null Returns false on failure.
 */
function gp_core_load_template( $templates ) {
    global $post, $wp_query, $wpdb;
    
    $gp = gampress();
    
    // Determine if the root object WP page exists for this request
    // note: get_page_by_path() breaks non-root pages
    if ( !empty( $gp->unfiltered_uri_offset ) ) {
        if ( !$page_exists = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s", $gp->unfiltered_uri[$gp->unfiltered_uri_offset] ) ) ) {
            return false;
        }
    }
    
    // Set the root object as the current wp_query-ied item
    $object_id = 0;
    foreach ( (array) $gp->pages as $page ) {
        if ( $page->name == $gp->unfiltered_uri[$gp->unfiltered_uri_offset] ) {
            $object_id = $page->id;
        }
    }
    
    // Make the queried/post object an actual valid page
    if ( !empty( $object_id ) ) {
        $wp_query->queried_object    = get_post( $object_id );
        $wp_query->queried_object_id = $object_id;
        $post                        = $wp_query->queried_object;
    }
    
    // Fetch each template and add the php suffix
    $filtered_templates = array();
    foreach ( (array) $templates as $template ) {
        $filtered_templates[] = $template . '.php';
    }
    
    // Filter the template locations so that plugins can alter where they are located
    $located_template = apply_filters( 'gp_located_template', locate_template( (array) $filtered_templates, false ), $filtered_templates );
    if ( !empty( $located_template ) ) {
        
        // Template was located, lets set this as a valid page and not a 404.
        status_header( 200 );
        $wp_query->is_page     = true;
        $wp_query->is_singular = true;
        $wp_query->is_404      = false;
        
        do_action( 'gp_core_pre_load_template', $located_template );
        
        load_template( apply_filters( 'gp_load_template', $located_template ) );
        
        do_action( 'gp_core_post_load_template', $located_template );
        
        // Kill any other output after this.
        exit();
        
        // No template found, so setup theme compatability
        // @todo Some other 404 handling if theme compat doesn't kick in
    } else {
        
        // We know where we are, so reset important $wp_query bits here early.
        // The rest will be done by gp_theme_compat_reset_post() later.
        if ( is_gampress() ) {
            status_header( 200 );
            $wp_query->is_page     = true;
            $wp_query->is_singular = true;
            $wp_query->is_404      = false;
        }
        
        do_action( 'gp_setup_theme_compat' );
    }
}