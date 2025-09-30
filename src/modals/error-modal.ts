/**
 * Error modal functionality for displaying user-friendly error messages.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import errorModalTemplate from '../templates/error-modal.html';
import { __ } from '../utils/i18n';

/**
 * Interface for error modal options
 */
interface ErrorModalOptions {
	title?: string;
	message: string;
	onClose?: () => void;
}

/**
 * Shows an error modal with the specified message and optional title.
 *
 * @param {ErrorModalOptions} options - The error modal configuration options
 */
export function showErrorModal( options: ErrorModalOptions ): void {
	const { title = __( 'error' ), message, onClose } = options;

	// Insert the modal HTML if it doesn't exist
	if ( ! document.querySelector( '.wc-ai-rr-error-modal' ) ) {
		document.body.insertAdjacentHTML( 'beforeend', errorModalTemplate );
	}

	const modal = document.querySelector(
		'.wc-ai-rr-error-modal'
	) as HTMLElement;
	const titleElement = modal.querySelector(
		'.wc-ai-rr-error-modal__title'
	) as HTMLElement;
	const messageElement = modal.querySelector(
		'.wc-ai-rr-error-modal__message'
	) as HTMLElement;
	const okButton = modal.querySelector(
		'#wc-ai-rr-error-modal-ok'
	) as HTMLButtonElement;
	const overlay = modal.querySelector(
		'.wc-ai-rr-error-modal__overlay'
	) as HTMLElement;

	// Set the content
	titleElement.textContent = title;
	messageElement.textContent = message;

	// Event listeners for closing the modal
	let okClickHandler: ( () => void ) | null = null;
	let overlayClickHandler: ( () => void ) | null = null;

	const cleanup = () => {
		if ( okClickHandler ) {
			okButton.removeEventListener( 'click', okClickHandler );
		}
		if ( overlayClickHandler ) {
			overlay.removeEventListener( 'click', overlayClickHandler );
		}
		modal.style.display = 'none';

		// Call the onClose callback if provided
		if ( onClose ) {
			onClose();
		}
	};

	okClickHandler = () => {
		cleanup();
	};

	overlayClickHandler = () => {
		cleanup();
	};

	okButton.addEventListener( 'click', okClickHandler );
	overlay.addEventListener( 'click', overlayClickHandler );

	// Show the modal
	modal.style.display = 'flex';
}

/**
 * Hides the error modal if it's currently visible.
 */
export function hideErrorModal(): void {
	const modal = document.querySelector(
		'.wc-ai-rr-error-modal'
	) as HTMLElement;
	if ( modal ) {
		modal.style.display = 'none';
	}
}

/**
 * Shows an error modal for a generic error with a user-friendly message.
 *
 * @param {Error | string} error   - The error object or error message
 * @param {string}         context - Optional context about where the error occurred
 */
export function showGenericError(
	error: Error | string,
	context?: string
): void {
	let message: string;

	if ( error instanceof Error ) {
		message = error.message || __( 'unexpectedError' );
	} else {
		message = error;
	}

	// Add context if provided
	if ( context ) {
		message = `${ context }: ${ message }`;
	}

	showErrorModal( {
		title: __( 'somethingWentWrong' ),
		message,
	} );
}
