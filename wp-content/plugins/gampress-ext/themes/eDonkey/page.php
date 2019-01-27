<?php get_header(); ?>

<div class="container">


<div id="content">
    <div class="row row-offcanvas row-offcanvas-right">
	    <div class="col-xs-12 col-sm-9">
		    
		    <div class="page" id="blog-page" role="main">
			    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			    <h2 class="pagetitle">
				    <?php the_title(); ?>
			    </h2>
			    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				    <div class="entry">
					    <?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'gampress' ) ); ?>
				    </div>
			    </div>
			    <?php endwhile; endif; ?>
		    </div>
		    <!-- .page -->
    		
	    </div>
        
        <?php get_sidebar(); ?>
        
	    <!-- .padder --> 
    </div>
</div>
<!-- #content -->

</div>

<?php get_footer(); ?>
