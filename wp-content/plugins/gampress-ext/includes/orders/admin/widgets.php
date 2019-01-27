<?php

function gp_orders_admin_dashboard_last_7days_total_fee_box() {
    global $wpdb;

    $total_fee = $wpdb->get_var( "select sum(price) as totals from ds_gp_orders where `type`= 'recharge' and status = 4 limit 1" );

    $cur_mon_total_fee = $wpdb->get_var( "select sum(price) as totals from ds_gp_orders where DATE_FORMAT( create_time, '%Y%m' ) = DATE_FORMAT( CURDATE( ) , '%Y%m' ) and  `type`= 'recharge' and status = 4 limit 1" );

    $paged_items = $wpdb->get_results( "select DATE_FORMAT(create_time,'%Y%m%d') days,  sum(price) as totals from ds_gp_orders where `type`= 'recharge' and status = 4 group by days order by days DESC limit 9" );

    echo "<div id=\"orders-7days-total-fee\"><div class=\"orders-block\"><h3>总收入:{$total_fee} / 当月收入{$cur_mon_total_fee}</h3><ul>";
    echo '<style>#orders-7days-total-fee span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->days}</span>{$item->totals}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_uncharge_order() {
    global $wpdb;

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_uncharge_order', 'gp_widgets', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results("SELECT DATE_FORMAT(create_time,'%Y%m%d') days, count(price) as c, `price` FROM `adaixiong`.`ds_gp_orders` WHERE type = 'recharge' and status = 3 GROUP BY days, `price` ORDER BY `id` DESC LIMIT 0, 10;");
        wp_cache_set( 'gp_orders_admin_dashboard_uncharge_order', $paged_items, 'gp_widgets', 1800 );
    }
    echo "<div id=\"new_charge_order\"><div class=\"orders-block\"><ul>";
    echo '<style>#new_charge_order span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->days}</span>{$item->price} / {$item->c} </li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_paid_order() {
    global $wpdb;

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_paid_order', 'gp_widgets', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results("SELECT DATE_FORMAT(create_time,'%Y%m%d') days, count(price) as c, `price` FROM `adaixiong`.`ds_gp_orders` WHERE type = 'recharge' and status = 4 GROUP BY days, `price` ORDER BY `id` DESC LIMIT 0, 15;");
        wp_cache_set( 'gp_orders_admin_dashboard_paid_order', $paged_items, 'gp_widgets', 7200 );
    }

    echo "<div id=\"new_charge_order\"><div class=\"orders-block\"><ul>";
    echo '<style>#new_charge_order span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {
        if ( $item->price == '50' ) {
            $coin = $item->c * 1000;
        } else {
            $coin = '';
        }
        echo "<li><span>{$item->days}</span>{$item->price} / {$item->c} / {$coin} </li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_consumption_bill() {
    global $wpdb;

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_consumption_bill', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results("SELECT DATE_FORMAT(create_time,'%Y%m%d') days, sum(fee) as fee FROM `adaixiong`.`ds_gp_coin_bills` WHERE type = 2 GROUP BY days ORDER BY `days` DESC  LIMIT 0,7;");
        wp_cache_set( 'gp_orders_admin_dashboard_order_consumption_bill', $paged_items, 'gp_widgets', 86400 );
    }
    echo "<div id=\"consumption_bill\"><div class=\"orders-block\"><ul>";
    echo '<style>#consumption_bill span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->days}</span>{$item->fee}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_consumption_bill_users() {
    global $wpdb;

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_consumption_bill_users', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results("SELECT DATE_FORMAT(create_time,'%Y%m%d') days, count(DISTINCT(user_id)) as c FROM `adaixiong`.`ds_gp_coin_bills` WHERE type = 2 GROUP BY days ORDER BY `days` DESC  LIMIT 0,7;");
        wp_cache_set( 'gp_orders_admin_dashboard_order_consumption_bill_users', $paged_items, 'gp_widgets', 86400 );
    }
    echo "<div id=\"consumption_bill\"><div class=\"orders-block\"><ul>";
    echo '<style>#consumption_bill span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->days}</span>{$item->c}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_consumption_ticket() {
    global $wpdb;

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_consumption_ticket', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results( "SELECT DATE_FORMAT(create_time,'%Y%m%d') days, sum(fee) as fee FROM `adaixiong`.`ds_gp_coin_bills` WHERE type = 4 GROUP BY days ORDER BY `days` DESC  LIMIT 0,7;" );
        wp_cache_set( 'gp_orders_admin_dashboard_order_consumption_ticket', $paged_items, 'gp_widgets', 86400 );
    }

    echo "<div id=\"consumption_ticket\"><div class=\"orders-block\"><ul>";
    echo '<style>#consumption_ticket span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->days}</span>{$item->fee}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_2day_top_item() {
    global $wpdb;

    $gp = gampress();

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_2day_top_item_a', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results($wpdb->prepare("SELECT item_id, SUM(total_fee) as fee FROM {$gp->orders->table_name} WHERE item_id > 0 AND (`type` = 'ticket' or `type` = 'coin') AND date_format(`create_time`, '%%Y%%m%%d') = %s GROUP BY item_id ORDER BY fee DESC LIMIT %d", date("Ymd", strtotime("-1 day")), 10));
        wp_cache_set( 'gp_orders_admin_dashboard_order_2day_top_item_a', $paged_items, 'gp_widgets', 7200 );
    }
    echo "<div id=\"yestoday_top_item\"><div class=\"orders-block\"><h4>昨日</h4><ul>";
    echo '<style>#yestoday_top_item span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 200px;}</style>';
    foreach ( $paged_items as $item ) {
        $book = gp_books_get_book($item->item_id);
        echo "<li><span>{$book->title}</span>{$item->fee}</li>";
    }
    echo '</ul></div></div>';

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_2day_top_item_b', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results($wpdb->prepare("SELECT item_id, SUM(total_fee) as fee FROM {$gp->orders->table_name} WHERE item_id > 0 AND (`type` = 'ticket' or `type` = 'coin') AND date_format(`create_time`, '%%Y%%m%%d') = %s GROUP BY item_id ORDER BY fee DESC LIMIT %d", date("Ymd", strtotime("+0 day")), 10));
        wp_cache_set( 'gp_orders_admin_dashboard_order_2day_top_item_b', $paged_items, 'gp_widgets', 7200 );
    }
    echo "<div id=\"today_top_item\"><div class=\"orders-block\"><h4>今日</h4><ul>";
    echo '<style>#today_top_item span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 200px;}</style>';
    foreach ( $paged_items as $item ) {
        $book = gp_books_get_book($item->item_id);
        echo "<li><span>{$book->title}</span>{$item->fee}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_recharge_top() {
    global $wpdb;

    $paged_items = $wpdb->get_results( "select count(user_id) as c, user_id 
from ds_gp_orders
where type = 'recharge' and status = 4
group by user_id 
order by c DESC
LIMIT 10" );

    echo "<div id=\"order_top_price_user\"><div class=\"orders-block\">Top10 次数<h4></h4><ul>";
    echo '<style>#order_top_price_user span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 200px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->c}</span>用户Id:{$item->user_id}</li>";
    }
    echo '</ul></div></div>';

    $paged_items = $wpdb->get_results( "select sum(price) as price, user_id 
from ds_gp_orders
where type = 'recharge' and status = 4
group by user_id 
order by price DESC
LIMIT 10" );

    echo "<div id=\"order_top_count_user\"><div class=\"orders-block\">Top金额<h4></h4><ul>";
    echo '<style>#order_top_count_user span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 200px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->price}</span>用户Id:{$item->user_id}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_consumption_ticket_comfrom() {
    global $wpdb;

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_consumption_ticket_comfrom_a', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $sql = $wpdb->prepare("
    select u.`user_activation_key` frm, sum(price) price
from `ds_users` u
JOIN `ds_gp_orders` o on o.`user_id`  = u.`ID` 
WHERE date_format(o.`create_time`, '%%Y%%m%%d') = %s
and u.`user_activation_key` <> '' and o.`type` ='recharge' and o.`status`  =4
GROUP BY u.`user_activation_key` LIMIT %d", date("Ymd", strtotime("-1 day")), 10);

        $paged_items = $wpdb->get_results($sql);

        wp_cache_set( 'gp_orders_admin_dashboard_order_consumption_ticket_comfrom_a', $paged_items, 'gp_widgets', 86400);
    }

    echo "<div id=\"yestoday_top_item\"><div class=\"orders-block\"><h4>昨日充值</h4><ul>";
    echo '<style>#yestoday_top_item span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 200px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->frm}</span>{$item->price}</li>";
    }
    echo '</ul></div></div>';

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_consumption_ticket_comfrom_b', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $sql = $wpdb->prepare("
    select u.`user_activation_key` frm, sum(price) price
