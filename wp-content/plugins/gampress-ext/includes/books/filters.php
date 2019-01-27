<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/30
 * Time: 14:51
 */

function gp_books_pays_adaixiong( $name ) {
    return new GP_Books_Pays_Adaixiong();
}
add_filter( 'gp_pays_adaixiong', 'gp_books_pays_adaixiong' );

function gp_get_books_orders_top_item( $items ) {
    foreach ( $items as $item ) {
        $book_id = $item->item_id;
        $item->book = gp_books_get_book( $book_id );
    }
    return $items;
}
add_filter( 'gp_get_orders_top_item', 'gp_get_books_orders_top_item' );

function gp_books_get_title_parts( $gp_title_parts, $seplocation ) {
    if ( gp_is_books_component() && !gp_is_current_action( 'chapters' ) && gp_is_single_item() ) {
        $book = gp_books_get_current_book();

        $gp_title_parts[1] = $book->author . '著';
    } else if ( gp_is_books_component()
         && gp_is_current_action('chapters')
         && gp_is_single_item()
         && $chapter_id = GP_Books_Chapter::chapter_exists( gp_books_get_current_book()->id, gp_action_variable(0), 0 ) ) {

        $chapter = gp_books_get_chapter( $chapter_id );

        if ( 'right' === $seplocation ) {
            array_unshift( $gp_title_parts, $chapter->title );
        } else {
            $gp_title_parts[] = $chapter->title;
        }
    }

    return $gp_title_parts;
}
add_filter( 'gp_get_title_parts', 'gp_books_get_title_parts', 10, 2 );

function gp_sns_autologin_redirect( $redirect, $user_id ) {
    $redirect2 = isset( $_COOKIE['redirect'] ) ? $_COOKIE['redirect'] : '';
    $tab2 = isset( $_COOKIE['tab'] ) ? $_COOKIE['tab'] : '';

    if ( $redirect2 == 'user' ) {
        $redirect = gp_core_get_user_domain( $user_id ) . $tab2;
    }
    return $redirect;
}
add_filter( 'sns_autologin_redirect', 'gp_sns_autologin_redirect', 10, 2 );

