<?php
/**
 * Plugin Name: GamPress-Ext
 * Plugin URI: http://weibo.com/texel
 * Description: GamPress 扩展插件
 * Version: 1.0.0
 * Author: Bourne Jiang
 * Author URI: http://weibo.com/texel
 */

if ( !defined( 'ABSPATH' ) ) exit;

function gp_ext_loaded() {
    define( 'GP_EXT_PLUGIN_DIR', trailingslashit( WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) )  );
    define( 'GP_EXT_INCLUDES_DIR', constant( 'GP_EXT_PLUGIN_DIR' ). 'includes' );

    define( 'GP_EXT_PLUGIN_URL', trailingslashit( plugins_url( basename( dirname( __FILE__ ) ) ) ) );


    $themes_dir = trailingslashit( GP_EXT_PLUGIN_DIR . 'themes' );
    register_theme_directory( $themes_dir );

    gp_register_template_stack( 'gp_ext_get_theme_compat_dir',  14 );

    load_plugin_textdomain( 'gampress-ext', false, 'gampress-ext/languages' );
}
add_action( 'plugins_loaded', 'gp_ext_loaded', 1 );

function gp_ext_get_theme_compat_dir() {
    $template_dir = trailingslashit( GP_EXT_PLUGIN_DIR . 'templates/legacy' );
    return apply_filters( 'gp_get_theme_compat_dir', $template_dir );
}

function gp_ext_core_install_videos() {
    register_taxonomy('cinema', 'cinema', array(
                // Hierarchical taxonomy (like categories)
                'hierarchical' => true,
                // This array of options controls the labels displayed in the WordPress Admin UI
                'labels' => array(
                    'name' => _x( 'Cinema Categories', 'taxonomy general name' ),
                    'singular_name' => _x( 'Cinema Category', 'taxonomy singular name' ),
                    'search_items' =>  __( 'Search Cinema Categories' ),
                    'all_items' => __( 'All Cinema Categories' ),
                    'parent_item' => __( 'Parent Cinema Category' ),
                    'parent_item_colon' => __( 'Parent Cinema Category:' ),
                    'edit_item' => __( 'Edit Cinema Category' ),
                    'update_item' => __( 'Update Cinema Category' ),
                    'add_new_item' => __( 'Add New Cinema Category' ),
                    'new_item_name' => __( 'New Cinema Category Name' ),
                    'menu_name' => __( 'Cinema Categories' ),
                    ),
                'rewrite' => false,
                ));
                
    $names = array(  '中学' ,'中老' ,'会员' ,'其他' ,'古典' ,'名家'
            ,'国标' ,'少儿' ,'幼儿' ,'广场' ,'当代' ,'歌舞'
            ,'民族' ,'汇报' ,'爵士' ,'现代' ,'群文' ,'舞蹈'
            ,'芭蕾' ,'街舞', '踢踏' );
    foreach ( $names as $name ) {
        if ( !term_exists( $name, 'cinema', 0 ) ) {
            wp_insert_term(
                    $name, 
                    'cinema',
                    array(
                        'description'=> $name,
                        'slug' => cn2py( $name ),
                        'parent'=> 0
                        )
                    );
        }
    }
}

