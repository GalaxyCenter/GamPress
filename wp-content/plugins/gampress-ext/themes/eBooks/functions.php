<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/3
 * Time: 9:34
 */

if ( !function_exists( 'active' ) ) {
    function active( $valuea, $valueb, $echo = true ) {
        if ( (string) $valuea === (string) $valueb )
            $result = "active";
        else
            $result = '';

        if ( $echo )
            echo $result;

        return $result;
    }
}

// 移除WP自动重定向到友好页面的功能.比如 /books/邪将天师 如果post中有邪将天师, 会重定向到 /post/邪将天师
remove_action( 'template_redirect', 'redirect_canonical' );

function gp_members_ajax_update_displayname() {
    $user_id = gp_loggedin_user_id();
    if ( get_user_meta( $user_id,  'name_updated', true ) == true ) {
        ajax_die( 1, '已经更新过', false );
        return;
    }

    $name = $_POST['name'];
    global $wpdb;

    $display_name = $wpdb->get_var( $wpdb->prepare("SELECT display_name FROM $wpdb->users WHERE display_name = %s AND ID <> %d LIMIT 1", $name, $user_id ) );
    if ( !empty( $display_name ) ) {
        ajax_die( 2, '名字已存在', false );
        return;
    }
    $args = array(
        'ID' => $user_id,
        'display_name' => $name,
        'fullname' => $name
    );
    wp_update_user( $args );
    wp_cache_delete( 'gp_user_username_' . $user_id, 'gp' );
    wp_cache_delete( 'gp_core_userdata_' . $user_id, 'gp' );
    wp_cache_delete( 'gp_user_fullname_' . $user_id, 'gp' );

    update_user_meta( $user_id, 'name_updated', true );

    ajax_die( 0, '更新成功', false );
}
add_action( 'wp_ajax_update_displayname', 'gp_members_ajax_update_displayname' );

function gp_members_ajax_import_chapter() {
    global $current_user;
    if ( $current_user->roles[0] == 'author' ) {
        $book_id = $_POST['book_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $content = gp_books_chapter_format_body( $content );

        $book = gp_books_get_book( $book_id );
        if ( empty( $title ) || empty( $content ) ) {
            ajax_die( 3, '标题或者内容不能为空', false );
            return;
        }
        if ( GP_Books_Chapter::chapter_exists( $book_id, $title ) ) {
            ajax_die( 4, '章节标题已存在', false );
            return;
        }
        if ( $book->author_id == $current_user->ID ) {
            $last_chapter = gp_books_get_last_chapter( $book_id, GP_CHAPTER_ALL );

            $chapter = new GP_Books_Chapter();
            $chapter->book_id = $book_id;
            $chapter->title = $title;
            $chapter->body = $content;
            $chapter->words = get_words( $content );
            $chapter->refer = 'author_' . $book_id;
            $chapter->order = $last_chapter->order + 1;
            $chapter->is_charge = $chapter->order > $book->charge_order ? 1 : 0;
            $chapter->status = GP_CHAPTER_UNAPPROVED;
            $chapter->post_time = $chapter->approved_time = $chapter->update_time = time();
            $chapter->save();

            $group = 'gp_ex_book_group_' . $book_id;
            wp_cache_clean( $group );

            $group = 'gp_ex_book_group_0';
            wp_cache_clean( $group );

            ajax_die( 0, '导入成功', false );
        } else {
            ajax_die( 2, '不是作品所有者', false );
        }
    } else {
        ajax_die( 1, '没有 作者 权限', false );
    }
}
add_action( 'wp_ajax_import_chapter', 'gp_members_ajax_import_chapter' );

function gp_pub_activity() {
    $user_id = gp_loggedin_user_id();
    $content = $_POST['content'];
    $book_id = $_POST['book_id'];
    $parent_id = isset( $_POST['parent_id'] ) ? $_POST['parent_id'] : 0;

    if ( empty( $content ) ) {
        ajax_die(1, '评论内容不能为空', false);
    } else if ( mb_strlen( $content ) > 500 ) {
        ajax_die(2, '评论长度不能超过500', false);
    } else if ( gp_core_get_user_displayname( $user_id ) == '游客' ) {
        ajax_die(3, '亲，登陆后才能评论哦。', false);
    } else {
        $activity_id  = gp_activities_add( array( 'user_id' => $user_id,
            'content'   => $content,
            'item_id'   => $book_id,
            'parent_id' => $parent_id,
            'type'      => 'book_comment',
            'component' => 'books') );

        ajax_die( 0, '评论成功', false );
    }

}
add_action( 'wp_ajax_pub_activity', 'gp_pub_activity' );

function gp_get_activities() {
    $item_id = $_POST['item_id'];
    $page_index = $_POST['page_index'];
    $page_size = $_POST['page_size'];
    $order_by = $_POST['order_by'];
    $user_id = gp_loggedin_user_id();

    $datas = gp_activities_get_activities( array(
        'item_id'        => $item_id,
        'orderby'        => $order_by,
        'order'          => 'DESC',
        'status'         => GP_ACTIVITY_APPROVED,
        'page'           => $page_index,
        'per_page'       => $page_size
    ) );
    foreach ( $datas['items'] as $item ) {
        if ( empty( $item->user_name ) )
            $item->author = gp_core_get_user_displayname( $item->user_id );
        else
            $item->author = $item->user_name;
        $item->post_time = gp_format_time( $item->post_time );
        $item->liked = gp_votes_user_liked( $user_id, $item->id );
        $item->avatar = gp_get_sns_user_avatar( $item->user_id );
    }
    ajax_die( 0, '', $datas );
}
add_action( 'wp_ajax_nopriv_get_activities', 'gp_get_activities' );
add_action( 'wp_ajax_get_activities', 'gp_get_activities' );

function par_pagenavi( $page_index, $page_size, $data_totals, $url_pattern, $page_range = 7 ) {
    if ( $data_totals == 0 )
        return false;

    if ( $page_index <= 0 )
        return false;

    $total_page = ceil( $data_totals / $page_size );

    if ( $page_index > $total_page )
        return false;

    $gauge = (int) ( $page_range / 2 );

    $start_index = $page_index - $gauge;
    $end_index = 1;

    $pre_index = $page_index - 1;
    $next_index = $page_index + 1;

    if ( $start_index <= 0 ) {
        $start_index = 1;
        $end_index = $page_range + 1;
    } else {
        $end_index = $start_index + $page_range;
    }

    if ( $end_index >= $total_page ) {
        $end_index = $total_page + 1;
        $start_index = $end_index - $page_range;

        //if ( $start_index > $total_page - $page_range )
        //    $start_index = $total_page - $page_range;

        if ( $start_index < 0 )
            $start_index = 1;
    }

    $pages = array();

    $html = '<nav aria-label="Page navigation"><ul class="pagination"><li class="page-item"><a class="page-link">'. '第' . $page_index . '页' .'（共' . $total_page . '页）'. '</a></li> ';
    if( $page_index != 1 ) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $url_pattern, 1 ) . '"> 第1页 </a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $pre_index, 1 ) . '">上一页</a></li>';
    }

    for( $i = $start_index; $i < $end_index; $i++ ) {
        if ( $i == $page_index )
            $html .= '<li class="page-item active"><a class="page-link">' . $i . '</a></li>';
        else
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $url_pattern, $i ) .'">' . $i . '</a></li>';
    }

    if( $page_index != $total_page ) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $next_index, 1 ) . '">下一页</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $url_pattern, $total_page ) . '"> 最后一页 </a></li>';
    }

    $html .= '</ul></nav>';

    echo $html;
}

