<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/11
 * Time: 20:27
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load the GP Orders admin.
add_action( 'gp_init', array( 'GP_Orders_Admin', 'register_orders_admin' ) );

if ( !class_exists( 'WP_List_Table' ) ) require( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

function gp_orders_add_admin_menu() {

    // Add our screen.
    $hook = add_menu_page(
        _x( 'Orders', 'Admin Orders page title', 'gampress-ext' ),
        _x( 'Orders', 'Admin Orders menu', 'gampress-ext' ),
        'gp_moderate',
        'gp-orders',
        'gp_orders_admin',
        'div'
    );

    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_orders_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_orders_add_admin_menu' );

function gp_orders_admin_load() {
    global $gp_orders_list_table;

    // Decide whether to load the dev version of the CSS and JavaScript
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

    $doaction = gp_admin_list_table_current_bulk_action();

    gp_core_setup_message();
    // Edit screen
    if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ) {
    } else if ( 'save' == $doaction ) {
    } else { // Index screen.
        $gp_orders_list_table = new GP_Orders_List_Table();
    }

    if ( !empty( $doaction ) && ! in_array( $doaction, array( '-1', 'edit', 'save', 'import' ) ) ) {

    } elseif ( $doaction && 'save' == $doaction ) {

    } elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
        wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
        exit;
    }
}

function gp_orders_admin() {
    // Decide whether to load the index or edit screen.
    $doaction = gp_admin_list_table_current_bulk_action();

    // Display the single group edit screen.
    if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ) {
        gp_orders_admin_edit();
    } else {
        gp_orders_admin_index();
    }
}

function gp_orders_admin_index() {
    global $gp_orders_list_table, $plugin_page;

    $messages = array();

    // If the user has just made a change to a group, build status messages.
    if ( ! empty( $_REQUEST['deleted'] ) ) {
        $deleted  = ! empty( $_REQUEST['deleted'] ) ? (int) $_REQUEST['deleted'] : 0;

        if ( $deleted > 0 ) {
            $messages[] = sprintf( _n( '%s group has been permanently deleted.', '%s orders have been permanently deleted.', $deleted, 'gampress-ext' ), number_format_i18n( $deleted ) );
        }
    }
    $messages[] = isset( $_COOKIE['gp-message'] ) ? $_COOKIE['gp-message'] : '';

    // Prepare the group items for display.
    $gp_orders_list_table->prepare_items();
    ?>

    <div class="wrap">
        <h1>
            <?php _e( 'Orders', 'gampress-ext' ); ?>

            <?php if ( !empty( $_REQUEST['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'gampress-ext' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
            <?php endif; ?>
        </h1>

        <hr class="wp-header-end" />

        <?php gp_core_render_message();?>

        <?php // Display each group on its own row. ?>
        <?php $gp_orders_list_table->views(); ?>

        <form id="gp-orders-form" action="" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
            <?php $gp_orders_list_table->display(); ?>

            <?php if ( isset( $_GET['user_id'] ) ) : ?>
            <input type="hidden" name="user_id" value="<?php echo $_GET['user_id'];?>" />
            <?php endif;?>
        </form>

    </div>

    <?php
}

function gp_orders_admin_edit() {
}