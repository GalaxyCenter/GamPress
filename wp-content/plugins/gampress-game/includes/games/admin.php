<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 11:01
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load the GP games admin.
add_action( 'gp_init', array( 'GP_Games_Admin', 'register_games_admin' ) );

if ( !class_exists( 'WP_List_Table' ) ) require(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

function gp_games_activities_add_admin_menu() {

    // Add our screen.
    $hook = add_menu_page(
        _x( 'Games', 'Admin games page title', 'gampress-game' ),
        _x( 'Games', 'Admin games menu', 'gampress-game' ),
        'gp_moderate',
        'gp-games',
        'gp_games_activities_admin',
        'div'
    );

    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_games_activities_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_games_activities_add_admin_menu' );

function gp_games_activities_admin_load() {
    global $gp_activities_list_table;

    // Decide whether to load the dev version of the CSS and JavaScript
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

    $doaction = gp_admin_list_table_current_bulk_action();

    gp_core_setup_message();
    // Edit screen
    if ( 'edit' == $doaction ) {

    } else if ( 'save' == $doaction ) {
        $redirect_to = remove_query_arg( array( 'action', 'id', 'deleted', 'error', 'spammed', 'unspammed', ), wp_get_referer() );

        $id = (int) $_REQUEST['id'];
        check_admin_referer( 'edit-activity_' . $id );

        $form_names = array(
            'activity_name'             => array( 'required' => true, 'error' => __( 'The activity name can\'t be empty', 'gampress-game' ) ),
            'activity_start_time'       => array( 'required' => true, 'error' => __( 'The activity start time can\'t be empty', 'gampress-game' ) ),
            'activity_expired'          => array( 'required' => true, 'error' => __( 'The activity expired can\'t be empty', 'gampress-game' ) ),
            'activity_status'           => array( 'required' => true, 'error' => __( 'The activity status can\'t be empty', 'gampress-game' ) ),
            'activity_type'             => array( 'required' => true, 'error' => __( 'The activity type can\'t be empty', 'gampress-game' ) ),

            'activity_user_create_group_max'    => array( 'required' => true, 'error' => __( 'The activity_user_create_group_max can\'t be empty', 'gampress-game' ) ),
            'activity_user_join_group_max'      => array( 'required' => true, 'error' => __( 'The activity_user_join_group_max can\'t be empty', 'gampress-game' ) ),
            'activity_group_max_members'        => array( 'required' => true, 'error' => __( 'The activity_group_max_members can\'t be empty', 'gampress-game' ) ),
            'activity_user_complete_conditions' => array( 'required' => true, 'error' => __( 'The activity_user_complete_conditions can\'t be empty', 'gampress-game' ) ),
            'activity_items'                    => array( 'required' => true, 'error' => __( 'The activity_items can\'t be empty', 'gampress-game' ) ),

            'activity_wechat_app_id'            => array( 'required' => true, 'error' => __( 'The activity_wechat_app_id can\'t be empty', 'gampress-game' ) ),
            'activity_wechat_mch_id'            => array( 'required' => true, 'error' => __( 'The activity_wechat_mch_id can\'t be empty', 'gampress-game' ) ),
            'activity_wechat_key'               => array( 'required' => true, 'error' => __( 'The activity_wechat_key can\'t be empty', 'gampress-game' ) ),
            'activity_wechat_pack_fee'          => array( 'required' => true, 'error' => __( 'The activity_wechat_pack_fee can\'t be empty', 'gampress-game' ) ),

            'activity_wechat_pack_sender'       => array( 'required' => true, 'error' => __( 'The activity_wechat_pack_sender can\'t be empty', 'gampress-game' ) ),
            'activity_wechat_pack_wishing'      => array( 'required' => true, 'error' => __( 'The activity_wechat_pack_wishing can\'t be empty', 'gampress-game' ) )
        );
        $form_values = get_request_values( $form_names );
        if ( !empty( $form_values['error'] ) ) {
            gp_core_add_message( sprintf( $form_values['error'] ), 'error' );

            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'error', 'true', $redirect_to );
        } else {
            $new_id = gp_games_update_activity( array( 'id' => $id ,
                'name'             => $form_values['values']['activity_name'],
                'start_time'       => $form_values['values']['activity_start_time'],
                'expired'          => $form_values['values']['activity_expired'],
                'type'             => $form_values['values']['activity_type'],
                'status'           => $form_values['values']['activity_status']
                ) );

            gp_games_activities_update_meta( $new_id, 'group_max_members', $form_values['values']['activity_group_max_members'] );
            gp_games_activities_update_meta( $new_id, 'user_create_group_max', $form_values['values']['activity_user_create_group_max'] );
            gp_games_activities_update_meta( $new_id, 'user_join_group_max', $form_values['values']['activity_user_join_group_max'] );
            gp_games_activities_update_meta( $new_id, 'user_complete_conditions', $form_values['values']['activity_user_complete_conditions'] );

            $complete_conditions = $form_values['values']['activity_user_complete_conditions'];
            $complete_conditions = explode(',',$complete_conditions );

            $activity_items = $form_values['values']['activity_items'];
            $activity_items = explode( ',', $activity_items );
            for ($i = 0; $i < count($activity_items); $i++) {
                gp_games_activities_update_meta($new_id, 'item_' . $complete_conditions[$i], $activity_items[$i]);
            }

            gp_games_activities_update_meta( $new_id, 'wechat_app_id', $form_values['values']['activity_wechat_app_id'] );
            gp_games_activities_update_meta( $new_id, 'wechat_mch_id', $form_values['values']['activity_wechat_mch_id'] );
            gp_games_activities_update_meta( $new_id, 'wechat_key', $form_values['values']['activity_wechat_key'] );
            gp_games_activities_update_meta( $new_id, 'wechat_pack_fee', $form_values['values']['activity_wechat_pack_fee'] );

            gp_games_activities_update_meta( $new_id, 'wechat_pack_sender', $form_values['values']['activity_wechat_pack_sender'] );
            gp_games_activities_update_meta( $new_id, 'wechat_pack_wishing', $form_values['values']['activity_wechat_pack_wishing'] );

            gp_core_add_message( __( 'Changes saved.', 'gampress-ext' ) );
            $redirect_to = add_query_arg( 'id', $new_id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'updated', $new_id, $redirect_to );
        }

        wp_redirect( $redirect_to );
        exit;
    } else { // Index screen.
        $gp_activities_list_table = new GP_Games_Activities_List_Table();
    }
}

function gp_games_activities_admin() {
    // Decide whether to load the index or edit screen.
    $doaction = gp_admin_list_table_current_bulk_action();

    // Display the single group edit screen.
    if ( 'edit' == $doaction ) {
        gp_games_activities_admin_edit();

    } else {
        gp_games_activities_admin_index();
    }
}

function gp_games_activities_admin_edit() {
    if ( ! is_super_admin() )
        die( '-1' );

    $id = ! empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;

    $activity = gp_games_get_activity( $id );
    $form_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $form_url = add_query_arg( 'action', 'save', $form_url );
    ?>
    <div class="wrap">
        <?php if ( empty( $id ) ) :?>
        <h1><?php printf( __( 'Add Activity', 'gampress-game' ) ); ?></h1>
        <?php else:?>
        <h1><?php printf( __( 'Editing Activity:《%s》', 'gampress-game' ), gp_games_get_activity_name( $activity ) ); ?></h1>
        <?php endif;?>
        <?php gp_core_render_message();?>

        <form action="<?php echo esc_url( $form_url ); ?>" id="gp-activitys-edit-form" method="post">
            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                    <div id="post-body-content">
                        <div id="postdiv">
                            <div id="gp_activity_name" class="postbox">
                                <h2><?php _e( 'Name', 'gampress-game' ); ?></h2>
                                <div class="inside">
                                    <input id="activity_name" type="text" autocomplete="off" spellcheck="true" value="<?php gp_games_activity_name( $activity );?>" size="30" name="activity_name">
                                </div>
                            </div>


                            <div id="gp_activity_config" class="postbox">
                                <div class="inside">
                                    用户可创建最大上限
                                    <input type="text" name="activity_user_create_group_max" value="<?php echo gp_games_activities_get_meta( $id, 'user_create_group_max', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    用户可加入最大上限
                                    <input type="text" name="activity_user_join_group_max" value="<?php echo gp_games_activities_get_meta( $id, 'user_join_group_max', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    用户组最大成员数量
                                    <input type="text" name="activity_group_max_members" value="<?php echo gp_games_activities_get_meta( $id, 'group_max_members', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    达成条件人数
                                    <input type="text" name="activity_user_complete_conditions" value="<?php echo gp_games_activities_get_meta( $id, 'user_complete_conditions', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    达成条件人数对应奖品
                                    <?php
                                    $complete_conditions = gp_games_activities_get_meta( $id, 'user_complete_conditions', true );
                                    $complete_conditions = explode(',',$complete_conditions );

                                    $activity_items = array();
                                    for ($i = 0; $i < count($complete_conditions); $i++) {
                                        $activity_items[] = gp_games_activities_get_meta( $id, 'item_' . $complete_conditions[$i], true );
                                    }
                                    ?>
                                    <input type="text" name="activity_items" value="<?php echo join( ',', $activity_items );?>"/>
                                </div>
                                <hr/>
                                <div class="inside">
                                    微信公众号app id
                                    <input type="text" name="activity_wechat_app_id" value="<?php echo gp_games_activities_get_meta( $id, 'wechat_app_id', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    微信商户号
                                    <input type="text" name="activity_wechat_mch_id" value="<?php echo gp_games_activities_get_meta( $id, 'wechat_mch_id', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    微信商户号key
                                    <input type="text" name="activity_wechat_key" value="<?php echo gp_games_activities_get_meta( $id, 'wechat_key', true ) ;?>"/>
                                </div>
                                <hr/>
                                <div class="inside">
                                    发送红包名称
                                    <input type="text" name="activity_wechat_pack_sender" value="<?php echo gp_games_activities_get_meta( $id, 'wechat_pack_sender', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    祝福语
                                    <input type="text" name="activity_wechat_pack_wishing" value="<?php echo gp_games_activities_get_meta( $id, 'wechat_pack_wishing', true ) ;?>"/>
                                </div>
                                <div class="inside">
                                    游戏后发放微信红包金额(单位元)
                                    <input type="text" name="activity_wechat_pack_fee" value="<?php echo gp_games_activities_get_meta( $id, 'wechat_pack_fee', true ) ;?>"/>
                                </div>
                            </div>

                        </div>
                    </div> <!-- #post-body-content -->

                    <div id="postbox-container-1" class="postbox-container">

                        <div id="gp_activity_publish" class="postbox">
                            <h2><?php _e( 'Publish', 'gampress-game' ); ?></h2>
                            <div class="inside">
                                <div id="submitpost" class="submitbox">
                                    <div id="minor-publishing">
                                        <div id="minor-publishing-actions"></div>
                                        <div id="misc-publishing-actions">
                                            <input id="activity_status" type="hidden" autocomplete="off" spellcheck="true" value="0" size="20" name="activity_status">

                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action">
                                            <?php submit_button( __( 'Update', 'gampress-game' ), 'primary', 'save', false ); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div id="gp_activity_start_time" class="postbox">
                            <h2><?php _e( 'StartTime', 'gampress-game' ); ?></h2>
                            <div class="inside">
                                <input id="activity_start_time" type="text" autocomplete="off" spellcheck="true" value="<?php gp_games_activity_start_time( $activity );?>" size="20" name="activity_start_time">

                            </div>

                        </div>

                        <div id="gp_activity_expired" class="postbox">
                            <h2><?php _e( 'Expired', 'gampress-game' ); ?></h2>
                            <div class="inside">
                                <input id="activity_expired" type="text" autocomplete="off" spellcheck="true" value="<?php gp_games_activity_expired( $activity );?>" size="20" name="activity_expired">
                            </div>
                        </div>

                        <div id="gp_activity_type" class="postbox">
                            <h2><?php _e( 'Type', 'gampress-game' ); ?></h2>
                            <div class="inside">
                                <input id="activity_type" type="text" autocomplete="off" spellcheck="true" value="invite" size="20" name="activity_type">
                            </div>
                        </div>

                    </div>


                </div>
            </div>
            <link rel="stylesheet" type="text/css" href="<?php echo GP_GAME_PLUGIN_URL;?>includes/games/admin/css/jquery.datetimepicker.css"/ >
            <script src="<?php echo GP_GAME_PLUGIN_URL;?>includes/games/admin/js/jquery.js"></script>
            <script src="<?php echo GP_GAME_PLUGIN_URL;?>includes/games/admin/js/jquery.datetimepicker.full.js"></script>
            <script>
                jQuery(document).ready(function() {
                    jQuery('#activity_start_time,#activity_expired').datetimepicker({
                    });
                });
            </script>
            <?php wp_nonce_field( 'edit-activity_' . $id ); ?>
        </form>
    </div>
    <?php
}

function gp_games_activities_admin_index() {
    global $gp_activities_list_table, $plugin_page;

    $messages = array();

    // If the user has just made a change to a group, build status messages.
    if ( ! empty( $_REQUEST['deleted'] ) ) {
        $deleted  = ! empty( $_REQUEST['deleted'] ) ? (int) $_REQUEST['deleted'] : 0;

        if ( $deleted > 0 ) {
            $messages[] = sprintf( _n( '%s group has been permanently deleted.', '%s activities have been permanently deleted.', $deleted, 'gampress-game' ), number_format_i18n( $deleted ) );
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
            <?php _e( 'Activity', 'gampress-game' ); ?>

            <?php if ( is_user_logged_in() ) : ?>
                <a class="add-new-h2" href="<?php echo $edit_url;?>"><?php _e( 'Add New', 'gampress-game' ); ?></a>
            <?php endif; ?>

            <?php if ( !empty( $_REQUEST['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'gampress-game' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
            <?php endif; ?>
        </h1>

        <hr class="wp-header-end" />

        <?php gp_core_render_message();?>

        <?php // Display each group on its own row. ?>
        <?php $gp_activities_list_table->views(); ?>

        <form id="gp-activities-form" action="" method="get">
            <?php $gp_activities_list_table->search_box( __( 'Search all Books', 'gampress-game' ), 'gp-activities' ); ?>
            <input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
            <?php $gp_activities_list_table->display(); ?>
        </form>

    </div>

    <?php
}
/********** Game Groups **********/
function gp_games_groups_add_admin_menu() {

    // Add our screen.
    $hook = add_menu_page(
        _x( 'GamesGroup', 'Admin games group page title', 'gampress-game' ),
        _x( 'GamesGroup', 'Admin games group menu', 'gampress-game' ),
        'gp_moderate',
        'gp-games-groups',
        'gp_games_groups_admin',
        'div'
    );

    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_games_groups_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_games_groups_add_admin_menu' );

function gp_games_groups_admin_load() {
    global $gp_groups_list_table;

    // Decide whether to load the dev version of the CSS and JavaScript
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

    $doaction = gp_admin_list_table_current_bulk_action();

    gp_core_setup_message();
    // Edit screen
    if ( 'edit' == $doaction ) {

    } else if ( 'save' == $doaction ) {
    } else { // Index screen.
        $gp_groups_list_table = new GP_Games_Groups_List_Table();
    }
}

function gp_games_groups_admin() {
    // Decide whether to load the index or edit screen.
    $doaction = gp_admin_list_table_current_bulk_action();

    // Display the single group edit screen.
    if ( 'edit' == $doaction ) {
    } else {
        gp_games_groups_admin_index();
    }
}

function gp_games_groups_admin_index() {
    global $gp_groups_list_table, $plugin_page;

    $messages = array();
    $messages[] = isset( $_COOKIE['gp-message'] ) ? $_COOKIE['gp-message'] : '';

    // Prepare the group items for display.
    $gp_groups_list_table->prepare_items();
    ?>

    <div class="wrap">
        <h1>
            <?php _e( 'GameGroups', 'gampress-game' ); ?>

            <?php if ( !empty( $_REQUEST['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'gampress-game' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
            <?php endif; ?>
        </h1>

        <hr class="wp-header-end" />

        <?php gp_core_render_message();?>

        <?php // Display each group on its own row. ?>
        <?php $gp_groups_list_table->views(); ?>

        <form id="gp-activities-form" action="" method="get">
            <?php $gp_groups_list_table->search_box( __( 'Search all Groups', 'gampress-game' ), 'gp-activities' ); ?>
            <input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
            <?php $gp_groups_list_table->display(); ?>
        </form>

    </div>

    <?php
}