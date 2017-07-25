<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/8
 * Time: 22:05
 */

function gp_sns_oauth_weibo( $name ) {
    return new GP_Sns_OAuth_Weibo();
}
add_filter( 'gp_sns_oauth_weibo', 'gp_sns_oauth_weibo' );

function gp_sns_oauth_wechat( $name ) {
    return new GP_Sns_OAuth_Wechat();
}
add_filter( 'gp_sns_oauth_wechat', 'gp_sns_oauth_wechat' );

function gp_sns_oauth_qq( $name ) {
    return new GP_Sns_OAuth_QQ();
}
add_filter( 'gp_sns_oauth_qq', 'gp_sns_oauth_qq' );