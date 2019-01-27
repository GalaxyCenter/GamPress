<?php

/**
 * GamPress Core Template Functions
 * 的
 * @package gampressustom
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_get_current_video() {
    $gp = gampress();
    
    return $gp->videos->current_video;
}

function gp_is_videos_component() {
    return gp_is_current_component( 'videos' );
}

function gp_video_title( $video ) {
    echo gp_get_video_title( $video );
}
    function gp_get_video_title( $video ) {
        return $video->title;
    }
    
function gp_video_cover( $video ) {
    echo gp_get_video_cover( $video );
}
    function gp_get_video_cover( $video ) {
        return '/wp-content/uploads/videos/' . $video->cover;
    }

function gp_get_video_term( $id ) {
    $term_id = GP_Videos_Video::get_term_id( $id );
    
    return get_term_by( 'term_id', $term_id, 'cinema' );
}

function gp_video_term_name( $id ) {
    echo gp_get_video_term_name( $id );
}
    
    function gp_get_video_term_name( $id ) {
        $term = gp_get_video_term( $id );
        return $term->name;
    }

function gp_video_permalink( $video ) {
    echo gp_get_video_permalink( $video );
}

    function gp_get_video_permalink( $video ) {
        return "/videos/show/$video->id";
    }
    
function gp_video_raw_code( $video ) {
    echo gp_get_video_raw_code( $video );
}
    function gp_get_video_raw_code( $video ) {
        $permalink = false;
        
        switch( $video->platform ) {
        case 'iqiyi':
            $permalink = "http://www.iqiyi.com/{$video->vid}.html";
            break;
            
        case 'tudou':
            $permalink = "http://www.tudou.com/programs/view/{$video->vid}/";
            break;
            
        case 'youku':
            $permalink = "http://v.youku.com/v_show/id_{$video->vid}.html";
            break;
            
        case '56':
            $permalink = "http://www.56.com/u67/v_{$video->vid}.html";
            break;
            
        case 'pps':
            break;
            
        case 'ku6':
            $permalink = "http://v.ku6.com/show/{$video->vid}.html";
            break;
            
        case 'tx':
            $permalink = "http://static.video.qq.com/TPout.swf?vid={$video->vid}&auto=0";
            break;
            
        case 'souhu':
            $permalink = "http://share.vrs.sohu.com/my/v.swf&topBar=1&id={$video->vid}&autoplay=false&from=page";
            break;
            
        case 'sina':
            $permalink = "http://video.sina.com.cn/view/{$video->vid}.html";
            break;
        }
        
        global $wp_embed;
        
        return $wp_embed->run_shortcode( "[embed width='800px' height='600px']{$permalink}[/embed]");
    }