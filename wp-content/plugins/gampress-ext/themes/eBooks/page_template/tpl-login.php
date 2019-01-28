<?php
/*
Template Name: Book Login
*/
get_header();
$redirect          = isset( $_GET['redirect'] ) ? $_GET['redirect'] : '';
$tab               = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
$auto_login        = isset( $_GET['auto_login'] ) ? $_GET['auto_login'] : 'true';

if ( $auto_login == 'true' && is_user_logged_in() ) {
    $user_id = gp_loggedin_user_id();
    $redirect = gp_core_get_user_domain( $user_id ) . $tab;
    ?>
    <script>
        window.location.href = "<?php echo $redirect;?>";
    </script>
<?php
}
?>
<style>
    .head-m, .nav-bar, .foot{display:none}
</style>
<div class="content" id="box_login">
    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/logo-login.png" alt="阿呆熊" class="logo-login"/>
    <div class="login-type">
        <div class="item"><a href="/sns/oauth/qq?callback=<?php echo $redirect;?>&tab=<?php echo $tab;?>" class="qq">QQ</a></div>
        <?php if ( is_weixin_browser() ) :?>
        <div class="item"><a href="/sns/oauth/wechat?callback=<?php echo $redirect;?>&tab=<?php echo $tab;?>" class="wx">微信</a></div>
        <?php else:?>
        <div class="item"><a href="/wxguid" class="wx">微信</a></div>
        <?php endif;?>
        <div class="item"><a href="/sns/oauth/weibo?callback=<?php echo $redirect;?>&tab=<?php echo $tab;?>" class="wb">微博</a></div>
    </div>
    <div class="login-title">使用第三方账号免费登录</div>
    <?php /*
    <div class="login-title">或手机号码登录</div>
    <div class="login-form">
        <div class="item">
            <i class="fa fa-mobile"></i>
            <input type="tel" class="txt" name="phone" placeholder="请输入手机号"/>
        </div>
        <div class="item short">
            <button type="button" class="r get-btn btn-disable" id="btn_get_sms_captcha" disabled="disabled">获取验证码</button>
            <i class="fa fa-key"></i>
            <input type="tel" class="txt" name="sms_code" placeholder="请输入短信验证码"/>
        </div>
    </div>
    <div class="wrap-btn mt40">
        <input type="hidden" name="redirect" value="<?php echo $redirect;?>"/>
        <input type="hidden" name="tab" value="<?php echo $tab;?>" />
        <button class="btn-primary btn-block radius btn-disable" id="btn_login" disabled="disabled">登录</button>
    </div>
    <?php */ ?>
</div>
<?php get_sidebar( 'qrcode' ); get_footer(); ?>
