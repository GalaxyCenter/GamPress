<?php get_header(); ?>
    <div class="container">

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>客户名</th>
                    <th>状态</th>
                    <th>报备时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $page = gp_action_variable( 0 );
                $per_page = 2;
                $datas = gp_missions_get_missions( array(
                    'page'           => $page,
                    'per_page'       => $per_page
                ) );
                if ( $datas['total'] == 0 ) : ?>
                    <tr><td colspan="4">没有内容</td></tr>
                    <?php
                else:
                    foreach ( $datas['items'] as $mission ) : ?>
                        <tr>
                            <td><?php gp_mission_custom_name( $mission );?></td>
                            <td><label class="bg-danger"><?php gp_mission_status( $mission );?></label></td>
                            <td><?php gp_mission_post_time( $mission );?></td>
                            <td><a class="bg-success" href="<?php echo gp_loggedin_user_domain() . gp_get_missions_slug() . '/view/' . $mission->id; ?>">查看详情</a></td>
                        </tr>
                        <?php
                    endforeach;
                endif;?>
                </tbody>
            </table>
            <div class="u-pager">
                <?php
                $url_patter = gp_loggedin_user_domain() . gp_get_missions_slug() . '/list/%1$s';
                par_pagenavi( $page, $per_page, $datas['total'], $url_patter ); ?>
            </div>
        </div>

    </div>
<?php get_footer();?>