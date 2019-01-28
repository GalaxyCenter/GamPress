<?php
get_header( 'chapter' );
$book = gp_books_get_current_book();
$chapter = gp_books_get_current_chapter( $book );
$next_chapter = gp_books_get_chapter_by_order( $book->id, $chapter->order + 1 );
$pre_chapter = gp_books_get_chapter_by_order( $book->id, $chapter->order - 1 );
$from = isset( $_GET['from'] ) ? $_GET['from'] : '';
?>

<div class="content" id="read" _type="on">
<!--    <div class="bk-name" id="bk_name">第--><?php //gp_chapter_order( $chapter );?><!--章 --><?php //gp_chapter_title( $chapter ); ?><!--</div>-->
    <?php
    $font_size = isset( $_COOKIE['_size'] ) ? $_COOKIE['_size'] : '2';
    $font_size = 'font-size:' . ( 14 + 2 * $font_size) .  'px;';
    ?>
    <div class="bk-read" style="<?php echo $font_size;?>">
        <div class="chapter">
            <?php
            preg_match( '/第.*?章/u', $chapter->title, $matches );
            ?>
            <h1> <?php if ( empty( $matches ) ) :?> 第<?php gp_chapter_order( $chapter );?>章 <?php endif;?> <?php gp_chapter_title( $chapter ); ?></h1>

            <?php gp_chapter_body( $chapter, true ); ?>

            <?php if ( $chapter->order == 0 ) :?>
                <div class="wrap-btn">
                    <a href="<?php gp_book_chapters_permalink( $book, $chapter->order ) ;?>&from=<?php echo $from;?>" class="btn-info btn-outline btn-block xs" _bubbling="1">目录</a>
                    <a href="<?php gp_chapter_permalink( $next_chapter )  ;?>?from=<?php echo $from;?>" class="btn-info btn-outline btn-block lg" _bubbling="1">下一章</a>
                </div>
            <?php elseif ( !empty( $next_chapter ) && !empty( $pre_chapter ) ) :?>
                <div class="wrap-btn">
                    <a href="<?php gp_chapter_permalink( $pre_chapter )  ;?>?from=<?php echo $from;?>" class="btn-info btn-outline btn-block" _bubbling="1">上一章</a>
                    <a href="<?php gp_book_chapters_permalink( $book, $chapter->order ) ;?>&from=<?php echo $from;?>" class="btn-info btn-outline btn-block xs" _bubbling="1">目录</a>
                    <a href="<?php gp_chapter_permalink( $next_chapter )  ;?>?from=<?php echo $from;?>" class="btn-info btn-outline btn-block" _bubbling="1">下一章</a>
                </div>
            <?php else:?>
                <div class="wrap-btn">
                    <a href="<?php gp_book_chapters_permalink( $book, $chapter->order ) ;?>&from=<?php echo $from;?>" class="btn-info btn-outline btn-block xs" _bubbling="1">目录</a>
                    <a href="<?php gp_chapter_permalink( $pre_chapter )  ;?>?from=<?php echo $from;?>" class="btn-info btn-outline btn-block lg" _bubbling="1">上一章</a>
                </div>
            <?php endif;?>

        </div>
    </div>
</div>

<?php if ( empty( $next_chapter ) ) : ;?>
<div class="content content-transparent">
    <div class="global-box" id="guess_books">
        <div class="hd">
            <h1>95%的人都爱看</h1>
        </div>
        <div class="bd">
            <div class="pic-list col-3">
                <?php
                $cbooks = gp_books_auto_recommend( gp_loggedin_user_id() );
                $count = 0;
                $tmp = [];
                if ( 1633 == $book->id ) {
                    $cbooks[0] = gp_books_get_book(1698);
                    $cbooks[1] = gp_books_get_book(1692);
                    $cbooks[2] = gp_books_get_book(1691);

                    $cbooks[3] = gp_books_get_book(1632);
                    $cbooks[4] = gp_books_get_book(1138);
                    $cbooks[5] = gp_books_get_book(1687);
                }

                foreach( $cbooks as $cbook ): if ( $cbook->id == $book->id || $count == 6 || in_array( $cbook->id, $tmp ) ) continue; $count++; $tmp[] = $cbook->id;?>
                <a href="<?php gp_book_permalink( $cbook ) ;?>?from=adx-cnxh" class="item" id="book_<?php echo $cbook->id;?>_link">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $cbook ); ?>" class="cover" id="book_<?php echo $cbook->id;?>_img"/>
                    <p id="book_<?php echo $cbook->id;?>_title"><?php gp_book_title( $cbook );?></p>
                </a>
                    <?php
                endforeach;
                ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="recommend-txt">
    <?php
    $terms = gp_get_object_terms( $book->id, 'book_library' );
    $term_name = '阅读页文字链 - 女频';
    foreach ( $terms as $term ) {
        if ( $term->parent == 0 && $term->name == '男频' ) {
            $term_name = '阅读页文字链 - 男频';
            break;
        }
    }
    $args = array( 'post_type' => gp_get_book_recommend_post_type(),
        'posts_per_page' => 4,
        'tax_query' => array(
            array(
                'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                'terms'    => $term_name,
                'field'    => 'name'
            )
        ) );
    $loop = new WP_Query( $args );
    $count = 0;
    while ( $loop->have_posts() && $count < 3 ) :
        $loop->the_post();
        $link = get_post_meta( $loop->post->ID, "gp_book_recommend_link", true );
        $bid = ( 10000 + $book->id ) . '';

        if( strpos( $link, $bid ) !== false ) {
            continue;
        }
        $count ++;
        ?>
        <p class="item"><a href="<?php echo $link;?>"><?php echo $loop->post->post_title;?></a></p>
        <?php
    endwhile;
    ?>
</div>
<?php endif;?>

<script>
    var book = <?php echo json_encode( $book );?>

    var chapter = <?php $chapter->body = ''; echo json_encode( $chapter );?>
</script>

<?php get_sidebar( 'chapter' ); ?>

<div class="foot">
    <?php
    $wechat = new GP_Sns_Wechat_Base();
    $val = gp_users_get_meta( gp_loggedin_user_id(), 'wechat_subscribe_' . $wechat->app_id, false, true );
    if ( empty( $val ) ) :?>
    <img class="qrcode" src="<?php echo get_template_directory_uri(); ?>/dist/images/fwh-qrcode.jpeg">
    <?php
    endif;?>

    <?php if ( is_weixin_browser() ) :?>
        <p>长按二维码免费领呆熊币</p>
    <?php else:?>
        <p>扫描二维码免费领呆熊币</p>
    <?php endif;?>
</div>

<?php get_footer( 'chapter' ); ?>