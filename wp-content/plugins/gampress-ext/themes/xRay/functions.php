<?php

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

    $html = '<nav aria-label="Page navigation"><ul class="pagination"><li class="disabled"><a>'. '第' . $page_index . '页' .'（共' . $total_page . '页）'. ' </a></li> ';
    if( $page_index != 1 ) {
        $html .= "<li><a href='" . sprintf( $url_pattern, 1 ) . "'> 第1页 </a></li>";
        $html .= "<li><a href='" . sprintf( $pre_index, 1 ) . "'>上一页</a></li>";
    }

    for( $i = $start_index; $i < $end_index; $i++ ) {
        if ( $i == $page_index )
            $html .= "<li class='active'><a>" . $i . '<span class="sr-only">(current)</span></a></li>';
        else
            $html .= "<li><a href='" . sprintf( $url_pattern, $i ) ."'>" . $i . '</a></li>';
    }

    if( $page_index != $total_page ) {
        $html .= "<li><a href='" . sprintf( $next_index, 1 ) . "'>下一页</a></li>";
        $html .= "<li><a href='" . sprintf( $url_pattern, $total_page ) . "'> 最后一页 </a></li>";
    }
    $html .= '</ul></nav>';

    echo $html;
}