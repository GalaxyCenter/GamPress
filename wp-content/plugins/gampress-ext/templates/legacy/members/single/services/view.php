<?php /*
<ul class="products">
    <?php
    $args =  array(
        'paged'          => 1,
        'posts_per_page' => 10,
        'author'         => bp_get_displayed_user_username(),
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'order'          => 'DESC',
        'meta_query'     => array()
    );
    $loop = new WP_Query( $args );
    if ( $loop->have_posts() ) : while ( $loop->have_posts() ) : $loop->the_post(); $post = $loop->posts[$loop->current_post];?>

        <?php wc_get_template_part( 'content', 'product' ); ?>

        <?php
    endwhile;
    else:
        ?>
        <div id="message" class="info">
            <p><?php _e( 'Sorry, there was no product found.', 'gampress-ext' ); ?></p>
        </div>
    <?php endif;?>
</ul>
 */ ?>


<?php
$datas = gp_services_get_services(array('paged' => 1, 'per_page' => 10, 'user_id' => bp_displayed_user_id()));
if ( !empty( $datas ) ) : ?>

    <ul class="service-list">

        <?php foreach ( $datas['items'] as $data ) :
            global $service;

            $service = $data; ?>

            <?php gp_get_template_part( 'services/entry' ); ?>

        <?php endforeach; ?>

    </ul>

<?php else: ?>

    <div id="message" class="info">
        <p><?php _e( 'Sorry, there was no product found.', 'gampress-ext' ); ?></p>
    </div>

<?php endif; ?>

