<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 10:25
 */
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Group object.
 *
 * @since 1.6.0
 */
class GP_Games_Group {

    /** @var  组标识 */
    var $id;

    /** @var  所属活动 */
    var $activity_id;

    /** @var  组名 */
    var $name;

    /** @var  所有者 */
    var $owner_id;

    /** @var  创建时间 */
    var $date_created;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $group = $wpdb->get_row( $wpdb->prepare( "SELECT g.* FROM {$gp->games->table_name_groups} g WHERE g.id = %d LIMIT 1", $this->id ) );

        if ( empty( $group ) || is_wp_error( $group ) ) {
            $this->id = 0;
            return false;
        }

        $this->id                   = (int) $group->id;
        $this->activity_id          = (int) $group->activity_id;
        $this->name                 = $group->name;
        $this->owner_id             = (int) $group->owner_id;
        $this->date_created         = $group->date_created;
    }

    public function save() {
        $retval = false;

        $data = array(
            'name'          => $this->name,
            'activity_id'   => $this->activity_id,
            'owner_id'      => $this->owner_id,
            'date_created'   => $this->date_created
        );
        $data_format = array( '%s', '%d', '%d', '%s' );

        global $wpdb;
        $gp = gampress();
        // Update.
        if ( ! empty( $this->id ) ) {
            $result = $wpdb->update( $gp->games->table_name_groups, $data, array( 'id' => $this->id ), $data_format, array( '%d' ) );

            // Set the notification ID if successful.
            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $retval   = $this->id;
            }
            // Insert.
        } else {
            $result = $wpdb->insert( $gp->games->table_name_groups, $data, $data_format );

            // Set the notification ID if successful.
            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $this->id = $wpdb->insert_id;
                $retval   = $wpdb->insert_id;
            }
        }
        // Return the result.
        return $retval;
    }

    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'activity_id'        => false,
            'order'              => 'DESC',
            'orderby'            => 'date_created',
            'owner_id'           => null,
            'per_page'           => 20,
            'page'               => 1, );


        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT g.*",
            'from'       => "{$gp->games->table_name_groups} g",
            'where'      => '',
            'orderby'    => '',
            'pagination' => '',
        );

        $where_conditions = array();
        if ( isset( $r['owner_id'] ) ) {
            $where_conditions[] = $wpdb->prepare( " g.owner_id = %d", $r['owner_id'] );
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

        $total_items_sql = "SELECT COUNT(DISTINCT g.id) FROM {$sql['from']} $where";
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {

            default :
                $order_by_term = 'g.id';
        }

        return $order_by_term;
    }

    public static function get_count( $owner_id, $activity_id ) {
        global $wpdb;

        $gp = gampress();

        $count = $wpdb->get_var( $wpdb->prepare( "SELECT count(0) FROM {$gp->games->table_name_groups} WHERE owner_id = %d AND activity_id = %d ORDER BY id ASC", $owner_id, $activity_id ) );

        return $count;
    }

    public static function exists( $id ) {
        global $wpdb;

        $gp = gampress();

        return (bool) $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$gp->games->table_name_groups} WHERE id = %d", $id ) );
    }
}