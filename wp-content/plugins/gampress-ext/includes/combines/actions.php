<?php
/**
 * GamPress Combine Action Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage CoreAdministration
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_register_combine_taxonomies() {
    register_taxonomy( 'combine', 'combine', array( 'public' => false, 'hierarchical' => true ) );
}
add_action( 'gp_register_taxonomies', 'gp_register_combine_taxonomies' );