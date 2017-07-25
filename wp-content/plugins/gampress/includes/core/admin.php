<?php
/**
 * Main GamPress Admin Class.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage CoreAdministration
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! gampress()->do_autoload ) {
    require dirname( __FILE__ ) . '/classes/class-gp-admin.php';
}

/**
 * Setup GamPress Admin.
 *
 *
 */
function gp_admin() {
    gampress()->admin = new GP_Admin();
    return;
}