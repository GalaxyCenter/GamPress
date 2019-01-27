<?php get_header(); ?>
    <div class="container">
        <h1>欢迎 <?php gp_displayed_user_fullname() ?></h1>

        <div class="row">
            <div class="col-sm-6"><a href="<?php echo gp_displayed_user_domain();?>missions/edit">我要推荐</a></div>
            <div class="col-sm-6"><a href="<?php echo gp_displayed_user_domain();?>missions/list">我的客户</a></div>
            <div class="col-sm-6"><a href="<?php echo gp_displayed_user_domain();?>missions/complete">我的佣金</a></div>
            <div class="col-sm-6"><a href="<?php echo gp_displayed_user_domain();?>profile">我的信息</a></div>
            <?php if ( is_super_admin() ) :?>
                <div class="col-sm-6"><a href="<?php echo gp_displayed_user_domain();?>/missions">管理</a></div>
            <?php endif;?>
        </div>

        <?php

        if ( gp_is_user_profile() ) :
            locate_template( array( 'users/single/profile.php'  ), true );
        elseif ( gp_is_user_missions() ):
            locate_template( array( 'users/single/missions.php'  ), true );
        endif;
        ?>

    </div>
<?php get_footer();?>