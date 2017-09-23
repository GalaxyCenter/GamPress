<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/2
 * Time: 14:32
 */

class GP_Activities_Component extends GP_Component {

    public function __construct() {
        parent::start(
            'activities',
            __( 'Activities', 'gampress' ),
            gampress()->includes_dir,
            array(
                'adminbar_myaccount_order' => 20
            )
        );
    }

    public function includes( $includes = array() ) {
        $includes = array(
            'actions',
            'filters',
            'functions',
            'screens',
            'template'
        );

        if (!gampress()->do_autoload) {
            $includes[] = 'classes';
        }

        if (is_admin()) {
            $includes[] = 'admin';
        }

        parent::includes( $includes );
    }

    public function setup_globals( $args = array() ) {
        $gp = gampress();

        // status
        if ( ! defined( 'GP_ACTIVITY_APPROVED' ) ) {
            /** 已审核 */
            define( 'GP_ACTIVITY_APPROVED', 0x0001 );
        }
        if ( ! defined( 'GP_ACTIVITY_APPROVAL_PENDING' ) ) {
            /** 审核中 */
            define( 'GP_ACTIVITY_APPROVAL_PENDING', 0x0002 );
        }
        if ( ! defined( 'GP_ACTIVITY_DISAPPROVED' ) ) {
            /** 未批准 */
            define( 'GP_ACTIVITY_DISAPPROVED', 0x0004 );
        }

        $global_tables = array(
            'table_name_activities'      => $gp->table_prefix . 'gp_activities'
        );

        $args = array(
            'has_directory' => false,
            'search_string' => __( 'Search Notifications...', 'gampress' ),
            'global_tables' => $global_tables
        );

        parent::setup_globals( $args );
    }
}