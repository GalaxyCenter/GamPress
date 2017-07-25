<?php
/**
 * GamPress Sns Template Functions.
 *
 * ⊙▂⊙
 * 
 * @package GamPress
 * @subpackage Sns
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function gp_is_sns_component() {
    return (bool) gp_is_current_component( 'sns' );
}

function gp_sns_slug() {
    echo gp_get_sns_slug();
}

    function gp_get_sns_slug() {
        return apply_filters( 'gp_get_sns_slug', gampress()->sns->slug );
	}

function gp_sns_wechat_subscription_token_link() {
    echo gp_get_sns_wechat_subscription_token_link();
}

    function gp_get_sns_wechat_subscription_token_link() {
        return sprintf( '%s/%s/sub_token_callback', gp_get_root_domain(), gp_get_sns_slug() );
    }

function gp_sns_weibo_redirect_uri() {
    echo gp_get_sns_weibo_redirect_uri();
}
    function gp_get_sns_weibo_redirect_uri() {
        //http://dance.yego.tech/sns/oauth_callback/weibo?callback=/members
        return sprintf( '%s/%s/oauth_callback/weibo', gp_get_root_domain(), gp_get_sns_slug() );
    }

function gp_sns_wechat_redirect_uri() {
    echo gp_get_sns_wechat_redirect_uri();
}
    function gp_get_sns_wechat_redirect_uri() {
        //http://dance.yego.tech/sns/oauth_callback/wechat?callback=/members
        return sprintf( '%s/%s/oauth_callback/wechat?callback/members', gp_get_root_domain(), gp_get_sns_slug() );
    }

function gp_sns_user_avatar ( $user_id ) {
    echo gp_get_sns_user_avatar( $user_id );
}

    function gp_get_sns_user_avatar ( $user_id ) {
        $url = get_user_meta( $user_id, 'sns_user_avatar', true );
        if ( empty( $url ) ) {
            $url = get_template_directory_uri() . '/dist/images/vest.png';
        }
        return $url;
    }

function gp_sns_wechat_app_id() {
    echo gp_get_sns_wechat_app_id();
}
    function gp_get_sns_wechat_app_id() {
        return gp_get_option( 'gp_sns_wechat_app_id' );
    }
    
function gp_sns_wechat_app_secret() {
    echo gp_get_sns_wechat_app_secret();
}
    function gp_get_sns_wechat_app_secret() {
        return gp_get_option( 'gp_sns_wechat_app_secret' );
    }

function gp_sns_wechat_sub_app_id() {
    echo gp_get_sns_wechat_sub_app_id();
}
    function gp_get_sns_wechat_sub_app_id() {
        return gp_get_option( 'gp_sns_wechat_sub_app_id' );
    }

function gp_sns_wechat_sub_app_secret() {
    echo gp_get_sns_wechat_sub_app_secret();
}
    function gp_get_sns_wechat_sub_app_secret() {
        return gp_get_option( 'gp_sns_wechat_sub_app_secret' );
    }

function gp_sns_wechat_sub_app_token() {
    echo gp_get_sns_wechat_sub_app_token();
}
    function gp_get_sns_wechat_sub_app_token() {
        return gp_get_option( 'gp_sns_wechat_sub_app_token' );
    }

function gp_sns_weibo_app_id() {
    echo gp_get_sns_weibo_app_id();
}
    function gp_get_sns_weibo_app_id() {
        return gp_get_option( 'gp_sns_weibo_app_id' );
    }

function gp_sns_weibo_app_secret() {
    echo gp_get_sns_weibo_app_secret();
}
    function gp_get_sns_weibo_app_secret() {
        return gp_get_option( 'gp_sns_weibo_app_secret' );
    }

function gp_sns_qq_app_id() {
    echo gp_get_sns_qq_app_id();
}
    function gp_get_sns_qq_app_id() {
        return gp_get_option( 'gp_sns_qq_app_id' );
    }

function gp_sns_qq_app_secret() {
    echo gp_get_sns_qq_app_secret();
}
    function gp_get_sns_qq_app_secret() {
        return gp_get_option( 'gp_sns_qq_app_secret' );
    }

function gp_sns_qq_redirect_uri() {
    echo gp_get_sns_qq_redirect_uri();
}
    function gp_get_sns_qq_redirect_uri() {
        return sprintf( '%s/%s/oauth_callback/qq', gp_get_root_domain(), gp_get_sns_slug() );
    }