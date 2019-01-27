<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/2
 * Time: 15:42
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Video object.
 *
 * @since 1.6.0
 */
class GP_Books_Book {
    public $id;

    /** @var 作品标题 */
    public $title;

    /** @var 作者名 */
    public $author;

    /** @var 作者ID */
    public $author_id;

    /** @var 作品入库来源 */
    public $refer;

    /** @var  作品状态 */
    public $status;

    /** @var  卷章形式 */
    public $chapter_type;

    /** @var  作品封面 */
    public $cover;

    /** @var  作品标签 */
    public $tags;

    /** @var  作品简介 */
    public $description;

    /** @var  作品推荐语 */
    public $summary;

    /** @var  内容级别 */
    public $level;

    /** @var  作品计费点 */
    public $point;

    /** @var  收费章节开始 */
    public $charge_order;

    /** @var  作品字数 */
    public $words;

    /** @var  计费类型&计费规则 */
    public $charge_type;

    public $post_time;

    public $bookmarks;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $book = $wpdb->get_row( $wpdb->prepare( "SELECT b.* FROM {$gp->books->table_name_book} b WHERE b.id = %d LIMIT 1", $this->id ) );

        if ( empty( $book ) || is_wp_error( $book ) ) {
            $this->id = 0;
            return false;
        }

