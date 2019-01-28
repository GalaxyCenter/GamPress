<?php
/*
Template Name: Book_Search
*/
get_header( 'search' );
$search_terms = isset( $_GET['wd'] ) ? $_GET['wd'] : false;
if ( empty( $search_terms ) ) {
    $datas = array( 'items' => array() );
} else {
    $datas = gp_books_get_books( array(
        'search_terms'   => $search_terms,
        'orderby'        => 'id',
        'status'         => GP_BOOK_SERIATING | GP_BOOK_FINISH,
        'order'          => 'DESC',
        'page'           => 1,
        'per_page'       => 10 ) );
}
?>

<header class="head-search head-pre">
    <a href="javascript:void(0)" class="r"  id="btn_search">搜索</a>
    <a href="/" class="l"><i class="icon-pre"></i></a>
    <i class="fa fa-search"></i>
    <input type="text" class="txt" id="search_txt" placeholder="<?php echo $search_terms;?>" value="<?php echo $search_terms;?>"/>
    <a href="javascript:void(0)" class="del" id="search_del" title="删除"><i class="fa fa-times-circle"></i></a>
</header>
<div class="content" id="box_books">
    <!--
    <div class="menu">
        <a href="javascript:;" class="item active">人气</a>
        <a href="javascript:;" class="item">时间</a>
        <a href="javascript:;" class="item">字数</a>
        <a href="javascript:;" class="item">点击</a>
    </div> -->
    <div id="list_books" class="global-box no-line mt-20">
        <div class="media list">
            <?php
            foreach ( $datas['items'] as $book ) :
                if ( empty( $search_terms ) ) {
                    $book_title = $book->title;
                    $book_author = $book->author;
                } else {
                    $book_title =  preg_replace("/{$search_terms}/i", "<em class='font-red'>{$search_terms}</em>", $book->title );
                    $book_author = preg_replace("/{$search_terms}/i", "<em class='font-red'>{$search_terms}</em>", $book->author );
                }

                $status_text = gp_get_book_status( $book, true );
                $views = (int) gp_books_get_bookmeta( $book->id, 'views', true );
            ?>
                <a href="<?php gp_book_permalink( $book ) ;?>?from=adx-search" class="item">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book ); ?>" class="cover"/>
                    <h3><?php echo $book_title;?></h3>
                    <p class="font-gray"><em class="tag r <?php if ( $book->status == 0 ) { echo 'blue'; } else { echo 'green'; };?>"><?php echo $status_text;?></em><?php echo $book_author; ?> 丨 <?php gp_book_words( $book );?>字 </p>
                    <p><?php echo mb_substr( gp_get_book_description( $book ), 0, 26 ); ?>...</p>
                </a>
            <?php
            endforeach;
            ?>
        </div>
        <?php if ( empty( $datas['items'] ) ) : ?>
            <p class="loading pt30">你要找的内容可能跑火星去啦~</p>
            <div class="hd">
                <h1>95%的人都爱看</h1>
            </div>
            <div class="bd">
                <div class="pic-list">
                    <?php
                    $cbooks = gp_books_auto_recommend( gp_loggedin_user_id() );
                    $count = 0;
                    foreach( $cbooks as $cbook ): if ( $cbook->id == $book->id || $count == 4 || in_array( $cbook->id, $tmp ) ) continue; $count++; $tmp[] = $cbook->id;?>
                        <a href="<?php gp_book_permalink( $cbook ) ;?>?from=adx-search" class="item">
                            <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $cbook ); ?>" class="cover"/>
                            <p><?php gp_book_title( $cbook );?></p>
                        </a>
                        <?php
                    endforeach;
                    ?>
                </div>
            </div>
        <?php endif;?>
        <p class="loading">努力加载中...</p>
        <script id="tpl_books_list" type="text/html">
            {{each items as value i}}
            <a href="{{value.link}}" class="item">
                <img src="{{value.cover}}?from=adx-search" data-src="{{value.cover}}" class="cover"/>
                <h3>{{#value.title}}</h3>
                <p class="font-gray"><em class="tag r {{if value.status === 0}} blue {{else}} green {{/if}}">{{value.status_text}}</em>{{#value.author}} 丨 {{value.words}}字 </p>
                <p>{{value.description}}...</p>
            </a>
            {{/each}}
        </script>
    </div>
</div>

<?php get_sidebar( 'qrcode' ); get_footer(); ?>
