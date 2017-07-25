<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/11
 * Time: 15:34
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the GamPress Sms Component.
 *
 * @since 1.0
 */
class GP_Sms_Component extends GP_Component {
    public $types = array();

    public function __construct() {
        parent::start(
            'sms',
            __( 'Sms', 'gampress' ),
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
        if ( ! defined( 'GP_SMS_SLUG' ) ) {
            define( 'GP_SMS_SLUG', $this->id );
        }

        $global_tables = array(
            'table_name'           => $gp->table_prefix . 'gp_sms',
        );

        // Fetch the default directory title.
        $default_directory_titles = gp_core_get_directory_page_default_titles();
        $default_directory_title  = $default_directory_titles[$this->id];

        // All globals for activity component.
        // Note that global_tables is included in this array.
        $args = array(
            'slug'                  => GP_SMS_SLUG,
            'root_slug'             => isset( $gp->pages->sms->slug ) ? $gp->pages->sms->slug : GP_SMS_SLUG,
            'has_directory'         => true,
            'directory_title'       => isset( $gp->pages->sms->title ) ? $gp->pages->sms->title : $default_directory_title,
            'notification_callback' => 'gp_sms_format_notifications',
            'search_string'         => __( 'Search Sms...', 'gampress' ),
            'global_tables'         => $global_tables
        );

        parent::setup_globals( $args );

    }

    public function setup_actions() {
        parent::setup_actions();
    }

    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
    }

    public function setup_title() {
        parent::setup_title();
    }

    public function setup_cache_groups() {
        parent::setup_cache_groups();
    }
}