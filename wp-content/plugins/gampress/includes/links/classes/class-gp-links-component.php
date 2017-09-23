<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/9/2
 * Time: 7:46
 */

class GP_Links_Component extends GP_Component {

    public function __construct() {
        parent::start(
            'links',
            __( 'Links', 'gampress' ),
            gampress()->includes_dir,
            array()
        );
    }

    public function includes( $includes = array() ) {
        $includes = array(
            'actions',
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
        if ( ! defined( 'GP_LINKS_SLUG' ) ) {
            define( 'GP_LINKS_SLUG', $this->id );
        }

        // Global tables for activity component.
        $global_tables = array(
        );

        // Metadata tables for groups component.
        $meta_tables = array(
        );

        // Fetch the default directory title.
        $default_directory_titles = gp_core_get_directory_page_default_titles();
        $default_directory_title  = $default_directory_titles[$this->id];

        // All globals for activity component.
        // Note that global_tables is included in this array.
        $args = array(
            'slug'                  => GP_LINKS_SLUG,
            'root_slug'             => isset( $gp->pages->links->slug ) ? $gp->pages->links->slug : GP_LINKS_SLUG,
            'has_directory'         => true,
            'directory_title'       => isset( $gp->pages->links->title ) ? $gp->pages->links->title : $default_directory_title,
            'global_tables'         => $global_tables,
            'meta_tables'           => $meta_tables,
        );

        parent::setup_globals( $args );

    }

}