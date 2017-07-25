<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/5/11
 * Time: 16:26
 */

function sms_login() {
    if ( !gp_is_sms_component() || ! gp_is_current_action( 'login' ) )
        return false;

    $phone = $_POST['phone'];
    $code = $_POST['code'];

    $sms_code = gp_sms_get_message_code( $phone, GP_Sms_Message::$LOGIN_CODE );

    if ( $code == $sms_code || true ) {
        $user = new stdClass();
        $user->ID = $phone;
        $user->user_name = $phone;
        $user->head_img = '';

        $redirect = sns_autologin( 'sms', $user );
        if ( empty( $redirect ) ) {
            $redirect = $_POST['redirect'];
            $tab = $_POST['tab'];

            if ( $redirect == 'user' ) {
                $redirect = gp_core_get_user_domain( $user->user_id ) . $tab;
            }
        }
        ajax_die(0, '登录成功', $redirect);
    } else {
        ajax_die(1, '短信验证码错误', '');
    }
}
add_action( 'gp_actions', 'sms_login' );

function sms_request_code() {
    if ( !gp_is_sms_component() || ! gp_is_current_action( 'request_code' ) )
        return false;

    $phone = $_POST['phone'];

    $data = GP_Sms_Message::get( array(
        'phone'     => $phone,
        'type'      => GP_Sms_Message::$LOGIN_CODE,
        'time_in'   => array( time() - 600, time() )
    ) );

    if ( $data['total'] > 5 ) {
        ajax_die( 2, '', false ); // 1个小时超出5次
    } else {
        $code = gp_sms_get_message_code( $phone, GP_Sms_Message::$LOGIN_CODE );
        if ( empty( $code ) ) {
            $code = rand( 1000, 9999 );
            $id = gp_sms_add_message( array(
                'type'                  => GP_Sms_Message::$LOGIN_CODE,
                'phone'                 => $phone,
                'content'               => $code,
            ) );

            if ( empty( $id ) ) {
                ajax_die(3, '短信发送失败', '');  // 插入数据失败
            }  else {
                $status = gp_sms_send_code( $phone, $code );
                if ( $status != 0 ) {
                    ajax_die(4, '短信发送失败,错误代码:' .$status, ''); // 短信发送失败
                } else {
                    ajax_die(0, '成功发送短信验证码', ''); // 成功
                }
            }
        } else {
            ajax_die( 5, '10分钟内只能发送3次短信哦', '' ); // 三分钟内多次请求算一次, 短信延时
        }
    }
}
add_action( 'gp_actions', 'sms_request_code' );