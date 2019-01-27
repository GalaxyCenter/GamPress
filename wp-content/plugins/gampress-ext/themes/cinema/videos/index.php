<?php 
get_header();

$gp = gampress();
$term_name = $gp->videos->current_item;
$term = get_term_by( 'name', $term_name, 'cinema' );
if ( !empty( $term ) ) {
    $term_id = $term->term_id;
} else {
    $term_id = 0;
    $term_name = '全部';
}
?> 

    <!--////////////////////////////////////Container-->
	<section id="container" class="index-page">
		<div class="wrap-container zerogrid">
			<div class="row">
				<div id="main-content" class="col-2-3">
					<section class="all">
						<div class="header">
							<h2><?php echo $term_name; ?>舞蹈视频</h2>
						</div>
						<div class="row">
                        
                            <?php
                            $datas = gp_videos_get_videos( array( 'page' => $gp->videos->current_page, 'per_page' => 32, 'term_id' => $term_id ) );
                            
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
					<div class="navigation">
                        <?php
                        $url_patter = "/videos/category/${term_name}/%d";
                        echo par_pagenavi( $gp->videos->current_page, 20, (int) $datas['total'], $url_patter ); ?>
					</div>
				</div>
				 
                <?php get_sidebar(); ?>
                
			</div>
		</div>
	</section>
    
<?php get_footer();?>