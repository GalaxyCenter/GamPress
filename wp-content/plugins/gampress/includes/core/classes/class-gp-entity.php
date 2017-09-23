<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/30
 * Time: 9:17
 */

abstract class GP_Entity {

    protected function _save( $table, $data, $data_format ) {
        $retval = false;

        global $wpdb;
        // Update.
        if ( ! empty( $this->id ) ) {
            $result = $wpdb->update( $table, $data, array( 'id' => $this->id ), $data_format, array( '%d' ) );

            // Set the notification ID if successful.
            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $retval   = $this->id;
            }
            // Insert.
        } else {
            $result = $wpdb->insert( $table, $data, $data_format );

            // Set the notification ID if successful.
            if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
                $this->id = $wpdb->insert_id;
                $retval   = $wpdb->insert_id;
            }
        }
        // Return the result.
        return $retval;
    }
}