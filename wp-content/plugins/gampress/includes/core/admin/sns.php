<?php
/**
 * GamPress Admin Sns Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage CoreAdministration
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_core_admin_sns_settings() {
	?>

	<div class="wrap">

		<h1><?php _e( 'Sns Settings', 'gampress' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php gp_core_admin_tabs( __( 'Sns', 'gampress' ) ); ?></h2>

		<form action="" method="post">

			<table class="form-table">
                <tbody>
                    <tr valign="top">
						<th scope="row"><?php _e('Wechat Subscript AppID','gampress')?></th>

						<td>
                            <input type="text" id="<?php echo esc_attr( "gp_sns_wechat_app_id" ); ?>" name="<?php echo esc_attr( "gp_sns_wechat_sub_app_id" ); ?>" value="<?php echo gp_sns_wechat_sub_app_id();?>"/>
                            <p class="description"><?php _e('This is the AppID of your Wechat Subscript application', 'gampress')?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
						<th scope="row"><?php _e('Wechat Subscript AppSecret','gampress')?></th>

						<td>
                            <input type="text" id="<?php echo esc_attr( "gp_sns_wechat_app_secret" ); ?>" name="<?php echo esc_attr( "gp_sns_wechat_sub_app_secret" ); ?>" value="<?php gp_sns_wechat_sub_app_secret();?>"/>
                            <p class="description"><?php _e('This is the AppSecret of your Wechat Subscript application', 'gampress')?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
						<th scope="row"><?php _e('Wechat Subscript Token','gampress')?></th>

						<td>
                            <input type="text" id="<?php echo esc_attr( "gp_sns_wechat_app_token" ); ?>" name="<?php echo esc_attr( "gp_sns_wechat_sub_app_token" ); ?>" value="<?php gp_sns_wechat_sub_app_token();?>"/>
                            <p class="description"><?php _e('This is the Token of your Sns subscribe application', 'gampress')?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('URL','gampress')?></th>
                        
                        <td>
                            <h4><?php gp_sns_wechat_subscription_token_link();?></h4>
                            <p class="description"><?php _e('This is the url of your Sns subscribe application', 'gampress')?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr/>
            
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Wechat Oauth AppID','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sns_wechat_app_id" ); ?>" name="<?php echo esc_attr( "gp_sns_wechat_app_id" ); ?>" value="<?php gp_sns_wechat_app_id();?>"/>
                        <p class="description"><?php _e('This is the AppID of your Wechat Oauth application', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Wechat Oauth AppSecret','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sns_wechat_app_secret" ); ?>" name="<?php echo esc_attr( "gp_sns_wechat_app_secret" ); ?>" value="<?php gp_sns_wechat_app_secret();?>"/>
                        <p class="description"><?php _e('This is the AppSecret of your Wechat Oauth application', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Wechat Oauth AccessToken','gampress')?></th>

                    <td>
                        <input type="text" value="<?php echo wp_cache_get( 'gp_wechat_access_token', 'gp' );?>"/>
                        <p class="description"></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Wechat UnAuthorize Login WordPress','gampress')?></th>

                    <td>
                        <input type="checkbox" id="<?php echo esc_attr( "gp_sns_wechat_unauthorize_login" ); ?>" <?php checked( gp_sns_wechat_is_unauthorize_login(), true );?>/>
                        <input type="hidden" name="<?php echo esc_attr( "gp_sns_wechat_unauthorize_login" ); ?>" value="<?php echo gp_sns_wechat_is_unauthorize_login() ? 1 : 0 ;?>"/>
                        <p class="description"><?php _e('This is the AppSecret of your Wechat Oauth application', 'gampress')?></p>
                    </td>
                    <script>
                        $ = jQuery;
                        $('#gp_sns_wechat_unauthorize_login').click(function () {
                            if ($(this).is(':checked')) {
                                $('input[name="gp_sns_wechat_unauthorize_login"]').val(1);
                            } else {
                                $('input[name="gp_sns_wechat_unauthorize_login"]').val(0);
                            }
                        });
                    </script>
                </tr>

                <tr>
                    <th scope="row"><?php _e('URL','gampress')?></th>

                    <td>
                        <h4><?php gp_sns_wechat_redirect_uri();?></h4>
                        <p class="description"><?php _e('This is the url of your wechat application', 'gampress')?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Weibo AppID','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sns_weibo_app_id" ); ?>" name="<?php echo esc_attr( "gp_sns_weibo_app_id" ); ?>" value="<?php gp_sns_weibo_app_id();?>"/>
                        <p class="description"><?php _e('This is the AppID of your Weibo application', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Weibo AppSecret','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sns_weibo_app_secret" ); ?>" name="<?php echo esc_attr( "gp_sns_weibo_app_secret" ); ?>" value="<?php gp_sns_weibo_app_secret();?>"/>
                        <p class="description"><?php _e('This is the AppSecret of your Weibo application', 'gampress')?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('URL','gampress')?></th>

                    <td>
                        <h4><?php gp_sns_weibo_redirect_uri();?></h4>
                        <p class="description"><?php _e('This is the url of your weibo application redirect_uri', 'gampress')?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr />

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('QQ AppID','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sns_qq_app_id" ); ?>" name="<?php echo esc_attr( "gp_sns_qq_app_id" ); ?>" value="<?php gp_sns_qq_app_id();?>"/>
                        <p class="description"><?php _e('This is the AppID of your QQ application', 'gampress')?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('QQ AppSecret','gampress')?></th>

                    <td>
                        <input type="text" id="<?php echo esc_attr( "gp_sns_qq_app_secret" ); ?>" name="<?php echo esc_attr( "gp_sns_qq_app_secret" ); ?>" value="<?php gp_sns_qq_app_secret();?>"/>
                        <p class="description"><?php _e('This is the AppSecret of your QQ application', 'gampress')?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('URL','gampress')?></th>

                    <td>
                        <h4><?php gp_sns_qq_redirect_uri();?></h4>
                        <p class="description"><?php _e('This is the url of your QQ application redirect_uri', 'gampress')?></p>
                    </td>
                </tr>
                </tbody>
            </table>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="gp-admin-sns-submit" id="gp-admin-sns-submit" value="<?php esc_attr_e( 'Save Settings', 'gampress' ) ?>"/>
			</p>
            
            <?php wp_nonce_field( 'gp-admin-sns-setup' ); ?>
		</form>
	</div>

<?php
}

function gp_core_admin_sns_settings_handler() {
    // Bail if not saving settings.
	if ( ! isset( $_POST['gp-admin-sns-submit'] ) )
		return;
        
    // Bail if nonce fails.
	if ( ! check_admin_referer( 'gp-admin-sns-setup' ) )
		return;
        
    // Where are we redirecting to?
	$base_url = gp_get_admin_url( add_query_arg( array( 'page' => 'gp-sns-settings', 'updated' => 'true' ), 'admin.php' ) );

    $form_items = array(
            'gp_sns_wechat_app_id',
            'gp_sns_wechat_app_secret',
			'gp_sns_wechat_unauthorize_login',

			'gp_sns_wechat_sub_app_id',
			'gp_sns_wechat_sub_app_secret',
			'gp_sns_wechat_sub_app_token',

            'gp_sns_weibo_app_id',
            'gp_sns_weibo_app_secret',

            'gp_sns_qq_app_id',
            'gp_sns_qq_app_secret'
		);

	foreach( $form_items as $item ) {
		gp_update_option( $item, $_POST[$item] );
	}
        
	// Redirect.
	wp_redirect( $base_url );
	die();
}
add_action( 'gp_admin_init', 'gp_core_admin_sns_settings_handler' );