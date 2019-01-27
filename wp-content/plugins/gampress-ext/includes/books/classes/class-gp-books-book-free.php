<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/3
 * Time: 1:54
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Video object.
 *
 * @since 1.6.0
 */
class GP_Books_Book_Free {

    var $id;

    var $name;

    var $book_ids;

    /** @var  开始时间 */
    var $start_time;

    /** @var  结束时间 */
    var $end_time;

    public function __construct( $id = null, $args = array() ) {
        $this->id = (int) $id;
        if ( !empty( $id ) ) {
            $this->populate();
        }
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->books->table_name_book_free} SET
                            book_ids = %s, start_time = %s, end_time = %s, `name` = %s
                        WHERE
                            id = %d
                        ",
                $this->book_ids, $this->start_time, $this->end_time, $this->name,
                $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->books->table_name_book_free} (
                        book_ids, start_time, end_time, `name`
                    ) VALUES(%s, %s, %s, %s)",
                $this->book_ids, $this->start_time, $this->end_time, $this->name
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    public function populate() {
        global $wpdb;

        $gp = gampress();
        $bf = $wpdb->get_row( $wpdb->prepare("SELECT bf.* FROM {$gp->books->table_name_book_free} bf WHERE bf.id = %d", $this->id ) );

        if ( empty( $bf ) || is_wp_error( $bf ) ) {
            $this->id = 0;
            return false;
        }

        $this->id           = (int)$bf->id;
        $this->name         = $bf->name;
        $this->book_ids     = $bf->book_ids;
        $this->start_time   = $bf->start_time;
        $this->end_time     = $bf->end_time;
    }

    public static function get_by_date( $date ) {
        global $wpdb;

        $gp = gampress();
        $bf = $wpdb->get_row( $wpdb->prepare("SELECT bf.* FROM {$gp->books->table_name_book_free} bf WHERE start_time <= %s and %s <= end_time", $date, $date ) );

        if ( empty( $bf ) || is_wp_error( $bf ) ) {
            return false;
        }
        return $bf;
    }

    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'order'          => 'DESC',
            'orderby'        => 'id',
            'intime'         => null,
            'page'           => 1,
            'per_page'       => 20 );

        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT bf.*",
            'from'       => "{$gp->books->table_name_book_free} bf",
            'where'      => '',
            'orderby'    => '',
            'pagination' => '',
        );
        $where_conditions = array();

        if ( $r['intime'] == 'true' )
            $where_conditions[] = 'bf.start_time <= now() AND bf.end_time >= now()';

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
        //$paged_books_sql = join( ' ', (array) $sql );
        $paged_items_sql = "{$sql['select']} FROM {$sql['from']} {$where} {$sql['orderby']} {$sql['pagination']}";
        $paged_items = $wpdb->get_results( $paged_items_sql );
        for ( $i = 0; $i < count( $paged_items ); $i++ ) {
            $item = $paged_items[$i];
            $book_ids = json_decode( $item->book_ids );
            $books = array();
            if ( !empty( $book_ids ) ) {
                foreach ( $book_ids as $book_id ) {
                    $books[] = gp_books_get_book( $book_id );
                }
            }
            $paged_items[$i]->books = $books;
        }
        $total_sql = "SELECT COUNT(DISTINCT bf.id) FROM {$sql['from']} $where";
        $total     = $wpdb->get_var( $total_sql );

        unset( $sql, $paged_items_sql, $total_sql);

        return array( 'items' => $paged_items, 'total' => $total );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {
            default :
                $order_by_term = 'bf.id';
                break;
        }

        return $order_by_term;
    }

    public static function exists( $book_id ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT bf.id FROM {$gp->books->table_name_book_free} bf WHERE bf.book_ids LIKE %s", '%' . $book_id . '%' ) );

        return $query;
    }

    public static function delete( $book_id ) {
        global $wpdb;
        $gp    = gampress();

        $wpdb->delete( $gp->books->table_name_book_free, array( 'book_id' => $book_id ), array( '%d') );
    }
}