# GamPress
一个WordPress插件

集成SNS登录组件(微信,微薄,QQ,短信), 

继承微信订阅号功能.

Pay组件(支付宝,微信支付), 

Activity留言模块, 

Notification通知模块, 

Votes投票模块

Members模块
=========================
## 安装并激活.
1)将GamPress目录放置到WordPress的Plugins目录
2)在WordPress后台激活
3)在GamPress设置里里面填入社交模块的appId和secret.

##在您的模板中使用
1)SNS登录
``` HTML
<a href="/sns/oauth/qq?callback=<?php echo $redirect;?>" class="qq">QQ</a>
<a href="/sns/oauth/wechat?callback=<?php echo $redirect;?>" class="wx">微信</a>
<a href="/sns/oauth/weibo?callback=<?php echo $redirect;?>" class="wb">微博</a>
``` 
2)支付
``` HTML
<form method="post" action="/orders/create?redirect=<?php echo $redirect;?>" class="form-box">
    <input type="hidden" name="product_id" id="product_id" value="0" />
    <input type="hidden" name="item_id" id="item_id" value="0" />
    <input type="hidden" name="price" id="price" value="0.01" />
    <input type="hidden" name="quantity" id="quantity" value="1" />
    <input type="hidden" name="total_fee" id="total_fee" value="0.01" />
    <input type="hidden" name="pay_module" id="pay_module" value="wechat" />
    <input type="hidden" name="product_name" id="product_name" value="订单名称" />
    <input type="hidden" name="product_description" id="product_description" value="订单描述" />
</form>
``` 
3) 微信分享代码
```PHP
<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$link = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$title = wp_title( '&lsaquo;', false, 'right' );
$desc = gp_get_description();
$icon = '';
gp_wechat_share( $link, $title, $desc, $icon );
?>

```
4)接入微信公号菜单和回复
```PHP

function gp_books_sns_sub_proc_event( $content, $object ) {
    switch ($object->Event) {
        case "subscribe":
            if ( $object->ToUserName == 'gh_035fc7f53229' ) { // 订阅号A
                $content = "关注并置顶『xxx』";
            } else { // 订阅号B
                $content = "关注并置顶『BBB』";
            }
            $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
            break;
        case "unsubscribe":
            $content = "很遗憾你取消关注";
            $content = GP_Sns_Wechat_Subscribe::transmit_text( $object, $content );
            break;
        case "CLICK":
            switch ($object->EventKey) {
                case 'MENU_AAA':
                    $content = '您点击了菜单 MENU_AAA';
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
    $content = "您给我发了这个内容:" $content;
    return $content;
}
add_filter( 'gp_wehchats_precess_text', 'gp_books_sns_sub_proc_text', 10, 2 );
```
5) 微信发红包功能
先将微信的3个pcm文件放入到 wp-content\plugins\gampress\includes\pays\libs\wechat\pem 目录下

```PHP
$send_pack_fee = 0.01;// 1分钱
$pay = new GP_Pays_Wechat_Pack();
        $result = $pay->do_pay( array(
            'order_id'          => $order_id,
            'sender'            => gp_games_activities_get_meta( $activity_id, 'wechat_pack_sender', true ),  // 发红包的人名称
            'product_fee'       => $send_pack_fee * 100,
            'wishing'           => gp_games_activities_get_meta( $activity_id, 'wechat_pack_wishing', true ), // 祝福语
            'product_name'      => gp_games_activities_get_meta( $activity_id, 'wechat_pack_wishing', true ), // 祝福语
            'wxappid'           => gp_games_activities_get_meta( $activity_id, 'wechat_app_id', true ), // 发红号的app id
            'mch_id'            => gp_games_activities_get_meta( $activity_id, 'wechat_mch_id', true ), // 商户号,在后台设置
            'wechat_key'        => gp_games_activities_get_meta( $activity_id, 'wechat_key', true ) // 商户key
        ) );

```
## 感谢
感谢BuddyPress. 插件的实现机制跟BuddyPress一样,并且部分代码来至BuddyPress


