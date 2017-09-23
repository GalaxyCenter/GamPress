<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/8
 * Time: 21:51
 */

defined( 'ABSPATH' ) || exit;

class GP_Sns_OAuth_Weibo extends GP_Sns_Api {

    public function __construct() {
        $this->app_id            = gp_get_sns_weibo_app_id();
        $this->app_secre         = gp_get_sns_weibo_app_secret();
    }

    public function request_authorize_code( $callback = '', $scope = '' ) {
        $this->redirect_url   = urlencode( gp_get_root_domain() . '/' . gp_get_sns_slug() . '/oauth_callback/weibo' );
        $url = "https://api.weibo.com/oauth2/authorize?client_id={$this->app_id}&redirect_uri={$this->redirect_url}&response_type=code";

        gp_core_redirect( $url );
    }

    public function request_access_token() {
        $this->redirect_url   = urlencode( gp_get_root_domain() . '/' . gp_get_sns_slug() . '/oauth_callback/weibo' );
        $url = 'https://api.weibo.com/oauth2/access_token';
        $data = "client_id={$this->app_id}&client_secret={$this->app_secre}&grant_type=authorization_code&code={$this->code}&redirect_uri={$this->redirect_url}";
        $resp = http_request( $url, 'POST', $data );

        GP_Log::INFO( 'weibo url:' . $url . '  resp:' . $resp );
        $json = json_decode( $resp );

        $this->access_token = $json->access_token;
        $this->user_id = $json->uid;
        if ( empty( $this->access_token ) ) {
            $errmsg = $json->error_description;
            throw new Exception( $errmsg );
        }
    }

    public function get_user_info() {
        $url = "https://api.weibo.com/2/users/show.json?access_token={$this->access_token}&uid={$this->user_id}";
        $user_info = json_decode( http_request( $url, 'GET' ) );
        if( empty( $user_info ) )
            throw new Exception( '授权时发生错误' );
        $sns_user = new GP_Sns_User();
        $sns_user->avatar = $user_info->profile_image_url;
        $sns_user->ID = $user_info->id;
        $sns_user->user_name = $user_info->name;
        return $sns_user;
    }
}