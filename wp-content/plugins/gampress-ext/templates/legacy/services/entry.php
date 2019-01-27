<?php

/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

global $service;
?>

<li class="" id="service-<?php gp_service_id( $service ); ?>">
    <div class="service-avatar">
        <a href="<?php gp_service_user_link( $service ); ?>">

            <?php gp_service_avatar( 'type=full&width=60&height=60' ); ?>

        </a>
    </div>

    <div class="service-content">

        <div class="service-header">

            <a href="<?php gp_service_user_link( $service );  ?>"><?php echo bp_core_get_username( $service->user_id ); ?></a>
            发布了一个#<?php gp_service_type( $service ); ?>#服务 <?php gp_service_name( $service ); ?>   ￥<?php gp_service_price( $service );?> / <?php gp_service_unit( $service );?>

        </div>

        <div class="service-meta">

            <?php gp_service_description( $service ); ?>
            
        </div>
        
        <?php if ( is_user_logged_in() && gp_is_my_service() ) : ?>
        <div class="service-meta">
            <a id="aservice-service-<?php gp_service_id( $service ); ?>" class="fa fa-edit aservice-reply bp-primary-action" href="<?php gp_service_edit_link( $service );?>"></a>
        </div>
        <?php endif; ?>

    </div>

</li>

