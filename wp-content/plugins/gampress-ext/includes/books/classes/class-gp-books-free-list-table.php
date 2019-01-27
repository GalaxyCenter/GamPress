<?php
/**
 * Created by PhpStorm.
 * User: bourne
 * Date: 2017/4/2
 * Time: 下午9:27
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GP_Books_Free_List_Table' ) ) :

    class GP_Books_Free_List_Table extends WP_List_Table {
        public function __construct() {

            // Define singular and plural labels, as well as whether we support AJAX.
            parent::__construct( array(
                'ajax'     => false,
                'plural'   => 'book-frees',
                'singular' => 'book-free',
                'screen'   => get_current_screen(),
            ) );
        }

        function prepare_items() {
            // Set current page
            $page = $this->get_pagenum();

            // Set per page from the screen options
            $per_page = $this->get_items_per_page( str_replace( '-', '_', "{$this->screen->id}_per_page" ) );

            // Are we doing a search?
            if ( !empty( $_REQUEST['s'] ) )
                $search_terms = $_REQUEST['s'];
            else
                $search_terms = false;

            if ( isset( $_REQUEST['orderby'] ) )
                $orderby = $_REQUEST['orderby'];
            else
                $orderby = false;

            if ( isset( $_REQUEST['order'] ) )
                $order = $_REQUEST['order'];
            else
                $order = 'desc';


            $datas = gp_books_get_frees( array(
                'search_terms'     => $search_terms,
                'orderby'          => $orderby,
                'order'            => $order,
                'page'             => $page,
                'per_page'         => $per_page
            ) );
            $this->items       = $datas['items'];

            // Store information needed for handling table pagination
            $this->set_pagination_args( array(
                'per_page'    => $per_page,
                'total_items' => $datas['total'],
                'total_pages' => ceil( $datas['total'] / $per_page )
            ) );
        }

        function single_row( $item ) {
            static $even = false;

            if ( $even ) {
                $row_class = ' class="even"';
            } else {
                $row_class = ' class="alternate odd"';
            }

            $root_id = $item->id;

            echo '<tr' . $row_class . ' id="book-free-' . esc_attr( $item->id ) . '" data-parent_id="' . esc_attr( $item->id ) . '" data-root_id="' . esc_attr( $root_id ) . '">';
            echo $this->single_row_columns( $item );
            echo '</tr>';

            $even = ! $even;
        }

        function get_bulk_actions() {
            $actions = array();

            return $actions;
        }

        function get_columns() {
            $columns = array(
                'cb'                => '<input name type="checkbox" />',
                'name'              => __( 'Name', 'gampress-ext' ),
                'books'             => __( 'Books', 'gampress-ext' ),
                'start_time'        => __( 'StartTime', 'gampress-ext' ),
                'end_time'          => __( 'EndTime', 'gampress-ext' )
            );

            return $columns;
        }

        function get_sortable_columns() {
            $c = array(
                'start_time' => 'start_time',
                'end_time' => 'end_time'
            );

            return $c;
        }

        function extra_tablenav( $which ) {
            if ( 'bottom' == $which )
                return;
        }

        function column_cb( $item ) {
            $args = array('post_type' => gp_get_book_recommend_post_type(), 'posts_per_page' => 1, 'post_parent' => $item->id);
            $loop = new WP_Query($args);
            if ($loop->have_posts()) {
                printf('<label class="screen-reader-text" for="book_id-%1$s">' . __('Select book item %1$s', 'gampress') . '</label>', $item->id);
            } else {
                printf('<label class="screen-reader-text" for="book_id-%1$s">' . __('Select book item %1$s', 'gampress') . '</label><input type="checkbox" name="id[]" value="%1$s" id="id-%1$s" />', $item->id);
            }
        }

        function column_name( $item ) {
            $base_url   = gp_get_admin_url( 'admin.php?page=gp-books-free&amp;id=' . $item->id );
            $edit_url    = $base_url . '&amp;action=edit';

            printf( __( '<a href="%1$s" id="id-%2$s">%3$s</a>', 'gampress-ext' ), $edit_url, $item->id, $item->name );
        }

        function column_books( $item ) {
            $bids = explode( ",", $item->book_ids );
            $bids = array_filter( $bids);
            $free_books = array();
            if ( !empty( $bids ) ) {
                foreach ($bids as $bid) {
                    $book = gp_books_get_book( $bid );
                    $free_books[] = $book->title;
                }
            }
            $free_books = join( ',', $free_books );

            echo $free_books;
        }

        function column_start_time( $item ) {
            echo $item->start_time;
        }

        function column_end_time( $item ) {
            echo $item->end_time;
        }

    }

endif;