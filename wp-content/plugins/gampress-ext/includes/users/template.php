<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 18:21
 */

function gp_is_user_profile() {
    return (bool) ( gp_is_user() && (bool) gp_is_current_component( 'profile' ) );
}

function gp_is_user_record() {
    return (bool) ( gp_is_user() && (bool) gp_is_current_component( 'record' ) );
}

function gp_is_user_bookmark() {
    return (bool) ( gp_is_user() && (bool) gp_is_current_component( 'bookmark' ) );
}

function gp_is_user_recharge() {
    return (bool) ( gp_is_user() && (bool) gp_is_current_component( 'recharge' ) );
}

function gp_is_user_msg() {
    return (bool) ( gp_is_user() && (bool) gp_is_current_component( 'msg' ) );
}

function gp_is_user_book() {
    return (bool) ( gp_is_user() && (bool) gp_is_current_component( 'book' ) );
}