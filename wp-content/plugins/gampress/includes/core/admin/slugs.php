<?php
/**
 * GamPress Admin Slug Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @sugpackage CoreAdministration
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_core_admin_slugs_settings() {
?>

	<div class="wrap">

		<h1><?php _e( 'GamPress Settings', 'gampress' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php gp_core_admin_tabs( __( 'Pages', 'gampress' ) ); ?></h2>
		<form action="" method="post" id="gp-admin-page-form">

			<?php gp_core_admin_slugs_options(); ?>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="gp-admin-pages-submit" id="gp-admin-pages-submit" value="<?php esc_attr_e( 'Save Settings', 'gampress' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'gp-admin-pages-setup' ); ?>

		</form>
	</div>

<?php
}

function gp_core_admin_slugs_options() {
	$gp = gampress();

	// Get the existing WP pages
	$existing_pages = gp_core_get_directory_page_ids();

	// Set up an array of components (along with component names) that have directory pages.
	$directory_pages = gp_core_admin_get_directory_pages();

	if ( !empty( $directory_pages ) ) : ?>

		<h3><?php _e( 'Directories', 'gampress' ); ?></h3>

		<p><?php _e( 'Associate a WordPress Page with each GamPress component directory.', 'gampress' ); ?></p>

		<table class="form-table">
			<tbody>

				<?php foreach ( $directory_pages as $name => $label ) : ?>

					<tr valign="top">
						<th scope="row">
							<label for="gp_pages[<?php echo esc_attr( $name ) ?>]"><?php echo esc_html( $label ) ?></label>
						</th>

						<td>

							<?php if ( ! gp_is_root_blog() ) switch_to_blog( gp_get_root_blog_id() ); ?>

							<?php echo wp_dropdown_pages( array(
								'name'             => 'gp_pages[' . esc_attr( $name ) . ']',
								'echo'             => false,
								'show_option_none' => __( '- None -', 'gampress' ),
								'selected'         => !empty( $existing_pages[$name] ) ? $existing_pages[$name] : false
							) ); ?>

							<?php if ( !empty( $existing_pages[$name] ) ) : ?>

								<a href="<?php echo get_permalink( $existing_pages[$name] ); ?>" class="button-secondary" target="_gp"><?php _e( 'View', 'gampress' ); ?></a>

							<?php endif; ?>

							<?php if ( ! gp_is_root_blog() ) restore_current_blog(); ?>

						</td>
					</tr>


				<?php endforeach ?>

				<?php

				/**
				 * Fires after the display of default directories.
				 *
				 * Allows plugins to add their own directory associations.
				 *
				 * @since 1.5.0
				 */
				do_action( 'gp_active_external_directories' ); ?>

			</tbody>
		</table>

	<?php

	endif;

	/** Static Display ********************************************************/

	$static_pages = gp_core_admin_get_static_pages();

	if ( !empty( $static_pages ) ) : ?>

		<h3><?php _e( 'Registration', 'gampress' ); ?></h3>

		<?php if ( gp_get_signup_allowed() ) : ?>
			<p><?php _e( 'Associate WordPress Pages with the following GamPress Registration pages.', 'gampress' ); ?></p>
		<?php else : ?>
			<?php if ( is_multisite() ) : ?>
				<p><?php printf( __( 'Registration is currently disabled.  Before associating a page is allowed, please enable registration by selecting either the "User accounts may be registered" or "Both sites and user accounts can be registered" option on <a href="%s">this page</a>.', 'gampress' ), network_admin_url( 'settings.php' ) ); ?></p>
			<?php else : ?>
				<p><?php printf( __( 'Registration is currently disabled.  Before associating a page is allowed, please enable registration by clicking on the "Anyone can register" checkbox on <a href="%s">this page</a>.', 'gampress' ), admin_url( 'options-general.php' ) ); ?></p>
			<?php endif; ?>
		<?php endif; ?>

		<table class="form-table">
			<tbody>

				<?php if ( gp_get_signup_allowed() ) : foreach ( $static_pages as $name => $label ) : ?>

					<tr valign="top">
						<th scope="row">
							<label for="gp_pages[<?php echo esc_attr( $name ) ?>]"><?php echo esc_html( $label ) ?></label>
						</th>

						<td>

							<?php if ( ! gp_is_root_blog() ) switch_to_blog( gp_get_root_blog_id() ); ?>

							<?php echo wp_dropdown_pages( array(
								'name'             => 'gp_pages[' . esc_attr( $name ) . ']',
								'echo'             => false,
								'show_option_none' => __( '- None -', 'gampress' ),
								'selected'         => !empty( $existing_pages[$name] ) ? $existing_pages[$name] : false
							) ) ?>

							<?php if ( !empty( $existing_pages[$name] ) ) : ?>

								<a href="<?php echo get_permalink( $existing_pages[$name] ); ?>" class="button-secondary" target="_gp"><?php _e( 'View', 'gampress' ); ?></a>

							<?php endif; ?>

							<?php if ( ! gp_is_root_blog() ) restore_current_blog(); ?>

						</td>
					</tr>

				<?php endforeach; endif; ?>

				<?php
				do_action( 'gp_active_external_pages' ); ?>

			</tbody>
		</table>

		<?php
	endif;
}

function gp_core_admin_get_directory_pages() {
	$gp = gampress();
	$directory_pages = array();

	// Loop through loaded components and collect directories.
	if ( is_array( $gp->loaded_components ) ) {
		foreach( $gp->loaded_components as $component_slug => $component_id ) {

			// Only components that need directories should be listed here.
			if ( isset( $gp->{$component_id} ) && !empty( $gp->{$component_id}->has_directory ) ) {

				// The component->name property was introduced in BP 1.5, so we must provide a fallback.
				$directory_pages[$component_id] = !empty( $gp->{$component_id}->name ) ? $gp->{$component_id}->name : ucwords( $component_id );
			}
		}
	}

	/** Directory Display *****************************************************/

	return apply_filters( 'gp_directory_pages', $directory_pages );
}

function gp_core_admin_get_static_pages() {
	$static_pages = array(
		'signup'   => __( 'Signup', 'gampress' ),
		'activate' => __( 'Activate', 'gampress' ),
	);

	return apply_filters( 'gp_static_pages', $static_pages );
}

function gp_core_admin_slugs_setup_handler() {

	if ( isset( $_POST['gp-admin-pages-submit'] ) ) {
		if ( !check_admin_referer( 'gp-admin-pages-setup' ) )
			return false;

		// Then, update the directory pages.
		if ( isset( $_POST['gp_pages'] ) ) {
			$valid_pages = array_merge( gp_core_admin_get_directory_pages(), gp_core_admin_get_static_pages() );

			$new_directory_pages = array();
			foreach ( (array) $_POST['gp_pages'] as $key => $value ) {
				if ( isset( $valid_pages[ $key ] ) ) {
					$new_directory_pages[ $key ] = (int) $value;
				}
			}
			gp_core_update_directory_page_ids( $new_directory_pages );
		}

		$base_url = gp_get_admin_url( add_query_arg( array( 'page' => 'gp-page-settings', 'updated' => 'true' ), 'admin.php' ) );

		wp_redirect( $base_url );
	}
}
add_action( 'gp_admin_init', 'gp_core_admin_slugs_setup_handler' );