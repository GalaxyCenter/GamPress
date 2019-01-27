<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/2
 * Time: 15:42
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Video object.
 *
 * @since 1.6.0
 */
class GP_Books_log {

    var $id;

    var $user_id;

    var $book_id;

    var $chapter_id;

    var $create_time;

    var $from;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $log = $wpdb->get_row( $wpdb->prepare( "SELECT b.* FROM {$gp->books->table_name_logs} l WHERE l.id = %d", $this->id ) );

        if ( empty( $log ) || is_wp_error( $log ) ) {
            $this->id = 0;
            return false;
        }

        $this->id               = (int) $log->id;
        $this->user_id          = (int) $log->user_id;
        $this->book_id          = (int) $log->book_id;
        $this->chapter_id       = (int) $log->chapter_id;
        $this->create_time      = $log->create_time;
        $this->from             = $log->from;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->books->table_name_logs} SET
                            user_id = %s, book_id = %s, chapter_id = %d,
                            create_time = %s, form = %s
                        WHERE
                            id = %d
                        ",
                $this->user_id, $this->book_id, $this->chapter_id,
                $this->create_time, $this->from,
                $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->books->table_name_logs} (
                        user_id, book_id, chapter_id,
                        create_time, `from`
                    ) VALUES(%s, %d, %d,
                    %s, %s)",
                $this->user_id, $this->book_id, $this->chapter_id,
                $this->create_time, $this->from
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    /*** static ***/

    /**
     * 300秒内是否存在记录
     * @param $user_id
     * @param $book_id
     * @param $chapter_id
     * @return null|string
     */
    public static function exists( $user_id, $book_id, $chapter_id ) {
        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->books->table_name_logs} WHERE user_id = %s and `book_id` = %d AND chapter_id = %d AND TIMESTAMPDIFF(SECOND, create_time, now()) < 600", $user_id, $book_id, $chapter_id ) );

        return $query;
    }

    public static function get_top( $mondate, $count ) {
        global $wpdb;

        $gp = gampress();

        return $wpdb->get_results( $wpdb->prepare( "SELECT book_id, COUNT(0) as counts FROM {$gp->books->table_name_logs} WHERE date_format(create_time, '%%Y%%m') = %s GROUP BY book_id ORDER BY counts DESC LIMIT %d", $mondate, $count ) );
    }
}