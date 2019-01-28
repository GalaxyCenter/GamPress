<?php
get_header( 'chapter' );
$book = gp_books_get_current_book();
$chapter = gp_books_get_current_chapter( $book );
$next_chapter = gp_books_get_chapter_by_order( $book->id, $chapter->order + 1 );
$pre_chapter = gp_books_get_chapter_by_order( $book->id, $chapter->order - 1 );

$read_count = isset( $_REQUEST['read_count'] ) ? $_REQUEST['read_count'] : 1;
$read_idx = isset( $_REQUEST['read_idx'] ) ? $_REQUEST['read_idx'] : 1;
?>

    <div class="content">
        <div class="bk-read">
            <div class="chapter">
                <?php
                preg_match( '/第.*?章/u', $chapter->title, $matches );
                ?>
                <h1> <?php if ( empty( $matches ) ) :?> 第<?php gp_chapter_order( $chapter );?>章 <?php endif;?> <?php gp_chapter_title( $chapter ); ?></h1>

                <?php gp_chapter_body( $chapter, true ); ?>

                <?php if ( $chapter->order == 0 ) :?>
                    <div class="wrap-btn">
                        <a href="<?php gp_chapter_permalink( $next_chapter, true )  ;?>?read_count=<?php echo $read_count;?>&read_idx=<?php echo $read_idx - 1;?>" class="btn-info btn-outline btn-block full" _bubbling="1">下一章</a>
                    </div>
                <?php elseif ( !empty( $next_chapter ) && !empty( $pre_chapter ) && ($read_count != $read_idx) ) :?>
                    <div class="wrap-btn">
                        <a href="<?php gp_chapter_permalink( $pre_chapter, true )  ;?>?read_count=<?php echo $read_count;?>&read_idx=<?php echo $read_idx - 1;?>" class="btn-info btn-outline btn-block half" _bubbling="1">上一章</a>
                        <a href="<?php gp_chapter_permalink( $next_chapter, true )  ;?>?read_count=<?php echo $read_count;?>&read_idx=<?php echo $read_idx + 1;?>" class="btn-info btn-outline btn-block half" _bubbling="1">下一章</a>
                    </div>
                <?php else:?>
                    <hr/>
                <?php endif;?>

            </div>
        </div>
    </div>

    <?php if ( $read_count == $read_idx ) :?>
    <div class="foot">
        <p class="mb10">因为版权问题<br/>后续内容还请长按识别下方二维码<br/>关注公众号之后继续阅读</p>
        <img class="qrcode" src="<?php echo get_template_directory_uri(); ?>/dist/images/fwh-qrcode.jpeg">
    </div>
    <?php endif;?>

<?php get_footer( 'chapter' ); ?>