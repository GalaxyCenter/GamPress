<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/7
 * Time: 12:56
 */

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class GP_Orders_Coin_Bill 金币 账单
 */
class GP_Orders_Coin_Bill {

    /**
     * 充值
     */
    const RECHARGE = 1;

    /**
     * 支付
     */
    const PAY = 2;

    /**
     * 券类型
     */
    const TICKET = 4;


    public $id;
    /**
     * @var所属用户id
     */
    public $user_id;

    /**
     * @var金额
     */
    public $fee;

    /**
     * @var账单类型
     */
    public $type;

    /**
     * @vard订单流水号 transactional number
     */
    public $tran_no;
    /**
     * @var 关联订单id
     */
    public $order_id;

    /**
     * @var 关联item id
     */
    public $item_id;

    /**
     * @var 说明
     */
    public $description;

    /**
     * @var账单生成时间
     */
    public $create_time;

    public function __construct( $id = '' ) {
        if ( !empty( $id ) ) {
            $this->$id = $id;
            $this->populate( $id );
        }
    }

    public function populate( $id ) {
        global $wpdb;

        $gp = gampress();
        $bill = $wpdb->get_row( $wpdb->prepare(
            "SELECT b.* FROM {$gp->orders->table_bill_name} b 
                        WHERE b.id = %d", $id ) );

        // 如果在数据库中未找到相关topic则重置id后返回
        if ( empty( $bill ) || is_wp_error( $bill ) ) {
            $this->id = 0;
            return;
        }

        $this->id               = $bill->id;
        $this->user_id          = $bill->user_id;
        $this->fee              = $bill->fee;
        $this->order_id         = $bill->order_id;
        $this->item_id          = $bill->item_id;
        $this->type             = $bill->type;
        $this->create_time      = $bill->create_time;
        $this->description      = $bill->description;
        $this->tran_no          = $bill->tran_no;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->orders->table_name_coin_bills} SET
                            user_id = %d, fee = %s, order_id = %s,
                            `type` = %d, create_time = %s, description = %s,
                            tran_no = %s, item_id = %s
                        WHERE
                            id = %d
                        ",
                $this->user_id, $this->fee, $this->order_id,
                $this->type, $this->create_time, $this->description,
                $this->tran_no, $this->item_id, $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->orders->table_name_coin_bills} (
                        user_id, fee, order_id,
                        `type`, create_time, description,
                        tran_no, item_id
                    ) VALUES(%d, %s, %s, %d, %s, %s, %s, %s)",
                $this->user_id, $this->fee, $this->order_id,
                $this->type, $this->create_time, $this->description, $this->tran_no, $this->item_id
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    // 用于判断支付回调
    public static function exists( $user_id, $order_id ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->orders->table_name_coin_bills} WHERE user_id = %d AND order_id = %s LIMIT 1", $user_id, $order_id ) );
        return is_numeric( $query ) ? (int) $query : $query;
    }

    // 用于判断支付熊币，赠币
    public static function exists2( $user_id, $order_id, $item_id ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->orders->table_name_coin_bills} WHERE user_id = %d AND order_id = %s AND item_id = %s LIMIT 1", $user_id, $order_id, $item_id ) );
        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function get( $args = '' ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'user_id'               => 0,
            'type'                  => false,
            'order'                 => 'DESC',
            'orderby'               => 'create_time',
            'search_term'           => false,
            'page'                  => 1,
            'per_page'              => 20
        );
        $r = wp_parse_args( $args, $defaults );

        $sql        = array();
        $tables     = array();
        $clause     = array();

        $sql['select'] = "SELECT DISTINCT b.*";
        $tables[]      = " FROM {$gp->orders->table_name_coin_bills} b";

        if ( !empty( $r['user_id'] ) )
            $clause[] = $wpdb->prepare( " b.user_id = %d", $r['user_id'] );
        
        if ( !empty( $r['type'] ) )
            $clause[] = $wpdb->prepare( " b.type & %d = b.type", $r['type'] );

        if ( !empty( $r['search_term'] ) )
            $clause[] = $wpdb->prepare( " b.description = %s", $r['search_term'] );

        $sql['from']  = join( ' ', (array) $tables );
        $sql['where'] = join( ' AND ', (array) $clause );
        if ( !empty( $sql['where'] ) )
            $sql['where'] = 'WHERE ' . $sql['where'];

        /** Order/orderby ********************************************/

        $order   = $r['order'];
        $orderby = $r['orderby'];

        // Sanitize 'order'
        $order = gp_esc_sql_order( $order );

        // Convert 'orderby' into the proper ORDER BY term
        $orderby = self::convert_orderby_to_order_by_term( $orderby );
        $sql[] = "ORDER BY {$orderby} {$order}";

        if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page'] ), intval( $r['per_page'] ) );

        // Get paginated results
        $paged_items_sql = join( ' ', (array) $sql );
        $paged_items     = $wpdb->get_results( $paged_items_sql );

        $sql['select'] = 'SELECT COUNT(DISTINCT b.id) ';
        $sql['pagination'] = '';
        $total_items_sql = join( ' ', (array) $sql );
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }

    public static function get_total_coin_for_user( $user_id ) {
        global $wpdb;

        $gp    = gampress();
        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT sum(fee) FROM {$gp->orders->table_name_coin_bills} WHERE `type` <> %d AND user_id = %d", GP_Orders_Coin_Bill::TICKET, $user_id ) );
    }

    public static function get_ticket_total_coin_for_user( $user_id, $tran_no = false ) {
        global $wpdb;

        $gp    = gampress();
        if ( empty( $tran_no ) )
            return (int) $wpdb->get_var( $wpdb->prepare( "SELECT sum(fee) FROM {$gp->orders->table_name_coin_bills} WHERE `type` = %d", GP_Orders_Coin_Bill::TICKET ) );
        else
            return (int) $wpdb->get_var( $wpdb->prepare( "SELECT sum(fee) FROM {$gp->orders->table_name_coin_bills} WHERE `type` = %d AND item_id = %s AND user_id = %d", GP_Orders_Coin_Bill::TICKET, $tran_no, $user_id )  );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {
            case 'create_time' :
            default :
                $order_by_term = 'b.create_time';
                break;
        }

        return $order_by_term;
    }
}