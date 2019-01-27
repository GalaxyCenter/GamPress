<?php
/**
 * GamPress Groups Classes.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage GroupsClasses
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Video object.
 *
 * @since 1.6.0
 */
class GP_Videos_Video {
    
    public $id;
    public $vid;
    public $platform;
    public $name;
    public $cover;
    public $slug;
    public $approved;
    
    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }
    
    public function populate() {
        global $wpdb;
        
        $gp    = gampress();
   
        $video = $wpdb->get_row( $wpdb->prepare( "SELECT v.* FROM {$gp->videos->table_name} v WHERE v.id = %d", $this->id ) );
        
        if ( empty( $video ) || is_wp_error( $video ) ) {
            $this->id = 0;
            return false;
        }
        
        $this->id           = (int) $video->id;
        $this->vid          = $video->vid;
        $this->title        = stripslashes( $video->title );
        $this->slug         = $video->slug;
        $this->platform     = $video->platform;
        $this->cover        = $video->cover;
        $this->approved     = $video->approved;
    }
    
    public function get( $query = null ) {
        global $wpdb;
        
        $gp = gampress();
        
        $default = array('term_id'    => 0,
                         'page'       => 1,
                         'per_page'   => 20);
        
        
        $r = wp_parse_args( $query, $defaults );
        extract( $r, EXTR_SKIP );
        
        $sql        = array();
        $total_sql  = array();
        $clause     = array();
        $tables     = array();
        
        $sql['select'] = "SELECT v.*";
        
        $tables[]   = " FROM {$gp->videos->table_name} v";
        
        if ( !empty( $term_id ) ){
            $tables[]   = " JOIN {$wpdb->term_relationships} r ON r.object_id = v.id";
            $clause[] = $wpdb->prepare( " r.term_taxonomy_id = %d", $term_id );
        }
        $sql['from']    = join( ' ', $tables );
        $sql['where'] = join( ' AND ', (array) $clause );
        if ( !empty( $sql['where'] ) ) 
            $sql['where'] = 'WHERE ' . $sql['where'];
        
        if ( !empty( $per_page ) && !empty( $page ) )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $page - 1 ) * $per_page), intval( $per_page ) );
            
        // Get paginated results
        $paged_videos_sql = join( ' ', (array) $sql );
        $paged_videos     = $wpdb->get_results( $paged_videos_sql );
        
        $sql['select'] = "SELECT COUNT(DISTINCT v.id) ";
        unset( $sql['pagination'] );
        
        $total_videos_sql = join( ' ', (array) $sql );
        $total_videos     = $wpdb->get_var( $total_videos_sql );
        
        unset( $sql );
        
        return array( 'items' => $paged_videos, 'total' => $total_videos );
    }
    
    public static function video_exists( $id ) {
        if ( empty( $id ) )
            return false;
            
        global $wpdb;
        $gp    = gampress();
        
        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->videos->table_name} WHERE id = %d", (int)$id ) );
        
        return is_numeric( $query ) ? (int) $query : $query;
    }
    
    public static function get_term_id( $id ) {
        if ( empty( $id ) )
            return false;
        
        global $wpdb;
        $gp    = gampress();
        
        $query = $wpdb->get_var( $wpdb->prepare( "SELECT term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id = %d", (int)$id ) );
        
        return is_numeric( $query ) ? (int) $query : $query;
    }
}