<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 12:59
 */

function gp_pays_alipay_id() {
    echo gp_get_pays_alipay_id();
}
    function gp_get_pays_alipay_id() {
        return gp_get_option( 'gp_pays_alipay_id' );
    }

function gp_pays_alipay_partner() {
    echo gp_get_pays_alipay_partner();
}
    function gp_get_pays_alipay_partner() {
        return gp_get_option( 'gp_pays_alipay_partner' );
    }

function gp_pays_alipay_key() {
    echo gp_get_pays_alipay_key();
}
    function gp_get_pays_alipay_key() {
        return gp_get_option( 'gp_pays_alipay_key' );
    }

function gp_pays_wechat_app_id() {
    echo gp_get_pays_wechat_app_id();
}
    function gp_get_pays_wechat_app_id() {
        return gp_get_option( 'gp_pays_wechat_app_id' );
    }

function gp_pays_wechat_app_secret() {
    echo gp_get_pays_wechat_app_secret();
}
    function gp_get_pays_wechat_app_secret() {
        return gp_get_option( 'gp_pays_wechat_app_secret' );
    }

function gp_pays_wechat_mchid() {
    echo gp_get_pays_wechat_mchid();
}

    function gp_get_pays_wechat_mchid() {
        return gp_get_option( 'gp_pays_wechat_mchid' );
    }

function gp_pays_wechat_key() {
    echo gp_get_pays_wechat_key();
}
    function gp_get_pays_wechat_key() {
        return gp_get_option( 'gp_pays_wechat_key' );
    }