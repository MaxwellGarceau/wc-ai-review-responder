<?php
/**
 * Reply generator implementation to finalize AI responses.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder;

/**
 * Generates the final sanitized reply string from a raw AI response.
 */
class Reply_Generate implements Generate_Reply_Interface {
    /**
     * Finalize and validate the AI reply string.
     *
     * @param string $ai_response Raw AI response string.
     * @return string Sanitized, validated reply.
     */
    public function generate_reply( string $ai_response ): string {
        $reply = is_string( $ai_response ) ? trim( $ai_response ) : '';
        if ( '' === $reply ) {
            return '';
        }

        // Sanitize for safe admin insertion.
        return wp_kses_post( $reply );
    }
}
