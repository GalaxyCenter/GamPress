<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 18:20
 */

function users_get_members_slug( $component ) {
    return 'users';
}
add_filter( 'gp_get_members_slug', 'users_get_members_slug', 10, 1 );
add_filter( 'gp_get_members_root_slug', 'users_get_members_slug', 10, 1 );