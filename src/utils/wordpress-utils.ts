/**
 * WordPress-specific utility functions for admin review actions.
 *
 * @since 1.0.0
 */

/**
 * Triggers the native WordPress reply box for the comment
 *
 * @param {string} commentId - The comment ID to reply to
 */
export function triggerWordPressReply( commentId: string ): void {
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
 * Updates the WordPress reply textarea with the generated content
 *
 * @param {string} content - The content to insert into the textarea
 * @return {boolean} True if successful, false if textarea not found
 */
export function updateReplyTextarea( content: string ): boolean {
	const replyTextarea: HTMLTextAreaElement | null = document.querySelector(
		'textarea[name="replycontent"]'
	);

	if ( ! replyTextarea ) {
		return false;
	}

	replyTextarea.value = content;

	// If TinyMCE is active, update it as well
	if (
		typeof ( window as unknown as { tinymce?: unknown } ).tinymce !==
		'undefined'
	) {
		const tinymce = (
			window as unknown as {
				tinymce: {
					get: ( id: string ) => {
						setContent: ( content: string ) => void;
					};
				};
			}
		 ).tinymce;
		const editor = tinymce.get( replyTextarea.id );
		if ( editor ) {
			editor.setContent( content );
		}
	}

	replyTextarea.dispatchEvent( new Event( 'change' ) );
	replyTextarea.focus();

	return true;
}
