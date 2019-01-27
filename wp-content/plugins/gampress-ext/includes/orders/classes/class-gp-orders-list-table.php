<?php
/**
 * Created by PhpStorm.
 * User: bourne
 * Date: 2017/4/2
 * Time: 下午9:27
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GP_Orders_List_Table' ) ) :

    class GP_Orders_List_Table extends WP_List_Table {
        public function __construct() {

            // Define singular and plural labels, as well as whether we support AJAX.
            parent::__construct( array(
                'ajax'     => false,
                'plural'   => 'orders',
                'singular' => 'order',
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

            if ( isset( $_REQUEST['status'] ) )
                $status = $_REQUEST['status'];
            else
                $status = false;

            if ( isset( $_REQUEST['type'] ) )
                $type = $_REQUEST['type'];
            else
                $type = false;

            if ( isset( $_REQUEST['orderby'] ) )
                $orderby = $_REQUEST['orderby'];
            else
                $orderby = 'create_time';

            if ( isset( $_REQUEST['order'] ) )
                $order = $_REQUEST['order'];
            else
                $order = 'desc';

            $datas = gp_orders_get_orders( array(
                'user_id'               => $user_id,
                'product_id'            => 0,
                'item_id'               => 0,
                'type'                  => $type,
                'status'                => $status,
                'order'                 => $order,
                'orderby'               => $orderby,
                'search_term'           => false,
                'meat_query'            => false,
                'page'                  => $page,
                'per_page'              => $per_page
            ) );

            $this->items       = $datas['items'];

            // Store information needed for handling table pagination
            $this->set_pagination_args( array(
                'per_page'    => $per_page,
                'total_items' => $datas['total'],
                'total_pages' => ceil( $datas['total'] / $per_page )
            ) );

            remove_filter( 'gp_get_order_content_body', 'gp_order_truncate_entry', 5 );
        }

        function single_row( $item ) {
            static $even = false;

            if ( $even ) {
                $row_class = ' class="even"';
            } else {
                $row_class = ' class="alternate odd"';
            }

            $root_id = $item->id;

            echo '<tr' . $row_class . ' id="order-' . esc_attr( $item->id ) . '" data-parent_id="' . esc_attr( $item->id ) . '" data-root_id="' . esc_attr( $root_id ) . '">';
            echo $this->single_row_columns( $item );
            echo '</tr>';

            $even = ! $even;
        }

        function get_bulk_actions() {
            $actions = array(  );

            return $actions;
        }

        function get_columns() {
            return array(
                'cb'                    => '<input name type="checkbox" />',
                'order_id'              => __( 'OrderId', 'gampress-ext' ),
                'product'               => __( 'ProductName', 'gampress-ext' ),
                'user_id'               => __( 'User', 'gampress-ext' ),
                'create_time'           => __( 'CreateTime', 'gampress-ext' ),
                'pay_time'              => __( 'PayTime', 'gampress-ext' ),
                'price'                 => __( 'Price', 'gampress-ext' ),
                'status'                => __( 'Status', 'gampress-ext' ),
                'time'                  => __( 'Time', 'gampress-ext' ),
                'type'                  => __( 'Type', 'gampress-ext' ),
                'ip'                    => __( 'IP', 'gampress-ext' ),
                'from'                  => __( 'From', 'gampress-ext' ),
            );
        }

        function get_sortable_columns() {
            $c = array(
                'price'             => 'price',
                'create_time'       => 'create_time',
                'pay_time'          => 'pay_time',
                'user_id'           => 'user_id',
                'time'              => 'time'
            );

            return $c;
        }

        function extra_tablenav( $which ) {
            if ( 'bottom' == $which )
                return;

            $status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';
            $type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';
            $status_actions  = gp_orders_get_order_actions();
            $types_actions  = gp_orders_get_order_types();
            ?>

            <div class="alignleft actions">
                <select name="status">
                    <option value="" <?php selected( !$status ); ?>><?php _e( 'Show all status', 'gampress-ext' ); ?></option>

                    <?php foreach ( $status_actions as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v,  $status ); ?>><?php echo esc_html( $k ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="type">
                    <option value="" <?php selected( !$type ); ?>><?php _e( 'Show all types', 'gampress-ext' ); ?></option>

                    <?php foreach ( $types_actions as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v,  $type ); ?>><?php echo esc_html( $k ); ?></option>
                    <?php endforeach; ?>
                </select>

                <?php submit_button( __( 'Filter', 'gampress-ext' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) ); ?>
            </div>

            <?php
        }

        function column_cb( $item ) {
            printf( '<label class="screen-reader-text" for="order_id-%1$s">' . __( 'Select order item %1$s', 'gampress' ) . '</label><input type="checkbox" name="id[]" value="%1$s" id="id-%1$s" />', $item->id );
        }

        function column_product( $item ) {
            if ( $item->type == GP_Orders_Order::BOOK ) {
                $order = gp_books_get_book( $item->item_id );
                $chapter = gp_books_get_chapter( $item->product_id );

                echo $order->title . '-(' . ( $chapter->order + 1 ) . ')-' . $chapter->title;
            } else {
                echo $item->item_id . '-' . $item->product_id;
            }
        }

        function column_order_id( $item ) {
            echo $item->order_id;
        }

        function column_user_id( $item ) {
            $base_url   = gp_get_admin_url( 'admin.php?page=gp-orders' );

            $filter_url = $base_url;
            if ( isset( $_GET['status'] ) )
                $filter_url = add_query_arg( 'status', $_GET['status'], $filter_url );

            if ( isset( $_GET['type'] ) )
                $filter_url = add_query_arg( 'type', $_GET['type'], $filter_url );

            $filter_url = add_query_arg( 'user_id', $item->user_id, $filter_url );
            printf( __( '<a href="%1$s" >%2$s</a>', 'gampress-ext' ), $filter_url, gp_core_get_user_displayname( $item->user_id ) );

            $user = get_userdata( $item->user_id );
            echo '<br/>' . $user->user_registered;
        }

        function column_create_time( $item ) {
            echo $item->create_time;
        }

        function column_pay_time( $item ) {
            echo $item->pay_time;
        }

        function column_price( $item ) {
            echo $item->price;
        }

        function column_time( $item ) {
            echo $item->time;
        }

        function column_status( $item ) {
            echo $item->status;
        }

        function column_type( $item ) {
            echo $item->type;
        }

        function column_ip( $item ) {
            echo $item->ip;
        }

        function column_from( $item ) {
            echo $item->come_from;
        }
    }

endif;