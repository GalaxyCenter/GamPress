<div class="foot">
    <div class="t">
        <a href="javascript:;" class="active">触屏版</a>
        <a href="/help" id="help_btn">帮助</a>
        <a href="javascript:;" id="go_top">顶部</a>
    </div>
	<img class="qrcode" src="<?php echo get_template_directory_uri(); ?>/dist/images/fwh-qrcode.jpeg">
    <?php if ( is_weixin_browser() ) :?>
        <p>长按二维码免费领呆熊币</p>
    <?php else:?>
        <p>扫描二维码免费领呆熊币</p>
    <?php endif;?>
	<p>佛山贝肯网络科技有限公司版权所有@2017</p>
    <p><a href="http://www.miitbeian.gov.cn" target="_blank">粤ICP备17085591号</p></p>
</div>
