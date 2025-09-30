/**
 * Unit tests for the review action handler.
 *
 * This test suite covers the complex logic within `handleAiResponseClick`,
 * ensuring that all dependencies are called correctly and the UI state is
 * managed as expected throughout the process of generating an AI response.
 *
 * @since 1.2.0
 */

// Mock dependencies from other modules.
jest.mock( '../../../src/utils/wordpress-utils', () => ( {
	triggerWordPressReply: jest.fn(),
	updateReplyTextarea: jest.fn(),
} ) );
jest.mock( '../../../src/modals/loading-modal', () => ( {
	showLoadingModal: jest.fn(),
	hideLoadingModal: jest.fn(),
} ) );
jest.mock( '../../../src/modals/prompt-modal', () => ( {
	showPromptModal: jest.fn(),
	getSelectedTemplate: jest.fn().mockReturnValue( 'selected-template' ),
	getSelectedMood: jest.fn().mockReturnValue( 'selected-mood' ),
} ) );
jest.mock( '../../../src/modals/error-modal', () => ( {
	showGenericError: jest.fn(),
} ) );
jest.mock( '../../../src/api/ajax-handler', () => ( {
	generateAiResponse: jest.fn(),
	getAiSuggestions: jest.fn(),
} ) );

import { handleAiResponseClick } from '../../../src/handlers/review-action-handler';
import {
	triggerWordPressReply,
	updateReplyTextarea,
} from '../../../src/utils/wordpress-utils';
import {
	showLoadingModal,
	hideLoadingModal,
} from '../../../src/modals/loading-modal';
import {
	showPromptModal,
	getSelectedTemplate,
	getSelectedMood,
} from '../../../src/modals/prompt-modal';
import { showGenericError } from '../../../src/modals/error-modal';
import {
	generateAiResponse,
	getAiSuggestions,
} from '../../../src/api/ajax-handler';
import { AiSuggestionsResponseData } from '../../../src/types/admin-types';

