<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/11
 * Time: 17:10
 */

function gp_sms_ytx_accound_sid() {
    echo gp_get_sms_ytx_accound_sid();
}
    function gp_get_sms_ytx_accound_sid() {
        return gp_get_option( 'gp_sms_ytx_accound_sid' );
    }

function gp_sms_ytx_auth_token() {
    echo gp_get_sms_ytx_auth_token();
}
    function gp_get_sms_ytx_auth_token() {
        return gp_get_option( 'gp_sms_ytx_auth_token' );
    }

function gp_sms_ytx_app_id() {
    echo gp_get_sms_ytx_app_id();
}
    function gp_get_sms_ytx_app_id() {
        return gp_get_option( 'gp_sms_ytx_app_id' );
    }

function gp_sms_ytx_template_code_id() {
    echo gp_get_sms_ytx_template_code_id();
}
    function gp_get_sms_ytx_template_code_id() {
        return gp_get_option( 'gp_sms_ytx_template_code_id' );
    }

function gp_is_sms_component() {
    return (bool) gp_is_current_component( 'sms' );
}