<?php
function book_chapters_pager( $book, $page_index, $page_size, $order ) {
    $catalogs = gp_books_catalogs( $book->id, $order, $page_size );
    ?>
    <div class="catalog-switch">
        <?php if ( $page_index != 1 ) : ?>
            <a href="<?php echo gp_get_book_chapters_permalink( $book ) . '/' . ( $page_index - 1 );?>?order=<?php echo $order;?>" class="btn-primary btn-outline btn-disable">上一页</a>
        <?php endif;?>
        <select class="catalog-select" data-order="<?php echo $order;?>">
            <?php
            for( $i = 0; $i< count($catalogs); $i++ ) :?>
            <option value="<?php echo $i + 1;?>" <?php selected( $page_index, ($i+1), true ) ;?>>第<?php echo $catalogs[$i][0] . '-' . $catalogs[$i][1];?>章</option>
            <?php endfor; ?>
        </select>
        <?php if ( $page_index != count($catalogs) ) : ?>
            <a href="<?php echo gp_get_book_chapters_permalink( $book ) . '/' . ( $page_index + 1 );?>?order=<?php echo $order;?>" class="btn-primary btn-outline">下一页</a>
        <?php endif;?>
    </div>
    <?php
}
$from = isset( $_GET['from'] ) ? $_GET['from'] : '';
$book = gp_books_get_current_book();
$page_index = gp_action_variable(0 );
$volumes = gp_books_get_volumes( $book->id );
if ( $page_index == 0)
    $page_index = 1;
$page_size = 100;
$order = isset( $_GET['order'] ) ? $_GET['order'] : 'ASC';

$datas = gp_books_get_chapters( array(
    'book_id'        => $book->id,
    'order'          => $order,
    'status'         => GP_CHAPTER_NORMAL,
    'page'           => $page_index,
    'per_page'       => $page_size ) );
?>
<div class="global-box no-line" id="box_chapters">
    <div class="hd">
        <?php if ( $order == 'DESC' ) : ?>
        <a href="?order=ASC" class="font-blue r" id="sort_btn">正序</a>
        <?php else : ?>
        <a href="?order=DESC" class="font-blue r" id="sort_btn">倒序</a>
        <?php endif;?>
        <h1>目录<em>共<?php echo $datas['total'] - $volumes;?>章</em></h1>
    </div>
    <div class="bd">
        <?php book_chapters_pager( $book, $page_index, $page_size, $order );?>
        <div class="catalog-list">
            <?php
            foreach ( $datas['items'] as $chapter ) :
                if ( $book->chapter_type == GP_CHAPTER_VOLUME && $chapter->parent_id == 0 ) :?>
                <a class="item mt-15"><?php gp_chapter_order( $chapter, true ) ;?> <?php gp_chapter_title( $chapter );?></a>
            <?php else:?>
                <a id="<?php echo $chapter->order + 1 ;?>" href="<?php gp_chapter_permalink( $chapter ) ?>?from=<?php echo $from;?>" class="item">
                    <?php gp_chapter_order( $chapter, true ) ;?> <?php gp_chapter_title( $chapter );?>
                    <?php if ( !gp_get_chapter_is_charge( $chapter ) ) :?><em class="tag">免费</em> <?php endif;?>
                </a>
            <?php
            endif;endforeach;
            ?>
        </div>
        <?php book_chapters_pager( $book, $page_index, $page_size, $order );?>
    </div>
</div>
<script>
    var book_chapters_permalink = '<?php echo gp_book_chapters_permalink( $book ) ;?>';
    var book_permalink = '<?php echo gp_book_permalink( $book );?>';
</script>