function gp_books_sns_sub_proc_event( $content, $object ) {
    $user_login = 'wechat_' . $object->FromUserName;
    $oauth_user = get_user_by( 'login', $user_login );

    switch ($object->Event) {
        case "subscribe":
            if ( $object->ToUserName == 'gh_035fc7f53229' ) { // 订阅号
                $ticket_link = 'http://www.adaixiong.com/games/ticket/subscribe/' . $object->FromUserName;
                $content = "关注并置顶『阿呆熊小说』，海量好书等你来看！\n点下方蓝色字一键看好书：\n◆<a href='http://www.adaixiong.com/'>「万本好书随便看」</a>\n男频好书： \n◆<a href='http://www.adaixiong.com/books/11132/chapters/1180279/'>「最强男下属」</a>\n◆<a href='http://www.adaixiong.com/books/11092/chapters/1168478/'>「那些年我泡过的公司美女」</a>\n女频好书：\n◆<a href='http://www.adaixiong.com/books/11123/chapters/1175205/'>「同床异梦」</a>\n◆<a href='http://www.adaixiong.com/books/10785/chapters/1092067/'>「宁愿动情不入婚」</a>\n点击底部菜单拥有更多福利哦！\n\n<a href='$ticket_link'>福利：点击此处立即领取300币</a>";

            } else {
                if( !empty( $oauth_user ) ) {
                    $user_id = $oauth_user->ID;
                    $data = gp_books_get_book_history( $user_id );
                    $link = $data['link'];
                    $title = $data['title'];
                    $order_text = $data['order_text'];

                    $sub_content = "\n<a href='{$link}'>继续阅读《{$title}》{$order_text}</a>\n热门好书，快上车：";

                    // 简单放置在这里 获取性别， 后面要清理掉
                    $wechat = new GP_Sns_Wechat_Base();
                    $wechat->openid = $object->FromUserName;
                    $wechat->request_access_token();
                    $sns_user = $wechat->get_user_info();
                    update_user_meta( $oauth_user->ID, 'gender', $sns_user->gender );
                    update_user_meta( $oauth_user->ID, 'sns_user_avatar',  $sns_user->avatar );
                } else {
                    $sub_content = '';

                    GP_Log::INFO("创建用户：" . $object->FromUserName . '##############A' );
                    $wechat = new GP_Sns_Wechat_Base();
                    $wechat->openid = $object->FromUserName;
                    $wechat->request_access_token();
                    GP_Log::INFO("创建用户：" . $object->FromUserName . '##############B' );
                    $sns_user = $wechat->get_user_info();
                    GP_Log::INFO("创建用户：" . $object->FromUserName . '##############C' );
                    $user_id = gp_sns_signup_user( $sns_user, 'wechat' );

                    update_user_meta( $user_id, 'gender', $sns_user->gender );
                }

                $val = gp_users_get_meta( $user_id, 'wechat_subscribe_' . $wechat->app_id, false, true );
                $msg = '';
                if ( empty( $val ) ) {
                    gp_orders_add_ticket( array(
                        'id'                => 0,
                        'name'              => '关注送赠',
                        'user_id'           => $user_id,
                        'fee'               => 50,
                        'type'              => 'ticket',
                        'expired'           => gp_format_time( time() + ( 86400 * 3 ) ),
                        'create_time'       => gp_format_time( time() )
                    ) );
                    update_user_meta( $user_id, 'wechat_subscribe_' . $wechat->app_id, "1" );
                    $msg = "PS：我们已免费赠送您50呆熊币，祝您阅读愉快!\n\n";
                }
                $msg = $msg;// . "点击下方签到,每日可领取50币~";

                $gender = gp_users_get_meta( $user_id, 'gender', false, true );
                if ( $gender == 1 || $gender == 0 ) {
$book_links = "热推：<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10935%2Fchapters%2F1110447%2F?from=adxyd'>搬进个新小区，发现这里的邻居都有点怪</a>
1、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10916%2Fchapters%2F1104148%2F?from=adxyd'>一桩盗尸案牵扯出的惊天罪恶，毛骨悚然</a>
2、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10065%2Fchapters%2F1005691%2F?from=adxyd'>在女监做管教，见识到了监狱里面女人的疯狂</a>
3、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10023%2Fchapters%2F1000840%2F?from=adxyd'>揭秘民国第一骗子的职业生涯</a>
4、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10771%2Fchapters%2F1090847%2F?from=adxyd'>大庆油田诡事录：地下九百米深处的蛇血</a>";
                } else if ( $gender == 2 ) {
$book_links = "热推：<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11633%2Fchapters%2F1475744%2F?from=adxyd'>隔壁男邻居帮我认清相恋八年男友的真面目</a>
1、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10047%2Fchapters%2F1003335%2F?from=adxyd'>新婚第一天，婆婆直接来掀我和老公的被子</a>
2、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11632%2Fchapters%2F1475635%2F?from=adxyd'>他说像我这的小三最可怕，不要钱，只要命</a>
3、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10874%2Fchapters%2F1100732%2F?from=adxyd'>老公要杀我，我要在他动手之前先下手</a>
4、<a href='http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11126%2Fchapters%2F1176691%2F?from=adxyd'>说好了只联系工作，又为什么走进我的心</a>";
                }
                $content = "终于等到你！感谢关注『阿呆熊』，置顶公众号，下次不迷路哦~
{$sub_content}
点下方蓝字可以阅读更多精品内容~
{$book_links}

<a href='http://www.adaixiong.com/login?redirect=user&tab=recharge'>【点击此处一键充值】</a>

PS：我们已免费赠送您50呆熊币，祝您阅读愉快!";
            }
            $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
            break;
        case "unsubscribe":
            $content = "很遗憾你取消关注";
            $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
            break;
        case "CLICK":
            switch ($object->EventKey) {
                case 'banquanhezuo':
                    $content = '您好，版权合作请联系QQ：2486726375';
                    $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
                    break;
                case 'checkin':
//                    if ( gp_games_user_is_checkin( $oauth_user->ID ) ) {
//                        // '今天已经签到';
//                        do_action( 'gp_games_user_checkined', $oauth_user->ID );
//                        $content = "今日已签到领过呆熊币了，请明日继续签到哦~\n" ;
//                    } else {
//                        // '今天还没签到';
//                        gp_games_user_checkin( $oauth_user->ID );
//                        $content = "50呆熊币已到账，请明日继续签到领币哦~\n";
//                    }

                    $content = "签到功能下线了~\n" ;
                    $link = 'http://www.adaixiong.com/login?redirect=user&tab=bookmark/history';
                    $sub_content = "\n<a href='{$link}'>点我继续上次阅读》》</a>";
                    $content = $content . $sub_content;
                    $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
                    break;

                default:
                    $content = "感谢您的支持";
                    $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
                    break;
            }

            break;
    }

    return $content;
}
add_filter( 'gp_wehchats_precess_event', 'gp_books_sns_sub_proc_event', 10, 2 );

