<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/11/8
 * Time: 20:15
 */
$user_id = gp_loggedin_user_id();
$val = gp_users_get_meta( $user_id, 'wechat_subscribe', false, true );
if ( $val === false ) {
    $wechat = new GP_Sns_Wechat_Base();
    $wechat->app_id = "wx949083ac45f6b726";
    $wechat->app_secre = "425805ea05eb34cf25c2897981a839eb";
    $wechat->openid = gp_action_variable(1);
    $wechat->request_access_token();
    $sns_user = $wechat->get_user_info();
    $unionid = $sns_user->unionid;

    global $wpdp;
    $user_id = $wpdb->get_var( "select user_id from ds_usermeta where meta_key = 'unionid' and meta_value = '$unionid'" );

    if ( empty( $user_id ) ||  empty( $sns_user->subscribe ) ) {
        $msg = "请先关注阿呆熊公众号【adaixiongxs】后再领取福利~";
    } else {
        gp_orders_add_ticket( array(
            'id'                => 0,
            'name'              => '关注送赠',
            'user_id'           => $user_id,
            'fee'               => 50,
            'type'              => 'ticket',
            'expired'           => gp_format_time( time() + ( 86400 * 3 ) ),
            'create_time'       => gp_format_time( time() )
        ) );

        update_user_meta( $user_id, 'wechat_subscribe', true );
        $msg = "您已成功领取300呆熊币，有效期3天，请移步去首页看精彩小说吧~";
    }
} else {
    $msg = "您已领取过该福利啦~移步去首页看精彩小说吧~";
}
get_header( 'pays' );
?>
<div class="content" id="pay_success">
    <div class="pay-result">
        <i class="fa fa-check-circle"></i>
        <h2><?php echo $msg;?></h2>
    </div>
    <div class="wrap-btn">
        <a href="/" class="btn-default btn-block radius">去首页看精彩小说</a>
    </div>
</div>
<script>
//    window.setTimeout(function(){
//        window.location.href = "/";
//    },2000)
</script>
<?php get_footer();?>