function gp_ext_core_install_combines() {
    register_taxonomy( 'combine', 'combine', array( 'public' => false, 'hierarchical' => true ) );
    
    $names = array(
                    '电影'  => array( '动作', '科幻', '战争', '喜剧', '恐怖', '灾难', '魔幻', '武侠', '爱情', '文艺', '记录', '剧情', '传记', '动画', '惊悚', '预告片' ),
                    '音乐'  => array( '华语音乐', '欧美音乐', '日韩音乐', 'MV', '演唱会', '原声音乐', '古典音乐', '新世纪音乐', '其它音乐' ),
                    '游戏'  => array( '光盘版游戏', '硬盘版游戏', '电视游戏', '掌机游戏', '网络游戏', '游戏周边' ), 
                    '动漫'  => array( '电视动画', '剧场动画', 'OVA', '漫画', '原创动漫', '动漫周边' ), 
                    '图书'  => array( '小说', '文学', '人文社科', '经济管理', '计算机与网络', '生活', '教育科技', '少儿', '其它图书' ), 
                    '综艺'  => array( '综艺娱乐', '艺人合集', '体育节目', '新闻综合', '晚会典礼', '科教节目', '纪录片' ), 
                    '软件'  => array( '操作系统', '应用软件', '网络软件', '系统工具', '多媒体类', '行业软件', '编程开发', '安全相关' ), 
                    '资料'  => array( '素材', '杂志期刊', '有声读物', '其它资料' ), 
                    '教育'  => array( '人文社科', '理工科', '艺术体育', '医学', '商学', '计算机', '外语', '其它' ));
                    
    foreach ( $names as $key => $sub_names ) {
        if ( !$term = term_exists( $key, 'combine', 0 ) ) {
            $term = wp_insert_term( $key,  'combine', array( 'description'=> $key, 'slug' => cn2py( $key ), 'parent'=> 0 ) );
        }
        
        foreach ( $sub_names as $sub_name ) {
            if ( !term_exists( $sub_name, 'combine', $term['term_id'] ) ) {
                wp_insert_term( $sub_name,  'combine', array( 'description'=> $sub_name, 'slug' => cn2py( $sub_name ), 'parent'=> $term['term_id'] ) );
            }
        }
    }
}

function gp_ext_core_install_services() {}

function gp_ext_core_install_missions() {}

function gp_ext_core_install_users() {}

function gp_ext_core_install_books() {
    register_taxonomy( 'book_library', 'book_library', array( 'public' => false, 'hierarchical' => true ) );

    $names = array(
        '女频' => array( '现代言情', '古代言情', '幻想言情', '女生悬疑', '浪漫青春' ),
        '男频' => array( '现代都市', '灵异悬疑', '官场职场', '奇幻玄幻', '历史军事', '武侠仙侠', '科幻小说' ),
        '图书' => array( '社科科普', '经管理财', '都市言情', '纪实传记', '官场商战', '古典名著', '历史军事', '青春校园', '悬疑推理', '教育亲子', '养生保健', '影视娱乐', '玄幻武侠' )
    );

    foreach ( $names as $key => $sub_names ) {
        if ( !$term = term_exists( $key, 'book_library', 0 ) ) {
            $term = wp_insert_term( $key,  'book_library', array( 'description'=> $key, 'slug' => cn2py( $key ), 'parent'=> 0 ) );
        }

        foreach ( $sub_names as $sub_name ) {
            if ( !term_exists( $sub_name, 'book_library', $term['term_id'] ) ) {
                wp_insert_term( $sub_name,  'book_library', array( 'description'=> $sub_name, 'slug' => cn2py( $sub_name ), 'parent'=> $term['term_id'] ) );
            }
        }
    }
}

function gp_ext_core_install_orders() {

}

function gp_ext_core_loaded() {
    add_filter( 'gp_optional_components', 'gp_ext_optional_components' );

//    $dirs = @ scandir( GP_EXT_INCLUDES_DIR );
//    foreach ( $dirs as $dir ) {
//        if ( ! is_dir( GP_EXT_INCLUDES_DIR . '/' . $dir ) || $dir[0] == '.' || $dir == 'CVS' )
//            continue;
//        if ( file_exists( GP_EXT_INCLUDES_DIR . '/' . $dir . '/loader.php' ) ) {
//            require( GP_EXT_INCLUDES_DIR . '/' . $dir . '/loader.php' );
//        }
//    }

    add_action( 'gp_core_install_videos',     'gp_ext_core_install_videos' );
    add_action( 'gp_core_install_combines',   'gp_ext_core_install_combines' );
    add_action( 'gp_core_install_services',   'gp_ext_core_install_services' );
    add_action( 'gp_core_install_missions',   'gp_ext_core_install_missions' );
    add_action( 'gp_core_install_users',      'gp_ext_core_install_users' );
    add_action( 'gp_core_install_books',      'gp_ext_core_install_books' );
    add_action( 'gp_core_install_orders',     'gp_ext_core_install_orders' );
}
add_action( 'gp_core_loaded', 'gp_ext_core_loaded' );

