<?php
/**
 * GamPress Member Action Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @sugpackage Members
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

function gp_core_signup_email() {
    if ( ! gp_is_current_component( 'signup' ) || ! gp_is_current_action( 'email' ) )
        return;
    
    $email = isset( $_SERVER[QUERY_STRING] ) ? $_SERVER[QUERY_STRING] : '';
    
    if ( empty( $email ) ) {
        ajax_die( 1, __( 'request email field', 'gampress' ), false );
    } else {
        preg_match('/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i', $email, $match );
        
        if ( empty( $match ) ) {
            ajax_die( 2, 'invalida email', false );
        } else {
            gp_core_signup_send_validation_email( $email );
            
            $data = array( 'link' => 'mail@' . $match[2] );
            
            ajax_die( 0, '', $data );
        }
        
    }

}
add_action( 'gp_actions', 'gp_core_signup_email' );
