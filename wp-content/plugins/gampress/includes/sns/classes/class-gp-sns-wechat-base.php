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

class GP_Sns_Wechat_Base extends GP_Sns_Api {

    var $redirect_url;
    var $code;

    public function __construct() {
        $this->app_id            = gp_get_sns_wechat_app_id();
        $this->app_secre         = gp_get_sns_wechat_app_secret();
    }

    public function request_access_token() {
        $this->access_token = wp_cache_get( 'gp_wechat_access_token', 'gp' );
        if ( empty( $this->access_token ) ) {
            GP_Log::INFO("access_token empty");
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->app_id}&secret={$this->app_secre}";
            $json = json_decode( file_get_contents( $url ), true );
            $this->access_token = $json['access_token'];
            if ( empty( $this->access_token ) ) {
                $errmsg = $json['errmsg'];
                throw new Exception( $errmsg );
            }
            wp_cache_set( 'gp_wechat_access_token', $this->access_token, 'gp', 6000 );
        }
    }

    /**
     * 判断 当前用户 是否已经关注 服务号
     */
    public function is_subscribe() {
        $user_info = get_user_info();
        return $user_info->subscribe == 1;
    }

    public function get_user_info() {
        $info_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->access_token . '&openid=' . $this->openid;

        $user_info = json_decode( file_get_contents( $info_url ), true );
        if ( isset( $user_info['errcode'] ) ) {
            GP_Log::INFO( 'get_user_info error:' . $user_info['errmsg'] );
        }
        $sns_user = new GP_Sns_User();
        $sns_user->avatar = $user_info['headimgurl'];
        $sns_user->ID = $user_info['openid'];
        $sns_user->user_name = $user_info['nickname'];
        $sns_user->unionid = $user_info['unionid'];
        $sns_user->subscribe = $user_info['subscribe'];
        $sns_user->gender = $user_info['sex'];
        $sns_user->subscribe = $user_info['subscribe'];
        return $sns_user;
    }

    public function send_template_data( $data ) {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->access_token;

        $data = json_encode( $data );
        $resp_data = http_request( $url, 'POST', $data );
        return json_decode( $resp_data, true );
    }

    public function get_users( $next_openid = false ) {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $this->access_token . '&next_openid=' . $next_openid;

        $json = json_decode( file_get_contents( $url ), true );
        return $json;
    }
}