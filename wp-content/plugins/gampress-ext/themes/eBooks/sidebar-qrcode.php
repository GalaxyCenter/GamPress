<div class="foot">
	<img class="qrcode" src="<?php echo get_template_directory_uri(); ?>/dist/images/fwh-qrcode.jpeg">
    <?php if ( is_weixin_browser() ) :?>
        <p>长按二维码免费领呆熊币</p>
    <?php else:?>
        <p>扫描二维码免费领呆熊币</p>
    <?php endif;?>
</div>