<?php $action = gp_current_action(); ?>
<div class="content" id="box_user_record">
    <div class="nav">
        <a href="<?php echo gp_loggedin_user_domain() . 'record/inpour' ?>" class="item <?php active( 'inpour', $action, true );?>">充值记录</a>
        <a href="<?php echo gp_loggedin_user_domain() . 'record/outpour' ?>" class="item <?php active( 'outpour', $action, true );?>">消费记录</a>
    </div>
    <div id="list_user_coin_bill" data-type="<?php echo $action == 'inpour' ? GP_Orders_Coin_Bill::RECHARGE : GP_Orders_Coin_Bill::PAY | GP_Orders_Coin_Bill::TICKET ;?>">
        <div class="list item-list"></div>
        <script id="tpl_user_coin_bill_list" type="text/html">
            {{each items as value i}}
            <div class="item">
                <em class="r">{{value.friendly_time}}</em>
                <h3>{{if value.type==1}}充值{{else}}支付{{/if}}{{value.fee}}呆熊币</h3>
                <p>{{value.description}}</p>
            </div>
            {{/each}}
        </script>
        <p class="loading">努力加载中...</p>
    </div>
</div>
