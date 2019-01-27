<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/26
 * Time: 16:51
 */

class GP_Users_Admin {

    public $admin_url = '';
    public $admin_dir = '';

    public function __construct() {
        $this->setup_globals();
        $this->includes();
        $this->setup_actions();
    }

    private function setup_globals() {
        // Paths and URLs
        $this->admin_dir  = trailingslashit( GP_EXT_PLUGIN_DIR  . 'includes/users/admin' ); // Admin path.dmin url.
    }

    private function includes() {
        require( $this->admin_dir . 'actions.php'  );
        require( $this->admin_dir . 'widgets.php'  );
    }

    private function setup_actions() {

    }

    public static function register_users_admin() {
        if ( ! is_admin() ) {
            return;
        }

        $gp = gampress();

        if ( empty( $gp->users->admin ) ) {
            $gp->users->admin = new self;
        }

        return $gp->users->admin;
    }


}