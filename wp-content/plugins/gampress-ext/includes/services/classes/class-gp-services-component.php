<?php
/**
 * GamPress Works Loader.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage Sns
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the GamPress Works Component.
 *
 * @since 1.0
 */
class GP_Services_Component extends GP_Component {
    public function __construct() {
        parent::start(
                'services',
                __( 'Services', 'gampress' ),
                GP_EXT_INCLUDES_DIR,
                array(
                    'adminbar_myaccount_order' => 20
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
        
        if ( !defined( 'GP_SERVICES_SLUG' ) ) {
            define( 'GP_SERVICES_SLUG', $this->id );
        }
        
        $global_tables = array(
            'table_name'           => $gp->table_prefix . 'gp_services'
        );

        $args = array(
            'slug'                  => GP_SERVICES_SLUG,
            'global_tables'         => $global_tables
        );

        parent::setup_globals( $args );
    }
}