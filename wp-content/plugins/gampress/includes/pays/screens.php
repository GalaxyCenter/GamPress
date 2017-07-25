<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/9
 * Time: 11:17
 */

function pays_success() {
    if ( !gp_is_pays_component() )
        return false;

    if (gp_is_current_action( 'success' ))
        gp_core_load_template( 'pays/success' );
    else
        gp_core_load_template( 'pays/fail' );
}
add_action( 'gp_screens', 'pays_success' );