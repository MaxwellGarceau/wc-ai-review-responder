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
		// Missing required data attributes - cannot proceed
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
				updateReplyTextarea( data.data.reply );
			}
		} catch ( error: unknown ) {
			// TODO: mgarceau 2025-09-29: Handle error from BE here
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