function gp_ext_core_get_components( $components, $type ) {
    if ( $type == 'optional' ) {
        $components['videos'] = array(
                'title'       => __( 'Videos', 'gampress-ext' ),
                'description' => __( 'Videos', 'gampress-ext' )
                );
        
        $components['combines'] = array(
                'title'       => __( 'Combines', 'gampress-ext' ),
                'description' => __( 'Combines', 'gampress-ext' )
                );

        $components['services'] = array(
            'title'       => __( 'Person Services', 'gampress-ext' ),
            'description' => __( 'Person Services', 'gampress-ext' )
        );

        $components['missions'] = array(
            'title'       => __( 'Missions', 'gampress-ext' ),
            'description' => __( 'Missions', 'gampress-ext' )
        );

        $components['users'] = array(
            'title'       => __( 'Users', 'gampress-ext' ),
            'description' => __( 'Users', 'gampress-ext' )
        );

        $components['books'] = array(
            'title'       => __( 'Books', 'gampress-ext' ),
            'description' => __( 'Books', 'gampress-ext' )
        );

        $components['orders'] = array(
            'title'       => __( 'Orders', 'gampress-ext' ),
            'description' => __( 'Orders', 'gampress-ext' )
        );
    }
    return $components;
}
add_filter( 'gp_core_get_components', 'gp_ext_core_get_components', 10, 2 );

function gp_ext_get_directory_page_default_titles( $pages ) {
    $pages['videos']       = _x( 'Videos',           'Page title for the Videos directory.',           'gampress-ext' );
    $pages['combines']     = _x( 'Combines',         'Page title for the Combines directory.',         'gampress-ext' );
    $pages['services']     = _x( 'Person Services',  'Page title for the Person Services directory.',  'gampress-ext' );
    $pages['users']        = _x( 'Users',            'Page title for the Users directory.',            'gampress-ext' );
    $pages['missions']     = _x( 'Missions',         'Page title for the Missions directory.',         'gampress-ext' );
    $pages['books']        = _x( 'Books',            'Page title for the Books directory.',            'gampress-ext' );
    $pages['orders']       = _x( 'Orders',           'Page title for the Orders directory.',           'gampress-ext' );
    return $pages;
}
add_filter( 'gp_core_get_directory_page_default_titles', 'gp_ext_get_directory_page_default_titles' );

function gp_ext_optional_components( $components ) {
    $components[] = 'videos';
    $components[] = 'combines';
    $components[] = 'services';
    $components[] = 'missions';
    $components[] = 'users';
    $components[] = 'books';
    $components[] = 'orders';

    return $components;
}

function gp_ext_autoload_components( $components ) {
    $components[] = 'videos';
    $components[] = 'combines';
    $components[] = 'services';
    $components[] = 'missions';
    $components[] = 'users';
    $components[] = 'books';
    $components[] = 'orders';

    return $components;
}
add_filter( 'gp_autoload_components', 'gp_ext_autoload_components' );

function gp_ext_includes_dir() {
    return constant( 'GP_EXT_INCLUDES_DIR' );
}
add_filter( 'gp_videos_includes_dir',       'gp_ext_includes_dir' );
add_filter( 'gp_combines_includes_dir',     'gp_ext_includes_dir' );
add_filter( 'gp_services_includes_dir',     'gp_ext_includes_dir' );
add_filter( 'gp_missions_includes_dir',     'gp_ext_includes_dir' );
add_filter( 'gp_users_includes_dir',        'gp_ext_includes_dir' );
add_filter( 'gp_books_includes_dir',        'gp_ext_includes_dir' );
add_filter( 'gp_orders_includes_dir',       'gp_ext_includes_dir' );