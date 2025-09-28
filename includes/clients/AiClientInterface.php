<?php
/**
 * Interface for AI client.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Clients;

use WcAiReviewResponder\Exceptions\AiResponseFailure;

/**
 * Contract for requesting replies from an AI provider.
 */
interface AiClientInterface {
	/**
	 * Get a reply from the AI provider using a prepared prompt.
	 *
	 * @param string $prompt Prepared prompt string.
	 * @return string Raw AI response.
	 * @throws AiResponseFailure When API key is missing or AI returns empty response.
	 */
	public function get( string $prompt ): string;
}
