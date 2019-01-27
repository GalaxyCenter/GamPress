<!DOCTYPE html>
<html lang="zh">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="<?php gp_keywords(); ?>" />
    <meta name="description" content="<?php gp_description(); ?>" />

    <link rel="icon" href="/favicon.ico">

    <title><?php wp_title( '_', true, 'right' ); bloginfo( 'name' ); ?></title>
    
    <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js" integrity="sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY" crossorigin="anonymous"></script>
    
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css" integrity="sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi" crossorigin="anonymous">
    
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/dist/css/style.css">

  </head>

  <body>
    <nav class="navbar navbar-dark navbar-fixed-top bg-inverse">
      <button type="button" class="navbar-toggler hidden-sm-up" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" aria-label="Toggle navigation"></button>
      <a class="navbar-brand" href="/">资源百科</a>
      <div id="navbar">
        <nav class="nav navbar-nav float-xs-left">
          <?php 
            $terms = get_terms( 'combine', array(
                            'orderby'    => 'count',
                            'hide_empty' => false,
                            'parent'     => 0
                        ) ); 
            foreach ( $terms as $term ) : ?>
          <a class="nav-item nav-link" href="<?php gp_combine_term_permalink( $term );?>"><?php echo $term->name; ?></a>
          <?php
          endforeach;?>
        </nav>
          <!--
        <form class="float-xs-right" target="_blank" action="http://www.google.com/search">
            <input type="hidden" name="domains" value="http://outwiki.com" />
            <input id="bdcsMain" type="text" class="form-control" placeholder="搜索">
        </form>-->
          <div class="float-xs-right" style="width:400px">
          <script>
              (function() {
                  var cx = '011205983680774531919:g-ua9sq93nm';
                  var gcse = document.createElement('script');
                  gcse.type = 'text/javascript';
                  gcse.async = true;
                  gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
                  var s = document.getElementsByTagName('script')[0];
                  s.parentNode.insertBefore(gcse, s);
              })();
          </script>
          <gcse:search></gcse:search>
          </div>
      </div>
    </nav>