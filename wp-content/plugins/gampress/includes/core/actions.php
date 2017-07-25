<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * 注册初始化相关的action
 * 
 */
add_action( 'plugins_loaded',           'gp_loaded',                 10    );
add_action( 'init',                     'gp_init',                   10    ); // Early for gp_register
add_action( 'set_current_user',         'gp_setup_current_user',     10    );
add_action( 'setup_theme',              'gp_setup_theme',            10    );
add_action( 'after_setup_theme',        'gp_after_setup_theme',      100   ); // After WP themes.
add_action( 'template_redirect',        'gp_template_redirect',      10    );

/**
 * 在插件加载后 plugins_loaded 之后执行的action
 * 
 */
add_action( 'gp_loaded', 'gp_setup_components',         2  );
add_action( 'gp_loaded', 'gp_include',                  4  );
add_action( 'gp_loaded', 'gp_register_theme_packages',  12 );
add_action( 'gp_loaded', 'gp_register_theme_directory', 14 );

/**
 * 在插件初始化 init 之后执行的action
 * 
 */
add_action( 'gp_init',    'gp_register_post_types',    2  );
add_action( 'gp_init',    'gp_register_taxonomies',    2  );
add_action( 'gp_init',    'gp_core_set_uri_globals',   2  );
add_action( 'gp_init',    'gp_setup_displayed_user',   4  );
add_action( 'gp_init',    'gp_setup_globals',          4  );
add_action( 'gp_init',    'gp_setup_nav',              6  );
add_action( 'gp_init',    'gp_setup_title',             8  );

add_action( 'gp_template_redirect', 'gp_actions', 4 );
add_action( 'gp_template_redirect', 'gp_screens', 6 );

add_action( 'gp_after_setup_theme', 'gp_load_theme_functions',                    1 );
add_action( 'gp_after_setup_theme', 'gp_register_theme_compat_default_features', 10 );

if ( is_admin() ) {
    add_action( 'gp_loaded', 'gp_admin' );
}