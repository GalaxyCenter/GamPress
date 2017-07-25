<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/16
 * Time: 10:27
 */

defined( 'ABSPATH' ) || exit;

class GP_Notifications_Notification {

    public $id;

    public $item_id;

    public $secondary_item_id = null;

    public $user_id;

    public $component_name;

    public $component_action;

    public $date_notified;

    public $is_new;

    /** Public Methods ********************************************************/
    public function __construct( $id = 0 ) {
        if ( ! empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function save() {
        $retval = false;

        $data = array(
            'user_id'           => $this->user_id,
            'item_id'           => $this->item_id,
            'secondary_item_id' => $this->secondary_item_id,
            'component_name'    => $this->component_name,
            'component_action'  => $this->component_action,
            'date_notified'     => $this->date_notified,
            'is_new'            => $this->is_new,
        );
        $data_format = array( '%d', '%d', '%d', '%s', '%s', '%s', '%d' );

        // Update.
        if ( ! empty( $this->id ) ) {
            $result = self::_update( $data, array( 'ID' => $this->id ), $data_format, array( '%d' ) );

            // Insert.
        } else {
            $result = self::_insert( $data, $data_format );
        }

        // Set the notification ID if successful.
        if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
            global $wpdb;

            $this->id = $wpdb->insert_id;
            $retval   = $wpdb->insert_id;
        }

        // Return the result.
        return $retval;
    }

    public function populate() {
        global $wpdb;

        $gp = gampress();

        // Look for a notification.
        $notification = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$gp->notifications->table_name} WHERE id = %d", $this->id ) );

        // Setup the notification data.
        if ( ! empty( $notification ) && ! is_wp_error( $notification ) ) {
            $this->item_id           = (int) $notification->item_id;
            $this->secondary_item_id = (int) $notification->secondary_item_id;
            $this->user_id           = (int) $notification->user_id;
            $this->component_name    = $notification->component_name;
            $this->component_action  = $notification->component_action;
            $this->date_notified     = $notification->date_notified;
            $this->is_new            = (int) $notification->is_new;
        }
    }

    /** Protected Static Methods **********************************************/
    protected static function _insert( $data = array(), $data_format = array() ) {
        global $wpdb;
        return $wpdb->insert( gampress()->notifications->table_name, $data, $data_format );
    }

    protected static function _update( $data = array(), $where = array(), $data_format = array(), $where_format = array() ) {
        global $wpdb;
        return $wpdb->update( gampress()->notifications->table_name, $data, $where, $data_format, $where_format );
    }

    protected static function _delete( $where = array(), $where_format = array() ) {
        global $wpdb;
        return $wpdb->delete( gampress()->notifications->table_name, $where, $where_format );
    }

    protected static function get_where_sql( $args = array(), $select_sql = '', $from_sql = '', $join_sql = '', $meta_query_sql = '' ) {
        global $wpdb;

        $where_conditions = array();
        $where            = '';

        // The id.
        if ( ! empty( $args['id'] ) ) {
            $id_in = implode( ',', wp_parse_id_list( $args['id'] ) );
            $where_conditions['id'] = "id IN ({$id_in})";
        }

        // The user_id.
        if ( ! empty( $args['user_id'] ) ) {
            $user_id_in = implode( ',', wp_parse_id_list( $args['user_id'] ) );
            $where_conditions['user_id'] = "user_id IN ({$user_id_in})";
        }

        // The item_id.
        if ( ! empty( $args['item_id'] ) ) {
            $item_id_in = implode( ',', wp_parse_id_list( $args['item_id'] ) );
            $where_conditions['item_id'] = "item_id IN ({$item_id_in})";
        }

        // The secondary_item_id.
        if ( ! empty( $args['secondary_item_id'] ) ) {
            $secondary_item_id_in = implode( ',', wp_parse_id_list( $args['secondary_item_id'] ) );
            $where_conditions['secondary_item_id'] = "secondary_item_id IN ({$secondary_item_id_in})";
        }

        // The component_name.
        if ( ! empty( $args['component_name'] ) ) {
            if ( ! is_array( $args['component_name'] ) ) {
                $component_names = explode( ',', $args['component_name'] );
            } else {
                $component_names = $args['component_name'];
            }

            $cn_clean = array();
            foreach ( $component_names as $cn ) {
                $cn_clean[] = $wpdb->prepare( '%s', $cn );
            }

            $cn_in = implode( ',', $cn_clean );
            $where_conditions['component_name'] = "component_name IN ({$cn_in})";
        }

        // The component_action.
        if ( ! empty( $args['component_action'] ) ) {
            if ( ! is_array( $args['component_action'] ) ) {
                $component_actions = explode( ',', $args['component_action'] );
            } else {
                $component_actions = $args['component_action'];
            }

            $ca_clean = array();
            foreach ( $component_actions as $ca ) {
                $ca_clean[] = $wpdb->prepare( '%s', $ca );
            }

            $ca_in = implode( ',', $ca_clean );
            $where_conditions['component_action'] = "component_action IN ({$ca_in})";
        }

        // If is_new.
        if ( ! empty( $args['is_new'] ) && 'both' !== $args['is_new'] ) {
            $where_conditions['is_new'] = "is_new = 1";
        } elseif ( isset( $args['is_new'] ) && ( 0 === $args['is_new'] || false === $args['is_new'] ) ) {
            $where_conditions['is_new'] = "is_new = 0";
        }

        // The search_terms.
        if ( ! empty( $args['search_terms'] ) ) {
            $search_terms_like = '%' . bp_esc_like( $args['search_terms'] ) . '%';
            $where_conditions['search_terms'] = $wpdb->prepare( "( component_name LIKE %s OR component_action LIKE %s )", $search_terms_like, $search_terms_like );
        }

        // The date query.
        if ( ! empty( $args['date_query'] ) ) {
            $where_conditions['date_query'] = self::get_date_query_sql( $args['date_query'] );
        }

        // The meta query.
        if ( ! empty( $meta_query_sql['where'] ) ) {
            $where_conditions['meta_query'] = $meta_query_sql['where'];
        }

        // Custom WHERE.
        if ( ! empty( $where_conditions ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_conditions );
        }

        return $where;
    }

