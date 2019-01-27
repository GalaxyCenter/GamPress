<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 18:21
 */

function gp_users_get_meta( $user_id = 0, $meta_key = '', $default_value = false, $single = true ) {
    $retval = gp_get_metadata( 'user', $user_id, $meta_key, $default_value, $single );
    return $retval;
}