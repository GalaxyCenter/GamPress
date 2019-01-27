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
    if ( 'save' == $doaction ) {
        $redirect_to = remove_query_arg( array( 'action', 'id', 'deleted', 'error', 'spammed', 'unspammed', ), wp_get_referer() );
        $id = (int) $_REQUEST['id'];

        $form_names = array(
            'user_id'             => array( 'required' => false, 'error' => __( 'The activity user_id can\'t be empty', 'gampress-game' ) ),
            'user_name'           => array( 'required' => false, 'error' => __( 'The activity user_name can\'t be empty', 'gampress-game' ) ),
            'item_id'             => array( 'required' => true, 'error' => __( 'The activity item_id can\'t be empty', 'gampress-game' ) ),
            'content'             => array( 'required' => true, 'error' => __( 'The activity content can\'t be empty', 'gampress-game' ) ),
            'likes'             => array( 'required' => false, 'error' => false ),
            'type'            => array( 'required' => false, 'error' => false ),
            'parent_id'            => array( 'required' => false, 'error' => false ),
            'book_cover'            => array( 'required' => false, 'error' => false ),
            'component'            => array( 'required' => false, 'error' => false )
        );
        $form_values = get_request_values( $form_names );
        if ( !empty( $form_values['error'] ) ) {
            gp_core_add_message( sprintf( $form_values['error'] ), 'error' );

            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'error', 'true', $redirect_to );
        } else {
            $new_id = gp_activities_add( array( 'id' => false ,
                'user_id'       => $form_values['values']['user_id'],
                'user_name'     => $form_values['values']['user_name'],
                'component'     => $form_values['values']['component'],
                'type'          => $form_values['values']['type'],
                'content'       => $form_values['values']['content'],
                'item_id'       => $form_values['values']['item_id'],
                'likes'         => $form_values['values']['likes'],
                'parent_id'     => $form_values['values']['parent_id']
            ) );

            gp_core_add_message( __( 'Changes saved.', 'gampress-ext' ) );
            $redirect_to = add_query_arg( 'id', $new_id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'updated', $new_id, $redirect_to );
        }

        wp_redirect( $redirect_to );
        exit;
    }


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
    // Decide whether to load the index or edit screen.
    $doaction = gp_admin_list_table_current_bulk_action();

    // Display the single group edit screen.
    if ( 'edit' == $doaction ) {
        gp_activities_admin_edit();

    } else {
        gp_activities_admin_index();
    }

}
function gp_activities_admin_edit() {
    if ( ! is_super_admin() )
        die( '-1' );

    $id = ! empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;
    $item_id = ! empty( $_REQUEST['item_id'] ) ? $_REQUEST['item_id'] : 0;

    $activity = gp_activities_get_activity( $id );
    $form_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $form_url = add_query_arg( 'action', 'save', $form_url );
    ?>
    <div class="wrap">
        <?php if ( empty( $id ) ) : ?>
            <h1><?php printf( __( 'Add Activity', 'gampress-ext' ) ); ?></h1>
        <?php else:?>
            <h1><?php printf( __( 'Editing Activity', 'gampress-ext' ) ); ?></h1>
        <?php endif;?>

        <?php gp_core_render_message();?>

        <form action="<?php echo esc_url( $form_url ); ?>" id="gp-books-edit-form" method="post">
            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                    <div id="post-body-content">
                        <div id="postdiv">
                            <div id="activity_user_name" class="postbox">
                                <h2><?php _e( 'UserName', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="user_name" type="text" autocomplete="off" spellcheck="true" value="<?php echo $activity->user_name;?>" size="30" name="user_name">
                                </div>
                            </div>

                            <div id="activity_user_id" class="postbox">
                                <h2><?php _e( 'UserId', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="user_id" type="text" autocomplete="off" spellcheck="true" value="<?php echo $activity->user_id; ?>" size="30" name="user_id">
                                </div>
                            </div>

                            <div id="activity_book_id" class="postbox">
                                <h2><?php _e( 'BookId', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="item_id" type="text" autocomplete="off" spellcheck="true" value="<?php echo !empty( $item_id ) ? $item_id : $activity->item_id ; ?>" size="30" name="item_id">
                                </div>
                            </div>

                            <div id="activity_book_id" class="postbox">
                                <h2><?php _e( 'Likes', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="likes" type="text" autocomplete="off" spellcheck="true" value="<?php echo $activity->likes; ?>" size="30" name="likes">
                                </div>
                            </div>

                            <div id="activity_content" class="postbox">
                                <h2><?php _e( 'Content', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="content" type="text" autocomplete="off" spellcheck="true" value="<?php echo $activity->content; ?>" size="30" name="content">
                                </div>
                            </div>

                            <div id="publishing-action">
                                <?php submit_button( __( 'Save', 'gampress-ext' ), 'primary', 'save', false ); ?>
                            </div>
                            <div class="clear"></div>

                        </div>
                    </div><!-- #post-body-content -->

                    <div id="postbox-container-1" class="postbox-container">
                        <?php do_meta_boxes( get_current_screen()->id, 'side', $activity ); ?>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <?php do_meta_boxes( get_current_screen()->id, 'normal', $activity ); ?>
                        <?php do_meta_boxes( get_current_screen()->id, 'advanced', $activity ); ?>
                    </div>
                </div><!-- #post-body -->

            </div><!-- #poststuff -->
            <input type="hidden" name="type" value="book_comment"/>
            <input type="hidden" name="component" value="book"/>
            <input type="hidden" name="parent_id" value="0"/>
            <input type="hidden" name="status" value="1"/>

            <?php wp_nonce_field( 'edit-activity_' . $activity->id ); ?>
        </form>

    </div>

    <?php
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