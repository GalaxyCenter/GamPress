<?php
/**
 * Created by PhpStorm.
 * User: Texel
 * Date: 2017/2/22
 * Time: 16:46
 */

function gp_services_current_service_id() {
    echo gp_get_services_current_service_id();
}

    function gp_get_services_current_service_id() {
        $id = gp_action_variable( 0 );
        if ( empty( $id ) ) {
            $id = 0;
        }

        return (int) $id;
    }

function gp_service_id( $service ) {
    echo gp_get_service_id( $service );
}
function gp_get_service_id( $service ) {
    return $service->id;
}

function gp_service_name( $service ) {
    echo gp_get_service_name( $service );
}
    function gp_get_service_name( $service ) {
        return $service->name;
    }

function gp_service_unit( $service ) {
    $cunit = gp_get_service_unit( $service );
    $sunits = gp_service_get_units();

    echo $sunits[$cunit];
}
    function gp_get_service_unit( $service ) {
        return $service->unit;
    }

function gp_service_price( $service ) {
    echo gp_get_service_price( $service );
}
    function gp_get_service_price( $service ) {
        return $service->price;
    }

function gp_service_description( $service ) {
    echo gp_get_service_description( $service );
}
    function gp_get_service_description( $service ) {
        return $service->description;
    }

function gp_service_type( $service ) {
    $ctype = gp_get_service_type( $service );
    $stypes = gp_service_get_types();

    echo $stypes[$ctype];
}
    function gp_get_service_type( $service ) {
        return $service->type;
    }

function gp_service_avatar( $args = '' ) {
    echo gp_get_service_avatar( $args );
}


function gp_service_user_link( $service ) {
    echo gp_get_service_user_link( $service );
}

    function gp_get_service_user_link( $service ) {
        $link = bp_core_get_user_domain( $service->user_id );
        return $link;
    }
    
function gp_service_edit_link( $service ) {
    echo gp_get_service_edit_link( $service );
}

    function gp_get_service_edit_link( $service ) {
        if ( empty( $service ) )
            return trailingslashit( bp_loggedin_user_domain() . gp_get_service_slug() . '/edit/' );
        else
            return trailingslashit( bp_loggedin_user_domain() . gp_get_service_slug() . '/edit/' . $service->id );
    }

function gp_service_slug() {
    echo gp_get_service_slug();
}

    function gp_get_service_slug() {
        return gampress()->services->slug;
    }
    
function gp_get_service_avatar( $args = '' ) {
    $gp = gampress();
    
    $type_default = 'thumb';

    $alt_default = !empty( $dn_default ) ? sprintf( __( 'Profile picture of %s', 'buddypress' ), $dn_default ) : __( 'Profile picture', 'buddypress' );

    $defaults = array(
        'alt'     => $alt_default,
        'class'   => 'avatar',
        'email'   => false,
        'type'    => $type_default,
        'user_id' => false
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( !isset( $height ) && !isset( $width ) ) {

        // Backpat.
        if ( isset( $gp->avatar->full->height ) || isset( $gp->avatar->thumb->height ) ) {
            $height = ( 'full' == $type ) ? $gp->avatar->full->height : $gp->avatar->thumb->height;
        } else {
            $height = 20;
        }

        // Backpat.
        if ( isset( $gp->avatar->full->width ) || isset( $gp->avatar->thumb->width ) ) {
            $width = ( 'full' == $type ) ? $gp->avatar->full->width : $gp->avatar->thumb->width;
        } else {
            $width = 20;
        }
    }

    $object  = apply_filters( 'bp_get_activity_avatar_object_' . $current_activity_item->component, 'user' );
    $item_id = !empty( $user_id ) ? $user_id : $current_activity_item->user_id;

    $item_id = apply_filters( 'bp_get_activity_avatar_item_id', $item_id );

    // If this is a user object pass the users' email address for Gravatar so we don't have to prefetch it.
    if ( 'user' == $object && empty( $user_id ) && empty( $email ) && isset( $current_activity_item->user_email ) ) {
        $email = $current_activity_item->user_email;
    }

    return apply_filters( 'bp_get_activity_avatar', bp_core_fetch_avatar( array(
        'item_id' => $item_id,
        'object'  => $object,
        'type'    => $type,
        'alt'     => $alt,
        'class'   => $class,
        'width'   => $width,
        'height'  => $height,
        'email'   => $email
    ) ) );
}

function gp_is_my_service() {
    if ( is_user_logged_in() && bp_loggedin_user_id() == bp_displayed_user_id() ) {
        $my_service = true;
    } else {
        $my_service = false;
    }
    
    return apply_filters( 'gp_is_my_service', $my_service );
}