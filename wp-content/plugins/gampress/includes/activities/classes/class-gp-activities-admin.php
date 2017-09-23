<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 11:04
 */

class GP_Activities_Admin {

    public $admin_url = '';
    public $admin_dir = '';

    public function __construct() {
        $this->setup_globals();
        $this->includes();
        $this->setup_actions();
    }

    private function setup_globals() {
        // Paths and URLs
        $this->admin_dir  = trailingslashit( GP_PLUGIN_DIR  . 'includes/activities/admin' ); // Admin path.dmin url.
    }

    private function includes() {

    }

    private function setup_actions() {

    }

    public static function register_activities_admin() {
        if ( ! is_admin() ) {
            return;
        }

        $gp = gampress();

        if ( empty( $gp->activities->admin ) ) {
            $gp->activities->admin = new self;
        }

        return $gp->activities->admin;
    }


}