<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/15
 * Time: 21:51
 */

class GP_Messages_Thread {

    public $thread_id;

    public $messages;

    public $recipients;

    public $sender_ids;

    public $unread_count;

    public $last_message_content;

    public $last_message_date;

    public $last_message_id;

    public $last_message_subject;

    public $last_sender_id;

    public $messages_order;

    public function __construct( $thread_id = false, $order = 'ASC', $args = array() ) {
        if ( $thread_id ) {
            $this->populate( $thread_id, $order, $args );
        }
    }

    public function populate( $thread_id = 0, $order = 'ASC', $args = array() ) {

        if ( 'ASC' !== $order && 'DESC' !== $order ) {
            $order = 'ASC';
        }

        // Merge $args with our defaults.
        $r = wp_parse_args( $args, array(
            'user_id'           => gp_loggedin_user_id(),
            'update_meta_cache' => true
        ) );

        $this->messages_order = $order;
        $this->thread_id      = (int) $thread_id;

        // Get messages for thread.
        $this->messages = self::get_messages( $this->thread_id );

        if ( empty( $this->messages ) || is_wp_error( $this->messages ) ) {
            return false;
        }

        // Flip if order is DESC.
        if ( 'DESC' === $order ) {
            $this->messages = array_reverse( $this->messages );
        }

        $last_message_index         = count( $this->messages ) - 1;
        $this->last_message_id      = $this->messages[ $last_message_index ]->id;
        $this->last_message_date    = $this->messages[ $last_message_index ]->date_sent;
        $this->last_sender_id       = $this->messages[ $last_message_index ]->sender_id;
        $this->last_message_subject = $this->messages[ $last_message_index ]->subject;
        $this->last_message_content = $this->messages[ $last_message_index ]->message;

        foreach ( (array) $this->messages as $key => $message ) {
            $this->sender_ids[ $message->sender_id ] = $message->sender_id;
        }

        // Fetch the recipients.
        $this->recipients = $this->get_recipients();

        // Get the unread count for the logged in user.
        if ( isset( $this->recipients[ $r['user_id'] ] ) ) {
            $this->unread_count = $this->recipients[ $r['user_id'] ]->unread_count;
        }

        // Grab all message meta.
        if ( true === (bool) $r['update_meta_cache'] ) {
            //gp_messages_update_meta_cache( wp_list_pluck( $this->messages, 'id' ) );
        }

    }

    public function mark_read() {
        GP_Messages_Thread::mark_as_read( $this->thread_id );
    }

    public function mark_unread() {
        GP_Messages_Thread::mark_as_unread( $this->thread_id );
    }

    public function get_recipients( $thread_id = 0 ) {
        global $wpdb;

        if ( empty( $thread_id ) ) {
            $thread_id = $this->thread_id;
        }

        $thread_id = (int) $thread_id;

        $recipients = wp_cache_get( 'thread_recipients_' . $thread_id, 'gp_messages' );
        if ( false === $recipients ) {
            $gp = gampress();

            $recipients = array();
            $sql        = $wpdb->prepare( "SELECT * FROM {$gp->messages->table_name_recipients} WHERE thread_id = %d", $thread_id );
            $results    = $wpdb->get_results( $sql );

            foreach ( (array) $results as $recipient ) {
                $recipients[ $recipient->user_id ] = $recipient;
            }

            wp_cache_set( 'thread_recipients_' . $thread_id, $recipients, 'gp_messages' );
        }

        // Cast all items from the messages DB table as integers.
        foreach ( (array) $recipients as $key => $data ) {
            $recipients[ $key ] = (object) array_map( 'intval', (array) $data );
        }

        return $recipients;
    }

    /** Static Functions ******************************************************/

    public static function get_messages( $thread_id = 0 ) {
        $thread_id = (int) $thread_id;
        $messages  = wp_cache_get( $thread_id, 'gp_messages_threads' );

        if ( false === $messages ) {
            global $wpdb;

            $gp = gampress();

            // Always sort by ASC by default.
            $messages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$gp->messages->table_name_messages} WHERE thread_id = %d ORDER BY date_sent ASC", $thread_id ) );

            wp_cache_set( $thread_id, (array) $messages, 'gp_messages_threads' );
        }

