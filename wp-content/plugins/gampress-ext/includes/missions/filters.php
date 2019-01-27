<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/3/18
 * Time: 11:47
 */

function missions_sns_sub_proc_event( $content, $object ) {
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
                case 'matchmaker':
                    $content = '<a href="' . gp_get_root_domain() . '/sns/subscribe_login?openid=' . $object->FromUserName . '">点我推荐</a>';
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
add_filter( 'gp_wehchats_precess_event', 'missions_sns_sub_proc_event', 10, 2 );