/**
 * Admin review actions functionality for WC AI Review Responder.
 *
 * This is the main entry point that sets up event listeners for AI review
 * response generation in the WordPress admin.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import { handleAiResponseClick } from './handlers/review-action-handler';
// import { showErrorModal, showGenericError } from './modals/error-modal';

document.addEventListener( 'DOMContentLoaded', (): void => {
	const aiResponseLinks: NodeListOf< HTMLAnchorElement > =
		document.querySelectorAll( '.ai-generate-response' );

	aiResponseLinks.forEach( ( link: HTMLAnchorElement ): void => {
		link.addEventListener( 'click', ( e: Event ): void => {
			e.preventDefault();
			handleAiResponseClick( link );
		} );
	} );

	// Temporary test function for error modal - remove in production
	// Uncomment the line below to test the error modal
	// showErrorModal( { title: 'Test Error', message: 'This is a test error message to verify the modal works correctly.' } );
} );
