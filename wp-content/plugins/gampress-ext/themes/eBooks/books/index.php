<?php
get_header();
$gp = gampress();

$page_index = gp_action_variable(1 );

if ( empty( $page_index ) )
    $page_index = 1;

$args = array(
    'search_terms'   => false,
    'orderby'        => 'id',
    'status'         => GP_BOOK_SERIATING | GP_BOOK_FINISH,
    'order'          => 'DESC',
    'page'           => $page_index,
    'per_page'       => 10 );

$term_name = gp_action_variable( 0 );
if ( !empty( $term_name ) && $term_name != '全部' ) {
    $term = get_term_by( 'name', $term_name, 'book_library' );
    $args['term_ids'] = $term->term_id;
}

$datas = gp_books_get_books( $args );
?>

    <div id="box_books" class="content mb50">
        <input id="search_txt" type="hidden"/>
        <div class="menu-box">
            <div class="menu" id="stacks_menu">
                <a href="javascript:;" class="item" id="classify">全部 <i class="fa fa-filter"></i></a>
                <a href="javascript:;" class="item">按时间 <i class="fa fa-sort"></i></a>
            </div>
            <!-- <div class="second-menu slide-down">
                <div class="sort-list">
                    <a href="javascript:;" class="item active" _type="id">按时间</a>
                    <a href="javascript:;" class="item" _type="words">按字数</a>
                </div>
            </div> -->
        </div>
        <div id="list_books" class="global-box no-line mt-20" data-term_name="<?php echo $term_name;?>">
            <div class="media list">
                <?php
                foreach ( $datas['items'] as $book ) :
                    $status_text = gp_get_book_status( $book, true );
                    ?>
                    <a href="<?php gp_book_permalink( $book ) ;?>?from=books" class="item">
                        <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="<?php gp_book_cover( $book, 'm' ); ?>" class="cover"/>
                        <h3><?php gp_book_title( $book ) ;?></h3>
                        <p class="font-gray"><em class="tag r <?php if ( $book->status == 1 ) { echo 'blue'; } else { echo 'green'; };?>"><?php echo $status_text;?></em><?php gp_book_author( $book ); ?> 丨 <?php gp_book_words( $book );?>字 </p>
                        <p><?php echo mb_substr( gp_get_book_description( $book ), 0, 26 ); ?>...</p>
                    </a>
                <?php
                endforeach;
                ?>
            </div>
            <div class="hide">
            <?php
            $url_patter = "/books/catalog/全部/%d";
            echo par_pagenavi( $page_index, 10, (int) $datas['total'], $url_patter ); ?>
            </div>
            <script id="tpl_books_list" type="text/html">
                {{each items as value i}}
                <a href="{{value.link}}?from=books" class="item">
                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="{{value.cover}}" class="cover"/>
                    <h3>{{value.title}}</h3>
                    <p class="font-gray"><em class="tag r {{if value.status === 1}} blue {{else}} green {{/if}}">{{value.status_text}}</em>{{value.author}} 丨 {{value.words}}字</p>
                    <p>{{value.description}}...</p>
                </a>
                {{/each}}
            </script>
        </div>
        <p class="loading"></p>
    </div>

    <div class="side-menu slide-right">
        <div class="category" id="category">
            <div class="item" id="cat_bigid">
                <div class="hd">类型</div>
                <div class="bd">
                    <a href="javascript:;" class="item active" data-id="woman_options">女频</a>
                    <a href="javascript:;" class="item" data-id="man_options">男频</a>
                </div>
            </div>
            <div class="item options active" id="woman_options">
                <div class="hd">类别</div>
                <div class="bd" _type="term_name">
                    <a href="javascript:;" class="item active" _type="现代言情">现代言情</a>
                    <a href="javascript:;" class="item" _type="古代言情">古代言情</a>
                    <a href="javascript:;" class="item" _type="幻想言情">幻想言情</a>
                    <a href="javascript:;" class="item" _type="女生悬疑">女生悬疑</a>
                    <a href="javascript:;" class="item" _type="浪漫青春">浪漫青春</a>
                </div>
            </div>
            <div class="item options" id="man_options">
                <div class="hd">类别</div>
                <div class="bd" _type="term_name">
                    <a href="javascript:;" class="item" _type="现代都市">现代都市</a>
                    <a href="javascript:;" class="item" _type="灵异悬疑">灵异悬疑</a>
                    <a href="javascript:;" class="item" _type="官场职场">官场职场</a>
                    <a href="javascript:;" class="item" _type="奇幻玄幻">奇幻玄幻</a>
                    <a href="javascript:;" class="item" _type="历史军事">历史军事</a>
                    <a href="javascript:;" class="item" _type="武侠仙侠">武侠仙侠</a>
                    <a href="javascript:;" class="item" _type="科幻小说">科幻小说</a>
                </div>
            </div>
            <div class="item">
                <div class="hd">字数</div>
                <div class="bd" _type="words_query">
                    <a href="javascript:;" class="item active" _type="">不限</a>
                    <a href="javascript:;" class="item" _type="words-l30w">30万以下</a>
                    <a href="javascript:;" class="item" _type="words-g30w">30~50万</a>
                    <a href="javascript:;" class="item" _type="words-g50w">50~100万</a>
                    <a href="javascript:;" class="item" _type="words-g100w">100万以上</a>
                </div>
            </div>
            <div class="item">
                <div class="hd">属性</div>
                <div class="bd" _type="charge_type">
                    <a href="javascript:;" class="item active" _type="">全部</a>
                    <a href="javascript:;" class="item" _type="1">免费</a>
                    <a href="javascript:;" class="item" _type="4">收费</a>
                </div>
            </div>
        </div>
        <div class="bottom">
            <div class="shell">
                <a href="javascript:;" class="btn-default" id="close_btn">取消</a>
            </div>
            <div class="shell">
                <a href="javascript:;" class="btn-primary" id="side_menu_btn">确定</a>
            </div>
        </div>
    </div>
<?php get_footer(); ?>