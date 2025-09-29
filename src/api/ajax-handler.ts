/**
 * AJAX request handling for admin review actions.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import { AiResponseData } from '../types/admin-types';

/**
 * Makes an AJAX request to generate an AI response
 *
 * @param {string} commentId - The comment ID to generate a response for
 * @param {string} template - The template to use for the response
 * @param {string} nonce - The WordPress nonce for security
 * @return {Promise<AiResponseData>} The response data from the server
 */
export async function generateAiResponse(
	commentId: string,
	template: string,
	nonce: string
): Promise< AiResponseData > {
	const formData: FormData = new FormData();
	formData.append( 'action', 'generate_ai_response' );
	formData.append( 'comment_id', commentId );
	formData.append( 'template', template );
	formData.append( '_wpnonce', nonce );

	const response: Response = await fetch( wcAiReviewResponder.ajaxurl, {
		method: 'POST',
		body: formData,
	} );

	return await response.json();
}
