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
 * Creates a custom reply box that mimics WordPress default styling
 */
function createCustomReplyBox(commentId: string): HTMLElement {
	const replyBox = document.createElement('div');
	replyBox.className = 'wc-ai-custom-reply-box';
	replyBox.id = `wc-ai-reply-${commentId}`;
	replyBox.style.display = 'none';
	
	replyBox.innerHTML = `
		<div class="wc-ai-reply-content">
			<div class="wc-ai-reply-header">
				<h4>Reply to Review</h4>
				<button type="button" class="wc-ai-reply-close" aria-label="Close reply box">×</button>
			</div>
			<div class="wc-ai-reply-form">
				<textarea name="replycontent" class="wc-ai-reply-textarea" placeholder="Write your reply..." rows="4"></textarea>
				<div class="wc-ai-reply-actions">
					<button type="button" class="button button-primary wc-ai-reply-submit">Reply</button>
					<button type="button" class="button wc-ai-reply-cancel">Cancel</button>
				</div>
			</div>
		</div>
	`;
	
	return replyBox;
}

/**
 * Shows the custom reply box with fade-in animation
 */
function showReplyBox(replyBox: HTMLElement): void {
	replyBox.style.display = 'block';
	replyBox.style.opacity = '0';
	replyBox.style.transform = 'translateY(-10px)';
	
	// Trigger reflow to ensure initial styles are applied
	replyBox.offsetHeight;
	
	// Animate in
	replyBox.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
	replyBox.style.opacity = '1';
	replyBox.style.transform = 'translateY(0)';
}

/**
 * Hides the custom reply box with fade-out animation
 */
function hideReplyBox(replyBox: HTMLElement): void {
	replyBox.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
	replyBox.style.opacity = '0';
	replyBox.style.transform = 'translateY(-10px)';
	
	setTimeout(() => {
		replyBox.style.display = 'none';
	}, 300);
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
			
			// Create or get existing custom reply box
			let replyBox: HTMLElement | null = document.getElementById(`wc-ai-reply-${commentId}`);
			if (!replyBox) {
				replyBox = createCustomReplyBox(commentId);
				// Insert the reply box after the comment row
				const commentRow = link.closest('tr');
				if (commentRow && commentRow.parentNode) {
					commentRow.parentNode.insertBefore(replyBox, commentRow.nextSibling);
				}
			}
			
			// Show the reply box
			showReplyBox(replyBox);
			
			// Set up event listeners for the reply box
			setupReplyBoxEventListeners(replyBox, commentId);
			
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
					// Insert the generated response into the textarea
					const replyTextarea: HTMLTextAreaElement | null = replyBox.querySelector('.wc-ai-reply-textarea');
					if (replyTextarea && data.data.reply) {
						replyTextarea.value = data.data.reply;
						// Trigger change event to update any listeners
						replyTextarea.dispatchEvent(new Event('change'));
						// Focus the textarea so user can see the generated content
						replyTextarea.focus();
					}
				} else {
					const errorMessage: string = data.data.message || 'Failed to generate AI response';
					alert(`Error: ${errorMessage}`);
				}
			} catch (error: unknown) {
				console.error('Error:', error);
				alert('Error: Failed to generate AI response');
			} finally {
				// Restore original state
				link.textContent = originalText;
				link.style.pointerEvents = 'auto';
			}
		});
	});
});

/**
 * Sets up event listeners for the custom reply box
 */
function setupReplyBoxEventListeners(replyBox: HTMLElement, commentId: string): void {
	// Close button
	const closeButton = replyBox.querySelector('.wc-ai-reply-close');
	if (closeButton) {
		closeButton.addEventListener('click', () => {
			hideReplyBox(replyBox);
		});
	}
	
	// Cancel button
	const cancelButton = replyBox.querySelector('.wc-ai-reply-cancel');
	if (cancelButton) {
		cancelButton.addEventListener('click', () => {
			hideReplyBox(replyBox);
		});
	}
	
	// Submit button (placeholder for future functionality)
	const submitButton = replyBox.querySelector('.wc-ai-reply-submit');
	if (submitButton) {
		submitButton.addEventListener('click', () => {
			// TODO: Implement actual reply submission
			alert('Reply submission functionality will be implemented in a future update.');
		});
	}
}
