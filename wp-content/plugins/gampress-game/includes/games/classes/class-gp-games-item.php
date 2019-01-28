<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/30
 * Time: 9:06
 */

class GP_Game_Item extends GP_Entity {

    var $id;

    /** @var  所属活动 */
    var $activity_id;

    /** @var  名称 */
    var $name;

    /** @var  数量 */
    var $count;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

    public function populate() {
        global $wpdb;

        $gp    = gampress();
        $group = $wpdb->get_row( $wpdb->prepare( "SELECT g.* FROM {$gp->games->table_name_items} g WHERE g.id = %d LIMIT 1", $this->id ) );

        if ( empty( $group ) || is_wp_error( $group ) ) {
            $this->id = 0;
            return false;
        }

        $this->id                   = (int) $group->id;
        $this->activity_id          = (int) $group->activity_id;
        $this->name                 = $group->name;
        $this->owner_id             = (int) $group->owner_id;
        $this->date_created         = $group->date_created;
    }

    public function save() {
        $data = array(
            'name'          => $this->name,
            'activity_id'   => $this->activity_id,
            'count'         => $this->count
        );
        $data_format = array( '%s', '%d', '%d' );

        $gp = gampress();
        return $this->_save( $gp->games->table_name_items, $data, $data_format );
    }
}