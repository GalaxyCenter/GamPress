<?php 
get_header();
?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                            <div class="tt2 f-cf">
                                <h2><?php the_title();?></h2></div>
                            <div class="entry">
                                <?php the_content(); ?>
                            </div>
                            <?php endwhile; endif; ?>
<?php get_footer();?>