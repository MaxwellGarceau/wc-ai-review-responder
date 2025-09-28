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
 * Creates the loading modal HTML content
 */
function createLoadingModalHTML(): string {
	return `
		<div class="modal-content">
			<div class="spinner"></div>
			<h3>Generating AI Response</h3>
			<p>Please wait while we generate a personalized response to this review...</p>
		</div>
	`;
}

/**
 * Injects the CSS styles for the loading modal
 */
function injectLoadingModalCSS(): void {
	// Check if CSS already exists
	if (document.getElementById('wc-ai-loading-modal-css')) {
		return;
	}

	const style = document.createElement('style');
	style.id = 'wc-ai-loading-modal-css';
	style.textContent = `
		#wc-ai-loading-modal {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			display: flex;
			justify-content: center;
			align-items: center;
			z-index: 999999;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
		}
		#wc-ai-loading-modal .modal-content {
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
			text-align: center;
			max-width: 400px;
			width: 90%;
		}
		#wc-ai-loading-modal .spinner {
			width: 40px;
			height: 40px;
			border: 4px solid #f3f3f3;
			border-top: 4px solid #2271b1;
			border-radius: 50%;
			animation: spin 1s linear infinite;
			margin: 0 auto 20px;
		}
		#wc-ai-loading-modal h3 {
			margin: 0 0 10px 0;
			color: #1d2327;
			font-size: 18px;
			font-weight: 600;
		}
		#wc-ai-loading-modal p {
			margin: 0;
			color: #646970;
			font-size: 14px;
			line-height: 1.4;
		}
		@keyframes spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
	`;
	document.head.appendChild(style);
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

	// Inject CSS styles
	injectLoadingModalCSS();

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
