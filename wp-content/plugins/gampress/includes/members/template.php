<?php
/**
 * GamPress Members Template Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @sugpackage Members
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_is_members_component() {
    return (bool) gp_is_current_component( 'members' );
}

function gp_members_slug() {
    echo gp_get_members_slug();
}

    function gp_get_members_slug() {
        return apply_filters( 'gp_get_members_slug', gampress()->members->slug );
	}
    
function gp_displayed_user_has_front_template() {
    $displayed_user = gp_get_displayed_user();
    
    return ! empty( $displayed_user->front_template );
}

function gp_displayed_user_domain() {
    $gp = gampress();
    
    return apply_filters( 'gp_displayed_user_domain', isset( $gp->displayed_user->domain ) ? $gp->displayed_user->domain : '' );
}

function gp_loggedin_user_domain() {
    $gp = gampress();
    
    return apply_filters( 'gp_loggedin_user_domain', isset( $gp->loggedin_user->domain ) ? $gp->loggedin_user->domain : '' );
}

function gp_is_members_directory() {
    if ( ! gp_is_user() && gp_is_members_component() ) {
        return true;
    }
    
    return false;
}

function gp_signup_allowed() {
	echo gp_get_signup_allowed();
}

	function gp_get_signup_allowed() {
		return apply_filters( 'gp_get_signup_allowed', (bool) gp_get_option( 'users_can_register' ) );
	}

function gp_members_root_slug() {
    echo gp_get_members_root_slug();
}

    function gp_get_members_root_slug() {
        return apply_filters( 'gp_get_members_root_slug', gampress()->members->root_slug );
    }

function gp_activate_slug() {
	echo gp_get_activate_slug();
}

	function gp_get_activate_slug() {
		$gp = gampress();

		if ( !empty( $gp->pages->activate->slug ) ) {
			$slug = $gp->pages->activate->slug;
		} elseif ( defined( 'GP_ACTIVATION_SLUG' ) ) {
			$slug = GP_ACTIVATION_SLUG;
		} else {
			$slug = 'activate';
		}

		return apply_filters( 'gp_get_activate_slug', $slug );
	}

function gp_has_custom_activation_page() {
    static $has_page = false;
    
    if ( empty( $has_page ) )
        $has_page = gp_get_activate_slug() && gp_locate_template( array( 'signup/activate.php', 'members/activate.php', 'activate.php' ), false );
    
    return (bool) $has_page;
}

function gp_activation_page() {
    echo esc_url( gp_get_activation_page() );
}

    function gp_get_activation_page() {
        if ( gp_has_custom_activation_page() ) {
            $page = trailingslashit( gp_get_root_domain() . '/' . gp_get_activate_slug() );
        } else {
            $page = trailingslashit( gp_get_root_domain() ) . 'wp-activate.php';
        }
        
        return apply_filters( 'gp_get_activation_page', $page );
	}

function gp_displayed_user_username() {
    echo gp_get_displayed_user_username();
}
    function gp_get_displayed_user_username() {
        $gp = gampress();

        if ( gp_displayed_user_id() ) {
            $username = gp_core_get_username( gp_displayed_user_id(), $gp->displayed_user->userdata->user_nicename, $gp->displayed_user->userdata->user_login );
        } else {
            $username = '';
        }

        return $username;
    }

function gp_displayed_user_fullname() {
    echo gp_get_displayed_user_fullname();
}
    function gp_get_displayed_user_fullname() {
        $gp = gampress();
        return apply_filters( 'gp_displayed_user_fullname', isset( $gp->displayed_user->fullname ) ? $gp->displayed_user->fullname : '' );
    }