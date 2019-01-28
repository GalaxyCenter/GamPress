<?php
get_header( 'pays' );
$redirect = !empty( $_GET['redirect'] ) ? $_GET['redirect'] : gp_loggedin_user_domain();
?>
<div class="content" id="pay_fail">
    <div class="pay-result">
        <i class="fa fa-times-circle"></i>
        <h2>充值失败</h2>
    </div>
    <div class="wrap-btn">
        <a href="<?php echo $redirect;?>" class="btn-danger btn-block radius">完成</a>
        <a href="/" class="btn-default btn-block radius">返回首页</a>
    </div>
</div>
<?php get_footer();?>