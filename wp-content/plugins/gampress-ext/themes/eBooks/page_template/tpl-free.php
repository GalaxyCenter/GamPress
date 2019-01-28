<?php
/*
Template Name: Book_Free
*/
get_header();

$book_fress = gp_books_get_current_free();
$bids = explode( ",", $book_fress->book_ids );
$diff = strtotime( $book_fress->end_time . ' 23:59:59' ) - time();
    ?>
    <div class="content">
        <div class="global-box no-line" id="free_mianfei">
            <div class="hd">
                <h1>限时免费<span class="timer" id="timer" _time="<?php echo $diff;?>"></span></h1>
            </div>
            <div class="bd">
                <div class="pic-list">
                    <?php
                    foreach ( $bids as $bid ) : $book = gp_books_get_book( $bid );?>
                    <a href="<?php gp_book_permalink( $book ) ;?>?from=adx-free" class="item">
                        <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book ); ?>" class="cover"/>
                        <p><?php gp_book_title( $book ) ;?></p>
                    </a>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>
        </div>

        <?php /*
        <div class="global-box no-line">
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
                    <a href="<?php gp_book_permalink( $book ) ;?>" class="item">
                        <h3><em class="tag <?php if ( $book->status == 1 ) { echo 'blue'; } else { echo 'green'; };?>"><?php echo $status_text;?></em><?php gp_book_title( $book );?><em><?php gp_book_words( $book );?>字</em></h3>
                        <p><?php echo mb_substr( gp_get_book_description( $book ), 0, 28 ); ?>...</p>
                    </a>
                <?php endwhile;?>
            </div>
        </div>
        */ ?>
        <div class="global-box" id="free_tuijian">
            <div class="hd">
                <h1>精彩推荐</h1>
            </div>
            <div class="bd">
                <div class="pic-list">
                    <?php
                    $args = array( 'post_type' => gp_get_book_recommend_post_type(),
                        'posts_per_page' => 12,
                        'tax_query' => array(
                            array(
                                'taxonomy' => gp_get_book_recommend_post_taxonomy(),
                                'terms'    => '精彩推荐',
                                'field'    => 'name'
                            )
                        ) );
                    $loop = new WP_Query( $args );
                    $count = 0;
                    while ( $loop->have_posts() && $count < 4 ) : $count++; $loop->the_post(); $book = gp_books_get_book( $loop->post->post_parent );?>
                    <a href="<?php gp_book_permalink( $book ) ;?>?from=adx-jxtj" class="item">
                        <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book ); ?>" class="cover"/>
                        <p><?php gp_book_title( $book );?></p>
                    </a>
                    <?php
                    endwhile;
                    ?>
                </div>
                <div class="txt-list">
                    <?php
                    while ( $loop->have_posts() ) : $loop->the_post(); $book = gp_books_get_book( $loop->post->post_parent );
                        $status_text = gp_get_book_status( $book, true );?>
                    <a href="<?php gp_book_permalink( $book ) ;?>?from=adx-jxtj" class="item">
                        <h3><em class="tag <?php if ( $book->status == 1 ) { echo 'blue'; } else { echo 'green'; };?>"><?php echo $status_text;?></em><?php gp_book_title( $book );?><em><?php gp_book_words( $book );?>字</em></h3>
                        <p><?php echo mb_substr( gp_get_book_description( $book ), 0, 26 ); ?>...</p>
                    </a>
                    <?php
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php get_sidebar( 'qrcode' ); get_footer(); ?>