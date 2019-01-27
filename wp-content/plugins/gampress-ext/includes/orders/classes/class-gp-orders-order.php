<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 23:32
 */

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Trip Orders object.
 */
class GP_Orders_Order {

    const BOOK = 'book';


    const TICKET = 'ticket';

    const COIN = 'coin';

    const RECHARGE = 'recharge';

    var $id;

    /**
     * 订单号 规则 yy + mm + dd + $id + $user_id + $topic_id + rand()
     *
     * @var mixed
     *
     */
    var $order_id;


    /**
     * 产品id
     *
     * @var mixed
     *
     */
    var $product_id;

    /**
     * 商品id
     *
     *
     */
    var $item_id;

    /**
     * 订单所有者
     *
     * @var mixed
     *
     */
    var $user_id;

    /**
     * 订单产生时间
     *
     * @var mixed
     *
     */
    var $create_time;


    /**
     * 支付时间
     *
     * @var mixed
     *
     */
    var $pay_time;

    /**
     * 订单状态 1未提交  2确认中 3已确认 4已付款 5已成交 6已取消
     *
     * @var mixed
     *
     */
    var $status;


    /**
     * 商品价格
     *
     * @var mixed
     *
     */
    var $price;

    /**
     * 购买数量
     *
     * @var mixed
     *
     */
    var $quantity;

    var $total_fee;

    var $type;

    var $age;

    var $ip;

    var $come_from;

    public function __construct( $order_id = '' ) {
        if ( !empty( $order_id ) ) {
            $this->order_id = $order_id;
            $this->populate( $order_id );
        }
    }

    public function populate( $order_id ) {
        global $wpdb;

        $gp = gampress();
        $order = $wpdb->get_row( $wpdb->prepare(
            "SELECT o.* FROM {$gp->orders->table_name} o 
                        WHERE o.order_id = %s", $order_id ) );

        // 如果在数据库中未找到相关topic则重置id后返回
        if ( empty( $order ) || is_wp_error( $order ) ) {
            $this->id = 0;
            return;
        }

        $this->id               = $order->id;
        $this->order_id         = $order->order_id;
        $this->product_id       = $order->product_id;
        $this->item_id          = $order->item_id;
        $this->user_id          = $order->user_id;
        $this->create_time      = $order->create_time;
        $this->pay_time         = $order->pay_time;
        $this->price            = $order->price;
        $this->quantity         = $order->quantity;
        $this->total_fee        = $order->total_fee;
        $this->status           = $order->status;
        $this->type             = $order->type;
        $this->time             = $order->time;
        $this->ip               = $order->ip;
        $this->come_from        = $order->come_from;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->orders->table_name} SET
                            product_id      = %d, item_id         = %d, user_id         = %d,
                            create_time     = %s, pay_time        = %s, price           = %s,
                            quantity        = %d, total_fee       = %s, status          = %d,
                            `type`          = %s, `time`          = %d, ip              = %s,
                            come_from       = %s
                        WHERE
                            order_id = %s
                        ",
                $this->product_id, $this->item_id, $this->user_id,
                $this->create_time, $this->pay_time, $this->price,
                $this->quantity, $this->total_fee, $this->status,
                $this->type,$this->time, $this->ip,
                $this->come_from,
                $this->order_id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->orders->table_name} (
                        order_id, product_id, item_id,
                        user_id, create_time, pay_time,
                        price, quantity, total_fee,
                        status, `type`, `time`,
                        ip, come_from
                    ) VALUES(%s, %d, %d, 
                    %d, %s, %s, 
                    %s, %d, %s, 
                    %d, %s, %d,
                    %s, %s)",
                $this->order_id, $this->product_id,     $this->item_id,
                $this->user_id,  $this->create_time,    $this->pay_time,
                $this->price,    $this->quantity,       $this->total_fee,
                $this->status,   $this->type,           $this->time,
                $this->ip,       $this->come_from
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    /** static ***************************************************/
    public static function get_user_order_count( $user_id, $status, $type ) {
        global $wpdb;

        $gp = gampress();

        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(0) FROM {$gp->orders->table_name} WHERE user_id = %d AND status = %d AND `type` = %s", $user_id, $status, $type ) );
    }

    public static function get_top_item( $mondate, $count ) {
        global $wpdb;

        $gp = gampress();

        return $wpdb->get_results( $wpdb->prepare( "SELECT item_id, SUM(total_fee) as fee FROM {$gp->orders->table_name} WHERE item_id > 0 AND (`type` = 'ticket' OR `type` = 'coin' ) AND date_format(`create_time`, '%%Y%%m') = %s GROUP BY item_id ORDER BY fee DESC LIMIT %d", $mondate, $count ) );
    }

    /**
     * 给book_rank 页面调用的屏蔽关闭的
     * @param $mondate
     * @param $count
     * @return array|null|object
     */
    public static function get_top_item2( $mondate, $count ) {
        global $wpdb;

        $gp = gampress();

        return $wpdb->get_results( $wpdb->prepare( "SELECT item_id, SUM(total_fee) as fee FROM ds_gp_orders o join `ds_gp_fictions_books` b on o.`item_id` = b.id WHERE item_id > 0 AND (`type` = 'ticket' OR `type` = 'coin' ) AND date_format(`create_time`, '%%Y%%m') = %s AND b.status & 4 <> 4 GROUP BY item_id ORDER BY fee DESC LIMIT %d", $mondate, $count ) );
    }

    public static function get_order_status( $order_id ) {
        global $wpdb;

        $gp = gampress();

        if ( empty( $order_id ) )
            return false;

        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$gp->orders->table_name} WHERE order_id = %s", $order_id ) );
    }

