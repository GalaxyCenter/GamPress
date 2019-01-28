<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/22
 * Time: 11:13
 */

/** 获取一个活动明细 */
function gp_games_activity_detail() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'activity' )
        || ! gp_action_variable_is(0, 'detail' ) )
        return false;

    $activity_id = gp_action_variable( 1 );
    $activity = gp_games_get_activity( $activity_id );

    ajax_die( 0, '', $activity );
}
add_action( 'gp_actions', 'gp_games_activity_detail' );

/** 加入一个组 */
function gp_games_groups_join() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'groups' )
            || ! gp_action_variable_is(0, 'join' ) )
        return false;

    if ( !is_user_logged_in() ) {
        ajax_die( 1, '未登录' );
    } else {
        $wechat = new GP_Sns_OAuth_Wechat();
        $is_subscribe = $wechat->is_subscribe( gp_sns_get_sns_user_id() );
        if ( $is_subscribe == false ) {
            ajax_die( 5, '您未关注公众号' );
        }
        $activity_id = gp_action_variable( 1 );
        $user_id = gp_loggedin_user_id();
        $group_id = $_GET['group_id'];

        if ( !GP_Games_Group::exists( $group_id ) ) {
            ajax_die( 2, '该组不存在' );
        } else if ( gp_games_groups_is_user_member( $activity_id, $group_id, $user_id ) ) {
            ajax_die( 3, '已经加入过该组' );
        } else {
            $join_count = gp_games_groups_get_user_join_count( $user_id, $activity_id );
            $max_count = gp_games_activities_get_meta( $activity_id, 'user_join_group_max', true );

            if ( $join_count >= $max_count ) {
                ajax_die( 4, '加入组的数量已经超限制' );
            } else {
                $inviter_id = $_GET['inviter_id'];

                gp_games_groups_join_group( $activity_id, $group_id, $user_id, $inviter_id );
                $datas = array(
                    'user' => array( 'name' => gp_core_get_user_displayname( $user_id ),
                        'avatar' => gp_get_sns_user_avatar( $user_id ) )
                );
                ajax_die( 0, '成功加入', $datas );
            }
        }
    }
}
add_action( 'gp_actions', 'gp_games_groups_join' );

/** 创建一个组 */
function gp_games_groups_create() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'groups' )
        || ! gp_action_variable_is(0, 'create' ) )
        return false;

    if ( !is_user_logged_in() ) {
        ajax_die( 1, '', '' );
    } else {
        $activity_id = gp_action_variable( 1 );
        $user_id = gp_loggedin_user_id();

        $user_count = gp_games_groups_get_total_count( $user_id, $activity_id );
        $max_count = gp_games_activities_get_meta( $activity_id, 'user_create_group_max', true );

        if ( $user_count < $max_count ) {
            $group_id = gp_games_update_group( array(
                'activity_id' => $activity_id,
                'name'        => gp_core_get_user_displayname( $user_id )
            ) );

            ajax_die( 0, '', array( 'group_id' => $group_id ) );
        } else {
            ajax_die( 2, "只能创建{$max_count}次" );
        }
    }
}
add_action( 'gp_actions', 'gp_games_groups_create' );

