<div class="">
    <h3>您可能喜欢</h3>
<?php
global $term_id;
$page = (int) date("i");
$datas = gp_combines_get_combines_by_term( $term_id, $page, 10, 'asc' );
foreach( $datas['items'] as $data ):
    $term = gp_get_combine_term( $data->id, -1 );
    ?>

    <h6><a class="alert-info" href="<?php gp_combine_term_permalink( $term );?>">[<?php gp_combine_term( $data->id, -1 ) ;?>]</a> <a href="<?php gp_combine_permalink( $data ); ;?>"><?php gp_combine_title( $data ); ?></a></h6>

    <?php


endforeach;?>
</div>