    protected static function get_order_by_sql( $args = array() ) {

        // Setup local variable.
        $conditions = array();
        $retval     = '';

        // Order by.
        if ( ! empty( $args['order_by'] ) ) {
            $order_by               = implode( ', ', (array) $args['order_by'] );
            $conditions['order_by'] = "{$order_by}";
        }

        // Sort order direction.
        if ( ! empty( $args['sort_order'] ) && in_array( $args['sort_order'], array( 'ASC', 'DESC' ) ) ) {
            $sort_order               = $args['sort_order'];
            $conditions['sort_order'] = "{$sort_order}";
        }

        // Custom ORDER BY.
        if ( ! empty( $conditions ) ) {
            $retval = 'ORDER BY ' . implode( ' ', $conditions );
        }

        return $retval;
    }

    protected static function get_paged_sql( $args = array() ) {
        global $wpdb;

        // Setup local variable.
        $retval = '';

        // Custom LIMIT.
        if ( ! empty( $args['page'] ) && ! empty( $args['per_page'] ) ) {
            $page     = absint( $args['page']     );
            $per_page = absint( $args['per_page'] );
            $offset   = $per_page * ( $page - 1 );
            $retval   = $wpdb->prepare( "LIMIT %d, %d", $offset, $per_page );
        }

        return $retval;
    }

    protected static function get_query_clauses( $args = array() ) {
        $where_clauses = array(
            'data'   => array(),
            'format' => array(),
        );

        // The id.
        if ( ! empty( $args['id'] ) ) {
            $where_clauses['data']['id'] = absint( $args['id'] );
            $where_clauses['format'][] = '%d';
        }

        // The user_id.
        if ( ! empty( $args['user_id'] ) ) {
            $where_clauses['data']['user_id'] = absint( $args['user_id'] );
            $where_clauses['format'][] = '%d';
        }

        // The item_id.
        if ( ! empty( $args['item_id'] ) ) {
            $where_clauses['data']['item_id'] = absint( $args['item_id'] );
            $where_clauses['format'][] = '%d';
        }

        // The secondary_item_id.
        if ( ! empty( $args['secondary_item_id'] ) ) {
            $where_clauses['data']['secondary_item_id'] = absint( $args['secondary_item_id'] );
            $where_clauses['format'][] = '%d';
        }

        // The component_name.
        if ( ! empty( $args['component_name'] ) ) {
            $where_clauses['data']['component_name'] = $args['component_name'];
            $where_clauses['format'][] = '%s';
        }

        // The component_action.
        if ( ! empty( $args['component_action'] ) ) {
            $where_clauses['data']['component_action'] = $args['component_action'];
            $where_clauses['format'][] = '%s';
        }

        // If is_new.
        if ( isset( $args['is_new'] ) ) {
            $where_clauses['data']['is_new'] = ! empty( $args['is_new'] ) ? 1 : 0;
            $where_clauses['format'][] = '%d';
        }

        return $where_clauses;
    }

