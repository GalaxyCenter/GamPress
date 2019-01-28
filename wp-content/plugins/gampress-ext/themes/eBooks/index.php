<?php get_header();?>

<div class="content">
    <?php
    $args = array( 'post_type' => gp_get_book_recommend_post_type(),
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'tax_query' => array(
                array(
                    'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                    'terms'    => '首页焦点图',
                    'field'    => 'name'
                )
        ) );
    $loop = new WP_Query( $args );
    if ( $loop->have_posts() ) : $loop->the_post();
        $book = gp_books_get_book( $loop->post->post_parent );
        $post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );
        if ( !empty( $post_thumbnail_id ) ) {
            $img = wp_get_attachment_image_src($post_thumbnail_id, array(710, 310), false);
            if ( !empty( $img ) )
                $img = $img[0];
            else
                $img = '';
        } else {
            $img = '';
        }
        $link = get_post_meta( $loop->post->ID, "gp_book_recommend_link", true );
        if ( empty( $link ) )
            $link = gp_get_book_permalink( $book );

        if ( strstr( $link, '?' ) )
            $link = $link . '&from=adaixiong_syjdt';
        else
            $link = $link . '?from=adaixiong_syjdt';
        ?>
        <a href="<?php echo $link ;?>" class="banner">
            <img src="<?php echo $img;?>" />
        </a>
        <?php
    endif;?>
    <div class="global-box no-line" id="zhongbang">
        <div class="hd">
            <!--<a href="#" class="r">更多 >></a>-->
            <h1>重磅推荐</h1>
        </div>
        <div class="bd">
            <?php
            $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                'posts_per_page' => 5,
                'tax_query' => array(
                    array(
                        'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                        'terms'    => '重磅推荐',
                        'field'    => 'name'
                    )
                ) );
            $loop = new WP_Query( $args );
            if ( $loop->have_posts() ) : $loop->the_post();
                $book = gp_books_get_book( $loop->post->post_parent );
                $status_text = gp_get_book_status( $book, true );
            ?>
            <div class="media big">
                <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_zbtj" class="item">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book ); ?>" class="cover"/>
                    <h3><?php gp_book_title( $book );?></h3>
                    <p class="font-gray"><?php gp_book_author( $book );?> 丨 <?php gp_book_words( $book );?>字 丨 <?php $terms = gp_get_object_terms( $book->id, 'book_library' );
                        foreach ( $terms as $term ) : if ( $term->parent != 0 ) :
                            echo $term->name;
                        endif; endforeach;
                        ?></p>
                    <p><?php echo mb_substr( gp_get_book_description( $book ), 0, 52 ); ?>...</p>
                </a>
            </div>
            <?php
            endif;?>
            <?php /*
            <div class="pic-list">
                <?php
                while ( $loop->have_posts() ) : $loop->the_post(); $book = gp_books_get_book( $loop->post->post_parent );
                ?>
                <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_zbtj" class="item">
                    <img data-src="<?php gp_book_cover( $book ); ?>" class="cover"/>
                    <p><?php gp_book_title( $book );?></p>
                </a>
                <?php
                endwhile;
                ?>
            </div>
            */ ?>
        </div>
    </div>
    <div class="global-box" id="remmen">
        <div class="hd">
           <!-- <a href="#" class="r">更多 >></a>-->
            <h1>热门精选</h1>
        </div>
        <div class="bd">
            <div class="pic-list col-3">
            <?php
                $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                    'posts_per_page' => 6,
                    'tax_query' => array(
                        array(
                            'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                            'terms'    => '热门精选',
                            'field'    => 'name'
                        )
                    ) );
                $loop = new WP_Query( $args );
                while ( $loop->have_posts() ) : $loop->the_post();
                    $book = gp_books_get_book( $loop->post->post_parent );
                ?>
                    <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_rmjx" class="item">
                        <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book, 'l' ); ?>" class="cover"/>
                        <p><?php gp_book_title( $book );?></p>
                    </a>
                <?php
                endwhile;
                ?>
            </div>
        </div>
    </div>
    <?php /*
    <div class="global-box" id="mianfei">
        <div class="hd">
            <!--<a href="#" class="r">更多 >></a>-->
            <h1>免费推荐</h1>
        </div>
        <div class="bd">
            <div class="pic-list">
                <?php
                $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                    'posts_per_page' => 4,
                    'tax_query' => array(
                        array(
                            'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                            'terms'    => '免费推荐',
                            'field'    => 'name'
                        )
                    ) );
                $loop = new WP_Query( $args );
                while ( $loop->have_posts() ) : $loop->the_post();
                    $book = gp_books_get_book( $loop->post->post_parent );?>
                <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_mftj" class="item">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book ); ?>" class="cover"/>
                    <p><?php gp_book_title( $book );?></p>
                </a>
                <?php
                endwhile;
                ?>
            </div>
            <div class="txt-list">
                <?php
                $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                    'offset' => 4,
                    'posts_per_page' => 6,
                    'tax_query' => array(
                        array(
                            'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                            'terms'    => '免费推荐',
                            'field'    => 'name'
                        )
                    ) );
                $loop = new WP_Query( $args );
                while ( $loop->have_posts() ) : $loop->the_post();
                    $book = gp_books_get_book( $loop->post->post_parent );
                    $status_text = gp_get_book_status( $book, true );?>
                    <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_mftj" class="item">
                        <h3><?php gp_book_title( $book );?><em><?php gp_book_words( $book );?>字 丨 <?php $terms = gp_get_object_terms( $book->id, 'book_library' );
                            foreach ( $terms as $term ) : if ( $term->parent != 0 ) :
                                echo $term->name;
                            endif; endforeach;
                            ?></em></h3>
                        <p><?php echo mb_substr( gp_get_book_description( $book ), 0, 28 ); ?>...</p>
                    </a>
                <?php endwhile;?>
            </div>
        </div>
    </div>
    */ ?>
    <div class="global-box" id="dashen">
        <div class="hd">
            <!--<a href="#" class="r">更多 >></a>-->
            <h1>大神佳作</h1>
        </div>
        <div class="bd">
            <div class="pic-list col-3">
            <?php
            $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                'posts_per_page' => 6,
                'tax_query' => array(
                    array(
                        'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                        'terms'    => '大神佳作',
                        'field'    => 'name'
                    )
                ) );
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post();
                $book = gp_books_get_book( $loop->post->post_parent );?>
                <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_dsjz" class="item">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book, 'l' ); ?>" class="cover"/>
                    <p><?php gp_book_title( $book );?></p>
                </a>
            <?php
            endwhile; ?>
            </div>
        </div>
    </div>
    <div class="global-box" id="jinpai">
        <div class="hd">
            <!--<a href="#" class="r">更多 >></a>-->
            <h1>金牌全本</h1>
        </div>
        <div class="bd">
            <div class="media">
            <?php
            $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                'posts_per_page' => 8,
                'tax_query' => array(
                    array(
                        'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                        'terms'    => '金牌全本',
                        'field'    => 'name'
                    )
                ) );
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post();
                $book = gp_books_get_book( $loop->post->post_parent );
                $status_text = gp_get_book_status( $book, true );
            ?>
                <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_jpqb" class="item">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book ); ?>" class="cover"/>
                    <h3><?php gp_book_title( $book );?></h3>
                    <p class="font-gray"><?php gp_book_author( $book ); ?> 丨 <?php gp_book_words( $book );?>字 丨 <?php $terms = gp_get_object_terms( $book->id, 'book_library' );
                        foreach ( $terms as $term ) : if ( $term->parent != 0 ) :
                            echo $term->name;
                        endif; endforeach;
                        ?></p>
                    <p><?php echo mb_substr( gp_get_book_description( $book ), 0,35 ); ?>...</p>
                </a>
                <?php
            endwhile; ?>
            </div>
        </div>
    </div>
    <div class="global-box">
        <div class="working">
            <p class="item"><em class="r">周一到周日 9:00-18:00</em>工作时间</p>
            <?php if ( is_apple_mobile_browser() && !is_weixin_browser() ) :?>
                <a href="mqqwpa://im/chat?chat_type=wpa&uin=1022301265&version=1&src_type=web&web_src=oicqzone.com" class="item text-center"><i class="icon-qq"></i> 在线咨询</a>
            <?php else:?>
                <a href="http://wpa.qq.com/msgrd?v=3&uin=1022301265&site=qq&menu=yes" class="item text-center"><i class="icon-qq"></i> 在线咨询</a>
            <?php endif;?>
        </div>
    </div>
