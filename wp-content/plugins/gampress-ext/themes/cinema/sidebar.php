                <div id="sidebar" class="col-1-3">
					<form id="form-container" action="">
						<!--<input type="submit" id="searchsubmit" value="" />-->
						<a class="search-submit-button" href="javascript:void(0)">
							<i class="fa fa-search"></i>
						</a>
						<div id="searchtext">
							<input type="text" id="s" name="s" placeholder="我要搜舞蹈">
						</div>
					</form>
					<!---- Start Widget ---->
					<div class="widget wid-post">
						<div class="wid-header">
							<h5>最新舞蹈</h5>
						</div>
						<div class="wid-content">
                        
                            <?php
                            $datas = gp_videos_get_videos( apply_filters( 'gp_videos_side_bar_newest', array( 'page' => 1, 'per_page' => 3 ) ) );
                            
                            foreach( $datas['items'] as $data ): ?>
                            
							<div class="wrap-vid">
								<div class="zoom-container">
									<a href="<?php gp_video_permalink( $data ); ?>">
										<span class="zoom-caption">
											<i class="icon-play fa fa-play"></i>
										</span>
										<img src="<?php gp_video_cover( $data );?>" />
									</a>
								</div>
								<div class="wrapper">
									<h5 class="vid-name"><a href="<?php gp_video_permalink( $data ); ?>"><?php gp_video_title( $data ); ?></a></h5>
								</div>
							</div>
                            
                            <?php endforeach;?>
                            
						</div>
					</div>
					<!---- Start Widget ---->
					<div class="widget wid-news">
						<div class="wid-header">
							<h5>最热舞蹈</h5>
						</div>
						<div class="wid-content">
                        
                            <?php
                            $datas = gp_videos_get_videos( apply_filters( 'gp_videos_side_bar_hot', array( 'page' => 1, 'per_page' => 3 ) ) );
                            
                            foreach( $datas['items'] as $data ): ?>
                            
							<div class="row">
								<div class="wrap-vid">
									<div class="zoom-container">
										<a href="<?php gp_video_permalink( $data ); ?>">
											<span class="zoom-caption">
												<i class="icon-play fa fa-play"></i>
											</span>
											<img src="<?php gp_video_cover( $data );?>" />
										</a>
									</div>
									<h3 class="vid-name"><?php gp_video_title( $data ); ?></h3>
								</div>
							</div>
                            
                            <?php endforeach;?>
                            
						</div>
					</div>
				</div>