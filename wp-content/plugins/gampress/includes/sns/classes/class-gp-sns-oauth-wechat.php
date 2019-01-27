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

    public function request_authorize_code( $callback = '', $forwardCode = 'false', $scope = 'snsapi_userinfo' ) {
        $this->redirect_url   = urlencode( gp_get_root_domain() . '/' . gp_get_sns_slug() . '/oauth_callback/wechat?callback=' . $callback . '&forwardCode=' . $forwardCode );
        $state                = $_SESSION ['state'] = md5 ( uniqid ( rand (), true ) );
        
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$this->redirect_url}&response_type=code&scope={$scope}&state={$state}#sns_redirect";
        
        gp_core_redirect( $url );
    }

    public function request_access_token() {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secre}&code={$this->code}&grant_type=authorization_code";
        $json = json_decode( file_get_contents( $url ), true );
        $this->access_token = $json['access_token'];

        if ( empty( $this->access_token ) || !isset( $json['openid'] ) ) {
            $errmsg = $json['errmsg'];
            throw new Exception( '微信授权登录失败：' . $errmsg . ",请重新登录" );
        }
        $this->openid = $json['openid'];
        $this->unionid = isset( $json['unionid'] ) ? $json['unionid'] : '';
    }

    public function get_user_info() {
        $info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $this->access_token . '&openid=' . $this->openid;

        $user_info = json_decode( file_get_contents( $info_url ), true );
        if( empty( $user_info ) )
            throw new Exception( '授权时发生错误' );

        // 当scope为snsapi_base 静默登录 会返回这2个
        if ( isset( $user_info['errcode'] ) && $user_info['errcode'] == 48001 ) {
            $user_info['openid'] = $this->openid;
            $user_info['headimgurl'] = $user_info['nickname'] = '';
            $user_info['unionid'] = $this->unionid;
        }

//{
//"subscribe": 1,
//"openid": "oDrutvwtpNndSZotkADD_nOeI0rI",
//"nickname": "原石",
//"sex": 1,
//"language": "zh_CN",
//"city": "郑州",
//"province": "河南",
//"country": "中国",
//"headimgurl": "http://wx.qlogo.cn/mmopen/PiajxSqBRaELDoxfQuoibBSYe4vhqERr08MtwFOGNmicl7rVCPvk4CnXtoIfqJMnnwQBZUJibyf7saPYm9HrwPBlWA/132",
//"subscribe_time": 1515651594,
//"unionid": "oNAF-0hutmLClWOc4OsUiEx5EXqg",
//"remark": "",
//"groupid": 0,
//"tagid_list": [ ]
//}
        if ( empty( $user_info['openid'] ) ) {
            throw new Exception( '微信登录失败，无法获取微信信息' );
        }

        $sns_user = new GP_Sns_User();
        $sns_user->avatar = $user_info['headimgurl'];
        $sns_user->ID = $user_info['openid'];
        $sns_user->user_name = $user_info['nickname'];
        $sns_user->unionid = $user_info['unionid'];
        //$sns_user->gender = $user_info['sex'];
        return $sns_user;
    }
}