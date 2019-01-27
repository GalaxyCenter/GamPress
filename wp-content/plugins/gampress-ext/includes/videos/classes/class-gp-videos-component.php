<?php
/**
 * GamPress Videos Loader.
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
 * Defines the GamPress Videos Component.
 *
 * @since 1.0
 */
class GP_Videos_Component extends GP_Component {
    
    public function __construct() {
        parent::start(
                'videos',
                __( 'Videos', 'gampress' ),
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
                'table_name'           => $gp->table_prefix . 'videos'
        );
        
        $args = array(
                'global_tables'         => $global_tables
                );
        parent::setup_globals( $args );
        
        $gp->current_action   = gp_current_action();
        
        if ( gp_is_videos_component() && $video_id = GP_Videos_Video::video_exists( gp_action_variable( 0 ) ) ) {
            $this->current_item = $this->current_video = gp_videos_get_video( $video_id );
        } else {
            $this->current_video = false;
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
//    
//    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
//    }
//    
//    public function setup_admin_bar( $wp_admin_nav = array() ) {
//    }
//    
//    public function setup_title() {
//    }
//    
//    public function setup_cache_groups() {
//    }
}