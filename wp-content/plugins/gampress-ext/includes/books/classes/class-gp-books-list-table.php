<?php
/**
 * Created by PhpStorm.
 * User: bourne
 * Date: 2017/4/2
 * Time: 下午9:27
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GP_Books_List_Table' ) ) :

    class GP_Books_List_Table extends WP_List_Table {
        public function __construct() {

            // Define singular and plural labels, as well as whether we support AJAX.
            parent::__construct( array(
                'ajax'     => false,
                'plural'   => 'books',
                'singular' => 'book',
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

            if ( isset( $_REQUEST['status'] ) )
                $status = $_REQUEST['status'];
            else
                $status = false;

            if ( isset( $_REQUEST['orderby'] ) )
                $orderby = $_REQUEST['orderby'];
            else
                $orderby = false;

            if ( isset( $_REQUEST['order'] ) )
                $order = $_REQUEST['order'];
            else
                $order = 'desc';

            if ( isset( $_REQUEST['category'] ) ) {
                $term = get_term_by( 'name', $_REQUEST['category'], 'book_library' );
                $cat_id = $term->term_id;
            } else {
                $cat_id = '';
            }

            $words_query = false;
            $words_query = isset( $_REQUEST['words_query'] ) ? $_REQUEST['words_query'] : '';
            if ( $words_query == 'words-g30w' )  {
                $words_query = array( 'value' => 300000, 'compare' => '>=' );
                $status = false;
            } elseif ( $words_query == 'words-g50w' )  {
                $words_query = array( 'value' => 500000, 'compare' => '>=' );
                $status = false;
            } elseif ( $words_query == 'words-g100w' )  {
                $words_query = array( 'value' => 1000000, 'compare' => '>=' );
                $status = false;
            } elseif ( $words_query == 'words-l30w' )  {
                $words_query = array( 'value' => 300000, 'compare' => '<=' );
                $status = false;
            }

            if ( isset( $_REQUEST['charge_type'] ) )
                $charge_type = $_REQUEST['charge_type'];
            else
                $charge_type = false;

            if ( $status == 'free' ) {
                $datas = gp_books_get_frees( array(
                    'intime'         => 'true',
                    'page'           => $page,
                    'per_page'       => $per_page ) );
            } else {
                // Get the books from the database
                $datas = gp_books_get_books( array(
                    'status'           => $status,
                    'search_terms'     => $search_terms,
                    'orderby'          => $orderby,
                    'order'            => $order,
                    'page'             => $page,
                    'term_ids'         => $cat_id,
                    'words_query'      => $words_query,
                    'charge_type'      => $charge_type,
                    'per_page'         => $per_page
                ) );
            }

            $this->items       = $datas['items'];

            // Store information needed for handling table pagination
            $this->set_pagination_args( array(
                'per_page'    => $per_page,
                'total_items' => $datas['total'],
                'total_pages' => ceil( $datas['total'] / $per_page )
            ) );

            remove_filter( 'gp_get_book_content_body', 'gp_book_truncate_entry', 5 );
        }

        function single_row( $item ) {
            static $even = false;

            if ( $even ) {
                $row_class = ' class="even"';
            } else {
                $row_class = ' class="alternate odd"';
            }

            $root_id = $item->id;

            echo '<tr' . $row_class . ' id="book-' . esc_attr( $item->id ) . '" data-parent_id="' . esc_attr( $item->id ) . '" data-root_id="' . esc_attr( $root_id ) . '">';
            echo $this->single_row_columns( $item );
            echo '</tr>';

            $even = ! $even;
        }

        function get_bulk_actions() {
            $actions = array(
                    'bulk_hide'  => __( 'Hide',     'gampress-ext' ),
                    'bulk_show'  => __(  'Show',      'gampress-ext' ),
                    'bulk_seriating' => __( 'Seriating',    'gampress-ext' ),
                    'bulk_finish'    => __( 'Finish',       'gampress-ext' ),
                    'bulk_free'      => __( 'Free',         'gampress-ext' ),
                    'bulk_unfree'    => __( 'UnFree',         'gampress-ext' ),
                    'bulk_recommend' => __( 'Recommend',    'gampress-ext' ) );

            $terms = get_terms( gp_get_book_recommend_post_taxonomy(), array(
                'orderby'    => 'count',
                'hide_empty' => false,
            ) );

            foreach ( $terms as $term ) {
                $actions['bulk_' . $term->slug] = __( 'Recommend', 'gampress-ext' ) . '->' . $term->name;
            }
            return $actions;
        }

        function get_columns() {
            $columns = array(
                'cb'                => '<input name type="checkbox" />',
                'title'             => __( 'Title', 'gampress-ext' ),
                'author'            => __( 'Author', 'gampress-ext' ),
                'words'             => __( 'Words', 'gampress-ext' ),
                'refer'             => __( 'Refer', 'gampress-ext' ),
                'status'            => __( 'Status', 'gampress-ext' ),
                'chapter_type'      => __( 'Chapter Type', 'gampress-ext' ),
                'tags'              => __( 'Tags', 'gampress-ext' ),
                'level'             => __( 'Level', 'gampress-ext' ),
                'point'             => __( 'Point', 'gampress-ext' ),
                'cover2'            => __( 'Cover', 'gampress-ext' ),
                'bookmarks'         => __( 'Bookmarks', 'gampress-ext' ),
                'price'             => __( 'Price', 'gampress-ext' ),
                'charge_type'       => __( 'Charge Type', 'gampress-ext' ),
                'category'          => _x( 'Category', 'admin', 'gampress-ext' ),
            );

            $status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';
            if ( $status === 'free' ) {
                $columns['start_time'] = __( 'Free StartTime', 'gampress-ext' );
                $columns['end_time']   = __( 'Free EndTime', 'gampress-ext' );
            }
            return $columns;
        }

        function get_sortable_columns() {
            $c = array(
                'cover2'    => 'cover',
                'words'     => 'words',
                'bookmarks' => 'bookmarks'
            );

            return $c;
        }

        function extra_tablenav( $which ) {
            if ( 'bottom' == $which )
                return;

            $status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';
            $charge_type = isset( $_REQUEST['charge_type'] ) ? $_REQUEST['charge_type'] : '';

            $actions  = gp_books_get_book_actions();

            $word_query = isset( $_REQUEST['words_query'] ) ? $_REQUEST['words_query'] : '';
            $word_query_actions = gp_books_get_book_words_query_actions();
            ?>

            <div class="alignleft actions">
                <select name="status">
                    <option value="" <?php selected( !$status ); ?>><?php _e( 'Show all books', 'gampress-ext' ); ?></option>

                    <?php foreach ( $actions as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v,  $status ); ?>><?php echo esc_html( $k ); ?></option>
                    <?php endforeach; ?>
                </select>


                <select name="words_query">
                    <option value="" <?php selected( !$word_query ); ?>><?php _e( 'Show all books', 'gampress-ext' ); ?></option>

                    <?php foreach ( $word_query_actions as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v,  $word_query ); ?>><?php echo esc_html( $k ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="charge_type">
                    <option value=""><?php _e( 'Show all books', 'gampress-ext' ); ?></option>

                    <option value="<?php echo GP_BOOK_CHARGE_TYPE_FREE;?>" <?php selected( GP_BOOK_CHARGE_TYPE_FREE,  $charge_type ); ?>><?php _e( 'CHARGE_TYPE_FREE', 'gampress-ext' ); ?></option>
                    <option value="<?php echo GP_BOOK_CHARGE_TYPE_CHAPTER;?>" <?php selected( GP_BOOK_CHARGE_TYPE_CHAPTER,  $charge_type ); ?>><?php _e( 'CHARGE_TYPE_CHAPTER', 'gampress-ext' ); ?></option>
                </select>

                <?php submit_button( __( 'Filter', 'gampress-ext' ), 'secondary', false, false, array( 'id' => 'btn-filter' ) ); ?>

            </div>

            <?php
        }

        function column_cb( $item ) {
//            $args = array('post_type' => gp_get_book_recommend_post_type(), 'posts_per_page' => 1, 'post_parent' => $item->id);
//            $loop = new WP_Query($args);
//            if ($loop->have_posts()) {
//                printf('<label class="screen-reader-text" for="book_id-%1$s">' . __('Select book item %1$s', 'gampress') . '</label>', $item->id);
//            } else {
//                printf('<label class="screen-reader-text" for="book_id-%1$s">' . __('Select book item %1$s', 'gampress') . '</label><input type="checkbox" name="id[]" value="%1$s" id="id-%1$s" />', $item->id);
//            }

            if ( ( $item->status & GP_BOOK_DISABLED ) != GP_BOOK_DISABLED )
                printf('<label class="screen-reader-text" for="book_id-%1$s">' . __('Select book item %1$s', 'gampress') . '</label><input type="checkbox" name="id[]" value="%1$s" id="id-%1$s" />', $item->id);
        }

        function column_title( $item ) {
            $base_url   = gp_get_admin_url( 'admin.php?page=gp-books&id=' . $item->id );
            $spam_nonce = esc_html( '_wpnonce=' . wp_create_nonce( 'spam-book-' . $item->id ) );

            $edit_url    = $base_url . '&amp;action=edit';
            $import_url  = $base_url . '&amp;action=import';
            $activity_url = remove_query_arg( array( 'page', 'id' ), $base_url );
            $activity_url = add_query_arg( 'item_id', $item->id, $activity_url );
            $activity_url = add_query_arg( 'page', 'gp-activities', $activity_url );
            $activity_url = add_query_arg( 'action', 'edit', $activity_url );


            $chapter_url = gp_get_admin_url( 'admin.php?page=gp-chapters&amp;book_id=' . $item->id );

            $args = array( 'post_type' => gp_get_book_recommend_post_type(), 'posts_per_page' => 1, 'post_parent' => $item->id, 'post_status' => 'publish' );
            $loop = new WP_Query( $args );
            if ( $loop->have_posts() ) {
                printf( __( '<a href="%1$s" id="id-%2$s" style="color:red;">%3$s</a><br/><br/>', 'gampress-ext' ), $chapter_url, $item->id, $item->title );

                $loop->the_post();
                $terms = wp_get_post_terms( $loop->post->ID, gp_get_book_recommend_post_taxonomy() );
                foreach ( $terms as $term ) {
                    echo $term->name;
                }
            } else {
                printf( __( '<a href="%1$s" id="id-%2$s">%3$s</a>', 'gampress-ext' ), $chapter_url, $item->id, $item->title );
            }

//            if ( GP_Books_Book_Free::exists( $item->id ) ) {
//                if ( $item->start_time < time() && time() < $item->end_time )
//                    echo __( 'Freeing', 'gampress-ext' );
//                else
//                    echo __( 'Free', 'gampress-ext' );
//            }

            $actions = array(
                'edit'   => sprintf( '<a href="%s" id="book_id-%s">%s</a>', $edit_url, $item->id, __( 'Edit', 'gampress-ext' ) ),
                'import' => sprintf( '<a href="%s" id="book_id-%s">%s</a>', $import_url, $item->id, __( 'Import', 'gampress-ext' ) ),
                'activity' => sprintf( '<a href="%s" id="book_id-%s">%s</a>', $activity_url, $item->id, __( 'Activity', 'gampress-ext' ) ),
            );
            echo $this->row_actions( $actions );
        }

        function column_author( $item ) {
            echo $item->author;
        }

        function column_refer( $item ) {
            $refer_url = gp_books_admin_get_refer_api_url( $item->refer );
            printf( __( '<a href="%1$s" target="_blank">%2$s</a>', 'gampress-ext' ), $refer_url, $item->refer );
        }

        function column_bookmarks( $item ) {
            echo '效果:' . $item->bookmarks . '+真实:' . gp_books_get_bookmark_count($item->id);
        }

        function column_status( $item ) {
            gp_book_status( $item, true, false );
        }

        function column_price( $item ) {
            echo gp_books_book_price( $item->id );
        }

        function column_words( $item ) {
            gp_book_words( $item );
        }

        function column_cover2( $item ) {
            echo empty( $item->cover ) ? 'NO' : 'YES';
        }

        function column_chapter_type( $item ) {
            gp_book_chapter_type( $item, true );
        }

        function column_tags( $item ) {
            gp_book_tags( $item );
        }

        function column_level( $item ) {
            gp_book_level( $item );
        }

        function column_point( $item ) {
            gp_book_point( $item );
        }

        function column_charge_type( $item ) {
            gp_book_charge_type( $item, true );
        }

        function column_category( $item ) {
            $terms = gp_get_object_terms( $item->id, 'book_library' );
            foreach ( $terms as $term ) {
                $filter_url = gp_get_admin_url( 'admin.php?page=gp-books&amp;category=' . $term->name );
                printf( __( '<a href="%1$s">%2$s</a><br/>', 'gampress' ), $filter_url, $term->name );
            }
        }

        function column_start_time( $item ) {
            echo gp_format_time( $item->start_time );
        }

        function column_end_time( $item ) {
            echo gp_format_time( $item->end_time );
        }

    }

endif;