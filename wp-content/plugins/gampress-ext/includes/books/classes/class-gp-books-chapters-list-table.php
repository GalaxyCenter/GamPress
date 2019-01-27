<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/16
 * Time: 17:50
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GP_Books_Chapters_List_Table' ) ) :

    class GP_Books_Chapters_List_Table extends WP_List_Table {
        public function __construct() {

            // Define singular and plural labels, as well as whether we support AJAX.
            parent::__construct( array(
                'ajax'     => false,
                'plural'   => 'chapters',
                'singular' => 'chapter',
                'screen'   => get_current_screen(),
            ) );
        }

        function prepare_items() {
            // Set current page
            $page = $this->get_pagenum();

            // Set per page from the screen options
            $per_page = $this->get_items_per_page( str_replace( '-', '_', "{$this->screen->id}_per_page" ) );

            if ( !empty( $_REQUEST['book_id'] ) )
                $book_id = $_REQUEST['book_id'];
            else
                $book_id = 0;

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
                $order = 'asc';


            if ( isset( $_REQUEST['status'] ) )
                $status = $_REQUEST['status'];
            else
                $status = -1;

            // Get the chapters from the database
            $datas = gp_books_get_chapters( array(
                'book_id'          => $book_id,
                'search_terms'     => $search_terms,
                'orderby'          => $orderby,
                'status'           => $status,
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
                    'charge' => 'Charge',
                    'approved' => 'Approved',
                    'format_body' => 'FormatBody' );

            return $actions;
        }

        function get_columns() {
            return array(
                'cb'                => '<input name type="checkbox" />',
                'title'             => __( 'Title', 'gampress-ext' ),
                'order'             => __( 'Chapter Order', 'gampress-ext' ),
                'words'             => __( 'Words', 'gampress-ext' ),
                'is_charge'         => __( 'Is Charge', 'gampress-ext' ),
                'post_time'         => __( 'PostTime', 'gampress-ext' ),
                'status'            => __( 'Status', 'gampress-ext' ),
                'approved_time'     => __( 'ApprovedTime', 'gampress-ext' ),
                'update_time'       => __( 'UpdateTime', 'gampress-ext' ),
            );
        }

        function get_sortable_columns() {
            $c = array(
                'refer' => 'refer',
            );

            return $c;
        }

        function extra_tablenav( $which ) {
            if ( 'bottom' == $which )
                return;

            $status = !empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';
            $filters  = gp_books_get_chapter_filters();
            ?>

            <div class="alignleft actions">
                <select name="status">
                    <option value="" <?php selected( !$status ); ?>><?php _e( 'Show all chapters', 'gampress' ); ?></option>

                    <?php foreach ( $filters as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v,  $status ); ?>><?php echo esc_html( $k ); ?></option>
                    <?php endforeach; ?>
                </select>

                <?php submit_button( __( 'Filter', 'gampress-ext' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) ); ?>
            </div>

            <?php
        }

        function column_cb( $item ) {
            printf( '<label class="screen-reader-text" for="chapter_id-%1$s">' . __( 'Select chapter item %1$s', 'gampress' ) . '</label><input type="checkbox" name="id[]" value="%1$s" id="chapter_id-%1$s" />', $item->id );
        }

        function column_title( $item ) {
            $base_url   = gp_get_admin_url( 'admin.php?page=gp-chapters&amp;book_id=' . $item->book_id . '&amp;id=' . $item->id );
            //$spam_nonce = esc_html( '_wpnonce=' . wp_create_nonce( 'spam-chapter-' . $item->id ) );

            $edit_url   = $base_url . '&amp;action=edit';
            if ( isset( $_GET['book_id'] ) ) {
                $title = $item->title;
            } else {
                $book = gp_books_get_book( $item->book_id );

                $title = $book->title . ' - ' . $item->title;
            }
            printf( __( '<a href="%1$s" id="id-%2$s">%3$s</a>', 'gampress' ), $edit_url, $item->id, $title );

            $simple_link_url = gp_get_links_permalink( gp_get_chapter_permalink( $item, true ) );
            $normal_link_url = gp_get_links_permalink( gp_get_chapter_permalink( $item, false ) );

            $actions = array(
                'slink'   => sprintf( '<a href="%s" id="book_id-%s">%s</a>', $simple_link_url, $item->id, __( 'Simple Link', 'gampress-ext' ) ),
                'nlink'   => sprintf( '<a href="%s" id="book_id-%s">%s</a>', $normal_link_url, $item->id, __( 'Normal Link', 'gampress-ext' ) ),
            );
            echo $this->row_actions( $actions );
        }

        function column_order( $item ) {
            gp_chapter_order( $item );
        }

        function column_words( $item ) {
            gp_chapter_words( $item );
        }

        function column_is_charge( $item ) {
            gp_chapter_is_charge( $item, true );
        }

        function column_post_time( $item ) {
            gp_chapter_post_time( $item );
        }

        function column_status( $item ) {
            gp_chapter_status( $item );
        }

        function column_approved_time( $item ) {
            gp_chapter_approved_time( $item );
        }

        function column_update_time( $item ) {
            gp_chapter_update_time( $item );
        }
    }

endif;