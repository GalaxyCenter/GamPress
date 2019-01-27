<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/11
 * Time: 20:32
 */

class GP_Orders_Admin {

    public $admin_url = '';
    public $admin_dir = '';

    public function __construct() {
        $this->setup_globals();
        $this->includes();
        $this->setup_actions();
    }

    private function setup_globals() {
        // Paths and URLs
        $this->admin_dir  = trailingslashit( GP_EXT_PLUGIN_DIR  . 'includes/orders/admin' ); // Admin path.dmin url.
    }

    private function includes() {
        require( $this->admin_dir . 'functions.php'  );
        require( $this->admin_dir . 'widgets.php'  );
    }

    private function setup_actions() {

    }

    public static function register_orders_admin() {
        if ( ! is_admin() ) {
            return;
        }

        $gp = gampress();

        if ( empty( $gp->orders->admin ) ) {
            $gp->orders->admin = new self;
        }

        return $gp->orders->admin;
    }


}