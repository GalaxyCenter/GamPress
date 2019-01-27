<?php
/**
 * GamPress Combines Loader.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @sugpackage Sns
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the GamPress Combines Component.
 *
 * @since 1.0
 */
class GP_Combines_Component extends GP_Component {
    public function __construct() {
        parent::start(
                'combines',
                __( 'Combines', 'gampress' ),
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
        
        $global_tables = array(
                'table_name'           => $gp->table_prefix . 'combines'
                );
        
        $args = array(
                'global_tables'         => $global_tables
                );
        parent::setup_globals( $args );
        
        $gp->current_action   = gp_current_action();
        if ( gp_is_combines_component() && $combine_id = GP_Combines_Combine::combine_exists( gp_action_variable( 0 ) ) ) {
            $gp->is_single_item  = true;
            $this->current_item = $this->current_combine = gp_combines_get_combine( $combine_id );
        } else {
            $this->current_combine = false;
            $this->current_item = urldecode( gp_action_variable( 0 ) );
            
            if ( !empty( $this->current_item ) ) {
                $this->current_page = gp_action_variable( 1 );
                if ( !empty( $this->current_page ) && !is_numeric( $this->current_page ) )  {
                    gp_do_404();                
                    die;
                }
            }
            if ( empty( $this->current_page ) )
                $this->current_page = 1;
        }       
    }

    public function setup_title() {
        if ( gp_is_combines_component() ) {
            $gp = gampress();

            $gp->gp_options_title = _x( 'Combines Categories', 'Page title', 'gampress-ext' );
        }
        
        parent::setup_title();
    }
}