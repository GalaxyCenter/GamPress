<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/15
 * Time: 21:06
 */

defined( 'ABSPATH' ) || exit;

class GP_Messages_Message {

    public $id;

    public $thread_id;

    public $sender_id;

    public $content;

    public $post_time;

    public $recipients = false;

    public function __construct( $id = null ) {
        $this->post_time = gp_core_current_time();
        $this->sender_id = gp_loggedin_user_id();

        if ( ! empty( $id ) ) {
            $this->populate( $id );
        }
    }

    public function populate( $id ) {
        global $wpdb;

        $gp    = gampress();
        if ( $message = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$gp->messages->table_name} WHERE id = %d", $id ) ) ) {
            $this->id        = (int) $message->id;
            $this->thread_id = (int) $message->thread_id;
            $this->sender_id = (int) $message->sender_id;
            $this->content   = $message->content;
            $this->post_time = $message->post_time;
        }
    }

    public function send() {
        global $wpdb;

        $gp    = gampress();
        if ( empty( $this->recipients ) ) {
            return false;
        }

        $new_thread = false;

        // If we have no thread_id then this is the first message of a new thread.
        if ( empty( $this->thread_id ) ) {
            $this->thread_id = (int) $wpdb->get_var( "SELECT MAX(thread_id) FROM {$gp->messages->table_name_messages}" ) + 1;
            $new_thread      = true;
        }

        // First insert the message into the messages table.
        if ( ! $wpdb->query( $wpdb->prepare( "INSERT INTO {$gp->messages->table_name_messages} ( thread_id, sender_id, subject, message, date_sent ) VALUES ( %d, %d, %s, %s, %s )", $this->thread_id, $this->sender_id, $this->subject, $this->message, $this->date_sent ) ) ) {
            return false;
        }

        $this->id = $wpdb->insert_id;

        $recipient_ids = array();

        if ( $new_thread ) {
            // Add an recipient entry for all recipients.
            foreach ( (array) $this->recipients as $recipient ) {
                $wpdb->query( $wpdb->prepare( "INSERT INTO {$gp->messages->table_name_recipients} ( user_id, thread_id, unread_count ) VALUES ( %d, %d, 1 )", $recipient->user_id, $this->thread_id ) );
                $recipient_ids[] = $recipient->user_id;
            }

            // Add a sender recipient entry if the sender is not in the list of recipients.
            if ( ! in_array( $this->sender_id, $recipient_ids ) ) {
                $wpdb->query( $wpdb->prepare( "INSERT INTO {$gp->messages->table_name_recipients} ( user_id, thread_id, sender_only ) VALUES ( %d, %d, 1 )", $this->sender_id, $this->thread_id ) );
            }
        } else {
            // Update the unread count for all recipients.
            $wpdb->query( $wpdb->prepare( "UPDATE {$gp->messages->table_name_recipients} SET unread_count = unread_count + 1, sender_only = 0, is_deleted = 0 WHERE thread_id = %d AND user_id != %d", $this->thread_id, $this->sender_id ) );
        }

        return $this->id;
    }

    public function get_recipients() {
        global $wpdb;

        $gp    = gampress();
        return $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM {$gp->messages->table_name_recipients} WHERE thread_id = %d", $this->thread_id ) );
    }

    public static function get_recipient_ids( $recipient_usernames ) {
        $recipient_ids = false;

        if ( ! $recipient_usernames ) {
            return $recipient_ids;
        }

        if ( is_array( $recipient_usernames ) ) {
            $rec_un_count = count( $recipient_usernames );

            for ( $i = 0, $count = $rec_un_count; $i < $count; ++ $i ) {
                if ( $rid = gp_core_get_userid( trim( $recipient_usernames[ $i ] ) ) ) {
                    $recipient_ids[] = $rid;
                }
            }
        }

        return $recipient_ids;
    }

    public static function get_last_sent_for_user( $thread_id ) {
        global $wpdb;

        $gp = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->messages->table_name_messages} WHERE sender_id = %d AND thread_id = %d ORDER BY date_sent DESC LIMIT 1", bp_loggedin_user_id(), $thread_id ) );

        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function is_user_sender( $user_id, $message_id ) {
        global $wpdb;

        $gp = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->messages->table_name_messages} WHERE sender_id = %d AND id = %d", $user_id, $message_id ) );

        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function get_message_sender( $message_id ) {
        global $wpdb;

        $gp = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT sender_id FROM {$gp->messages->table_name_messages} WHERE id = %d", $message_id ) );

        return is_numeric( $query ) ? (int) $query : $query;
    }
}