function gp_books_sns_sub_proc_text( $content, $object ) {
    $key_content = array(
        '充值'        => "您好，点击下方蓝字即可一键充值~\n<a href=\"http://www.adaixiong.com/login?redirect=user&tab=recharge\">现在充值最高可获赠33000呆熊币</a>，快点行动起来吧！\n充值遇到问题，可直接联系<a href=\"http://wpa.qq.com/msgrd?v=3&uin=1022301265&site=qq&menu=yes\">人工客服</a>。",
        '看书'        => "小主您好，点击下方蓝字即可：\n1、<a href=\"www.adaixiong.com\">阅读全站所有好书</a>;\n2、<a href=\"www.adaixiong.com/book_free\">免费看书</a>;\n祝您阅读愉快~",
        '客服'        => "点击右侧联系<a href=\"http://wpa.qq.com/msgrd?v=3&uin=1022301265&site=qq&menu=yes\">人工客服</a>。",
        '帮助'        => "小主有什么需要吗？\n1、充值问题请回复『充值』；\n2、阅读问题请回复『看书』；\n3、人工服务请回复『客服』直接咨询客服QQ；\n4、回复『帮助』可重新呼出本菜单；\n点击蓝字部分即可<a href=\"http://www.adaixiong.com/book_free\">免费看书</a>哦\n祝您阅读愉快~",

        '11'         => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10055%2Fchapters%2F1004285%2F?from=cyr">隐爱，情深深如许</a>',
        '12'         => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11138%2Fchapters%2F1187399%2F?from=cyr">愿情深不负你</a>',
        '13'         => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11056%2Fchapters%2F1160912%2F?from=cyr">腹黑老公的心尖宠</a>',
        '14'         => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10047%2Fchapters%2F1003446%2F?from=cyr">包子女，入戏别太深</a>',
        '15'         => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11138%2Fchapters%2F1187399%2F?from=wyn">愿情深不负你</a>',
        '16'         => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10047%2Fchapters%2F1003446%2F?from=wyn">包子女，入戏别太深</a>',

        '11691'      => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11691%2Fchapters%2F1508228%2F?from=adxyd">《爱你不及流年》</a>',
        '11633'      => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11633%2Fchapters%2F1475744%2F?from=zxfaq">《时光清浅，向爱则暖》</a>',
        '11656'      => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11656%2Fchapters%2F1495563%2F?from=adxyd">《被老公的男伴催眠》</a>',
        '11692'      => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11692%2Fchapters%2F1514536%2F?from=adxyd">《老公你不仁，休怪我不义》</a>',
        '11698'      => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F11698%2Fchapters%2F1515386%2F?from=adxyd">《你好，旧爱情敌》</a>',
        '10935'      => '点击阅读：<a href="http://www.adaixiong.com/links?http%3A%2F%2Fwww.adaixiong.com%2Fbooks%2F10935%2Fchapters%2F1110447%2F?from=adxyd">《生人勿禁》</a>',


    );
    $content = (string) $content;
    if ( array_key_exists( $content, $key_content ) ) {
        $content = $key_content[$content];

        $content = GP_Sns_Wechat_Subscribe::transmit_text($object, $content);
    } else if ( (int) $content > 0 ) {
        $id = (int) $content;
        $id -= GP_BOOK_BASE_INDEX;

        if ( GP_Books_Book::book_exists( $id ) ) {
            $book = gp_books_get_book( $id );
            $chapter = gp_books_get_first_chapter( $id );
            $link = gp_get_book_chapters_permalink( $chapter, 0 ) . '?from=adx-gzh';
            $pic_url = gp_root_domain() . gp_get_book_cover( $book, 'l' );
            $content = array();
            $content[] = array("Title" => "点击阅读《{$book->title}》", "Description" => $book->description, "PicUrl" => $pic_url, "Url" => $link);
            $content = GP_Sns_Wechat_Subscribe::transmit_news( $object, $content );
        } else {
            $content = apply_filters( 'gp_wehchats_default_message', "找不到 {$content} 相关内容~" ); //"找不到 {$content} 相关内容~";
            $content = GP_Sns_Wechat_Subscribe::transmit_text($object, $content);
        }
    } else {
        $datas = gp_books_get_books( array(
            'search_terms'   => $content,
            'orderby'        => 'id',
            'status'         => GP_BOOK_SERIATING | GP_BOOK_FINISH | GP_BOOK_HIDE,
            'order'          => 'DESC',
            'page'           => 1,
            'per_page'       => 3 ) );

        if ( count( $datas['items'] ) == 0 ) {
            $content = apply_filters( 'gp_wehchats_default_message', "找不到 {$content} 相关内容~" ); //"找不到 {$content} 相关内容~";
            $content = GP_Sns_Wechat_Subscribe::transmit_text($object, $content);
        } else {
            $content = array();
            foreach ( $datas['items'] as $book ) {
                $chapter = gp_books_get_first_chapter( $book->id );
                $link = gp_get_chapter_permalink( $chapter ) . '?from=adx-gzh';
                $pic_url = gp_root_domain() . gp_get_book_cover( $book, 'l' );

                $content[] = array( "Title" => "《{$book->title}》", "Description" => $book->description, "PicUrl" => $pic_url, "Url" => $link );
            }
            $content = GP_Sns_Wechat_Subscribe::transmit_news( $object, $content );
        }

    }

    return $content;
}
add_filter( 'gp_wehchats_precess_text', 'gp_books_sns_sub_proc_text', 10, 2 );

function gp_books_sns_sub_default_msg( $msg ) {
    return "小主有什么需要吗？

1、充值问题请回复『充值』；

2、阅读问题请回复『看书』；

3、人工服务请回复『客服』直接咨询客服QQ；

4、回复『帮助』可重新呼出本菜单；

点击蓝字部分即可<a href=\"http://www.adaixiong.com/book_free\">免费看书</a>哦

祝您阅读愉快~";
}
add_filter( 'gp_wehchats_default_message', 'gp_books_sns_sub_default_msg', 10, 1 );

function gp_books_links_redirect( $link ) {
    $from = query_get_value( $link , 'from' );
    if ( !empty( $from ) )
        @setcookie( 'from', $from, time() + 60000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
    return $link;
}
add_filter( 'gp_links_redirect', 'gp_books_links_redirect', 10, 1 );