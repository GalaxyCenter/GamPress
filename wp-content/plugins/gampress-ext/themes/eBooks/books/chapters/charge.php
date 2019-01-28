<?php
get_header( 'chapter' );
$user_id = gp_loggedin_user_id();
$book = gp_books_get_current_book();
$chapter = gp_books_get_current_chapter( $book );
$user_coin = gp_orders_get_total_coin_for_user( $user_id );
$user_ticket = gp_orders_get_tickets_total_fee( $user_id );
$total_coin = $user_coin + $user_ticket;
$auto_create_order = gp_books_user_is_auto_create_order( $user_id, $book->id );
$from = isset( $_GET['from'] ) ? $_GET['from'] : '';
if ( $auto_create_order == -1 )
    $auto_create_order = 'true';
$redirect = urlencode( gp_get_chapter_permalink( $chapter ) . '?auto_create_order=true&from=' . $from );
?>

<div class="content read-content" id="read" _type="on">
    <div class="bk-read" id="vip_order">
        <div class="chapter">
            <form method="post" action="/orders/create?redirect=<?php echo $redirect ;?>" class="form-box">
                <?php
                preg_match( '/第.*?章/u', $chapter->title, $matches );
                ?>
                <h1> <?php if ( empty( $matches ) ) :?> 第<?php gp_chapter_order( $chapter );?>章 <?php endif;?> <?php gp_chapter_title( $chapter ); ?></h1>

                <div class="order">
                    <p>本章<?php gp_chapter_words( $chapter ) ;?>字 需收费<em><?php gp_chapter_coin( $chapter->id );?></em>呆熊币</p>
                    <p><input type="checkbox" id="auto_create_order" name="auto_create_order" checked value="true" _bubbling="1"><label for="auto_create_order" _bubbling="1">自动购买下一章节</label></p>
                    <?php if ( !is_user_logged_in() ): ?>
                        <p><a href="/login?redirect=<?php gp_chapter_permalink( $chapter );?>" class="btn-danger btn-xs" id="read_login_btn" _bubbling="1">请先登录</a></p>
                    <?php elseif ( gp_get_chapter_coin( $chapter->id )  < $total_coin ) : ?>
                        <p><button class="btn-danger btn-xs" type="submit" _bubbling="1">购买本章</button></p>
                    <?php else : ?>
                        <p><a class="btn-danger btn-xs" id="read_recharge_btn" type="buttom" href="<?php echo gp_loggedin_user_domain();?>recharge?from=<?php echo $from;?>&redirect=<?php gp_chapter_permalink( $chapter ) ;?>" _bubbling="1">余额不足请充值</a></p>
                    <?php endif;?>
                </div>
                <input type="hidden" name="product_id" id="product_id" value="<?php gp_chapter_id( $chapter ) ;?>" />
                <input type="hidden" name="item_id" id="item_id" value="<?php gp_chapter_book_id( $chapter ) ;?>" />
                <input type="hidden" name="price" id="price" value="<?php gp_chapter_fee( $chapter->id );?>" />
                <input type="hidden" name="quantity" id="quantity" value="1" />
                <input type="hidden" name="total_fee" id="total_fee" value="<?php gp_chapter_fee( $chapter->id );?>" />
                <input type="hidden" name="pay_module" id="pay_module" value="adaixiong" />
                <input type="hidden" name="product_type" id="pay_module" value="<?php echo GP_Orders_Order::BOOK;?>" />
                <input type="hidden" name="product_name" id="product_name" value="支付阅读费用" />
                <input type="hidden" name="product_description" id="product_description" value="支付阅读费用" />
                <input type="hidden" name="from" id="from" value="<?php echo $from;?>" />
            </form>
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
