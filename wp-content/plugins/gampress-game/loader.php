<?php
/**
 * Plugin Name: GamPress-Game
 * Plugin URI: http://weibo.com/texel
 * Description: GamPress 游戏插件
 * Version: 1.0.0
 * Author: Bourne Jiang
 * Author URI: http://weibo.com/texel
 */

if ( !defined( 'ABSPATH' ) ) exit;

function gp_game_loaded() {
    define( 'GP_GAME_PLUGIN_DIR', trailingslashit( WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) )  );
    define( 'GP_GAME_INCLUDES_DIR', constant( 'GP_GAME_PLUGIN_DIR' ). 'includes' );

    define( 'GP_GAME_PLUGIN_URL', trailingslashit( plugins_url( basename( dirname( __FILE__ ) ) ) ) );

    /** 弹出资料框 */
    define( 'GP_GAME_LOTTERY_POP',       0x0001 );
    /** 单次中奖 */
    define( 'GP_GAME_LOTTERY_SINGLE',    0x0002 );
    /** 显示中奖项目 */
    define( 'GP_GAME_LOTTERY_SHOW',      0x0004 );

    /** 普通奖品 */
    define( 'GP_GAME_LOTTERY_NORMAL',    0x0010 );
    /** POINT类奖品 */
    define( 'GP_GAME_LOTTERY_POINT',     0x0020 );
    /** 券类奖品 */
    define( 'GP_GAME_LOTTERY_TICKET',    0x0040 );
    /** 需要收集信息 */
    define( 'GP_GAME_LOTTERY_CONTACT',    0x0080 );

    /** 收集姓名 */
    define( 'GP_GAME_LOTTERY_NAME',       0x0100 );
    /** 收集手机 */
    define( 'GP_GAME_LOTTERY_PHONE',      0x0200 );
    /** 收集地址*/
    define( 'GP_GAME_LOTTERY_ADDRESS',    0x0400 );
    /** 收集QQ*/
    define( 'GP_GAME_LOTTERY_QQ',         0x0800 );

    $themes_dir = trailingslashit( GP_GAME_PLUGIN_DIR . 'themes' );
    register_theme_directory( $themes_dir );

    gp_register_template_stack( 'gp_game_get_theme_compat_dir',  14 );

    load_plugin_textdomain( 'gampress-game', false, 'gampress-game/languages' );
}
add_action( 'plugins_loaded', 'gp_game_loaded', 1 );

function gp_game_get_theme_compat_dir() {
    $template_dir = trailingslashit( GP_GAME_PLUGIN_DIR . 'templates/legacy' );
    return apply_filters( 'gp_get_theme_compat_dir', $template_dir );
}

function gp_game_core_install_games() {

}

function gp_game_core_loaded() {
    add_filter( 'gp_optional_components', 'gp_game_optional_components' );
    
    add_action( 'gp_core_install_games',     'gp_game_core_install_games' );
}
add_action( 'gp_core_loaded', 'gp_game_core_loaded' );

function gp_game_core_get_components( $components, $type ) {
    if ( $type == 'optional' ) {

        $components['games'] = array(
            'title'       => __( 'Games', 'gampress-game' ),
            'description' => __( 'Games', 'gampress-game' )
        );
    }
    return $components;
}
add_filter( 'gp_core_get_components', 'gp_game_core_get_components', 10, 2 );

function gp_game_get_directory_page_default_titles( $pages ) {
    $pages['games']       = _x( 'Games',           'Page title for the Games directory.',           'gampress-game' );

    return $pages;
}
add_filter( 'gp_core_get_directory_page_default_titles', 'gp_game_get_directory_page_default_titles' );

function gp_game_optional_components( $components ) {
    $components[] = 'games';

    return $components;
}

function gp_game_autoload_components( $components ) {
    $components[] = 'games';

    return $components;
}
add_filter( 'gp_autoload_components', 'gp_game_autoload_components' );

function gp_game_includes_dir() {
    return constant( 'GP_GAME_INCLUDES_DIR' );
}
add_filter( 'gp_games_includes_dir',        'gp_game_includes_dir' );