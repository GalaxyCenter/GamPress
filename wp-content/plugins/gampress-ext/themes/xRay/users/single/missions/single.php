<?php
$mission = gp_get_current_mission();
if ( $mission->user_id == gp_displayed_user_id() ) : ?>
<div class="row">
    <div class="col-md-4">姓名：<?php gp_mission_custom_name( $mission );?></div>
    <div class="col-md-4">性别：<?php gp_mission_gender( $mission );?></div>
    <div class="col-md-4">状态：<?php gp_mission_status( $mission );?></div>
    <div class="col-md-4">电话：<?php gp_mission_phone( $mission );?></div>
    <div class="col-md-4">银行卡：<?php gp_mission_card( $mission );?></div>
    <div class="col-md-4">推荐时间：<?php gp_mission_post_time( $mission );?></div>
    <div class="col-md-4">备注：<?php gp_mission_remark( $mission );?></div>
    <?php if ( $mission->status == GP_MISSION_1 ) : ?>
    <div class="col-md-4"><a class="bg-success" href="<?php echo gp_loggedin_user_domain() . gp_get_missions_slug() . '/edit/' . $mission->id; ?>">编辑</a></div>
    <?php endif ;?>
</div>
<?php endif;?>