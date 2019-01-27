<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 11:50
 */

defined( 'ABSPATH' ) || exit;

class GP_Missions_Component extends GP_Component {
    public function __construct() {
        parent::start(
            'missions',
            __( 'Missions', 'gampress' ),
            GP_EXT_INCLUDES_DIR,
            array(
                'adminbar_myaccount_mission' => 20
            )
        );
    }

    public function includes( $includes = array() ) {
        $includes = array(
            'screens',
            'functions',
            'actions',
            'filters',
            'template'
        );

        if ( ! gampress()->do_autoload ) {
            $includes[] = 'classes';
        }

        if ( is_admin() ) {
            $includes[] = 'admin';
        }

        parent::includes( $includes );
    }

    public function setup_globals( $args = array() ) {
        $gp = gampress();

        if ( !defined( 'GP_MISSIONS_SLUG' ) ) {
            define( 'GP_MISSIONS_SLUG', $this->id );
        }

        define( 'GP_MISSION_1', 1 );
        define( 'GP_MISSION_2', 2 );
        define( 'GP_MISSION_3', 3 );
        define( 'GP_MISSION_4', 4 );

        $global_tables = array(
            'table_name'           => $gp->table_prefix . 'gp_missions'
        );

        $args = array(
            'slug'                  => GP_MISSIONS_SLUG,
            'global_tables'         => $global_tables
        );

        parent::setup_globals( $args );

        $gp->current_action   = gp_current_action();
        if ( gp_is_missions_component() && $mission_id = GP_Missions_Mission::mission_exists( gp_action_variable( 0 ) ) ) {
            $gp->is_single_item  = true;
            $this->current_item = $this->current_mission = gp_missions_get_mission( $mission_id );
        } else {
            $this->current_mission = false;
            $this->current_item = urldecode( gp_action_variable( 0 ) );

            if ( !empty( $this->current_item ) ) {
                $this->current_page = gp_action_variable( 1 );
                if ( !empty( $this->current_page ) && !is_numeric( $this->current_page ) )  {
                    gp_do_404();
                    die;
                }
            }
            if ( empty( $this->current_page ) )
                $this->current_page = 1;
        }
    }
}