<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/24
 * Time: 9:18
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Video object.
 *
 * @since 1.6.0
 */
class GP_Books_Bookmark {

    /**
     * 书签
     */
    const BOOKMARKS = 'bookmarks';

    /**
     * 阅读历史
     */
    const HISTORY = 'history';

    public $id;

    public $user_id;

    public $type;

    public $book_id;

    public $chapter_id;

    public $post_time;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp = gampress();
        $bm = $wpdb->get_row($wpdb->prepare("SELECT b.* FROM {$gp->books->table_name_bookmarks} b WHERE b.id = %d", $this->id));

        if (empty($bm) || is_wp_error($bm)) {
            $this->id = 0;
            return false;
        }

        $this->id = (int)$bm->id;
        $this->user_id = $bm->user_id;
        $this->type = $bm->type;
        $this->book_id = $bm->book_id;
        $this->chapter_id = $bm->chapter_id;
        $this->post_time = $bm->post_time;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->books->table_name_bookmarks} SET
                            user_id = %d, `type` = %s, `book_id` = %d,
                            chapter_id = %d, post_time = %d
                        WHERE
                            id = %d
                        ",
                $this->user_id, $this->type, $this->book_id,
                $this->chapter_id, $this->post_time, $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->books->table_name_bookmarks} (
                        user_id, `type`, `book_id`,
                        chapter_id, post_time
                    ) VALUES(%d, %s, %d, %d, %d)",
                $this->user_id, $this->type, $this->book_id,
                $this->chapter_id, $this->post_time
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    public static function exists( $user_id, $type, $book_id ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_row( $wpdb->prepare( "SELECT id, chapter_id FROM {$gp->books->table_name_bookmarks} WHERE user_id = %d AND `type` = %s AND `book_id` = %d LIMIT 1", $user_id, $type, $book_id ) );

        return $query;
    }

    public static function get_top( $type, $mondate, $count ) {
        global $wpdb;

        $gp = gampress();

        return $wpdb->get_results( $wpdb->prepare( "SELECT book_id, COUNT(0) as counts FROM {$gp->books->table_name_bookmarks} WHERE `type` = %s AND date_format(FROM_UNIXTIME(post_time), '%%Y%%m') = %s GROUP BY book_id ORDER BY counts DESC LIMIT %d", $type, $mondate, $count ) );
    }

    public static function bookmark_count( $book_id, $type ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(0) FROM {$gp->books->table_name_bookmarks} WHERE book_id = %d AND `type` = %s", $book_id, $type  ) );

        return empty( $query ) ? 0 : (int) $query;
    }

    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'user_id'        => false,
            'type'           => false,
            'order'          => 'DESC',
            'orderby'        => 'post_time',
            'page'           => 1,
            'per_page'       => 20);

        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT bm.*",
            'from'       => "{$gp->books->table_name_bookmarks} bm",
            'where'      => '',
            'orderby'    => '',
            'pagination' => '',
        );
        $where_conditions = array();
        if ( !empty( $r['user_id'] ) )
            $where_conditions[] = $wpdb->prepare( " bm.user_id = %d", $r['user_id'] );

        if ( !empty( $r['type'] ) )
            $where_conditions[] = $wpdb->prepare( " bm.type = %s", $r['type'] );

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
        foreach ( $paged_items as $item ) {
            $item->book = gp_books_get_book( $item->book_id );
            $item->chapter = gp_books_get_chapter( $item->chapter_id );
        }

        $total_sql = "SELECT COUNT(DISTINCT bm.id) FROM {$sql['from']} $where";
        $total     = $wpdb->get_var( $total_sql );

        unset( $sql, $paged_items_sql, $total_sql);

        return array( 'items' => $paged_items, 'total' => $total );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {
            case 'post_time' :
            default :
                $order_by_term = 'bm.post_time';
                break;
        }

        return $order_by_term;
    }
}