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
        $this->token             = gp_get_sns_wechat_sub_app_token();
    }

    public function check() {
        $echoStr = $_GET["echostr"];
        
        if( $this->check_signature() ) {
            return $echoStr;
        }
    }
    
    private function check_signature() {
        $token = $this->token;
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array( $token, $timestamp, $nonce );
        sort( $tmpArr );
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        return $tmpStr == $signature;
    }
    
    public function process() {
        if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
            $GLOBALS["HTTP_RAW_POST_DATA"] = file_get_contents( 'php://input' );
        }
        $postStr = isset( $GLOBALS["HTTP_RAW_POST_DATA"] ) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';
        
        if ( !empty( $postStr ) ) {
            $postObj = simplexml_load_string( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
            $RX_TYPE = trim( $postObj->MsgType );
            
            switch ($RX_TYPE) {
                case 'text':
                    $result = $this->process_text( $postObj );
                    break;
                case "event":
                    $result = $this->process_event( $postObj );
                    break;
                default:
                    $result = $this->transmit_text( $postObj, apply_filters( 'gp_wehchats_default_message', _x( 'Default Message', 'gampress' ) ) );
                    break;
            }
            return $result;
        } else {
            return "";
        }
    }

    private function process_text( $object ) {
        $content = apply_filters( 'gp_wehchats_precess_text', $object->Content, $object );
        return $content;
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
}
