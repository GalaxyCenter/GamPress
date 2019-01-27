<?php
$mission = gp_get_current_mission();
if ( $mission->user_id == gp_displayed_user_id() ) : ?>
<form class="form-signin" method="post">
    <div class="form-group">
        <label for="custom_name" class="">姓名：</label><input type="text" id="custom_name" name="custom_name" class="form-control" placeholder="请填写您客户的姓名" value="<?php gp_mission_custom_name( $mission );?>" required autofocus>
    </div>
    <div class="form-group">
        <label for="phone" class="">电话：</label><input type="phone" id="phone" name="phone" class="form-control" placeholder="请填写您客户的手机号码" value="<?php gp_mission_phone( $mission );?>" required autofocus>
    </div>
    <div class="form-group">
        <label for="card" class="">银行卡号：</label><input type="card" id="card" name="card" class="form-control" placeholder="请填写您的银行卡号" value="<?php gp_mission_card( $mission );?>" required autofocus>
    </div>
    <div class="form-group">
        <label for="phone" class="">性别：</label>
        <div class="radio">
            <label>
                <input type="radio" name="gender" id="gender1" class="" value="1" <?php checked( 1, gp_get_mission_gender( $mission ), 'checked' );?> required autofocus>
                男
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="gender" id="gender0" class="" value="0" <?php checked( 0, gp_get_mission_gender( $mission ), 'checked' );?> required autofocus>
                女
            </label>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="">备注：</label>
        <textarea class="form-control" rows="3" name="remark" placeholder="简单填写一些内容"><?php gp_mission_remark( $mission );?></textarea>
    </div>
    <input name="status" value="1" type="hidden"/>
    <button class="btn btn-lg btn-primary btn-block" type="submit">保存</button>
</form>
<?php endif;?>