</div>
<a href="javascript:;" class="read-lately-btn" id="read_lately_btn">最近阅读</a>
<div class="side-menu right slide-left">
    <?php
    if ( is_user_logged_in() ) {
        $datas = gp_books_get_bookmarks( array(
            'user_id'       => gp_loggedin_user_id(),
            'type'          => 'history',
            'page_index'    => 1,
            'page_size'     => 6
        ) );
    } else {
        $datas = false;
    }
    if ( empty( $datas ) || empty( $datas['items'] ) ) : ?>
        <div class="global-box mt-20">
            <div class="pic-list col-3" id="lately_tuijian">
                <?php
                $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                    'posts_per_page' => 6,
                    'tax_query' => array(
                        array(
                            'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                            'terms'    => '精彩推荐',
                            'field'    => 'name'
                        )
                    ) );
                $loop = new WP_Query( $args );
                while ( $loop->have_posts() ) : $loop->the_post();
                    $book = gp_books_get_book( $loop->post->post_parent );?>
                <a href="<?php gp_book_permalink( $book ) ;?>?from=adaixiong_jctj" class="item">
                    <span class="badge"><em>荐</em></span>
                    <img src="<?php gp_book_cover( $book, 'm' );?>" class="cover"/>
                    <p><?php gp_book_title( $book ) ;?></p>
                </a>
                <?php
                endwhile;
                ?>
            </div>
        </div>
    <?php
    else: ?>
        <div class="item-list"  id="lately_read">
            <?php foreach ( $datas['items'] as $item ) : $chapter = gp_books_get_chapter( $item->chapter_id ); ?>
            <a href="<?php gp_chapter_permalink( $item->chapter ) ;?>?from=adaixiong_zjyd" class="item">
                <em class="r"><?php echo friendly_time( $item->post_time );?></em>
                <h3><?php gp_book_title( $item->book ) ;?></h3>
                <p>读至 <?php gp_chapter_order( $item->chapter, true ); echo ' '; gp_chapter_title( $item->chapter ) ;?></p>
                <p>继续阅读 >></p>
            </a>
            <?php endforeach;?>
        </div>
    <?php
    endif; ?>

    <?php if ( !is_user_logged_in() ) : ?>
    <div class="bottom">
        <a href="javascript:;" _rel="/login?redirect=/" class="btn-primary btn-block login-msg"  id="lately_login">请先登录</a>
    </div>
    <?php endif;?>
</div>
<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$link = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$title = '阿呆熊';
$desc = gp_get_description();
$icon = 'http://www.adaixiong.com/wp-content/plugins/gampress-ext/themes/eBooks/dist/images/logo-login.png';
gp_wechat_share( $link, $title, $desc, $icon );
get_sidebar( 'help' ); get_footer();?>
