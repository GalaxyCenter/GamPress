<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 12:34
 */

function gp_pays_alipay( $name ) {
    return new GP_Pays_Alipay();
}
add_filter( 'gp_pays_alipay', 'gp_pays_alipay' );

function gp_pays_wechat( $name ) {
    return new GP_Pays_Wechat();
}
add_filter( 'gp_pays_wechat', 'gp_pays_wechat' );

function gp_pays_wechat_pack( $name ) {
    return new GP_Pays_Wechat_Pack();
}
add_filter( 'gp_pays_wechat_pack', 'gp_pays_wechat_pack' );