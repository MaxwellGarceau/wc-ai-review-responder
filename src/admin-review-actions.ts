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

interface WcAiReviewResponder {
	ajaxurl: string;
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
	let modal = document.getElementById( 'wc-ai-loading-modal' );
	if ( modal ) {
		modal.style.display = 'flex';
		return;
	}

	// Create the loading modal
	modal = document.createElement( 'div' );
	modal.id = 'wc-ai-loading-modal';
	modal.innerHTML = createLoadingModalHTML();
	document.body.appendChild( modal );
}

/**
 * Hides the loading modal
 */
function hideLoadingModal(): void {
	const modal = document.getElementById( 'wc-ai-loading-modal' );
	if ( modal ) {
		modal.style.display = 'none';
	}
}

document.addEventListener( 'DOMContentLoaded', (): void => {
	const aiResponseLinks: NodeListOf< HTMLAnchorElement > =
		document.querySelectorAll( '.ai-generate-response' );

	aiResponseLinks.forEach( ( link: HTMLAnchorElement ): void => {
		link.addEventListener( 'click', async ( e: Event ): Promise< void > => {
			e.preventDefault();

			const commentId: string | null =
				link.getAttribute( 'data-comment-id' );
			const nonce: string | null = link.getAttribute( 'data-nonce' );

			if ( ! commentId || ! nonce ) {
				// Missing required data attributes - cannot proceed
				return;
			}

			// Show loading state
			const originalText: string = link.textContent || '';
			link.textContent = 'Generating...';
			link.style.pointerEvents = 'none';

			// Trigger the native WordPress reply box
			triggerWordPressReply( commentId );

			// Show loading modal after a short delay to ensure reply box is open
			setTimeout( () => {
				showLoadingModal();
			}, 100 );

			try {
				// Make AJAX request
				const formData: FormData = new FormData();
				formData.append( 'action', 'generate_ai_response' );
				formData.append( 'comment_id', commentId );
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
							typeof ( window as { tinymce?: unknown } )
								.tinymce !== 'undefined'
						) {
							const tinymce = (
								window as {
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

					// Hide loading modal on success
					hideLoadingModal();
				} else {
					// Error occurred - hide loading modal
					hideLoadingModal();
				}
			} catch ( error: unknown ) {
				// Handle error silently - user will see the loading modal disappear
				hideLoadingModal();
			} finally {
				// Restore original state
				link.textContent = originalText;
				link.style.pointerEvents = 'auto';
			}
		} );
	} );
} );
