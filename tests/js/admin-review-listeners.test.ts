/**
 * Unit tests for the admin review listeners.
 *
 * @since 1.2.0
 */
import { handleAiResponseClick } from '../../src/handlers/review-action-handler';

// Mock the handler module that the listeners will call.
jest.mock( '../../src/handlers/review-action-handler', () => ( {
	handleAiResponseClick: jest.fn(),
} ) );

describe( 'Admin Review Listeners', () => {
	// Helper function to simulate the DOMContentLoaded event, which triggers the script.
	const fireDOMContentLoaded = () => {
		const event = new Event( 'DOMContentLoaded', {
			bubbles: true,
			cancelable: true,
		} );
		document.dispatchEvent( event );
	};

	beforeEach( () => {
		// Reset the DOM and mock history before each test.
		document.body.innerHTML = '';
		( handleAiResponseClick as jest.Mock ).mockClear();
		// We need to re-import the module for each test to ensure the event listener
		// is re-registered in the fresh JSDOM environment provided by each test.
		jest.isolateModules( () => {
			require( '../../src/admin-review-listeners' );
		} );
	} );

	/**
	 * Test: It should find all AI response links and attach click event listeners.
	 * Why: This is the core purpose of the script. This test verifies that the script
	 * correctly identifies all the target links on the page and wires them up to the
	 * event handling logic.
	 */
	it( 'should add click event listeners to all ai-generate-response links', () => {
		// Arrange: Create multiple mock links in the DOM that the script should find.
		const link1 = document.createElement( 'a' );
		link1.className = 'ai-generate-response';
		const link2 = document.createElement( 'a' );
		link2.className = 'ai-generate-response';
		const otherLink = document.createElement( 'a' ); // A link that should be ignored.
		otherLink.className = 'some-other-link';
		document.body.append( link1, link2, otherLink );

		// Act: Fire the DOMContentLoaded event to trigger the script.
		fireDOMContentLoaded();

		// Simulate a user click on each of the target links.
		link1.click();
		link2.click();

		// Assert: Check that our mocked handler was called for each click.
		expect( handleAiResponseClick ).toHaveBeenCalledTimes( 2 );
		expect( handleAiResponseClick ).toHaveBeenCalledWith( link1 );
		expect( handleAiResponseClick ).toHaveBeenCalledWith( link2 );
	} );

	/**
	 * Test: It should call the handler and prevent the default link action on click.
	 * Why: This test ensures that when a link is clicked, not only is our handler
	 * called, but the browser's default behavior (like navigating to the link's href)
	 * is correctly prevented, which is essential for an AJAX-driven interaction.
	 */
	it( 'should call handleAiResponseClick and prevent default on click', () => {
		// Arrange: Create a single target link.
		const link = document.createElement( 'a' );
		link.className = 'ai-generate-response';
		document.body.appendChild( link );

		fireDOMContentLoaded();

		// Act: Dispatch a new click event that we can inspect.
		const clickEvent = new MouseEvent( 'click', {
			bubbles: true,
			cancelable: true,
		} );
		const preventDefaultSpy = jest.spyOn( clickEvent, 'preventDefault' );
		link.dispatchEvent( clickEvent );

		// Assert: The handler should have been called with the correct link element.
		expect( handleAiResponseClick ).toHaveBeenCalledWith( link );
		// Assert that the default browser action was prevented.
		expect( preventDefaultSpy ).toHaveBeenCalled();
	} );

	/**
	 * Test: It should not throw errors or attach listeners if no matching links are found.
	 * Why: This is a sanity check to ensure the script runs safely and doesn't crash
	 * if it's loaded on a page that doesn't contain any of the target "Generate Response" links.
	 */
	it( 'should do nothing if no ai-generate-response links are found', () => {
		// Arrange: The body is empty, with no matching links.

		// Act: Fire the event.
		fireDOMContentLoaded();

		// Assert: The click handler should not have been called.
		expect( handleAiResponseClick ).not.toHaveBeenCalled();
	} );
} );

