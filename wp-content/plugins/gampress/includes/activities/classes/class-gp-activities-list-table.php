<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 12:42
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GP_Activities_List_Table' ) ) :

    class GP_Activities_List_Table extends WP_List_Table {
        public function __construct() {

            // Define singular and plural labels, as well as whether we support AJAX.
            parent::__construct( array(
                'ajax'     => false,
                'plural'   => 'activities',
                'singular' => 'activities',
                'screen'   => get_current_screen(),
            ) );
        }

        function prepare_items() {
            // Set current page
            $page = $this->get_pagenum();

            // Set per page from the screen options
            $per_page = $this->get_items_per_page( str_replace( '-', '_', "{$this->screen->id}_per_page" ) );

            if ( isset( $_REQUEST['user_id'] ) )
                $user_id = $_REQUEST['user_id'];
            else
                $user_id = false;

            if ( isset( $_REQUEST['item_id'] ) )
                $item_id = $_REQUEST['item_id'];
            else
                $item_id = false;

            if ( !empty( $_REQUEST['s'] ) )
                $search_terms = $_REQUEST['s'];
            else
                $search_terms = false;

            // Get the chapters from the database
            $datas = gp_activities_get_activities( array(
                'search_terms'   => $search_terms,
                'user_id'        => $user_id,
                'item_id'        => $item_id,
                'order'          => 'DESC',
                'page'           => $page,
                'per_page'       => $per_page
            ) );

            $this->items       = $datas['items'];

            // Store information needed for handling table pagination
            $this->set_pagination_args( array(
                'per_page'    => $per_page,
                'total_items' => $datas['total'],
                'total_pages' => ceil( $datas['total'] / $per_page )
            ) );

            remove_filter( 'gp_get_chapter_content_body', 'gp_chapter_truncate_entry', 5 );
        }

        function single_row( $item ) {
            static $even = false;

            if ( $even ) {
                $row_class = ' class="even"';
            } else {
                $row_class = ' class="alternate odd"';
            }

            $root_id = $item->id;

            echo '<tr' . $row_class . ' id="chapter-' . esc_attr( $item->id ) . '" data-parent_id="' . esc_attr( $item->id ) . '" data-root_id="' . esc_attr( $root_id ) . '">';
            echo $this->single_row_columns( $item );
            echo '</tr>';

            $even = ! $even;
        }

        function get_bulk_actions() {
            $actions = array(
                'bulk_disapproved'  => __( 'Disapproved',     'gampress' ) );

            return $actions;
        }

        function get_columns() {
            return array(
                'cb'                => '<input name type="checkbox" />',
                'user'              => __( 'User', 'gampress' ),
                'item'              => __( 'Item', 'gampress' ),
                'content'           => __( 'Content', 'gampress' ),
                'status'            => __( 'Status', 'gampress' ),
                'post_time'         => __( 'PostTime', 'gampress' )
            );
        }

        function get_sortable_columns() {
            $c = array(
                'refer' => 'refer',
            );

            return $c;
        }

        function extra_tablenav( $which ) {
        }

        function column_cb( $item ) {
            printf( '<label class="screen-reader-text" for="chapter_id-%1$s">' . __( 'Select chapter item %1$s', 'gampress' ) . '</label><input type="checkbox" name="id[]" value="%1$s" id="chapter_id-%1$s" />', $item->id );
        }

        function column_user( $item ) {
            $base_url   = gp_get_admin_url( 'admin.php?page=gp-activities' );

            $filter_url = $base_url;
            if ( isset( $_GET['item_id'] ) )
                $filter_url = add_query_arg( 'item_id', $_GET['item_id'], $filter_url );

            $filter_url = add_query_arg( 'user_id', $item->user_id, $filter_url );
            printf( __( '<a href="%1$s" >%2$s</a>', 'gampress' ), $filter_url, gp_core_get_user_displayname( $item->user_id ) );
        }

        function column_item( $item ) {
            if ( $item->component == 'books' ) {
                $book = gp_books_get_book( $item->item_id );
                $title = $book->title;
            } else {
                $title = $item->id;
            }

            $base_url   = gp_get_admin_url( 'admin.php?page=gp-activities' );

            $filter_url = $base_url;
            if ( isset( $_GET['user_id'] ) )
                $filter_url = add_query_arg( 'user_id', $_GET['user_id'], $filter_url );

            $filter_url = add_query_arg( 'item_id', $item->item_id, $filter_url );
            printf( __( '<a href="%1$s" >%2$s</a>', 'gampress' ), $filter_url, $title );


        }

        function column_status( $item ) {
            echo $item->status;
        }

        function column_content( $item ) {
            echo $item->content;
        }

        function column_post_time( $item ) {
            echo gp_format_time( $item->post_time );
        }
    }

endif;