from `ds_users` u
JOIN `ds_gp_orders` o on o.`user_id`  = u.`ID` 
WHERE date_format(o.`create_time`, '%%Y%%m%%d') = %s
and u.`user_activation_key` <> '' and o.`type` ='recharge' and o.`status`  =4
GROUP BY u.`user_activation_key` LIMIT %d", date("Ymd"), 10);


        $paged_items = $wpdb->get_results($sql);
        wp_cache_set( 'gp_orders_admin_dashboard_order_consumption_ticket_comfrom_b', $paged_items, 'gp_widgets', 86400);
    }
    echo "<div id=\"yestoday_top_item\"><div class=\"orders-block\"><h4>今日充值</h4><ul>";
    echo '<style>#yestoday_top_item span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 200px;}</style>';
    foreach ( $paged_items as $item ) {
        echo "<li><span>{$item->frm}</span>{$item->price}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_consumption_order_new_old() {
    global $wpdb;

    $paged_items = wp_cache_get( 'gp_orders_admin_dashboard_order_consumption_order_new_old', 'gp_widgets' );
    if ( empty( $paged_items ) ) {
        $paged_items = $wpdb->get_results("select DATE_FORMAT(create_time,'%Y%m%d') days,  sum(price) as totals from ds_gp_orders where `type`= 'book' and status = 4 group by days order by days DESC limit 7");
        wp_cache_set( 'gp_orders_admin_dashboard_order_consumption_order_new_old', $paged_items, 'gp_widgets', 86400);

    }
    echo "<div id=\"mption_order_new_old\"><div class=\"orders-block\"><h3></h3><ul>";
    echo '<style>#orders-7days-total-fee span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $paged_items as $item ) {
        $sql = $wpdb->prepare( "select DISTINCT(user_id) from ds_gp_orders where `type`= 'book' and status = 4 and to_days(pay_time) = to_days(%s)  ORDER BY user_id", $item->days);
        $user_items = $wpdb->get_col( $sql );
        $day_total_new = 0;
        $day_total_old = 0;
        $day_today_total = 0;
        foreach ( $user_items as $user ) {
            $first_pay_time = gp_users_get_meta( $user, 'first_pay_time', false, true );
            //echo $user . '%' . $first_pay_time . '#%' . date('Ymd', $first_pay_time) . '--------' . $item->days . '<br/>';
            if(date('Ymd', $first_pay_time) == $item->days ) {
                $day_total_new++;
            }
            $day_today_total++;
        }
        $day_total_old = $day_today_total - $day_total_new;
        // 取出当日所有订单,在计算每个订单的用户首次支付时间
        echo "<li><span>{$item->days}</span>-- 新用户{$day_total_new} 老用户:{$day_total_old}</li>";
    }
    echo '</ul></div></div>';
}

