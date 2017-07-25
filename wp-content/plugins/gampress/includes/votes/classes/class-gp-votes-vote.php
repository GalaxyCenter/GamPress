<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/19
 * Time: 16:33
 */

defined( 'ABSPATH' ) || exit;

class GP_Votes_Vote {

    public static $VOTE = "vote";
    public static $LIKE = "like";
    public static $UNLIKE = "unlike";

    public $id;

    public $user_id;

    public $item_id;

    public $type;

    public $post_time;

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
            'type'              => $this->type,
            'post_time'         => $this->post_time
        );
        $data_format = array( '%d', '%d', '%s', '%s' );

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

    /** Protected Static Methods **********************************************/
    protected static function _insert( $data = array(), $data_format = array() ) {
        global $wpdb;
        return $wpdb->insert( gampress()->votes->table_name_votes, $data, $data_format );
    }

    protected static function _update( $data = array(), $where = array(), $data_format = array(), $where_format = array() ) {
        global $wpdb;
        return $wpdb->update( gampress()->votes->table_name_votes, $data, $where, $data_format, $where_format );
    }

    protected static function _delete( $where = array(), $where_format = array() ) {
        global $wpdb;
        return $wpdb->delete( gampress()->votes->table_name_votes, $where, $where_format );
    }

    public static function user_vote_count( $user_id, $item_id, $start_time = '', $end_time = '' ) {
        global $wpdb;
        $gp    = gampress();

        $sql = array(
            'select'     => "SELECT count(0)",
            'from'       => "{$gp->votes->table_name_votes} v",
            'where'      => ''
        );
        $where_conditions = array();

        if ( !empty($user_id) )
            $where_conditions[] = $wpdb->prepare( " v.user_id = %d", $user_id );

        if ( !empty($item_id) )
            $where_conditions[] = $wpdb->prepare( " v.item_id = %d", $item_id );

        if ( !empty($start_time) )
            $where_conditions[] = $wpdb->prepare( " v.post_time < %s", $start_time );

        if ( !empty($start_time) )
            $where_conditions[] = $wpdb->prepare( " v.end_time > %s", $end_time );

        $where = '';
        if ( ! empty( $where_conditions ) ) {
            $sql['where'] = implode( ' AND ', $where_conditions );
            $where = "WHERE {$sql['where']}";
        }

        if ( !empty( $sql['where'] ) )
            $sql['where'] = 'WHERE ' . $sql['where'];

        $query_sql = "{$sql['select']} FROM {$sql['from']} {$where}";

        $query = $wpdb->get_var( $query_sql );
        return (int) $query;
    }
}