    /** Public Static Methods *************************************************/
    public static function check_access( $user_id, $notification_id ) {
        global $wpdb;

        $gp = gampress();

        return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$gp->core->table_name_notifications} WHERE id = %d AND user_id = %d", $notification_id, $user_id ) );
    }

    public static function parse_args( $args = '' ) {
        return wp_parse_args( $args, array(
            'id'                => false,
            'user_id'           => false,
            'item_id'           => false,
            'secondary_item_id' => false,
            'component_name'    => bp_notifications_get_registered_components(),
            'component_action'  => false,
            'is_new'            => true,
            'search_terms'      => '',
            'order_by'          => false,
            'sort_order'        => false,
            'page'              => false,
            'per_page'          => false,
            'meta_query'        => false,
            'date_query'        => false,
            'update_meta_cache' => true
        ) );
    }

    public static function get( $args = array() ) {
        global $wpdb;

        // Parse the arguments.
        $r = self::parse_args( $args );

        // Get BuddyPress.
        $gp = gampress();

        // METADATA.
        $meta_query_sql = self::get_meta_query_sql( $r['meta_query'] );

        // SELECT.
        $select_sql = "SELECT *";

        // FROM.
        $from_sql   = "FROM {$gp->notifications->table_name} n ";

        // JOIN.
        $join_sql   = $meta_query_sql['join'];

        // WHERE.
        $where_sql  = self::get_where_sql( array(
            'id'                => $r['id'],
            'user_id'           => $r['user_id'],
            'item_id'           => $r['item_id'],
            'secondary_item_id' => $r['secondary_item_id'],
            'component_name'    => $r['component_name'],
            'component_action'  => $r['component_action'],
            'is_new'            => $r['is_new'],
            'search_terms'      => $r['search_terms'],
            'date_query'        => $r['date_query']
        ), $select_sql, $from_sql, $join_sql, $meta_query_sql );

        // ORDER BY.
        $order_sql  = self::get_order_by_sql( array(
            'order_by'   => $r['order_by'],
            'sort_order' => $r['sort_order']
        ) );

        // LIMIT %d, %d.
        $pag_sql    = self::get_paged_sql( array(
            'page'     => $r['page'],
            'per_page' => $r['per_page']
        ) );

        // Concatenate query parts.
        $sql = "{$select_sql} {$from_sql} {$join_sql} {$where_sql} {$order_sql} {$pag_sql}";

        $results = $wpdb->get_results( $sql );

        // Integer casting.
        foreach ( $results as $key => $result ) {
            $results[$key]->id                = (int) $results[$key]->id;
            $results[$key]->user_id           = (int) $results[$key]->user_id;
            $results[$key]->item_id           = (int) $results[$key]->item_id;
            $results[$key]->secondary_item_id = (int) $results[$key]->secondary_item_id;
            $results[$key]->is_new            = (int) $results[$key]->is_new;
        }

        // Update meta cache.
        if ( true === $r['update_meta_cache'] ) {
            bp_notifications_update_meta_cache( wp_list_pluck( $results, 'id' ) );
        }

        return $results;
    }

    public static function get_total_count( $args ) {
        global $wpdb;

        // Parse the arguments.
        $r = self::parse_args( $args );

        // Load BuddyPress.
        $gp = gampress();

        // METADATA.
        $meta_query_sql = self::get_meta_query_sql( $r['meta_query'] );

        // SELECT.
        $select_sql = "SELECT COUNT(*)";

        // FROM.
        $from_sql   = "FROM {$gp->notifications->table_name} n ";

        // JOIN.
        $join_sql   = $meta_query_sql['join'];

        // WHERE.
        $where_sql  = self::get_where_sql( array(
            'id'                => $r['id'],
            'user_id'           => $r['user_id'],
            'item_id'           => $r['item_id'],
            'secondary_item_id' => $r['secondary_item_id'],
            'component_name'    => $r['component_name'],
            'component_action'  => $r['component_action'],
            'is_new'            => $r['is_new'],
            'search_terms'      => $r['search_terms'],
            'date_query'        => $r['date_query']
        ), $select_sql, $from_sql, $join_sql, $meta_query_sql );

        // Concatenate query parts.
        $sql = "{$select_sql} {$from_sql} {$join_sql} {$where_sql}";

        // Return the queried results.
        return (int) $wpdb->get_var( $sql );
    }

    public static function get_meta_query_sql( $meta_query = array() ) {

        // Default array keys & empty values.
        $sql_array = array(
            'join'  => '',
            'where' => '',
        );

        // Bail if no meta query.
        if ( empty( $meta_query ) ) {
            return $sql_array;
        }

        // WP_Meta_Query expects the table name at $wpdb->notificationmeta.
        $GLOBALS['wpdb']->notificationmeta = gampress()->notifications->table_name_meta;

        $n_meta_query = new WP_Meta_Query( $meta_query );
        $meta_sql     = $n_meta_query->get_sql( 'notification', 'n', 'id' );

        // Strip the leading AND - it's handled in get().
        $sql_array['where'] = preg_replace( '/^\sAND/', '', $meta_sql['where'] );
        $sql_array['join']  = $meta_sql['join'];

        return $sql_array;
    }

    public static function get_date_query_sql( $date_query = array() ) {

        // Bail if not a proper date query format.
        if ( empty( $date_query ) || ! is_array( $date_query ) ) {
            return '';
        }

        // Date query.
        $date_query = new GP_Date_Query( $date_query, 'date_recorded' );

        // Strip the leading AND - it's handled in get().
        return preg_replace( '/^\sAND/', '', $date_query->get_sql() );
    }

    public static function update( $update_args = array(), $where_args = array() ) {
        $update = self::get_query_clauses( $update_args );
        $where  = self::get_query_clauses( $where_args  );

        return self::_update(
            $update['data'],
            $where['data'],
            $update['format'],
            $where['format']
        );
    }

    public static function delete( $args = array() ) {
        $where = self::get_query_clauses( $args );

        return self::_delete( $where['data'], $where['format'] );
    }

    /** Convenience methods ***************************************************/
    public static function delete_by_id( $id ) {
        return self::delete( array(
            'id' => $id,
        ) );
    }

    public static function get_all_for_user( $user_id, $status = 'is_new' ) {
        return self::get( array(
            'user_id' => $user_id,
            'is_new'  => 'is_new' === $status,
        ) );
    }

    public static function get_unread_for_user( $user_id = 0 ) {
        return self::get( array(
            'user_id' => $user_id,
            'is_new'  => true,
        ) );
    }

    public static function get_read_for_user( $user_id = 0 ) {
        return self::get( array(
            'user_id' => $user_id,
            'is_new'  => false,
        ) );
    }

    public static function get_current_notifications_for_user( $args = array() ) {
        $r = wp_parse_args( $args, array(
            'user_id'      => bp_loggedin_user_id(),
            'is_new'       => true,
            'page'         => 1,
            'per_page'     => 25,
            'search_terms' => '',
        ) );

        $notifications = self::get( $r );

        // Bail if no notifications.
        if ( empty( $notifications ) ) {
            return false;
        }

        $total_count = self::get_total_count( $r );

        return array( 'notifications' => &$notifications, 'total' => $total_count );
    }

    /** Mark ******************************************************************/

    public static function mark_all_for_user( $user_id, $is_new = 0, $item_id = 0, $component_name = '', $component_action = '', $secondary_item_id = 0 ) {

        // Values to be updated.
        $update_args = array(
            'is_new' => $is_new,
        );

        // WHERE clauses.
        $where_args = array(
            'user_id' => $user_id,
        );

        if ( ! empty( $item_id ) ) {
            $where_args['item_id'] = $item_id;
        }

        if ( ! empty( $component_name ) ) {
            $where_args['component_name'] = $component_name;
        }

        if ( ! empty( $component_action ) ) {
            $where_args['component_action'] = $component_action;
        }

        if ( ! empty( $secondary_item_id ) ) {
            $where_args['secondary_item_id'] = $secondary_item_id;
        }

        return self::update( $update_args, $where_args );
    }

    public static function mark_all_from_user( $user_id, $is_new = 0, $component_name = '', $component_action = '', $secondary_item_id = 0 ) {

        // Values to be updated.
        $update_args = array(
            'is_new' => $is_new,
        );

        // WHERE clauses.
        $where_args = array(
            'item_id' => $user_id,
        );

        if ( ! empty( $component_name ) ) {
            $where_args['component_name'] = $component_name;
        }

        if ( ! empty( $component_action ) ) {
            $where_args['component_action'] = $component_action;
        }

        if ( ! empty( $secondary_item_id ) ) {
            $where_args['secondary_item_id'] = $secondary_item_id;
        }

        return self::update( $update_args, $where_args );
    }

    public static function mark_all_by_type( $item_id, $is_new = 0, $component_name = '', $component_action = '', $secondary_item_id = 0 ) {

        // Values to be updated.
        $update_args = array(
            'is_new' => $is_new,
        );

        // WHERE clauses.
        $where_args = array(
            'item_id' => $item_id,
        );

        if ( ! empty( $component_name ) ) {
            $where_args['component_name'] = $component_name;
        }

        if ( ! empty( $component_action ) ) {
            $where_args['component_action'] = $component_action;
        }

        if ( ! empty( $secondary_item_id ) ) {
            $where_args['secondary_item_id'] = $secondary_item_id;
        }

        return self::update( $update_args, $where_args );
    }
}

