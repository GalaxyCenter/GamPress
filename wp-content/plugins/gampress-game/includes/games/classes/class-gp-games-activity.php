<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/21
 * Time: 17:18
 */

defined( 'ABSPATH' ) || exit;

/**
 * GamPress Section object.
 *
 * @since 1.6.0
 */
class GP_Games_Activity {

    /** @var  活动标识 */
    var $id;

    /** @var  父活动id */
    var $parent_id;

    /** @var  活动名称 */
    var $name;

    /** @var  创建者 */
    var $owner_id;

    /** @var  创建时间 */
    var $create_time;

    /** @var  栏目启用时间 */
    var $start_time;

    /** @var  栏目失效时间 */
    var $expired;

    /** @var  栏目状态 */
    var $status;

    /** @var  活动类型 */
    var $type;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $activity = $wpdb->get_row( $wpdb->prepare( "SELECT s.* FROM {$gp->games->table_name_activities} s WHERE s.id = %d LIMIT 1", $this->id ) );

        if ( empty( $activity ) || is_wp_error( $activity ) ) {
            $this->id = 0;
            return false;
        }

        $this->id                   = (int) $activity->id;
        $this->parent_id            = (int) $activity->parent_id;
        $this->name                 = $activity->name;
        $this->owner_id             = (int) $activity->owner_id;
        $this->create_time          = $activity->create_time;
        $this->start_time           = $activity->start_time;
        $this->expired              = $activity->expired;
        $this->status               = (int) $activity->status;
        $this->type                 = $activity->type;
    }

    public function save() {
        $retval = false;

        $data = array(
            'parent_id'     => $this->parent_id,
            'name'          => $this->name,
            'owner_id'      => $this->owner_id,

            'create_time'   => $this->create_time,
            'start_time'    => $this->start_time,
            'expired'       => $this->expired,

            'status'        => $this->status,
            'type'          => $this->type
        );
        $data_format = array( '%d', '%s', '%d',
            '%s', '%s', '%s',
            '%d', '%s' );

        global $wpdb;
        $gp = gampress();
        // Update.
        if ( ! empty( $this->id ) ) {
            $result = $wpdb->update( $gp->games->table_name_activities, $data, array( 'id' => $this->id ), $data_format, array( '%d' ) );

            // Set the notification ID if successful.
            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $retval   = $this->id;
            }

            // Insert.
        } else {
            $result = $wpdb->insert( $gp->games->table_name_activities, $data, $data_format );

            // Set the notification ID if successful.
            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $this->id = $wpdb->insert_id;
                $retval   = $wpdb->insert_id;
            }
        }


        // Return the result.
        return $retval;
    }

    /**************** static function ***************************/
    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'book_id'        => false,
            'search_terms'   => false,
            'orderby'        => 'id',
            'order'          => 'ASC',
            'type'           => null,
            'status'         => null,
            'page'           => 1,
            'per_page'       => 20 );


        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT a.*",
            'from'       => "{$gp->games->table_name_activities} a",
            'where'      => '',
            'orderby'    => '',
            'pagination' => '',
        );

        $where_conditions = array();
        if ( isset( $r['status'] ) ) {
            $where_conditions[] = $wpdb->prepare( " a.status = %d", $r['status'] );
        }

        if ( isset( $r['type'] ) ) {
            $where_conditions[] = $wpdb->prepare( " a.type = %s", $r['type'] );
        }

        $search = '';
        if ( isset( $r['search_terms'] ) ) {
            $search = trim( $r['search_terms'] );
        }

        if ( $search ) {
            $leading_wild = ( ltrim( $search, '*' ) != $search );
            $trailing_wild = ( rtrim( $search, '*' ) != $search );
            if ( $leading_wild && $trailing_wild ) {
                $wild = 'both';
            } elseif ( $leading_wild ) {
                $wild = 'leading';
            } elseif ( $trailing_wild ) {
                $wild = 'trailing';
            } else {
                // Default is to wrap in wildcard characters.
                $wild = 'both';
            }
            $search = trim( $search, '*' );

            $searches = array();
            $leading_wild = ( 'leading' == $wild || 'both' == $wild ) ? '%' : '';
            $trailing_wild = ( 'trailing' == $wild || 'both' == $wild ) ? '%' : '';
            $wildcarded = $leading_wild . bp_esc_like( $search ) . $trailing_wild;

            $search_columns = array( 'title', 'author' );
            if ( $r['search_columns'] ) {
                $search_columns = array_intersect( $r['search_columns'], $search_columns );
            }

            foreach ( $search_columns as $search_column ) {
                $searches[] = $wpdb->prepare( "$search_column LIKE %s", $wildcarded );
            }

            $where_conditions['search'] = '(' . implode(' OR ', $searches) . ')';
        }
        
        $where = '';
        if ( ! empty( $where_conditions ) ) {
            $sql['where'] = implode( ' AND ', $where_conditions );
            $where = "WHERE {$sql['where']}";
        }

        if ( !empty( $sql['where'] ) )
            $sql['where'] = 'WHERE ' . $sql['where'];

        /* order/orderby ********************************************/
        $order   = $r['order'];
        $orderby = $r['orderby'];

        $order   =  gp_esc_sql_order( $order );
        $orderby = self::convert_orderby_to_order_by_term( $orderby );

        if ( 'rand()' === $orderby ) {
            $sql['orderby'] = "ORDER BY rand()";
        } else {
            $sql['orderby'] = "ORDER BY {$orderby} {$order}";
        }

        if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) && $r['per_page'] != -1 )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page'] ), intval( $r['per_page'] ) );

        // Get paginated results
        //$paged_chapters_sql = join( ' ', (array) $sql );
        $paged_items_sql = "{$sql['select']} FROM {$sql['from']} {$where} {$sql['orderby']} {$sql['pagination']}";
        $paged_items = $wpdb->get_results( $paged_items_sql );

        $total_items_sql = "SELECT COUNT(DISTINCT a.id) FROM {$sql['from']} $where";
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {

            default :
                $order_by_term = 'a.id';
        }

        return $order_by_term;
    }
}