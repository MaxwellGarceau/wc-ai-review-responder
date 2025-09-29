/**
 * Admin review actions functionality for WC AI Review Responder.
 *
 * We use a function-based approach here instead of a class-based approach because:
 * - The logic is primarily procedural and event-driven, matching the structure of WordPress admin scripts.
 * - Functions provide a simpler, more readable way to organize small, self-contained behaviors.
 * - There is no need for stateful objects or inheritance, so classes would add unnecessary complexity.
 * - This approach aligns with WordPress and WooCommerce JavaScript best practices for admin UI enhancements.
 *
 * @since 1.0.0
 */

/**
 * External dependencies
 */

/**
 * Internal dependencies
 */
import loadingModalTemplate from './templates/loading-modal.html';
import promptModalTemplate from './templates/prompt-selection-modal.html';

interface Template {
	value: string;
	label: string;
}

interface WcAiReviewResponder {
	ajaxurl: string;
	templates: Template[];
}

interface AiResponseData {
	success: boolean;
	data: {
		reply?: string;
		message?: string;
	};
}

declare const wcAiReviewResponder: WcAiReviewResponder;

/**
 * Triggers the native WordPress reply box for the comment
 */
function triggerWordPressReply( commentId: string ): void {
	const replyButton = document.querySelector(
		`button[data-comment-id="${ commentId }"][data-action="replyto"]`
	) as HTMLButtonElement;

	if ( replyButton ) {
		replyButton.click();
	} else {
		// Reply button not found - this is expected in some cases
		// The user can manually open the reply box if needed
	}
}

/**
 * Creates the loading modal HTML content with improved spinner
 */
function createLoadingModalHTML(): string {
	return loadingModalTemplate;
}

/**
 * Shows a loading modal over the reply box
 */
function showLoadingModal(): void {
	// Check if modal already exists
	const modal = document.querySelector(
		'.wc-ai-loading-modal'
	) as HTMLElement;
	if ( modal ) {
		modal.style.display = 'flex';
		return;
	}

	// Insert the modal HTML directly into the body
	document.body.insertAdjacentHTML( 'beforeend', createLoadingModalHTML() );
}

/**
 * Hides the loading modal
 */
function hideLoadingModal(): void {
	const modal = document.querySelector(
		'.wc-ai-loading-modal'
	) as HTMLElement;
	if ( modal ) {
		modal.style.display = 'none';
	}
}

/**
 * Shows the prompt selection modal.
 *
 * @param {() => void} onGenerate - Callback function to execute when the generate button is clicked.
 * @param {() => void} onCancel - Callback function to execute when the cancel button is clicked.
 */
function showPromptModal( onGenerate: () => void, onCancel: () => void ): void {
	// Insert the modal HTML if it doesn't exist
	if ( ! document.querySelector( '.wc-ai-prompt-modal' ) ) {
		document.body.insertAdjacentHTML( 'beforeend', promptModalTemplate );
	}

	const modal = document.querySelector( '.wc-ai-prompt-modal' ) as HTMLElement;
	const select = modal.querySelector( '#wc-ai-prompt-modal-select' ) as HTMLSelectElement;
	const generateButton = modal.querySelector( '#wc-ai-prompt-modal-generate' ) as HTMLButtonElement;
	const cancelButton = modal.querySelector( '#wc-ai-prompt-modal-cancel' ) as HTMLButtonElement;
	const overlay = modal.querySelector( '.wc-ai-prompt-modal__overlay' ) as HTMLElement;

	// Populate select options
	select.innerHTML = ''; // Clear existing options
	wcAiReviewResponder.templates.forEach( ( template: Template ) => {
		const option = document.createElement( 'option' );
		option.value = template.value;
		option.textContent = template.label;
		select.appendChild( option );
	} );

	// Event listeners for buttons
	const generateClickHandler = () => {
		onGenerate();
		cleanup();
	};

	const cancelClickHandler = () => {
		onCancel();
		cleanup();
	};

	const cleanup = () => {
		generateButton.removeEventListener( 'click', generateClickHandler );
		cancelButton.removeEventListener( 'click', cancelClickHandler );
		overlay.removeEventListener( 'click', cancelClickHandler );
		modal.style.display = 'none';
	};

	generateButton.addEventListener( 'click', generateClickHandler );
	cancelButton.addEventListener( 'click', cancelClickHandler );
	overlay.addEventListener( 'click', cancelClickHandler );

	modal.style.display = 'flex';
}

/**
 * Gets the selected template from the prompt modal.
 *
 * @return {string} The selected template value.
 */
function getSelectedTemplate(): string {
	const select = document.querySelector( '#wc-ai-prompt-modal-select' ) as HTMLSelectElement;
	return select ? select.value : 'default';
}

document.addEventListener( 'DOMContentLoaded', (): void => {
	const aiResponseLinks: NodeListOf< HTMLAnchorElement > =
		document.querySelectorAll( '.ai-generate-response' );

	aiResponseLinks.forEach( ( link: HTMLAnchorElement ): void => {
		link.addEventListener( 'click', ( e: Event ): void => {
			e.preventDefault();

			const commentId: string | null =
				link.getAttribute( 'data-comment-id' );
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
					// Make AJAX request
					const formData: FormData = new FormData();
					formData.append( 'action', 'generate_ai_response' );
					formData.append( 'comment_id', commentId );
					formData.append( 'template', getSelectedTemplate() );
					formData.append( '_wpnonce', nonce );

					const response: Response = await fetch(
						wcAiReviewResponder.ajaxurl,
						{
							method: 'POST',
							body: formData,
						}
					);

					const data: AiResponseData = await response.json();

					if ( data.success && data.data.reply ) {
						// Insert the generated response into the WordPress reply textarea
						const replyTextarea: HTMLTextAreaElement | null =
							document.querySelector(
								'textarea[name="replycontent"]'
							);
						if ( replyTextarea && data.data.reply ) {
							replyTextarea.value = data.data.reply;

							// If TinyMCE is active, update it as well
							if (
								typeof (
									window as unknown as { tinymce?: unknown }
								).tinymce !== 'undefined'
							) {
								const tinymce = (
									window as unknown as {
										tinymce: {
											get: ( id: string ) => {
												setContent: (
													content: string
												) => void;
											};
										};
									}
								).tinymce;
								const editor = tinymce.get( replyTextarea.id );
								if ( editor ) {
									editor.setContent( data.data.reply );
								}
							}

							replyTextarea.dispatchEvent( new Event( 'change' ) );
							replyTextarea.focus();
						}
					}
				} catch ( error: unknown ) {
					// Handle fetch error silently
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
		} );
	} );
} );
