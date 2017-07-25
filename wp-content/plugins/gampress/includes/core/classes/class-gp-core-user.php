<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 22:38
 */

defined( 'ABSPATH' ) || exit;

class GP_Core_User {

    public $id;

    public $fullname;

    public static function get_core_userdata( $user_id ) {
        global $wpdb;

        if ( !$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->users} WHERE ID = %d LIMIT 1", $user_id ) ) )
            return false;

        return $user;
    }
}