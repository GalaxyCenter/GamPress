<?php 
get_header();?>

    <div class="container">

      <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-xs-12 col-sm-9">
          <p class="float-xs-right hidden-sm-up">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="offcanvas">Toggle nav</button>
          </p>
           
          <div class="row">
            <?php 
            $terms = get_terms( 'combine', array(
                            'orderby'    => 'count',
                            'hide_empty' => false,
                            'parent'     => 0
                        ) ); 
            foreach ( $terms as $term ) : ?>
            
            <div class="col-xs-6 col-lg-6">
            
            <h2><a href="<?php gp_combine_term_permalink( $term );?>"><?php echo $term->name; ?></a></h2>
            <?php
            $datas = gp_combines_get_combines_by_term( $term->term_id, 1, 10 );
            foreach( $datas['items'] as $data ): 
                $term = gp_get_combine_term( $data->id, -1 );
            ?>
            
              <h6><a class="alert-info" href="<?php gp_combine_term_permalink( $term );?>">[<?php gp_combine_term( $data->id, -1 ) ;?>]</a> <a href="<?php gp_combine_permalink( $data ); ;?>"><?php gp_combine_title( $data ); ?></a> <label class="alert-danger">[<?php gp_combine_post_date( $data, 'm-d' ); ?>]</label></h6> 
            
            <?php
            endforeach;?>
            
            </div><!--/span-->
            
            <?php
            endforeach;?>
 
          </div><!--/row-->
        </div><!--/span-->
        
        <?php get_sidebar(); ?>
        
      </div><!--/row-->
    
    </div><!--/.container-->
    
    <?php get_sidebar( 'footer' ); ?>

<?php get_footer();?>