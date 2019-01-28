<?php

$book = gp_books_get_current_book();
$chapter_first = gp_books_get_first_chapter( $book->id );
$user_id = gp_loggedin_user_id();
$from = isset( $_GET['from'] ) ? $_GET['from'] : '';
?>

<footer class="btn-bar">
    <div class="item">
        <?php $bm = gp_books_user_is_readed( $user_id, $book->id );
        if ( empty( $bm ) ) : ?>
            <a href="<?php gp_chapter_permalink( $chapter_first );?>?from=<?php echo $from;?>" class="btn-primary btn-block">免费阅读</a>
        <?php
        else:
            $read_chapter_id = $bm->chapter_id;
            $read_chapter = gp_books_get_chapter( $read_chapter_id ); ?>
            <a href="<?php gp_chapter_permalink( $read_chapter );?>?from=<?php echo $from;?>" class="btn-primary btn-block">继续阅读</a>
        <?php
        endif;
        ?>

    </div>
    <div class="item">
        <?php if ( is_user_logged_in() ) : ?>
            <?php if ( gp_books_user_is_mark( gp_loggedin_user_id(), $book->id ) ) :?>
                <a href="javascript:void(0)" class="btn-default btn-block">已追书</a>
            <?php else:?>
                <a href="javascript:void(0)" class="btn-default btn-block" id="btn_add_bookmark" data-book-id="<?php echo $book->id;?>">追书</a>
            <?php endif;?>
        <?php else :?>
            <a href="javascript:;" _rel="/login?redirect=<?php gp_book_permalink( $book );?>" class="btn-default btn-block btn-unlogin login-msg">追书</a>
        <?php endif;?>
    </div>
</footer>

<script>
    var book_id = <?php echo $book->id;?>;
</script>

    <div class="book-intr">
        <div class="info">
            <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book, 'l' ) ;?>" class="cover"/>
            <h3>
                <?php gp_book_title( $book );?>
                <em class="tag green"><?php gp_book_status( $book, true );?></em>
            </h3>
            <p class="font-blue"><?php gp_book_author( $book ); ?></p>
            <!--
            <p>
                <span>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star-half-o"></i>
                    <i class="fa fa-star-o"></i>
                    <i class="fa fa-star-o"></i>
                    8.6分
                </span>
            </p> -->
        </div>
        <p class="type">
            <em class="r">追书(<span id="book_marks"></span>)</em>
            <em class="l"><?php gp_book_words( $book );?>字</em>
            <?php
            $terms = gp_get_object_terms( $book->id, 'book_library' );
            foreach ( $terms as $term ) : if ( $term->parent != 0 ) :?>
                <a href="<?php gp_books_catalog_permlink( $term->name );?>?from=<?php echo $from;?>"><?php echo $term->name;?></a>
            <?php
            endif; endforeach;
            ?>
        </p>
        <p class="summary">
            <?php gp_book_description( $book ); ?>
            <a href="javascript:;" class="r" id="txt_down" title="down">
                <i class="fa fa-angle-down"></i>
                <!--<i class="fa fa-angle-up"></i>-->
            </a>
        </p>
        <div class="update">
            <a href="<?php gp_book_chapters_permalink( $book ) ;?>?from=<?php echo $from;?>" class="r">
                <i class="fa fa-list-ul"></i>
                目录
            </a>
            <?php $chapter = gp_books_get_last_chapter( $book->id ); preg_match( '/第.*?章/u', $chapter->title, $matches );?>
            <a href="<?php gp_chapter_permalink( $chapter ) ?>?from=<?php echo $from;?>" class="txt">
                <span class="r">
                    <i class="fa fa-angle-right"></i>
                </span>
                最新：<?php if ( empty( $matches ) ) :?> 第<?php gp_chapter_order( $chapter );?>章 <?php endif;?>  <?php gp_chapter_title( $chapter );?>
            </a>
        </div>
    </div>
    <div class="global-box">
        <div class="hd">
            <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php gp_book_permalink( $book );?>pub-activity" class="font-blue r" id="comment_btn">写评论</a>
            <?php else : ?>
            <a href="javascript:;" _rel="/login?redirect=<?php gp_book_permalink( $book );?>pub-activity" class="font-blue r login-msg" id="comment_btn">写评论</a>
            <?php endif;?>
            <?php
            $datas = gp_activities_get_activities( array(
                'item_id'        => $book->id,
                'orderby'        => 'post_time',
                'order'          => 'DESC',
                'status'         => GP_ACTIVITY_APPROVED,
                'page'           => 1,
                'per_page'       => 6
            ) );
            ;?>
            <h1>书评（<span><?php echo $datas['total'];?></span>）</h1>
        </div>
        <div class="bd" id="box_activities">
            <div class="bd" id="list_comments" data-orderby="likes" data-auto-load="false">
                <ul class="comment-list list active"></ul>
                <p class="loading">努力加载中...</p>
                <script id="tpl_activity_list" type="text/html">
                    {{each items as value i}}
                    <li data-id="{{value.id}}">
                        <img src="{{value.avatar}}" class="vest"/>
                        <h3><em class="r">{{value.post_time}}</em>{{value.author}}</h3>
                        <p class="txt">{{value.content}}</p>
                        <p class="text-right"><a href="javascript:;" class="{{if value.liked}} active {{else if value.user_id}} gp-icon-like {{else}} login-msg {{/if}}" _rel="/login?redirect=<?php gp_book_permalink( $book );?>"><i class="fa fa-thumbs-o-up"></i> <em>{{value.likes}}</em></a></p>
                    </li>
                    {{/each}}
                </script>
            </div>
        </div>
        <div class="fd">
            <a href="<?php gp_book_permalink( $book );?>activities" class="text-center">查看更多 <i class="fa fa-angle-double-right"></i></a>
        </div>
    </div>
    <div class="global-box">
        <div class="hd">
            <h1>精彩推荐</h1>
        </div>
        <div class="bd">
            <div class="pic-list">
                <?php
                $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                    'posts_per_page' => 5,
                    'tax_query' => array(
                        array(
                            'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                            'terms'    => '精彩推荐',
                            'field'    => 'name'
                        )
                    ) );
                $loop = new WP_Query( $args );
                $idx = 0;
                while ( $loop->have_posts() && $idx < 4 ) : $loop->the_post();
                    if ( $book->id == $loop->post->post_parent ) continue;
                    $idx++;
                    $cbook = gp_books_get_book( $loop->post->post_parent );?>
                <a href="<?php gp_book_permalink( $cbook ) ;?>" class="item">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $cbook ); ?>" class="cover"/>
                    <p><?php gp_book_title( $cbook );?></p>
                </a>
                    <?php
                endwhile;
                ?>
            </div>
        </div>
<!--        <div class="fd">-->
<!--            <a href="javascript:;"class="text-center font-blue">换一换</a>-->
<!--        </div>-->
    </div>

<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$link = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$title = wp_title( '_', false, 'right' );
$desc = gp_get_description();
$icon = gp_get_book_cover( $book );
gp_wechat_share( $link, $title, $desc, $icon );
?>
