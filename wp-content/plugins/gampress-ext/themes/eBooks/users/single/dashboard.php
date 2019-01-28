<?php
global $current_user;
$user_id = gp_loggedin_user_id();
?>

<div class="content">
    <div class="uc-info">
        <span>
            <img src="<?php gp_sns_user_avatar( $user_id );?>" class="vest"/>
        </span>
<!--        <p><em>LV14</em></p>-->
        <h3><?php gp_displayed_user_fullname();?></h3>
        <p class="mt0">ID:<?php echo gp_loggedin_user_id() + 1000000;?></p>
    </div>
    <div class="uc-ac">
        <a href="<?php echo gp_loggedin_user_domain();?>recharge" class="btn-default r" id="recharge_btn">立即充值<em>奖</em></a>
        <i class="icon-uc-1"></i>
        <h3>我的呆熊币</h3>
        <p><em class="font-orange"><?php echo  gp_orders_get_total_coin_for_user( $user_id );?></em>呆熊币<em class="ml10"><?php echo  gp_orders_get_tickets_total_fee( $user_id );?>赠币</em></p>
    </div>
    <div class="uc-list">
        <?php if ( get_user_meta( $user_id,  'name_updated', true ) == false ) :?>
        <a href="<?php echo gp_loggedin_user_domain();?>profile/edit" class="item"><i class="icon-uc-2"></i>修改资料</a>
        <?php endif;?>
        <a href="<?php echo gp_loggedin_user_domain();?>record/inpour" class="item"><i class="icon-uc-3"></i>充值记录</a>
        <a href="<?php echo gp_loggedin_user_domain();?>record/outpour" class="item"><i class="icon-uc-4"></i>消费记录</a>
        <a href="<?php echo gp_loggedin_user_domain();?>msg" class="item tip" id="user_tip"><i class="icon-uc-5"></i>消息中心</a>
        <a href="<?php echo gp_loggedin_user_domain();?>bookmark/bookmarks" class="item"><i class="icon-uc-6"></i>我的追书</a>
        <a href="<?php echo gp_loggedin_user_domain();?>bookmark/history" class="item"><i class="icon-uc-7"></i>最近阅读</a>
        <?php if( is_super_admin( $user_id ) || $current_user->roles[0] == 'author' )  :?>
        <a href="<?php echo gp_loggedin_user_domain();?>book/list" class="item"><i class="icon-uc-7"></i>我的作品</a>
        <?php endif;?>
        <?php if ( is_super_admin( $user_id ) ) :?>
        <a href="/wp-admin/" class="item"><i class="icon-uc-8"></i>后台管理</a>
        <?php endif;?>
    </div>
    <a href="<?php echo wp_logout_url( home_url() ); ?>" class="uc-btn">退出登录</a>
</div>
