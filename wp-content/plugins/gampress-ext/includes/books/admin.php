<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/11
 * Time: 20:27
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load the GP Books admin.
add_action( 'gp_init', array( 'GP_Books_Admin', 'register_books_admin' ) );

if ( !class_exists( 'WP_List_Table' ) ) require( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

function gp_books_add_admin_menu() {

    // Add our screen.
    $hook = add_menu_page(
        _x( 'Books', 'Admin Books page title', 'gampress-ext' ),
        _x( 'Books', 'Admin Books menu', 'gampress-ext' ),
        'gp_moderate',
        'gp-books',
        'gp_books_admin',
        'div'
    );

    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_books_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_books_add_admin_menu' );

function gp_books_admin_load() {
    global $gp_books_list_table;

    // Decide whether to load the dev version of the CSS and JavaScript
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

    $doaction = gp_admin_list_table_current_bulk_action();

    gp_core_setup_message();
    // Edit screen
    if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ) {
        require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );

        add_meta_box( 'submitdiv', _x( 'Status', 'book admin edit screen', 'gampress-ext' ), 'gp_books_admin_edit_metabox_status', get_current_screen()->id, 'side', 'core' );

        add_meta_box('categorydiv', __('Categories'), 'post_categories_meta_box', get_current_screen()->id, 'side', 'low', array( 'taxonomy' => 'book_library' ));

    } else if ( 'save' == $doaction ) {
        $redirect_to = remove_query_arg( array( 'action', 'id', 'deleted', 'error', 'spammed', 'unspammed', ), wp_get_referer() );

        $id = (int) $_REQUEST['id'];
        check_admin_referer( 'edit-book_' . $id );

        $form_names = array(
            'book_title'            => array( 'required' => true, 'error' => __( 'The book title can\'t be empty', 'gampress-ext' ) ),
            'book_author'           => array( 'required' => true, 'error' => __( 'The book author can\'t be empty', 'gampress-ext' ) ),
            'book_description'      => array( 'required' => true, 'error' => __( 'The book description can\'t be empty', 'gampress-ext' ) ),
            'book_summary'          => array( 'required' => false, 'error' => false ),
            'book_tags'             => array( 'required' => true, 'error' => __( 'The book tags can\'t be empty', 'gampress-ext' ) ),
            'book_bookmarks'        => array( 'required' => false, 'error' => false ),
            'book_author_id'        => array( 'required' => false, 'error' => false ),
            'book_refer'            => array( 'required' => false, 'error' => false ),
            'book_chapter_type'     => array( 'required' => false, 'error' => false ),
            'book_charge_type'      => array( 'required' => false, 'error' => false ),
            'book_charge_type2'     => array( 'required' => false, 'error' => false ),
            'book_point'            => array( 'required' => false, 'error' => false ),
            'book_charge_order'     => array( 'required' => false, 'error' => false ),
            'book_cover'            => array( 'required' => false, 'error' => false ),
            'book_status'           => array( 'required' => false, 'error' => false )
        );
        $form_values = get_request_values( $form_names );
        if ( !empty( $form_values['error'] ) ) {
            gp_core_add_message( sprintf( $form_values['error'] ), 'error' );

            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'error', 'true', $redirect_to );
        } else {
            // FILE
            if ( !empty( $_FILES['book_cover']['tmp_name'] ) ) {
                $dir = wp_upload_dir();
                $cover = md5( $form_values['values']['book_title'] ) . '.jpg';
                $raw_cover = $dir['basedir'] . '/ebooks/' . $cover;
                $l_cover = $dir['basedir'] . '/ebooks/l/' . $cover;
                $m_cover = $dir['basedir'] . '/ebooks/m/' . $cover;
                $s_cover = $dir['basedir'] . '/ebooks/s/' . $cover;

                $image = wp_get_image_editor( $_FILES["book_cover"]['tmp_name'] );
                $image->set_quality( 99 );
                $image->save($raw_cover);

                $image->resize( 360, 480, false );
                $image->save($l_cover);

                $image->resize( 140, 180, false );
                $image->save($m_cover);

                $image->resize( 80, 100, false );
                $image->save($s_cover);

                $form_values['values']['book_cover'] = $cover;
            }
            $books_status = 0;
            foreach ($form_values['values']['book_status'] as $stauts) {
                $books_status |= $stauts;
            }

            gp_books_update_book( array( 'id' => $id ,
                'title'             => $form_values['values']['book_title'],
                'author'            => $form_values['values']['book_author'],
                'description'       => $form_values['values']['book_description'],
                'summary'           => $form_values['values']['book_summary'],
                'chapter_type'      => $form_values['values']['book_chapter_type'],
                'tags'              => $form_values['values']['book_tags'],
                'refer'             => $form_values['values']['book_refer'],
                'author_id'         => (int) $form_values['values']['book_author_id'],
                'charge_type'       => (int) $form_values['values']['book_charge_type'] | (int) $form_values['values']['book_charge_type2'],
                'charge_order'      => (int) $form_values['values']['book_charge_order'],
                'point'             => $form_values['values']['book_point'],
                'cover'             => $form_values['values']['book_cover'],
                'bookmarks'         => $form_values['values']['book_bookmarks'],
                'status'            => $books_status ) );

            // update charge order
            if ( !empty( $form_values['values']['book_charge_order'] ) )
                gp_books_admin_update_charge_order( $id, $form_values['values']['book_charge_order'] );

            $terms = gp_get_object_terms( $id, 'book_library' );
            foreach( $terms as $term ) {
                gp_remove_object_terms( $id, $term->slug, 'book_library' );
            }

            $cats = $_POST['tax_input']['book_library'];
            foreach( $cats as $cat ) {
                if ( empty( $cat ) ) continue;

                $cat = gp_get_term_by( 'id', $cat, 'book_library' );
                wp_set_object_terms( $id, $cat->slug, 'book_library', true );
            }

            $cache_group = 'gp_ex_chapters_g_' . $id;
            wp_cache_clean( $cache_group );

            gp_core_add_message( __( 'Changes saved.', 'gampress-ext' ) );
            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'updated', $id, $redirect_to );
        }

        wp_redirect( $redirect_to );
        exit;
    } elseif ( 'import' == $doaction && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
        $id = (int) $_REQUEST['id'];
        $content = $_POST['content'];

        $id = gp_books_admin_import_book( $content, $id );
        if ( !is_wp_error( $id ) ) {
            gp_core_add_message( __( 'Import success.', 'gampress-ext' ) );

            $redirect_to = remove_query_arg( array( 'action', 'id', 'deleted', 'error', 'spammed', 'unspammed', ), wp_get_referer() );

            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'updated', $id, $redirect_to );

            wp_redirect( $redirect_to );
        } else {
            //wp_redirect( $redirect_to );
        }
        exit;

    } else { // Index screen.
        $gp_books_list_table = new GP_Books_List_Table();
    }

    if ( !empty( $doaction ) && ! in_array( $doaction, array( '-1', 'edit', 'save', 'import' ) ) ) {
        // Build redirection URL
        $redirect_to = remove_query_arg( array( 'id', 'deleted', 'error', 'comfirmed', ), wp_get_referer() );
        $redirect_to = add_query_arg( 'paged', $gp_books_list_table->get_pagenum(), $redirect_to );

        $ids = (array) $_REQUEST['id'];

        if ( 'bulk_' == substr( $doaction, 0, 5 ) && ! empty( $_REQUEST['id'] ) ) {
            // Check this is a valid form submission
            check_admin_referer( 'bulk-books' );

            // Trim 'bulk_' off the action name to avorder_id duplicating a ton of code
            $doaction = substr( $doaction, 5 );

        }
        $disabled = $seriating = $finish = 0;

        $errors = array();

        foreach ( $ids as $id ) {
            $book = gp_books_get_book( $id );

            if ( empty( $book ) ) {
                $errors[] = $id;
                continue;
            }

            switch( $doaction ) {
                case 'show':
                    gp_books_book_show( $id );
                    $disabled++;
                    break;


                case 'hide':
                    gp_books_book_hide( $id );
                    $disabled++;
                    break;

                case 'seriating':
                    gp_books_book_seriating( $id );
                    $seriating++;
                    break;

                case 'finish':
                    gp_books_book_finish( $id );
                    $finish++;
                    break;

                case 'recommend':
                    gp_books_book_recommend( $id );
                    break;

                case 'free':
                    gp_books_book_free( $id );
                    break;

                case 'unfree':
                    gp_books_book_unfree( $id );
                    break;

                default:
                    gp_books_book_recommend( $id, $doaction );
                    break;
            }

            unset( $book );
        }

        if ( $disabled )
            $redirect_to = add_query_arg( 'disabled', $disabled, $redirect_to );

        if ( ! empty( $errors ) )
            $redirect_to = add_query_arg( 'error', implode ( ',', array_map( 'absint', $errors ) ), $redirect_to );

        wp_redirect( $redirect_to );

    } elseif ( $doaction && 'save' == $doaction ) {

    } elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
        wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
        exit;
    }
}

