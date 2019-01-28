<?php
// Profile Edit
if ( gp_is_current_action( 'edit' ) )
    locate_template( array( 'users/single/profile/edit.php' ), true );

?>