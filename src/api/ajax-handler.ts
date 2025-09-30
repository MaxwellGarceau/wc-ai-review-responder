/**
 * AJAX request handling for admin review actions.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import { AiResponseData, AiSuggestionsResponseData } from '../types/admin-types';

/**
 * Makes an AJAX request to generate an AI response
 *
 * @param {string} commentId - The comment ID to generate a response for
 * @param {string} template  - The template to use for the response
 * @param {string} mood      - The mood to use for the response
 * @param {string} nonce     - The WordPress nonce for security
 * @return {Promise<AiResponseData>} The response data from the server
 */
export async function generateAiResponse(
	commentId: string,
	template: string,
	mood: string,
	nonce: string
): Promise< AiResponseData > {
	const formData: FormData = new FormData();
	formData.append( 'action', 'generate_ai_response' );
	formData.append( 'comment_id', commentId );
	formData.append( 'template', template );
	formData.append( 'mood', mood );
	formData.append( '_wpnonce', nonce );

	const response: Response = await fetch( window.wcAiReviewResponder.ajaxurl, {
		method: 'POST',
		body: formData,
	} );

	return await response.json();
}

/**
 * Makes an AJAX request to get AI suggestions for mood and template
 *
 * @since 1.1.0
 *
 * @param {string} commentId - The comment ID to get suggestions for
 * @param {string} nonce     - The WordPress nonce for security
 * @return {Promise<AiSuggestionsResponseData>} The response data from the server
 */
export async function getAiSuggestions(
	commentId: string,
	nonce: string
): Promise< AiSuggestionsResponseData > {
	const formData: FormData = new FormData();
	formData.append( 'action', 'get_ai_suggestions' );
	formData.append( 'comment_id', commentId );
	formData.append( '_wpnonce', nonce );

	const response: Response = await fetch( window.wcAiReviewResponder.ajaxurl, {
		method: 'POST',
		body: formData,
	} );

	return await response.json();
}
