<?php
/**
 * Created by PhpStorm.
 * User: Texel
 * Date: 2017/1/5
 * Time: 17:30
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_core_admin_settings() {
    ?>

    <div class="wrap">

        <h1><?php _e( 'GamPress Settings', 'gampress' ); ?> </h1>

        <h2 class="nav-tab-wrapper"><?php gp_core_admin_tabs( __( 'Options', 'gampress' ) ); ?></h2>
        <form action="" method="post" id="gp-admin-page-form">

            <?php settings_fields( 'gampress' ); ?>

            <?php do_settings_sections( 'gampress' ); ?>

            <p class="submit">
                <input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'gampress' ); ?>" />
            </p>

        </form>
    </div>

    <?php
}

function gp_core_admin_settings_save() {
    if ( isset( $_GET['page'] ) && 'gp-settings' == $_GET['page'] && !empty( $_POST['submit'] ) ) {
        check_admin_referer('gampress-options');

        if ( isset( $wp_settings_fields['gampress'] ) ) {
            foreach( (array) $wp_settings_fields['gampress'] as $section => $settings ) {
                foreach( $settings as $setting_name => $setting ) {
                    $value = isset( $_POST[$setting_name] ) ? $_POST[$setting_name] : '';

                    gp_update_option( $setting_name, $value );
                }
            }
        }

        // Some legacy options are not registered with the Settings API, or are reversed in the UI.
        $legacy_options = array(
            'page-description',
            'page-keywords'
        );

        foreach( $legacy_options as $legacy_option ) {
            // Note: Each of these options is represented by its opposite in the UI
            // Ie, the Profile Syncing option reads "Enable Sync", so when it's checked,
            // the corresponding option should be unset.
            $value = isset( $_POST[$legacy_option] ) ? $_POST[$legacy_option] : "";
            gp_update_option( $legacy_option, $value );
        }
        
        gp_core_redirect( add_query_arg( array( 'page' => 'gp-settings', 'updated' => 'true' ), gp_get_admin_url( 'admin.php' ) ) );
    }
}
add_action( 'gp_admin_init', 'gp_core_admin_settings_save', 100 );

function gp_admin_setting_callback_main_section() { }

function gp_admin_setting_callback_page_keywords() {
    ?>

    <input id="page-keywords" name="page-keywords" type="text" value="<?php gp_page_keywords();?>" />

    <?php
}

function gp_admin_setting_callback_page_description() {
    ?>

    <input id="page-description" name="page-description" type="text" value="<?php gp_page_description();?>" />

    <?php
}