/**
 * Prompt selection modal functionality for admin review actions.
 *
 * @since 1.0.0
 */

/**
 * Internal dependencies
 */
import promptModalTemplate from '../templates/prompt-selection-modal.html';
import { Template, Mood } from '../types/admin-types';

/**
 * Shows the prompt selection modal.
 *
 * @param {() => void} onGenerate - Callback function to execute when the generate button is clicked.
 * @param {() => void} onCancel - Callback function to execute when the cancel button is clicked.
 * @param {string} [suggestedTemplate] - Optional suggested template to pre-select.
 * @param {string} [suggestedMood] - Optional suggested mood to pre-select.
 * @param {boolean} [suggestionFailed] - Optional flag to indicate if suggestions failed.
 */
export function showPromptModal(
	onGenerate: () => void,
	onCancel: () => void,
	suggestedTemplate?: string,
	suggestedMood?: string,
	suggestionFailed?: boolean
): void {
	// Insert the modal HTML if it doesn't exist
	if ( ! document.querySelector( '.wc-ai-rr-prompt-modal' ) ) {
		document.body.insertAdjacentHTML( 'beforeend', promptModalTemplate );
	}

	const modal = document.querySelector(
		'.wc-ai-rr-prompt-modal'
	) as HTMLElement;
	const templateSelect = modal.querySelector(
		'#wc-ai-rr-prompt-modal-template-select'
	) as HTMLSelectElement;
	const moodSelect = modal.querySelector(
		'#wc-ai-rr-prompt-modal-mood-select'
	) as HTMLSelectElement;
	const generateButton = modal.querySelector(
		'#wc-ai-rr-prompt-modal-generate'
	) as HTMLButtonElement;
	const cancelButton = modal.querySelector(
		'#wc-ai-rr-prompt-modal-cancel'
	) as HTMLButtonElement;
	const suggestionText = modal.querySelector(
		'.wc-ai-rr-prompt-modal__suggestion'
	) as HTMLElement;
	const suggestionFailureText = modal.querySelector(
		'.wc-ai-rr-prompt-modal__suggestion-failure'
	) as HTMLElement;
	const overlay = modal.querySelector(
		'.wc-ai-rr-prompt-modal__overlay'
	) as HTMLElement;

	// Populate template select options
	templateSelect.innerHTML = ''; // Clear existing options
	window.wcAiReviewResponder.templates.forEach( ( template: Template ) => {
		const option = document.createElement( 'option' );
		option.value = template.value;
		option.textContent = template.label;
		templateSelect.appendChild( option );
	} );

	// Populate mood select options
	moodSelect.innerHTML = ''; // Clear existing options
	window.wcAiReviewResponder.moods.forEach( ( mood: Mood ) => {
		const option = document.createElement( 'option' );
		option.value = mood.value;
		option.textContent = mood.label;
		moodSelect.appendChild( option );
	} );

	// Pre-select suggested options and show suggestion text if provided
	if ( suggestedTemplate && suggestedMood ) {
		templateSelect.value = suggestedTemplate;
		moodSelect.value = suggestedMood;
		suggestionText.style.display = 'block';
	} else {
		suggestionText.style.display = 'none';
	}

	if ( suggestionFailed ) {
		suggestionFailureText.style.display = 'block';
	} else {
		suggestionFailureText.style.display = 'none';
	}

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
		// Also hide suggestion text on cleanup
		suggestionText.style.display = 'none';
		suggestionFailureText.style.display = 'none';
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
		'#wc-ai-rr-prompt-modal-template-select'
	) as HTMLSelectElement;
	return select ? select.value : 'default';
}

/**
 * Gets the selected mood from the prompt modal.
 *
 * @return {string} The selected mood value.
 */
export function getSelectedMood(): string {
	const select = document.querySelector(
		'#wc-ai-rr-prompt-modal-mood-select'
	) as HTMLSelectElement;
	return select ? select.value : 'empathetic_problem_solver';
}
