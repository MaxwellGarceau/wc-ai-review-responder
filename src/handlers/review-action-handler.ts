/**
 * Event handlers for review action functionality.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import {
	triggerWordPressReply,
	updateReplyTextarea,
} from '../utils/wordpress-utils';
import { showLoadingModal, hideLoadingModal } from '../modals/loading-modal';
import {
	showPromptModal,
	getSelectedTemplate,
	getSelectedMood,
} from '../modals/prompt-modal';
import { showGenericError } from '../modals/error-modal';
import { generateAiResponse, getAiSuggestions } from '../api/ajax-handler';
import { __ } from '../utils/i18n';

/**
 * Handles the click event for AI response generation links.
 *
 * @param {HTMLAnchorElement} link - The clicked link element
 */
export function handleAiResponseClick( link: HTMLAnchorElement ): void {
	const commentId: string | null = link.getAttribute( 'data-comment-id' );
	const suggestNonce: string | null =
		link.getAttribute( 'data-suggest-nonce' );
	const generateNonce: string | null = link.getAttribute(
		'data-generate-nonce'
	);

	if ( ! commentId || ! suggestNonce || ! generateNonce ) {
		// Missing required data attributes - show error
		showGenericError(
			__( 'missingDataAttributes' ),
			__( 'configurationError' )
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
	link.textContent = __( 'gettingSuggestions' );
	link.style.pointerEvents = 'none';
	showLoadingModal();

	// Define what happens when the user clicks "Generate" in the modal
	const handleGenerate = async () => {
		showLoadingModal();
		try {
			const data = await generateAiResponse(
				commentId,
				getSelectedTemplate(),
				getSelectedMood(),
				generateNonce
			);

			if ( data.success && data.data.reply ) {
				const updateSuccess = updateReplyTextarea( data.data.reply );
				if ( ! updateSuccess ) {
					showGenericError(
						__( 'couldNotFindTextarea' ),
						__( 'interfaceError' )
					);
				}
			} else {
				const errorMessage =
					data.data?.message ||
					__( 'serverReturnedError' );
				showGenericError( errorMessage, __( 'serverError' ) );
			}
		} catch ( error: unknown ) {
			showGenericError(
				error as Error,
				__( 'failedToGenerateResponse' )
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

	getAiSuggestions( commentId, suggestNonce )
		.then( ( suggestions ) => {
			hideLoadingModal();
			let suggestedTemplate: string | undefined;
			let suggestedMood: string | undefined;
			let suggestionFailed = false;

			if ( suggestions.success ) {
				suggestedTemplate = suggestions.data.template;
				suggestedMood = suggestions.data.mood;
			} else {
				suggestionFailed = true;
			}

			showPromptModal(
				handleGenerate,
				handleCancel,
				suggestedTemplate,
				suggestedMood,
				suggestionFailed
			);
		} )
		.catch( () => {
			hideLoadingModal();
			showPromptModal(
				handleGenerate,
				handleCancel,
				undefined,
				undefined,
				true
			);
		} )
		.finally( () => {
			link.textContent = originalText; // Reset link text after suggestions are loaded
		} );
}
