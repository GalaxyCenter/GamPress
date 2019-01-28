<?php
/*
Template Name: Book_Tag
*/
get_header( 'search' );

$referer = isset( $_SERVER['HTTP_REFERER'] ) ? 'javascript:history.go(-1)' : '/';
?>

<div class="search-pop">
    <form id="form_search" method="get" action="/book_search">
        <header class="head-search head-pre">
            <a href="javascript:;" class="r"  id="btn_search_form">搜索</a>
            <a href="<?php echo $referer;?>" class="l"><i class="icon-pre"></i></a>
            <i class="fa fa-search"></i>
            <input type="text" class="txt" id="search_txt" name="wd" placeholder=""/>
            <a href="javascript:;" class="del" id="search_del" title="删除"><i class="fa fa-times-circle"></i></a>
        </header>
    </form>
    <div class="category">
        <div class="item" >
            <div class="hd">大家都在搜</div>
            <div class="bd" id="quick_search">
                <a href="/books/%E6%88%91%E5%9C%A8%E5%A5%B3%E5%AD%90%E7%9B%91%E7%8B%B1%E5%BD%93%E7%AE%A1%E6%95%99/chapters/%E9%82%82%E9%80%85%E6%BC%82%E4%BA%AE%E5%A5%B3%E4%BA%BA?from=adx-tag" class="item" data-id="woman_options">我在女子监狱当管教</a>
                <a href="/books/%E6%9D%91%E9%87%8E%E5%B0%8F%E9%82%AA%E5%8C%BB/chapters/%E7%9B%B8%E4%BA%B2%E8%A2%AB%E7%A0%B4%E5%9D%8F?from=adx-tag" class="item" data-id="man_options">村野小邪医</a>
                <a href="/books/%E4%BA%BA%E9%97%B4%E8%AF%A1%E4%BA%8B/chapters/%E6%8D%A1%E7%81%B5%E5%B8%88?from=adx-tag/" class="item" data-id="book_options">人间诡事</a>
                <a href="/books/%E6%B7%B1%E7%88%B1%E5%BC%8F%E8%B0%8B%E6%9D%80/chapters/%E8%80%81%E5%85%AC%E8%A6%81%E6%9D%80%E6%88%91%EF%BC%81?from=adx-tag" class="item" data-id="book_options">深爱式谋杀</a>
                <a href="/books/%E6%B7%B1%E5%9C%B3%E7%88%B1%E6%83%85%E6%95%85%E4%BA%8B2%E9%9B%8F%E8%8F%8A%E4%B9%8B%E6%81%8B/chapters/%E9%82%A3%E4%B8%80%E5%B9%B4%EF%BC%8C%E9%82%A3%E6%AC%A1%E5%81%B7%E7%AA%A5?from=adx-tag" class="item" data-id="book_options">深圳爱情故事2</a>
            </div>
        </div>
        <div class="item">
            <!--
            <div class="hd"><a href="/book_search?wd=脑洞大爆炸" class="r"><i class="fa fa-trash-o"></i> 清空</a>搜索历史</div>
            <div class="bd">
                <a href="/book_search?wd=脑洞大爆炸" class="item">道门振兴系统</a>
                <a href="/book_search?wd=脑洞大爆炸" class="item">白袍总管</a>
            </div>-->
        </div>
    </div>
</div>

<?php get_sidebar( 'qrcode' ); get_footer(); ?>
