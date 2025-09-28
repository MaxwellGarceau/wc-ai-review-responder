/**
 * Admin review actions functionality for WC AI Review Responder.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

interface WcAiReviewResponder {
	ajaxurl: string;
	editorNonce: string;
	editorSettings: {
		teeny: boolean;
		media_buttons: boolean;
		textarea_rows: number;
		quicktags: boolean;
		tinymce: {
			toolbar1: string;
			toolbar2: string;
		};
	};
}

interface AiResponseData {
	success: boolean;
	data: {
		reply?: string;
		message?: string;
	};
}

interface EditorResponseData {
	success: boolean;
	data: {
		editor_html?: string;
		message?: string;
	};
}

declare const wcAiReviewResponder: WcAiReviewResponder;

/**
 * Creates a custom reply box with WordPress editor
 */
async function createCustomReplyBox(commentId: string): Promise<HTMLElement> {
	const replyBox = document.createElement('tr');
	replyBox.className = 'inline-edit-row wc-ai-custom-reply-row';
	replyBox.id = `wc-ai-reply-${commentId}`;
	replyBox.style.display = 'none';
	
	// Get the comment post ID from the comment row
	const commentRow = document.querySelector(`#comment-${commentId}`);
	const commentPostId = commentRow?.getAttribute('data-post-id') || '';
	
	// Create basic structure first
	replyBox.innerHTML = `
		<td colspan="7" class="colspanchange">
			<fieldset class="comment-reply">
				<legend>
					<span class="hidden" id="editlegend-${commentId}" style="display: none;">Edit Comment</span>
					<span class="hidden" id="replyhead-${commentId}" style="display: inline;">Reply to Comment</span>
					<span class="hidden" id="addhead-${commentId}" style="display: none;">Add Comment</span>
				</legend>

				<div id="replycontainer-${commentId}">
					<div id="wp-editor-loading-${commentId}" class="wp-editor-loading">
						<span class="spinner is-active"></span> Loading editor...
					</div>
				</div>

				<div id="edithead-${commentId}" style="display:none;">
					<div class="inside">
						<label for="author-name-${commentId}">Name</label>
						<input type="text" name="newcomment_author" size="50" value="" id="author-name-${commentId}">
					</div>
					<div class="inside">
						<label for="author-email-${commentId}">Email</label>
						<input type="text" name="newcomment_author_email" size="50" value="" id="author-email-${commentId}">
					</div>
					<div class="inside">
						<label for="author-url-${commentId}">URL</label>
						<input type="text" id="author-url-${commentId}" name="newcomment_author_url" class="code" size="103" value="">
					</div>
				</div>

				<div id="replysubmit-${commentId}" class="submit">
					<p class="reply-submit-buttons">
						<button type="button" class="save button button-primary">
							<span id="addbtn-${commentId}" style="display: none;">Add Comment</span>
							<span id="savebtn-${commentId}" style="display: none;">Update Comment</span>
							<span id="replybtn-${commentId}" style="">Reply</span>
						</button>
						<button type="button" class="cancel button">Cancel</button>
						<span class="waiting spinner"></span>
					</p>
					<div class="notice notice-error notice-alt inline hidden"><p class="error"></p></div>
				</div>

				<input type="hidden" name="action" id="action-${commentId}" value="replyto-comment">
				<input type="hidden" name="comment_ID" id="comment_ID-${commentId}" value="${commentId}">
				<input type="hidden" name="comment_post_ID" id="comment_post_ID-${commentId}" value="${commentPostId}">
				<input type="hidden" name="status" id="status-${commentId}" value="">
				<input type="hidden" name="position" id="position-${commentId}" value="-1">
				<input type="hidden" name="checkbox" id="checkbox-${commentId}" value="1">
				<input type="hidden" name="mode" id="mode-${commentId}" value="detail">
				<input type="hidden" id="_ajax_nonce-replyto-comment-${commentId}" name="_ajax_nonce-replyto-comment" value="">
				<input type="hidden" id="_wp_unfiltered_html_comment-${commentId}" name="_wp_unfiltered_html_comment" value="">
			</fieldset>
		</td>
	`;
	
	// Load WordPress editor via AJAX
	try {
		const formData = new FormData();
		formData.append('action', 'get_wp_editor_html');
		formData.append('comment_id', commentId);
		formData.append('_wpnonce', wcAiReviewResponder.editorNonce);
		
		const response = await fetch(wcAiReviewResponder.ajaxurl, {
			method: 'POST',
			body: formData
		});
		
		const data: EditorResponseData = await response.json();
		
		if (data.success && data.data.editor_html) {
			// Replace loading message with actual editor
			const container = replyBox.querySelector(`#replycontainer-${commentId}`);
			if (container) {
				container.innerHTML = data.data.editor_html;
			}
		} else {
			// Fallback to simple textarea if editor fails
			const container = replyBox.querySelector(`#replycontainer-${commentId}`);
			if (container) {
				container.innerHTML = `
					<label for="replycontent-${commentId}" class="screen-reader-text">Comment</label>
					<textarea name="replycontent" id="replycontent-${commentId}" rows="10" cols="40" class="large-text"></textarea>
				`;
			}
		}
	} catch (error) {
		console.error('Failed to load WordPress editor:', error);
		// Fallback to simple textarea
		const container = replyBox.querySelector(`#replycontainer-${commentId}`);
		if (container) {
			container.innerHTML = `
				<label for="replycontent-${commentId}" class="screen-reader-text">Comment</label>
				<textarea name="replycontent" id="replycontent-${commentId}" rows="10" cols="40" class="large-text"></textarea>
			`;
		}
	}
	
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
				replyBox = await createCustomReplyBox(commentId);
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
					const replyTextarea: HTMLTextAreaElement | null = replyBox.querySelector(`#replycontent-${commentId}`);
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
	// Cancel button
	const cancelButton = replyBox.querySelector('.cancel');
	if (cancelButton) {
		cancelButton.addEventListener('click', () => {
			hideReplyBox(replyBox);
		});
	}
	
	// Submit button - integrate with WordPress comment reply functionality
	const submitButton = replyBox.querySelector('.save');
	if (submitButton) {
		submitButton.addEventListener('click', () => {
			// Use WordPress's native comment reply functionality
			if (typeof (window as any).replytoComment === 'function') {
				(window as any).replytoComment(commentId);
			} else {
				// Fallback: show alert for now
				alert('Reply submission functionality will be implemented in a future update.');
			}
		});
	}
}

