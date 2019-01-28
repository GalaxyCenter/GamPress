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
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/main.css"/>
    <title><?php wp_title( '_', true, 'right' ); bloginfo( 'name' ); ?></title>
</head>
<body>
<header class="head-s">
    <div class="r">
        <a href="/book_tag" class="l" title="搜索"><i class="icon-search"></i></a>
        <a href="/" class="l" title="首页"><i class="icon-home"></i></a>
    </div>
    <a href="/" class="l"><i class="icon-pre"></i></a>
    <h3>帮助文档</h3>
</header>

<div class="content">
    <div class="help-box">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'gampress' ) ); ?>
    <?php endwhile; endif; ?>
    </div>
</div>
<?php get_footer();?>