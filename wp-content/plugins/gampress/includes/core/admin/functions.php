<?php

/**
 * GamPress Admin Functions
 * ⊙▂⊙
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_admin_list_table_current_bulk_action() {
    
    $action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
    
    // If the bottom is set, let it override the action
    if ( ! empty( $_REQUEST['action2'] ) && $_REQUEST['action2'] != "-1" ) {
        $action = $_REQUEST['action2'];
    }
    
    return $action;
}

function gp_core_modify_admin_menu_highlight() {
    global $plugin_page, $submenu_file;
    
    // This tweaks the Settings subnav menu to show only one GamPress menu item.
    if ( ! in_array( $plugin_page, array( 'gp-general-settings', ) ) ) {
        $submenu_file = 'gp-components';
    }
    
    // Network Admin > Tools.
    if ( in_array( $plugin_page, array( 'gp-tools', 'available-tools' ) ) ) {
        $submenu_file = $plugin_page;
    }
}

function gp_core_admin_tabs( $active_tab = '' ) {
    $tabs_html    = '';
    $idle_class   = 'nav-tab';
    $active_class = 'nav-tab nav-tab-active';
    
    $tabs         = apply_filters( 'gp_core_admin_tabs', gp_core_get_admin_tabs( $active_tab ) );
    
    // Loop through tabs and build navigation.
    foreach ( array_values( $tabs ) as $tab_data ) {
        $is_current = (bool) ( $tab_data['name'] == $active_tab );
        $tab_class  = $is_current ? $active_class : $idle_class;
        $tabs_html .= '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['name'] ) . '</a>';
    }
    
    echo $tabs_html;
}

function gp_core_get_admin_tabs( $active_tab = '' ) {
    $tabs = array(
            '0' => array(
                'href' => gp_get_admin_url( add_query_arg( array( 'page' => 'gp-components' ), 'admin.php' ) ),
                'name' => __( 'Components', 'gampress' )
                ),
            '1' => array(
                'href' => gp_get_admin_url( add_query_arg( array( 'page' => 'gp-page-settings' ), 'admin.php' ) ),
                'name' => __( 'Pages', 'gampress' )
                ),
            '3' => array(
                'href' => gp_get_admin_url( add_query_arg( array( 'page' => 'gp-settings' ), 'admin.php' ) ),
                'name' => __( 'Options', 'gampress' )
                )
            );

    if ( gp_is_active( 'sns' ) ) {
        array_push( $tabs, array(
            'href' => gp_get_admin_url(add_query_arg(array('page' => 'gp-sns-settings'), 'admin.php')),
            'name' => __('Sns', 'gampress')
        ) );
    }

    if ( gp_is_active( 'sms' ) ) {
        array_push( $tabs, array(
            'href' => gp_get_admin_url(add_query_arg(array('page' => 'gp-sms-settings'), 'admin.php')),
            'name' => __('Sms', 'gampress')
        ) );
    }

    if ( gp_is_active( 'pays' ) ) {
        array_push( $tabs, array(
            'href' => gp_get_admin_url(add_query_arg(array('page' => 'gp-pays-settings'), 'admin.php')),
            'name' => __('Pays', 'gampress')
        ) );
    }

    return apply_filters( 'gp_core_get_admin_tabs', $tabs );
}

function gp_terms_checklist_args( $args, $post_id ) {
    
    $args['checked_ontop'] = false;
    return $args;
}
add_filter( 'wp_terms_checklist_args', 'gp_terms_checklist_args', 10, 2 );

function gp_core_admin_backpat_menu() {
    global $_parent_pages, $_registered_pages, $submenu;
    
    // If there's no gp-general-settings menu (perhaps because the current
    // user is not an Administrator), there's nothing to do here.
    if ( ! isset( $submenu['gp-general-settings'] ) ) {
        return;
    }
    
    if ( 1 != count( $submenu['gp-general-settings'] ) ) {
        return;
    }
    
    // This removes the top-level menu.
    remove_submenu_page( 'gp-general-settings', 'gp-general-settings' );
    remove_menu_page( 'gp-general-settings' );
    
    // These stop people accessing the URL directly.
    unset( $_parent_pages['gp-general-settings'] );
    unset( $_registered_pages['toplevel_page_gp-general-settings'] );
}
add_action( gp_core_admin_hook(), 'gp_core_admin_backpat_menu', 999 );

function gp_admin_menu_order( $menu_order ) {
    // Bail if user cannot see admin pages.
    if ( empty( $menu_order ) || ! gp_current_user_can( 'gp_moderate' ) ) {
        return $menu_order;
    }
    
    // Initialize our custom order array.
    $gp_menu_order = array();
    
    // Menu values.
    $last_sep     = is_network_admin() ? 'separator1' : 'separator2';
    
    $custom_menus = (array) apply_filters( 'gp_admin_menu_order', array() );
    
    // Bail if no components have top level admin pages.
    if ( empty( $custom_menus ) ) {
        return $menu_order;
    }
    
    // Add our separator to beginning of array.
    array_unshift( $custom_menus, 'separator-gampress' );
    
    // Loop through menu order and do some rearranging.
    foreach ( (array) $menu_order as $item ) {
        
        // Position GamPress menus above appearance.
        if ( $last_sep == $item ) {
            
            // Add our custom menus.
            foreach( (array) $custom_menus as $custom_menu ) {
                if ( array_search( $custom_menu, $menu_order ) ) {
                    $gp_menu_order[] = $custom_menu;
                }
            }
            
            // Add the appearance separator.
            $gp_menu_order[] = $last_sep;
            
            // Skip our menu items.
        } elseif ( ! in_array( $item, $custom_menus ) ) {
            $gp_menu_order[] = $item;
        }
    }
    
    // Return our custom order.
    return $gp_menu_order;
}

function gp_admin_separator() {
    
    // Bail if GamPress is not network activated and viewing network admin.
    if ( is_network_admin() && ! gp_is_network_activated() ) {
        return;
    }
    
    // Bail if GamPress is network activated and viewing site admin.
    if ( ! is_network_admin() && gp_is_network_activated() ) {
        return;
    }
    
    // Prevent duplicate separators when no core menu items exist.
    if ( ! gp_current_user_can( 'gp_moderate' ) ) {
        return;
    }
   
    global $menu;
    
    $menu[] = array( '', 'read', 'separator-gampress', '', 'wp-menu-separator gampress' );
}

function gp_admin_custom_menu_order( $menu_order = false ) {
    
    // Bail if user cannot see admin pages.
    if ( ! gp_current_user_can( 'gp_moderate' ) ) {
        return $menu_order;
    }
    
    return true;
}