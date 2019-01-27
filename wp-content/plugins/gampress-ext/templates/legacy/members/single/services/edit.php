<?php
/**
 * Created by PhpStorm.
 * User: Texel
 * Date: 2017/2/22
 * Time: 12:58
 */

$service_id = gp_get_services_current_service_id();

if ( !empty( $service_id ) )
    $service = gp_services_get_service( $service_id );
?>

<form action="<?php gp_service_edit_link( $service );?>" method="post" id="service-edit-form" class="standard-form">

    <?php if ( empty( $service_id ) ) : ?>
        <h2><?php _e( 'Publish Service', 'gampress-ext' ) ?></h2>
    <?php else : ?>
        <h2><?php printf( __( "Edit '%s'", 'gampress-ext' ), gp_get_service_name( $service ) );?></h2>
    <?php endif;?>

    <div class="editfield required-field visibility-public field_type_textbox">
        <label for="name"><?php _e( 'Service Name', 'gampress-ext' ) ?><span class="bp-required-field-label"><?php _e( '(required)', 'gampress-ext' ) ?></span></label>

        <input type="text" aria-required="true" value="<?php if ( !empty( $service_id ) ) echo gp_get_service_name( $service ) ;?>" name="name" id="name">

        <div id="field-visibility-settings-toggle-1" class="field-visibility-settings-notoggle">
            <?php _e( 'Enter Service Name', 'gampress-ext' ) ?>
        </div>
    </div>

    <div class="editfield required-field visibility-public field_type_textbox">
        <label for="price"><?php _e( 'Price', 'gampress-ext' ) ?><span class="bp-required-field-label"><?php _e( '(required)', 'gampress-ext' ) ?></span></label>

        <input type="text" aria-required="true" value="<?php if ( !empty( $service_id ) ) echo gp_get_service_price( $service ) ;?>" name="price" id="price">

        <div id="field-visibility-settings-toggle-1" class="field-visibility-settings-notoggle">
            <?php _e( 'Enter Service Price', 'gampress-ext' ) ?>
        </div>
    </div>

    <div class="editfield required-field visibility-public field_type_textbox">
        <label for="description"><?php _e( 'Service Description', 'gampress-ext' ) ?><span class="bp-required-field-label"><?php _e( '(required)', 'gampress-ext' ) ?></span></label>

        <textarea type="text" aria-required="true" name="description" id="description"><?php if ( !empty( $service_id ) ) echo gp_get_service_description( $service ) ;?></textarea>

        <div id="field-visibility-settings-toggle-1" class="field-visibility-settings-notoggle">
            <?php _e( 'Enter Service Name', 'gampress-ext' ) ?>
        </div>
    </div>

    <div class="editfield required-field visibility-public field_type_textbox">
        <label for="unit"><?php _e( 'Unit', 'gampress-ext' ) ?><span class="bp-required-field-label"><?php _e( '(required)', 'gampress-ext' ) ?></span></label>

        <select name="unit" id="unit">
            <option value=""><?php _e( 'Select Service Unit', 'gampress-ext' ); ?></option>
            <?php
            $sunits = gp_service_get_units();
            $cunit = gp_get_service_unit( $service );
            ?>

            <?php foreach ( $sunits as $k => $v ) : ?>
                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k,  $cunit ); ?>><?php echo esc_html( $v ); ?></option>
            <?php endforeach; ?>
        </select>

        <div id="field-visibility-settings-toggle-1" class="field-visibility-settings-notoggle">
            <?php _e( 'Choose a unit', 'gampress-ext' ) ?>
        </div>
    </div>

    <div class="editfield required-field visibility-public field_type_textbox">
        <label for="type"><?php _e( 'Type', 'gampress-ext' ) ?><span class="bp-required-field-label"><?php _e( '(required)', 'gampress-ext' ) ?></span></label>

        <select name="type" id="type">
            <option value=""><?php _e( 'Select Service Type', 'gampress-ext' ); ?></option>
            <?php
            $stypes = gp_service_get_types();
            $ctype = gp_get_service_type( $service );
            ?>

            <?php foreach ( $stypes as $k => $v ) : ?>
                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k,  $ctype ); ?>><?php echo esc_html( $v ); ?></option>
            <?php endforeach; ?>
        </select>

        <div id="field-visibility-settings-toggle-1" class="field-visibility-settings-notoggle">
            <?php _e( 'Choose a Type', 'gampress-ext' ) ?>
        </div>
    </div>

    <div class="submit">
        <input type="submit" value="Save Changes " id="profile-group-edit-submit" name="profile-group-edit-submit">
    </div>
    
    <input type="hidden" name="gp_service_edit" value="<?php echo wp_create_nonce( 'gp_service_edit' ); ?>" />
</form>
