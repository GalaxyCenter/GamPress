<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/21
 * Time: 14:20
 */

defined( 'ABSPATH' ) || exit;

class GP_Games_Component extends GP_Component {

    public function __construct() {
        parent::start(
            'games',
            __('Games', 'gampress-ext'),
            GP_GAME_INCLUDES_DIR,
            array(
                'adminbar_myaccount_order' => 20
            )
        );
    }

    public function includes($includes = array()) {
        $includes = array(
            'screens',
            'functions',
            'actions',
            'ajaxs',
            'filters',
            'template'
        );

        if (!gampress()->do_autoload) {
            $includes[] = 'classes';
        }

        if (is_admin()) {
            $includes[] = 'admin';
        }

        parent::includes($includes);
    }

    public function setup_globals($args = array()) {
        $gp = gampress();

        $global_tables = array(
            'table_name_groups'              => $gp->table_prefix . 'gp_games_groups',
            'table_name_groups_members'      => $gp->table_prefix . 'gp_games_groups_members',
            'table_name_activities'          => $gp->table_prefix . 'gp_games_activities',
            'table_name_items'               => $gp->table_prefix . 'gp_games_items'
        );

        $meta_tables = array(
            'games_activity' => $gp->table_prefix . 'gp_games_activity_meta',
        );

        $args = array(
            'global_tables'         => $global_tables,
            'meta_tables'           => $meta_tables
        );

        parent::setup_globals($args);
    }

}