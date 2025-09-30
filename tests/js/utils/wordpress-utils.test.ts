/**
 * Unit tests for WordPress utility functions.
 *
 * @since 1.2.0
 */

/**
 * Internal dependencies
 */
import {
	triggerWordPressReply,
	updateReplyTextarea,
} from '../../../src/utils/wordpress-utils';

describe( 'WordPress Utilities', () => {
	// Before each test, reset the document body and any mocks to ensure a clean state.
	beforeEach( () => {
		document.body.innerHTML = '';
		// Reset the mock for tinymce if it was used in a previous test.
		if ( ( global.tinymce.get as jest.Mock ).mockClear ) {
			( global.tinymce.get as jest.Mock ).mockClear();
		}
	} );

	describe( 'triggerWordPressReply', () => {
		/**
		 * Test: It should find and click the reply button for a given comment ID.
		 * Why: This test ensures the function can correctly locate the specific reply
		 * button associated with a comment and simulate a user click to open the
		 * native WordPress reply interface.
		 */
		it( 'should find and click the reply button for a given comment ID', () => {
			// Arrange: Create a mock button in the DOM with the necessary data attributes.
			const commentId = '123';
			const mockButton = document.createElement( 'button' );
			mockButton.setAttribute( 'data-comment-id', commentId );
			mockButton.setAttribute( 'data-action', 'replyto' );
			mockButton.click = jest.fn(); // Mock the click method to spy on it.
			document.body.appendChild( mockButton );

			// Act: Call the function to trigger the reply.
			triggerWordPressReply( commentId );

			// Assert: Verify that the button's click method was called exactly once.
			expect( mockButton.click ).toHaveBeenCalledTimes( 1 );
		} );

		/**
		 * Test: It should not throw an error if the reply button is not found.
		 * Why: This behavior is expected in some cases (e.g., the reply box is already open).
		 * The test ensures the function fails gracefully without crashing the script if the
		 * button doesn't exist in the DOM.
		 */
		it( 'should do nothing if the reply button is not found', () => {
			// Arrange: Ensure no reply button exists for this comment ID.
			const commentId = '456';

			// Act & Assert: We expect that calling the function does not throw any errors.
			expect( () => triggerWordPressReply( commentId ) ).not.toThrow();
		} );
	} );

	describe( 'updateReplyTextarea', () => {
		/**
		 * Test: It should find the reply textarea by its name and update its value.
		 * Why: This is the primary function of this utility. The test verifies that it can
		 * locate the standard WordPress reply textarea and correctly insert the AI-generated content.
		 */
		it( 'should find the reply textarea and update its value', () => {
			// Arrange: Create a mock textarea in the DOM.
			const content = 'This is an AI-generated reply.';
			const mockTextarea = document.createElement( 'textarea' );
			mockTextarea.name = 'replycontent';
			document.body.appendChild( mockTextarea );

			// Act: Call the function to update the textarea.
			const result = updateReplyTextarea( content );

			// Assert: Check that the textarea's value was updated and the function returned true.
			expect( mockTextarea.value ).toBe( content );
			expect( result ).toBe( true );
		} );

		/**
		 * Test: It should return false if the reply textarea is not found.
		 * Why: This ensures the function provides a clear failure signal to the calling code
		 * (like the review action handler), allowing it to show an appropriate error message
		 * to the user.
		 */
		it( 'should return false if the reply textarea is not found', () => {
			// Arrange: Ensure no reply textarea exists in the DOM.
			const content = 'This content will not be used.';

			// Act: Call the function.
			const result = updateReplyTextarea( content );

			// Assert: Verify that the function correctly signals failure by returning false.
			expect( result ).toBe( false );
		} );

		/**
		 * Test: It should update the TinyMCE editor if one is active for the textarea.
		 * Why: WordPress can use the TinyMCE rich text editor. This test ensures that if
		 * the editor is active, we correctly use its API to set the content, providing a
		 * seamless experience for the user.
		 */
		it( 'should update TinyMCE editor if it is active', () => {
			// Arrange: Create a mock textarea and a mock TinyMCE editor instance.
			const content = 'This is a reply for TinyMCE.';
			const mockTextarea = document.createElement( 'textarea' );
			mockTextarea.name = 'replycontent';
			mockTextarea.id = 'replycontent-id';
			document.body.appendChild( mockTextarea );

			const mockEditor = {
				setContent: jest.fn(),
			};
			// Configure the global `tinymce.get` mock to return our fake editor.
			( global.tinymce.get as jest.Mock ).mockReturnValue( mockEditor );

			// Act: Call the function to update the content.
			updateReplyTextarea( content );

			// Assert: Verify the TinyMCE API was called correctly.
			expect( global.tinymce.get ).toHaveBeenCalledWith(
				mockTextarea.id
			);
			expect( mockEditor.setContent ).toHaveBeenCalledWith( content );
		} );

		/**
		 * Test: It should not fail if TinyMCE exists but no editor is found for the textarea.
		 * Why: This covers an edge case where the global `tinymce` object is present, but an
		 * editor instance for our specific textarea isn't. The function should handle this
		 * gracefully and still update the underlying textarea's value as a fallback.
		 */
		it( 'should handle when TinyMCE is present but the editor is not found', () => {
			// Arrange: Create a mock textarea and configure the TinyMCE mock to find no editor.
			const content = 'Another test reply.';
			const mockTextarea = document.createElement( 'textarea' );
			mockTextarea.name = 'replycontent';
			mockTextarea.id = 'replycontent-id-2';
			document.body.appendChild( mockTextarea );

			// Make the mocked tinymce.get return null, simulating an editor not found.
			( global.tinymce.get as jest.Mock ).mockReturnValue( null );

			// Act & Assert: Expect no errors to be thrown.
			expect( () => updateReplyTextarea( content ) ).not.toThrow();
			// Assert that the textarea value is still updated as a fallback.
			expect( mockTextarea.value ).toBe( content );
		} );
	} );
} );
