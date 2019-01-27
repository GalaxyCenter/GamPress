<?php
/**
 * Created by PhpStorm.
 * User: Texel
 * Date: 2017/2/20
 * Time: 16:56
 */

function services_is_woocommerce( $is_woocommerce ) {
    if ( ! bp_is_members_component() && ! bp_is_user() )
        return $is_woocommerce;


    if ( bp_current_action() == 'services' )
        return true;
}
add_filter( 'is_woocommerce', 'services_is_woocommerce' );

function services_sns_sub_proc_event( $content, $object ) {
    switch ($object->Event) {
        case "subscribe":
            $content = "感谢您的关注";
            $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
            break;
        case "unsubscribe":
            $content = "很遗憾你取消关注";
            $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
            break;
        case "CLICK":
            switch ($object->EventKey) {
                case 'shop':
                    $content = '<a href="' . gp_get_root_domain() . '/sns/login.php?openid=' . $object->FromUserName . '">订餐通道</a>';
                    $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
                    break;

                case 'menu2_sub_item_a':
                    $content = array();
                    $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                    $content = GP_Sns_Wechat_Subscribe::transmit_news( $object, $content );
                    break;

                case 'menu2_sub_item_b':
                    $content = array();
                    $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                    $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                    $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                    $content = GP_Sns_Wechat_Subscribe::transmit_news( $object, $content );
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
add_filter( 'gp_wehchats_precess_event', 'services_sns_sub_proc_event', 10, 2 );