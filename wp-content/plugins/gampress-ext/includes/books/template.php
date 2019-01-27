<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/2
 * Time: 9:42
 */

function gp_books_get_current_book() {
    $gp = gampress();

    return $gp->books->current_book;
}

function gp_is_books_component() {
    return gp_is_current_component( 'books' );
}

function gp_get_book_recommend_post_type() {
    return gampress()->books->book_post_type;
}

function gp_get_book_recommend_post_taxonomy() {
    return gampress()->books->book_post_taxonomy;
}

function gp_get_book_recommend_post_type_labels() {
    return array(
        'name'               => __( 'Book Recommends',                   'gampress-ext' ),
        'menu_name'          => __( 'Book Recommends',                   'gampress-ext' ),
        'singular_name'      => __( 'Book Recommend',                    'gampress-ext' ),
        'all_items'          => __( 'All Recommends',               'gampress-ext' ),
        'add_new'            => __( 'New Recommend',                'gampress-ext' ),
        'add_new_item'       => __( 'Create New Recommend',         'gampress-ext' ),
        'edit'               => __( 'Edit',                              'gampress-ext' ),
        'edit_item'          => __( 'Edit Recommend',               'gampress-ext' ),
        'new_item'           => __( 'New Recommend',                'gampress-ext' ),
        'view'               => __( 'View Recommend',               'gampress-ext' ),
        'view_item'          => __( 'View Recommend',               'gampress-ext' ),
        'search_items'       => __( 'Search Recommends',            'gampress-ext' ),
        'not_found'          => __( 'No Recommends found',          'gampress-ext' ),
        'not_found_in_trash' => __( 'No Recommends found in Trash', 'gampress-ext' ),
        'parent_item_colon'  => __( 'Forum:',                            'gampress-ext' )
    );
}

function gp_get_book_recommend_post_type_rewrite() {
    return array(
        'slug'       => 'Book Recommend',
        'with_front' => false
    );
}

function gp_get_book_recommend_post_type_supports() {
    return array(
        'title',
        'editor',
        'thumbnail',
        'comments'
    );
}

function gp_user_can_create_books() {

    // Super admin can always create books.
    if ( gp_current_user_can( 'gp_moderate' ) ) {
        return true;
    }

    // Get book creation option, default to 0 (allowed).
    $restricted = (int) gp_get_option( 'gp_restrict_book_creation', 0 );

    // Allow by default.
    $can_create = true;

    // Are regular users restricted?
    if ( $restricted ) {
        $can_create = false;
    }
    
    return apply_filters( 'gp_user_can_create_books', $can_create, $restricted );
}

function gp_books_directory_permalink() {
    echo esc_url( gp_get_books_directory_permalink() );
}

    function gp_get_books_directory_permalink() {
        return trailingslashit( gp_get_root_domain() . '/' . gp_get_books_root_slug() );
    }

function gp_is_books_directory() {
    if ( ! gp_displayed_user_id() && gp_is_books_component() && ! gp_current_action() ) {
        return true;
    }
    return false;
}

function gp_book_slug( $book ) {
    echo gp_get_book_slug( $book );
}
    function gp_get_book_slug( $book ) {
        //return $book->title;
        return $book->id + GP_BOOK_BASE_INDEX;
    }

function gp_books_slug() {
    echo gp_get_books_slug();
}
    function gp_get_books_slug() {
        return apply_filters( 'gp_get_books_root_slug', gampress()->books->slug );
    }

function gp_books_root_slug() {
    echo gp_get_books_root_slug();
}
    function gp_get_books_root_slug() {
        return apply_filters( 'gp_get_books_root_slug', gampress()->books->root_slug );
    }

function gp_book_title($book ) {
    echo gp_get_book_title( $book );
}
    function gp_get_book_title( $book ) {
        return $book->title;
    }

function gp_book_tags( $book ) {
    echo gp_get_book_tags( $book );
}
    function gp_get_book_tags( $book ) {
        echo $book->tags;
    }

function gp_book_level( $book ) {
    echo gp_get_book_level( $book );
}
    function gp_get_book_level( $book ) {
        echo $book->level;
    }

function gp_book_summary( $book ) {
    echo gp_get_book_summary( $book );
}
    function gp_get_book_summary( $book ) {
        echo $book->summary;
    }

function gp_book_description( $book ) {
    echo gp_get_book_description( $book );
}
    function gp_get_book_description( $book ) {
        return $book->description;
    }

function gp_book_point( $book ) {
    echo gp_get_book_point( $book );
}
    function gp_get_book_point( $book ) {
        return $book->point;
    }