/** 获取一个组的明细 */
function gp_games_groups_detail() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'groups' )
        || ! gp_action_variable_is(0, 'detail' ) )
        return false;

    $activity_id = gp_action_variable( 1 );
    $user_id = gp_loggedin_user_id();

    $group_id = isset( $_GET['group_id'] ) ? $_GET['group_id'] : '';
    $group = gp_games_groups_get_group( $group_id );
    if ( empty( $group ) ) {
        $datas = gp_games_groups_get_groups( array( 'activity_id' => $activity_id, 'owner_id' => $user_id ) );
        if ( count( $datas['items'] ) ) {
            $group_id = $datas['items'][0]->id;
            $group = gp_games_groups_get_group( $group_id );
        } else {
            ajax_die( 1, '' );
        }
    }
    $group->member_count = gp_games_groups_get_total_member_count( $group_id );
    $group->is_creator = $user_id == $group->owner_id;
    $datas = gp_games_get_group_members( array(
        'group_id'  => $group_id,
        'per_page'  => 128
    ) );
    $group->members = array();
    foreach ( $datas['items'] as $item ) {
        $group->members[] = array( 'name' => gp_core_get_user_displayname( $item->user_id ), 'avatar' => gp_get_sns_user_avatar( $item->user_id ) );
    }

    $max_members = gp_games_activities_get_meta( $group->activity_id, 'group_max_members', true );
    $group->max_members = $max_members;
    $group->allow_join = $group->member_count < $max_members;// 当前成员数量 大于 最大成员数就返回false,不允许加入该组
    $group->is_complete = gp_users_get_meta( $group->owner_id, 'activity_is_complete', false );
    // items
    $items = array();
    if ($group->is_creator == true) {
        $complete_conditions = gp_games_activities_get_meta( $activity_id, 'user_complete_conditions', true );
        $complete_conditions = explode(',',$complete_conditions );

        $items['item_is_empty'] = true;
        for($i = 0; $i<count($complete_conditions); $i++) {
            $meta_key = 'item_' . $complete_conditions[$i];
            $item_count = gp_games_activities_get_meta( $activity_id, $meta_key, true );

            // 是否满足条件
            $items['item_' . $i . '_satisfy'] = $group->member_count >= $complete_conditions[$i];
            $items['item_' . $i] = $item_count > 0;

            if ( $items['item_' . $i] == true ) {
                $items['item_is_empty'] = false;
            }
        }
    }

    // user
    $join_count = gp_games_groups_get_user_join_count( $user_id, $group->activity_id );//// 当前用户加入的组数量
    $max_count = gp_games_activities_get_meta( $group->activity_id, 'user_join_group_max', true ); // 用户最多能加入的数量
    $user = array();
    $user['join_count'] = $join_count;
    $user['user_id'] = $user_id;
    $user['allow_join'] = $join_count < $max_count; // 当前用户加入组的数量大于最大加入的数量,则不允许加入任意组
    $user['is_complete'] = gp_users_get_meta( $user_id, 'activity_is_complete', false ); // 是否完成游戏

    if ( !$group->is_creator ) { // 当前用户不是当前组的创建者
        $datas = gp_games_groups_get_groups( array( 'activity_id' => $activity_id, 'owner_id' => $user_id ) );
        if ( count( $datas['items'] ) ) {
            $group_id = $datas['items'][0]->id;
            $user['group_id'] = $group_id;
        } else {
            $user['group_id'] = 0;
        }
    }
    ajax_die( 0, '', array( 'group' => $group, 'user' => $user, 'items' => $items ) );
}
add_action( 'gp_actions', 'gp_games_groups_detail' );

function gp_game_users_contact_save() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'user' )
        || ! gp_action_variable_is(0, 'contact' )
        || ! gp_action_variable_is(1, 'save' ) )
        return false;

    $activity_id = gp_action_variable( 2 );
    $group_id = $_GET['group_id'];
    $user_id = gp_loggedin_user_id();
    $members = gp_games_groups_get_total_member_count( $group_id );
    $max_members = gp_games_activities_get_meta( $activity_id, 'group_max_members', true );
    $group = gp_games_groups_get_group( $group_id );

    if ( $user_id == 0 ) {
        ajax_die(1, '未登录');
        exit;
    } elseif ( $user_id != $group->owner_id ) {
        ajax_die(2, '您不是该组的创建者' );
        exit;
    } elseif ( $members < $max_members ) {
        ajax_die(3, '不满足条件');
        exit;
    } elseif ( gp_users_get_meta( $user_id, 'activity_is_complete', false ) == true ) {
        ajax_die(4, '已经领取过了');
        exit;
    } else {
        $complete_conditions = gp_games_activities_get_meta( $activity_id, 'user_complete_conditions', true );
        $complete_conditions = explode(',',$complete_conditions );

        $i = count($complete_conditions) - 1;
        $meta_key = 'item_' . $complete_conditions[$i];
        $item_count = gp_games_activities_get_meta( $activity_id, $meta_key, true );

        // 是否满足条件
        if ( $item_count <= 0) {
            ajax_die( 5, '奖品已经领取完' );
            exit;
        }
        // 设置用户完成游戏
        update_user_meta( $user_id, 'activity_is_complete', true );
        // 减少奖品数量
        gp_games_activities_update_meta( $activity_id, $meta_key, $item_count - 1 );

        $contact_user_name = isset( $_POST['contact_user_name'] ) ? $_POST['contact_user_name'] : '';
        $contact_phone     = isset( $_POST['contact_phone'] ) ? $_POST['contact_phone'] : '';

        if ( !empty( $contact_user_name ) )
            update_user_meta( $user_id, 'contact_user_name', $contact_user_name );

        if ( !empty( $contact_phone ) )
            update_user_meta( $user_id, 'contact_phone', $contact_phone );

        $order_id = $user_id . date('YmdHis') . rand( 1000, 9999 );
        gp_orders_update_order( array(
            'order_id'        => $order_id,
            'product_id'      => $group_id,
            'item_id'         => $activity_id,
            'user_id'         => $user_id,
            'price'           => 0,
            'create_time'     => gp_core_current_time(),
            'quantity'        => 1,
            'total_fee'       => 0,
            'status'          => GP_ORDER_PAID,
            'type'            => 'contact' ) );

        ajax_die( 0, '' );
    }
}
add_action( 'gp_actions', 'gp_game_users_contact_save' );

