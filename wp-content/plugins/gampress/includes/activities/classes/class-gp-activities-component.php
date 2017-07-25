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

        parent::includes( $includes );
    }

    public function setup_globals( $args = array() ) {
        $gp = gampress();

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