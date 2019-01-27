
page
<?php

/**
 * Teazaar - Users Home
 *
 * @package Teazaar ²è
 * @subpackage teazaar-v2
 */
?>
<?php get_header(); ?>
<!-- main -->
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            
	<?php the_title('<h1>','</h1>'); ?>
	
    <?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'buddypress' ) ); ?>
    <?php wp_link_pages( array( 'before' => '<div class="page-link"><p>' . __( 'Pages: ', 'buddypress' ), 'after' => '</p></div>', 'next_or_number' => 'number' ) ); ?>
    
<?php endwhile; endif; ?>
<!-- end main -->
<?php get_footer(); ?>