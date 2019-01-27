<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/3
 * Time: 1:55
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Video object.
 *
 * @since 1.6.0
 */
class GP_Books_Chapter {
    public $id;

    /** @var  指向id，当父id为0和word为0时，为卷。 */
    public $parent_id;

    /** @var  作品id */
    public $book_id;

    /** @var  章节来源标识 */
    public $refer;

    /** @var  章节序号 */
    public $order;

    /** @var  章节序号+章节标题 */
    public $title;

    /** @var  章节内容 */
    public $body;

    /** @var  章节字数 */
    public $words;

    /** @var  是否计费章节 */
    public $is_charge;

    /** @var  章节上传更新时间 */
    public $post_time;

    /** @var  章节审核时间 */
    public $approved_time;

    /** @var  章节修改时间 */
    public $update_time;

    /** @var  章节状态 */
    public $status;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $chapter = $wpdb->get_row( $wpdb->prepare( "SELECT c.* FROM {$gp->books->table_name_book_chapter} c WHERE c.id = %d", $this->id ) );

        if ( empty( $chapter ) || is_wp_error( $chapter ) ) {
            $this->id = 0;
            return false;
        }

        $this->id                          = (int) $chapter->id;
        $this->parent_id                   = (int) $chapter->parent_id;
        $this->book_id                     = (int) $chapter->book_id;
        $this->refer                       = $chapter->refer;
        $this->order                       = (int) $chapter->order;
        $this->title                     = $chapter->title;
        $this->body                        = $chapter->body;
        $this->words                       = (int) $chapter->words;
        $this->is_charge                   = (int) $chapter->is_charge;
        $this->post_time                   = (int) $chapter->post_time;
        $this->approved_time               = (int) $chapter->approved_time;
        $this->update_time                 = (int) $chapter->update_time;
        $this->status                      = (int) $chapter->status;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->books->table_name_book_chapter} SET 
                            parent_id = %d, book_id = %d, refer = %s,
                            `order` = %d, title = %s, body = %s,
                            words = %d, is_charge = %d, post_time = %d,
                            approved_time = %d, update_time = %d, status = %d
                        WHERE
                            id = %d
                        ",
                $this->parent_id, $this->book_id, $this->refer,
                $this->order, $this->title, $this->body,
                $this->words, $this->is_charge, $this->post_time,
                $this->approved_time, $this->update_time, $this->status,
                $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->books->table_name_book_chapter} (
                        parent_id, book_id, refer,
                        `order`, title, body,
                        words, is_charge, post_time,
                        approved_time, update_time, status
                    ) VALUES(%d, %d, %s,
                    %d, %s, %s,
                    %d, %d, %d,
                    %d, %d, %d)",
                $this->parent_id, $this->book_id, $this->refer,
                $this->order, $this->title, $this->body,
                $this->words, $this->is_charge, $this->post_time,
                $this->approved_time, $this->update_time, $this->status
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    public static function chapter_exists( $book_id, $val, $status = false ) {
        global $wpdb;
        $gp    = gampress();

        if ( is_numeric( $val ) ) {
            $val = $val - GP_CHAPTER_BASE_INDEX;
            if ( $status === false )
                $query = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$gp->books->table_name_book_chapter} WHERE id = %d LIMIT 1", $val));
            else
                $query = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$gp->books->table_name_book_chapter} WHERE id = %d AND status = 0 LIMIT 1", $val));
        } else {
            if ( $status === false )
                $query = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$gp->books->table_name_book_chapter} WHERE book_id = %d AND title = %s LIMIT 1", $book_id, $val));
            else
                $query = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$gp->books->table_name_book_chapter} WHERE book_id = %d AND status = 0 AND title = %s LIMIT 1", $book_id, $val));
        }
        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function chapter_order_exists( $book_id, $order ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->books->table_name_book_chapter} WHERE book_id = %d AND status = 0 AND `order` = %d ORDER BY id ASC LIMIT 1", $book_id, $order ) );
        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function get_volumes( $book_id ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(0) FROM {$gp->books->table_name_book_chapter} WHERE book_id = %d AND words = 0 LIMIT 1", $book_id ) );
        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'book_id'        => false,
            'search_terms'   => false,
            'orderby'        => 'id',
            'order'          => 'ASC',
            'page'           => 1,
            'per_page'       => 20 );


        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT *, '' as body",
            'from'       => "{$gp->books->table_name_book_chapter} c",
            'where'      => '',
            'orderby'    => '',
            'pagination' => '',
        );

        $where_conditions = array();
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

        if ( ! empty( $r['book_id'] ) )
            $where_conditions[] = $wpdb->prepare( "c.book_id = %d", $r['book_id'] );

        if ( isset( $r['status'] ) && $r['status'] !== GP_CHAPTER_ALL )
            $where_conditions[] = $wpdb->prepare( " c.status = %d", $r['status'] );

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

        $total_items_sql = "SELECT COUNT(DISTINCT c.id) FROM {$sql['from']} $where";
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {
            case 'refer':
                $order_by_term = 'c.refer';
                break;

            case 'order' :
                $order_by_term = 'c.order';
                break;

            default :
                $order_by_term = 'c.id';
        }

        return $order_by_term;
    }

}