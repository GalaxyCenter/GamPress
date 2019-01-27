<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <!-- Basic Page Needs
  ================================================== -->
	<meta charset="utf-8">
	<title><?php wp_title( '&lsaquo;', true, 'right' ); bloginfo( 'name' ); ?></title>
	<meta name="description" content="蒙舞 寂静的天空,民族舞蒙舞 寂静的天空舞蹈视频在线欣赏、免费下载。">
	<meta name="author" content="yego.tech">
	
    <!-- Mobile Specific Metas
  ================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <!-- CSS
  ================================================== -->
  	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/zerogrid.css">
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/style.css">
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/menu.css">
	<!-- Owl Carousel Assets -->
	<link href="<?php echo get_template_directory_uri(); ?>/dist/css/owl.carousel.css" rel="stylesheet">
    <link href="<?php echo get_template_directory_uri(); ?>/dist/css/owl.theme.css" rel="stylesheet">
	<!-- Custom Fonts -->
    <link href="<?php echo get_template_directory_uri(); ?>/dist/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	
	<!--[if lt IE 8]>
       <div style=' clear: both; text-align:center; position: relative;'>
         <a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode">
           <img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." />
        </a>
      </div>
    <![endif]-->
    <!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/dist/js/html5.js"></script>
		<script src="<?php echo get_template_directory_uri(); ?>/dist/js/css3-mediaqueries.js"></script>
	<![endif]-->
    <script src="<?php echo get_template_directory_uri(); ?>/dist/js/jquery-2.1.1.js"></script>
</head>
<body id="wrapper" >
<div class="wrap-body">
	
	<!--////////////////////////////////////Header-->
	<header>
		<div class="wrap-header">
			<div class="zerogrid">
				<div class="row">
					<a href="/" class="logo"><img src="<?php echo get_template_directory_uri(); ?>/dist/images/logo.png" /></a>
				</div>
			</div>
		</div>
    </header>
    <div class="copyrights"></div>
	<!--////////////////////////////////////Menu-->
	<a href="#" class="nav-toggle">点击显示分类</a>
    <nav class="cmn-tile-nav">
		<ul class="clearfix">
            <?php 
            $terms = get_terms( 'cinema', array(
                'orderby'    => 'count',
                'hide_empty' => false,
            ) ); 
            for( $i = 0; $i < count( $terms ); $i ++): ?>
			<li class="colour-<?php echo ($i % 8 + 1);?>"><a href="/videos/category/<?php echo $terms[$i]->name;?>"><?php echo $terms[$i]->name;?></a></li>
            <?php
            endfor;?>
		</ul>
    </nav>
	<!--////////////////////////////////////Container-->