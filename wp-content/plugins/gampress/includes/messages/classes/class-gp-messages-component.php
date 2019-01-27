<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/15
 * Time: 21:06
 */
defined( 'ABSPATH' ) || exit;

class GP_Messages_Component extends GP_Component {

    public function __construct() {
        parent::start(
            'messages',
            __( 'Messages', 'gampress' ),
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
            'template',
            'ajaxs'
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

        // Define a slug, if necessary.
        if ( ! defined( 'GP_MESSAGES_SLUG' ) ) {
            define( 'GP_MESSAGES_SLUG', $this->id );
        }

        $global_tables = array(
            'table_name_notices'    => $gp->table_prefix . 'gp_messages_notices',
            'table_name_messages'   => $gp->table_prefix . 'gp_messages_messages',
            'table_name_recipients' => $gp->table_prefix . 'gp_messages_recipients',
            'table_name_meta'       => $gp->table_prefix . 'gp_messages_meta',
        );

        // All globals for activity component.
        // Note that global_tables is included in this array.
        $args = array(
            'global_tables'         => $global_tables
        );

        parent::setup_globals( $args );

    }
}