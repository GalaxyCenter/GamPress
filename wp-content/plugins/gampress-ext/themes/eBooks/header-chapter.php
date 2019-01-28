<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php $book = gp_books_get_current_book(); gp_book_title( $book ) ;?>">
    <meta name="keywords" content="<?php gp_keywords(); ?>" />
    <meta name="description" itemprop="description" content="<?php gp_description(); ?>" />
    <script src="<?php echo get_template_directory_uri(); ?>/dist/js/pxTorem.js"></script>
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/dist/images/apple-touch-icon-chapter.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/dist/images/apple-touch-icon-chapter.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="<?php echo get_template_directory_uri(); ?>/dist/images/apple-touch-icon-chapter.png"/>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/main.css?v=<?php gp_version();?>"/>
    <title><?php wp_title( '_', true, 'right' ); bloginfo( 'name' ); ?></title>
</head>
<?php
$mode = isset( $_COOKIE['_mode'] ) ? $_COOKIE['_mode'] : 'day';
$color = isset( $_COOKIE['_color'] ) ? $_COOKIE['_color'] : '1';
$colors = array( 'skin_brown', 'skin_cyan', 'skin_sky_blue', 'skin_white' );
$body_id = false;

if( $mode === 'night' ){
    $body_id = 'skin_night';
} else{
    $body_id = $colors[$color - 1];
}
?>
<body id="<?php echo $body_id;?>">
<?php do_action( 'gp_header' );?>