<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/19
 * Time: 18:51
 */

function gp_is_missions_component() {
    return gp_is_current_component( 'missions' );
}

function gp_missions_root_slug() {
    echo gp_get_missions_slug();
}

    function gp_get_missions_slug() {
        return gampress()->missions->slug;
    }

function gp_mission_custom_name( $mission ) {
    echo gp_get_mission_custom_name( $mission );
}
    function gp_get_mission_custom_name( $mission ) {
        if ( empty( $mission ) )
            return false;
    
        if ( $mission instanceof GP_Missions_Mission || is_object( $mission ) ) {
            $_mission = $mission;
        } else if ( is_numeric( $mission ) ) {
            $_mission = gp_get_mission( $mission );
        } else if ( is_array( $mission ) ) {
            $_mission = array2obj( $mission );
        } else {
            return false;
        }

        return $_mission->custom_name;
    }


function gp_mission_card( $mission ) {
    echo gp_get_mission_card( $mission );
}
    function gp_get_mission_card( $mission ) {
        if ( empty( $mission ) )
            return false;

        if ( $mission instanceof GP_Missions_Mission || is_object( $mission ) ) {
            $_mission = $mission;
        } else if ( is_numeric( $mission ) ) {
            $_mission = gp_get_mission( $mission );
        } else if ( is_array( $mission ) ) {
            $_mission = array2obj( $mission );
        } else {
            return false;
        }

        return $_mission->card;
    }

function gp_mission_phone( $mission ) {
    echo gp_get_mission_phone( $mission );
}
    function gp_get_mission_phone( $mission ) {
        if ( empty( $mission ) )
            return false;

        if ( $mission instanceof GP_Missions_Mission || is_object( $mission ) ) {
            $_mission = $mission;
        } else if ( is_numeric( $mission ) ) {
            $_mission = gp_get_mission( $mission );
        } else if ( is_array( $mission ) ) {
            $_mission = array2obj( $mission );
        } else {
            return false;
        }

        return $_mission->phone;
    }

function gp_mission_gender( $mission ) {
    $gender = gp_get_mission_gender( $mission );
    echo gp_get_gender_desc( $gender );
}
function gp_get_mission_gender( $mission ) {
    if ( empty( $mission ) )
        return false;

    if ( $mission instanceof GP_Missions_Mission || is_object( $mission ) ) {
        $_mission = $mission;
    } else if ( is_numeric( $mission ) ) {
        $_mission = gp_get_mission( $mission );
    } else if ( is_array( $mission ) ) {
        $_mission = array2obj( $mission );
    } else {
        return false;
    }

    return $_mission->gender;
}

function gp_mission_post_time( $mission ) {
    echo gp_get_mission_post_time( $mission );
}
    function gp_get_mission_post_time( $mission ) {
        if ( empty( $mission ) )
            return false;

        if ( $mission instanceof GP_Missions_Mission || is_object( $mission ) ) {
            $_mission = $mission;
        } else if ( is_numeric( $mission ) ) {
            $_mission = gp_get_mission( $mission );
        } else if ( is_array( $mission ) ) {
            $_mission = array2obj( $mission );
        } else {
            return false;
        }

        return $_mission->post_time;
    }

function gp_mission_remark( $mission ) {
    echo gp_get_mission_remark( $mission );
}
    function gp_get_mission_remark( $mission ) {
        if ( empty( $mission ) )
            return false;

        if ( $mission instanceof GP_Missions_Mission || is_object( $mission ) ) {
            $_mission = $mission;
        } else if ( is_numeric( $mission ) ) {
            $_mission = gp_get_mission( $mission );
        } else if ( is_array( $mission ) ) {
            $_mission = array2obj( $mission );
        } else {
            return false;
        }

        return $_mission->remark;
    }

function gp_get_gender_desc( $gender ) {
    $desc = '';

    switch( $gender ) {
        case 1:
            $desc = __('男', 'gp');
            break;

        case2:
            $desc = __('女', 'gp');
            break;
    }

    return $desc;
}

function gp_mission_status( $mission ) {
    echo gp_get_mission_status( $mission );
}
    function gp_get_mission_status( $mission ) {
        if ( empty( $mission ) )
            return false;

        if ( $mission instanceof GP_Missions_Mission || is_object( $mission ) ) {
            $_mission = $mission;
        } else if ( is_numeric( $mission ) ) {
            $_mission = gp_get_mission( $mission );
        } else if ( is_array( $mission ) ) {
            $_mission = array2obj( $mission );
        } else {
            return false;
        }

        $desc = gp_get_status_description( $_mission->status );

        return $desc;
    }

function gp_status_description( $status ) {
    echo gp_get_status_description( $status );
}
    function gp_get_status_description( $status ) {
        $desc = '';

        switch( $status ) {
            case GP_ORDER_1:
                $desc = __( '报备提交', 'gp' );
                break;

            case GP_ORDER_2:
                $desc = __( '带看中', 'gp' );
                break;

            case GP_ORDER_3:
                $desc = __( '交易中', 'gp' );
                break;

            case GP_ORDER_4:
                $desc = __( '结佣', 'gp' );
                break;
        }

        return $desc;
    }

function gp_get_current_mission() {
    $gp = gampress();

    return $gp->missions->current_mission;
}

function gp_is_missions_directory() {
    if ( ! gp_displayed_user_id() && gp_is_missions_component() && ! gp_current_action() ) {
        return true;
    }
    return false;
}