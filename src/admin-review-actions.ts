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
					// Find the reply textarea for this comment
					const replyTextarea: HTMLTextAreaElement | null = document.querySelector('textarea[name="replycontent"]');
					if (replyTextarea) {
						replyTextarea.value = data.data.reply;
						// Trigger change event to update any listeners
						replyTextarea.dispatchEvent(new Event('change'));
					}
					
					// Log the generated reply in alert
					alert(data.data.reply);
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