/** 发红包 */
function gp_games_activity_send_pack() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'activity' )
        || ! gp_action_variable_is(0, 'send_pack' ) )
        return false;

    $activity_id = gp_action_variable( 1 );
    $group_id = $_GET['group_id'];
    $item = $_GET['item'];
    $user_id = gp_loggedin_user_id();
    $members = gp_games_groups_get_total_member_count( $group_id );
    $complete_conditions = gp_games_activities_get_meta( $activity_id, 'user_complete_conditions', true );
    $complete_conditions = explode(',',$complete_conditions );
    $group = gp_games_groups_get_group( $group_id );

    if ( $user_id == 0 ) {
        ajax_die(1, '未登录');
        exit;
    } elseif ( $user_id != $group->owner_id ) {
        ajax_die(2, '您不是该组的创建者');
        exit;
    } elseif ( $members < $complete_conditions[$item] ) {
        ajax_die( 3, '不满足条件' );
        exit;
    } elseif ( gp_users_get_meta( $user_id, 'activity_is_complete', false ) == true ) {
        ajax_die(4, '已经领取过了');
        exit;
    } else {
        $wechat_pack_fee = gp_games_activities_get_meta( $activity_id, 'wechat_pack_fee', true ) ;
        $wechat_pack_fee = explode( ',', $wechat_pack_fee );
        $meta_key = 'item_' . $complete_conditions[$item];
        $item_count = gp_games_activities_get_meta( $activity_id, $meta_key, true );

        if ( $item_count <= 0) {
            ajax_die( 5, '奖品已经领取完' );
            exit;
        }
        $send_pack_fee = $wechat_pack_fee[$item];
        $order_id = $user_id . date('YmdHis') . rand( 1000, 9999 );

        $pay = new GP_Pays_Wechat_Pack();
        $result = $pay->do_pay( array(
            'order_id'          => $order_id,
            'sender'            => gp_games_activities_get_meta( $activity_id, 'wechat_pack_sender', true ),
            'product_fee'       => $send_pack_fee * 100,
            'wishing'           => gp_games_activities_get_meta( $activity_id, 'wechat_pack_wishing', true ),
            'product_name'      => gp_games_activities_get_meta( $activity_id, 'wechat_pack_wishing', true ),
            'wxappid'           => gp_games_activities_get_meta( $activity_id, 'wechat_app_id', true ),
            'mch_id'            => gp_games_activities_get_meta( $activity_id, 'wechat_mch_id', true ),
            'wechat_key'        => gp_games_activities_get_meta( $activity_id, 'wechat_key', true )
        ) );
        if ( !$result ) {
            ajax_die( 6, '微信红包已发完' );
            exit;
        }
        // 设置用户完成游戏
        update_user_meta( $user_id, 'activity_is_complete', true );

        // 减少奖品数量
        gp_games_activities_update_meta( $activity_id, $meta_key, $item_count - 1 );

        // 创建已经支付的一个订单
        gp_orders_update_order( array(
            'order_id'        => $order_id,
            'product_id'      => $group_id,
            'item_id'         => $activity_id,
            'user_id'         => $user_id,
            'price'           => $send_pack_fee,
            'create_time'     => gp_core_current_time(),
            'quantity'        => 1,
            'total_fee'       => $send_pack_fee,
            'status'          => GP_ORDER_PAID,
            'type'            => 'wechat_pack' ) );
        ajax_die( 0, '' );
    }
}
add_action( 'gp_actions', 'gp_games_activity_send_pack' );

function gp_games_activity_orders() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'activity' )
        || ! gp_action_variable_is(0, 'orders' ) )
        return false;

    $activity_id = gp_action_variable( 1 );

    $datas = gp_orders_get_orders( array(
        'item_id'         => $activity_id,
        'page'            => 1,
        'order'           => 'DESC',
        'per_page'        => 100
    ) );

    foreach( $datas['items'] as $item ) {
        $item->user_name = gp_core_get_user_displayname( $item->user_id );
        $item->user_avatar = gp_get_sns_user_avatar( $item->user_id );
    }
    ajax_die( 0, '', $datas );
}
add_action( 'gp_actions', 'gp_games_activity_orders' );
remove_action( 'template_redirect', 'redirect_canonical' );

