<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 18:21
 */

function users_screen_home() {
    if ( !gp_is_my_home() )
        return false;
    
    gp_core_load_template( 'users/single/home' );
}
add_action( 'gp_screens', 'users_screen_home' );
