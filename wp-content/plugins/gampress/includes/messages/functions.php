<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/6/15
 * Time: 21:07
 */

function gp_messages_new_message( $args = '' ) {

    // Parse the default arguments.
    $r = gp_parse_args( $args, array(
        'sender_id'  => gp_loggedin_user_id(),
        'thread_id'  => false,   // False for a new message, thread id for a reply to a thread.
        'recipients' => array(), // Can be an array of usernames, user_ids or mixed.
        'subject'    => false,
        'content'    => false,
        'post_time'  => gp_core_current_time(),
        'error_type' => 'bool'
    ), 'messages_new_message' );

    // Bail if no sender or no content.
    if ( empty( $r['sender_id'] ) || empty( $r['content'] ) ) {
        if ( 'wp_error' === $r['error_type'] ) {
            if ( empty( $r['sender_id'] ) ) {
                $error_code = 'messages_empty_sender';
                $feedback   = __( 'Your message was not sent. Please use a valid sender.', 'gampress' );
            } else {
                $error_code = 'messages_empty_content';
                $feedback   = __( 'Your message was not sent. Please enter some content.', 'gampress' );
            }

            return new WP_Error( $error_code, $feedback );

        } else {
            return false;
        }
    }

    // Create a new message object.
    $message            = new GP_Messages_Message;
    $message->thread_id = $r['thread_id'];
    $message->sender_id = $r['sender_id'];
    $message->subject   = $r['subject'];
    $message->message   = $r['content'];
    $message->post_time = $r['post_time'];

    // If we have a thread ID...
    if ( ! empty( $r['thread_id'] ) ) {

        // ...use the existing recipients
        $thread              = new GP_Messages_Thread( $r['thread_id'] );
        $message->recipients = $thread->get_recipients();

        // Strip the sender from the recipient list, and unset them if they are
        // not alone. If they are alone, let them talk to themselves.
        if ( isset( $message->recipients[ $r['sender_id'] ] ) && ( count( $message->recipients ) > 1 ) ) {
            unset( $message->recipients[ $r['sender_id'] ] );
        }

        // Set a default reply subject if none was sent.
        if ( empty( $message->subject ) ) {
            $message->subject = sprintf( __( 'Re: %s', 'gampress' ), $thread->messages[0]->subject );
        }

        // ...otherwise use the recipients passed
    } else {

        // Bail if no recipients.
        if ( empty( $r['recipients'] ) ) {
            if ( 'wp_error' === $r['error_type'] ) {
                return new WP_Error( 'message_empty_recipients', __( 'Message could not be sent. Please enter a recipient.', 'gampress' ) );
            } else {
                return false;
            }
        }

        // Set a default subject if none exists.
        if ( empty( $message->subject ) ) {
            $message->subject = __( 'No Subject', 'gampress' );
        }

        // Setup the recipients array.
        $recipient_ids = array();

        // Invalid recipients are added to an array, for future enhancements.
        $invalid_recipients = array();

        // Loop the recipients and convert all usernames to user_ids where needed.
        foreach ( (array) $r['recipients'] as $recipient ) {

            // Trim spaces and skip if empty.
            $recipient = trim( $recipient );
            if ( empty( $recipient ) ) {
                continue;
            }

            // Check user_login / nicename columns first
            // @see http://gampress.trac.wordpress.org/ticket/5151.
            if ( gp_is_username_compatibility_mode() ) {
                $recipient_id = gp_core_get_userid( urldecode( $recipient ) );
            } else {
                $recipient_id = gp_core_get_userid_from_nicename( $recipient );
            }

            // Check against user ID column if no match and if passed recipient is numeric.
            if ( empty( $recipient_id ) && is_numeric( $recipient ) ) {
                if ( gp_core_get_core_userdata( (int) $recipient ) ) {
                    $recipient_id = (int) $recipient;
                }
            }

            // Decide which group to add this recipient to.
            if ( empty( $recipient_id ) ) {
                $invalid_recipients[] = $recipient;
            } else {
                $recipient_ids[] = (int) $recipient_id;
            }
        }

        // Strip the sender from the recipient list, and unset them if they are
        // not alone. If they are alone, let them talk to themselves.
        $self_send = array_search( $r['sender_id'], $recipient_ids );
        if ( ! empty( $self_send ) && ( count( $recipient_ids ) > 1 ) ) {
            unset( $recipient_ids[ $self_send ] );
        }

        // Remove duplicates & bail if no recipients.
        $recipient_ids = array_unique( $recipient_ids );
        if ( empty( $recipient_ids ) ) {
            if ( 'wp_error' === $r['error_type'] ) {
                return new WP_Error( 'message_invalid_recipients', __( 'Message could not be sent because you have entered an invalid username. Please try again.', 'gampress' ) );
            } else {
                return false;
            }
        }

        // Format this to match existing recipients.
        foreach ( (array) $recipient_ids as $i => $recipient_id ) {
            $message->recipients[ $i ]          = new stdClass;
            $message->recipients[ $i ]->user_id = $recipient_id;
        }
    }

    // Bail if message failed to send.
    $send = $message->send();
    if ( false === is_int( $send ) ) {
        if ( 'wp_error' === $r['error_type'] ) {
            if ( is_wp_error( $send ) ) {
                return $send;
            } else {
                return new WP_Error( 'message_generic_error', __( 'Message was not sent. Please try again.', 'gampress' ) );
            }
        }

        return false;
    }

    return $message->thread_id;
}