function gp_book_charge_order( $book ) {
    echo gp_get_book_charge_order( $book );
}
    function gp_get_book_charge_order( $book ) {
        return $book->charge_order;
    }

function gp_book_cover( $book, $size = 'l' ) {
    echo gp_get_book_cover( $book, $size );
}
    function gp_get_book_cover( $book, $size = 'l' ) {
        if ( empty( $book->cover ) )
            return get_template_directory_uri() . '/dist/images/cover.png';
        return wp_get_upload_dir()['baseurl'] . '/ebooks/' . $size . '/' . $book->cover;
    }

function gp_book_raw_cover( $book, $size = 'm' ) {
    echo gp_get_book_raw_cover( $book, $size );
}
    function gp_get_book_raw_cover( $book, $size = 'm' ) {
        return $book->cover;
    }

function gp_book_author( $book ) {
    echo gp_get_book_author( $book );
}
    function gp_get_book_author( $book ) {
        echo $book->author;
    }

function gp_book_author_id( $book ) {
    echo gp_get_book_author_id( $book );
}
    function gp_get_book_author_id( $book ) {
        echo $book->author_id;
    }

function gp_book_words( $book ) {
    echo gp_get_book_words( $book );
}
    function gp_get_book_words( $book ) {
        echo $book->words;
    }

function gp_book_chapter_type( $book, $echo_text = false ) {
    echo gp_get_book_chapter_type( $book, $echo_text );
}
    function gp_get_book_chapter_type( $book, $echo_text = false ) {
        if ( $echo_text ) {
            $chapter_type = gp_get_books_chapter_type();

            return $chapter_type[$book->chapter_type];
        }

        return $book->chapter_type;
    }

    function gp_get_books_chapter_type() {
        $arr = array(
            __( 'CHAPTER', 'gampress-ext' ),
            __( 'VOLUME', 'gampress-ext' )
        );

        return $arr;
    }

function gp_book_status( $book, $echo_text = false, $simple = true  ) {
    echo gp_get_book_status( $book, $echo_text, $simple );
}
    function gp_get_book_status( $book, $echo_text = false, $simple = true ) {
        if ( $echo_text ) {
            $status = gp_get_books_status();
            $txts = array();

            foreach ($status as $val => $txt) {
                if ( ( $book->status & $val ) === $val ) {
                    $txts[] = $txt;
                    if ( $simple == true )
                        break;
                }
            }
            return join( $txts, ' - ' );
        }

        return $book->status;
    }

function gp_get_books_status() {
    $arr = array(
        1 => __( 'Book Seriating', 'gampress-ext' ),
        2 => __( 'Book Finish', 'gampress-ext' ),
        4 => __( 'Book Hide', 'gampress-ext' ),
        8 => __( 'Book Disabled', 'gampress-ext' )
    );

    return $arr;
}

function gp_book_charge_type( $book, $echo_text = false ) {
    echo gp_get_book_charge_type( $book, $echo_text );
}

    function gp_get_book_charge_type( $book, $echo_text = false ) {
        if ( $echo_text ) {
            $charge_types = gp_get_books_charge_type();
            $txts = array();

            foreach ( $charge_types as $k => $v ) {
                if ( ( $k & $book->charge_type ) == $k )
                    $txts[] = $v;
            }

            return join( $txts, ',');
        }

        return $book->charge_type;
    }


function gp_get_books_charge_type() {
    $arr = array(
        GP_BOOK_CHARGE_TYPE_FREE                => __( 'FREE', 'gampress-ext' ) ,
        GP_BOOK_CHARGE_TYPE_VOLUME              => __( 'VOLUME',  'gampress-ext' ) ,
        GP_BOOK_CHARGE_TYPE_CHAPTER             => __( 'CHAPTER', 'gampress-ext' ) ,

        GP_BOOK_CHARGE_TYPE_CHAPTER_WORDS       => __( 'CHAPTER_WORDS', 'gampress-ext' )
    );

    return $arr;
}

function gp_book_permalink( $book = null ) {
    echo gp_get_book_permalink( $book );
}

    function gp_get_book_permalink( $book = null ) {
        return trailingslashit( gp_get_books_directory_permalink() . gp_get_book_slug( $book ) . '/' );
    }

