<?php
/**
 * GamPress Sns Service Loader.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage Sns
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the GamPress Sns Service Component.
 *
 * @since 1.0
 */

class GP_Sns_OAuth_Wechat extends GP_Sns_Api {
    
    var $redirect_url;
    var $code;

    public function __construct() {
        $this->app_id            = gp_get_sns_wechat_app_id();
        $this->app_secre         = gp_get_sns_wechat_app_secret();
    }

    public function request_authorize_code( $callback = '', $scope = 'snsapi_userinfo' ) {
        $this->redirect_url   = urlencode( gp_get_root_domain() . '/' . gp_get_sns_slug() . '/oauth_callback/wechat?callback=' . $callback );
        $state                = $_SESSION ['state'] = md5 ( uniqid ( rand (), true ) );
        
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$this->redirect_url}&response_type=code&scope={$scope}&state={$state}#sns_redirect";
        
        gp_core_redirect( $url );
    }

    /**
     * 判断 当前用户 是否已经关注 服务号
     */
    public function is_subscribe( $open_id ) {
        $access_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->app_id}&secret={$this->app_secre}";
        $json = json_decode( file_get_contents( $access_url ), true );
        $this->access_token = $json['access_token'];

        $info_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->access_token . '&openid=' . $open_id;

        $user_info = json_decode( file_get_contents( $info_url ), true );
        return $user_info['subscribe'] == 1;
    }
    
    public function request_access_token() {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secre}&code={$this->code}&grant_type=authorization_code";
        $json = json_decode( file_get_contents( $url ), true );

        $this->access_token = $json['access_token'];
        if ( empty( $this->access_token ) ) {
            $errmsg = $json['errmsg'];
            throw new Exception( $errmsg );
        }
        $this->openid = $json['openid'];
        //$this->unionid = $json['unionid'];
    }

    public function send_template_data( $data ) {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->access_token;

        $resp_data = http_request( $url, $data );
        return json_decode( $resp_data, true );
    }

    public function get_user_info() {
        $info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $this->access_token . '&openid=' . $this->openid;

        $user_info = json_decode( file_get_contents( $info_url ), true );
        if( empty( $user_info ) )
            throw new Exception( '授权时发生错误' );

        // 当scope为snsapi_base会返回这个
        if ( isset( $user_info['errcode'] ) && $user_info['errcode'] == 48001 ) {
            $user_info['openid'] = $this->openid;
            $user_info['headimgurl'] = $user_info['nickname'] = '';
        }

        $sns_user = new GP_Sns_User();
        $sns_user->avatar = $user_info['headimgurl'];
        $sns_user->ID = $user_info['openid'];
        $sns_user->user_name = $user_info['nickname'];
        return $sns_user;
    }
}