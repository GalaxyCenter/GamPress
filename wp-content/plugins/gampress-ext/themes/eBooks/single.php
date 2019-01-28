<?php get_header();?>
    <div class="content">
        <div class="help-box">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'gampress' ) ); ?>
            <?php endwhile; endif; ?>
        </div>
    </div>
<?php get_footer();?>