function gp_book_chapters_permalink( $book, $order = '' ) {
    echo gp_get_book_chapters_permalink( $book, $order );
}

    function gp_get_book_chapters_permalink( $book, $order = '' ) {
        if ( $order === '' ) {
            $link = gp_get_book_permalink( $book ) . 'catalog';
        } else {
//            $pi = ceil( $order / 100 );
//            if ( $pi == 0 )
//                $pi = 1;
//            $link = gp_get_book_permalink( $book ) . 'catalog/' . $pi . '?order=' .$order;

            $catalogs = gp_books_catalogs( $book->id, 'ASC', 100 );
            $order ++;
            for ($i = 0; $i < count($catalogs); $i++) {
                if ( $order <= $catalogs[$i][1] ) {
                    $link = gp_get_book_permalink( $book ) . 'catalog/' . ($i + 1) . '?idx=' . $order;
                    break;
                }
            }
        }
        return $link;
    }

function gp_books_catalog_permlink( $term_name ) {
    echo gp_books_get_catalog_permlink( $term_name );
}
    function gp_books_get_catalog_permlink( $term_name ) {
        return gp_get_books_directory_permalink() . 'catalog/' . $term_name;
    }

/** Chapter */
function gp_books_catalogs( $book_id, $order, $size = 100) {
    $datas = gp_books_get_chapters(array(
        'book_id' => $book_id,
        'status' => GP_CHAPTER_NORMAL,
        'page' => 1,
        'order' => $order,
        'per_page' => 99999));

    $catalogs = array();
    $count = ceil( count( $datas['items'] ) / $size ) - 1;
    $start = $datas['items'][0]->order + 1;
    $end = 1;

    if ( $count == 0 ) {
        $item = $datas['items'][count( $datas['items'] ) - 1];
        $end = $item->order + 1;
        $catalogs[] = array( $start, $end ); //"第{$start} - {$end}章";
    } else {
        for ($i = 0; $i < $count; $i++) {
            $pos = ($i + 1) * $size - 1;
            $item = $datas['items'][$pos];
            $end = $item->order + 1;

            $catalogs[] = array( $start, $end );//"第{$start} - {$end}章";

            if ( $order == 'ASC' )
                $start = $end + 1;
            else
                $start = $end - 1;
        }
        $item = $datas['items'][count( $datas['items'] ) - 1];
        $end = $item->order + 1;
        $catalogs[] = array( $start, $end );//"第{$start} - {$end}章";
    }

    return $catalogs;
}

function gp_books_get_current_chapter( $book ) {
    $chapter_id = gp_action_variable( 0 );
    if ( gp_get_books_chapter_vip_slug() == $chapter_id || gp_get_books_chapter_simple_slug() == $chapter_id )
        $chapter_id = gp_action_variable( 1 );

    $chapter_id = GP_Books_Chapter::chapter_exists( $book->id, $chapter_id, 0 );
    return gp_books_get_chapter( $chapter_id );
}

function gp_chapter_id( $chapter ) {
    echo gp_get_chapter_id( $chapter );
}
    function gp_get_chapter_id( $chapter ) {
        return $chapter->id;
    }

function gp_chapter_title( $chapter ) {
    echo gp_get_chapter_title( $chapter );
}
    function gp_get_chapter_title( $chapter ) {
        if ( empty( $chapter ) )
            return '';
        return $chapter->title;
    }

function gp_chapter_body( $chapter, $format = false ) {
    echo gp_get_chapter_body( $chapter, $format );
}

    function gp_get_chapter_body( $chapter, $format = false ) {
        if ( $format ) {
            $str = nl2p( htmlspecialchars( $chapter->body ) );
            return $str;
        } else {
            return $chapter->body;
        }
    }

function gp_chapter_parent_id( $chapter ) {
    echo gp_get_chapter_parent_id( $chapter );
}

    function gp_get_chapter_parent_id( $chapter ) {
        return $chapter->parent_id;
    }

function gp_chapter_book_id( $chapter ) {
    echo gp_get_chapter_book_id( $chapter );
}

    function gp_get_chapter_book_id( $chapter ) {
        return $chapter->book_id;
    }

function gp_chapter_refer( $chapter ) {
    echo gp_get_chapter_refer( $chapter );
}

    function gp_get_chapter_refer( $chapter ) {
        return $chapter->refer;
    }

function gp_chapter_order( $chapter, $echo_text = false ) {
    echo gp_get_chapter_order( $chapter, $echo_text );
}

    function gp_get_chapter_order( $chapter, $echo_text = false ) {
        $txt = '';

        if ( !empty( $chapter ) ) {
            if ( $echo_text ) {
                $book = gp_books_get_book( $chapter->book_id );
                if ( $book->chapter_type == GP_CHAPTER_VOLUME && $chapter->parent_id == 0 ) {
                    preg_match('/第.*?卷/u', $chapter->title, $matches);
                    if ( empty( $matches ) )
                        $txt = sprintf(__('No: %s Volume', 'gampress-ext'), (int)$chapter->order + 1);
                } else {
                    preg_match( '/第.*?章/u', $chapter->title, $matches );
                    if ( empty( $matches ) )
                        $txt = sprintf(__('No: %s Chapter', 'gampress-ext'), (int)$chapter->order + 1);
                }
            } else {
                $txt = (int)$chapter->order + 1;
            }
        }

        return $txt;
    }

