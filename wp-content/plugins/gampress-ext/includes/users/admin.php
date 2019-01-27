<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/26
 * Time: 16:50
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load the GP Orders admin.
add_action( 'gp_init', array( 'GP_Users_Admin', 'register_users_admin' ) );