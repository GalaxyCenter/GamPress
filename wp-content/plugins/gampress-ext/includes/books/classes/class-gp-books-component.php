<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/3
 * Time: 9:37
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class GP_Books_Component extends GP_Component {

    public function __construct() {
        parent::start(
            'books',
            __('Books', 'gampress'),
            GP_EXT_INCLUDES_DIR,
            array(
                'adminbar_myaccount_order' => 20
            )
        );
    }

    public function includes($includes = array()) {
        $includes = array(
            'screens',
            'functions',
            'actions',
            'ajaxs',
            'filters',
            'template'
        );

        if (!gampress()->do_autoload) {
            $includes[] = 'classes';
        }

        if (is_admin()) {
            $includes[] = 'admin';
        }

        parent::includes($includes);
    }

    public function setup_globals($args = array()) {
        $gp = gampress();

        // status
        if ( ! defined( 'GP_BOOK_SERIATING' ) ) {
            define( 'GP_BOOK_SERIATING', 0x0001 );
        }
        if ( ! defined( 'GP_BOOK_FINISH' ) ) {
            define( 'GP_BOOK_FINISH', 0x0002 );
        }
        if ( ! defined( 'GP_BOOK_HIDE' ) ) {
            define( 'GP_BOOK_HIDE', 0x0004 );
        }
        if ( ! defined( 'GP_BOOK_DISABLED' ) ) {
            define( 'GP_BOOK_DISABLED', 0x0008 );
        }

        // type
        if ( ! defined( 'GP_BOOK_CHARGE_TYPE_FREE' ) ) {
            // 免费
            define( 'GP_BOOK_CHARGE_TYPE_FREE', 0x0001 );
        }

        if ( ! defined( 'GP_BOOK_CHARGE_TYPE_VOLUME' ) ) {
            // 卷收费
            define( 'GP_BOOK_CHARGE_TYPE_VOLUME', 0x0002 );
        }

        if ( ! defined( 'GP_BOOK_CHARGE_TYPE_CHAPTER' ) ) {
            // 章节收费
            define( 'GP_BOOK_CHARGE_TYPE_CHAPTER', 0x0004 );
        }

        if ( ! defined( 'GP_BOOK_CHARGE_TYPE_CHAPTER_WORDS' ) ) {
            // 按1000字收费
            define( 'GP_BOOK_CHARGE_TYPE_CHAPTER_WORDS', 0x0008 );
        }

        if ( ! defined( 'GP_CHAPTER_FREE' ) ) {
            // 免费章节
            define( 'GP_CHAPTER_FREE', 0 );
        }

        if ( !defined( 'GP_CHAPTER_VOLUME' ) ) {
            define( 'GP_CHAPTER_VOLUME', 1 );
        }

        if ( ! defined( 'GP_CHAPTER_CHARGE' ) ) {
            // 收费章节
            define( 'GP_CHAPTER_CHARGE', 1 );
        }

        if ( ! defined( 'GP_BOOK_BASE_INDEX' ) ) {
            // 作品id基数
            define( 'GP_BOOK_BASE_INDEX', 10000 );
        }

        if ( ! defined( 'GP_CHAPTER_BASE_INDEX' ) ) {
            // 作品id基数
            define( 'GP_CHAPTER_BASE_INDEX', 1000000 );
        }

        // 章节状态
        if ( ! defined( 'GP_CHAPTER_ALL' ) ) {
            // 所有状态的章节
            define( 'GP_CHAPTER_ALL', -1 );
        }

        if ( ! defined( 'GP_CHAPTER_NORMAL' ) ) {
            // 正常状态
            define( 'GP_CHAPTER_NORMAL', 0 );
        }

        if ( ! defined( 'GP_CHAPTER_UNAPPROVED' ) ) {
            // 未审核
            define( 'GP_CHAPTER_UNAPPROVED', 1 );
        }

        add_theme_support( 'post-thumbnails' );

        $global_tables = array(
            'table_name_book'           => $gp->table_prefix . 'gp_fictions_books',
            'table_name_bookmeta'       => $gp->table_prefix . 'gp_fictions_bookmeta',
            'table_name_book_sign'      => $gp->table_prefix . 'gp_fictions_book_signs',
            'table_name_book_status'    => $gp->table_prefix . 'gp_fictions_book_status',
            'table_name_book_free'      => $gp->table_prefix . 'gp_fictions_book_free',
            'table_name_book_chapter'   => $gp->table_prefix . 'gp_fictions_book_chapters',
            'table_name_bookmarks'      => $gp->table_prefix . 'gp_fictions_bookmarks',
            'table_name_logs'           => $gp->table_prefix . 'gp_fictions_book_logs',
        );

        $default_directory_titles = gp_core_get_directory_page_default_titles();
        $default_directory_title  = $default_directory_titles[$this->id];

        $args = array(
            'global_tables'         => $global_tables,
            'directory_title'       => isset( $gp->pages->books->title ) ? $gp->pages->books->title : $default_directory_title,
        );

        // Main capability.
        $this->capability = gp_core_do_network_admin() ? 'manage_network_options' : 'manage_options';

        $this->chapters_slug           = apply_filters( 'gp_' . $this->id . '_chapters_slug',       'chapters' );
        $this->chapters_root_slug      = apply_filters( 'gp_' . $this->id . '_chapters_root_slug',  'chapters' );

        parent::setup_globals($args);

        if ( gp_is_books_component() && $book_id = GP_Books_Book::book_exists( gp_current_action() ) ) {
            $gp->is_single_item = true;

            $book = gp_books_get_book($book_id);
            if ( ( $book->status & GP_BOOK_DISABLED) != GP_BOOK_DISABLED ) {
                $this->current_item = $this->current_book = $book;
                $gp->current_item = gp_current_action();
                $gp->current_action = gp_action_variable(0);
                array_shift($gp->action_variables);
            } else {
                $this->current_book = 0;
            }
        } else {
            $this->current_book = 0;
        }
    }

    public function register_post_types() {
        $this->book_post_type = 'book_recommend';
        $this->book_post_taxonomy = 'book_recommend_category';

        register_post_type(
            gp_get_book_recommend_post_type(),
            array(
                'labels'              => gp_get_book_recommend_post_type_labels(),
                'rewrite'             => gp_get_book_recommend_post_type_rewrite(),
                'supports'            => gp_get_book_recommend_post_type_supports(),
                'description'         => __( 'Book Recommend', 'gampress-ext' ),
                'menu_position'       => 555555,
                'has_archive'         => true,
                'exclude_from_search' => true,
                'show_in_nav_menus'   => false,
                'public'              => true,
                'show_ui'             => true,
                'can_export'          => true,
                'hierarchical'        => false,
                'query_var'           => true,
                'menu_icon'           => 'xxx',
                'register_meta_box_cb' => 'gp_book_recommand_add_post_type_metabox'
            )
        );

        register_taxonomy( gp_get_book_recommend_post_taxonomy(), gp_get_book_recommend_post_type(), array(
            'hierarchical' => true,
            'labels' => array(
                'name'                  => _x( 'Book Recommends Categories', 'taxonomy general name' ),
                'singular_name'         => _x( 'Book Recommends Category', 'taxonomy singular name' ),
                'search_items'          =>  __( 'Search Book Recommends Categories' ),
                'all_items'             => __( 'All Book Recommends Categories' ),
                'parent_item'           => __( 'Parent Book Recommends Category' ),
                'parent_item_colon'     => __( 'Parent Book Recommends Category:' ),
                'edit_item'             => __( 'Edit Book Recommends Category' ),
                'update_item'           => __( 'Update Book Recommends Category' ),
                'add_new_item'          => __( 'Add New Book Recommends Category' ),
                'new_item_name'         => __( 'New Book Recommends Category Name' ),
                'menu_name'             => __( 'Book Recommends Categories' ),
            ),
            'rewrite' => false,
        ));

        if (is_admin()) {
            add_action('manage_book_recommend_posts_custom_column', 'gp_books_manage_book_recommend_custom_column');
            add_filter('manage_book_recommend_posts_columns', 'gp_books_manage_book_recommend_posts_columns');

            add_action('restrict_manage_posts', 'gp_book_recommend_restrict_manage_posts', 10, 2);
            add_filter('parse_query', 'gp_book_recommend_convert_id_to_term_in_query');

            add_action( 'save_post', 'book_recommend_post_save_meta', 1, 2 );
        }

    }

    public function setup_title() {
        if ( gp_is_books_component() ) {
            $gp = gampress();

            $gp->gp_options_title = _x( 'Books', 'Page title', 'gampress-ext' );
        }

        parent::setup_title();
    }
}