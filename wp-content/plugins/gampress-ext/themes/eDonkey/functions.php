<?php
/**
 * 
 * 嗯
 */

function gp_ext_exception_handler( $ex ) { 
    get_header();
?>
    <div class="m-main">
        <div class="m-filter">
            <div class="container">
            <?php
            global $exception;

            echo $ex->getMessage();
            ?>
            </div>
        </div>
    </div>
<?php
get_footer();
}
set_exception_handler( 'gp_ext_exception_handler' );

function par_pagenavi( $page_index, $page_size, $data_totals, $url_pattern, $page_range = 7 ) {
    if ( $data_totals == 0 )
        return false;
    
    if ( $page_index <= 0 )
        return false;
        
    $total_page = ceil( $data_totals / $page_size );
    
    if ( $page_index > $total_page )
        return false;
        
    $gauge = (int) ( $page_range / 2 );
    
    $start_index = $page_index - $gauge;
    $end_index = 1;
    
    $pre_index = $page_index - 1;
    $next_index = $page_index + 1;
    
    if ( $start_index <= 0 ) {
        $start_index = 1;
        $end_index = $page_range + 1;
    } else {
        $end_index = $start_index + $page_range;
    }
    
    if ( $end_index >= $total_page ) {
        $end_index = $total_page + 1;
        $start_index = $end_index - $page_range;
        
        //if ( $start_index > $total_page - $page_range )
        //    $start_index = $total_page - $page_range;
        
        if ( $start_index < 0 )
            $start_index = 1;
    }
    
    $pages = array();
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination"><li class="page-item"><a class="page-link">'. '第' . $page_index . '页' .'（共' . $total_page . '页）'. '</a></li> ';  
    if( $page_index != 1 ) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $url_pattern, 1 ) . '"> 第1页 </a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $pre_index, 1 ) . '">上一页</a></li>';
    }
        
    for( $i = $start_index; $i < $end_index; $i++ ) {
        if ( $i == $page_index )
            $html .= '<li class="page-item active"><a class="page-link">' . $i . '</a></li>';
        else
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $url_pattern, $i ) .'">' . $i . '</a></li>';
    }
    
    if( $page_index != $total_page ) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $next_index, 1 ) . '">下一页</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf( $url_pattern, $total_page ) . '"> 最后一页 </a></li>';
    }
    
    $html .= '</ul></nav>';
            
    echo $html;
}

if ( !function_exists( 'active' ) ) {
    function active( $valuea, $valueb, $echo = true ) {
        if ( (string) $valuea === (string) $valueb )
		    $result = "active";
	    else
		    $result = '';

	    if ( $echo )
		    echo $result;

	    return $result;
    }
}

if ( ! function_exists( 'gp_dtheme_setup' ) ) :
function gp_dtheme_setup() {

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'gampress' ),
	) );
}
add_action( 'after_setup_theme', 'gp_dtheme_setup' );
endif;