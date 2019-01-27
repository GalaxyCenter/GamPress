<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/7
 * Time: 12:56
 */

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class GP_Orders_Tickets 券
 */
class GP_Orders_Tickets {

    var $id;

    /**
     * @var 用户id
     */
    var $user_id;

    /**
     * @var 券名
     */
    var $name;

    /**
     * @var 金额
     */
    var $fee;

    /**
     * @var 类型
     */
    var $type;

    /**
     * @var 失效时间
     */
    var $expired;

    /**
     * @var 领取时间
     */
    var $create_time;

    public function __construct( $id = '' ) {
        if ( !empty( $id ) ) {
            $this->$id = $id;
            $this->populate( $id );
        }
    }

    public function populate( $id ) {
        global $wpdb;

        $gp = gampress();
        $ticket = $wpdb->get_row( $wpdb->prepare(
            "SELECT t.* FROM {$gp->orders->table_name_tickets} t 
                        WHERE t.id = %d", $id ) );

        // 如果在数据库中未找到相关topic则重置id后返回
        if ( empty( $ticket ) || is_wp_error( $ticket ) ) {
            $this->id = 0;
            return;
        }

        $this->id               = $ticket->id;
        $this->name             = $ticket->name;
        $this->user_id          = $ticket->user_id;
        $this->fee              = $ticket->fee;
        $this->type             = $ticket->type;
        $this->expired          = $ticket->expired;
        $this->create_time      = $ticket->create_time;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        $sql = $wpdb->prepare(
            "INSERT INTO {$gp->orders->table_name_tickets} (
                        user_id, fee, `type`,
                        `expired`, create_time, `name`,
                        `id`
                    ) VALUES(%d, %s, %s, %s, %s, %s, %s)",
            $this->user_id, $this->fee, $this->type,
            $this->expired, $this->create_time, $this->name,
            $this->id
        );

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return $this->id;
    }

    public static function get( $user_id ) {
        global $wpdb;

        $gp = gampress();

        $sql = "SELECT t.* FROM {$gp->orders->table_name_tickets} t WHERE user_id = %d AND expired > now()";
        return $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );
    }

    public static function get_totel_fee( $user_id ) {
        global $wpdb;

        $gp = gampress();

        $sql = "SELECT SUM(fee) FROM {$gp->orders->table_name_tickets} t WHERE user_id = %d AND expired > now()";
        return $wpdb->get_var( $wpdb->prepare( $sql, $user_id ) );
    }

    public static function verify( $id, $user_id ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->orders->table_name_tickets} WHERE user_id = %d AND id = %s", $user_id, $id ) );
        return is_numeric( $query ) ? (int) $query : $query;
    }
}