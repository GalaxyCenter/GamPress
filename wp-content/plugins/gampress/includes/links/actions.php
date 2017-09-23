<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/9/2
 * Time: 7:48
 */

function gp_links_redirect() {
    if ( !gp_is_links_component() )
        return false;

    $link =  urldecode( $_SERVER['REQUEST_URI'] );
    $link = str_replace( '/links?', '', $link );

    $link = apply_filters( 'gp_links_redirect', $link );
    gp_core_redirect( $link );
}
add_action( 'gp_actions', 'gp_links_redirect' );
