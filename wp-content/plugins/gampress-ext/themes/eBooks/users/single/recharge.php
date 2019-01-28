<?php
$redirect = isset( $_GET['redirect'] ) ? urlencode( $_GET['redirect'] ) : '';
?>

<div class="content" id="pay_content">
    <form method="post" action="/orders/create?redirect=<?php echo $redirect;?>" class="form-box">
        <div class="sub-box">
            <div class="hd">
                <h3>
                    <?php if ( is_weixin_browser() ) :?>
                    <i class="icon-pay-wx"></i>微信支付
                    <?php else:?>
                    <i class="icon-pay-ali"></i>支付宝支付
                    <?php endif;?>
                </h3>
            </div>
            <div class="bd">
                <ul class="money-list">
                    <li _price="10" data-item_id="6">
                        <a href="javascript:;" class="item">
                            <div class="t">
                                <h3>1000币</h3>
                                <p>无折扣</p>
                            </div>
                            <div class="b">
                                <div class="btn-block">10元</div>
                            </div>
                        </a>
                    </li>
                    <li _price="30" data-item_id="1">
                        <a href="javascript:;" class="item">
                            <div class="t">
                                <h3>3000币</h3>
                                <p>赠300币</p>
                                <span class="tip">9.0折</span>
                            </div>
                            <div class="b">
                                <div class="btn-block">30元</div>
                            </div>
                        </a>
                    </li>
                    <li class="active" _price="50" data-item_id="2">
                        <a href="javascript:;" class="item">
                            <div class="t">
                                <h3>5000币</h3>
                                <p>赠900币</p>
                                <span class="tip">8.5折</span>
                            </div>
                            <div class="b">
                                <div class="btn-block">50元</div>
                            </div>
                        </a>
                    </li>
                    <li _price="100" data-item_id="3">
                        <a href="javascript:;" class="item">
                            <div class="t">
                                <h3>10000币</h3>
                                <p>赠2500币</p>
                                <span class="tip">8.0折</span>
                            </div>
                            <div class="b">
                                <div class="btn-block">100元</div>
                            </div>
                        </a>
                    </li>
                    <li _price="200" data-item_id="4">
                        <a href="javascript:;" class="item">
                            <div class="t">
                                <h3>20000币</h3>
                                <p>赠6500币</p>
                                <span class="tip">7.5折</span>
                            </div>
                            <div class="b">
                                <div class="btn-block">200元</div>
                            </div>
                        </a>
                    </li>
<!--                    <li _price="300" data-item_id="5">-->
<!--                        <a href="javascript:;" class="item">-->
<!--                            <div class="t">-->
<!--                                <h3>30000币</h3>-->
<!--                                <p>赠13000币</p>-->
<!--                                <p class="font-red">腾讯视频年卡</p>-->
<!--                                <span class="tip">7.0折</span>-->
<!--                            </div>-->
<!--                            <div class="b">-->
<!--                                <div class="btn-block">300元</div>-->
<!--                            </div>-->
<!--                        </a>-->
<!--                    </li>-->
                    <li _price="500" data-item_id="7">
                        <a href="javascript:;" class="item">
                            <div class="t">
                                <h3>50000币</h3>
                                <p>赠33000币</p>
                                <span class="tip">6.0折</span>
                            </div>
                            <div class="b">
                                <div class="btn-block">500元</div>
                            </div>
                        </a>
                    </li>
                    <?php if ( is_super_admin( gp_loggedin_user_id() ) ) :?>
                    <li _price="0.01" data-item_id="8">
                        <a href="javascript:;" class="item">
                            <div class="t">
                                <h3>1币</h3>
                                <p>赠0</p>
                                <span class="tip">管理员</span>
                            </div>
                            <div class="b">
                                <div class="btn-block">0.01</div>
                            </div>
                        </a>
                    </li>
                    <?php endif;?>
                </ul>
            </div>
        </div>
        <div class="wrap-btn mt30">
            <div class="item-box">
                <h3>说明：</h3>
                <p>1、赠币有效期为30天。赠币到期后，将自动清零。</p>
                <p>2、1元=100呆熊币。充值成功后金额会立即到达您的账户中；</p>
                <p>3、所充值金额不支持退款或提现。如充值遇到问题，请关注微信公众号【<em class="font-orange">adaixiongread</em>】反馈，我们会及时为您服务。客服时间：周一到周日 9:00-18:00。</p>
            </div>
        </div>
        <input type="hidden" name="product_id" id="product_id" value="0" />
        <input type="hidden" name="item_id" id="item_id" value="2" />
        <input type="hidden" name="price" id="price" value="50" />
        <input type="hidden" name="quantity" id="quantity" value="1" />
        <input type="hidden" name="total_fee" id="total_fee" value="50" />
        <input type="hidden" name="pay_module" id="pay_module" value="<?php if ( is_weixin_browser() ) { echo 'wechat';} else { echo 'alipay'; };?>" />
        <input type="hidden" name="product_name" id="product_name" value="阿呆熊充值50" data-text="阿呆熊充值" />
        <input type="hidden" name="product_description" id="product_description" value="阿呆熊充值50" data-text="阿呆熊充值" />
        <input type="hidden" name="product_type" id="pay_module" value="<?php echo GP_Orders_Order::RECHARGE;?>" />
    </form>
</div>