function gp_wechat_share( $link, $title, $desc, $icon  ) {
?>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
    <script>
        var cfg = <?php gp_sns_wechat_config( $link );?>;
        wx.config(cfg);
        wx.error(function(res){
        });
        wx.ready(function () {
            wx.onMenuShareAppMessage({
                title: document.title, // 分享标题
                desc: '<?php echo $desc;?>', // 分享描述
                link: '<?php echo $link;?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: '<?php echo $icon;?>', // 分享图标
                type: 'link', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户确认分享后执行的回调函数
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                }
            });
        });
    </script>
<?php
}

function gp_themes_footer() {
    gp_core_render_message();
?>
    <div id="tongji" class="hide">
        <script src="https://s19.cnzz.com/z_stat.php?id=1263431792&web_id=1263431792" language="JavaScript"></script>
        <script>
            var _hmt = _hmt || [];
            (function() {
                var hm = document.createElement("script");
                hm.src = "https://hm.baidu.com/hm.js?5f95ec3b0ce1571866c5a6652de4baf3";
                var s = document.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(hm, s);
            })();
        </script>
    </div>
    <?php
}
add_action( 'gp_footer', 'gp_themes_footer' );


function gp_themes_header() {
    $showMsg = isset($_COOKIE['show_msg']) ? 0 : 1;
    ?>
    <script>
        var showMsg = "<?php echo $showMsg;?>";
    </script>
    <?php
}
add_action( 'gp_header', 'gp_themes_header' );

function gp_books_close_tips() {
    @setcookie( 'show_msg', 1, time() + 36000000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );

    ajax_die( 0, '', '' );
}
add_action( 'wp_ajax_nopriv_close_tips', 'gp_books_close_tips' );
add_action( 'wp_ajax_close_tips', 'gp_books_close_tips' );

function gp_themes_render_message() {
    ?>
    <script>

        var $el = msgAlert({                 // $el 弹层选择器
            title: '',            // 标题
            txt: $('#message').html(),      // 内容
            btnTxt: '我知道啦',
            isShade: true,                  // 是否添加遮罩层：true, 添加， false, 不添加； 默认 true
            closeType: 'remove',            // 关闭类型： 'remove', 移除， 'hide'， 隐藏； 默认 'remove',
            onlyClose: false,               // 是否只关闭弹层: true, 只关闭， false: 同时关闭遮罩层； 默认 false
            callback: function($el) {       // 点击确认按钮， 参数：$el,弹层选择器
                $el.remove();
                $('.shade').remove();
            }
        });

    </script>
<?php
}
add_action( 'gp_core_render_message', 'gp_themes_render_message' );

//function gp_disable_dashboard() {
//    if ( current_user_can('subscriber') && is_admin() ) {
//        wp_redirect( home_url() );
//        exit;
//    }
//}
//add_action( 'admin_init', 'gp_disable_dashboard' );

function gp_exception_handler( $ex ) {
    get_header();
    ?>
    <div class="m-main">
        <div class="m-filter">
            <div class="container">
                <?php
                echo $ex->getMessage();
                ?>
            </div>
        </div>
    </div>
    <?php
    get_footer();
}
set_exception_handler('gp_exception_handler' );