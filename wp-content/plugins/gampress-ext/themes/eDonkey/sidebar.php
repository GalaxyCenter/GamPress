<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
  <div class="list-group">
    <a class="list-group-item active">本周热点</a>
    <?php
    $datas = gp_combines_get_combines( array( 'page' => 1, 'per_page' => 24 ) );
    foreach( $datas['items'] as $data ): ?>
    <a href="<?php gp_combine_permalink( $data ); ;?>" class="list-group-item"><?php gp_combine_title( $data ); ?></a>
    <?php
    endforeach;?>
  </div>
</div><!--/span-->