function gp_orders_admin_dashboard_order_come_from() {
    global $wpdb;

    $froms = array(
        'adx-zjyd'          => '个人中心最近阅读',
        'adaixiong_zjyd'    => '首页最近阅读',
        'adxyd'             => '微信关注推荐',
        'adaixiong_syjdt'   => '首页焦点',
        'adaixiong_zbtj'    => '重磅推荐',
        'singlemessage'     => '微信链接',
        'adx-rank-top-views-cm' => '排行榜页面',
        'adx-rank-top-seller-cm'    => '排行榜页面',
        'adx-rank-top-seller-lm'    => '排行榜页面',
        'adaixiong_rmjx'    => '热门精选',
        'adx-gzh'           => '公众号搜索',
        'adaixiong_jpqb'    => '金牌全本',
        'adaixiong_jctj'    => '精彩推荐',
        'adx-jxtj'          => '精彩推荐2',
        'books'             => '作品列表页',
        'adaixiong_dsjz'    => '大神佳作',
        'adx-cnxh'          => '95%的人都在看',
        'cnxh'              => '95%的人都在看',
        'adx-bookmarks'     => '个人中心-追书',
        'adx-history'       => '个人中心-最近阅读'
    );

    $sql = "SELECT `come_from`, sum(`price` ) as price FROM `ds_gp_orders` WHERE (`type`='coin' or `type`='ticket') and status = 4 and `come_from` != '' and to_days(create_time) = to_days(now()) GROUP BY `come_from` ORDER BY price desc";
    $items = $wpdb->get_results( $sql );
    echo "<div id=\"new_charge_order\"><div class=\"orders-block\"><ul>";
    echo '<style>#new_charge_order span{color: #72777c;display: inline-block;margin-right: 5px;min-width: 150px;}</style>';
    foreach ( $items as $item ) {
        if (isset($froms[$item->come_from]))
            echo "<li><span>{$froms[$item->come_from]}</span>{$item->price} </li>";
        else
            echo "<li><span>{$item->come_from}</span>{$item->price} </li>";
    }
    echo '</ul></div></div>';
}

