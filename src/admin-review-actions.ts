/**
 * Admin review actions functionality for WC AI Review Responder.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */


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
function triggerWordPressReply(commentId: string): void {
	const replyButton = document.querySelector(`button[data-comment-id="${commentId}"][data-action="replyto"]`) as HTMLButtonElement;
	
	if (replyButton) {
		replyButton.click();
	} else {
		console.error(`Reply button not found for comment ID: ${commentId}`);
	}
}

/**
 * Creates the loading modal HTML content with improved spinner
 */
function createLoadingModalHTML(): string {
	return `
		<div class="modal-content">
			<svg class="spinner-svg" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="20" cy="20" r="18" stroke="#f3f3f3" stroke-width="4"/>
				<path d="M 20 2 A 18 18 0 1 1 19.9 2" stroke="#2271b1" stroke-width="4" stroke-linecap="round">
					<animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" values="0 20 20;360 20 20"/>
				</path>
			</svg>
			<h3>Generating AI Response</h3>
			<p>Please wait while we generate a personalized response to this review...</p>
		</div>
	`;
}

/**
 * Shows a loading modal over the reply box
 */
function showLoadingModal(): void {
	// Check if modal already exists
	let modal = document.getElementById('wc-ai-loading-modal');
	if (modal) {
		modal.style.display = 'flex';
		return;
	}

	// Create the loading modal
	modal = document.createElement('div');
	modal.id = 'wc-ai-loading-modal';
	modal.innerHTML = createLoadingModalHTML();
	document.body.appendChild(modal);
}

/**
 * Hides the loading modal
 */
function hideLoadingModal(): void {
	const modal = document.getElementById('wc-ai-loading-modal');
	if (modal) {
		modal.style.display = 'none';
	}
}

document.addEventListener('DOMContentLoaded', (): void => {
	const aiResponseLinks: NodeListOf<HTMLAnchorElement> = document.querySelectorAll('.ai-generate-response');
	
	aiResponseLinks.forEach((link: HTMLAnchorElement): void => {
		link.addEventListener('click', async (e: Event): Promise<void> => {
			e.preventDefault();
			
			const commentId: string | null = link.getAttribute('data-comment-id');
			const nonce: string | null = link.getAttribute('data-nonce');
			
			if (!commentId || !nonce) {
				console.error('Missing required data attributes');
				return;
			}
			
			// Show loading state
			const originalText: string = link.textContent || '';
			link.textContent = 'Generating...';
			link.style.pointerEvents = 'none';
			
			// Trigger the native WordPress reply box
			triggerWordPressReply(commentId);
			
			// Show loading modal after a short delay to ensure reply box is open
			setTimeout(() => {
				showLoadingModal();
			}, 100);
			
			try {
				// Make AJAX request
				const formData: FormData = new FormData();
				formData.append('action', 'generate_ai_response');
				formData.append('comment_id', commentId);
				formData.append('_wpnonce', nonce);
				
				const response: Response = await fetch(wcAiReviewResponder.ajaxurl, {
					method: 'POST',
					body: formData
				});
				
				const data: AiResponseData = await response.json();
				
				if (data.success && data.data.reply) {
					// Insert the generated response into the WordPress reply textarea
					const replyTextarea: HTMLTextAreaElement | null = document.querySelector('textarea[name="replycontent"]');
					if (replyTextarea && data.data.reply) {
						replyTextarea.value = data.data.reply;
						
						// If TinyMCE is active, update it as well
						if (typeof (window as any).tinymce !== 'undefined') {
							const editor = (window as any).tinymce.get(replyTextarea.id);
							if (editor) {
								editor.setContent(data.data.reply);
							}
						}
						
						replyTextarea.dispatchEvent(new Event('change'));
						replyTextarea.focus();
					}
					
					// Hide loading modal on success
					hideLoadingModal();
				} else {
					const errorMessage: string = data.data.message || 'Failed to generate AI response';
					alert(`Error: ${errorMessage}`);
					hideLoadingModal();
				}
			} catch (error: unknown) {
				console.error('Error:', error);
				alert('Error: Failed to generate AI response');
				hideLoadingModal();
			} finally {
				// Restore original state
				link.textContent = originalText;
				link.style.pointerEvents = 'auto';
			}
		});
	});
});
