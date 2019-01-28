<?php
$book = gp_books_get_current_book();
$views = gp_books_get_bookmeta( $book->id, 'views', true );
?>
<script>
    var book_id = <?php echo $book->id;?>;
</script>
<div class="comment-info">
    <img data-src="<?php gp_book_cover( $book, 's' );?>" class="cover"/>
    <h3><?php gp_book_title( $book );?></h3>
</div>
<div class="comment-box" id="box_activities">
    <div class="hd">
        <a href="javascript:;" class="item active" data-orderby="post_time">全部</a>
        <a href="javascript:;" class="item" data-orderby="likes">热门</a>
    </div>
    <div class="bd" id="list_comments" data-orderby="post_time" data-auto-load="true">
        <ul class="comment-list list active"></ul>
        <p class="loading">努力加载中...</p>
        <script id="tpl_activity_list" type="text/html">
            {{each items as value i}}
            <li data-id="{{value.id}}">
                <img src="{{value.avatar}}" class="vest"/>
                <h3><em class="r">{{value.post_time}}</em>{{value.author}}</h3>
                <p class="txt">{{value.content}}</p>
                <p class="text-right"><a href="javascript:;" class="{{if value.liked}} active {{else if value.user_id}} gp-icon-like {{else}} login-msg {{/if}}" _rel="/login?redirect=<?php gp_book_permalink( $book );?>activities"><i class="fa fa-thumbs-o-up"></i> <em>{{value.likes}}</em></a></p>
            </li>
            {{/each}}
        </script>
    </div>
</div>
<div class="wrap-btn">
    <a href="<?php gp_get_book_permalink( $book );?>pub-activity" class="btn-primary btn-block radius">去评论</a>
</div>
<style>
    .foot{display:none}
</style>