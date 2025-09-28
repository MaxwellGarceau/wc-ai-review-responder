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
 * Creates a textarea placeholder under the comment and initializes the WYSIWYG editor
 */
function createAndInitializeEditor(commentId: string): void {
	// Check if editor already exists for this comment
	const existingEditor = document.getElementById(`wc-ai-editor-${commentId}`);
	if (existingEditor) {
		// Editor already exists, just show it
		existingEditor.style.display = 'block';
		existingEditor.scrollIntoView({ behavior: 'smooth', block: 'start' });
		return;
	}

	// Find the comment row
	const commentRow = document.querySelector(`#comment-${commentId}`);
	if (!commentRow) {
		console.error(`Comment row not found for ID: ${commentId}`);
		return;
	}

	// Create the editor container
	const editorContainer = document.createElement('tr');
	editorContainer.id = `wc-ai-editor-${commentId}`;
	editorContainer.className = 'wc-ai-editor-row';
	editorContainer.innerHTML = `
		<td colspan="7" class="colspanchange">
			<div class="wc-ai-editor-container">
				<h4>AI Reply Editor</h4>
				<textarea name="wc_ai_reply_content" id="wc-ai-editor-${commentId}" rows="10" class="large-text"></textarea>
				<div class="wc-ai-editor-actions">
					<button type="button" class="button button-primary wc-ai-save-reply" data-comment-id="${commentId}">Save Reply</button>
					<button type="button" class="button wc-ai-cancel-reply" data-comment-id="${commentId}">Cancel</button>
				</div>
			</div>
		</td>
	`;

	// Insert the editor after the comment row
	const commentTable = commentRow.closest('table');
	if (commentTable) {
		// Find the tbody element within the table
		const tbody = commentTable.querySelector('tbody');
		if (tbody) {
			// Insert after the comment row within the tbody
			const nextSibling = commentRow.nextElementSibling;
			if (nextSibling) {
				tbody.insertBefore(editorContainer, nextSibling);
			} else {
				// If no next sibling, append to the end of tbody
				tbody.appendChild(editorContainer);
			}
		} else {
			// Fallback: append to the table directly
			commentTable.appendChild(editorContainer);
		}
	}

	// Initialize the WordPress editor
	initializeWordPressEditor(`wc-ai-editor-${commentId}`);

	// Set up event listeners
	setupEditorEventListeners(commentId);

	// Scroll to the editor
	editorContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Initialize WordPress editor using wp.editor.initialize
 */
function initializeWordPressEditor(editorId: string): void {
	// Wait for WordPress editor to be available
	if (typeof (window as any).wp !== 'undefined' && (window as any).wp.editor) {
		(window as any).wp.editor.initialize(editorId, {
			tinymce: {
				wp_skip_init: true, // Prevent WordPress from auto-initializing
				plugins: 'wordpress,wplink,wpeMCE,media,fullscreen,paste,image',
				toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,dfw,wp_adv',
				toolbar2: 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
			},
			quicktags: true, // Enable Quicktags (Text tab)
			mediaButtons: true // Enable media upload button
		});
	} else {
		// Fallback: try again after a short delay
		setTimeout(() => {
			initializeWordPressEditor(editorId);
		}, 100);
	}
}

/**
 * Set up event listeners for the editor
 */
function setupEditorEventListeners(commentId: string): void {
	// Cancel button
	const cancelButton = document.querySelector(`.wc-ai-cancel-reply[data-comment-id="${commentId}"]`);
	if (cancelButton) {
		cancelButton.addEventListener('click', () => {
			hideEditor(commentId);
		});
	}

	// Save button (placeholder for future functionality)
	const saveButton = document.querySelector(`.wc-ai-save-reply[data-comment-id="${commentId}"]`);
	if (saveButton) {
		saveButton.addEventListener('click', () => {
			// TODO: Implement actual reply saving
			alert('Reply saving functionality will be implemented in a future update.');
		});
	}
}

/**
 * Hide the editor
 */
function hideEditor(commentId: string): void {
	const editor = document.getElementById(`wc-ai-editor-${commentId}`);
	if (editor) {
		editor.style.display = 'none';
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
			
			// Create and initialize the editor
			createAndInitializeEditor(commentId);
			
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
					// Insert the generated response into the editor
					const editorTextarea: HTMLTextAreaElement | null = document.querySelector(`#wc-ai-editor-${commentId}`);
					if (editorTextarea && data.data.reply) {
						// Set the content in the textarea
						editorTextarea.value = data.data.reply;
						
						// If TinyMCE is active, update it as well
						if (typeof (window as any).tinymce !== 'undefined') {
							const editor = (window as any).tinymce.get(`wc-ai-editor-${commentId}`);
							if (editor) {
								editor.setContent(data.data.reply);
							}
						}
						
						// Trigger change event to update any listeners
						editorTextarea.dispatchEvent(new Event('change'));
						// Focus the editor
						editorTextarea.focus();
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


