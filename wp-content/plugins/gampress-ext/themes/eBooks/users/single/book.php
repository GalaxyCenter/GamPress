<script src="<?php echo get_template_directory_uri(); ?>/dist/js/vue/vue.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/dist/js/vue/plugins.min.js"></script>
<?php
// Profile Edit
if ( gp_is_current_action( 'list' ) )
    locate_template( array( 'users/single/book/list.php' ), true );
else if ( gp_is_current_action( 'import' ) )
    locate_template( array( 'users/single/book/import.php' ), true );
?>