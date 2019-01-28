<?php
get_header( 'chapter' );
$user_id = gp_loggedin_user_id();
$book = gp_books_get_current_book();
$chapter = gp_books_get_current_chapter( $book );
?>

<div class="content read-content" id="read" _type="on">
    <div class="bk-read" id="vip_order">
        <div class="chapter">
            <?php
            preg_match( '/第.*?章/u', $chapter->title, $matches );
            ?>
            <h1> <?php if ( empty( $matches ) ) :?> 第<?php gp_chapter_order( $chapter );?>章 <?php endif;?> <?php gp_chapter_title( $chapter ); ?></h1>

            <div class="order">
                <p>小哥哥正在处理您的请求，请稍后</p>
            </div>
            <script>
                setTimeout(function () {
                    window.location.href = "<?php echo gp_get_chapter_permalink( $chapter ) ;?>";
                }, 3000);
            </script>
        </div>
    </div>
</div>


<?php get_sidebar( 'chapter' ); ?>

<div class="foot fixed">
    <img class="qrcode" src="<?php echo get_template_directory_uri(); ?>/dist/images/fwh-qrcode.jpeg">
    <?php if ( is_weixin_browser() ) :?>
        <p>长按二维码免费领呆熊币</p>
    <?php else:?>
        <p>扫描二维码免费领呆熊币</p>
    <?php endif;?>
</div>

<?php get_footer(); ?>
