<?php 
get_header();

$video = gp_get_current_video();
?> 

    <!--////////////////////////////////////Container-->
	<section id="container" class="index-page">
		<div class="wrap-container zerogrid">
			<div class="row">
				<div id="main-content" class="col-2-3">
                    <h1 class="vid-name"><?php gp_video_title( $video ); ?></h1>
					<div class="info">
						<span><i class="fa fa-heart"></i>1,200</span>
					</div>
					<p> </p>
                    
					<div class="wrap-vid">
						
                        <?php gp_video_raw_code( $video );?>
                        
					</div>
					
					<div class="tags">
						<a href="#">热舞</a>
						<a href="#">广场舞</a>
						<a href="#">街舞</a>
						<a href="#">民族舞</a>
						<a href="#">现代舞</a>
					</div>
                    
					<section class="vid-related">
						<div class="header">
							<h2>热门<?php gp_video_term_name( $video->id );?>舞推荐</h2>
						</div>
						<div class="row"><!--Start Box-->
                            <?php
                            $datas = gp_videos_get_videos( array( 'page' => 1, 'per_page' => 20 ) );
                            
                            foreach( $datas['items'] as $data ): ?>
							<div class="item wrap-vid">
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
							<?php
                            endforeach;?>
						</div>
					</section>
				</div>
				
                <?php get_sidebar(); ?>
                
			</div>
		</div>
	</section>
    <!-- Carousel -->
	<script src="<?php echo get_template_directory_uri(); ?>/dist/js/owl.carousel.js"></script>
    <script>
    $(document).ready(function() {
 
	  $("#owl-demo-2").owlCarousel({
        items : 4,
        lazyLoad : true,
        navigation : true
      });

    });
    </script>
<?php get_footer();?>