/**
 * Loading modal functionality for admin review actions.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import loadingModalTemplate from '../templates/loading-modal.html';

/**
 * Creates the loading modal HTML content with improved spinner
 *
 * @return {string} The HTML content for the loading modal
 */
function createLoadingModalHTML(): string {
	return loadingModalTemplate;
}

/**
 * Shows a loading modal over the reply box
 */
export function showLoadingModal(): void {
	// Check if modal already exists
	const modal = document.querySelector(
		'.wc-ai-loading-modal'
	) as HTMLElement;
	if ( modal ) {
		modal.style.display = 'flex';
		return;
	}

	// Insert the modal HTML directly into the body
	document.body.insertAdjacentHTML( 'beforeend', createLoadingModalHTML() );
}

/**
 * Hides the loading modal
 */
export function hideLoadingModal(): void {
	const modal = document.querySelector(
		'.wc-ai-loading-modal'
	) as HTMLElement;
	if ( modal ) {
		modal.style.display = 'none';
	}
}
