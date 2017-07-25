<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 11:28
 */

defined( 'ABSPATH' ) || exit;

require_once  GP_PLUGIN_DIR . 'includes/pays/libs/wechat/WxPay.JsApiPay.php';
require_once  GP_PLUGIN_DIR . 'includes/pays/libs/wechat/lib/WxPay.Notify.php';

class GP_Pays_Wechat extends GP_Pays {

    var $appid;

    var $secret;

    var $mchid;

    var $key;

    public function __construct() {
        $this->appid    = gp_get_pays_wechat_app_id();
        $this->secret   = gp_get_pays_wechat_app_secret();
        $this->mchid    = gp_get_pays_wechat_mchid();
        $this->key      = gp_get_pays_wechat_key();
    }

    public function do_pay( $order_id, $product_name, $product_fee, $redirect = '' ) {
//①、获取用户openid
        GP_Log::Init();

        $tools = new JsApiPay();
//        $openId = $tools->GetOpenid();

        // 微信以分作为单位所以要*100
        $wx_fee = floatval( $product_fee ) * 100;
//②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($product_name);
        $input->SetAttach($order_id);
        $input->SetOut_trade_no($order_id);
        $input->SetTotal_fee( $wx_fee );
        $input->SetTime_start(date("YmdHis"));
        //$input->SetTime_expire(date("YmdHis", time() + 1200));
        //$input->SetGoods_tag("test");
        $input->SetNotify_url( gp_get_pays_wechat_notify_url() );
        if ( is_weixin_browser() ) {
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid( gp_sns_get_sns_user_id() );
            GP_Log::INFO( 'Wechat@do_pay$input:' . json_encode( $input ) );
            $order = WxPayApi::unifiedOrder($input);
            GP_Log::INFO( 'Wechat@do_pay$order:' . json_encode( $order ) );
            $jsApiParameters = $tools->GetJsApiParameters($order);
            GP_Log::INFO( 'Wechat@do_pay$jsApiParameters:' . $jsApiParameters );
            ?>
            <html>
            <head>
                <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <title>
                    <?php bloginfo( 'name' ); ?>
                    微信支付
                    <?php echo $product_name;?> - <?php echo $product_fee;?>
                </title>
                <script type="text/javascript">
                    //调用微信JS api 支付
                    function jsApiCall()
                    {
                        WeixinJSBridge.invoke(
                            'getBrandWCPayRequest',
                            <?php echo $jsApiParameters; ?>,
                            function(res){
                                WeixinJSBridge.log(res.err_msg);
                                //alert(res.err_code+res.err_desc+res.err_msg);
                                if(res.err_msg.indexOf('ok')>0){
                                    window.location.href='/pays/success?redirect=<?php echo $redirect;?>';
                                }else{
                                    //alert(JSON.stringify(res));
                                    window.location.href='/pays/fail?redirect=<?php echo $redirect;?>';
                                }
                            }
                        );
                    }

                    function callpay()
                    {
                        if (typeof WeixinJSBridge == "undefined"){
                            if( document.addEventListener ){
                                document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                            }else if (document.attachEvent){
                                document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                                document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                            }
                        }else{
                            jsApiCall();
                        }
                    }
                    callpay()
                </script>
            </head>
            <body>
            </body>
            </html>
            <?php
            header('HTTP/1.1 200 OK' );
            die;
        }
    }

    public function notify() {
        GP_Log::Init();
        $notify = new PayNotifyCallBack();
        $notify->Handle(true);
    }

    public function verify( $params ) {
    }
}

class PayNotifyCallBack extends WxPayNotify {
    //查询订单
    public function Queryorder($transaction_id) {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        GP_Log::DEBUG("PayNotifyCallBack@Queryorder:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS") {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg) {
        GP_Log::INFO("PayNotifyCallBack@NotifyProcess:" . json_encode($data));
        if(!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }
        GP_Log::INFO("PayNotifyCallBack@gp_pays_success:" . $data['out_trade_no'] . $data['total_fee'] . $data['time_end']);

        // 微信以分作为单位, 这里要将转换为分
        $total_fee = $data['total_fee'] / 100;
        do_action( 'gp_pays_success', $data['out_trade_no'], $total_fee, $data['time_end'] );
        return true;
    }
}