<?php
get_header( 'user' );

if ( gp_is_user_profile() ) :
    locate_template( array( 'users/single/profile.php'  ), true );
elseif ( gp_is_user_record() ):
    locate_template( array( 'users/single/record.php'  ), true );
elseif ( gp_is_user_bookmark() ):
    locate_template( array( 'users/single/bookmark.php'  ), true );
elseif ( gp_is_user_recharge() ):
    locate_template( array( 'users/single/recharge.php'  ), true );
elseif ( gp_is_user_msg() ) :
    locate_template( array( 'users/single/msg.php' ), true );
elseif ( gp_is_user_book() ) :
    locate_template( array( 'users/single/book.php' ), true );
else:
    locate_template( array( 'users/single/dashboard.php'  ), true );
endif;

get_footer();?>