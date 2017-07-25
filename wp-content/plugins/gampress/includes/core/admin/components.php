<?php
/**
 * GamPress Admin Component Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage CoreAdministration
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_core_admin_components_settings() {
?>

	<div class="wrap">

		<h1><?php _e( 'GamPress Settings', 'gampress' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php gp_core_admin_tabs( __( 'Components', 'gampress' ) ); ?></h2>
		<form action="" method="post" id="gp-admin-component-form">

			<?php gp_core_admin_components_options(); ?>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="gp-admin-component-submit" id="gp-admin-component-submit" value="<?php esc_attr_e( 'Save Settings', 'gampress' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'gp-admin-component-setup' ); ?>

		</form>
	</div>

<?php
}

function gp_core_admin_get_components( $type = 'all' ) {
	$components = gp_core_get_components( $type );
	return apply_filters( 'gp_core_admin_get_components', $components, $type );
}

function gp_core_admin_components_options() {
    $deactivated_components = array();
    $active_components      = apply_filters( 'gp_active_components', gp_get_option( 'gp-active-components' ) );
    
    $default_components  = array();
    $optional_components = gp_core_admin_get_components( 'optional' );
	$required_components = gp_core_admin_get_components( 'required' );
	$retired_components  = gp_core_admin_get_components( 'retired'  );
    
    $all_components = $optional_components + $required_components;
    if ( empty( $active_components ) ) {
		$deactivated_components = gp_get_option( 'gp-deactivated-components' );
		if ( !empty( $deactivated_components ) ) {

			// Trim off namespace and filename.
			$trimmed = array();
			foreach ( array_keys( (array) $deactivated_components ) as $component ) {
				$trimmed[] = str_replace( '.php', '', str_replace( 'gp-', '', $component ) );
			}

			// Loop through the optional components to create an active component array.
			foreach ( array_keys( (array) $optional_components ) as $ocomponent ) {
				if ( !in_array( $ocomponent, $trimmed ) ) {
					$active_components[$ocomponent] = 1;
				}
			}
		}
	}
    
    if ( empty( $active_components ) ) {
		$active_components = $default_components;
	}
    
    $active_components['core'] = $all_components['core'];
	$inactive_components       = array_diff( array_keys( $all_components ) , array_keys( $active_components ) );

    /** Display **************************************************************
	 */
    
    $all_count = count( $all_components );
	$page      = gp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';
	$action    = !empty( $_GET['action'] ) ? $_GET['action'] : 'all';
    
    switch( $action ) {
		case 'all' :
			$current_components = $all_components;
			break;
		case 'active' :
			foreach ( array_keys( $active_components ) as $component ) {
				$current_components[$component] = $all_components[$component];
			}
			break;
		case 'inactive' :
			foreach ( $inactive_components as $component ) {
				$current_components[$component] = $all_components[$component];
			}
			break;
		case 'mustuse' :
			$current_components = $required_components;
			break;
		case 'retired' :
			$current_components = $retired_components;
			break;
	} ?>
    
    <h3 class="screen-reader-text"><?php _e( 'Filter components list', 'gampress' );?></h3>
    
    <ul class="subsubsub">
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'gp-components', 'action' => 'all'      ), gp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'all'      ) : ?>class="current"<?php endif; ?>><?php printf( _nx( 'All <span class="count">(%s)</span>',      'All <span class="count">(%s)</span>',      $all_count,         'plugins', 'gampress' ), number_format_i18n( $all_count                    ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'gp-components', 'action' => 'active'   ), gp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'active'   ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Active <span class="count">(%s)</span>',   'Active <span class="count">(%s)</span>',   count( $active_components   ), 'gampress' ), number_format_i18n( count( $active_components   ) ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'gp-components', 'action' => 'inactive' ), gp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'inactive' ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', count( $inactive_components ), 'gampress' ), number_format_i18n( count( $inactive_components ) ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'gp-components', 'action' => 'mustuse'  ), gp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'mustuse'  ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Must-Use <span class="count">(%s)</span>', 'Must-Use <span class="count">(%s)</span>', count( $required_components ), 'gampress' ), number_format_i18n( count( $required_components ) ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'gp-components', 'action' => 'retired'  ), gp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'retired'  ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Retired <span class="count">(%s)</span>',  'Retired <span class="count">(%s)</span>',  count( $retired_components ),  'gampress' ), number_format_i18n( count( $retired_components  ) ) ); ?></a></li>
	</ul>

	<h3 class="screen-reader-text"><?php _e( 'Components list', 'gampress' );?></h3>
    
    <table class="wp-list-table widefat plugins">
		<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox" disabled><label class="screen-reader-text" for="cb-select-all-1"><?php
					/* translators: accessibility text */
					_e( 'Bulk selection is disabled', 'gampress' );
				?></label></td>
				<th scope="col" id="name" class="manage-column column-title column-primary"><?php _e( 'Component', 'gampress' ); ?></th>
				<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', 'gampress' ); ?></th>
			</tr>
		</thead>

		<tbody id="the-list">

			<?php if ( !empty( $current_components ) ) : ?>

				<?php foreach ( $current_components as $name => $labels ) : ?>

					<?php if ( !in_array( $name, array( 'core', 'members' ) ) ) :
						$class = isset( $active_components[esc_attr( $name )] ) ? 'active' : 'inactive';
					else :
						$class = 'active';
					endif; ?>

					<tr id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $name ) . ' ' . esc_attr( $class ); ?>">
						<th scope="row" class="check-column">

							<?php if ( !in_array( $name, array( 'core', 'members' ) ) ) : ?>

								<input type="checkbox" id="<?php echo esc_attr( "gp_components[$name]" ); ?>" name="<?php echo esc_attr( "gp_components[$name]" ); ?>" value="1"<?php checked( isset( $active_components[esc_attr( $name )] ) ); ?> /><label for="<?php echo esc_attr( "gp_components[$name]" ); ?>" class="screen-reader-text"><?php
									/* translators: accessibility text */
									printf( __( 'Select %s', 'gampress' ), esc_html( $labels['title'] ) ); ?></label>

							<?php else : ?>

								<input type="checkbox" id="<?php echo esc_attr( "gp_components[$name]" ); ?>" name="<?php echo esc_attr( "gp_components[$name]" ); ?>" value="1" checked="checked" disabled><label for="<?php echo esc_attr( "gp_components[$name]" ); ?>" class="screen-reader-text"><?php
									/* translators: accessibility text */
									printf( __( '%s is a required component', 'gampress' ), esc_html( $labels['title'] ) ); ?></label>

							<?php endif; ?>

						</th>
						<td class="plugin-title column-primary">
							<span aria-hidden="true"></span>
							<strong><?php echo esc_html( $labels['title'] ); ?></strong>
						</td>

						<td class="column-description desc">
							<div class="plugin-description">
								<p><?php echo $labels['description']; ?></p>
							</div>

						</td>
					</tr>

				<?php endforeach ?>

			<?php else : ?>

				<tr class="no-items">
					<td class="colspanchange" colspan="3"><?php _e( 'No components found.', 'gampress' ); ?></td>
				</tr>

			<?php endif; ?>

		</tbody>

		<tfoot>
			<tr>
				<td class="manage-column column-cb check-column"><input id="cb-select-all-2" type="checkbox" disabled><label class="screen-reader-text" for="cb-select-all-2"><?php
					/* translators: accessibility text */
					_e( 'Bulk selection is disabled', 'gampress' );
				?></label></td>
				<th class="manage-column column-title column-primary"><?php _e( 'Component', 'gampress' ); ?></th>
				<th class="manage-column column-description"><?php _e( 'Description', 'gampress' ); ?></th>
			</tr>
		</tfoot>

	</table>

	<input type="hidden" name="gp_components[members]" value="1" />

	<?php
}

