<?php
/**
 * Created by PhpStorm.
 * User: kuibo
* Date: 2017/3/21
* Time: 22:50
*/

function missions_screen_index() {
    if ( gp_is_missions_directory() && is_super_admin() ) {
        gp_update_is_directory( true, 'missions' );
        gp_core_load_template( 'missions/index' );
    }
}
add_action( 'gp_screens', 'missions_screen_index' );