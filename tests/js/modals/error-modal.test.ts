/**
 * Unit tests for the error modal functionality.
 *
 * @since 1.2.0
 */
import {
	showErrorModal,
	hideErrorModal,
	showGenericError,
} from '../../../src/modals/error-modal';
import errorModalTemplate from '../../../src/templates/error-modal.html';

describe( 'Error Modal', () => {
	beforeEach( () => {
		document.body.innerHTML = '';
	} );

	describe( 'showErrorModal', () => {
		/**
		 * Test: It should add the modal to the DOM if it is not already present.
		 * Why: This ensures the modal's HTML is injected into the document on the first
		 * call, making it available for display.
		 */
		it( 'should add the modal to the DOM if not present', () => {
			showErrorModal( { message: 'Test error' } );
			const modal = document.querySelector( '.wc-ai-rr-error-modal' );
			expect( modal ).not.toBeNull();
		} );

		/**
		 * Test: It should set the title and message content correctly.
		 * Why: This verifies that the modal can be customized with a specific title and
		 * message, ensuring the user receives accurate and contextual error information.
		 */
		it( 'should set the title and message content correctly', () => {
			const title = 'Custom Title';
			const message = 'This is a custom error message.';
			showErrorModal( { title, message } );

			const titleElement = document.querySelector(
				'.wc-ai-rr-error-modal__title'
			);
			const messageElement = document.querySelector(
				'.wc-ai-rr-error-modal__message'
			);

			expect( titleElement?.textContent ).toBe( title );
			expect( messageElement?.textContent ).toBe( message );
		} );

		/**
		 * Test: It should use a default title if one is not provided.
		 * Why: This ensures the modal always has a title, providing a consistent user
		 * experience even if a custom title is omitted in the function call.
		 */
		it( 'should use a default title if one is not provided', () => {
			showErrorModal( { message: 'Another error' } );
			const titleElement = document.querySelector(
				'.wc-ai-rr-error-modal__title'
			);
			expect( titleElement?.textContent ).toBe( 'Error' );
		} );

		/**
		 * Test: It should make the modal visible by setting its display style.
		 * Why: This confirms that calling the function actually shows the modal to the user.
		 */
		it( 'should display the modal', () => {
			showErrorModal( { message: 'Visible error' } );
			const modal = document.querySelector(
				'.wc-ai-rr-error-modal'
			) as HTMLElement;
			expect( modal.style.display ).toBe( 'flex' );
		} );

		/**
		 * Test: It should close the modal when the OK button is clicked.
		 * Why: This verifies a primary user interaction for dismissing the modal. The user
		 * needs a clear and functional way to close the error message.
		 */
		it( 'should close the modal when the OK button is clicked', () => {
			showErrorModal( { message: 'Click test' } );
			const modal = document.querySelector(
				'.wc-ai-rr-error-modal'
			) as HTMLElement;
			const okButton = document.querySelector(
				'#wc-ai-rr-error-modal-ok'
			) as HTMLButtonElement;

			okButton.click();

			expect( modal.style.display ).toBe( 'none' );
		} );

		/**
		 * Test: It should close the modal when the overlay is clicked.
		 * Why: This tests another common and intuitive way for users to dismiss modals.
		 * Clicking the background should close the message.
		 */
		it( 'should close the modal when the overlay is clicked', () => {
			showErrorModal( { message: 'Overlay click test' } );
			const modal = document.querySelector(
				'.wc-ai-rr-error-modal'
			) as HTMLElement;
			const overlay = document.querySelector(
				'.wc-ai-rr-error-modal__overlay'
			) as HTMLElement;

			overlay.click();

			expect( modal.style.display ).toBe( 'none' );
		} );

		/**
		 * Test: It should call the onClose callback when the modal is closed.
		 * Why: This ensures that any cleanup actions or follow-up logic provided in the
		 * `onClose` callback are correctly executed after the user dismisses the modal.
		 */
		it( 'should call the onClose callback when the modal is closed', () => {
			const onCloseCallback = jest.fn();
			showErrorModal( {
				message: 'Callback test',
				onClose: onCloseCallback,
			} );

			const okButton = document.querySelector(
				'#wc-ai-rr-error-modal-ok'
			) as HTMLButtonElement;
			okButton.click();

			expect( onCloseCallback ).toHaveBeenCalledTimes( 1 );
		} );
	} );

	describe( 'hideErrorModal', () => {
		/**
		 * Test: It should hide the modal by setting its display style to 'none'.
		 * Why: This test verifies the direct functionality of the hide function, ensuring
		 * it correctly makes the modal invisible.
		 */
		it( 'should hide the modal by setting its display to "none"', () => {
			document.body.innerHTML = errorModalTemplate;
			const modal = document.querySelector(
				'.wc-ai-rr-error-modal'
			) as HTMLElement;
			modal.style.display = 'flex'; // Make it visible first.

			hideErrorModal();

			expect( modal.style.display ).toBe( 'none' );
		} );

		/**
		 * Test: It should not throw an error if the modal is not in the DOM.
		 * Why: This ensures the function is safe and won't cause script errors if called
		 * when the modal is not present on the page.
		 */
		it( 'should not throw an error if the modal does not exist', () => {
			expect( () => hideErrorModal() ).not.toThrow();
		} );
	} );

	describe( 'showGenericError', () => {
		/**
		 * Test: It should display a modal with the message from a string input.
		 * Why: This verifies that the helper function correctly handles and displays
		 * simple string-based error messages.
		 */
		it( 'should show a modal with the message from a string', () => {
			const message = 'A simple error message.';
			showGenericError( message );

			const messageElement = document.querySelector(
				'.wc-ai-rr-error-modal__message'
			);
			expect( messageElement?.textContent ).toBe( message );
		} );

		/**
		 * Test: It should display a modal with the message from an Error object.
		 * Why: This ensures the function can correctly extract the `message` property from
		 * a standard JavaScript Error object and display it to the user.
		 */
		it( 'should show a modal with the message from an Error object', () => {
			const errorMessage = 'Error from an object.';
			const error = new Error( errorMessage );
			showGenericError( error );

			const messageElement = document.querySelector(
				'.wc-ai-rr-error-modal__message'
			);
			expect( messageElement?.textContent ).toBe( errorMessage );
		} );

		/**
		 * Test: It should prepend the context to the message if one is provided.
		 * Why: This test verifies that the `context` parameter works as intended, allowing
		 * developers to provide more specific, user-friendly error messages (e.g.,
		 * "Saving Settings: Network error").
		 */
		it( 'should prepend the context to the message if provided', () => {
			const context = 'AJAX Request Failed';
			const message = 'Server returned 500.';
			showGenericError( message, context );

			const messageElement = document.querySelector(
				'.wc-ai-rr-error-modal__message'
			);
			expect( messageElement?.textContent ).toBe(
				`${ context }: ${ message }`
			);
		} );

		/**
		 * Test: It should use a default message if an Error object has no message property.
		 * Why: This covers an edge case to ensure that even a poorly-formed Error object
		 * results in a sensible, generic message being displayed to the user, rather than
		 * an empty or broken modal.
		 */
		it( 'should use a default message if Error object has no message', () => {
			const error = new Error();
			( error as { message?: string } ).message = undefined; // Force undefined message for test
			showGenericError( error );
			const messageElement = document.querySelector(
				'.wc-ai-rr-error-modal__message'
			);
			expect( messageElement?.textContent ).toBe(
				'An unexpected error occurred.'
			);
		} );
	} );
} );
