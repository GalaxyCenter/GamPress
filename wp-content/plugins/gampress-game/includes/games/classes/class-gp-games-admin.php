<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 11:04
 */

class GP_Games_Admin {

    public $admin_url = '';
    public $admin_dir = '';

    public function __construct() {
        $this->setup_globals();
        $this->includes();
        $this->setup_actions();
    }

    private function setup_globals() {
        // Paths and URLs
        $this->admin_dir  = trailingslashit( GP_EXT_PLUGIN_DIR  . 'includes/games/admin' ); // Admin path.dmin url.
    }

    private function includes() {

    }

    private function setup_actions() {

    }

    public static function register_games_admin() {
        if ( ! is_admin() ) {
            return;
        }

        $gp = gampress();

        if ( empty( $gp->games->admin ) ) {
            $gp->games->admin = new self;
        }

        return $gp->games->admin;
    }


}