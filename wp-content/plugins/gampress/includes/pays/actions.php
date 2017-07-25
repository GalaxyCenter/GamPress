<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 11:35
 */

defined( 'ABSPATH' ) || exit;

function pays_create() {
    if ( !gp_is_pays_component() || ! gp_is_current_action( 'create' ) )
        return false;

    $form_names = array(
        'order_id'                  => array( 'required' => true, '' => ''),
        'product_fee'               => array( 'required' => true, 'error' => __( 'The product_fee can\'t be empty', 'gampress-ext' ) ),
        'redirect'                  => array( 'required' => false )
    );

    $form_values = get_request_values( $form_names );
    if ( !empty( $form_values['error'] ) )
        return false;

    $name = gp_action_variable(0);
    $order_id               = $form_values['values']['order_id'];
    $product_fee            = $form_values['values']['product_fee'];
    $product_name           = get_bloginfo('name') . '-' . $order_id;

    if ( empty( $name ) ) {
        gp_do_404();
        return false;
    }
    $pay = apply_filters( "gp_pays_{$name}", false );
    $pay->do_pay( $order_id, $product_name, $product_fee, $form_values['values']['redirect'] );

}
add_action( 'gp_actions', 'pays_create' );

function pays_notify() {
    if ( !gp_is_pays_component() || ! gp_is_current_action( 'notify' ) )
        return false;

//    $result = pays_virify( $_POST );
//    if ( $result )
//        echo 'success';
//    else
//        echo 'false';

    $name = gp_action_variable(0);
    $pay = apply_filters( "gp_pays_{$name}", false );
    $pay->notify();
}
add_action( 'gp_actions', 'pays_notify' );

function pays_return() {
    if ( !gp_is_pays_component() || ! gp_is_current_action( 'return' ) )
        return false;

    // redirect 是系统追加的,需要reset, 不然无法通过md5校验
    if ( isset( $_GET['redirect'] ) ) {
        $redirect = $_GET['redirect'];
        unset($_GET['redirect']);
    } else {
        $redirect = '';
    }

    $result = pays_virify( $_GET );
    if ( $result )
        gp_core_redirect( '/pays/success?redirect=' . $redirect );
    else
        gp_core_redirect( '/pays/fail?redirect=' . $redirect );
}
add_action( 'gp_actions', 'pays_return' );

function pays_virify( $params ) {
    $name = gp_action_variable(0);
    $pay = apply_filters( "gp_pays_{$name}", false );
    return $pay->verify( $params );
}