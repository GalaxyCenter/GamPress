<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/21
 * Time: 14:44
 */

defined( 'ABSPATH' ) || exit;

/**
 * GamPress-EXT Group Module
 *
 * @since 1.6.0
 */
class GP_Games_Group_Members {

    var $id;

    /** @var  活动标识 */
    var $activity_id;

    /** @var  组标识 */
    var $group_id;

    /** @var  用户id */
    var $user_id;

    /** @var  邀请者id */
    var $inviter_id;

    /** @var  是否该组管理员 */
    var $is_admin;

    /** @var  加入时间 */
    var $date_modified;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $gm = $wpdb->get_row( $wpdb->prepare( "SELECT gm.* FROM {$gp->books->table_name_groups_members} gm WHERE gm.id = %d LIMIT 1", $this->id ) );

        if ( empty( $gm ) || is_wp_error( $gm ) ) {
            $this->id = 0;
            return false;
        }

        $this->id                   = (int) $gm->id;
        $this->activity_id          = (int) $gm->activity_id;
        $this->group_id             = (int) $gm->group_id;
        $this->user_id              = (int) $gm->user_id;
        $this->inviter_id           = (int) $gm->inviter_id;
        $this->is_admin             = (int) $gm->is_admin;
        $this->date_modified        = $gm->date_modified;
    }

    public function save() {
        $retval = false;

        $data = array(
            'group_id'          => $this->group_id,
            'user_id'           => $this->user_id,
            'activity_id'       => $this->activity_id,

            'inviter_id'        => $this->inviter_id,
            'is_admin'          => $this->is_admin,
            'date_modified'     => $this->date_modified
        );
        $data_format = array( '%d', '%d', '%d',
            '%d', '%d', '%s' );

        global $wpdb;
        $gp = gampress();
        // Update.
        if ( ! empty( $this->id ) ) {
            $result = $wpdb->update( $gp->games->table_name_groups_members, $data, array( 'id' => $this->id ), $data_format, array( '%d' ) );

            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $retval   = $this->id;
            }
            // Insert.
        } else {
            $result = $wpdb->insert( $gp->games->table_name_groups_members, $data, $data_format );

            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $this->id = $wpdb->insert_id;
                $retval   = $wpdb->insert_id;
            }
        }

        // Return the result.
        return $retval;
    }

    public static function get( $args ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'activity_id'        => null,
            'group_id'           => null,
            'user_id'            => null,
            'inviter_id'         => null,
            'is_admin'           => null,
            'order'              => 'DESC',
            'orderby'            => 'id',
            'per_page'           => 20,
            'page'               => 1, );


        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT gm.*",
            'from'       => "{$gp->games->table_name_groups_members} gm",
            'where'      => '',
            'orderby'    => '',
            'pagination' => '',
        );

        $where_conditions = array();
        if ( isset( $r['activity_id'] ) ) {
            $where_conditions[] = $wpdb->prepare( " gm.activity_id = %d", $r['activity_id'] );
        }

        if ( isset( $r['group_id'] ) ) {
            $where_conditions[] = $wpdb->prepare( " gm.group_id = %d", $r['group_id'] );
        }

        if ( isset( $r['user_id'] ) ) {
            $where_conditions[] = $wpdb->prepare( " gm.user_id = %d", $r['user_id'] );
        }

        if ( isset( $r['inviter_id'] ) ) {
            $where_conditions[] = $wpdb->prepare( " gm.inviter_id = %d", $r['inviter_id'] );
        }

        if ( isset( $r['is_admin'] ) ) {
            $where_conditions[] = $wpdb->prepare( " gm.is_admin = %d", $r['is_admin'] );
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

        $total_items_sql = "SELECT COUNT(DISTINCT gm.id) FROM {$sql['from']} $where";
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {

            default :
                $order_by_term = 'gm.id';
        }

        return $order_by_term;
    }

    public static function get_total_member_count( $group_id ) {
        global $wpdb;

        $gp = gampress();

        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$gp->games->table_name_groups_members} WHERE group_id = %d", $group_id ) );
    }

    public static function is_user_member( $activity_id, $group_id, $user_id ) {
        global $wpdb;

        $gp = gampress();

        return (bool) $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$gp->games->table_name_groups_members} WHERE activity_id = %d AND group_id = %d AND user_id = %d", $activity_id, $group_id, $user_id ) );
    }

    public static function get_membership_ids_for_user( $user_id, $activity_id ) {
        global $wpdb;

        $gp = gampress();

        $group_ids = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->games->table_name_groups_members} WHERE user_id = %d AND activity_id = %d ORDER BY id ASC", $user_id, $activity_id ) );

        return $group_ids;
    }

    public static function get_count_for_user( $user_id, $activity_id ) {
        global $wpdb;

        $gp = gampress();

        $group_ids = $wpdb->get_var( $wpdb->prepare( "SELECT count(0) FROM {$gp->games->table_name_groups_members} WHERE user_id = %d AND activity_id = %d ORDER BY id ASC", $user_id, $activity_id ) );

        return (int) $group_ids;
    }
}