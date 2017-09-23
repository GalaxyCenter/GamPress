<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/8/26
 * Time: 8:22
 */

class GP_Pays_Wechat_Pack extends GP_Pays {

    var $key;

    var $openid;

    public function do_pay( $args ) {
        require_once  GP_PLUGIN_DIR . 'includes/pays/libs/wechat/WxPay.SendPack.php';

        $obj = array();
        $obj['mch_billno'] = $args['order_id'];
        $obj['mch_id'] = $args['mch_id']; //"1231369502";//商户id
        $obj['wxappid'] = $args['wxappid'];//"wx59234bcb3a7cb9f1"; //appid

        $obj['send_name'] = $args['sender'];
        $obj['re_openid'] = gp_sns_get_sns_user_id();//接收红包openid
        $obj['total_amount'] = $args['product_fee'];//最低1元，单位分

        $obj['total_num'] = 1;
        $obj['wishing'] = $args['wishing'];
        $obj['client_ip'] = $_SERVER['REMOTE_ADDR'];
        $obj['act_name'] = $args['product_name'];
        $obj['remark'] = $args['product_name'];

        $wxpay = new SendPack();
        $resp = $wxpay->pay( $obj, $args['wechat_key'] );
        GP_Log::INFO( json_encode( $resp ) );
        return $resp['result_code'] != 'FAIL';
    }
}