/**
 * Unit tests for the AJAX request handler.
 *
 * @since 1.2.0
 */

import {
	generateAiResponse,
	getAiSuggestions,
} from '../../../src/api/ajax-handler';
import {
	AiResponseData,
	AiSuggestionsResponseData,
} from '../../../src/types/admin-types';

// Mock the global fetch function before all tests.
global.fetch = jest.fn();

describe( 'AJAX Handler', () => {
	// Common test data.
	const commentId = '123';
	const nonce = 'a-nonce';

	beforeEach( () => {
		// Clear mock history before each test.
		( global.fetch as jest.Mock ).mockClear();
	} );

	describe( 'generateAiResponse', () => {
		/**
		 * Test: It should make a POST request with the correct form data.
		 * Why: This test verifies that the function correctly constructs the FormData
		 * object and sends it to the WordPress AJAX endpoint with the appropriate
		 * action and all required parameters.
		 */
		it( 'should call fetch with the correct action and form data', async () => {
			// Arrange
			const template = 'test-template';
			const mood = 'test-mood';
			const mockResponse: AiResponseData = {
				success: true,
				data: { reply: 'AI reply' },
			};
			( global.fetch as jest.Mock ).mockResolvedValue( {
				json: () => Promise.resolve( mockResponse ),
			} );

			// Act
			await generateAiResponse( commentId, template, mood, nonce );

			// Assert
			// Check that fetch was called exactly once.
			expect( fetch ).toHaveBeenCalledTimes( 1 );

			// Check the URL it was called with.
			expect( fetch ).toHaveBeenCalledWith(
				window.wcAiReviewResponder.ajaxurl,
				expect.any( Object )
			);

			// Check the request body details.
			const fetchCall = ( global.fetch as jest.Mock ).mock.calls[ 0 ];
			const requestBody = fetchCall[ 1 ].body as FormData;
			expect( fetchCall[ 1 ].method ).toBe( 'POST' );
			expect( requestBody.get( 'action' ) ).toBe( 'generate_ai_response' );
			expect( requestBody.get( 'comment_id' ) ).toBe( commentId );
			expect( requestBody.get( 'template' ) ).toBe( template );
			expect( requestBody.get( 'mood' ) ).toBe( mood );
			expect( requestBody.get( '_wpnonce' ) ).toBe( nonce );
		} );

		/**
		 * Test: It should return the JSON response from the server.
		 * Why: This test ensures that the function correctly parses the JSON response
		 * from the fetch call and returns it to the caller, which is the expected
		 * successful outcome of the function.
		 */
		it( 'should return the parsed JSON response on success', async () => {
			// Arrange
			const mockResponse: AiResponseData = {
				success: true,
				data: { reply: 'Test successful' },
			};
			( global.fetch as jest.Mock ).mockResolvedValue( {
				json: () => Promise.resolve( mockResponse ),
			} );

			// Act
			const result = await generateAiResponse( commentId, 't', 'm', nonce );

			// Assert
			expect( result ).toEqual( mockResponse );
		} );
	} );

	describe( 'getAiSuggestions', () => {
		/**
		 * Test: It should make a POST request with the correct form data for suggestions.
		 * Why: This test verifies that the function sends the correct action and
		 * comment ID to the AJAX endpoint for getting suggestions, ensuring the
		 * backend can identify and process the request correctly.
		 */
		it( 'should call fetch with the correct action and form data', async () => {
			// Arrange
			const mockResponse: AiSuggestionsResponseData = {
				success: true,
				data: { template: 'suggested-template', mood: 'suggested-mood' },
			};
			( global.fetch as jest.Mock ).mockResolvedValue( {
				json: () => Promise.resolve( mockResponse ),
			} );

			// Act
			await getAiSuggestions( commentId, nonce );

			// Assert
			expect( fetch ).toHaveBeenCalledTimes( 1 );
			expect( fetch ).toHaveBeenCalledWith(
				window.wcAiReviewResponder.ajaxurl,
				expect.any( Object )
			);

			const fetchCall = ( global.fetch as jest.Mock ).mock.calls[ 0 ];
			const requestBody = fetchCall[ 1 ].body as FormData;
			expect( fetchCall[ 1 ].method ).toBe( 'POST' );
			expect( requestBody.get( 'action' ) ).toBe( 'get_ai_suggestions' );
			expect( requestBody.get( 'comment_id' ) ).toBe( commentId );
			expect( requestBody.get( '_wpnonce' ) ).toBe( nonce );
		} );

		/**
		 * Test: It should return the JSON response from the server for suggestions.
		 * Why: This test ensures that the function correctly parses and returns the
		 * suggestion data from the server's JSON response, allowing the UI to
		 * pre-select the suggested options.
		 */
		it( 'should return the parsed JSON response on success', async () => {
			// Arrange
			const mockResponse: AiSuggestionsResponseData = {
				success: true,
				data: { mood: 'happy', template: 'positive-review' },
			};
			( global.fetch as jest.Mock ).mockResolvedValue( {
				json: () => Promise.resolve( mockResponse ),
			} );

			// Act
			const result = await getAiSuggestions( commentId, nonce );

			// Assert
			expect( result ).toEqual( mockResponse );
		} );
	} );
} );

