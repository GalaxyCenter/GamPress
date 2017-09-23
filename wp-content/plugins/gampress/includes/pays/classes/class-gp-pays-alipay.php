<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 11:28
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GP_Pays_Alipay
 *
 *
 * https://doc.open.alipay.com/docs/doc.htm?spm=a219a.7629140.0.0.dsNjYY&treeId=108&articleId=104743&docType=1
 */
class GP_Pays_Alipay extends GP_Pays {

    var $alipay_id;
    var $alipay_partner;
    var $alipay_key;

    public function __construct() {
        $this->alipay_id            = gp_get_pays_alipay_id();
        $this->alipay_partner       = gp_get_pays_alipay_partner();
        $this->alipay_key           = gp_get_pays_alipay_key();
    }

    public function do_pay( $args ) {

        $params = array(
            "service"           => 'alipay.wap.create.direct.pay.by.user',
            "partner"           => $this->alipay_partner,
            "seller_id"         => $this->alipay_partner,
            "payment_type"	    => 1,
            "notify_url"	    => gp_get_pays_alipay_notify_url(),
            "return_url"	    => gp_get_pays_alipay_return_url() . '?redirect=' . urlencode( $args['redirect'] ),
            "_input_charset"	=> 'utf-8',
            "out_trade_no"	    => $args['order_id'],
            "subject"	        => $args['product_name'],
            "total_fee"	        => $args['product_fee'],
            "app_pay"	        => 'Y'
        );

        ksort($params);
        reset($params);

        $param = '';
        $sign  = '';

        foreach ($params AS $key => $val)
        {
            $param .= "$key=" .urlencode($val). "&";
            $sign  .= "$key=$val&";
        }

        $param = substr($param, 0, -1);
        $sign  = substr($sign, 0, -1). $this->alipay_key;
        $sign = md5($sign);

        $pay_link = "https://mapi.alipay.com/gateway.do?{$param}&sign={$sign}&sign_type=MD5";
        gp_core_redirect( $pay_link );
    }

    public function notify() {
        $params = $_POST;
        if ( isset( $_GET['redirect'] ) )
            unset($_GET['redirect']);

        $result = $this->verify( $params );
        if ($result)
            echo 'success';
        else
            echo 'false';
    }

    public function verify( $params ) {
        if ( !in_array( $params['trade_status'], array( 'TRADE_FINISHED', 'TRADE_SUCCESS' ) ) )
            return false;

        ksort($params);
        reset($params);

        $sign = '';
        foreach ($params AS $key => $val) {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code' && $key != 'act') {
                $sign .= "$key=$val&";
            }
        }

        $sign = substr($sign, 0, -1) . $this->alipay_key;
        if ( md5( $sign ) != $params['sign'] )
            return false;

        //return array( 'order_id' => $params['out_trade_no'], 'total_fee' => $params['total_fee'], 'notify_time' => $params['notify_time'] );
        do_action( 'gp_pays_success', $params['out_trade_no'], $params['total_fee'], $params['notify_time'] );
        return true;
    }
}