<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/19
 * Time: 16:30
 */

defined( 'ABSPATH' ) || exit;

class GP_Votes_Component extends GP_Component {

    public function __construct() {
        parent::start(
            'votes',
            __( 'Votes', 'gampress' ),
            gampress()->includes_dir,
            array()
        );
    }

    public function includes( $includes = array() ) {
        $includes = array(
            'functions',
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
        if ( ! defined( 'GP_VOTES_SLUG' ) ) {
            define( 'GP_VOTES_SLUG', $this->id );
        }

        $global_tables = array(
            'table_name_votes'           => $gp->table_prefix . 'gp_votes',
        );


        // All globals for activity component.
        // Note that global_tables is included in this array.
        $args = array(
            'slug'                  => GP_VOTES_SLUG,
            'root_slug'             => '',
            'has_directory'         => true,
            'directory_title'       =>'',
            'notification_callback' => 'gp_votes_format_notifications',
            'search_string'         => __( 'Search Votes...', 'gampress' ),
            'global_tables'         => $global_tables
        );

        parent::setup_globals( $args );

    }
    
}