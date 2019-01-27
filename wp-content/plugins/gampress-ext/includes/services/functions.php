<?php
/**
 * Created by PhpStorm.
 * User: Texel
 * Date: 2017/2/23
 * Time: 9:53
 */

if ( !defined( 'ABSPATH' ) ) exit;

function gp_services_get_services ( $args = '' ) {
    if ( empty( $args ) )
        return false;
    
    $key = 'gp_ex_services_' . join( '_', $args );
    $datas = wp_cache_get( $key );
    
    if ( empty( $datas ) ) {
        $datas = GP_Services_Service::get( $args );
        wp_cache_set( $key, $datas );
    }
    
    return $datas;
}

function gp_services_get_service( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }
    
    $key = 'gp_ex_service_' . $id;
    
    $service = wp_cache_get( $key );
    if ( empty( $service ) ) {
        $service = new GP_Services_Service( $id );
        wp_cache_set( $key, $service );
    }
    
    return $service;
}

function gp_services_update_service( $args ) {
    if ( ! gp_is_active( 'services' ) ) {
        return false;
    }                       
    
    $defaults = array(
            'id'                    => false,
            'user_id'               => false,
            'name'                  => false,
            'price'                 => false,
            'unit'                  => false,
            'type'                  => false,
            'description'           => false,
            'status'                => 0,
            'date_created'          => gp_core_current_time()
            );
            
    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );
    
    if ( empty( $user_id ) || empty( $name ) || empty( $price )
            || empty( $unit ) || empty( $type ) || empty( $description ) )
        return false;
        
    if ( !empty( $id ) ) {
        $service = gp_services_get_service( $id );
    } else {
        $service = new GP_Services_Service();
        $service->id = $id;
    }
    
    $service->user_id       = $user_id;
    $service->name          = $name;
    $service->price         = $price;
    $service->unit          = $unit;
    $service->type          = $type;
    $service->description   = $description;
    $service->status        = $status;
    $service->date_create   = $date_created;
    
    if ( !$service->save() )
        return $service;
        
    wp_cache_set( 'gp_service_' . $service->id, $service, 'gp_services' );
    
    return $service->id;
}

function gp_service_get_units() {
    return array(
        'time' => __( 'Time', 'gampress-ext'),
        'hour' => __( 'Hour', 'gampress-ext'),
        'day' => __( 'Day', 'gampress-ext'));
}

function gp_service_get_types() {
    return array(
        'housewifery' => __('Housewifery', 'gampress-ext'));
}
