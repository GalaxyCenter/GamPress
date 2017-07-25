<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/15
 * Time: 21:06
 */
defined( 'ABSPATH' ) || exit;

class GP_Message_Component extends GP_Component {

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

        // Define a slug, if necessary.
        if ( ! defined( 'GP_MESSAGES_SLUG' ) ) {
            define( 'GP_MESSAGES_SLUG', $this->id );
        }

        $global_tables = array(
            'table_name_messages'           => $gp->table_prefix . 'gp_messages',
        );

        // Fetch the default directory title.
        $default_directory_titles = gp_core_get_directory_page_default_titles();
        $default_directory_title  = $default_directory_titles[$this->id];

        // All globals for activity component.
        // Note that global_tables is included in this array.
        $args = array(
            'slug'                  => GP_MESSAGE_SLUG,
            'root_slug'             => isset( $gp->pages->messages->slug ) ? $gp->pages->messages->slug : GP_MESSAGES_SLUG,
            'has_directory'         => true,
            'directory_title'       => isset( $gp->pages->messages->title ) ? $gp->pages->messages->title : $default_directory_title,
            'notification_callback' => 'gp_messages_format_notifications',
            'search_string'         => __( 'Search Messages...', 'gampress' ),
            'global_tables'         => $global_tables
        );

        parent::setup_globals( $args );

    }
}