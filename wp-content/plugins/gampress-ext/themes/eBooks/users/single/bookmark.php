<?php $action = gp_current_action(); ?>
<div class="content" id="box_user_bookmark">
    <div class="nav">
        <a href="<?php echo gp_loggedin_user_domain() . 'bookmark/bookmarks' ?>" class="item <?php active( 'bookmarks', $action, true );?>">我的追书</a>
        <a href="<?php echo gp_loggedin_user_domain() . 'bookmark/history' ?>" class="item <?php active( 'history', $action, true );?>">最近阅读</a>
    </div>
    <div id="list_user_bookmark" data-type="<?php echo $action;?>" class="uc-box">
        <div class="pic-list list"></div>
        <script id="tpl_user_bookmark_list" type="text/html">
            {{each items as value i}}
            <a href="{{value.book.link}}?from=adx-<?php echo $action;?>" class="item">
                {{if value.rid == '1' }}<span class="badge"><em>荐</em></span>{{/if}}
                <img src="<?php echo get_template_directory_uri(); ?>/dist/images/cover.png" data-src="{{value.book.cover}}" class="cover"/>
                <p>{{value.book.title}}</p>
            </a>
            {{/each}}
        </script>
        <p class="loading">努力加载中...</p>
    </div>
</div>
