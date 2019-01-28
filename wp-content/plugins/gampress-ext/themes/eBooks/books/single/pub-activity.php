<?php $book = gp_books_get_current_book();?>

<div class="content" id="box_pub_activity">
    <div class="global-box no-line">
        <div class="comment-form">
            <form class="form-box">
                <textarea name="content" placeholder="《<?php gp_book_title( $book );?>》期待您的评论哦~~"></textarea>
                <!--<p id="star">
                    评分：
                    <a href="javascript:;" class="fa fa-star-o"></a>
                    <a href="javascript:;" class="fa fa-star-o"></a>
                    <a href="javascript:;" class="fa fa-star-o"></a>
                    <a href="javascript:;" class="fa fa-star-o"></a>
                    <a href="javascript:;" class="fa fa-star-o"></a>
                </p>-->
                <input type="hidden" name="book_id" value="<?php echo $book->id;?>">
                <input type="hidden" name="action" value="pub_activity"/>
                <input type="hidden" name="url" value="/wp-admin/admin-ajax.php"/>
            </form>
        </div>
    </div>
    <div class="wrap-btn absolute">
        <button class="btn-primary btn-block radius btn-submit" id="comment_submit">评论</button>
        <a href="<?php gp_book_permalink( $book ) ;?>" class="btn-primary btn-block radius btn-outline">取消</a>
    </div>
</div>

<style>
    .foot{display:none}
</style>