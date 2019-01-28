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
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/main.css?v=<?php gp_version();?>"/>
    <title><?php wp_title( '_', true, 'right' ); bloginfo( 'name' ); ?></title>
</head>
<body>
