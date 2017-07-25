<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/12
 * Time: 20:31
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class GP_Sms_Message {

    public static $LOGIN_CODE = 0x0001;

    public $id;

    public $type;

    public $user_id;

    public $phone;

    public $content;

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
        $msg = $wpdb->get_row( $wpdb->prepare( "SELECT m.* FROM {$gp->sms->table_name} m WHERE m.id = %d", $this->id ) );

        if ( empty( $book ) || is_wp_error( $book ) ) {
            $this->id = 0;
            return false;
        }

        $this->id                   = (int) $msg->id;
        $this->type                 = (int) $msg->type;
        $this->user_id              = (int) $msg->user_id;
        $this->phone                = $msg->phone;
        $this->content              = $msg->content;
        $this->post_time            = $msg->post_time;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->sms->table_name} SET
                            `type` = %d, user_id = %d, phone = %s,
                            content = %s, post_time = %s
                        WHERE
                            id = %d
                        ",
                $this->type, $this->user_id, $this->phone,
                $this->content, $this->post_time,
                $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->sms->table_name} (
                        `type`, user_id, phone,
                        content, post_time
                    ) VALUES(%d, %d, %s,
                    %s, %s)",
                $this->type, $this->user_id, $this->phone,
                $this->content, $this->post_time
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    public static function get( $args ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'per_page'           => null,
            'page'               => null,
            'type'               => false,
            'user_id'            => false,
            'phone'              => false,
            'time_in'            => array() );

        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT DISTINCT m.id",
            'from'       => "{$gp->sms->table_name} m",
            'where'      => '',
            'orderby'    => '',
            'pagination' => '',
        );
        $where_conditions = array();

        if ( !empty( $r['phone'] ) )
            $where_conditions[] = $wpdb->prepare( " m.phone = %s", $r['phone'] );

        if ( !empty( $r['user_id'] ) )
            $where_conditions[] = $wpdb->prepare( " m.user_id = %d", $r['user_id'] );

        if ( !empty( $r['type'] ) )
            $where_conditions[] = $wpdb->prepare( " m.type = %d", $r['type'] );

        if ( !empty( $r['time_in'] ) )
            $where_conditions[] = $wpdb->prepare( " m.post_time > %s AND m.post_time < %s", $r['time_in'][0], $r['time_in'][1] );

        $where = '';
        if ( ! empty( $where_conditions ) ) {
            $sql['where'] = implode( ' AND ', $where_conditions );
            $where = "WHERE {$sql['where']}";
        }

        if ( !empty( $sql['where'] ) )
            $sql['where'] = 'WHERE ' . $sql['where'];

        /* order/orderby ********************************************/
        $order   =  'DESC';
        $orderby = 'id';

        if ( 'rand()' === $orderby ) {
            $sql['orderby'] = "ORDER BY rand()";
        } else {
            $sql['orderby'] = "ORDER BY {$orderby} {$order}";
        }

        if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) && $r['per_page'] != -1 )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page'] ), intval( $r['per_page'] ) );

        // Get paginated results
        //$paged_books_sql = join( ' ', (array) $sql );
        $paged_msgs_sql = "{$sql['select']} FROM {$sql['from']} {$where} {$sql['orderby']} {$sql['pagination']}";
        $paged_msgs     = $wpdb->get_results( $paged_msgs_sql );

        $total_msgs_sql = "SELECT COUNT(DISTINCT m.id) FROM {$sql['from']} $where";
        $total_msgs     = $wpdb->get_var( $total_msgs_sql );

        unset( $sql );

        return array( 'items' => $paged_msgs, 'total' => $total_msgs );
    }

    public static function get_code( $phone, $type ) {
        if ( empty( $phone ) || empty( $type ) )
            return false;

        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$gp->sms->table_name} WHERE phone = %s and `type` = %d AND post_time > unix_timestamp() - 300 LIMIT 1", $phone, GP_Sms_Message::$LOGIN_CODE ) );

        return $query;
    }
}