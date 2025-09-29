/**
 * Prompt selection modal functionality for admin review actions.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import promptModalTemplate from '../templates/prompt-selection-modal.html';
import { Template } from '../types/admin-types';

/**
 * Shows the prompt selection modal.
 *
 * @param {() => void} onGenerate - Callback function to execute when the generate button is clicked.
 * @param {() => void} onCancel - Callback function to execute when the cancel button is clicked.
 */
export function showPromptModal(
	onGenerate: () => void,
	onCancel: () => void
): void {
	// Insert the modal HTML if it doesn't exist
	if ( ! document.querySelector( '.wc-ai-prompt-modal' ) ) {
		document.body.insertAdjacentHTML( 'beforeend', promptModalTemplate );
	}

	const modal = document.querySelector(
		'.wc-ai-prompt-modal'
	) as HTMLElement;
	const select = modal.querySelector(
		'#wc-ai-prompt-modal-select'
	) as HTMLSelectElement;
	const generateButton = modal.querySelector(
		'#wc-ai-prompt-modal-generate'
	) as HTMLButtonElement;
	const cancelButton = modal.querySelector(
		'#wc-ai-prompt-modal-cancel'
	) as HTMLButtonElement;
	const overlay = modal.querySelector(
		'.wc-ai-prompt-modal__overlay'
	) as HTMLElement;

	// Populate select options
	select.innerHTML = ''; // Clear existing options
	wcAiReviewResponder.templates.forEach( ( template: Template ) => {
		const option = document.createElement( 'option' );
		option.value = template.value;
		option.textContent = template.label;
		select.appendChild( option );
	} );

	// Event listeners for buttons
	let generateClickHandler: ( () => void ) | null = null;
	let cancelClickHandler: ( () => void ) | null = null;

	const cleanup = () => {
		if ( generateClickHandler ) {
			generateButton.removeEventListener( 'click', generateClickHandler );
		}
		if ( cancelClickHandler ) {
			cancelButton.removeEventListener( 'click', cancelClickHandler );
			overlay.removeEventListener( 'click', cancelClickHandler );
		}
		modal.style.display = 'none';
	};

	generateClickHandler = () => {
		onGenerate();
		cleanup();
	};

	cancelClickHandler = () => {
		onCancel();
		cleanup();
	};

	generateButton.addEventListener( 'click', generateClickHandler );
	cancelButton.addEventListener( 'click', cancelClickHandler );
	overlay.addEventListener( 'click', cancelClickHandler );

	modal.style.display = 'flex';
}

/**
 * Gets the selected template from the prompt modal.
 *
 * @return {string} The selected template value.
 */
export function getSelectedTemplate(): string {
	const select = document.querySelector(
		'#wc-ai-prompt-modal-select'
	) as HTMLSelectElement;
	return select ? select.value : 'default';
}
