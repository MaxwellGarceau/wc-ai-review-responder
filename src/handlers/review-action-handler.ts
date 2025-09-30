/**
 * Event handlers for review action functionality.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import { triggerWordPressReply, updateReplyTextarea } from '../utils/wordpress-utils';
import { showLoadingModal, hideLoadingModal } from '../modals/loading-modal';
import { showPromptModal, getSelectedTemplate } from '../modals/prompt-modal';
import { showGenericError } from '../modals/error-modal';
import { generateAiResponse } from '../api/ajax-handler';

/**
 * Handles the click event for AI response generation links.
 *
 * @param {HTMLAnchorElement} link - The clicked link element
 */
export function handleAiResponseClick( link: HTMLAnchorElement ): void {
	const commentId: string | null = link.getAttribute( 'data-comment-id' );
	const nonce: string | null = link.getAttribute( 'data-nonce' );

	if ( ! commentId || ! nonce ) {
		// Missing required data attributes - show error
		showGenericError( 
			'Missing required data attributes. Please refresh the page and try again.',
			'Configuration Error'
		);
		return;
	}

	// Trigger the native WordPress reply box
	triggerWordPressReply( commentId );

	// Show loading state on the link
	const originalText: string = link.textContent || '';
	const restoreLinkState = () => {
		link.textContent = originalText;
		link.style.pointerEvents = 'auto';
	};
	link.textContent = 'Generating...';
	link.style.pointerEvents = 'none';

	// Define what happens when the user clicks "Generate" in the modal
	const handleGenerate = async () => {
		showLoadingModal();

		try {
			const data = await generateAiResponse(
				commentId,
				getSelectedTemplate(),
				nonce
			);

			if ( data.success && data.data.reply ) {
				const updateSuccess = updateReplyTextarea( data.data.reply );
				if ( ! updateSuccess ) {
					showGenericError(
						'Could not find the reply textarea. Please make sure the reply box is open and try again.',
						'Interface Error'
					);
				}
			} else if ( data.success && ! data.data.reply ) {
				// Server returned success but no reply content
				showGenericError(
					'The AI service returned an empty response. Please try again.',
					'Empty Response'
				);
			} else {
				// Server returned an error response
				const errorMessage = data.data?.message || 'The server returned an error response.';
				showGenericError(
					errorMessage,
					'Server Error'
				);
			}
		} catch ( error: unknown ) {
			// Display user-friendly error message
			showGenericError( 
				error as Error, 
				'Failed to generate AI response' 
			);
		} finally {
			hideLoadingModal();
			restoreLinkState();
		}
	};

	// Define what happens when the user clicks "Cancel" in the modal
	const handleCancel = () => {
		restoreLinkState();
	};

	// Show the prompt selection modal
	showPromptModal( handleGenerate, handleCancel );
}
