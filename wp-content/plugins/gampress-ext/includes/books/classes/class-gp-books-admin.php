<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/11
 * Time: 20:32
 */

class GP_Books_Admin {

    public $admin_url = '';
    public $admin_dir = '';

    public function __construct() {
        $this->setup_globals();
        $this->includes();
        $this->setup_actions();
    }

    private function setup_globals() {
        // Paths and URLs
        $this->admin_dir  = trailingslashit( GP_EXT_PLUGIN_DIR  . 'includes/books/admin' ); // Admin path.dmin url.
    }

    private function includes() {
        require( $this->admin_dir . 'actions.php'  );
        require( $this->admin_dir . 'functions.php'  );
    }

    private function setup_actions() {

    }

    public static function register_books_admin() {
        if ( ! is_admin() ) {
            return;
        }

        $gp = gampress();

        if ( empty( $gp->books->admin ) ) {
            $gp->books->admin = new self;
        }

        return $gp->books->admin;
    }


}