<?php
/**
 * Created by PhpStorm.
 * User: Texel
 * Date: 2017/2/20
 * Time: 10:37
 */

function services_setup_bp_nav() {
    bp_core_new_nav_item(
        array(
            'name'                => _x( 'Services', 'Services header menu', 'gampress-ext' ),
            'slug'                => 'services',
            'position'            => 20,
            'screen_function'     => 'services_screen_services',
            'default_subnav_slug' => 'services',
            'item_css_id'         => 12
        ), 'members' );

    $sub_nav = array();

    $sub_nav[] = array(
        'name'            => _x( 'View', 'View User Services', 'gampress-ext' ),
        'slug'            => 'services',
        'parent_url'      => trailingslashit( bp_displayed_user_domain() . 'services' ),
        'parent_slug'     => 'services',
        'screen_function' => 'services_screen_services_view',
        'position'        => 10,
        'user_has_access' => true
    );

    if ( gp_is_my_service() ) {
        $sub_nav[] = array(
            'name' => _x('Publish', 'Edit product', 'gampress-ext'),
            'slug' => 'edit',
            'parent_url' => trailingslashit(bp_displayed_user_domain() . 'services'),
            'parent_slug' => 'services',
            'screen_function' => 'services_screen_services_edit',
            'position' => 20,
            'user_has_access' => bp_core_can_edit_settings()
        );
    }
    foreach( (array) $sub_nav as $nav ) {
        bp_core_new_subnav_item( $nav, 'members' );
    }
}
add_action( 'bp_setup_nav', 'services_setup_bp_nav' );

function services_screen_services_view() {
    gp_core_load_template( 'members/single/home' );
}

function services_screen_services_edit() {
    if ( ! gp_is_my_service() && ! gp_current_user_can( 'gp_moderate' ) )
        return false;
    
    $error = false;
    
    if ( 'POST' == strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
        // Check the nonce.
		check_admin_referer( 'gp_service_edit', 'gp_service_edit' );
            
        $errors = false;

        $form_names = array( 
                'name'          => array( 'name' => 'Service name',   'error' => __( 'The service name can\'t be empty', 'gampress-ext' ) ),
                'price'         => array( 'name' => 'Service price',   'error' => __( 'The service price can\'t be empty', 'gampress-ext' ) ),
                'unit'          => array( 'name' => 'Service unit',    'error' => __( 'Please choose service unit', 'gampress-ext' ) ),
                'type'          => array( 'name' => 'Service type',   'error'  => __( 'Please choose service type', 'gampress-ext' ) ),
                'description'   => array( 'description' => 'Service description',   'error' => __( 'The service description can\'t be empty', 'gampress-ext' ) ),
				);
		$form_values = get_request_values( $form_names );
        if ( !empty( $form_values['error'] ) ) {
            tea_core_add_message( sprintf( $form_values['error'] ), 'error' );
            gp_core_redirect( bp_displayed_user_domain() . gp_get_service_slug() . $service_id );
        }

        $user_id = bp_loggedin_user_id();
        $service = gp_services_update_service( array(
                    'id'                    => $id,
                    'user_id'               => $user_id,
                    'name'                  => $form_values['values']['name'],
                    'price'                 => $form_values['values']['price'],
                    'unit'                  => $form_values['values']['unit'],
                    'type'                  => $form_values['values']['type'],
                    'description'           => $form_values['values']['description'],
                    'status'                => 0,
                    'date_created'          => gp_core_current_time()
            ) );

        // Set the feedback messages.
        if ( !empty( $errors ) ) {
            bp_core_add_message( __( 'There was a problem updating some of your profile information. Please try again.', 'gampress-ext' ), 'error' );
        } else {
            bp_core_add_message( __( 'Changes saved.', 'gampress-ext' ) );
        }

        // Redirect back to the edit screen to display the updates and message.
        gp_core_redirect( trailingslashit( bp_displayed_user_domain() . gp_get_service_slug() . '/' . $service->id ) );
    }

    gp_core_load_template( 'member/single/home' );
}

new GP_Services_Theme_Compat();