function gp_books_admin() {
    // Decide whether to load the index or edit screen.
    $doaction = gp_admin_list_table_current_bulk_action();

    // Display the single group edit screen.
    if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ) {
        gp_books_admin_edit();

        // Display the group deletion confirmation screen.
    } elseif ( 'delete' == $doaction && ! empty( $_GET['id'] ) ) {
        gp_books_admin_delete();

        // Otherwise, display the books index screen.
    } elseif ( 'import' == $doaction ) {
        gp_books_admin_import();
    } else {
        gp_books_admin_index();
    }
}

function gp_books_admin_index() {
    global $gp_books_list_table, $plugin_page;

    $messages = array();

    // If the user has just made a change to a group, build status messages.
    if ( ! empty( $_REQUEST['deleted'] ) ) {
        $deleted  = ! empty( $_REQUEST['deleted'] ) ? (int) $_REQUEST['deleted'] : 0;

        if ( $deleted > 0 ) {
            $messages[] = sprintf( _n( '%s group has been permanently deleted.', '%s books have been permanently deleted.', $deleted, 'gampress-ext' ), number_format_i18n( $deleted ) );
        }
    }
    $messages[] = isset( $_COOKIE['gp-message'] ) ? $_COOKIE['gp-message'] : '';

    // Prepare the group items for display.
    $gp_books_list_table->prepare_items();

    $import_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $import_url = add_query_arg( 'action', 'import', $import_url );

    $all_chapters_url = remove_query_arg( array( 'page', ), $_SERVER['REQUEST_URI'] );
    $all_chapters_url = add_query_arg( 'page', 'gp-chapters', $all_chapters_url );
    $all_chapters_url = add_query_arg( 'status', '1', $all_chapters_url );
    ?>

    <div class="wrap">
        <h1>
            <?php _e( 'Books', 'gampress-ext' ); ?>

            <?php if ( is_user_logged_in() && gp_user_can_create_books() ) : ?>
                <a class="add-new-h2" href="<?php echo $import_url;?>"><?php _e( 'Add New', 'gampress-ext' ); ?></a>
            <?php endif; ?>

            <?php if ( !isset( $_GET['book_id'] ) ) :?>
                <a class="add-new-h2" href="<?php echo $all_chapters_url;?>"><?php _e( 'All Unapproved Chapters', 'gampress-ext' ); ?></a>
            <?php endif;?>

            <?php if ( !empty( $_REQUEST['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'gampress-ext' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
            <?php endif; ?>
        </h1>

        <hr class="wp-header-end" />

        <?php gp_core_render_message();?>

        <?php // Display each group on its own row. ?>
        <?php $gp_books_list_table->views(); ?>

        <form id="gp-books-form" action="" method="get">
            <?php $gp_books_list_table->search_box( __( 'Search all Books', 'gampress-ext' ), 'gp-books' ); ?>
            <input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
            <?php $gp_books_list_table->display(); ?>
        </form>

    </div>

    <?php
}

function gp_books_admin_import() {
    $id = ! empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;

    $book = gp_books_get_book( $id );
    $form_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $form_url = add_query_arg( 'action', 'import', $form_url );
    ?>
    <div class="wrap">
        <?php if ( empty( $id ) ) : ?>
        <h1><?php _e( 'Import Book', 'gampress-ext' ); ?></h1>
        <?php else :?>
            <h1><?php printf( __( 'Import Book:《%s》', 'gampress-ext' ), gp_get_book_title( $book ) ); ?></h1>
        <?php endif;?>
        <?php gp_core_render_message();?>

        <form action="<?php echo esc_url( $form_url ); ?>" id="gp-books-import-form" method="post">
            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                    <div id="post-body-content">
                        <div id="gp_book_description" class="postbox">
                            <h2><?php _e( 'Book Content', 'gampress-ext' ); ?></h2>
                            <div class="inside">
                                <textarea name="content" rows="30" cols="80" style="width:100%"></textarea>
                            </div>

                            <div id="publishing-action">
                                <?php submit_button( __( 'Import', 'gampress-ext' ), 'primary', 'save', false ); ?>
                            </div>
                        </div>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div id="gp_book_chapter_order" class="postbox">

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function gp_books_admin_edit() {
    if ( ! is_super_admin() )
        die( '-1' );

    $id = ! empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;

    $book = gp_books_get_book( $id );
    $form_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $form_url = add_query_arg( 'action', 'save', $form_url );
    ?>
    <div class="wrap">
        <h1><?php printf( __( 'Editing Book:《%s》', 'gampress-ext' ), gp_get_book_title( $book ) ); ?></h1>

        <?php gp_core_render_message();?>

        <?php if ( ! empty( $book ) ) : ?>

                <form action="<?php echo esc_url( $form_url ); ?>" id="gp-books-edit-form" method="post" enctype="multipart/form-data">
                <div id="poststuff">

                    <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                        <div id="post-body-content">
                            <div id="postdiv">
                                <div id="gp_book_title" class="postbox">
                                    <h2><?php _e( 'Title', 'gampress-ext' ); ?></h2>
                                    <div class="inside">
                                        <input id="book_title" type="text" autocomplete="off" spellcheck="true" value="<?php gp_book_title( $book );?>" size="30" name="book_title">
                                    </div>
                                </div>

                                <div id="gp_book_author" class="postbox">
                                    <h2><?php _e( 'Author', 'gampress-ext' ); ?></h2>
                                    <div class="inside">
                                        <input id="book_author" type="text" autocomplete="off" spellcheck="true" value="<?php gp_book_author( $book );?>" size="30" name="book_author">
                                    </div>
                                </div>

                                <div id="gp_book_description" class="postbox">
                                    <h2><?php _e( 'Description', 'gampress-ext' ); ?></h2>
                                    <div class="inside">
                                        <?php wp_editor( stripslashes( $book->description ), 'book_description', array( 'media_buttons' => false, 'textarea_rows' => 7, 'teeny' => true, 'quicktags' => array( 'buttons' => 'strong,em,link,block,del,ins,img,code,spell,close' ) ) ); ?>
                                    </div>
                                </div>

                                <div id="gp_book_summary" class="postbox">
                                    <h2><?php _e( 'Summary', 'gampress-ext' ); ?></h2>
                                    <div class="inside">
                                        <?php wp_editor( stripslashes( $book->summary ), 'book_summary', array( 'media_buttons' => false, 'textarea_rows' => 7, 'teeny' => true, 'quicktags' => array( 'buttons' => 'strong,em,link,block,del,ins,img,code,spell,close' ) ) ); ?>
                                    </div>
                                </div>


                            </div>
                        </div><!-- #post-body-content -->

                        <div id="postbox-container-1" class="postbox-container">
                            <div id="gp_book_bookmarks" class="postbox">
                                <h2><?php _e( 'Bookmarks', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="book_bookmarks" type="text" autocomplete="off" spellcheck="true" value="<?php echo $book->bookmarks;?>" size="20" name="book_bookmarks">
                                </div>
                            </div>

                            <div id="gp_book_tags" class="postbox">
                                <h2><?php _e( 'Tags', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="book_tags" type="text" autocomplete="off" spellcheck="true" value="<?php gp_book_tags( $book );?>" size="20" name="book_tags">
                                </div>
                            </div>

                            <div id="gp_book_author_id" class="postbox">
                                <h2><?php _e( 'AuthorId', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="book_author_id" type="text" autocomplete="off" spellcheck="true" value="<?php gp_book_author_id( $book );?>" size="20" name="book_author_id">
                                </div>
                            </div>

                            <div id="gp_book_refer" class="postbox">
                                <h2><?php _e( 'Refer', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="book_refer" type="text" autocomplete="off" spellcheck="true" value="<?php gp_chapter_refer( $book );?>" size="20" name="book_refer">
                                </div>
                            </div>


                            <div id="gp_book_chapter_type" class="postbox">
                                <h2><?php _e( 'Chapter Type', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <select id="book_chapter_type" name="book_chapter_type">
                                        <?php $book_chapter_types  = gp_get_books_chapter_type(); ?>
                                        <?php foreach ( $book_chapter_types as $k => $v ) : ?>
                                            <option value="<?php echo $k; ?>" <?php selected( $k,  $book->chapter_type ); ?>><?php echo esc_html( $v ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="gp_book_charge_type" class="postbox">
                                <h2><?php _e( 'Charge Type', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <select id="book_charge_type" name="book_charge_type">
                                        <?php $charge_types = gp_get_books_charge_type() ;?>
                                        <option value="<?php echo GP_BOOK_CHARGE_TYPE_FREE; ?>" <?php selected( GP_BOOK_CHARGE_TYPE_FREE,  $book->charge_type & GP_BOOK_CHARGE_TYPE_FREE ); ?>><?php echo esc_html( $charge_types[GP_BOOK_CHARGE_TYPE_FREE] ); ?></option>
                                        <option value="<?php echo GP_BOOK_CHARGE_TYPE_VOLUME; ?>" <?php selected( GP_BOOK_CHARGE_TYPE_VOLUME,  $book->charge_type & GP_BOOK_CHARGE_TYPE_VOLUME ); ?>><?php echo esc_html( $charge_types[GP_BOOK_CHARGE_TYPE_VOLUME] ); ?></option>
                                        <option value="<?php echo GP_BOOK_CHARGE_TYPE_CHAPTER; ?>" <?php selected( GP_BOOK_CHARGE_TYPE_CHAPTER,  $book->charge_type & GP_BOOK_CHARGE_TYPE_CHAPTER ); ?>><?php echo esc_html( $charge_types[GP_BOOK_CHARGE_TYPE_CHAPTER] ); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div id="gp_book_charge_type2" class="postbox">
                                <h2><?php _e( 'Charge Type2', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <select id="book_charge_type" name="book_charge_type2">
                                        <option value="<?php echo GP_BOOK_CHARGE_TYPE_CHAPTER_WORDS; ?>" <?php selected( GP_BOOK_CHARGE_TYPE_CHAPTER_WORDS,  $book->status & GP_BOOK_CHARGE_TYPE_CHAPTER_WORDS ); ?>><?php echo esc_html( $charge_types[GP_BOOK_CHARGE_TYPE_CHAPTER_WORDS] ); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div id="gp_book_point" class="postbox">
                                <h2><?php _e( 'Point', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="book_point" type="text" autocomplete="off" spellcheck="true" value="<?php gp_book_point( $book );?>" size="20" name="book_point">
                                </div>
                            </div>

                            <div id="gp_book_charge_order" class="postbox">
                                <h2><?php _e( 'Charge Order', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="book_charge_order" type="text" autocomplete="off" spellcheck="true" value="<?php gp_book_charge_order( $book );?>" size="20" name="book_charge_order">
                                </div>
                            </div>

                            <?php wp_enqueue_media();?>
                            <div id="gp_book_cover" class="postbox">
                                <h2><?php _e( 'Cover', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <div class="book-cover-container">
                                        <img src="<?php gp_book_cover( $book );?>?v=<?php echo time();?>" style="max-width:100%;"/>
                                    </div>
                                    <input type="file" name="book_cover"/>
                                    <input id="book_cover" type="hidden" autocomplete="off" spellcheck="true" value="<?php gp_book_raw_cover( $book );?>" size="20" name="book_cover">
                                </div>
                            </div>

                            <script>
                                jQuery(function($){

                                    // Set all variables to be used in scope
                                    var frame,
                                        metaBox = $('#gp_book_cover.postbox'), // Your meta box id here
                                        addImgLink = metaBox.find('.upload-book-cover'),
                                        delImgLink = metaBox.find( '.delete-book-cover'),
                                        imgContainer = metaBox.find( '.book-cover-container'),
                                        imgIdInput = metaBox.find( '#book_cover' );

                                    // ADD IMAGE LINK
                                    addImgLink.on( 'click', function( event ){

                                        event.preventDefault();

                                        // If the media frame already exists, reopen it.
                                        if ( frame ) {
                                            frame.open();
                                            return;
                                        }

                                        // Create a new media frame
                                        frame = wp.media({
                                            title: 'Select or Upload Media Of Your Chosen Persuasion',
                                            button: {
                                                text: 'Use this media'
                                            },
                                            multiple: false  // Set to true to allow multiple files to be selected
                                        });


                                        // When an image is selected in the media frame...
                                        frame.on( 'select', function() {

                                            // Get media attachment details from the frame state
                                            var attachment = frame.state().get('selection').first().toJSON();

                                            // Send the attachment URL to our custom image input field.
                                            imgContainer.append( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );

                                            // Send the attachment id to our hidden input
                                            imgIdInput.val( attachment.url );

                                            // Hide the add image link
                                            addImgLink.addClass( 'hidden' );

                                            // Unhide the remove image link
                                            delImgLink.removeClass( 'hidden' );
                                        });

                                        // Finally, open the modal on click
                                        frame.open();
                                    });


                                    // DELETE IMAGE LINK
                                    delImgLink.on( 'click', function( event ){

                                        event.preventDefault();

                                        // Clear out the preview image
                                        imgContainer.html( '' );

                                        // Un-hide the add image link
                                        addImgLink.removeClass( 'hidden' );

                                        // Hide the delete image link
                                        delImgLink.addClass( 'hidden' );

                                        // Delete the image id from the hidden input
                                        imgIdInput.val( '' );

                                    });

                                });
                            </script>

                            <?php $book->ID = $book->id; do_meta_boxes( get_current_screen()->id, 'side', $book ); ?>

                        </div>

                        <div id="postbox-container-2" class="postbox-container">
                            <?php do_meta_boxes( get_current_screen()->id, 'normal', $book ); ?>
                            <?php do_meta_boxes( get_current_screen()->id, 'advanced', $book ); ?>
                        </div>
                    </div><!-- #post-body -->

                </div><!-- #poststuff -->
                <?php wp_nonce_field( 'edit-book_' . $book->id ); ?>
            </form>

        <?php else : ?>

            <p><?php
                printf(
                    '%1$s <a href="%2$s">%3$s</a>',
                    __( 'No book found with this ID.', 'gampress-ext' ),
                    esc_url( gp_get_admin_url( 'admin.php?page=gp-books' ) ),
                    __( 'Go back and try again.', 'gampress-ext' )
                );
                ?></p>

        <?php endif; ?>

    </div><!-- .wrap -->

    </div><!-- .wrap -->
    <?php
}

function gp_books_admin_edit_metabox_status( $book, $box ) {
    $import_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $import_url = add_query_arg( 'action', 'import', $import_url );
    $import_url = add_query_arg( 'id', $book->id, $import_url );
    ?>
    <div id="submitpost" class="submitbox">
        <div id="minor-publishing">
            <div id="major-publishing-actions">
                <div id="minor-publishing-actions">
                    <div id="preview-action">
                        <a class="button preview" href="<?php echo gp_book_permalink( $book ) ?>" target="_blank"><?php _e( 'View Book', 'gampress-ext' ); ?></a>
                        <a class="button preview" href="<?php echo $import_url;?>"><?php _e( 'Import Chapter', 'gampress-ext' ); ?></a>

                    </div>

                    <div class="clear"></div>
                </div><!-- #minor-publishing-actions -->

                <div id="misc-publishing-actions">
                    <div class="misc-pub-section" id="comment-status-radio">
                        <select id="book_status" name="book_status[]" multiple="multiple">
                            <?php $book_status  = gp_get_books_status(); ?>
                            <?php foreach ( $book_status as $k => $v ) : ?>
                                <option value="<?php echo $k; ?>" <?php selected( $k,  $book->status & $k ); ?>><?php echo esc_html( $v ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="major-publishing-actions">
            <div id="publishing-action">
                <?php submit_button( __( 'Update', 'gampress-ext' ), 'primary', 'save', false ); ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <?php
}

/** Chapter */
function gp_books_chapters_add_admin_menu() {

    // Add our screen.
    $hook = add_menu_page(
        _x( 'Chapters', 'Admin Chapters page title', 'gampress-ext' ),
        _x( 'Chapters', 'Admin Chapters menu', 'gampress-ext' ),
        'gp_moderate',
        'gp-chapters',
        'gp_books_chapters_admin',
        'div'
    );

    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_books_chapters_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_books_chapters_add_admin_menu' );

function gp_books_chapters_admin_load() {
    global $gp_books_chapters_list_table;

    // Decide whether to load the dev version of the CSS and JavaScript
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

    $doaction = gp_admin_list_table_current_bulk_action();

    gp_core_setup_message();
    // Edit screen
    if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ) {
        add_meta_box( 'submitdiv', _x( 'Status', 'chapter admin edit screen', 'gampress-ext' ), 'gp_books_chapters_admin_edit_metabox_status', get_current_screen()->id, 'side', 'core' );
    } else if ( 'save' == $doaction ) {
        $redirect_to = remove_query_arg( array( 'action', 'id', 'deleted', 'error', 'spammed', 'unspammed', ), wp_get_referer() );

        $id = (int) $_REQUEST['id'];

        check_admin_referer( 'edit-chapter_' . $id );
        $form_names = array(
            'chapter_title'          => array( 'required' => true, 'error' => __( 'The chapter title can\'t be empty', 'gampress-ext' ) ),
            'chapter_body'             => array( 'required' => true, 'error' => __( 'The chapter body can\'t be empty', 'gampress-ext' ) ),
            'chapter_order'            => array( 'required' => true, 'error' => __( 'The chapter order can\'t be empty', 'gampress-ext' ) ),
            'chapter_is_charge'        => array( 'required' => false ),
        );
        $form_values = get_request_values( $form_names );
        if ( !empty( $form_values['error'] ) ) {
            gp_core_add_message( sprintf( $form_values['error'] ), 'error' );

            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'error', 'true', $redirect_to );
        } else {
            gp_books_update_chapter( array( 'id' => $id ,
                'title'             => $form_values['values']['chapter_title'],
                'body'              => $form_values['values']['chapter_body'],
                'order'             => $form_values['values']['chapter_order'] - 1,
                'is_charge'         => $form_values['values']['chapter_is_charge'] ) );

            gp_core_add_message( __( 'Changes saved.', 'gampress-ext' ) );
            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'updated', $id, $redirect_to );
        }

        wp_redirect( $redirect_to );
        exit;
    } else { // Index screen.
        $gp_books_chapters_list_table = new GP_Books_Chapters_List_Table();
    }

    if ( !empty( $doaction ) && ! in_array( $doaction, array( '-1', 'edit', 'save' ) ) ) {
        // Build redirection URL
        $redirect_to = remove_query_arg( array( 'aid', 'deleted', 'error', 'comfirmed', ), wp_get_referer() );
        $redirect_to = add_query_arg( 'paged', $gp_books_chapters_list_table->get_pagenum(), $redirect_to );

        $ids = (array) $_REQUEST['id'];

        if ( 'bulk_' == substr( $doaction, 0, 5 ) && ! empty( $_REQUEST['id'] ) ) {
            // Check this is a valid form submission
            check_admin_referer( 'bulk-orders' );

            // Trim 'bulk_' off the action name to avorder_id duplicating a ton of code
            $doaction = substr( $doaction, 5 );

            // This is a request to delete, spam, or un-spam, a single item.
        } elseif ( !empty( $_REQUEST['id'] ) ) {

            // Check this is a valid form submission
        }

        $confirmed = $submit = 0;

        $errors = array();

        foreach ( $ids as $id ) {
            if ( empty( $id ) )
                continue;

            switch( $doaction ) {
                case 'charge':
                    gp_books_chapter_set_charge( $id, 1 );
                    break;

                case 'approved':
                    gp_books_chapter_update_status( $id, 0 );
                    break;

                case 'format_body':
                    $chapter = gp_books_get_chapter( $id );
                    $body = gp_books_chapter_format_body( $chapter->body );
                    gp_books_chapter_update_body( $id, $body );
                    break;
            }

            unset( $order );
        }

        if ( $confirmed )
            $redirect_to = add_query_arg( 'confirmed', $confirmed, $redirect_to );

        if ( $submit )
            $redirect_to = add_query_arg( 'submit', $submit, $redirect_to );

        if ( ! empty( $errors ) )
            $redirect_to = add_query_arg( 'error', implode ( ',', array_map( 'absint', $errors ) ), $redirect_to );

        wp_redirect( $redirect_to );

    } elseif ( $doaction && 'save' == $doaction ) {

    } elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
        wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
        exit;
    }
}

function gp_books_chapters_admin() {
    // Decide whether to load the index or edit screen.
    $doaction = gp_admin_list_table_current_bulk_action();

    // Display the single group edit screen.
    if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ) {
        gp_books_chapters_admin_edit();

        // Display the group deletion confirmation screen.
    } elseif ( 'delete' == $doaction && ! empty( $_GET['id'] ) ) {
        gp_books_chapters_admin_delete();

        // Otherwise, display the books index screen.
    } else {
        gp_books_chapters_admin_index();
    }
}

function gp_books_chapters_admin_edit() {
    if ( ! is_super_admin() )
        die( '-1' );

    $book_id = ! empty( $_REQUEST['book_id'] ) ? $_REQUEST['book_id'] : 0;
    $id = ! empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;

    $chapter = gp_books_get_chapter( $id );
    $form_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $form_url = add_query_arg( 'action', 'save', $form_url );
    ?>
    <div class="wrap">
    <h1><?php printf( __( 'Editing Chapter:《%s》', 'gampress-ext' ), gp_get_chapter_title( $chapter ) ); ?></h1>

    <?php gp_core_render_message();?>

    <?php if ( ! empty( $chapter ) ) : ?>

    <form action="<?php echo esc_url( $form_url ); ?>" id="gp-books-edit-form" method="post">
        <div id="poststuff">

            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                <div id="post-body-content">
                    <div id="postdiv">
                        <div id="gp_chapter_title" class="postbox">
                            <h2><?php _e( 'Chapter Name', 'gampress-ext' ); ?></h2>
                            <div class="inside">
                                <input id="chapter_title" type="text" autocomplete="off" spellcheck="true" value="<?php gp_chapter_title( $chapter );?>" size="30" name="chapter_title">
                            </div>
                        </div>

                        <div id="gp_chapter_body" class="postbox">
                            <h2><?php _e( 'Body', 'gampress-ext' ); ?></h2>
                            <div class="inside">
                                <?php wp_editor( stripslashes( $chapter->body ), 'chapter_body', array( 'media_buttons' => false, 'textarea_rows' => 7, 'teeny' => true, 'quicktags' => array( 'buttons' => 'strong,em,link,block,del,ins,img,code,spell,close' ) ) ); ?>
                            </div>
                        </div>

                    </div>
                </div><!-- #post-body-content -->

                <div id="postbox-container-1" class="postbox-container">

                    <div id="gp_chapter_is_charge" class="postbox">
                        <h2><?php _e( 'Chapter Type', 'gampress-ext' ); ?></h2>
                        <div class="inside">
                            <select id="chapter_is_charge" name="chapter_is_charge">
                                <?php $chapter_chapter_types  = gp_get_chapters_charge_type(); ?>
                                <?php foreach ( $chapter_chapter_types as $k => $v ) : ?>
                                    <option value="<?php echo $k; ?>" <?php selected( $k,  $chapter->is_charge ); ?>><?php echo esc_html( $v ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div id="gp_chapter_is_charge" class="postbox">
                        <h2><?php _e( 'Chapter Order', 'gampress-ext' ); ?></h2>
                        <div class="inside">
                             <input type="text" name="chapter_order" value="<?php gp_chapter_order( $chapter, false ) ;?>"/>
                        </div>
                    </div>

                    <?php do_meta_boxes( get_current_screen()->id, 'side', $chapter ); ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes( get_current_screen()->id, 'normal', $chapter ); ?>
                    <?php do_meta_boxes( get_current_screen()->id, 'advanced', $chapter ); ?>
                </div>
            </div><!-- #post-body -->

        </div><!-- #poststuff -->
        <?php wp_nonce_field( 'edit-chapter_' . $chapter->id ); ?>

    </form>

    <?php endif;?>

    </div>

    <?php
}

function gp_books_chapters_admin_delete() {

}

function gp_books_chapters_admin_index() {
    global $gp_books_chapters_list_table, $plugin_page;


    $book_id = isset( $_REQUEST['book_id'] ) ? $_REQUEST['book_id'] : false;
    if ( !empty( $book_id ) )
        $book = gp_books_get_book( $book_id );

    $gp_books_chapters_list_table->prepare_items();

    $import_url = remove_query_arg( array( 'page', 'book_id', ), $_SERVER['REQUEST_URI'] );
    $import_url = add_query_arg( 'action', 'import', $import_url );
    $import_url = add_query_arg( 'page', 'gp-books', $import_url );
    $import_url = add_query_arg( 'id', $book_id, $import_url );

    wp_enqueue_script( 'jquery-ui-dialog' ); //script
    wp_enqueue_style ( 'wp-jquery-ui-dialog');
    ?>

    <div class="wrap">
        <h1>
            <?php
            if ( !empty( $book_id ) )
                printf( __( '《%s》 Chapters', 'gampress-ext' ), gp_get_book_title( $book ) );
            else
                _e( 'All Chapters', 'gampress-ext' );
            ?>

            <?php if ( is_user_logged_in() && gp_user_can_create_books() ) : ?>
                <a class="add-new-h2" href="<?php echo $import_url;?>"><?php _e( 'Add New', 'gampress-ext' ); ?></a>
            <?php endif; ?>

            <?php if ( !empty( $_REQUEST['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'gampress-ext' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
            <?php endif; ?>
        </h1>

        <hr class="wp-header-end" />

        <?php gp_core_render_message();?>

        <?php // Display each group on its own row. ?>
        <?php $gp_books_chapters_list_table->views(); ?>

        <form id="gp-books-form" action="" method="get">
            <?php $gp_books_chapters_list_table->search_box( __( 'Search all Chapters', 'gampress-ext' ), 'gp-chapters' ); ?>
            <input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
            <?php $gp_books_chapters_list_table->display(); ?>
        </form>

    </div>

    <div id="dlg-slink">
        可阅读章节
        <input type="text" id="slink-reads" value="8"/>

        <br/>

        渠道来源
        <input type="text" id="slink-from"/>

        <br/>

        地址:
        <input type="text" id="slink-url" value="" data=""/>
    </div>

    <div id="dlg-nlink" class="hide">
        渠道来源
        <input type="text" id="nlink-from"/>
        <br/>

        地址:
        <input type="text" id="nlink-url" value="" data-url=""/>
    </div>

    <style>
        #dlg-slink input, #dlg-nlink input{width:100%;}
        #dlg-slink, #dlg-nlink{display:none}
    </style>
    <script>
        $ = jQuery;
        jQuery('#gp-books-form select[name="status"]').change(function(){
            if ( $(this).val() == 'free' ) {

            }
        })

        $("table.chapters div.row-actions span.slink a").click(function(){
            $('#dlg-slink #slink-url').data("url", $(this).attr('href'));
            $('#dlg-slink').dialog({
                width:640,
                height: 480,
                modal: true
            });
            $("#dlg-slink #slink-from").val('');
            $("#dlg-slink #slink-reads").val('8');
            $("#dlg-slink #slink-url").val('');
            return false;
        });

        $("table.chapters div.row-actions span.nlink a").click(function(){
            $('#dlg-nlink #nlink-url').data("url", $(this).attr('href'));

            $('#dlg-nlink').dialog({
                width:640,
                height: 480,
                modal: true
            });
            $("#dlg-nlink #nlink-from").val('');
            $("#dlg-nlink #nlink-url").val('');
            return false;
        });

        $("#dlg-slink input").on('input propertychange',function(){
            var link = $("#dlg-slink #slink-url").data('url');

            link = link + "?read_count=" + $("#slink-reads").val() + "&from=" + $("#slink-from").val()
            $("#dlg-slink #slink-url").attr('value', link);
        });

        $("#dlg-nlink input").on('input propertychange',function(){
            var link = $("#dlg-nlink #nlink-url").data('url');

            link = link + "?from=" + $("#nlink-from").val()
            $("#dlg-nlink #nlink-url").attr('value', link);
        });

    </script>

    <?php
}

function gp_books_chapters_admin_edit_metabox_status( $chapter ) {
    ?>
    <div id="submitpost" class="submitbox">
        <div id="minor-publishing">
            <div id="major-publishing-actions">
                <div id="minor-publishing-actions">
                    <div id="preview-action">
                        <a class="button preview" href="<?php gp_chapter_permalink( $chapter ) ?>" target="_blank"><?php _e( 'View Chapter', 'gampress-ext' ); ?></a>
                    </div>

                    <div class="clear"></div>
                </div><!-- #minor-publishing-actions -->

                <div id="misc-publishing-actions">
                    <div class="misc-pub-section" id="comment-status-radio">

                    </div>
                </div>
            </div>
        </div>

        <div id="major-publishing-actions">
            <div id="publishing-action">
                <?php submit_button( __( 'Update', 'gampress-ext' ), 'primary', 'save', false ); ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <?php
}

function gp_books_manage_book_recommend_custom_column( $column ){
    global $post;
    switch ($column) {
        case 'taxonomy':
            $term_ids = wp_get_post_terms( $post->ID, gp_get_book_recommend_post_taxonomy(), array( 'fields' => 'ids' ) );
            $term_name = array();
            foreach( $term_ids as $t_id ) {
                $term = get_term_by('id', $t_id, gp_get_book_recommend_post_taxonomy());
                $term_name[] = $term->name;
            }
            echo join( $term_name, ',');
            break;

        case 'offline_date':
            echo get_post_meta( $post->ID, 'offline_date', true );
            break;

        case 'book_id';
            echo $post->post_parent;
            break;
    }
}

function gp_books_manage_book_recommend_posts_columns($columns){

    $columns['taxonomy'] = '类别';
    $columns['offline_date'] = '下线时间';
    $columns['book_id'] = 'book_id';

    return $columns;
}

function gp_book_recommend_restrict_manage_posts( $post_type, $which ) {
    if ( is_object_in_taxonomy( $post_type, gp_get_book_recommend_post_taxonomy() ) ) {
        echo '<label class="screen-reader-text" for="cat">' . __( 'Filter by category' ) . '</label>';
        $taxonomy      = 'book_recommend_category'; // change to your taxonomy
        $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        $info_taxonomy = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => __("Show All {$info_taxonomy->label}"),
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'show_count'      => true,
            'hide_empty'      => true,
        ));
    }
}

function gp_book_recommend_convert_id_to_term_in_query($query) {
    global $pagenow;
    $post_type = 'book_recommend'; // change to your post type
    $taxonomy  = 'book_recommend_category'; // change to your taxonomy
    $q_vars    = &$query->query_vars;
    if ( $pagenow == 'edit.php' && isset($q_vars['post_type'])
        && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy])
        && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
        $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
        $q_vars[$taxonomy] = $term->slug;
    }
}

function gp_book_recommend_metabox() {
    global $post;
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="gp_book_recommend_noncename" id="gp_book_recommend_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

    // Get the data if its already been entered
    $post_link = get_post_meta($post->ID, 'gp_book_recommend_link', true);
    // Echo out the field
    ?>

    <div class="width_full p_box">
        <p>
            <label>Link<br>
                <input type="text" name="gp_book_recommend_link" class="widefat" value="<?php echo $post_link; ?>">
            </label>
        </p>
    </div>
    <?php
}

function gp_book_recommand_add_post_type_metabox() {
    add_meta_box( 'gp_book_recommend_metabox', 'Link', 'gp_book_recommend_metabox', 'book_recommend', 'normal', 'high');
}

function book_recommend_post_save_meta( $post_id, $post ) { // save the data
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( $post->post_type != 'book_recommend' )
        return $post->ID;

    if( empty( $_REQUEST['gp_book_recommend_noncename'] ) || !wp_verify_nonce( $_POST['gp_book_recommend_noncename'], plugin_basename(__FILE__) ) )
        return $post->ID;

    // is the user allowed to edit the post or page?
    if( ! current_user_can( 'edit_post', $post->ID ) )
        return $post->ID;

    // ok, we're authenticated: we need to find and save the data
    // we'll put it into an array to make it easier to loop though

    $post_meta['gp_book_recommend_link'] = $_POST['gp_book_recommend_link'];

    // add values as custom fields
    foreach( $post_meta as $key => $value ) { // cycle through the $quote_post_meta array
        // if( $post->post_type == 'revision' ) return; // don't store custom data twice
        $value = implode(',', (array)$value); // if $value is an array, make it a CSV (unlikely)
        if( get_post_meta( $post->ID, $key, FALSE ) ) { // if the custom field already has a value
            update_post_meta($post->ID, $key, $value);
        } else { // if the custom field doesn't have a value
            add_post_meta( $post->ID, $key, $value );
        }
        if( !$value ) { // delete if blank
            delete_post_meta( $post->ID, $key );
        }
    }
}

/*********** Book Free ***********/
function gp_books_free_add_admin_menu() {

    // Add our screen.
    $hook = add_menu_page(
        _x( 'Frees', 'Admin free page title', 'gampress-ext' ),
        _x( 'Frees', 'Admin free menu', 'gampress-ext' ),
        'gp_moderate',
        'gp-books-free',
        'gp_books_frees_admin',
        'div'
    );

    // Hook into early actions to load custom CSS and our init handler.
    add_action( "load-$hook", 'gp_books_free_admin_load' );
}
add_action( gp_core_admin_hook(), 'gp_books_free_add_admin_menu' );

function gp_books_free_admin_load() {
    global $gp_books_free_list_table;

    // Decide whether to load the dev version of the CSS and JavaScript
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

    $doaction = gp_admin_list_table_current_bulk_action();

    gp_core_setup_message();
    // Edit screen
    if ( 'edit' == $doaction ) {

    } else if ( 'save' == $doaction ) {
        $redirect_to = remove_query_arg( array( 'action', 'id', 'deleted', 'error', 'spammed', 'unspammed', ), wp_get_referer() );

        $id = (int) $_REQUEST['id'];
        check_admin_referer( 'edit-book-free_' . $id );

        $form_names = array(
            'free_name'             => array( 'required' => true, 'error' => __( 'The activity name can\'t be empty', 'gampress-game' ) ),
            'free_book_names'       => array( 'required' => true, 'error' => __( 'The activity names can\'t be empty', 'gampress-game' ) ),
            'free_start_time'       => array( 'required' => true, 'error' => __( 'The activity start time can\'t be empty', 'gampress-game' ) ),
            'free_end_time'         => array( 'required' => true, 'error' => __( 'The activity end time can\'t be empty', 'gampress-game' ) ),

        );
        $form_values = get_request_values( $form_names );
        if ( !empty( $form_values['error'] ) ) {
            gp_core_add_message( sprintf( $form_values['error'] ), 'error' );

            $redirect_to = add_query_arg( 'id', $id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'error', 'true', $redirect_to );
        } else {
            $book_ids = $form_values['values']['free_book_names'];
            $book_names = explode( ",", $book_ids );
            $book_ids = array();
            foreach($book_names as $book_name) {
                $book_ids[] = GP_Books_Book::book_exists( $book_name );
            }
            $book_ids = join( ',', $book_ids );

            $new_id = gp_books_update_book_free( array( 'id' => $id ,
                'name'              => $form_values['values']['free_name'],
                'book_ids'          => $book_ids,
                'start_time'        => $form_values['values']['free_start_time'],
                'end_time'          => $form_values['values']['free_end_time']
            ) );

            gp_core_add_message( __( 'Changes saved.', 'gampress-ext' ) );
            $redirect_to = add_query_arg( 'id', $new_id, $redirect_to );
            $redirect_to = add_query_arg( 'action', 'edit', $redirect_to );
            $redirect_to = add_query_arg( 'updated', $new_id, $redirect_to );
        }

        wp_redirect( $redirect_to );
        exit;
    } else { // Index screen.
        $gp_books_free_list_table = new GP_Books_Free_List_Table();
    }
}

function gp_books_frees_admin() {
    // Decide whether to load the index or edit screen.
    $doaction = gp_admin_list_table_current_bulk_action();

    // Display the single group edit screen.
    if ( 'edit' == $doaction ) {
        gp_books_free_admin_edit();

    } else {
        gp_books_free_admin_index();
    }
}

function gp_books_free_admin_edit() {
    if ( ! is_super_admin() )
        die( '-1' );

    $id = ! empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;

    $free = gp_books_get_free( $id );
    if ( !empty( $free ) ) {
        $bids = explode( ",", $free->book_ids );
        $bids = array_filter( $bids);
        $free_books = array();
        if ( !empty( $bids ) ) {
            foreach ($bids as $bid) {
                $book = gp_books_get_book( $bid );
                $free_books[] = $book->title;
            }
        }
        $free_books = join( ',', $free_books );
    } else {
        $free_books = '';
    }

    $form_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $form_url = add_query_arg( 'action', 'save', $form_url );
    ?>
    <div class="wrap">
        <?php if ( empty( $id ) ) : ?>
            <h1><?php printf( __( 'Add Free', 'gampress-ext' ) ); ?></h1>
        <?php else:?>
            <h1><?php printf( __( 'Editing Free:《%s》', 'gampress-ext' ), $free->name ); ?></h1>
        <?php endif;?>

        <?php gp_core_render_message();?>

        <form action="<?php echo esc_url( $form_url ); ?>" id="gp-books-edit-form" method="post">
            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                    <div id="post-body-content">
                        <div id="postdiv">
                            <div id="gp_free_name" class="postbox">
                                <h2><?php _e( 'Free Name', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="free_name" type="text" autocomplete="off" spellcheck="true" value="<?php echo $free->name;?>" size="30" name="free_name">
                                </div>
                            </div>

                            <div id="gp_free_book_names" class="postbox">
                                <h2><?php _e( 'Book Names', 'gampress-ext' ); ?></h2>
                                <div class="inside">
                                    <input id="free_book_names" type="text" autocomplete="off" spellcheck="true" value="<?php echo $free_books; ?>" size="30" name="free_book_names">
                                </div>
                            </div>

                        </div>
                    </div><!-- #post-body-content -->

                    <div id="postbox-container-1" class="postbox-container">

                        <div id="gp_free_start_time" class="postbox">
                            <h2><?php _e( 'StartTime', 'gampress-game' ); ?></h2>
                            <div class="inside">
                                <input id="free_start_time" type="text" autocomplete="off" spellcheck="true" value="<?php echo $free->start_time;?>" size="20" name="free_start_time">
                            </div>
                        </div>

                        <div id="gp_free_start_time" class="postbox">
                            <h2><?php _e( 'EndTime', 'gampress-game' ); ?></h2>
                            <div class="inside">
                                <input id="free_end_time" type="text" autocomplete="off" spellcheck="true" value="<?php echo $free->end_time;?>" size="20" name="free_end_time">
                            </div>


                            <div id="major-publishing-actions">
                                <div id="publishing-action">
                                    <?php submit_button( __( 'Update', 'gampress-ext' ), 'primary', 'save', false ); ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>

                        <?php do_meta_boxes( get_current_screen()->id, 'side', $free ); ?>

                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <?php do_meta_boxes( get_current_screen()->id, 'normal', $free ); ?>
                        <?php do_meta_boxes( get_current_screen()->id, 'advanced', $free ); ?>
                    </div>
                </div><!-- #post-body -->

            </div><!-- #poststuff -->
            <link rel="stylesheet" type="text/css" href="<?php echo GP_EXT_PLUGIN_URL;?>includes/books/admin/css/jquery.datetimepicker.css"/ >
            <script src="<?php echo GP_EXT_PLUGIN_URL;?>includes/books/admin/js/jquery.js"></script>
            <script src="<?php echo GP_EXT_PLUGIN_URL;?>includes/books/admin/js/jquery.datetimepicker.full.js"></script>
            <script>
                jQuery(document).ready(function() {
                    jQuery('#free_start_time,#free_end_time').datetimepicker({timepicker:false,format:"Y-m-d",lang:"ch"});
                });
            </script>
            <?php wp_nonce_field( 'edit-book-free_' . $free->id ); ?>
        </form>

    </div>

    <?php
}

function gp_books_free_admin_index() {
    global $gp_books_free_list_table, $plugin_page;

    $messages = array();

    // If the user has just made a change to a group, build status messages.
    if ( ! empty( $_REQUEST['deleted'] ) ) {
        $deleted  = ! empty( $_REQUEST['deleted'] ) ? (int) $_REQUEST['deleted'] : 0;

        if ( $deleted > 0 ) {
            $messages[] = sprintf( _n( '%s group has been permanently deleted.', '%s activities have been permanently deleted.', $deleted, 'gampress-game' ), number_format_i18n( $deleted ) );
        }
    }
    $messages[] = isset( $_COOKIE['gp-message'] ) ? $_COOKIE['gp-message'] : '';

    // Prepare the group items for display.
    $gp_books_free_list_table->prepare_items();

    $edit_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
    $edit_url = add_query_arg( 'action', 'edit', $edit_url );
    $edit_url = add_query_arg( 'id', '0', $edit_url );
    ?>

    <div class="wrap">
        <h1>
            <?php _e( 'Free', 'gampress-ext' ); ?>

            <?php if ( is_user_logged_in() ) : ?>
                <a class="add-new-h2" href="<?php echo $edit_url;?>"><?php _e( 'Add New', 'gampress-ext' ); ?></a>
            <?php endif; ?>

            <?php if ( !empty( $_REQUEST['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'gampress-ext' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
            <?php endif; ?>
        </h1>

        <hr class="wp-header-end" />

        <?php gp_core_render_message();?>

        <?php // Display each group on its own row. ?>
        <?php $gp_books_free_list_table->views(); ?>

        <form id="gp-activities-form" action="" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
            <?php $gp_books_free_list_table->display(); ?>
        </form>

    </div>

    <?php
}