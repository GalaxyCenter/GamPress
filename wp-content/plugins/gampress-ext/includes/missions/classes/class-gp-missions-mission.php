<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 16:12
 */

defined( 'ABSPATH' ) || exit;

class GP_Missions_Mission {
    public $id;
    public $user_id;
    public $custom_name;
    public $phone;
    public $card;
    public $remark;
    public $status;
    public $gender;
    public $post_time;
    public $update_time;

    public function __construct($id = null, $args = array()) {
        if (!empty($id)) {
            $this->id = (int)$id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $mission = $wpdb->get_row( $wpdb->prepare( "SELECT m.* FROM {$gp->missions->table_name} o WHERE m.id = %d", $this->id ) );

        if ( empty( $mission ) || is_wp_error( $mission ) ) {
            $this->id = 0;
            return false;
        }

        $this->id               = (int) $mission->id;
        $this->user_id          = (int) $mission->user_id;
        $this->custom_name      = stripslashes( $mission->custom_name );
        $this->phone            = $mission->phone;
        $this->remark           = json_decode( $mission->remark );
        $this->status           = $mission->status;
        $this->card             = $mission->card;
        $this->post_time        = $mission->post_time;
        $this->update_time      = $mission->update_time;
        $this->gender           = $mission->gender;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();
        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->missions->table_name} SET
                            custom_name = %s, phone = %s, remark = %s,
                            status = %d, post_time = %s, gender = %d,
                            user_id = %d, card = %s
                        WHERE
                            id = %d
                        ",
                $this->custom_name, $this->phone, $this->remark,
                $this->status, $this->post_time, $this->gender, $this->user_id, $this->card,
                $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->missions->table_name} (
                        custom_name, phone, remark,
                        status,      post_time, gender, user_id, card
                    ) VALUES(%s, %s, %s, %d, %s, %d, %d, %s)",
                $this->custom_name, $this->phone, $this->remark,
                $this->status, $this->post_time, $this->gender, $this->user_id, $this->card
            );
        }

        if ( false === $wpdb->query( $sql ) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        return true;
    }

    public static function mission_exists( $id ) {
        if ( empty( $id ) )
            return false;

        global $wpdb;
        $gp    = gampress();

        $query = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$gp->missions->table_name} WHERE id = %d", (int)$id ) );
        return is_numeric( $query ) ? (int) $query : $query;
    }

    public static function get( $args = null ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array('user_id'    => 0,
            'missionby'     => 'post_time',
            'status'      => false,
            'mission'       => 'DESC',
            'page'        => 1,
            'per_page'    => 20 );


        $r = wp_parse_args( $args, $defaults );

        $sql        = array();
        $clause     = array();
        $tables     = array();

        $sql['select'] = "SELECT m.*";

        $tables[]   = " FROM {$gp->missions->table_name} o";

        if ( !empty( $r['user_id'] ) )
            $clause[] = $wpdb->prepare( " m.user_id = %d", $r['user_id'] );

        if ( !empty( $r['status'] ) )
            $clause[] = $wpdb->prepare( " m.status = %d", $r['status'] );

        $sql['from']    = join( ' ', $tables );
        $sql['where'] = join( ' AND ', (array) $clause );
        if ( !empty( $sql['where'] ) )
            $sql['where'] = 'WHERE ' . $sql['where'];

        /* Mission/missionby ********************************************/
        $mission   = $r['mission'];
        $missionby = $r['missionby'];

        $mission = gp_esc_sql_order( $mission );
        if ( 'rand()' === $missionby ) {
            $sql['missionby'] = "ORDER BY rand()";
        } else {
            $sql['missionby'] = "ORDER BY {$missionby} {$mission}";
        }

        if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) && $r['per_page'] != -1 )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page'] ), intval( $r['per_page'] ) );

        // Get paginated results
        $paged_missions_sql = join( ' ', (array) $sql );
        $paged_missions     = $wpdb->get_results( $paged_missions_sql );

        $sql['select'] = "SELECT COUNT(DISTINCT m.id) ";
        unset( $sql['pagination'] );

        $total_missions_sql = join( ' ', (array) $sql );
        $total_missions     = $wpdb->get_var( $total_missions_sql );

        unset( $sql );

        return array( 'items' => $paged_missions, 'total' => $total_missions );
    }
}