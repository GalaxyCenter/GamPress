<?php

/**
 * GamPress Core Function Functions
 * 的
 * @package gampressustom
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_combines_get_combine( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }
    
    $key = 'gp_ex_combine_' . $id;
    
    $combine = wp_cache_get( $key );
    if ( empty( $combine ) ) {
        $combine = new GP_Combines_Combine( $id );
        wp_cache_set( $key, $combine );
    }
    
    return $combine;
}

function gp_combines_get_combines_by_term ( $term_id, $page, $per_page, $order = 'desc') {
    $key = "gp_ex_combines_{$term_id}_{$page}_{$per_page}_{$order}";
    $datas = wp_cache_get( $key );
    if ( empty( $datas ) ) {
        $datas = GP_Combines_Combine::get_by_term( $term_id, $page, $per_page, $order);
        wp_cache_set( $key, $datas );
    }
    return $datas;
}

function gp_combines_get_combines ( $args = '' ) {
    if ( empty( $args ) )
        return false;
    
    $key = 'gp_ex_combines_' . join( '_', $args );
    $datas = wp_cache_get( $key );
    if ( empty( $datas ) ) {
        $datas = GP_Combines_Combine::get( $args );
        wp_cache_set( $key, $datas );
    }
    return $datas;
}