/** 判断是否有抽奖机会 */
function gp_games_lottery_check() {
    if (!gp_is_games_component() || !gp_is_current_action('lottery')
        || !gp_action_variable_is(0, 'check')
    )
        return false;

    $user_id = gp_loggedin_user_id();
    if ( empty( $user_id ) ) {
        ajax_die(1, '没登录', false);
        return false;
    }

    $activity_id = gp_action_variable(1);
    $count = gp_users_get_meta($user_id, 'lottery_count_' . $activity_id, 0, true);
    ajax_die(0, '', $count);
}
add_action( 'gp_actions', 'gp_games_lottery_check' );

/** 抽奖 */
function gp_games_lottery_get() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'lottery' )
        || ! gp_action_variable_is(0, 'get' ) )
        return false;
    $user_id = gp_loggedin_user_id();
    if ( empty( $user_id ) ) {
        ajax_die(1, '没登录', false);
        return false;
    }

    $activity_id = gp_action_variable( 1 );
    $count = gp_users_get_meta( $user_id, 'lottery_count_' . $activity_id, 0, true );
    if ($count == 0) {
        ajax_die( 2, '刮刮卡已用完啦~', false );
        return false;
    }

    global $wpdb;

    $base_rate = $wpdb->get_var( $wpdb->prepare( "select sum(rate) from ds_gp_lotteries where activity_id = %d", $activity_id ));
    $luck_num = mt_rand(1, $base_rate);

    $base_rate = 0;
    $lotteries = $wpdb->get_results( "select * from ds_gp_lotteries order by id asc" );
    $luck_lott = false;

    foreach($lotteries as $lott) {
        $base_rate += $lott->rate;
        if ($luck_num <= $base_rate && $lott->count > 0) {
            $luck_lott = $lott;
            break;
        }
    }
    if ($luck_lott == false) {
        ajax_die( 3, '系统粗错', $luck_num );
        return;
    }

    // 添加中奖人
    $wpdb->query( $wpdb->prepare( "insert into ds_gp_user_in_lotteries (activity_id, user_id, lottery_id) values(%d, %d, %d) ", $activity_id, $user_id, $luck_lott->id ) );
    $item_id = $wpdb->insert_id;
    // 奖品次数-1
    $wpdb->query( $wpdb->prepare( "update ds_gp_lotteries set `count` = `count` -1 where id = %d", $luck_lott->id ) );
    // 抽奖次数-1
    update_user_meta( $user_id, 'lottery_count_1', $count - 1 );
    if ( ( $luck_lott->type & GP_GAME_LOTTERY_TICKET ) == GP_GAME_LOTTERY_TICKET ) {
        gp_orders_add_ticket( array(
            'id'                => 0,
            'name'              => $luck_lott->message,
            'user_id'           => $user_id,
            'fee'               => $luck_lott->point,
            'type'              => 'ticket',
            'expired'           => gp_format_time( time() + ( 86400 * 90 ) ),
            'create_time'       => gp_format_time( time() )
        ) );
    }
    $luck_lott->{'item_id'} = $item_id;
    unset($luck_lott->rate);
    unset($luck_lott->rcountank);
    unset($luck_lott->rank);
    ajax_die( 0, '', $luck_lott );
}
add_action( 'gp_actions', 'gp_games_lottery_get' );

/** 添加获奖人信息 */
function gp_games_lottery_contact() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'lottery' )
        || ! gp_action_variable_is(0, 'contact' ) )
        return false;

    $user_id = gp_loggedin_user_id();
    $activity_id = gp_action_variable( 1 );
    $lottery_id = $_POST['lottery_id'];
    $item_id = $_POST['item_id'];

    global $wpdb;
    $id = $wpdb->get_var( $wpdb->prepare( "select id from ds_gp_user_in_lotteries where activity_id = %d and user_id = %d and lottery_id = %d limit 1", $activity_id, $user_id, $lottery_id ) );
    if (!is_numeric($id)) {
        ajax_die( 1, '你不是中奖用户', false );
    }
    $lottery = $wpdb->get_row( $wpdb->prepare( "select * from ds_gp_lotteries where id = %d limit 1", $lottery_id ) );
    $data = array();
    $data_format = array();

    $data['item_id'] = $item_id;
    $data['user_id'] = $user_id;
    array_push( $data_format, '%s' );
    // 姓名
    if ( ($lottery->type & 0x0100 ) == 0x0100 ) {
        $data['user_name'] = isset($_POST['user_name']) ? $_POST['user_name'] : '';
        array_push( $data_format, '%s' );
    }

    // 手机
    if ( ( $lottery->type & 0x0200 ) == 0x0200 ) {
        $data['phone'] = isset($_POST['phone']) ? $_POST['phone'] : '';
        array_push( $data_format, '%s' );
    }

    // 地址
    if ( ( $lottery->type & 0x0400 ) == 0x0400 ) {
        $data['address'] = isset($_POST['address']) ? $_POST['address'] : '';
        array_push( $data_format, '%s' );
    }

    // qq
    if ( ( $lottery->type & 0x0800 ) == 0x0800 ) {
        $data['mail'] = isset($_POST['qq']) ? $_POST['qq'] : '';
        array_push( $data_format, '%s' );
    }
    $wpdb->insert( 'ds_gp_contacts', $data, $data_format );

    ajax_die( 0, '成功记录', false );
}
add_action( 'gp_actions', 'gp_games_lottery_contact' );

