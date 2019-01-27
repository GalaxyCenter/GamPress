<?php 
get_header();

$combine = gp_get_current_combine();

$gp = gampress();
$term_name = gp_get_combine_term_name( $combine->id );
$term = get_term_by( 'name', $term_name, 'combine' );

global $term_id;
if ( !empty( $term ) ) {
    $term_id = $term->term_id;
} else {
    $term_id = 0;
    $term_name = '全部';
}


?> 
    <style>
    .main [class*="col-"] {
      padding-top: 1rem;
      padding-bottom: 1rem;
      background-color: rgba(86,61,124,.15);
      border: 1px solid rgba(86,61,124,.2);
    }
    </style>
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
          <h2><?php echo $term_name;?><?php gp_combine_title( $combine ); ?></h2>
        
        <hr>
        
            <div class="row">
              <div class="col-xs-4">发布日期：<?php gp_combine_post_date( $combine ) ;?></div>
              <div class="col-xs-4">大小：<?php gp_combine_size( $combine ) ;?></div>
              <div class="col-xs-4">标签：<?php gp_combine_tags( $combine ) ;?></div>
            </div>
            
            
            <!-- desc start -->
            <p id="c_desc">
                <?php if ( empty( $combine->links->ed2ks ) && empty( $combine->links->magnets ) ) : ?>

                <p>由于版权原因，本站只提供内容描述，不提供下载链接。：）</p>
                <p>Because of copyright reasons, the site only provides content description, do not provide download link. :)</p>

                <?php endif; ?>

            <?php gp_combine_description( $combine );?>
            
            </p>
            
            <!-- desc end -->
            
            <hr>

            <?php if ( empty( $combine->links->ed2ks ) && empty( $combine->links->magnets ) ) : ?>

                <p>由于版权原因，本站只提供内容描述，不提供下载链接。：）</p>
                <p>Because of copyright reasons, the site only provides content description, do not provide download link. :)</p>

            <?php else: ?>
                <?php if ( !empty( $combine->links->ed2ks ) ) : ?>
                <h3>ed2k eDonkey2000 电驴 迅雷下载链接</h3>
                <?php
                    gp_combine_ed2k_table( $combine->links->ed2ks );?>

                <?php endif;?>

                <?php if ( !empty( $combine->links->magnets ) ) : ?>
                <h3>magnet 磁力链 种子 迅雷下载链接</h3>
                <?php
                    gp_combine_magnet_table( $combine->links->magnets );?>

                <?php endif;?>

            <?php endif;?>

            <hr>

            <?php get_sidebar( 'combine' ); ?>

            <hr>
            <?php get_sidebar( 'footer' ); ?>
        
        </div>
        
      </div>
    
    </div>

<script>
//var c_desc = jQuery('#c_desc');
//var temp = c_desc.html().replace(/<img.*?src="(.*?)".*?[/]?>/g, ' $1 ');

$('#cb_ed2k').click(function(){
    if(this.checked){
        jQuery("input[name='cb_ed2k']").each(function(){this.checked=true;});
    }else{
        jQuery("input[name='cb_ed2k']").each(function(){this.checked=false;});
    }
});
</script>
<?php get_footer();?>