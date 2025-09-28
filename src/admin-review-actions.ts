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
	// Find the reply button for this comment
	const replyButton = document.querySelector(`button[data-comment-id="${commentId}"][data-action="replyto"]`) as HTMLButtonElement;
	
	if (replyButton) {
		// Click the reply button to trigger WordPress's native reply functionality
		replyButton.click();
	} else {
		console.error(`Reply button not found for comment ID: ${commentId}`);
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
					// WordPress creates a textarea with name="replycontent" in the reply box
					const replyTextarea: HTMLTextAreaElement | null = document.querySelector('textarea[name="replycontent"]');
					if (replyTextarea && data.data.reply) {
						// Set the content in the textarea
						replyTextarea.value = data.data.reply;
						
						// If TinyMCE is active, update it as well
						if (typeof (window as any).tinymce !== 'undefined') {
							const editor = (window as any).tinymce.get(replyTextarea.id);
							if (editor) {
								editor.setContent(data.data.reply);
							}
						}
						
						// Trigger change event to update any listeners
						replyTextarea.dispatchEvent(new Event('change'));
						// Focus the textarea
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


