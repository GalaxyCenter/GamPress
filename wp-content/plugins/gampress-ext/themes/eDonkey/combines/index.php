<?php 
get_header();

$gp = gampress();
$term_name = $gp->combines->current_item;
$term = get_term_by( 'name', $term_name, 'combine' );
if ( !empty( $term ) ) {
    $term_id = $term->term_id;
} else {
    $term_id = 0;
    $term_name = '全部'; 
}
?> 

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <?php 
            $terms = get_terms( 'combine', array(
                            'orderby'    => 'count',
                            'hide_empty' => false,
                            'parent'     => $term_id
                        ) );
            if ( empty( $terms ) ) {
                $terms = get_terms( 'combine', array(
                            'orderby'    => 'count',
                            'hide_empty' => false,
                            'parent'     => $term->parent
                            ) );
            } else {
                echo "<li class='active'><a href=''>{$term_name}</a></li>";
            }
            
            foreach( $terms as $term ) : ?>
            
            <li class="<?php active( $term->name, $term_name, true);?>"><a href="<?php gp_combine_term_permalink( $term );?>"><?php echo $term->name;?></a></li>
            <?php
            endforeach;?>
          </ul>
        </div>
        <div class="col-sm-9 offset-sm-3 col-md-10 offset-md-2 main">
          <h2><?php echo $term_name;?>资源列表</h2>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>标题</th>
                  <th>发布日期</th>
                  <th>大小</th>
                  <th>类型</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $datas = gp_combines_get_combines_by_term( $term_id, $gp->combines->current_page, 20 );
                foreach( $datas['items'] as $data ): 
                    $term = gp_get_combine_term( $data->id, -1 );
                ?>
                <tr>
                  <td><a href="<?php gp_combine_permalink( $data ); ;?>"><?php gp_combine_title( $data ); ?></a></td>
                  <td><?php gp_combine_post_date( $data, 'Y-m-d' ); ?></td>
                  <td><?php gp_combine_size( $data );?></td>
                  <td><?php gp_combine_type( $data );?></td>
                </tr>
                <?php
                endforeach;?>
              </tbody>
            </table>
            
            <?php
            $url_patter = "/combines/category/${term_name}/%d";
            echo par_pagenavi( $gp->combines->current_page, 20, (int) $datas['total'], $url_patter ); ?>
                        
          </div>
        <hr>
        <?php get_sidebar( 'footer' ); ?>
        
        
        </div>
        
      </div>
    </div>

<?php get_footer();?>