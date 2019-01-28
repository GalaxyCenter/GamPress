<?php
$book = gp_books_get_current_book();
$chapter = gp_books_get_current_chapter( $book );
?>
<div class="read-head slide-down">
    <div class="r">
        <a href="/" class="l"><i class="icon-home_white"></i></a>
        <a href="<?php gp_book_permalink( $book ) ;?>" class="l"><i class="icon-book"></i></a>
        <a href="javascript:;" class="l dots" id="collapse"><i></i></a>
    </div>
    <a href="<?php gp_book_permalink( $book ) ;?>" class="l"><i class="icon-pre_white"></i></a>
</div>

<div class="read-foot slide-up">
    <a href="<?php gp_book_chapters_permalink( $book, $chapter->order ) ;?>" class="item">
        <i class="fa fa-list-ul"></i>
        <p>目录</p>
    </a>
    <a href="javascript:;" class="item" id="setting">
        <i class="fa fa-gear"></i>
        <p>设置</p>
    </a>
    <a href="javascript:;" class="item" id="night" _mode="day">
        <i class="fa fa-moon-o"></i>
        <p>夜间</p>
    </a>
    <a href="<?php gp_book_permalink( $book );?>activities" class="item">
        <i class="fa fa-commenting-o"></i>
        <?php
        $datas = gp_activities_get_activities( array(
            'item_id'        => $book->id,
            'orderby'        => 'post_time',
            'order'          => 'DESC',
            'status'         => GP_ACTIVITY_APPROVED,
            'page'           => 1,
            'per_page'       => 6
        ) );
        ?>
        <p>评论<?php if ( !empty( $datas['total'] ) ) : ?><span class="font-red">（<?php echo $datas['total'];?>）</span><?php endif;?></p>
    </a>
</div>

<?php if ( is_user_logged_in() ) : ?>
<div class="read-menu">
    <?php
    $user_id = gp_loggedin_user_id();
    $auto_create_order = gp_books_user_is_auto_create_order( $user_id, $book->id );
    if ( $auto_create_order == -1 )
        $auto_create_order = 'true';
    ?>
    <a href="javascript:;" class="item" id="auto_pay" _auto="<?php echo $auto_create_order === 'true' ? 'yes' : 'no';?>" data-book_id="<?php echo $book->id;?>">
        <i class="fa fa-cart-plus"></i>自动订购下一章 <i class="fa toggle <?php if ( $auto_create_order === 'true' ) :;?>fa-toggle-on<?php else:?>fa-toggle-off<?php endif;?>"></i>
    </a>
    <a href="<?php echo gp_loggedin_user_domain();?>bookmark/bookmarks" class="item"><i class="fa fa-book"></i>返回我的追书</a>
</div>
<?php endif;?>

<div class="read-setting">
    <div class="font" id="font_size" _size="2">
        <div class="item">
            <a href="javascript:;" id="font_plus">+<i class="fa fa-font"></i></a>
        </div>
        <div class="item">
            <a href="javascript:;" id="font_minus">-<i class="fa fa-font"></i></a>
        </div>
    </div>
    <div class="color" id="color" _color="1">
        <div class="item active" _color="1" _id="skin_brown">
            <a href="javascript:;" class="sc-1"></a>
        </div>
        <div class="item" _color="2" _id="skin_cyan">
            <a href="javascript:;" class="sc-2"></a>
        </div>
        <div class="item" _color="3" _id="skin_sky_blue">
            <a href="javascript:;" class="sc-3"></a>
        </div>
        <div class="item" _color="4" _id="skin_white">
            <a href="javascript:;" class="sc-4"></a>
        </div>
    </div>
</div>
<?php if ( is_user_logged_in() ) : ?>
    <?php if ( !gp_books_user_is_mark( gp_loggedin_user_id(), $book->id ) ) :?>
    <a href="javascript:;" class="read-mark" id="btn_add_bookmark" data-book-id="<?php echo $book->id;?>">追书</a>
    <?php endif;?>
<?php else : ?>
    <a href="javascript:;" _rel="/login?redirect=<?php gp_chapter_permalink( $chapter );?>" class="read-mark login-msg">追书</a>
<?php endif;?>

<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$link = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$title = wp_title( '_', false, 'right' );
$desc = gp_get_description();
$icon = gp_get_book_cover( $book );
gp_wechat_share( $link, $title, $desc, $icon );
?>