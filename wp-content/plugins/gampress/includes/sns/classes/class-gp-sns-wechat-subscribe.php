<?php
/**
 * GamPress Sns Subscribe Loader.
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
 * Defines the GamPress Sns Subscribe Component.
 *
 * @since 1.0
 */

class GP_Sns_Wechat_Subscribe extends GP_Sns_Api {

    var $token = '';

    public function __construct() {
        $this->app_id            = gp_get_sns_wechat_sub_app_id();
        $this->app_secre         = gp_get_sns_wechat_sub_app_secret();
    }

    public function request_access_token() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->app_id}&secret={$this->app_secre}";
        $json = json_decode( file_get_contents( $url ), true );

        $this->access_token = $json['access_token'];
        if ( empty( $this->access_token ) ) {
            $errmsg = $json['errmsg'];
            throw new Exception( $errmsg );
        }
    }

    public function get_user_info() {
        $info_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->access_token . '&openid=' . $this->openid;

        $user_info = json_decode( file_get_contents( $info_url ), true );
        if( empty( $user_info ) )
            throw new Exception( '授权时发生错误' );
        $sns_user = new GP_Sns_User();
        $sns_user->avatar = $user_info['headimgurl'];
        $sns_user->ID = $user_info['openid'];
        $sns_user->user_name = $user_info['nickname'];
        return $sns_user;
    }

    public function check() {
        $echoStr = $_GET["echostr"];
        
        if( $this->check_signature() ) {
            return $echoStr;
        }
    }
    
    private function check_signature() {
        $token = $this->token;$signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array( $token, $timestamp, $nonce );
        sort( $tmpArr );
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        return $tmpStr == $signature;
    }
    
    public function process() {
        $postStr = isset( $GLOBALS["HTTP_RAW_POST_DATA"] ) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';
        
        if ( !empty( $postStr ) ) {
            $this->logger( "R ".$postStr );
            
            $postObj = simplexml_load_string( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
            $RX_TYPE = trim( $postObj->MsgType );
            
            switch ($RX_TYPE) {
                case "event":
                    $result = $this->process_event( $postObj );
                    break;
                default:
                    $result = $this->transmit_text( $postObj, _x( 'Default Message', 'gampress' ) );
                    break;
            }
            $this->logger( "T ". $result );
            return $result;
        } else {
            return "";
        }
    }
    
    private function process_event( $object ) {
        $content = _x( 'Process Event Message', 'gampress' );
        $content = apply_filters( 'gp_wehchats_precess_event', $content, $object );
        return $content;
    }

    public static function transmit_text( $object, $content ) {
        $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
        $result = sprintf( $textTpl, $object->FromUserName, $object->ToUserName, time(), $content );
        return $result;
    }

    public static function transmit_news($object, $newsArray)
    {
        if ( !is_array( $newsArray ) )
            return '';

        $itemTpl = "<item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>";
        $item_str = "";
        foreach ( $newsArray as $item ) {
            $item_str .= sprintf( $itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url'] );
        }
        $xmlTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>%s</ArticleCount><Articles>$item_str</Articles></xml>";

        $result = sprintf( $xmlTpl, $object->FromUserName, $object->ToUserName, time(), count( $newsArray ) );
        return $result;
    }
    
    private function logger( $log_content ) {
    }
}
