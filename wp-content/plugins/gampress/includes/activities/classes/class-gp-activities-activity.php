<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/12
 * Time: 20:31
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class GP_Activities_Activity {
    public $id;
    public $user_id;
    public $component;
    public $type;
    public $content;
    public $item_id;
    public $parent_id;
    public $likes;
    public $post_time;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();

        $activity = $wpdb->get_row( $wpdb->prepare( "SELECT a.* FROM {$gp->activities->table_name_activities} a WHERE a.id = %d", $this->id ) );

        if ( empty( $activity ) || is_wp_error( $activity ) ) {
            $this->id = 0;
            return false;
        }

        $this->id           = (int) $activity->id;
        $this->user_id      = (int) $activity->user_id;
        $this->component    = $activity->component;
        $this->type         = $activity->type;
        $this->content      = $activity->content;
        $this->item_id      = (int) $activity->item_id;
        $this->parent_id    = (int) $activity->parent_id;
        $this->likes        = (int) $activity->likes;
        $this->post_time    = $activity->post_time;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->activities->table_name_activities} SET
                            user_id = %d, component = %s, `type` = %s,
                            content = %s, item_id = %d, parent_id = %d,
                            post_time = %s, likes = %d
                        WHERE
                            id = %d
                        ",
                $this->user_id, $this->component, $this->type,
                $this->content, $this->item_id, $this->parent_id,
                $this->post_time, $this->likes,
                $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->activities->table_name_activities} (
                        user_id, component, `type`,
                        content, item_id, parent_id,
                        post_time, likes
                    ) VALUES(%d, %s, %s,
                    %s, %d, %d,
                    %s, %d)",
                $this->user_id, $this->component, $this->type,
                $this->content, $this->item_id, $this->parent_id,
                $this->post_time, $this->likes
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'search_terms'  => false,
            'user_id'       => false,
            'type'          => false,
            'component'     => false,
            'item_id'       => false,
            'parent_id'     => false,
            'order'         => 'DESC',
            'orderby'       => 'post_time',
            'page'          => 1,
            'per_page'      => 20);


        $r = wp_parse_args($args, $defaults);

        $sql = array(
            'select' => "SELECT DISTINCT a.id",
            'from' => "{$gp->activities->table_name_activities} a",
            'where' => '',
            'orderby' => '',
            'pagination' => '',
        );
        $where_conditions = array();

        $search = '';
        if (isset($r['search_terms'])) {
            $search = trim($r['search_terms']);
        }

        if ( !empty( $r['type'] ) )
            $where_conditions[] = $wpdb->prepare( " a.type = %s", $r['type'] );

        if ( !empty( $r['component'] ) )
            $where_conditions[] = $wpdb->prepare( " a.component = %s", $r['component'] );

        if ( !empty( $r['item_id'] ) )
            $where_conditions[] = $wpdb->prepare( " a.item_id = %d", $r['item_id'] );

        if ( !empty( $r['parent_id'] ) )
            $where_conditions[] = $wpdb->prepare( " a.component = %d", $r['parent_id'] );

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
        $paged_items_sql = "{$sql['select']} FROM {$sql['from']} {$where} {$sql['orderby']} {$sql['pagination']}";
        $paged_item_ids = $wpdb->get_col( $paged_items_sql );
        $paged_items = array();
        foreach ( $paged_item_ids as $paged_item_id ) {
            $paged_items[] = new GP_Activities_Activity( $paged_item_id );
        }

        $total_items_sql = "SELECT COUNT(DISTINCT a.id) FROM {$sql['from']} $where";
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {
            case 'likes' :
                $order_by_term = 'a.likes';
                break;
            case 'post_time' :
            default :
                $order_by_term = 'a.post_time';
                break;
        }

        return $order_by_term;
    }
}