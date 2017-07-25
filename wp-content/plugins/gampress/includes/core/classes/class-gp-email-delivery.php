<?php
/**
 * Core component classes.
 *
 * @package GamPress
 * @subpackage Core
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Email delivery implementation base class.
 *
 * When implementing support for an email delivery service into GamPress,
 * you are required to create a class that implements this interface.
 *
 * @since 2.5.0
 */
interface GP_Email_Delivery {

    /**
     * Send email(s).
     *
     * @since 2.5.0
     *
     * @param GP_Email $email Email to send.
     * @return bool|WP_Error Returns true if email send, else a descriptive WP_Error.
     */
    public function gp_email( GP_Email $email );
}
