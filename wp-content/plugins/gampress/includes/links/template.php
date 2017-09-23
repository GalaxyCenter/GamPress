<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/9/2
 * Time: 9:22
 */

function gp_is_links_component() {
    return (bool) gp_is_current_component( 'links' );
}

function gp_get_links_permalink( $link ) {
    return gp_get_links_directory_permalink() . '?' . urlencode( $link );
}

function gp_links_directory_permalink() {
    echo esc_url( gp_get_links_directory_permalink() );
}
    
    function gp_get_links_directory_permalink() {
        return gp_get_root_domain() . '/' . gp_get_links_root_slug();
    }

function gp_links_root_slug() {
    echo gp_get_links_root_slug();
}
    function gp_get_links_root_slug() {
        return apply_filters( 'gp_get_links_root_slug', gampress()->links->root_slug );
    }