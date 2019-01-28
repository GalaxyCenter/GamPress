<?php
global $current_user;
if ( is_super_admin( gp_loggedin_user_id() ) || $current_user->roles[0] == 'author' ) : ?>
    <div class="content">
        <div class="global-box no-line">
            <div class="hd">
                <h1>
                    <?php
                        $last_chapter = gp_books_get_last_chapter( $_GET['id'], GP_CHAPTER_ALL );
                        preg_match( '/第.*?章/u', $last_chapter->title, $matches );
                        ?>
                        已更新至：<?php if ( empty( $matches ) ) :?> 第<?php gp_chapter_order( $last_chapter );?>章 <?php endif;?>  <?php gp_chapter_title( $last_chapter );?>
                </h1>
            </div>
            <div class="bd">
                <form class="form-box">
                    <div class="form-group">
                        <div class="item">
                            <label>章节标题</label>
                            <input name="title" type="text" class="txt text-left" placeholder="请输入章节标题" maxlength="32" />
                        </div>
                        <div class="item">
                            <label>章节内容</label>
                            <textarea name="content" class="txt text-left" placeholder="请输入章节内容"></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="book_id" value="<?php echo $_GET['id'];?>"/>
                    <input type="hidden" name="action" value="import_chapter"/>
                    <input type="hidden" name="url" value="/wp-admin/admin-ajax.php"/>
                </form>
            </div>
        </div>
    </div>
    <div class="wrap-btn absolute">
        <button id="btn_user_book_import" class="btn-primary btn-block radius">保存</button>
    </div>
<?php endif;?>
