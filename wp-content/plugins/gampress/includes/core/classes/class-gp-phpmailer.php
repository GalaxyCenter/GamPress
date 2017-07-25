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
 * Created by PhpStorm.
 * User: Texel
 * Date: 2016/12/27
 * Time: 22:35
 */
class GP_PHPMailer implements GP_Email_Delivery {

    public function gp_email( GP_Email $email ) {
        static $phpmailer = null;

        if ( $phpmailer === null ) {
            if ( ! class_exists( 'PHPMailer' ) ) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
            }

            $phpmailer = new PHPMailer( true );
        }


        /*
         * Resets.
         */

        $phpmailer->clearAllRecipients();
        $phpmailer->clearAttachments();
        $phpmailer->clearCustomHeaders();
        $phpmailer->clearReplyTos();
        $phpmailer->Sender = '';


        /*
         * Set up.
         */

        $phpmailer->IsMail();
        $phpmailer->CharSet = bp_get_option( 'blog_charset' );


        /*
         * Content.
         */

        $phpmailer->Subject = $email->get_subject( 'replace-tokens' );
        $content_plaintext  = PHPMailer::normalizeBreaks( $email->get_content_plaintext( 'replace-tokens' ) );

        if ( $email->get( 'content_type' ) === 'html' ) {
            $phpmailer->msgHTML( $email->get_template( 'add-content' ) );
            $phpmailer->AltBody = $content_plaintext;

        } else {
            $phpmailer->IsHTML( false );
            $phpmailer->Body = $content_plaintext;
        }

        $recipient = $email->get_from();
        try {
            $phpmailer->SetFrom( $recipient->get_address(), $recipient->get_name(), false );
        } catch ( phpmailerException $e ) {
        }

        $recipient = $email->get_reply_to();
        try {
            $phpmailer->addReplyTo( $recipient->get_address(), $recipient->get_name() );
        } catch ( phpmailerException $e ) {
        }

        $recipients = $email->get_to();
        foreach ( $recipients as $recipient ) {
            try {
                $phpmailer->AddAddress( $recipient->get_address(), $recipient->get_name() );
            } catch ( phpmailerException $e ) {
            }
        }

        $recipients = $email->get_cc();
        foreach ( $recipients as $recipient ) {
            try {
                $phpmailer->AddCc( $recipient->get_address(), $recipient->get_name() );
            } catch ( phpmailerException $e ) {
            }
        }

        $recipients = $email->get_bcc();
        foreach ( $recipients as $recipient ) {
            try {
                $phpmailer->AddBcc( $recipient->get_address(), $recipient->get_name() );
            } catch ( phpmailerException $e ) {
            }
        }

        $headers = $email->get_headers();
        foreach ( $headers as $name => $content ) {
            $phpmailer->AddCustomHeader( $name, $content );
        }


        /**
         * Fires after PHPMailer is initialised.
         *
         * @since 2.5.0
         *
         * @param PHPMailer $phpmailer The PHPMailer instance.
         */
        do_action( 'bp_phpmailer_init', $phpmailer );

        /** This filter is documented in wp-includes/pluggable.php */
        do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

        try {
            return $phpmailer->Send();
        } catch ( phpmailerException $e ) {
            return new WP_Error( $e->getCode(), $e->getMessage(), $email );
        }
    }

    public static function get_hostname() {
        return '';
    }
}