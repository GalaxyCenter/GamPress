<div class="content">
    <div class="global-box no-line">
        <form class="form-box">
        <div class="form-group">
            <div class="item">
                <label>昵称</label>
                <input name="name" type="text" class="txt" placeholder="<?php gp_displayed_user_fullname();?>" value="<?php gp_displayed_user_fullname();?>"/>
                <input type="hidden" name="action" value="update_displayname"/>
                <input type="hidden" name="url" value="/wp-admin/admin-ajax.php"/>
            </div>
        </div>
        </form>
    </div>
</div>
<div class="wrap-btn absolute">
    <button id="btn_user_profile_save" class="btn-primary btn-block radius">保存</button>
</div>