function gp_chapter_words( $chapter ) {
    echo gp_get_chapter_words( $chapter );
}

    function gp_get_chapter_words( $chapter ) {
        return $chapter->words;
    }

function gp_chapter_is_charge( $chapter, $echo_text = false ) {
    echo gp_get_chapter_is_charge( $chapter );
}
    function gp_get_chapter_is_charge( $chapter ) {
        return $chapter->is_charge == 1;
    }

function gp_chapter_post_time( $chapter ) {
    echo gp_get_chapter_post_time( $chapter );
}
    function gp_get_chapter_post_time( $chapter ) {
        return gp_format_time( $chapter->post_time );
    }

function gp_chapter_status( $chapter ) {
    echo gp_get_chapter_status( $chapter );
}
    function gp_get_chapter_status( $chapter ) {
        return $chapter->status;
    }

function gp_chapter_approved_time( $chapter ) {
    echo gp_get_chapter_approved_time( $chapter );
}

    function gp_get_chapter_approved_time( $chapter ) {
        return gp_format_time( $chapter->approved_time );
    }

function gp_chapter_update_time( $chapter ) {
    echo gp_get_chapter_update_time( $chapter );
}

    function gp_get_chapter_update_time( $chapter ) {
        return gp_format_time( $chapter->update_time );
    }


    function gp_get_chapters_charge_type() {
        $arr = array(
            GP_CHAPTER_FREE                => __( 'FREE',    'gampress-ext' ) ,
            GP_CHAPTER_CHARGE              => __( 'CHARGE',  'gampress-ext' ) ,
        );

        return $arr;
    }

function gp_chapter_fee( $chapter_id ) {
    echo gp_get_chapter_fee( $chapter_id );
}

    function gp_get_chapter_fee( $chapter_id ) {
        $chapter = new GP_Books_Chapter( $chapter_id );
        return ceil( $chapter->words / 1000 * 5 ) / 100;
    }

function gp_chapter_coin( $chapter_id ) {
    echo gp_get_chapter_coin( $chapter_id );
}

    function gp_get_chapter_coin( $chapter_id ) {
        $chapter = new GP_Books_Chapter( $chapter_id );
        return gp_books_word2coin( $chapter->words );//ceil( $chapter->words / 1000 * 5 );
    }

function gp_chapter_permalink( $chapter, $is_simple = false ) {
    echo gp_get_chapter_permalink( $chapter, $is_simple );
}

    function gp_get_chapter_permalink( $chapter, $is_simple = false ) {
        if ( empty( $chapter ) )
            return '';
        $book = gp_books_get_book( $chapter->book_id );
        return trailingslashit( gp_get_book_permalink($book) . gp_get_books_chapters_slug() . '/' . gp_get_book_chapter_slug( $chapter, $is_simple ) );
    }

function gp_book_chapter_slug( $chapter, $is_simple = false ) {
    echo gp_get_book_chapter_slug( $chapter, $is_simple );
}
    function gp_get_book_chapter_slug( $chapter, $is_simple = false ) {
        //return $chapter->title;
        if ( $chapter->is_charge )
            return gp_get_books_chapter_vip_slug() . '/' . ($chapter->id + GP_CHAPTER_BASE_INDEX);
        else if ( $is_simple == true )
            return gp_get_books_chapter_simple_slug() . '/' . ($chapter->id + GP_CHAPTER_BASE_INDEX);
        else
            return $chapter->id + GP_CHAPTER_BASE_INDEX;
    }

function gp_books_chapters_slug() {
    echo gp_get_books_chapters_slug();
}
    function gp_get_books_chapters_slug() {
        return apply_filters( 'gp_get_books_chapters_slug', gampress()->books->chapters_slug );
    }

function gp_books_chapters_root_slug() {
    echo gp_get_books_chapters_root_slug();
}
    function gp_get_books_chapters_root_slug() {
        return apply_filters( 'gp_get_books_root_slug', gampress()->books->chapters_root_slug );
    }

function gp_books_chapter_vip_slug() {
    echo gp_get_books_chapter_vip_slug();
}
    function gp_get_books_chapter_vip_slug() {
        return 'vip';
    }

function gp_books_chapter_simple_slug() {
    echo gp_get_books_chapter_simple_slug();
}
    function gp_get_books_chapter_simple_slug() {
        return 'simple';
    }

