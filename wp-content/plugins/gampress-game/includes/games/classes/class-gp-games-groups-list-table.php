<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 12:42
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GP_Games_Groups_List_Table' ) ) :

    class GP_Games_Groups_List_Table extends WP_List_Table {
        public function __construct() {

            // Define singular and plural labels, as well as whether we support AJAX.
            parent::__construct( array(
                'ajax'     => false,
                'plural'   => 'groups',
                'singular' => 'group',
                'screen'   => get_current_screen(),
            ) );
        }

        function prepare_items() {
            // Set current page
            $page = $this->get_pagenum();

            // Set per page from the screen options
            $per_page = $this->get_items_per_page( str_replace( '-', '_', "{$this->screen->id}_per_page" ) );

            if ( !empty( $_REQUEST['activity_id'] ) )
                $activity_id = $_REQUEST['activity_id'];
            else
                $activity_id = false;

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

            // Get the chapters from the database
            $datas = gp_games_groups_get_groups( array(
                'activity_id'      => $activity_id,
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

            remove_filter( 'gp_get_group_content_body', 'gp_group_truncate_entry', 5 );
        }

        function single_row( $item ) {
            static $even = false;

            if ( $even ) {
                $row_class = ' class="even"';
            } else {
                $row_class = ' class="alternate odd"';
            }

            $root_id = $item->id;

            echo '<tr' . $row_class . ' id="group-' . esc_attr( $item->id ) . '" data-parent_id="' . esc_attr( $item->id ) . '" data-root_id="' . esc_attr( $root_id ) . '">';
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
                'cb'                => '<input name type="checkbox" />',
                'name'              => __( 'Name', 'gampress-game' ),
                'user'              => __( 'User', 'gampress-game' ),
                'date_created'      => __( 'CreateTime', 'gampress-game' ),
                'members'           => __( 'Members', 'gampress-game' ),
                'contact'           => __( 'Contact', 'gampress-game' )
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
            $actions  = array();
            ?>

            <div class="alignleft actions">
                <select name="status">
                    <option value="" <?php selected( !$status ); ?>><?php _e( 'Show all groups', 'gampress' ); ?></option>

                    <?php foreach ( $actions as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v,  $status ); ?>><?php echo esc_html( $k ); ?></option>
                    <?php endforeach; ?>
                </select>

                <?php submit_button( __( 'Filter', 'gampress-game' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) ); ?>
            </div>

            <?php
        }

        function column_cb( $item ) {
            printf( '<label class="screen-reader-text" for="group_id-%1$s">' . __( 'Select group item %1$s', 'gampress' ) . '</label><input type="checkbox" name="id[]" value="%1$s" id="group_id-%1$s" />', $item->id );
        }

        function column_name( $item ) {
            echo $item->name;
        }

        function column_user( $item ) {
            echo gp_core_get_user_displayname( $item->owner_id );
        }

        function column_date_created( $item ) {
            echo $item->date_created;
        }

        function column_members( $item ) {
            echo gp_games_groups_get_total_member_count( $item->id );
        }

        function column_contact( $item ) {
            echo gp_users_get_meta( $item->owner_id, 'contact_user_name', '', true );
            echo gp_users_get_meta( $item->owner_id, 'contact_phone', '', true );
        }
    }

endif;