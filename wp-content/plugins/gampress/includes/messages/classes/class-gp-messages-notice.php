<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/11/23
 * Time: 11:49
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class GP_Messages_Notice {

    public $id = null;

    public $subject;

    public $message;

    public $date_sent;

    public $is_active;

    public function __construct( $id = null ) {
        if ( $id ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp = gampress();

        $notice = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$gp->messages->table_name_notices} WHERE id = %d", $this->id ) );

        if ( $notice ) {
            $this->subject   = $notice->subject;
            $this->message   = $notice->message;
            $this->date_sent = $notice->date_sent;
            $this->is_active = (int) $notice->is_active;
        }
    }

    public function save() {
        global $wpdb;

        $gp = gampress();

        $this->subject = apply_filters( 'messages_notice_subject_before_save', $this->subject, $this->id );
        $this->message = apply_filters( 'messages_notice_message_before_save', $this->message, $this->id );

        do_action_ref_array( 'messages_notice_before_save', array( &$this ) );

        if ( empty( $this->id ) ) {
            $sql = $wpdb->prepare( "INSERT INTO {$gp->messages->table_name_notices} (subject, message, date_sent, is_active) VALUES (%s, %s, %s, %d)", $this->subject, $this->message, $this->date_sent, $this->is_active );
        } else {
            $sql = $wpdb->prepare( "UPDATE {$gp->messages->table_name_notices} SET subject = %s, message = %s, is_active = %d WHERE id = %d", $this->subject, $this->message, $this->is_active, $this->id );
        }

        if ( ! $wpdb->query( $sql ) ) {
            return false;
        }

        if ( ! $id = $this->id ) {
            $id = $wpdb->insert_id;
        }

        // Now deactivate all notices apart from the new one.
        $wpdb->query( $wpdb->prepare( "UPDATE {$gp->messages->table_name_notices} SET is_active = 0 WHERE id != %d", $id ) );

        //gp_update_user_last_activity( gp_loggedin_user_id(), gp_core_current_time() );

        do_action_ref_array( 'messages_notice_after_save', array( &$this ) );

        return true;
    }

    public function activate() {
        $this->is_active = 1;
        return (bool) $this->save();
    }

    public function deactivate() {
        $this->is_active = 0;
        return (bool) $this->save();
    }

    public function delete() {
        global $wpdb;

        do_action( 'messages_notice_before_delete', $this );

        $gp  = gampress();
        $sql = $wpdb->prepare( "DELETE FROM {$gp->messages->table_name_notices} WHERE id = %d", $this->id );

        if ( ! $wpdb->query( $sql ) ) {
            return false;
        }

        do_action( 'messages_notice_after_delete', $this );

        return true;
    }

    /** Static Methods ********************************************************/

    public static function get_notices( $args = array() ) {
        global $wpdb;

        $r = wp_parse_args( $args, array(
            'pag_num'  => 20, // Number of notices per page.
            'pag_page' => 1   // Page number.
        ) );

        $limit_sql = '';
        if ( (int) $r['pag_num'] >= 0 ) {
            $limit_sql = $wpdb->prepare( "LIMIT %d, %d", (int) ( ( $r['pag_page'] - 1 ) * $r['pag_num'] ), (int) $r['pag_num'] );
        }

        $gp = gampress();

        $notices = $wpdb->get_results( "SELECT * FROM {$gp->messages->table_name_notices} ORDER BY date_sent DESC {$limit_sql}" );

        // Integer casting.
        foreach ( (array) $notices as $key => $data ) {
            $notices[ $key ]->id        = (int) $notices[ $key ]->id;
            $notices[ $key ]->is_active = (int) $notices[ $key ]->is_active;
        }

        return apply_filters( 'messages_notice_get_notices', $notices, $r );
    }

    public static function get_total_notice_count() {
        global $wpdb;

        $gp = gampress();

        $notice_count = $wpdb->get_var( "SELECT COUNT(id) FROM {$gp->messages->table_name_notices}" );

        return (int) apply_filters( 'messages_notice_get_total_notice_count', $notice_count );
    }

    public static function get_active() {
        $notice = wp_cache_get( 'active_notice', 'gp_messages' );

        if ( false === $notice ) {
            global $wpdb;

            $gp = gampress();

            $notice_id = $wpdb->get_var( "SELECT id FROM {$gp->messages->table_name_notices} WHERE is_active = 1" );
            $notice    = new gp_Messages_Notice( $notice_id );

            wp_cache_set( 'active_notice', $notice, 'gp_messages' );
        }
        
        return apply_filters( 'messages_notice_get_active', $notice );
    }
}
