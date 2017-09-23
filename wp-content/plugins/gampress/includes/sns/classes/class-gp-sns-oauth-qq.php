<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/9
 * Time: 12:57
 */

defined( 'ABSPATH' ) || exit;

class GP_Sns_OAuth_QQ extends GP_Sns_Api {

    var $redirect_url;
    var $code;

    public function __construct() {
        $this->app_id            = gp_get_sns_qq_app_id();
        $this->app_secre         = gp_get_sns_qq_app_secret();
    }

    public function request_authorize_code( $callback = '', $scope = 'get_user_info' ) {
        $this->redirect_url   = urlencode( gp_get_root_domain() . '/' . gp_get_sns_slug() . '/oauth_callback/qq?callback=' . $callback );
        $state                = $_SESSION ['state'] = md5 ( uniqid ( rand (), true ) );

        $url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id={$this->app_id}&redirect_uri={$this->redirect_url}&scope={$scope}&state={$state}";

        gp_core_redirect( $url );
    }

    public function request_access_token() {
        $this->redirect_url   = urlencode( gp_get_root_domain() . '/' . gp_get_sns_slug() . '/oauth_callback/qq?callback=' );
        $url = "https://graph.qq.com/oauth2.0/token?client_id={$this->app_id}&client_secret={$this->app_secre}&code={$this->code}&redirect_uri={$this->redirect_url}&grant_type=authorization_code";
        $resp = http_request( $url, 'GET' );
        if(strpos($resp, "callback") !== false){

            $lpos = strpos($resp, "(");
            $rpos = strrpos($resp, ")");
            $resp  = substr($resp, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($resp);

            if(isset($msg->error)){
                throw new Exception( 'QQ request token:' . $msg->error . ' msg:' . $msg->error_description );
            }
        }

        $params = array();
        parse_str($resp, $params);
        $this->access_token = $params["access_token"];
    }

    public function get_open_id() {
        $url = "https://graph.qq.com/oauth2.0/me?access_token={$this->access_token}";
        $resp = http_request( $url, 'GET' );

        if(strpos($resp, "callback") !== false){

            $lpos = strpos($resp, "(");
            $rpos = strrpos($resp, ")");
            $resp = substr($resp, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($resp);
        $this->openid = $user->openid;
        return $user->openid;
    }

    public function get_user_info() {
        $this->get_open_id();

        $url = "https://graph.qq.com/user/get_user_info?access_token={$this->access_token}&oauth_consumer_key={$this->app_id}&openid={$this->openid}";
        $user_info = json_decode( http_request( $url, 'GET' ) );
        if( empty( $user_info ) )
            throw new Exception( '授权时发生错误' );
        $sns_user = new GP_Sns_User();
        if ( empty( $user_info->figureurl_qq_2 ) ) {
            $sns_user->avatar = $user_info->figureurl_qq_1;
        } else {
            $sns_user->avatar = $user_info->figureurl_qq_2;
        }
        $sns_user->ID = $this->openid;
        $sns_user->user_name = $user_info->nickname;
        return $sns_user;
    }
}