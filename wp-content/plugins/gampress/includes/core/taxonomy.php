<?php
/**
 * GamPress taxonomy functions.
 *
 * ⊙▂⊙
 * 
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_get_taxonomy_term_site_id( $taxonomy = '' ) {
    $site_id = gp_get_root_blog_id();
    
    return (int) apply_filters( 'gp_get_taxonomy_term_site_id', $site_id, $taxonomy );
}

function gp_set_object_terms( $object_id, $terms, $taxonomy, $append = false ) {
    $site_id = gp_get_taxonomy_term_site_id( $taxonomy );
    
    $switched = false;
    if ( $site_id !== get_current_blog_id() ) {
        switch_to_blog( $site_id );
        gp_register_taxonomies();
        $switched = true;
    }
    
    $tt_ids = wp_set_object_terms( $object_id, $terms, $taxonomy, $append );
    
    if ( $switched ) {
        restore_current_blog();
    }
    
    do_action( 'gp_set_object_terms', $object_id, $terms, $tt_ids, $taxonomy );
    
    return $tt_ids;
}

function gp_get_object_terms( $object_ids, $taxonomies, $args = array() ) {
    // Different taxonomies must be stored on different sites.
    $taxonomy_site_map = array();
    foreach ( (array) $taxonomies as $taxonomy ) {
        $taxonomy_site_id = gp_get_taxonomy_term_site_id( $taxonomy );
        $taxonomy_site_map[ $taxonomy_site_id ][] = $taxonomy;
    }
    
    $retval = array();
    foreach ( $taxonomy_site_map as $taxonomy_site_id => $site_taxonomies ) {
        $switched = false;
        if ( $taxonomy_site_id !== get_current_blog_id() ) {
            switch_to_blog( $taxonomy_site_id );
            gp_register_taxonomies();
            $switched = true;
        }
        
        $site_terms = wp_get_object_terms( $object_ids, $site_taxonomies, $args );
        $retval     = array_merge( $retval, $site_terms );
        
        if ( $switched ) {
            restore_current_blog();
        }
    }
    
    return $retval;
}

function gp_remove_object_terms( $object_id, $terms, $taxonomy ) {
    $site_id = gp_get_taxonomy_term_site_id( $taxonomy );
    
    $switched = false;
    if ( $site_id !== get_current_blog_id() ) {
        switch_to_blog( $site_id );
        gp_register_taxonomies();
        $switched = true;
    }
    
    $retval = wp_remove_object_terms( $object_id, $terms, $taxonomy );
    
    if ( $switched ) {
        restore_current_blog();
    }
    
    do_action( 'gp_remove_object_terms', $object_id, $terms, $taxonomy );
    
    return $retval;
}

function gp_get_objects_in_term( $term_ids, $taxonomies, $args = array() ) {
    // Different taxonomies may be stored on different sites.
    $taxonomy_site_map = array();
    foreach ( (array) $taxonomies as $taxonomy ) {
        $taxonomy_site_id = gp_get_taxonomy_term_site_id( $taxonomy );
        $taxonomy_site_map[ $taxonomy_site_id ][] = $taxonomy;
    }
    
    $retval = array();
    foreach ( $taxonomy_site_map as $taxonomy_site_id => $site_taxonomies ) {
        $switched = false;
        if ( $taxonomy_site_id !== get_current_blog_id() ) {
            switch_to_blog( $taxonomy_site_id );
            gp_register_taxonomies();
            $switched = true;
        }
        
        $site_objects = get_objects_in_term( $term_ids, $site_taxonomies, $args );
        $retval       = array_merge( $retval, $site_objects );
        
        if ( $switched ) {
            restore_current_blog();
        }
    }
    
    return $retval;
}


function gp_get_term_by( $field, $value, $taxonomy = '', $output = OBJECT, $filter = 'raw' ) {
    $site_id = gp_get_taxonomy_term_site_id( $taxonomy );
    
    $switched = false;
    if ( $site_id !== get_current_blog_id() ) {
        switch_to_blog( $site_id );
        gp_register_taxonomies();
        $switched = true;
    }
    
    $term = get_term_by( $field, $value, $taxonomy, $output, $filter );
    
    if ( $switched ) {
        restore_current_blog();
    }
    
    return $term;
}

function gp_register_default_taxonomies() {
    // Email type.
    register_taxonomy(
            gp_get_email_tax_type(),
            gp_get_email_post_type(),
            apply_filters( 'gp_register_email_tax_type', array(
                    'description'   => _x( 'GamPress email types', 'email type taxonomy description', 'gampress' ),
                    'labels'        => gp_get_email_tax_type_labels(),
                    'meta_box_cb'   => 'gp_email_tax_type_metabox',
                    'public'        => false,
                    'query_var'     => false,
                    'rewrite'       => false,
                    'show_in_menu'  => false,
                    'show_tagcloud' => false,
                    'show_ui'       => gp_is_root_blog() && gp_current_user_can( 'gp_moderate' ),
                    ) )
            );
}
add_action( 'gp_register_taxonomies', 'gp_register_default_taxonomies' );