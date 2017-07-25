<?php
/**
 * Functions of GamPress's Legacy theme.
 *
 * @since 1.7.0
 *
 * @package GamPress
 * @sugpackage BP_Theme_Compat
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Theme Setup ***************************************************************/

if ( !class_exists( 'GP_Legacy' ) ) :

    class GP_Legacy extends GP_Theme_Compat {
        public function __construct() {
            parent::start();
        }

        protected function setup_globals() {
            $gp            = gampress();
            $this->id      = 'legacy';
            $this->name    = __( 'GamPress Legacy', 'gampress' );
            $this->version = gp_get_version();
            $this->dir     = trailingslashit( $gp->themes_dir . '/gp-legacy' );
            $this->url     = trailingslashit( $gp->themes_url . '/gp-legacy' );
        }

        protected function setup_actions() {

        }

        public function enqueue_styles() {

        }

        public function enqueue_scripts() {

        }

        private function locate_asset_in_stack( $file, $type = 'css', $script_handle = '' ) {

        }

        public function add_nojs_body_class( $classes ) {
            if ( ! in_array( 'no-js', $classes ) )
                $classes[] = 'no-js';

            return array_unique( $classes );
        }

        public function localize_scripts() {
        }

        public function sitewide_notices() {
            // Do not show notices if user is not logged in.
            if ( ! is_user_logged_in() )
                return;

            // Add a class to determine if the admin bar is on or not.
            $class = did_action( 'admin_bar_menu' ) ? 'admin-bar-on' : 'admin-bar-off';

            echo '<div id="sitewide-notice" class="' . $class . '">';
            gp_message_get_notices();
            echo '</div>';
        }

        function secondary_avatars( $action, $activity ) {
            switch ( $activity->component ) {
                case 'groups' :
                case 'friends' :
                    // Only insert avatar if one exists.
                    if ( $secondary_avatar = gp_get_activity_secondary_avatar() ) {
                        $reverse_content = strrev( $action );
                        $position        = strpos( $reverse_content, 'a<' );
                        $action          = substr_replace( $action, $secondary_avatar, -$position - 2, 0 );
                    }
                    break;
            }

            return $action;
        }

        public function theme_compat_page_templates( $templates = array() ) {

            /**
             * Filters whether or not we are looking at a directory to determine if to return early.
             *
             * @since 2.2.0
             *
             * @param bool $value Whether or not we are viewing a directory.
             */
            if ( true === (bool) apply_filters( 'gp_legacy_theme_compat_page_templates_directory_only', ! gp_is_directory() ) ) {
                return $templates;
            }

            // No page ID yet.
            $page_id = 0;

            // Get the WordPress Page ID for the current view.
            foreach ( (array) gampress()->pages as $component => $gp_page ) {

                // Handles the majority of components.
                if ( gp_is_current_component( $component ) ) {
                    $page_id = (int) $gp_page->id;
                }

                // Stop if not on a user page.
                if ( ! gp_is_user() && ! empty( $page_id ) ) {
                    break;
                }

                // The Members component requires an explicit check due to overlapping components.
                if ( gp_is_user() && ( 'members' === $component ) ) {
                    $page_id = (int) $gp_page->id;
                    break;
                }
            }

            // Bail if no directory page set.
            if ( 0 === $page_id ) {
                return $templates;
            }

            // Check for page template.
            $page_template = get_page_template_slug( $page_id );

            // Add it to the beginning of the templates array so it takes precedence
            // over the default hierarchy.
            if ( ! empty( $page_template ) ) {

                /**
                 * Check for existence of template before adding it to template
                 * stack to avoid accidentally including an unintended file.
                 *
                 * @see: https://gampress.trac.wordpress.org/ticket/6190
                 */
                if ( '' !== locate_template( $page_template ) ) {
                    array_unshift( $templates, $page_template );
                }
            }

            return $templates;
        }
    }

    new GP_Legacy();
endif;