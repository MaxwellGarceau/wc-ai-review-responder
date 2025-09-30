<?php
/**
 * Localization translations for WC AI Review Responder.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Localization;

/**
 * Class containing all translatable strings for the plugin.
 */
class Localizations {

	/**
	 * Get all JavaScript localization strings.
	 *
	 * @return array Array of localized strings for JavaScript.
	 * @since 1.0.0
	 */
	public function get_js_strings(): array {
		return array(
			'selectPromptOptions'      => __( 'Select Prompt Options', 'wc-ai-review-responder' ),
			'chooseTemplateAndMood'    => __( 'Choose a template and mood to guide the AI\'s response.', 'wc-ai-review-responder' ),
			'aiSuggestsOptions'        => __( 'Based on the review, the AI suggests these options for the best response.', 'wc-ai-review-responder' ),
			'aiSuggestionsFailed'      => __( 'AI suggestions failed to load. Please make a manual selection.', 'wc-ai-review-responder' ),
			'template'                 => __( 'Template:', 'wc-ai-review-responder' ),
			'mood'                     => __( 'Mood:', 'wc-ai-review-responder' ),
			'cancel'                   => __( 'Cancel', 'wc-ai-review-responder' ),
			'generate'                 => __( 'Generate', 'wc-ai-review-responder' ),
			'error'                    => __( 'Error', 'wc-ai-review-responder' ),
			'ok'                       => __( 'OK', 'wc-ai-review-responder' ),
			'generatingAiResponse'     => __( 'Generating AI Response', 'wc-ai-review-responder' ),
			'pleaseWaitGenerating'     => __( 'Please wait while we generate a personalized response to this review...', 'wc-ai-review-responder' ),
			'gettingSuggestions'       => __( 'Getting suggestions...', 'wc-ai-review-responder' ),
			'missingDataAttributes'    => __( 'Missing required data attributes. Please refresh the page and try again.', 'wc-ai-review-responder' ),
			'configurationError'       => __( 'Configuration Error', 'wc-ai-review-responder' ),
			'couldNotFindTextarea'     => __( 'Could not find the reply textarea. Please make sure the reply box is open and try again.', 'wc-ai-review-responder' ),
			'interfaceError'           => __( 'Interface Error', 'wc-ai-review-responder' ),
			'serverReturnedError'      => __( 'The server returned an error response.', 'wc-ai-review-responder' ),
			'serverError'              => __( 'Server Error', 'wc-ai-review-responder' ),
			'failedToGenerateResponse' => __( 'Failed to generate AI response', 'wc-ai-review-responder' ),
			'somethingWentWrong'       => __( 'Something went wrong', 'wc-ai-review-responder' ),
			'unexpectedError'          => __( 'An unexpected error occurred.', 'wc-ai-review-responder' ),
		);
	}

	/**
	 * Get all PHP localization strings.
	 *
	 * @return array Array of localized strings for PHP.
	 * @since 1.0.0
	 */
	public function get_php_strings(): array {
		return array(
			'generateAiResponse'     => __( 'Generate AI Response', 'wc-ai-review-responder' ),
			/* translators: %s WC download URL link. */
			'wooCommerceRequired'    => __( 'Wc Ai Review Responder requires WooCommerce to be installed and active. You can download %s here.', 'wc-ai-review-responder' ),
			'cloningForbidden'       => __( 'Cloning is forbidden.', 'wc-ai-review-responder' ),
			'unserializingForbidden' => __( 'Unserializing instances of this class is forbidden.', 'wc-ai-review-responder' ),
			'reviewMissingComment'   => __( 'Review is missing a comment.', 'wc-ai-review-responder' ),
			'reviewMissingRating'    => __( 'Review is missing a rating.', 'wc-ai-review-responder' ),
			'ratingInvalidRange'     => __( 'Rating must be between 1 and 5.', 'wc-ai-review-responder' ),
		);
	}

	/**
	 * Get all CLI localization strings.
	 *
	 * @return array Array of localized strings for CLI.
	 * @since 1.0.0
	 */
	public function get_cli_strings(): array {
		return array(
			'missingCommentId'             => __( 'Missing or invalid comment_id.', 'wc-ai-review-responder' ),
			'step1PrepareData'             => __( 'Step 1: Prepare Review Data', 'wc-ai-review-responder' ),
			'fetchingReviewContext'        => __( '- Fetching review context...', 'wc-ai-review-responder' ),
			'reviewContextData'            => __( '  Review context data: ', 'wc-ai-review-responder' ),
			'validatingReview'             => __( '- Validating review for AI processing...', 'wc-ai-review-responder' ),
			'sanitizingInput'              => __( '- Sanitizing input for AI processing...', 'wc-ai-review-responder' ),
			'sanitizedContextData'         => __( '  Sanitized context data: ', 'wc-ai-review-responder' ),
			'reviewDataPrepared'           => __( '✓ Review data prepared for all AI operations.', 'wc-ai-review-responder' ),
			'step2GetSuggestions'          => __( 'Step 2: Get AI Suggestions', 'wc-ai-review-responder' ),
			'buildingSuggestionPrompt'     => __( '- Building suggestion prompt...', 'wc-ai-review-responder' ),
			'generatedSuggestionPrompt'    => __( '  Generated suggestion prompt: ', 'wc-ai-review-responder' ),
			'sendingSuggestionRequest'     => __( '- Sending suggestion request to AI...', 'wc-ai-review-responder' ),
			'suggestedMood'                => __( 'The suggested mood.', 'wc-ai-review-responder' ),
			'suggestedTemplate'            => __( 'The suggested template.', 'wc-ai-review-responder' ),
			'validatingSuggestionResponse' => __( '- Validating suggestion response...', 'wc-ai-review-responder' ),
			'rawAiResponse'                => __( '  Raw AI response: ', 'wc-ai-review-responder' ),
			'jsonDecodeError'              => __( '  JSON decode error: ', 'wc-ai-review-responder' ),
			'decodedSuggestions'           => __( '  Decoded suggestions: ', 'wc-ai-review-responder' ),
			'invalidJsonResponse'          => __( 'Invalid JSON response from AI for suggestions.', 'wc-ai-review-responder' ),
			'aiSuggestionsReceived'        => __( '✓ AI suggestions received', 'wc-ai-review-responder' ),
			'suggestedMoodValue'           => __( '  - Suggested mood: ', 'wc-ai-review-responder' ),
			'suggestedTemplateValue'       => __( '  - Suggested template: ', 'wc-ai-review-responder' ),
			'step3GenerateResponse'        => __( 'Step 3: Generate Final AI Response', 'wc-ai-review-responder' ),
			'buildingFinalPrompt'          => __( '- Building final prompt with suggestions...', 'wc-ai-review-responder' ),
			'generatedFinalPrompt'         => __( '  Generated final prompt: ', 'wc-ai-review-responder' ),
			'sendingFinalRequest'          => __( '- Sending final response request to AI...', 'wc-ai-review-responder' ),
			'aiResponseData'               => __( '  AI response data: ', 'wc-ai-review-responder' ),
			'validatingFinalResponse'      => __( '- Validating final AI response...', 'wc-ai-review-responder' ),
			'finalResponseValidated'       => __( '✓ Final response validated.', 'wc-ai-review-responder' ),
			'validatedReply'               => __( '  Validated reply: ', 'wc-ai-review-responder' ),
			'generatedAiReply'             => __( 'Generated AI reply: ', 'wc-ai-review-responder' ),
			'rateLimitExceeded'            => __( 'Rate limit exceeded: ', 'wc-ai-review-responder' ),
		);
	}
}
