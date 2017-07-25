<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/11
 * Time: 15:46
 */

defined( 'ABSPATH' ) || exit;

function gp_core_admin_sms_settings() {
    ?>

    <div class="wrap">

        <h1><?php _e( 'Sms Settings', 'gampress' ); ?> </h1>

        <h2 class="nav-tab-wrapper"><?php gp_core_admin_tabs( __( 'Sms', 'gampress' ) ); ?></h2>

        <form action="" method="post">

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('YunTongXun ACCOUNT SID','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sms_ytx_accound_sid" ); ?>" name="<?php echo esc_attr( "gp_sms_ytx_accound_sid" ); ?>" value="<?php echo gp_sms_ytx_accound_sid();?>"/>
                        <p class="description"><?php _e('This is the YunTongXun ACCOUNT SID', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('YunTongXun AUTH TOKEN','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sms_ytx_auth_token" ); ?>" name="<?php echo esc_attr( "gp_sms_ytx_auth_token" ); ?>" value="<?php gp_sms_ytx_auth_token();?>"/>
                        <p class="description"><?php _e('This is YunTongXun AUTH TOKEN', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('YunTongXun AppID','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sms_ytx_app_id" ); ?>" name="<?php echo esc_attr( "gp_sms_ytx_app_id" ); ?>" value="<?php gp_sms_ytx_app_id();?>"/>
                        <p class="description"><?php _e('This is YunTongXun AppID', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('YunTongXun Code Template Id','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sms_ytx_template_code_id" ); ?>" name="<?php echo esc_attr( "gp_sms_ytx_template_code_id" ); ?>" value="<?php gp_sms_ytx_template_code_id();?>"/>
                        <p class="description"><?php _e('This is YunTongXun Code Template Id', 'gampress')?></p>
                    </td>
                </tr>

                </tbody>
            </table>

            <p class="submit clear">
                <input class="button-primary" type="submit" name="gp-admin-sms-submit" id="gp-admin-sms-submit" value="<?php esc_attr_e( 'Save Settings', 'gampress' ) ?>"/>
            </p>

            <?php wp_nonce_field( 'gp-admin-sms-setup' ); ?>
        </form>
    </div>

    <?php
}

function gp_core_admin_sms_settings_handler() {
    // Bail if not saving settings.
    if ( ! isset( $_POST['gp-admin-sms-submit'] ) )
        return;

    // Bail if nonce fails.
    if ( ! check_admin_referer( 'gp-admin-sms-setup' ) )
        return;

    // Where are we redirecting to?
    $base_url = gp_get_admin_url( add_query_arg( array( 'page' => 'gp-sms-settings', 'updated' => 'true' ), 'admin.php' ) );

    $form_items = array(
        'gp_sms_ytx_accound_sid',
        'gp_sms_ytx_app_id',
        'gp_sms_ytx_auth_token',
        'gp_sms_ytx_template_code_id'
    );

    foreach( $form_items as $item ) {
        gp_update_option( $item, $_POST[$item] );
    }

    // Redirect.
    wp_redirect( $base_url );
    die();
}
add_action( 'gp_admin_init', 'gp_core_admin_sms_settings_handler' );