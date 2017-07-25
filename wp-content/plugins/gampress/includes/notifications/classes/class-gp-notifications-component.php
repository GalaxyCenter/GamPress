<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/16
 * Time: 10:25
 */

defined( 'ABSPATH' ) || exit;

/**
 * Extends the component class to set up the Notifications component.
 */
class GP_Notifications_Component extends GP_Component {

    public function __construct() {
        parent::start(
            'notifications',
            _x( 'Notifications', 'Page <title>', 'gampress' ),
            gampress()->plugin_dir,
            array(
                'adminbar_myaccount_order' => 30
            )
        );
    }
    
    public function includes( $includes = array() ) {
        $includes = array(
            'actions',
            'screens',
            'adminbar',
            'template',
            'functions',
            'ajaxs',
            'cache',
        );

        parent::includes( $includes );
    }
    
    public function setup_globals( $args = array() ) {
        $gp = gampress();

        // Define a slug, if necessary.
        if ( ! defined( 'GP_NOTIFICATIONS_SLUG' ) ) {
            define( 'GP_NOTIFICATIONS_SLUG', $this->id );
        }

        // Global tables for the notifications component.
        $global_tables = array(
            'table_name'      => $gp->table_prefix . 'gp_notifications',
            'table_name_meta' => $gp->table_prefix . 'gp_notifications_meta',
        );

        // Metadata tables for notifications component.
        $meta_tables = array(
            'notification' => $gp->table_prefix . 'gp_notifications_meta',
        );

        // All globals for the notifications component.
        // Note that global_tables is included in this array.
        $args = array(
            'slug'          => GP_NOTIFICATIONS_SLUG,
            'has_directory' => false,
            'search_string' => __( 'Search Notifications...', 'gampress' ),
            'global_tables' => $global_tables,
            'meta_tables'   => $meta_tables
        );

        parent::setup_globals( $args );
    }
}