        $this->id                    = (int) $book->id;
        $this->title                 = $book->title;
        $this->author                = $book->author;
        $this->author_id             = (int) $book->author_id;
        $this->refer                 = $book->refer;
        $this->status                = (int) $book->status;
        $this->chapter_type          = (int) $book->chapter_type;
        $this->cover                 = $book->cover;
        $this->tags                  = $book->tags;
        $this->description           = $book->description;
        $this->summary               = $book->summary;
        $this->level                 = (int) $book->level;
        $this->point                 = $book->point;
        $this->charge_order          = $book->charge_order;
        $this->words                 = (int) $book->words;
        $this->charge_type           = (int) $book->charge_type;
        $this->post_time             = $book->post_time;
        $this->bookmarks             = $book->bookmarks;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->books->table_name_book} SET
                            title = %s, author = %s, author_id = %d,
                            refer = %s, status = %d, chapter_type = %d,
                            cover = %s, tags = %s, description = %s,
                            summary = %s, `level` = %d, point = %d,
                            charge_type = %d, words = %d, post_time = %s,
                            bookmarks = %d, charge_order = %d
                        WHERE
                            id = %d
                        ",
                $this->title, $this->author, $this->author_id,
                $this->refer, $this->status, $this->chapter_type,
                $this->cover, $this->tags, $this->description,
                $this->summary, $this->level, $this->point,
                $this->charge_type, $this->words, $this->post_time,
                $this->bookmarks, $this->charge_order,
                    $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->books->table_name_book} (
                        title, author, author_id,
                        refer, status, chapter_type,
                        cover, tags, description,
                        summary, `level`, point,
                        charge_type, words, post_time,
                        bookmarks, charge_order
                    ) VALUES(%s, %s, %d,
                    %s, %d, %d,
                    %s, %s, %s,
                    %s, %d, %d,
                    %d, %d, %s,
                    %d, %d)",
                $this->title, $this->author, $this->author_id,
                $this->refer, $this->status, $this->chapter_type,
                $this->cover, $this->tags, $this->description,
                $this->summary, $this->level, $this->point,
                $this->charge_type, $this->words, $this->post_time,
                $this->bookmarks, $this->charge_order
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    public static function book_exists( $val ) {
        if ( empty( $val ) )
            return false;

        global $wpdb;
        $gp    = gampress();

        if ( is_numeric( $val ) ) {
            $val = $val - GP_BOOK_BASE_INDEX;
            $query = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$gp->books->table_name_book} WHERE id = %d LIMIT 1", $val));
        } else {
            $query = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$gp->books->table_name_book} WHERE title = %s LIMIT 1", $val));
        }
        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'search_terms'   => false,
            'orderby'        => 'post_time',
            'status'         => false,
            'charge_type'    => false,
            'search_columns' => false,
            'term_ids'       => false,
            'author_id'      => false,
            'order'          => 'DESC',
            'page'           => 1,
            'per_page'       => 20,
            'words_query'    => false );

        $r = wp_parse_args( $args, $defaults );

        $sql = array(
            'select'     => "SELECT b.id",
            'from'       => "{$gp->books->table_name_book} b",
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
            $wildcarded = $leading_wild . gp_esc_like( $search ) . $trailing_wild;

            $search_columns = array( 'title', 'author' );
            if ( $r['search_columns'] ) {
                $search_columns = array_intersect( $r['search_columns'], $search_columns );
            }

            foreach ( $search_columns as $search_column ) {
                $searches[] = $wpdb->prepare( "$search_column LIKE %s", $wildcarded );
            }

            $where_conditions['search'] = '(' . implode(' OR ', $searches) . ')';
        }

        if ( !empty( $r['author_id'] ) )
            $where_conditions[] = $wpdb->prepare( " b.author_id = %d", $r['author_id'] );

        if ( !empty( $r['status'] ) )
            $where_conditions[] = $wpdb->prepare( " b.status & %d = b.status", $r['status'] );

        if ( !empty( $r['charge_type'] ) )
            $where_conditions[] = $wpdb->prepare( " b.charge_type & %d = b.charge_type", $r['charge_type'] );

        // 'words_query' => array( 'value' => 3000, 'compare' => '>=' )
        if ( !empty( $r['words_query'] ) ) {
            $where_conditions[] = $wpdb->prepare( " b.words " . $r['words_query']['compare'] . '%d', $r['words_query']['value'] );
        }

        if ( !empty( $r['term_ids'] ) ) {
            $sql['from'] .= " JOIN {$wpdb->term_relationships} r ON r.object_id = b.id";
            $in_terms = $r['term_ids'];
            $where_conditions[] = " r.term_taxonomy_id IN ($in_terms)";
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
        //$paged_items_sql = join( ' ', (array) $sql );
        $paged_items_sql = "{$sql['select']} FROM {$sql['from']} {$where} {$sql['orderby']} {$sql['pagination']}";
        $paged_item_ids = $wpdb->get_col( $paged_items_sql );
        $paged_items = array();
        foreach ( $paged_item_ids as $paged_item_id ) {
            $paged_items[] = new GP_Books_Book( $paged_item_id );
        }

        $total_items_sql = "SELECT COUNT(DISTINCT b.id) FROM {$sql['from']} $where";
        $total_items     = $wpdb->get_var( $total_items_sql );

        unset( $sql );

        return array( 'items' => $paged_items, 'total' => $total_items );
    }

    public static function reset_status( $id, $status ) {
        global $wpdb;

        $gp = gampress();
        $wpdb->query( $wpdb->prepare( "UPDATE {$gp->books->table_name_book} SET status = status ^ %d WHERE id = %d", $status, $id ) );
    }

    public static function update_status( $id, $status ) {
        global $wpdb;

        $gp = gampress();
        $wpdb->query( $wpdb->prepare( "UPDATE {$gp->books->table_name_book} SET status = status | %d WHERE id = %d", $status, $id ) );
    }

    protected static function convert_orderby_to_order_by_term( $orderby ) {
        $order_by_term = '';

        switch ( $orderby ) {
            case 'cover':
                $order_by_term = 'b.cover';
                break;

            case 'post_time' :
                $order_by_term = 'b.post_time, b.id';
                break;

            case 'words' :
                $order_by_term = 'b.words';
                break;

            case 'bookmarks':
                $order_by_term = 'b.bookmarks';
                break;

            default :
                $order_by_term = 'b.id';
                break;
        }

        return $order_by_term;
    }
}