/** 获奖项目 */
function gp_games_lottery_my() {
    if ( !gp_is_games_component() || ! gp_is_current_action( 'lottery' )
        || ! gp_action_variable_is(0, 'my' ) )
        return false;

    $user_id = gp_loggedin_user_id();
    $activity_id = gp_action_variable( 1 );
    global $wpdb;
    $uils = $wpdb->get_results( $wpdb->prepare( "select uil.* from ds_gp_user_in_lotteries uil join ds_gp_lotteries l on l.id = uil.lottery_id where (l.`type` & 0x04) = 0x04 and uil.activity_id = %d and uil.user_id = %d", $activity_id, $user_id ) );
    foreach ($uils as $uil) {
        $uil->lottery = $wpdb->get_row( $wpdb->prepare( "select id, `name`,`type` from ds_gp_lotteries where id = %d", $uil->lottery_id ) );

        if ( ( $uil->lottery->type & GP_GAME_LOTTERY_CONTACT ) == GP_GAME_LOTTERY_CONTACT ) {
            $uil->contact = $wpdb->get_var( $wpdb->prepare( "select id from ds_gp_contacts where item_id = %d and user_id = %d limit 1", $uil->id, $user_id ) );
            if (is_numeric($uil->contact))
                $uil->contact = -1;
            else
                $uil->contact = 0;
        } else {
            $uil->contact = -1;
        }

    }
    ajax_die( 0, '', $uils );
}
add_action( 'gp_actions', 'gp_games_lottery_my' );

//function gp_games_orders_pays_success( $order_id, $total_fee, $notify_time ) {
//    if ($total_fee == 10)
//        return;
//
//    $order = gp_orders_get_order( $order_id );
//    if ( $total_fee == 30 ) {
//        $count = 1;
//    } else if ( $total_fee == 50 ) {
//        $count = 2;
//    } else if ( $total_fee == 100 ) {
//        $count = 4;
//    } else if ( $total_fee == 200 ) {
//        $count = 9;
//    } else if ( $total_fee == 300 ) {
//        $count = 12;
//    }
//    $count = $count + gp_users_get_meta($order->user_id, 'lottery_count_1', 0, true);
//    update_user_meta( $order->user_id, 'lottery_count_1', $count );
//}
//add_action( 'gp_orders_pays_success', 'gp_games_orders_pays_success', 10, 3);

// 充值活动 回调提示
function gp_game_after_pay_success_activity1123( $order_id ) {
    $order = gp_orders_get_order( $order_id );
    $total_fee = $order->price;

    if ( is_apple_mobile_browser() && !is_weixin_browser() )
        $link = 'mqqwpa://im/chat?chat_type=wpa&uin=1022301265&version=1&src_type=web&web_src=oicqzone.com';
    else
        $link = 'http://wpa.qq.com/msgrd?v=3&uin=1022301265&site=qq&menu=yes';

    if ( $total_fee == 100 ) {
        $msg = "您已获得腾讯视频30天VIP，请联系<a href='$link'>客服</a>QQ 1022301265以便我们给您发放奖品。";
    } else if ( $total_fee == 200 ) {
        $msg = "您已获得腾讯视频30天VIP，请联系<a href='$link'>客服</a>QQ 1022301265以便我们给您发放奖品。";
    } else if ( $total_fee == 500 ) {
        $msg = "您已获得腾讯视频30天VIP，请联系<a href='$link'>客服</a>QQ 1022301265以便我们给您发放奖品。";
    } else if ( $total_fee == 0.01 ) {
        $msg = "您已获得腾讯视频30天VIP，请联系<a href='$link'>客服</a>QQ 1022301265以便我们给您发放奖品。";
    }

    echo "<h3>$msg</h3>";
    gp_core_add_message( $msg, 'success' );
}
//add_action( 'gp_after_pay_success', 'gp_game_after_pay_success_activity1123', 10, 1);