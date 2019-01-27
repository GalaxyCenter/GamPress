<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/19
 * Time: 13:30
 */
switch ( gp_current_action() ) :

    case 'view':
        gp_get_template_part( 'users/single/missions/single' );
        break;

    case 'edit' :
        gp_get_template_part( 'users/single/missions/edit' );
        break;

    case 'list' :
        gp_get_template_part( 'users/single/missions/list' );
        break;

    case 'complete' :
        gp_get_template_part( 'users/single/missions/complete' );
        break;
        
endswitch;