<?php
/**
 * Interface for generating AI replies from review context.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder;

interface Generate_Reply_Interface {
	/**
	 * Finalize and validate the AI reply string.
	 *
	 * @param string $ai_response Raw AI response string.
	 * @return string Sanitized, validated reply.
	 */
	public function generate_reply( $ai_response );
}