    public static function order_exists( $order_id ) {
        global $wpdb;

        $gp = gampress();

        if ( empty( $order_id ) )
            return false;

        return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->orders->table_name} WHERE order_id = %s", $order_id ) );
    }

    public static function get_order_count( $user_id, $product_id ) {
        global $wpdb;

        $gp = gampress();

        $sql        = array();
        $clause     = array();

        $sql['select'] = "SELECT COUNT(DISTINCT o.id) ";
        $sql['from']   = " FROM {$gp->orders->table_name} o";

        if ( !empty( $user_id ) )
            $clause[] = $wpdb->prepare( " o.usre_id = %d", $user_id );

        if ( !empty( $product_id ) )
            $clause[] = $wpdb->prepare( " o.product_id = %d", $product_id );

        $sql['where'] = join( ' AND ', (array) $clause );
        if ( !empty( $sql['where'] ) )
            $sql['where'] = 'WHERE ' . $sql['where'];

        $sql = join( ' ', (array) $sql );
        return $wpdb->get_var( $sql );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {
            case 'pay_time':
                $order_by_term = 'o.create_time';
                break;

            case 'user_id':
                $order_by_term = 'o.user_id';
                break;

            case 'price':
                $order_by_term = 'o.price';
                break;

            case 'time':
                $order_by_term = 'o.time';
                break;

            case 'create_time' :
            default:
                $order_by_term = 'o.create_time';
                break;
        }

        return $order_by_term;
    }

    public static function update_status( $order_id, $status, $status_old ) {
        global $wpdb;

        $gp = gampress();

        $sql = $wpdb->prepare(
            "UPDATE {$gp->orders->table_name} SET
                            status          = %d
                        WHERE
                            order_id = %s AND status = %d
                        ",
            $status,
            $order_id,
            $status_old
        );

        if ( false === $wpdb->query( $sql ) )
            return false;

        return true;
    }

    public static function update_pay_time( $order_id, $time ) {
        global $wpdb;

        $gp = gampress();

        $sql = $wpdb->prepare(
            "UPDATE {$gp->orders->table_name} SET
                    pay_time          = %s
                    WHERE
                    order_id = %s",
            $time,
            $order_id
        );

        if ( false === $wpdb->query( $sql ) )
            return false;

        return true;
    }

    public static function update_time($order_id, $time ) {
        global $wpdb;

        $gp = gampress();

        $sql = $wpdb->prepare(
            "UPDATE {$gp->orders->table_name} SET
                    `time`          = %d
                    WHERE
                    order_id = %s",
            $time,
            $order_id
        );

        if ( false === $wpdb->query( $sql ) )
            return false;

        return true;
    }

    public static function gp_orders_update_type( $order_id, $type ) {
        global $wpdb;

        $gp = gampress();

        $sql = $wpdb->prepare(
            "UPDATE {$gp->orders->table_name} SET
                    `type`          = %s
                    WHERE
                    order_id = %s",
            $type,
            $order_id
        );

        if ( false === $wpdb->query( $sql ) )
            return false;

        return true;
    }

    public static function get_order_id( $user_id, $item_id, $product_id, $status = false ) {
        global $wpdb;
        $gp    = gampress();

        if ( empty( $status ) ) {
            $query = $wpdb->get_var($wpdb->prepare("SELECT order_id FROM {$gp->orders->table_name} 
                                                      WHERE user_id = %d AND item_id = %d AND product_id = %d
                                                      LIMIT 1", $user_id, $item_id, $product_id) );
        } else {
            $query = $wpdb->get_var($wpdb->prepare("SELECT order_id FROM {$gp->orders->table_name} 
                                                      WHERE user_id = %d AND item_id = %d AND product_id = %d AND status = %d
                                                      LIMIT 1", $user_id, $item_id, $product_id, $status) );
        }
        return is_numeric( $query ) ? $query : false;
    }

    public static function get( $args = '' ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'user_id'               => 0,
            'product_id'            => 0,
            'item_id'               => 0,
            'type'                  => false,
            'status'                => false,
            'order'                 => 'DESC',
            'orderby'               => 'create_time',
            'search_term'           => false,
            'meat_query'            => false,
            'page'                  => 1,
            'per_page'              => 20
        );
        $r = wp_parse_args( $args, $defaults );

        $sql        = array();
        $tables     = array();
        $clause     = array();

        $sql['select'] = "SELECT DISTINCT o.*";
        $tables[]      = " FROM {$gp->orders->table_name} o";

        if ( !empty( $r['user_id'] ) )
            $clause[] = $wpdb->prepare( " o.user_id = %d", $r['user_id'] );

        if ( !empty( $r['item_id'] ) )
            $clause[] = $wpdb->prepare( " o.item_id = %d", $r['item_id'] );

        if ( !empty( $r['product_id'] ) )
            $clause[] = $wpdb->prepare( " o.product_id = %d", $r['product_id'] );

        if ( !empty( $r['status'] ) )
            $clause[] = $wpdb->prepare( " o.status = %d", $r['status'] );

        if ( !empty( $r['type'] ) )
            $clause[] = $wpdb->prepare( " o.type = %s", $r['type'] );

        if ( !empty( $r['search_term'] ) ) {
            $r['search_term'] = esc_sql( $r['search_term'] );
            $earch_term = esc_sql( wpdb::esc_like( $r['search_term'] ) );

            $tables[] = " JOIN {$gp->orders->table_name_ordermeta} m ON m.order_id = o.order_id";

            $clause[] = " (o.order_id LIKE '%%" . $earch_term . "%%'
                        OR m.meta_value LIKE '%%" . $earch_term . "%%')";
        }
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

        $sql['select'] = 'SELECT COUNT(DISTINCT o.id) ';
        $sql['pagination'] = '';
        $total_items_sql = join( ' ', (array) $sql );
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }
}