function gp_core_admin_components_settings_handler() {
    // Bail if not saving settings.
	if ( ! isset( $_POST['gp-admin-component-submit'] ) )
		return;

	// Bail if nonce fails.
	if ( ! check_admin_referer( 'gp-admin-component-setup' ) )
		return;

	// Settings form submitted, now save the settings. First, set active components.
	if ( isset( $_POST['gp_components'] ) ) {
		$gp = gampress();
        
        require_once( $gp->includes_dir . '/core/admin/schema.php' );

		$submitted = stripslashes_deep( $_POST['gp_components'] );
		$gp->active_components = gp_core_admin_get_active_components_from_submitted_settings( $submitted );

		gp_core_install( $gp->active_components );
		gp_core_add_page_mappings( $gp->active_components );
		gp_update_option( 'gp-active-components', $gp->active_components );
	}

	// Where are we redirecting to?
	$base_url = gp_get_admin_url( add_query_arg( array( 'page' => 'gp-components', 'updated' => 'true' ), 'admin.php' ) );

	// Redirect.
	wp_redirect( $base_url );
	die();
}
add_action( 'gp_admin_init', 'gp_core_admin_components_settings_handler' );

function gp_core_admin_get_active_components_from_submitted_settings( $submitted ) {
    $current_action = 'all';

	if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'active', 'inactive', 'retired' ) ) ) {
		$current_action = $_GET['action'];
	}

	$current_components = gampress()->active_components;

	switch ( $current_action ) {
		case 'retired' :
			$retired_components = gp_core_admin_get_components( 'retired' );
			foreach ( array_keys( $retired_components ) as $retired_component ) {
				if ( ! isset( $submitted[ $retired_component ] ) ) {
					unset( $current_components[ $retired_component ] );
				}
			} // Fall through.


		case 'inactive' :
			$components = array_merge( $submitted, $current_components );
			break;

		case 'all' :
		case 'active' :
		default :
			$components = $submitted;
			break;
	}

	return $components;
}