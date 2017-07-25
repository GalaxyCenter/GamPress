<div id="buddypress" >

    <?php if(!bp_is_current_component('events') || ( bp_is_current_component('events') && 'profile' == bp_current_action() ) ): //show if not Events Manager page or My Profile of Events ?>

        <?php do_action( 'bp_before_member_home_content' ); ?>

        <div id="item-header" role="complementary">

            <?php bp_get_template_part( 'members/single/member-header' ) ?>

        </div><!-- #item-header -->

    <?php endif; ?>

    <div class="<?php echo ( boss_get_option( 'boss_layout_style' ) == 'boxed' && is_active_sidebar( 'profile' ) && bp_is_user() ) ? 'right-sidebar' : 'full-width'; ?>">
        <div id="item-main-content">
            <?php if(!bp_is_current_component('events') || ( bp_is_current_component('events') && 'profile' == bp_current_action() ) ): //show if not Events Manager page or My Profile of Events ?>
                <div id="item-nav">
                    <div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
                        <ul id="nav-bar-filter">

                            <?php bp_get_displayed_user_nav(); ?>

                            <?php do_action( 'bp_member_options_nav' ); ?>

                        </ul>
                    </div>
                </div><!-- #item-nav -->
            <?php endif; ?>

            <div id="wizard-content" role="main">

                <div id="message" class="info">
                    <p>
                        <a href="<?php echo trailingslashit( bp_loggedin_user_domain() . bp_get_profile_slug() . '/edit/group/1' ); ?>">
                            <?php _e( 'Welcome! now click me to improve your profile.', 'gampress' ); ?>
                        </a>
                    </p>

                </div>

            </div><!-- #item-body -->

            <?php do_action( 'bp_after_member_home_content' ); ?>

        </div>
        <!-- /.item-main-content -->
        <?php
        // Boxed layout sidebar
        if ( boss_get_option( 'boss_layout_style' ) == 'boxed' ) {
            get_sidebar( 'buddypress' );
        }
        ?>
    </div>

</div><!-- #buddypress -->