<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/25
 * Time: 13:59
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load the GP games admin.
add_action( 'gp_init', array( 'GP_Activities_Admin', 'register_activities_admin' ) );

if ( !class_exists( 'WP_List_Table' ) ) require(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

function gp_activities_add_admin_menu() {

    // Add our screen.
    $hook = add_menu_page(
        _x( 'Activities', 'Admin activities page title', 'gampress' ),
        _x( 'Activities', 'Admin activities menu', 'gampress' ),
        'gp_moderate',
        'gp-activities',
        'gp_activities_admin',
        'div'
    );

    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_activities_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_activities_add_admin_menu' );

function gp_activities_admin_load() {
    global $gp_activities_list_table;

    $doaction = gp_admin_list_table_current_bulk_action();
    gp_core_setup_message();
    $gp_activities_list_table = new GP_Activities_List_Table();

    if ( !empty( $doaction ) && ! in_array( $doaction, array( '-1', 'edit', 'save', 'import' ) ) ) {
        // Build redirection URL
        $redirect_to = remove_query_arg( array( 'id', 'deleted', 'error', 'comfirmed', ), wp_get_referer() );
        $redirect_to = add_query_arg( 'paged', $gp_activities_list_table->get_pagenum(), $redirect_to );

        $ids = (array) $_REQUEST['id'];

        if ( 'bulk_' == substr( $doaction, 0, 5 ) && ! empty( $_REQUEST['id'] ) ) {
            // Check this is a valid form submission
            check_admin_referer( 'bulk-activities' );

            // Trim 'bulk_' off the action name to avorder_id duplicating a ton of code
            $doaction = substr( $doaction, 5 );

        }
        $disapproved = 0;

        $errors = array();

        foreach ( $ids as $id ) {

            switch( $doaction ) {
                case 'disapproved':
                    gp_activities_activity_disapproved( $id );
                    $disapproved++;
                    break;
            }

        }

        if ( ! empty( $errors ) )
            $redirect_to = add_query_arg( 'error', implode ( ',', array_map( 'absint', $errors ) ), $redirect_to );

        wp_redirect( $redirect_to );
    }
}

function gp_activities_admin() {
    gp_activities_admin_index();
}

function gp_activities_admin_index() {
    global $gp_activities_list_table, $plugin_page;

    $messages = array();

    // If the user has just made a change to a group, build status messages.
    if ( ! empty( $_REQUEST['deleted'] ) ) {
        $deleted  = ! empty( $_REQUEST['deleted'] ) ? (int) $_REQUEST['deleted'] : 0;

        if ( $deleted > 0 ) {
            $messages[] = sprintf( _n( '%s group has been permanently deleted.', '%s activities have been permanently deleted.', $deleted, 'gampress' ), number_format_i18n( $deleted ) );
        }
    }
    $messages[] = isset( $_COOKIE['gp-message'] ) ? $_COOKIE['gp-message'] : '';

    // Prepare the group items for display.
    $gp_activities_list_table->prepare_items();

    $edit_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $edit_url = add_query_arg( 'action', 'edit', $edit_url );
    $edit_url = add_query_arg( 'id', '0', $edit_url );
    ?>

    <div class="wrap">
        <h1>
            <?php _e( 'Activity', 'gampress' ); ?>

            <?php if ( is_user_logged_in() ) : ?>
                <a class="add-new-h2" href="<?php echo $edit_url;?>"><?php _e( 'Add New', 'gampress' ); ?></a>
            <?php endif; ?>

            <?php if ( !empty( $_REQUEST['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'gampress' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
            <?php endif; ?>
        </h1>

        <hr class="wp-header-end" />

        <?php gp_core_render_message();?>

        <?php // Display each group on its own row. ?>
        <?php $gp_activities_list_table->views(); ?>

        <form id="gp-activities-form" action="" method="get">
            <?php $gp_activities_list_table->search_box( __( 'Search all Activities', 'gampress' ), 'gp-activities' ); ?>
            <input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
            <?php $gp_activities_list_table->display(); ?>
        </form>

    </div>

    <?php
}