<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 14:52
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_core_admin_pays_settings() {
    ?>

    <div class="wrap">

        <h1><?php _e( 'Pays Settings', 'gampress' ); ?> </h1>

        <h2 class="nav-tab-wrapper"><?php gp_core_admin_tabs( __( 'Pays', 'gampress' ) ); ?></h2>

        <form action="" method="post">

            <table class="form-table">
                <tbody>

                    <tr valign="top">
                        <th scope="row"><?php _e('Alipay ID','gampress')?></th>

                        <td>
                            <input type="text" id="<?php echo esc_attr( "gp_pays_alipay_id" ); ?>" name="<?php echo esc_attr( "gp_pays_alipay_id" ); ?>" value="<?php gp_pays_alipay_id();?>"/>
                            <p class="description"><?php _e('This is the ID of your Alipay application', 'gampress')?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Alipay Partner','gampress')?></th>

                        <td>
                            <input type="text" id="<?php echo esc_attr( "gp_pays_alipay_partner" ); ?>" name="<?php echo esc_attr( "gp_pays_alipay_partner" ); ?>" value="<?php gp_pays_alipay_partner();?>"/>
                            <p class="description"><?php _e('This is the partner of your Alipay application', 'gampress')?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Alipay Key','gampress')?></th>

                        <td>
                            <input type="text" id="<?php echo esc_attr( "gp_pays_alipay_key" ); ?>" name="<?php echo esc_attr( "gp_pays_alipay_key" ); ?>" value="<?php gp_pays_alipay_key();?>"/>
                            <p class="description"><?php _e('This is the key of your Alipay application', 'gampress')?></p>
                        </td>
                    </tr>

                </tbody>

            </table>

            <hr/>

            <table class="form-table">
                <tbody>

                <tr valign="top">
                    <th scope="row"><?php _e('Wechat App ID','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_pays_wechat_app_id" ); ?>" name="<?php echo esc_attr( "gp_pays_wechat_app_id" ); ?>" value="<?php gp_pays_wechat_app_id();?>"/>
                        <p class="description"><?php _e('This is the App ID of your Wechat application', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Wechat App Secret','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_pays_wechat_app_secret" ); ?>" name="<?php echo esc_attr( "gp_pays_wechat_app_secret" ); ?>" value="<?php gp_pays_wechat_app_secret();?>"/>
                        <p class="description"><?php _e('This is the secret of your Wechat application', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Wechat Mchid','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_pays_wechat_mchid" ); ?>" name="<?php echo esc_attr( "gp_pays_wechat_mchid" ); ?>" value="<?php gp_pays_wechat_mchid();?>"/>
                        <p class="description"><?php _e('This is the mchid of your Wechat application', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Wechat Key','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_pays_wechat_key" ); ?>" name="<?php echo esc_attr( "gp_pays_wechat_key" ); ?>" value="<?php gp_pays_wechat_key();?>"/>
                        <p class="description"><?php _e('This is the key of your Wechat application', 'gampress')?></p>
                    </td>
                </tr>

                </tbody>

            </table>

            <p class="submit clear">
                <input class="button-primary" type="submit" name="gp-admin-pays-submit" id="gp-admin-pays-submit" value="<?php esc_attr_e( 'Save Settings', 'gampress' ) ?>"/>
            </p>

            <?php wp_nonce_field( 'gp-admin-pays-setup' ); ?>

        </form>
    </div>
    <?php
}

function gp_core_admin_pays_settings_handler() {
// Bail if not saving settings.
    if ( ! isset( $_POST['gp-admin-pays-submit'] ) )
        return;

    // Bail if nonce fails.
    if ( ! check_admin_referer( 'gp-admin-pays-setup' ) )
        return;

    // Where are we redirecting to?
    $base_url = gp_get_admin_url( add_query_arg( array( 'page' => 'gp-pays-settings', 'updated' => 'true' ), 'admin.php' ) );

    $form_items = array(
        'gp_pays_alipay_id',
        'gp_pays_alipay_partner',
        'gp_pays_alipay_key',

        'gp_pays_wechat_app_id',
        'gp_pays_wechat_app_secret',
        'gp_pays_wechat_mchid',
        'gp_pays_wechat_key'
    );

    foreach( $form_items as $item ) {
        gp_update_option( $item, $_POST[$item] );
    }

    // Redirect.
    wp_redirect( $base_url );
    die();
}
add_action( 'gp_admin_init', 'gp_core_admin_pays_settings_handler' );