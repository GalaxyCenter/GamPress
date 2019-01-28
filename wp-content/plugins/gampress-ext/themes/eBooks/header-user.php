<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php gp_keywords(); ?>">
    <meta name="keywords" content="<?php gp_keywords(); ?>" />
    <meta name="description" itemprop="description" content="<?php gp_description(); ?>" />
    <script src="<?php echo get_template_directory_uri(); ?>/dist/js/pxTorem.js"></script>
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/dist/images/apple-touch-icon.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/dist/images/apple-touch-icon.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="<?php echo get_template_directory_uri(); ?>/dist/images/apple-touch-icon.png"/>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/main.css?v=<?php gp_version();?>"/>
    <title><?php wp_title( '_', true, 'right' ); bloginfo( 'name' ); ?></title>
</head>
<body>
<header class="head-s">
    <div class="r">
        <a href="/book_tag" class="l" title="搜索"><i class="icon-search"></i></a>
        <a href="/" class="l" title="首页"><i class="icon-home"></i></a>
    </div>
    <?php
        if (empty(gp_current_component())) {
            $previous   = '/';
        } else {
            $refer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
            if ( strstr( $refer, 'from' ) )
                $previous = '/';
            else
                $previous = gp_loggedin_user_domain();
        }
    ?>
    <a href="<?php echo $previous;?>" class="l"><i class="icon-pre"></i></a>
    <h3>个人中心</h3>
</header>
<?php do_action( 'gp_header' );?>