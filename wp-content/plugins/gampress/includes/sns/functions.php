<?php
/**
 * GamPress Admin Sns Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage CoreAdministration
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_sns_get_sns_user_id() {
    $key_user_id = 'sns_user_id';
    return get_user_meta( gp_loggedin_user_id(), $key_user_id, true );
}