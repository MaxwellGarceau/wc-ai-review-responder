<?php
/**
 * Reply generator implementation to finalize AI responses.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder;

class Reply_Generate implements Generate_Reply_Interface {
    /**
     * {@inheritDoc}
     */
    public function generate_reply( $ai_response ) {
        $reply = is_string( $ai_response ) ? trim( $ai_response ) : '';
        if ( '' === $reply ) {
            return '';
        }

        // Sanitize for safe admin insertion.
        return wp_kses_post( $reply );
    }
}


