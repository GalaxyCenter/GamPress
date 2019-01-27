<?php

/**
 * GamPress-Ext Video Functions
 *
 * ⊙▂⊙
 * 
 * @package gampress
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_videos_get_video( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }
    
    $key = 'gp_ex_video_' . $id;
    
    $video = wp_cache_get( $key );
    if ( empty( $video ) ) {
        $video = new GP_Videos_Video( $id );
        wp_cache_set( $key, $video );
    }
    
    return apply_filters( 'gp_videos_get_video', $video );
}

function gp_videos_get_videos ( $args = '' ) {
    if ( empty( $args ) )
        return false;
    
    $key = 'gp_ex_videos_' . join( '_', $args );
    $datas = wp_cache_get( $key );
    if ( empty( $datas ) ) {
        $datas = GP_Videos_Video::get( $args );
        wp_cache_set( $key, $datas );
    }
    return $datas;
}