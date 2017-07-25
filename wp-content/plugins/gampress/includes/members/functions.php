<?php
/**
 * GamPress Member Function Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @sugpackage Members
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

function gp_get_displayed_user() {
    $gp = gampress();
    
    $displayed_user = null;
    if ( ! empty( $gp->displayed_user->id ) ) {
        $displayed_user = $gp->displayed_user;
    }
    
    return apply_filters( 'gp_get_displayed_user', $displayed_user );
}

function gp_core_signup_user( $user_login, $user_password, $user_email, $usermeta ) {
    $gp = gampress();
    
    // We need to cast $user_id to pass to the filters.
    $user_id = false;
    
    // Multisite installs have their own install procedure.
    if ( is_multisite() ) {
        wpmu_signup_user( $user_login, $user_email, $usermeta );
        
    } else {
        // Format data.
        $user_login     = preg_replace( '/\s+/', '', sanitize_user( $user_login, true ) );
        $user_email     = sanitize_email( $user_email );
        $activation_key = wp_generate_password( 32, false );
        
        if ( ! defined( 'GP_SIGNUPS_SKIP_USER_CREATION' ) || ! GP_SIGNUPS_SKIP_USER_CREATION ) {
            $user_id = GP_Signup::add_backcompat( $user_login, $user_password, $user_email, $usermeta );
            
            if ( is_wp_error( $user_id ) ) {
                return $user_id;
            }
            
            gp_update_user_meta( $user_id, 'activation_key', $activation_key );
        }
        
        $args = array(
                'user_login'     => $user_login,
                'user_email'     => $user_email,
                'activation_key' => $activation_key,
                'meta'           => $usermeta,
                );
        
        GP_Signup::add( $args );
        
        if ( apply_filters( 'gp_core_signup_send_activation_key', true, $user_id, $user_email, $activation_key, $usermeta ) ) {
            gp_core_signup_send_validation_email( $user_id, $user_email, $activation_key, $user_login );
        }
    }
    
    $gp->signup->username = $user_login;
    
    do_action( 'gp_core_signup_user', $user_id, $user_login, $user_password, $user_email, $usermeta );
    
    return $user_id;
}

function gp_core_signup_send_validation_email( $user_email ) {
    $activation_key = wp_generate_password( 32, false );
    
    $args = array(
            'user_email'     => $user_email,
            'activation_key' => $activation_key
            );
    
    //GP_Signup::add( $args );
    
    $args = array(
            'tokens' => array(
                'activate.url' => esc_url( trailingslashit( gp_get_activation_page() ) . "{$activation_key}/" ),
                'key'          => $activation_key,
                'user.email'   => $user_email,
                'user.id'      => 0,
                ),
            );

    $to = array( array( $user_email => $user_email ) );
    
    gp_send_email( 'core-user-registration', $to, $args );
}

function gp_core_get_user_displaynames( $user_ids ) {
    
    // Sanitize.
    $user_ids = wp_parse_id_list( $user_ids );
    
    // Remove dupes and empties.
    $user_ids = array_unique( array_filter( $user_ids ) );
    
    if ( empty( $user_ids ) ) {
        return array();
    }
    
    $uncached_ids = array();
    foreach ( $user_ids as $user_id ) {
        if ( false === wp_cache_get( 'gp_user_fullname_' . $user_id, 'gp' ) ) {
            $uncached_ids[] = $user_id;
        }
    }
    
    // Prime caches.
    if ( ! empty( $uncached_ids ) ) {
        if ( gp_is_active( 'xprofile' ) ) {
            $fullname_data = array();//GP_XProfile_ProfileData::get_value_byid( 1, $uncached_ids );
            
            // Key by user_id.
            $fullnames = array();
            foreach ( $fullname_data as $fd ) {
                if ( ! empty( $fd->value ) ) {
                    $fullnames[ intval( $fd->user_id ) ] = $fd->value;
                }
            }
            
            // If xprofiledata is not found for any users,  we'll look
            // them up separately.
            $no_xprofile_ids = array_diff( $uncached_ids, array_keys( $fullnames ) );
        } else {
            $fullnames = array();
            $no_xprofile_ids = $user_ids;
        }
        
        if ( ! empty( $no_xprofile_ids ) ) {
            // Use WP_User_Query because we don't need gp information.
            $query = new WP_User_Query( array(
                        'include'     => $no_xprofile_ids,
                        'fields'      => array( 'ID', 'user_nicename', 'display_name', ),
                        'count_total' => false,
                        'blog_id'     => 0,
                        ) );
            
            foreach ( $query->results as $qr ) {
                $fullnames[ $qr->ID ] = ! empty( $qr->display_name ) ? $qr->display_name : $qr->user_nicename;
                
                // If xprofile is active, set this value as the
                // xprofile display name as well.
                if ( gp_is_active( 'xprofile' ) ) {
                    xprofile_set_field_data( 1, $qr->ID, $fullnames[ $qr->ID ] );
                }
            }
        }
        
        foreach ( $fullnames as $fuser_id => $fname ) {
            wp_cache_set( 'gp_user_fullname_' . $fuser_id, $fname, 'gp' );
        }
    }
    
    $retval = array();
    foreach ( $user_ids as $user_id ) {
        $retval[ $user_id ] = wp_cache_get( 'gp_user_fullname_' . $user_id, 'gp' );
    }
    
    return $retval;
}

function gp_core_get_user_displayname( $user_id_or_username ) {
    if ( empty( $user_id_or_username ) ) {
        return false;
    }
    
    if ( ! is_numeric( $user_id_or_username ) ) {
        $user_id = gp_core_get_userid( $user_id_or_username );
    } else {
        $user_id = $user_id_or_username;
    }
    
    if ( empty( $user_id ) ) {
        return false;
    }
    
    $display_names = gp_core_get_user_displaynames( array( $user_id ) );
    
    if ( ! isset( $display_names[ $user_id ] ) ) {
        $fullname = false;
    } else {
        $fullname = $display_names[ $user_id ];
    }
    
    return apply_filters( 'gp_core_get_user_displayname', $fullname, $user_id );
}
add_filter( 'gp_core_get_user_displayname', 'strip_tags', 1 );
add_filter( 'gp_core_get_user_displayname', 'trim'          );
add_filter( 'gp_core_get_user_displayname', 'stripslashes'  );
add_filter( 'gp_core_get_user_displayname', 'esc_html'      );

function gp_core_activate_signup( $key ) {
    $signups = GP_Signup::get( array(
        'activation_key' => $key,
    ) );

    if ( empty( $signups['signups'] ) ) {
        return new WP_Error( 'invalid_key', __( 'Invalid activation key.', '' ) );
    }

    $signup = $signups['signups'][0];

    if ( $signup->active ) {
        if ( empty( $signup->domain ) ) {
            return new WP_Error( 'already_active', __( 'The user is already active.', 'gampress' ), $signup );
        } else {
            return new WP_Error( 'already_active', __( 'The site is already active.', 'gampress' ), $signup );
        }
    }

    return true;
}

function gp_is_user_spammer( $user_id = 0 ) {

    // No user to check.
    if ( empty( $user_id ) ) {
        return false;
    }

    $gp = gampress();

    // Assume user is not spam.
    $is_spammer = false;

    // Setup our user.
    $user = false;

    // Get locally-cached data if available.
    switch ( $user_id ) {
        case gp_loggedin_user_id() :
            $user = ! empty( $gp->loggedin_user->userdata ) ? $gp->loggedin_user->userdata : false;
            break;

        case gp_displayed_user_id() :
            $user = ! empty( $gp->displayed_user->userdata ) ? $gp->displayed_user->userdata : false;
            break;
    }

    // Manually get userdata if still empty.
    if ( empty( $user ) ) {
        $user = get_userdata( $user_id );
    }

    // No user found.
    if ( empty( $user ) ) {
        $is_spammer = false;

        // User found.
    } else {

        // Check if spam.
        if ( !empty( $user->spam ) ) {
            $is_spammer = true;
        }

        if ( 1 == $user->user_status ) {
            $is_spammer = true;
        }
    }

    return apply_filters( 'gp_is_user_spammer', (bool) $is_spammer );
}

function gp_core_get_core_userdata( $user_id = 0 ) {
    if ( empty( $user_id ) ) {
        return false;
    }

    if ( !$userdata = wp_cache_get( 'gp_core_userdata_' . $user_id, 'gp' ) ) {
        $userdata = GP_Core_User::get_core_userdata( $user_id );
        wp_cache_set( 'gp_core_userdata_' . $user_id, $userdata, 'gp' );
    }

    return apply_filters( 'gp_core_get_core_userdata', $userdata );
}

function gp_core_get_username( $user_id = 0, $user_nicename = false, $user_login = false ) {
    $gp = gampress();

    // Check cache for user nicename.
    $username = wp_cache_get( 'gp_user_username_' . $user_id, 'gp' );
    if ( false === $username ) {

        // Cache not found so prepare to update it.
        $update_cache = true;

        // Nicename and login were not passed.
        if ( empty( $user_nicename ) && empty( $user_login ) ) {

            // User ID matches logged in user.
            if ( gp_loggedin_user_id() == $user_id ) {
                $userdata = &$gp->loggedin_user->userdata;

                // User ID matches displayed in user.
            } elseif ( gp_displayed_user_id() == $user_id ) {
                $userdata = &$gp->displayed_user->userdata;

                // No user ID match.
            } else {
                $userdata = false;
            }

            // No match so go dig.
            if ( empty( $userdata ) ) {

                // User not found so return false.
                if ( !$userdata = gp_core_get_core_userdata( $user_id ) ) {
                    return false;
                }
            }

            // Update the $user_id for later.
            $user_id       = $userdata->ID;

            // Two possible options.
            $user_nicename = $userdata->user_nicename;
            $user_login    = $userdata->user_login;
        }

        // Pull an audible and maybe use the login over the nicename.
        $username = gp_is_username_compatibility_mode() ? $user_login : $user_nicename;

        // Username found in cache so don't update it again.
    } else {
        $update_cache = false;
    }

    // Add this to cache.
    if ( ( true === $update_cache ) && !empty( $username ) ) {
        wp_cache_set( 'gp_user_username_' . $user_id, $username, 'gp' );

        // @todo bust this cache if no $username found?
        // } else {
        // wp_cache_delete( 'gp_user_username_' . $user_id );
    }

    return apply_filters( 'gp_core_get_username', $username );
}

function gp_core_get_user_domain( $user_id = 0, $user_nicename = false, $user_login = false ) {

    if ( empty( $user_id ) ) {
        return;
    }

    $username = gp_core_get_username( $user_id, $user_nicename, $user_login );

    if ( gp_is_username_compatibility_mode() ) {
        $username = rawurlencode( $username );
    }

    $after_domain = gp_core_enable_root_profiles() ? $username : gp_get_members_root_slug() . '/' . $username;
    $domain       = trailingslashit( gp_get_root_domain() . '/' . $after_domain );

    // Don't use this filter.  Subject to removal in a future release.
    // Use the 'gp_core_get_user_domain' filter instead.
    $domain       = apply_filters( 'gp_core_get_user_domain_pre_cache', $domain, $user_id, $user_nicename, $user_login );
    
    return apply_filters( 'gp_core_get_user_domain', $domain, $user_id, $user_nicename, $user_login );
}