<?php

/* TODO: Add code here */

defined( 'ABSPATH' ) || exit;

class GP_Services_Service {

    public $id;

    public $user_id;

    public $name;

    public $price;

    public $description;

    public $unit;

    public $type;

    public $status;

    public $date_created;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        // Get gampress.
        $gp    = gampress();

        $service = $wpdb->get_row( $wpdb->prepare( "SELECT s.* FROM {$gp->services->table_name} s WHERE s.id = %d", $this->id ) );

        // No group found so set the ID and bail.
        if ( empty( $service ) || is_wp_error( $service ) ) {
            $this->id = 0;
            return;
        }

        // Group found so setup the object variables.
        $this->id           = (int) $service->id;
        $this->user_id      = (int) $service->user_id;
        $this->price        = $service->price;
        $this->type         = $service->type;
        $this->unit         = $service->unit;
        $this->name         = stripslashes( $service->name );
        $this->description  = stripslashes( $service->description );
        $this->status       = $service->status;
        $this->date_created = $service->date_created;
    }

    public function save() {
        global $wpdb;

        $gp = gampress();

        if ( empty( $this->price ) || empty( $this->name ) || empty( $this->description ) ) {
            return false;
        }

        if ( !empty( $this->id ) ) {
            $sql = $wpdb->prepare(
                "UPDATE {$gp->services->table_name} SET
					user_id = %d,
					price = %s,
					type = %s,
					unit = %s,
					name = %s,
					description = %s,
					status = %d,
					date_created = %s
				WHERE
					id = %d
				",
                $this->user_id,
                $this->price,
                $this->type,
                $this->unit,
                $this->name,
                $this->description,
                $this->status,
                $this->date_created,
                $this->id
            );
        } else {
            $sql = $wpdb->prepare(
                "INSERT INTO {$gp->services->table_name} (
					user_id,
					price,
					type,
					unit,
					name,
					description,
					status,
					date_created
				) VALUES (
					%d, %s, %s, %s, %s, %s, %d, %s
				)",
                $this->user_id,
                $this->price,
                $this->type,
                $this->unit,
                $this->name,
                $this->description,
                $this->status,
                $this->date_created
            );
        }

        if ( false === $wpdb->query($sql) )
            return false;

        if ( empty( $this->id ) )
            $this->id = $wpdb->insert_id;

        wp_cache_delete( $this->id, 'gp_service' );

        return true;
    }

    public function delete() {
        global $wpdb;

        $gp = gampress();

        // Finally remove the group entry from the DB.
        if ( !$wpdb->query( $wpdb->prepare( "DELETE FROM {$gp->services->table_name} WHERE id = %d", $this->id ) ) )
            return false;

        return true;
    }

    protected static function convert_orderby_to_order_by_term( $orderby )
    {
        $order_by_term = '';

        switch ($orderby) {
            case 'date_created' :
            default :
                $order_by_term = 's.date_created';
                break;
        }

        return $order_by_term;
    }

    public static function get( $args = '' ) {
        global $wpdb;

        $gp = gampress();

        $defaults = array(
            'user_id'               => 0,
            'orderby'               => 'date_created',
            'page'                  => 1,
            'per_page'              => 20
        );
        $r = wp_parse_args( $args, $defaults );

        $sql        = array();
        $tables     = array();
        $clause     = array();

        $sql['select'] = "SELECT DISTINCT s.*";
        $tables[]      = " FROM {$gp->services->table_name} s";

        if ( !empty( $r['user_id'] ) )
            $clause[] = $wpdb->prepare( " s.user_id = %d", $r['user_id'] );

        if ( !empty( $r['status'] ) )
            $clause[] = $wpdb->prepare( " s.status = %d", $r['status'] );

        $sql['from']  = join( ' ', (array) $tables );
        $sql['where'] = join( ' AND ', (array) $clause );
        if ( !empty( $sql['where'] ) )
            $sql['where'] = 'WHERE ' . $sql['where'];

        /** Order/orderby ********************************************/

        $order   = $r['order'];
        $orderby = $r['orderby'];

        // Sanitize 'order'
        $order = gp_esc_sql_order( $order );

        // Convert 'orderby' into the proper ORDER BY term
        $orderby = self::convert_orderby_to_order_by_term( $orderby );
        $sql[] = "ORDER BY {$orderby} {$order}";

        if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) )
            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page'] ), intval( $r['per_page'] ) );

        // Get paginated results
        $paged_services_sql = join( ' ', (array) $sql );
        $paged_services     = $wpdb->get_results( $paged_services_sql );

        $sql['select'] = "SELECT COUNT(DISTINCT s.id) ";
        $sql['pagination'] = '';
        $total_services_sql = join( ' ', (array) $sql );
        $total_services     = $wpdb->get_var( $total_services_sql );

        unset( $sql, $paged_services_sql,  $total_services_sql );

        return array( 'items' => $paged_services, 'total' => $total_services );
    }
}