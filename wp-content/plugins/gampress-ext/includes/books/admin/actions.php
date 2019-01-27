<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/14
 * Time: 10:14
 */

function gp_books_user_coins_and_tickets( $profileuser ) {
    ?>

    <table class="form-table">
        <tr>
            <td colspan="2"><?php _e('Personal') ?></td>
        </tr>
        <tr>
            <th>
                <label for="nombre">熊币</label></th>
            <td>
                <?php echo  gp_orders_get_total_coin_for_user( $profileuser->ID );?>
            </td>
        </tr>
        <tr>
            <th>
                <label for="nombre">赠币</label></th>
            <td>
                <?php echo  gp_orders_get_tickets_total_fee( $profileuser->ID );?>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'edit_user_profile', 'gp_books_user_coins_and_tickets', 10, 1 );