<?php
/**
 * GamPress Core Template-Loader.
 *
 * ⊙▂⊙
 * 
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package GamPress
 * @sugpackage Core
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_locate_template( $template_names, $load = false, $require_once = true ) {
    
    // No file found yet.
    $located            = false;
    $template_locations = gp_get_template_stack();
    
    // Try to find a template file.
    foreach ( (array) $template_names as $template_name ) {
        
        // Continue if template is empty.
        if ( empty( $template_name ) ) {
            continue;
        }
        
        // Trim off any slashes from the template name.
        $template_name  = ltrim( $template_name, '/' );
        
        // Loop through template stack.
        foreach ( (array) $template_locations as $template_location ) {
            
            // Continue if $template_location is empty.
            if ( empty( $template_location ) ) {
                continue;
            }
            
            // Check child theme first.
            if ( file_exists( trailingslashit( $template_location ) . $template_name ) ) {
                $located = trailingslashit( $template_location ) . $template_name;
                break 2;
            }
        }
    }
    
    do_action( 'gp_locate_template', $located, $template_name, $template_names, $template_locations, $load, $require_once );
    
    $load_template = (bool) apply_filters( 'gp_locate_template_and_load', true );
    
    if ( $load_template && $load && ! empty( $located ) ) {
        load_template( $located, $require_once );
    }
    
    return $located;
}

function gp_register_template_stack( $location_callback = '', $priority = 10 ) {
    
    // Bail if no location, or function/method is not callable.
    if ( empty( $location_callback ) || ! is_callable( $location_callback ) ) {
        return false;
    }
    
    // Add location callback to template stack.
    return add_filter( 'gp_template_stack', $location_callback, (int) $priority );
}

function gp_get_template_stack() {
    global $wp_filter, $merged_filters, $wp_current_filter;
    
    // Setup some default variables.
    $tag  = 'gp_template_stack';
    $args = $stack = array();
    
    // Add 'gp_template_stack' to the current filter array.
    $wp_current_filter[] = $tag;
    
    // Sort.
    if ( class_exists( 'WP_Hook' ) ) {
        $filter = $wp_filter[ $tag ]->callbacks;
    } else {
        $filter = &$wp_filter[ $tag ];
        
        if ( ! isset( $merged_filters[ $tag ] ) ) {
            ksort( $filter );
            $merged_filters[ $tag ] = true;
        }
    }
    
    // Ensure we're always at the beginning of the filter array.
    reset( $filter );
    
    // Loop through 'gp_template_stack' filters, and call callback functions.
    do {
        foreach( (array) current( $filter ) as $the_ ) {
            if ( ! is_null( $the_['function'] ) ) {
                $args[1] = $stack;
                $stack[] = call_user_func_array( $the_['function'], array_slice( $args, 1, (int) $the_['accepted_args'] ) );
            }
        }
    } while ( next( $filter ) !== false );
    
    // Remove 'gp_template_stack' from the current filter array.
    array_pop( $wp_current_filter );
    
    // Remove empties and duplicates.
    $stack = array_unique( array_filter( $stack ) );
    
    /**
     * Filters the "template stack" list of registered directories where templates can be found.
     *
     * @since 1.7.0
     *
     * @param array $stack Array of registered directories for template locations.
     */
    return (array) apply_filters( 'gp_get_template_stack', $stack ) ;
}

function gp_load_theme_functions() {
	global $pagenow, $wp_query;

	// Do not load our custom BP functions file if theme compat is disabled.
	if ( ! gp_use_theme_compat_with_current_theme() ) {
		return;
	}

	// Do not include on BuddyPress deactivation.
	if ( gp_is_deactivation() ) {
		return;
	}

	// If the $wp_query global is empty (the main query has not been run,
	// or has been reset), load_template() will fail at setting certain
	// global values. This does not happen on a normal page load, but can
	// cause problems when running automated tests.
	if ( ! is_a( $wp_query, 'WP_Query' ) ) {
		return;
	}

	// Only include if not installing or if activating via wp-activate.php.
	if ( ! defined( 'WP_INSTALLING' ) || 'wp-activate.php' === $pagenow ) {
		gp_locate_template( 'gampress-functions.php', true );
	}
}

function gp_get_template_locations( $templates = array() ) {
    $locations = array(
        'gampress',
        ''
    );
    return apply_filters( 'gp_get_template_locations', $locations, $templates );
}

function gp_add_template_stack_locations( $stacks = array() ) {
    $retval = array();

    // Get alternate locations.
    $locations = gp_get_template_locations();

    // Loop through locations and stacks and combine.
    foreach ( (array) $stacks as $stack ) {
        foreach ( (array) $locations as $custom_location ) {
            $retval[] = untrailingslashit( trailingslashit( $stack ) . $custom_location );
        }
    }
    
    return apply_filters( 'gp_add_template_stack_locations', array_unique( $retval ), $stacks );
}

function gp_buffer_template_part( $slug, $name = null, $echo = true ) {
    ob_start();

    // Remove 'bp_replace_the_content' filter to prevent infinite loops.
    remove_filter( 'the_content', 'gp_replace_the_content' );

    gp_get_template_part( $slug, $name );

    // Remove 'bp_replace_the_content' filter to prevent infinite loops.
    add_filter( 'the_content', 'gp_replace_the_content' );

    // Get the output buffer contents.
    $output = ob_get_clean();

    // Echo or return the output buffer contents.
    if ( true === $echo ) {
        echo $output;
    } else {
        return $output;
    }
}

function gp_get_template_part( $slug, $name = null ) {

    do_action( 'get_template_part_' . $slug, $slug, $name );

    // Setup possible parts.
    $templates = array();
    if ( isset( $name ) ) {
        $templates[] = $slug . '-' . $name . '.php';
    }
    $templates[] = $slug . '.php';

    $templates = apply_filters( 'gp_get_template_part', $templates, $slug, $name );

    // Return the part that is found.
    return gp_locate_template( $templates, true, false );
}