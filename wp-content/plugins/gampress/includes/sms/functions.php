<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/11
 * Time: 16:26
 */

function gp_sms_send_code( $phone, $content ) {
    //请求地址，格式如下，不需要写https://
    $serverIP = 'app.cloopen.com';
    //请求端口
    $serverPort = '8883';
    //REST版本号
    $softVersion = '2013-12-26';

    $rest = new GP_Sms_Ytx_Rest($serverIP, $serverPort, $softVersion);
    $rest->setAccount(gp_get_sms_ytx_accound_sid(), gp_get_sms_ytx_auth_token());
    $rest->setAppId(gp_get_sms_ytx_app_id());

    // 发送模板短信
    if ( !is_array( $content ) ) {
        $content = array( $content );
    }
    $result = $rest->sendTemplateSMS($phone, $content, gp_get_sms_ytx_template_code_id());
    if ($result == NULL) {
        //echo "result error!";
        return false;
    }
    return (int) $result->statusCode;
//    if ($result->statusCode != 0) {
//        echo "error code :" . $result->statusCode . "<br>";
//        echo "error msg :" . $result->statusMsg . "<br>";
//        //TODO 添加错误处理逻辑
//    } else {
//        echo "Sendind TemplateSMS success!<br/>";
//        // 获取返回信息
//        $smsmessage = $result->TemplateSMS;
//        echo "dateCreated:" . $smsmessage->dateCreated . "<br/>";
//        echo "smsMessageSid:" . $smsmessage->smsMessageSid . "<br/>";
//        //TODO 添加成功处理逻辑
//    }
}


function gp_sms_messages_get_message( $id ) {
    if ( ! is_numeric( $id ) ) {
        return false;
    }

    $key = 'gp_sms_message_' . $id;

    $service = wp_cache_get( $key );
    if ( empty( $service ) ) {
        $service = new GP_Sms_Message( $id );
        wp_cache_set( $key, $service );
    }

    return $service;
}

function gp_sms_get_message_code( $phone, $type ) {
    $code = GP_Sms_Message::get_code( $phone, $type );
    return $code;
}

function gp_sms_add_message( $args ) {
    if ( ! gp_is_active( 'sms' ) ) {
        return false;
    }

    $defaults = array(
        'id'                    => false,
        'type'                  => false,
        'user_id'               => false,
        'phone'                 => false,
        'content'               => false,
        'post_time'             => time()
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty( $type ) || empty( $phone ) || empty( $content ) )
        return false;

    if ( !empty( $id ) ) {
        $msg = gp_sms_messages_get_message( $id );
    } else {
        $msg = new GP_Sms_Message();
        $msg->id = $id;
    }

    $msg->user_id       = $user_id;
    $msg->type          = $type;
    $msg->phone         = $phone;
    $msg->content       = $content;
    $msg->post_time     = $post_time;
    if ( !$msg->save() )
        return $msg;

    return $msg->id;
}