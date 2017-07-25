<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 11:37
 */

defined( 'ABSPATH' ) || exit;

function gp_pays_directory_permalink() {
    echo esc_url( gp_get_pays_directory_permalink() );
}

    function gp_get_pays_directory_permalink() {
        return trailingslashit( gp_get_root_domain() . '/' . gp_get_pays_slug() );
    }

function gp_is_pays_component() {
    return (bool) gp_is_current_component( 'pays' );
}

function gp_pays_slug() {
    echo gp_get_pays_slug();
}

    function gp_get_pays_slug() {
        return apply_filters( 'gp_get_pays_slug', gampress()->pays->slug );
    }

function gp_pays_alipay_notify_url() {
    echo gp_get_pays_alipay_notify_url();
}

    function gp_get_pays_alipay_notify_url() {
        return sprintf( '%s/%s/notify/alipay', gp_get_root_domain(), gp_get_pays_slug() );
    }

function gp_pays_alipay_return_url() {
    echo gp_get_pays_alipay_return_url();
}

    function gp_get_pays_alipay_return_url() {
        return sprintf( '%s/%s/return/alipay', gp_get_root_domain(), gp_get_pays_slug() );
    }

//=======================
function gp_pays_wechat_return_url() {
    echo gp_get_pays_wechat_return_url();
}

    function gp_get_pays_wechat_return_url() {
        return sprintf( '%s/%s/return/wechat', gp_get_root_domain(), gp_get_pays_slug() );
    }

function gp_pays_wechat_notify_url() {
    echo gp_get_pays_wechat_notify_url();
}

    function gp_get_pays_wechat_notify_url() {
        return sprintf( '%s/%s/notify/wechat', gp_get_root_domain(), gp_get_pays_slug() );
    }