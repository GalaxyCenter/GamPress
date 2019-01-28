<?php
/*
Template Name: Book_Rank
*/
get_header();

$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'top-seller';
$sub= isset( $_GET['sub'] ) ? $_GET['sub'] : 'cm';

if ( $sub == 'cm' )
    $mondate = date("Ym",strtotime("+0 month") );
else
    $mondate = date("Ym",strtotime("last month") );

if ( $tab == 'top-seller') {
    $items = gp_get_orders_top_item2( $mondate, 10 );
} else if ( $tab == 'top-bookmarks' ) {
    $items = gp_books_get_top_bookmark( $mondate, 30 );
} else if ( $tab == 'top-views' ) {
    $items = gp_books_get_top_views( $mondate, 30 );
}

?>

<div class="content" id="book_rank">
    <div class="nav">
        <a href="?tab=top-seller" class="item <?php active( 'top-seller', $tab, true ) ;?>">畅销榜</a>
        <a href="?tab=top-bookmarks" class="item <?php active( 'top-bookmarks', $tab, true ) ;?>">追书榜</a>
        <a href="?tab=top-views" class="item <?php active( 'top-views', $tab, true ) ;?>">人气榜</a>
    </div>
    <div class="tab-ranking">
        <div class="hd">
            <a href="?tab=<?php echo $tab;?>&sub=cm" class="item <?php active( 'cm', $sub, true ) ;?>" data-id="m-1">本月</a><a href="?tab=<?php echo $tab;?>&sub=lm" class="item <?php active( 'lm', $sub, true ) ;?>" data-id="m-2">上月</a>
        </div>
        <div class="bd">
            <div class="list active">
                <?php $idx = 0; foreach ( $items as $i => $item ) : if ( ( $item->book->status & GP_BOOK_HIDE) != GP_BOOK_HIDE && $idx < 10 ) : $idx++; ?>
                <a href="<?php gp_book_permalink( $item->book ) ;?>?from=adx-rank-<?php echo $tab . '-' . $sub;?>" class="item"><i class="<?php if ( $i < 3 ) { echo 'color'; } ;?>"><?php echo $idx;?></i><?php gp_book_title( $item->book ) ;?></a>
                <?php endif;  endforeach;?>
            </div>
        </div>
    </div>
</div>

<?php get_sidebar( 'qrcode' ); get_footer(); ?>
