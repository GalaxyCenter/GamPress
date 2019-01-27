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
class GP_Combines_Combine {
    public $id;
    public $title;
    public $post_date;
    public $size;
    public $tags;
    public $description;
    public $links;
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
        
        $combine = $wpdb->get_row( $wpdb->prepare( "SELECT e.* FROM {$gp->combines->table_name} e WHERE e.id = %d", $this->id ) );
        
        if ( empty( $combine ) || is_wp_error( $combine ) ) {
            $this->id = 0;
            return false;
        }
        
        $this->id           = (int) $combine->id;
        $this->title        = stripslashes( $combine->title );
        $this->post_date    = $combine->post_date;
        $this->size         = $combine->size;
        $this->tags         = $combine->tags;
        $this->links        = json_decode( $combine->links ); 
        $this->description  = $combine->desc;
        //$this->approved     = $combine->approved;
    }
    
    public static function get_by_term( $term_id, $page, $per_page, $order = 'description') {
        global $wpdb;

        $sql        = array();
        $clause     = array();
        $tables     = array();
        
        $sql['select'] = "SELECT t.*";
        
        $tables[]   = " FROM {$wpdb->term_relationships} t";

        if ( !empty($term_id) )
            $clause[] = $wpdb->prepare( " t.term_taxonomy_id = %d", $term_id );
        $sql['from']    = join( ' ', $tables );
        $sql['where'] = join( ' AND ', (array) $clause );
        if ( !empty( $sql['where'] ) ) 
            $sql['where'] = 'WHERE ' . $sql['where'];

        if ( $order == 'desc' )
            $sql['orderby'] = "ORDER BY object_id desc";
        else
            $sql['orderby'] = "ORDER BY object_id asc";

        if ( ! empty( $per_page ) && ! empty( $page ) && $per_page != -1 )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $page - 1 ) * $per_page ), intval( $per_page ) );
        
        // Get paginated results
        $objects_sql = join( ' ', (array) $sql );
        $objects     = $wpdb->get_results( $objects_sql );
        
        $items = array();
        foreach( $objects as $obj ) {
            $items[] = gp_combines_get_combine( $obj->object_id );
        }
        
        $sql['select'] = "SELECT COUNT(DISTINCT t.object_id) ";
        unset( $sql['pagination'], $sql['orderby'] );

        $total_sql = join( ' ', (array) $sql );
        $total = (int) wp_cache_get( $total_sql );
        if ( empty( $total ) ) {
            $total     = $wpdb->get_var( $total_sql );
            wp_cache_set( $total_sql, $total );
        }

        
        unset( $sql, $total_sql, $objects_sql );
        
        return array( 'items' => $items, 'total' => $total );
    }
    
    public static function get( $args = null ) {
        global $wpdb;
        
        $gp = gampress();
        
        $defaults = array('term_id'    => 0,
                        'orderby'     => 'post_date',
                        'order'       => 'DESC',
                        'page'        => 1,
                        'per_page'    => 20 );
        
        
        $r = wp_parse_args( $args, $defaults );
        
        $sql        = array();
        $clause     = array();
        $tables     = array();
        
        $sql['select'] = "SELECT e.*";
        
        $tables[]   = " FROM {$gp->combines->table_name} e";
        
        if ( !empty( $r['term_id'] ) ){
            //性能太差,建议使用 get_by_term
            //$tables[]   = " JOIN {$wpdb->term_relationships} r ON r.object_id = e.id";
            //$clause[] = $wpdb->prepare( " r.term_taxonomy_id = %d", $r['term_id'] );
        }
        $sql['from']    = join( ' ', $tables );
        $sql['where'] = join( ' AND ', (array) $clause );
        if ( !empty( $sql['where'] ) ) 
            $sql['where'] = 'WHERE ' . $sql['where'];
        
        /* Order/orderby ********************************************/
        $order   = $r['order'];
        $orderby = $r['orderby'];
        
        $order = gp_esc_sql_order( $order );
        if ( 'rand()' === $orderby ) {
            $sql['orderby'] = "ORDER BY rand()";
        } else {
            $sql['orderby'] = "ORDER BY {$orderby} {$order}";
        }
        
        if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) && $r['per_page'] != -1 )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page'] ), intval( $r['per_page'] ) );
        
        // Get paginated results
        $item_sql = join( ' ', (array) $sql );
        $items     = $wpdb->get_results( $item_sql );
        
        $sql['select'] = "SELECT COUNT(DISTINCT e.id) ";
        unset( $sql['pagination'], $sql['orderby'] );
        
        $total_sql = join( ' ', (array) $sql );
        $total = (int) wp_cache_get( $total_sql );
        if ( empty( $total ) ) {
            $total     = $wpdb->get_var( $total_sql );
            wp_cache_set( $total_sql, $total );
        }

        
        unset( $sql );
        
        return array( 'items' => $items, 'total' => $total );
    }
    
    public static function combine_exists( $id ) {
        if ( empty( $id ) )
            return false;
        
        global $wpdb;
        $gp    = gampress();
        
        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->combines->table_name} WHERE id = %d", (int)$id ) );
        
        return is_numeric( $query ) ? (int) $query : $query;
    }
    
    public static function get_term_ids( $id ) {
        if ( empty( $id ) )
            return false;
        
        global $wpdb;
        $gp    = gampress();
        
        $ids = $wpdb->get_col( $wpdb->prepare( "SELECT term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id = %d", (int)$id ) );
        
        return $ids;
    }
}