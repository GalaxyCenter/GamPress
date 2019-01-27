<form class="form-signin" method="post">
    <label for="nice_name" class="">姓名：</label><input type="nice_name" id="nice_name" name="nice_name" class="form-control" placeholder="请填写您的姓名" value="<?php gp_displayed_user_fullname() ;?>" required autofocus>
    <label for="phone" class="">电话：</label><input type="phone" id="phone" name="phone" class="form-control" placeholder="请填写您的手机号码" value="<?php echo get_user_meta( gp_displayed_user_id(), 'phone', true ) ;?>" required autofocus>
    <button class="btn btn-lg btn-primary btn-block" type="submit">更新</button>
</form>