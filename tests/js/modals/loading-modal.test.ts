/**
 * Unit tests for the loading modal functionality.
 *
 * @since 1.2.0
 */

/**
 * Internal dependencies
 */
import {
	showLoadingModal,
	hideLoadingModal,
} from '../../../src/modals/loading-modal';
import loadingModalTemplate from '../../../src/templates/loading-modal.html';

describe( 'Loading Modal', () => {
	// Before each test, reset the document body to a clean state.
	beforeEach( () => {
		document.body.innerHTML = '';
	} );

	describe( 'showLoadingModal', () => {
		/**
		 * Test: It should add the modal HTML to the body if it does not exist.
		 * Why: This ensures that the first time the function is called, it correctly
		 * injects the modal's HTML structure into the document so it can be displayed.
		 */
		it( 'should add the modal to the DOM if it is not already present', () => {
			// Arrange: The DOM is empty.

			// Act: Show the loading modal.
			showLoadingModal();

			// Assert: Check that the modal's main element is now in the body.
			const modal = document.querySelector( '.wc-ai-rr-loading-modal' );
			expect( modal ).not.toBeNull();
			// Also check that the injected HTML matches the template.
			expect( document.body.innerHTML ).toContain( loadingModalTemplate );
		} );

		/**
		 * Test: It should display the modal if it already exists but is hidden.
		 * Why: This covers the scenario where the modal was previously shown and then hidden.
		 * Calling the function again should make it visible without adding a duplicate to the DOM.
		 */
		it( 'should display the modal if it is already in the DOM', () => {
			// Arrange: Manually add the modal to the body and hide it.
			document.body.innerHTML = loadingModalTemplate;
			const modal = document.querySelector(
				'.wc-ai-rr-loading-modal'
			) as HTMLElement;
			modal.style.display = 'none';

			// Act: Show the loading modal.
			showLoadingModal();

			// Assert: Verify that the modal's display style is set to 'flex' to make it visible.
			expect( modal.style.display ).toBe( 'flex' );
		} );

		/**
		 * Test: It should not add a duplicate modal if one already exists.
		 * Why: This is an important check to prevent multiple instances of the modal from being
		 * added to the DOM, which could cause display issues and unexpected behavior.
		 */
		it( 'should not add a second modal if one is already present', () => {
			// Arrange: Show the modal once to add it to the DOM.
			showLoadingModal();

			// Act: Call the function a second time.
			showLoadingModal();

			// Assert: Check that there is still only one modal element in the document.
			const modals = document.querySelectorAll(
				'.wc-ai-rr-loading-modal'
			);
			expect( modals.length ).toBe( 1 );
		} );
	} );

	describe( 'hideLoadingModal', () => {
		/**
		 * Test: It should hide the modal by setting its display style to 'none'.
		 * Why: This verifies that the function correctly hides the modal, assuming
		 * it is already present and visible in the DOM.
		 */
		it( 'should hide the modal by setting its display to "none"', () => {
			// Arrange: Add the modal to the DOM and make sure it's visible.
			document.body.innerHTML = loadingModalTemplate;
			const modal = document.querySelector(
				'.wc-ai-rr-loading-modal'
			) as HTMLElement;
			modal.style.display = 'flex';

			// Act: Hide the modal.
			hideLoadingModal();

			// Assert: Check that the modal's display style is now 'none'.
			expect( modal.style.display ).toBe( 'none' );
		} );

		/**
		 * Test: It should not throw an error if the modal is not in the DOM.
		 * Why: This ensures the function is safe to call even if the modal element
		 * does not exist (e.g., if it's called before the modal is ever shown),
		 * preventing potential runtime errors.
		 */
		it( 'should not throw an error if the modal does not exist', () => {
			// Arrange: The DOM is empty.

			// Act & Assert: Expect no errors to be thrown when calling hideLoadingModal.
			expect( () => hideLoadingModal() ).not.toThrow();
		} );
	} );
} );
