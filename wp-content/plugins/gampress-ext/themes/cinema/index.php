<?php 
get_header();

function gp_videos_side_bar_newest( $args ) { 
    return array( 'page' => 1, 'per_page' => 10 );
}
add_filter( 'gp_videos_side_bar_newest', 'gp_videos_side_bar_newest', 10, 1 );

function gp_videos_side_bar_hot( $args ) { 
    return array( 'page' => 1, 'per_page' => 10 );
}
add_filter( 'gp_videos_side_bar_hot', 'gp_videos_side_bar_hot', 10, 1 );
?> 

	<section id="container" class="index-page">
		<div class="wrap-container zerogrid">
			<!------------------------------------->
			<div class="row">
				<div class="header">
					<h2>热门舞蹈</h2>
				</div>
				<div class="row">
                    <?php
                    $datas = gp_videos_get_videos( array( 'page' => 1, 'per_page' => 5) );?>
				    <div class="most-viewed">
					    <div class="col-2-4">
                        
                            <?php for ( $i = 0; $i < 1; $i++ ) :?>
                            
						    <div class="wrap-col">
							    <div class="zoom-container">
								    <a href="<?php gp_video_permalink( $datas['items'][$i] ); ?>">
									    <span class="zoom-caption">
										    <i class="icon-play fa fa-play"></i>
									    </span>
									    <img src="<?php gp_video_cover( $datas['items'][$i] ) ;?>" />
								    </a>
							    </div>
						    </div>
                            
                            <?php endfor;?>
                            
					    </div>
				    </div>
				    <div class="extra">
					    <div class="col-1-4">
						    <div class="wrap-col">
                            
                                <?php for ( $i = 1; $i < 3; $i++ ) :?>
                                
							    <div class="zoom-container">
								    <a href="<?php gp_video_permalink( $datas['items'][$i] ); ?>">
									    <span class="zoom-caption">
										    <i class="icon-play fa fa-play"></i>
									    </span>
									    <img src="<?php gp_video_cover( $datas['items'][$i] ) ;?>" />
								    </a>
							    </div>
                                
							    <?php endfor;?>
                                
						    </div>
					    </div>
					    <div class="col-1-4">
						    <div class="wrap-col">
                            
                                <?php for ( $i = 3; $i < 5; $i++ ) :?>
                                
							    <div class="zoom-container">
								    <a href="<?php gp_video_permalink( $datas['items'][$i] ); ?>">
									    <span class="zoom-caption">
										    <i class="icon-play fa fa-play"></i>
									    </span>
									    <img src="<?php gp_video_cover( $datas['items'][$i] ) ;?>" />
								    </a>
							    </div>
                                
							    <?php endfor;?>
                                
						    </div>
					    </div>
				    </div>
				</div>
			</div>
			<div class="row">
				<div id="main-content" class="col-2-3">
					<div class="wrap-content">
                    
                        <?php 
                        $terms = get_terms( 'cinema', array(
                            'orderby'    => 'count',
                            'hide_empty' => false,
                        ) ); 
                        
                        foreach ( $terms as $term ) : ?>
                        
						<section class="all">
							<div class="header">
								<h2><?php echo $term->name; ?>舞蹈</h2>
							</div>
							<div class="row">
								<?php
                                $datas = gp_videos_get_videos( array( 'page' => 1, 'per_page' => 4, 'term_id' => $term->term_id ) );
                                
                                foreach( $datas['items'] as $data ): ?>
                                
							    <div class="col-1-4">
								    <div class="wrap-col">
									    <div class="wrap-vid">
										    <div class="zoom-container">
											    <a href="<?php gp_video_permalink( $data ); ?>">
												    <span class="zoom-caption">
													    <i class="icon-play fa fa-play"></i>
												    </span>
												    <img src="<?php gp_video_cover( $data ) ;?>" />
											    </a>
										    </div>
										    <h3 class="vid-name"><a href="<?php gp_video_permalink( $data ); ?>"><?php gp_video_title( $data ) ;?></a></h3>
									    </div>
								    </div>
							    </div>
                                
							    <?php
                                endforeach;?>
                            
							</div>
						</section>
						
                        <?php
                        endforeach;?>
						 
					</div>
				</div>
                
				<?php get_sidebar(); ?>
                
			</div>
		</div>
	</section>
    <!-- Slider -->
	<script src="<?php echo get_template_directory_uri(); ?>/dist/js/demo.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/dist/js/classie.js"></script>
	<!-- Carousel -->
	<script src="<?php echo get_template_directory_uri(); ?>/dist/js/owl.carousel.js"></script>
    <script>
    $(document).ready(function() {

      $("#owl-demo-1").owlCarousel({
        items : 4,
        lazyLoad : true,
        navigation : true
      });
	  $("#owl-demo-2").owlCarousel({
        items : 4,
        lazyLoad : true,
        navigation : true
      });
	  $("#owl-demo-3").owlCarousel({
        items : 4,
        lazyLoad : true,
        navigation : true
      });
    });
    </script>
    
<?php get_footer();?>