describe( 'handleAiResponseClick', () => {
	let link: HTMLAnchorElement;

	// Before each test, create a fresh mock link element and clear all mocks.
	beforeEach( () => {
		jest.clearAllMocks();
		link = document.createElement( 'a' );
		link.setAttribute( 'data-comment-id', '123' );
		link.setAttribute( 'data-suggest-nonce', 'suggest-nonce-123' );
		link.setAttribute( 'data-generate-nonce', 'generate-nonce-123' );
		link.textContent = 'Generate Response';
		document.body.appendChild( link );
	} );

	// Cleanup the link from the DOM after each test.
	afterEach( () => {
		if ( document.body.contains( link ) ) {
			document.body.removeChild( link );
		}
	} );

	/**
	 * Test: It should show an error if required data attributes are missing.
	 * This test ensures that the function fails gracefully and informs the user
	 * if the link is not configured correctly.
	 */
	it( 'should show an error if data attributes are missing', () => {
		// Arrange: Remove a required attribute.
		link.removeAttribute( 'data-comment-id' );

		// Act: Call the handler.
		handleAiResponseClick( link );

		// Assert: Verify that an error is shown and the process stops.
		expect( showGenericError ).toHaveBeenCalledWith(
			'Missing required data attributes. Please refresh the page and try again.',
			'Configuration Error'
		);
		expect( triggerWordPressReply ).not.toHaveBeenCalled();
	} );

	/**
	 * Test: It should trigger the WordPress reply box.
	 * The first step in the process should be to open the native comment reply UI.
	 */
	it( 'should trigger the WordPress reply box', () => {
		handleAiResponseClick( link );
		expect( triggerWordPressReply ).toHaveBeenCalledWith( '123' );
	} );

	/**
	 * Test: It should manage the link's state to provide user feedback.
	 * This test checks that the link's text is updated to "Getting suggestions..."
	 * and that it's temporarily disabled to prevent multiple clicks.
	 */
	it( 'should update the link text and disable pointer events', () => {
		handleAiResponseClick( link );
		expect( link.textContent ).toBe( 'Getting suggestions...' );
		expect( link.style.pointerEvents ).toBe( 'none' );
	} );

	/**
	 * Test: It should show the loading modal while fetching suggestions.
	 * This provides immediate visual feedback to the user that something is happening.
	 */
	it( 'should show the loading modal while fetching suggestions', () => {
		handleAiResponseClick( link );
		expect( showLoadingModal ).toHaveBeenCalled();
	} );

	/**
	 * Test: It should call getAiSuggestions with the correct parameters.
	 * This verifies that the handler correctly passes the comment ID and nonce
	 * to the API layer.
	 */
	it( 'should call getAiSuggestions with correct commentId and nonce', () => {
		handleAiResponseClick( link );
		expect( getAiSuggestions ).toHaveBeenCalledWith( '123', 'suggest-nonce-123' );
	} );

	describe( 'When AI suggestions are fetched successfully', () => {
		// Helper function to simulate a successful API call for suggestions.
		const setupSuccessfulSuggestion = () => {
			const suggestionResponse: AiSuggestionsResponseData = {
				success: true,
				data: { template: 'suggested-template', mood: 'suggested-mood' },
			};
			( getAiSuggestions as jest.Mock ).mockResolvedValue( suggestionResponse );
		};

		/**
		 * Test: It should hide the loading modal and show the prompt modal.
		 * Once suggestions are received, the loading indicator should be replaced
		 * by the prompt selection modal.
		 */
		it( 'should hide loading modal and show prompt modal on success', async () => {
			setupSuccessfulSuggestion();
			handleAiResponseClick( link );
			await Promise.resolve(); // Wait for promises to resolve.

			expect( hideLoadingModal ).toHaveBeenCalled();
			expect( showPromptModal ).toHaveBeenCalled();
		} );

		/**
		 * Test: It should pass the fetched suggestions to the prompt modal.
		 * This ensures that the suggested template and mood are correctly pre-selected
		 * in the UI.
		 */
		it( 'should pass suggestions to the prompt modal', async () => {
			setupSuccessfulSuggestion();
			handleAiResponseClick( link );
			await Promise.resolve();

			expect( showPromptModal ).toHaveBeenCalledWith(
				expect.any( Function ), // onGenerate
				expect.any( Function ), // onCancel
				'suggested-template',
				'suggested-mood',
				false // suggestionFailed
			);
		} );
	} );

	describe( 'When fetching AI suggestions fails', () => {
		/**
		 * Test: It should show the prompt modal with a failure flag.
		 * This test checks both API failure (success: false) and network errors.
		 */
		it.each( [
			[ 'API returns success: false', Promise.resolve( { success: false, data: {} } ) ],
			[ 'Network error', Promise.reject( new Error( 'Network error' ) ) ],
		] )(
			'should show prompt modal with failure flag when %s',
			async ( _, promise ) => {
				( getAiSuggestions as jest.Mock ).mockReturnValue( promise );
				handleAiResponseClick( link );
				await Promise.resolve(); // Let the promise resolve/reject
				await new Promise( setImmediate ); // Wait for the .catch/.then chain

				expect( hideLoadingModal ).toHaveBeenCalled();
				expect( showPromptModal ).toHaveBeenCalledWith(
					expect.any( Function ),
					expect.any( Function ),
					undefined,
					undefined,
					true // suggestionFailed
				);
			}
		);
	} );

	describe( 'User interaction with the Prompt Modal', () => {
		// Helper to get the callbacks passed to `showPromptModal`
		const getModalCallbacks = async () => {
			( getAiSuggestions as jest.Mock ).mockResolvedValue( { success: true, data: {} } );
			handleAiResponseClick( link );
			await Promise.resolve();
			const mockCalls = ( showPromptModal as jest.Mock ).mock.calls;
			const [ handleGenerate, handleCancel ] = mockCalls[ 0 ];
			return { handleGenerate, handleCancel };
		};

		/**
		 * Test: When the user clicks "Generate".
		 * This block tests the entire flow after the user confirms their selection
		 * in the prompt modal.
		 */
		describe( 'on Generate', () => {
			/**
			 * Test: It should show the loading modal again and call generateAiResponse.
			 * The UI should show a loading state while the final response is being generated.
			 */
			it( 'should show loading modal and call generateAiResponse', async () => {
				const { handleGenerate } = await getModalCallbacks();
				( generateAiResponse as jest.Mock ).mockResolvedValue( { success: false, data: {} } );

				await handleGenerate();

				expect( showLoadingModal ).toHaveBeenCalledTimes( 2 ); // Once for suggestions, once for generation
				expect( generateAiResponse ).toHaveBeenCalledWith(
					'123',
					'selected-template', // From mock
					'selected-mood',     // From mock
					'generate-nonce-123'
				);
				expect( getSelectedTemplate ).toHaveBeenCalled();
				expect( getSelectedMood ).toHaveBeenCalled();
			} );

			/**
			 * Test: It should update the reply textarea on a successful response.
			 * This is the primary success outcome of the entire feature.
			 */
			it( 'should update reply textarea on successful response', async () => {
				const { handleGenerate } = await getModalCallbacks();
				( generateAiResponse as jest.Mock ).mockResolvedValue( {
					success: true,
					data: { reply: 'AI generated text' },
				} );
				( updateReplyTextarea as jest.Mock ).mockReturnValue( true );

				await handleGenerate();

				expect( updateReplyTextarea ).toHaveBeenCalledWith( 'AI generated text' );
			} );

			/**
			 * Test: It should show an error if updating the textarea fails.
			 * This covers cases where the reply box might have been closed by the user
			 * after starting the process.
			 */
			it( 'should show an error if updateReplyTextarea fails', async () => {
				const { handleGenerate } = await getModalCallbacks();
				( generateAiResponse as jest.Mock ).mockResolvedValue( {
					success: true,
					data: { reply: 'AI text' },
				} );
				( updateReplyTextarea as jest.Mock ).mockReturnValue( false );

				await handleGenerate();

				expect( showGenericError ).toHaveBeenCalledWith(
					'Could not find the reply textarea. Please make sure the reply box is open and try again.',
					'Interface Error'
				);
			} );

			/**
			 * Test: It should show a server error if the API returns success: false.
			 * This handles cases where the backend fails to generate a response.
			 */
			it( 'should show server error if API response is not successful', async () => {
				const { handleGenerate } = await getModalCallbacks();
				( generateAiResponse as jest.Mock ).mockResolvedValue( {
					success: false,
					data: { message: 'API credit exhausted' },
				} );

				await handleGenerate();

				expect( showGenericError ).toHaveBeenCalledWith(
					'API credit exhausted',
					'Server Error'
				);
			} );

			/**
			 * Test: It should show a generic error if the generate call fails (e.g., network error).
			 * This tests the catch block for the `generateAiResponse` call.
			 */
			it( 'should show generic error on generate call failure', async () => {
				const { handleGenerate } = await getModalCallbacks();
				const error = new Error( 'Network Failure' );
				( generateAiResponse as jest.Mock ).mockRejectedValue( error );

				await handleGenerate();

				expect( showGenericError ).toHaveBeenCalledWith(
					error,
					'Failed to generate AI response'
				);
			} );

			/**
			 * Test: It should always hide the loading modal and restore the link state.
			 * This `finally` block behavior is critical to ensure the UI is never left in a
			 * permanent loading state.
			 */
			it( 'should hide loading modal and restore link state in all generate scenarios', async () => {
				const { handleGenerate } = await getModalCallbacks();
				( generateAiResponse as jest.Mock ).mockRejectedValue( new Error( 'any error' ) );

				await handleGenerate();

				expect( hideLoadingModal ).toHaveBeenCalled();
				expect( link.textContent ).toBe( 'Generate Response' );
				expect( link.style.pointerEvents ).toBe( 'auto' );
			} );
		} );

		/**
		 * Test: When the user clicks "Cancel".
		 * The cancel action should simply restore the UI to its original state.
		 */
		describe( 'on Cancel', () => {
			it( 'should restore the link state', async () => {
				const { handleCancel } = await getModalCallbacks();
				// The link text is 'Getting suggestions...' at this point.
				handleCancel();
				expect( link.textContent ).toBe( 'Generate Response' );
				expect( link.style.pointerEvents ).toBe( 'auto' );
			} );
		} );
	} );
} );
