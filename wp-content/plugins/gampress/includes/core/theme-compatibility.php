<?php

/**
 * GamPress Core Template Functions
 * ��
 * @package gampressustom
 * @sugpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_register_theme_package( $theme = array(), $override = true ) {
    
    // Create new GP_Theme_Compat object from the $theme array.
    if ( is_array( $theme ) ) {
        $theme = new GP_Theme_Compat( $theme );
    }
    
    // Bail if $theme isn't a proper object.
    if ( ! is_a( $theme, 'GP_Theme_Compat' ) ) {
        return;
    }
    
    // Load up BuddyPress.
    $gp = gampress();
    
    // Only set if the theme package was not previously registered or if the
    // override flag is set.
    if ( empty( $gp->theme_compat->packages[$theme->id] ) || ( true === $override ) ) {
        $gp->theme_compat->packages[$theme->id] = $theme;
    }
}

function gp_set_theme_compat_feature( $theme_id, $feature = array() ) {
    if ( empty( $theme_id ) || empty( $feature['name'] ) ) {
        return;
    }

    // Get BuddyPress instance.
    $gp = gampress();

    // Get current theme compat theme.
    $theme_compat_theme = $gp->theme_compat->theme;

    // Bail if the Theme Compat theme is not in use.
    if ( $theme_id !== gp_get_theme_compat_id() ) {
        return;
    }

    $features = $theme_compat_theme->__get( 'features' );
    if ( empty( $features ) ) {
        $features = array();
    }

    // Bail if the feature is already registered or no settings were provided.
    if ( isset( $features[ $feature['name'] ] ) || empty( $feature['settings'] ) ) {
        return;
    }

    // Add the feature.
    $features[ $feature['name'] ] = (object) $feature['settings'];

    // The feature is attached to components.
    if ( isset( $features[ $feature['name'] ]->components ) ) {
        // Set the feature for each concerned component.
        foreach ( (array) $features[ $feature['name'] ]->components as $component ) {
            // The xProfile component is specific.
            if ( 'xprofile' === $component ) {
                $component = 'profile';
            }

            if ( isset( $gp->{$component} ) ) {
                if ( isset( $gp->{$component}->features ) ) {
                    $gp->{$component}->features[] = $feature['name'];
                } else {
                    $gp->{$component}->features = array( $feature['name'] );
                }
            }
        }
    }

    // Finally update the theme compat features.
    $theme_compat_theme->__set( 'features', $features );
}

function gp_register_theme_compat_default_features() {
	global $content_width;

	// Do not set up default features on deactivation.
	if ( gp_is_deactivation() ) {
		return;
	}

	// If the current theme doesn't need theme compat, bail at this point.
	if ( ! gp_use_theme_compat_with_current_theme() ) {
		return;
	}

	// Make sure BP Legacy is the Theme Compat in use.
	if ( 'legacy' !== gp_get_theme_compat_id() ) {
		return;
	}

	// Get the theme.
	$current_theme = wp_get_theme();
	$theme_handle  = $current_theme->get_stylesheet();
	$parent        = $current_theme->parent();

	if ( $parent ) {
		$theme_handle = $parent->get_stylesheet();
	}

	/**
	 * Since Companion stylesheets, the $content_width is smaller
	 * than the width used by BuddyPress, so we need to manually set the
	 * content width for the concerned themes.
	 *
	 * Example: array( stylesheet => content width used by BuddyPress )
	 */
	$gp_content_widths = array(
		'twentyfifteen'  => 1300,
		'twentyfourteen' => 955,
		'twentythirteen' => 890,
	);

	// Default values.
	$gp_content_width = (int) $content_width;
	$gp_handle        = 'gp-legacy-css';

	// Specific to themes having companion stylesheets.
	if ( isset( $gp_content_widths[ $theme_handle ] ) ) {
		$gp_content_width = $gp_content_widths[ $theme_handle ];
		$gp_handle        = 'gp-' . $theme_handle;
	}

	if ( is_rtl() ) {
		$gp_handle .= '-rtl';
	}

	$top_offset    = 150;
	$avatar_height = apply_filters( 'gp_core_avatar_full_height', $top_offset );

	if ( $avatar_height > $top_offset ) {
		$top_offset = $avatar_height;
	}

	gp_set_theme_compat_feature( 'legacy', array(
		'name'     => 'cover_image',
		'settings' => array(
			'components'   => array( 'xprofile', 'groups' ),
			'width'        => $gp_content_width,
			'height'       => $top_offset + round( $avatar_height / 2 ),
			'callback'     => 'gp_legacy_theme_cover_image',
			'theme_handle' => $gp_handle,
		),
	) );
}

function gp_use_theme_compat_with_current_theme() {
    if ( ! isset( gampress()->theme_compat->use_with_current_theme ) ) {
        gp_detect_theme_compat_with_current_theme();
    }
    
    return apply_filters( 'gp_use_theme_compat_with_current_theme', gampress()->theme_compat->use_with_current_theme );
}

function gp_detect_theme_compat_with_current_theme() {
    if ( isset( gampress()->theme_compat->use_with_current_theme ) ) {
        return gampress()->theme_compat->use_with_current_theme;
    }
    
    // Theme compat enabled by default.
    $theme_compat = true;
    
    // If the theme supports 'gampress', bail.
    if ( current_theme_supports( 'gampress' ) ) {
        $theme_compat = false;
        
        // If the theme doesn't support BP, do some additional checks.
    } else {
        // Bail if theme is a derivative of gp-default.
        if ( in_array( 'gp-default', array( get_template(), get_stylesheet() ) ) ) {
            $theme_compat = false;
            
            // Brute-force check for a BP template.
            // Examples are clones of gp-default.
        } elseif ( locate_template( 'members/members-loop.php', false, false ) ) {
            $theme_compat = false;
        }
    }
    
    // Set a flag in the gampress() singleton so we don't have to run this again.
    gampress()->theme_compat->use_with_current_theme = $theme_compat;
    
    return $theme_compat;
}

function gp_get_theme_compat_id() {
    return apply_filters( 'gp_get_theme_compat_id', gampress()->theme_compat->theme->id );
}

function gp_get_theme_compat_dir() {
    return apply_filters( 'gp_get_theme_compat_dir', gampress()->theme_compat->theme->dir );
}

function gp_setup_theme_compat( $theme = '' ) {
    $gp = gampress();

    // Make sure theme package is available, set to default if not.
    if ( ! isset( $gp->theme_compat->packages[$theme] ) || ! is_a( $gp->theme_compat->packages[$theme], 'GP_Theme_Compat' ) ) {
        $theme = 'legacy';
    }

    // Set the active theme compat theme.
    $gp->theme_compat->theme = $gp->theme_compat->packages[$theme];
}