function wp_dashboard_order_setup() {
    if (!is_super_admin())
        return;

    date_default_timezone_set('Etc/GMT-8');

    wp_add_dashboard_widget('order_last_7days_total_fee_box', '最近7日收入', 'gp_orders_admin_dashboard_last_7days_total_fee_box');
    //wp_add_dashboard_widget('order_uncharge_order', '未支付订单数', 'gp_orders_admin_dashboard_uncharge_order');
    wp_add_dashboard_widget('order_charge_paid_order', '每天支付费用数', 'gp_orders_admin_dashboard_paid_order');

    // bill
    wp_add_dashboard_widget('order_consumption_bill', '最近7日消费熊币', 'gp_orders_admin_dashboard_order_consumption_bill');
    //wp_add_dashboard_widget('order_consumption_bill_users', '最近7日消费熊币人数', 'gp_orders_admin_dashboard_order_consumption_bill_users');
    wp_add_dashboard_widget('order_consumption_ticket', '最近7日消费增币数', 'gp_orders_admin_dashboard_order_consumption_ticket');
    //wp_add_dashboard_widget('order_consumption_ticket_comfrom', '昨日&今日来源', 'gp_orders_admin_dashboard_order_consumption_ticket_comfrom');
    //wp_add_dashboard_widget('order_consumption_order_new_old', '最近7日新老用户消费', 'gp_orders_admin_dashboard_order_consumption_order_new_old');


    // 昨日销量top 10
    wp_add_dashboard_widget('order_2day_top_item', '昨日&今日 销量Top10', 'gp_orders_admin_dashboard_order_2day_top_item');
    //wp_add_dashboard_widget('order_recharge_top', '充值top10用户', 'gp_orders_admin_dashboard_order_recharge_top');

    wp_add_dashboard_widget('order_come_from', '今日来源收入', 'gp_orders_admin_dashboard_order_come_from');
}
add_action( 'wp_dashboard_setup', 'wp_dashboard_order_setup' );