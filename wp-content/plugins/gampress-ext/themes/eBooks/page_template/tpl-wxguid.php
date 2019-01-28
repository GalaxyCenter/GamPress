<?php
/*
Template Name: Wechat_Guid
*/?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="阿呆熊">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/main.css"/>
    <title>阿呆熊</title>
</head>
<body>
<div class="content">
    <div class="guide-info">
        <i class="icon-wx"></i>
        <h3>微信登录提示</h3>
        <p>亿万用户已选择微信账号登录</p>
    </div>
    <div class="guide-bd">
        <img class="qrcode" src="<?php echo get_template_directory_uri(); ?>/dist/images/fwh-qrcode.jpeg">
        <p>扫描二维码关注我们，立享免费看书</p>
    </div>
    <div class="guide-txt">
        <h3>您也可以关注阿呆熊微信公众号登录：</h3>
        <p>1. 打开微信，搜索“<em class="font-orange">阿呆熊阅读</em>”，关注公众号。</p>
        <p>2. 点击公众号下方菜单栏“<em class="font-orange">最近阅读</em>”，即可继续阅读当前阅读的小说。</p>
        <p>3. 点击页面右上方的“<em class="font-orange">登录</em>”按钮，选择“<em class="font-orange">微信</em>”账号登录。</p>
    </div>
</div>
<script src="<?php echo get_template_directory_uri(); ?>/dist/js/pxTorem.js"></script>
</body>
</html>