        // Integer casting.
        foreach ( $messages as $key => $data ) {
            $messages[ $key ]->id        = (int) $messages[ $key ]->id;
            $messages[ $key ]->thread_id = (int) $messages[ $key ]->thread_id;
            $messages[ $key ]->sender_id = (int) $messages[ $key ]->sender_id;
        }

        return $messages;
    }

    public static function get_recipients_for_thread( $thread_id = 0 ) {
        $thread = new self( false );
        return $thread->get_recipients( $thread_id );
    }

    public static function delete( $thread_id = 0, $user_id = 0 ) {
        global $wpdb;

        $thread_id = (int) $thread_id;
        $user_id = (int) $user_id;

        if ( empty( $user_id ) ) {
            $user_id = gp_loggedin_user_id();
        }

        $gp = gampress();

        // Mark messages as deleted
        $wpdb->query( $wpdb->prepare( "UPDATE {$gp->messages->table_name_recipients} SET is_deleted = 1 WHERE thread_id = %d AND user_id = %d", $thread_id, $user_id ) );

        // Get the message ids in order to pass to the action.
        $message_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$gp->messages->table_name_messages} WHERE thread_id = %d", $thread_id ) );

        // Check to see if any more recipients remain for this message.
        $recipients = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$gp->messages->table_name_recipients} WHERE thread_id = %d AND is_deleted = 0", $thread_id ) );

        // No more recipients so delete all messages associated with the thread.
        if ( empty( $recipients ) ) {

            // Delete all the messages.
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$gp->messages->table_name_messages} WHERE thread_id = %d", $thread_id ) );

            // Do something for each message ID.
            foreach ( $message_ids as $message_id ) {
                // Delete message meta.
                gp_messages_delete_meta( $message_id );
            }

            // Delete all the recipients.
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$gp->messages->table_name_recipients} WHERE thread_id = %d", $thread_id ) );
        }

        return true;
    }

    public static function get_current_threads_for_user( $args = array() ) {
        global $wpdb;

        $r = gp_parse_args( $args, array(
            'user_id'      => false,
            'box'          => 'inbox',
            'type'         => 'all',
            'limit'        => null,
            'page'         => null,
            'search_terms' => '',
            'meta_query'   => array()
        ) );

        $pag_sql = $type_sql = $search_sql = $user_id_sql = $sender_sql = '';
        $meta_query_sql = array(
            'join'  => '',
            'where' => ''
        );

        if ( $r['limit'] && $r['page'] ) {
            $pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['limit'] ), intval( $r['limit'] ) );
        }

        if ( $r['type'] == 'unread' ) {
            $type_sql = " AND r.unread_count != 0 ";
        } elseif ( $r['type'] == 'read' ) {
            $type_sql = " AND r.unread_count = 0 ";
        }

        if ( ! empty( $r['search_terms'] ) ) {
            $search_terms_like = '%' . gp_esc_like( $r['search_terms'] ) . '%';
            $search_sql        = $wpdb->prepare( "AND ( subject LIKE %s OR message LIKE %s )", $search_terms_like, $search_terms_like );
        }

        $r['user_id'] = (int) $r['user_id'];

        // Default deleted SQL.
        $deleted_sql = 'r.is_deleted = 0';

        switch ( $r['box'] ) {
            case 'sentbox' :
                $user_id_sql = 'AND ' . $wpdb->prepare( 'm.sender_id = %d', $r['user_id'] );
                $sender_sql  = 'AND m.sender_id = r.user_id';
                break;

            case 'inbox' :
                $user_id_sql = 'AND ' . $wpdb->prepare( 'r.user_id = %d', $r['user_id'] );
                $sender_sql  = 'AND r.sender_only = 0';
                break;

            default :
                // Omit user-deleted threads from all other custom message boxes.
                $deleted_sql = $wpdb->prepare( '( r.user_id = %d AND r.is_deleted = 0 )', $r['user_id'] );
                break;
        }

        // Process meta query into SQL.
        $meta_query = self::get_meta_query_sql( $r['meta_query'] );
        if ( ! empty( $meta_query['join'] ) ) {
            $meta_query_sql['join'] = $meta_query['join'];
        }
        if ( ! empty( $meta_query['where'] ) ) {
            $meta_query_sql['where'] = $meta_query['where'];
        }

        $gp = gampress();

        // Set up SQL array.
        $sql = array();
        $sql['select'] = 'SELECT m.thread_id, MAX(m.date_sent) AS date_sent';
        $sql['from']   = "FROM {$gp->messages->table_name_recipients} r INNER JOIN {$gp->messages->table_name_messages} m ON m.thread_id = r.thread_id {$meta_query_sql['join']}";
        $sql['where']  = "WHERE {$deleted_sql} {$user_id_sql} {$sender_sql} {$type_sql} {$search_sql} {$meta_query_sql['where']}";
        $sql['misc']   = "GROUP BY m.thread_id ORDER BY date_sent DESC {$pag_sql}";

        // Get thread IDs.
        $thread_ids = $wpdb->get_results( implode( ' ', $sql ) );
        if ( empty( $thread_ids ) ) {
            return false;
        }

        // Adjust $sql to work for thread total.
        $sql['select'] = 'SELECT COUNT( DISTINCT m.thread_id )';
        unset( $sql['misc'] );
        $total_threads = $wpdb->get_var( implode( ' ', $sql ) );

        // Sort threads by date_sent.
        foreach( (array) $thread_ids as $thread ) {
            $sorted_threads[ $thread->thread_id ] = strtotime( $thread->date_sent );
        }

        arsort( $sorted_threads );

        $threads = array();
        foreach ( (array) $sorted_threads as $thread_id => $date_sent ) {
            $threads[] = new GP_Messages_Thread( $thread_id, 'ASC', array(
                'update_meta_cache' => false
            ) );
        }

        return array(
            'threads' => &$threads,
            'total'   => (int) $total_threads
        );
    }

    public static function get_meta_query_sql( $meta_query = array() ) {
        global $wpdb;

        $sql_array = array(
            'join'  => '',
            'where' => '',
        );

        if ( ! empty( $meta_query ) ) {
            $meta_query = new WP_Meta_Query( $meta_query );

            // WP_Meta_Query expects the table name at
            // $wpdb->messagemeta.
            $wpdb->messagemeta = gampress()->messages->table_name_meta;

            return $meta_query->get_sql( 'message', 'm', 'id' );
        }

        return $sql_array;
    }

    public static function mark_as_read( $thread_id = 0 ) {
        global $wpdb;

        $gp     = gampress();
        $retval = $wpdb->query( $wpdb->prepare( "UPDATE {$gp->messages->table_name_recipients} SET unread_count = 0 WHERE user_id = %d AND thread_id = %d", gp_loggedin_user_id(), $thread_id ) );

        wp_cache_delete( 'thread_recipients_' . $thread_id, 'gp_messages' );
        wp_cache_delete( gp_loggedin_user_id(), 'gp_messages_unread_count' );

        return $retval;
    }

    public static function mark_as_unread( $thread_id = 0 ) {
        global $wpdb;

        $gp     = gampress();
        $retval = $wpdb->query( $wpdb->prepare( "UPDATE {$gp->messages->table_name_recipients} SET unread_count = 1 WHERE user_id = %d AND thread_id = %d", gp_loggedin_user_id(), $thread_id ) );

        wp_cache_delete( 'thread_recipients_' . $thread_id, 'gp_messages' );
        wp_cache_delete( gp_loggedin_user_id(), 'gp_messages_unread_count' );

        return $retval;
    }

    public static function get_total_threads_for_user( $user_id, $box = 'inbox', $type = 'all' ) {
        global $wpdb;

        $exclude_sender = $type_sql = '';
        if ( $box !== 'sentbox' ) {
            $exclude_sender = 'AND sender_only != 1';
        }

        if ( $type === 'unread' ) {
            $type_sql = 'AND unread_count != 0';
        } elseif ( $type === 'read' ) {
            $type_sql = 'AND unread_count = 0';
        }

        $gp = gampress();

        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(thread_id) FROM {$gp->messages->table_name_recipients} WHERE user_id = %d AND is_deleted = 0 {$exclude_sender} {$type_sql}", $user_id ) );
    }

    public static function user_is_sender( $thread_id ) {
        global $wpdb;

        $gp = gampress();

        $sender_ids = $wpdb->get_col( $wpdb->prepare( "SELECT sender_id FROM {$gp->messages->table_name_messages} WHERE thread_id = %d", $thread_id ) );

        if ( empty( $sender_ids ) ) {
            return false;
        }

        return in_array( gp_loggedin_user_id(), $sender_ids );
    }

    public static function get_last_sender( $thread_id ) {
        global $wpdb;

        $gp = gampress();

        if ( ! $sender_id = $wpdb->get_var( $wpdb->prepare( "SELECT sender_id FROM {$gp->messages->table_name_messages} WHERE thread_id = %d GROUP BY sender_id ORDER BY date_sent LIMIT 1", $thread_id ) ) ) {
            return false;
        }

        return gp_core_get_userlink( $sender_id, true );
    }

    public static function get_inbox_count( $user_id = 0 ) {
        global $wpdb;

        if ( empty( $user_id ) ) {
            $user_id = gp_loggedin_user_id();
        }

        $unread_count = wp_cache_get( $user_id, 'gp_messages_unread_count' );

        if ( false === $unread_count ) {
            $gp = gampress();

            $unread_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(unread_count) FROM {$gp->messages->table_name_recipients} WHERE user_id = %d AND is_deleted = 0 AND sender_only = 0", $user_id ) );

            wp_cache_set( $user_id, $unread_count, 'gp_messages_unread_count' );
        }

        return (int) $unread_count;
    }

    public static function check_access( $thread_id, $user_id = 0 ) {

        if ( empty( $user_id ) ) {
            $user_id = gp_loggedin_user_id();
        }

        $recipients = self::get_recipients_for_thread( $thread_id );

        if ( isset( $recipients[ $user_id ] ) && 0 == $recipients[ $user_id ]->is_deleted ) {
            return $recipients[ $user_id ]->id;
        } else {
            return null;
        }
    }

    public static function is_valid( $thread_id = 0 ) {

        // Bail if no thread ID is passed.
        if ( empty( $thread_id ) ) {
            return false;
        }

        $thread = self::get_messages( $thread_id );

        if ( ! empty( $thread ) ) {
            return $thread_id;
        } else {
            return null;
        }
    }

    public static function get_recipient_links( $recipients ) {

        if ( count( $recipients ) >= 5 ) {
            return sprintf( __( '%s Recipients', 'gampress' ), number_format_i18n( count( $recipients ) ) );
        }

        $recipient_links = array();

        foreach ( (array) $recipients as $recipient ) {
            $recipient_link = gp_core_get_userlink( $recipient->user_id );

            if ( empty( $recipient_link ) ) {
                $recipient_link = __( 'Deleted User', 'gampress' );
            }

            $recipient_links[] = $recipient_link;
        }

        return implode( ', ', (array) $recipient_links );
    }

    public static function update_tables() {
        global $wpdb;

        $gp_prefix = gp_core_get_table_prefix();
        $errors    = false;
        $threads   = $wpdb->get_results( "SELECT * FROM {$gp_prefix}gp_messages_threads" );

        // Nothing to update, just return true to remove the table.
        if ( empty( $threads ) ) {
            return true;
        }

        $gp = gampress();

        foreach( (array) $threads as $thread ) {
            $message_ids = maybe_unserialize( $thread->message_ids );

            if ( ! empty( $message_ids ) ) {
                $message_ids = implode( ',', $message_ids );

                // Add the thread_id to the messages table.
                if ( ! $wpdb->query( $wpdb->prepare( "UPDATE {$gp->messages->table_name_messages} SET thread_id = %d WHERE id IN ({$message_ids})", $thread->id ) ) ) {
                    $errors = true;
                }
            }
        }

        return (bool) ! $errors;
    }
}