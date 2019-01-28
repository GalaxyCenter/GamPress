<?php
get_header( 'pays' );
$redirect = !empty( $_GET['redirect'] ) ? $_GET['redirect'] : gp_loggedin_user_domain();
?>
<div class="content" id="pay_success">
    <div class="pay-result">
        <i class="fa fa-check-circle"></i>
        <h2>充值成功</h2>
        <?php
        $order_id = isset( $_GET['order_id'] ) ? $_GET['order_id'] : 0;
        if ( !empty( $order_id ) ) {
            do_action( 'gp_after_pay_success', $order_id );
        }
        ?>
    </div>
    <div class="wrap-btn">
        <a href="<?php echo $redirect;?>" class="btn-success btn-block radius">完成</a>
        <a href="/" class="btn-default btn-block radius">返回首页</a>
    </div>
</div>
<script>
    window.setTimeout(function(){
        window.location.href = "<?php echo $redirect;?>";
    }, 4000)
</script>
<?php get_footer();?>
