<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php wp_title( '_', true, 'right' ); bloginfo( 'name' ); ?>">
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
<header class="head-m">
    <a href="/book_tag" class="r" title="搜索"><i class="icon-search"></i></a>
    <?php if ( is_user_logged_in() ) : ?>
    <a href="<?php echo gp_loggedin_user_domain();?>" class="l user" id="user_tip" title=""><i class="icon-user"></i></a>
    <?php else : ?>
    <a href="javascript:;" _rel="/login?redirect=<?php echo $_SERVER['REQUEST_URI'] ;?>" class="l login-msg" title=""><i class="icon-user"></i></a>
    <?php endif;?>
    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/logo.png" alt="阿呆熊" class="logo"/>
</header>
<footer class="nav-bar">
    <a href="/" class="item <?php echo is_home() ? 'active' : ''; ?>"><i class="icon-nav-1"></i><p>首页</p></a>
    <a href="/book_rank" class="item <?php echo is_page( 'book_rank' ) ? 'active' : ''; ?>"><i class="icon-nav-2"></i><p>排行</p></a>
    <a href="/books" class="item <?php echo is_page( 'books' ) ? 'active' : ''; ?>"><i class="icon-nav-3"></i><p>书库</p></a>
    <a href="/book_free" class="item <?php echo is_page( 'book_free' ) ? 'active' : ''; ?>"><i class="icon-nav-4"></i><p>免费</p></a>
</footer>
<?php do_action( 'gp_header' );?>