<?php
function gp_users_admin_dashboard_new_user_everyday() {
    global $wpdb;

    $paged_items = $wpdb->get_results( "SELECT DATE_FORMAT(user_registered, '%Y%m%d') days, count(0) as c FROM `adaixiong`.`ds_users` GROUP BY days ORDER BY `days` DESC  LIMIT 0,10;" );

    echo "<div id=\"new_user_everyday\"><div class=\"orders-block\"><ul>";
    echo '<style>#new_user_everyday span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {

        echo "<li><span>{$item->days}</span>{$item->c}</li>";
    }
    echo '</ul></div></div>';
}

function wp_dashboard_user_setup() {
    wp_add_dashboard_widget('users_new_user_everyday', 'New users every day', 'gp_users_admin_dashboard_new_user_everyday');
}
add_action( 'wp_dashboard_setup', 'wp_dashboard_user_setup' );