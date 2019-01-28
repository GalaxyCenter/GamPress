<?php get_header( 'book' ); ?>

    <div class="content" id="book">

        <?php
        $action = gp_current_action();

        if ( $action == '') : gp_get_template_part( 'books/single/dashboard');

        elseif ( $action == 'catalog' ) : gp_get_template_part( 'books/single/catalog');

        elseif ( $action == 'activities' ) : gp_get_template_part( 'books/single/activities');

        elseif ( $action == 'pub-activity' ) : gp_get_template_part( 'books/single/pub-activity');

        else :

        endif; ?>

    </div>

<?php get_sidebar( 'qrcode' ); get_footer(); ?>