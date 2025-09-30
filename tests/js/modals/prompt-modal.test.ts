/**
 * Unit tests for the prompt selection modal.
 *
 * @since 1.2.0
 */

/**
 * Internal dependencies
 */
import {
	showPromptModal,
	getSelectedTemplate,
	getSelectedMood,
} from '../../../src/modals/prompt-modal';

describe( 'Prompt Modal', () => {
	// Mock callbacks for generate and cancel actions to spy on them.
	const onGenerateMock = jest.fn();
	const onCancelMock = jest.fn();

	beforeEach( () => {
		// Reset the DOM and clear mock history before each test.
		document.body.innerHTML = '';
		onGenerateMock.mockClear();
		onCancelMock.mockClear();
	} );

	describe( 'showPromptModal', () => {
		/**
		 * Test: It should add the modal to the DOM if it is not already present.
		 * Why: This ensures the modal's HTML structure is injected into the page on the
		 * first call, making it available to the user.
		 */
		it( 'should add the modal to the DOM if not present', () => {
			showPromptModal( onGenerateMock, onCancelMock );
			const modal = document.querySelector( '.wc-ai-rr-prompt-modal' );
			expect( modal ).not.toBeNull();
		} );

		/**
		 * Test: It should populate the template and mood select options from the global object.
		 * Why: This verifies that the dropdowns are correctly filled with the available
		 * templates and moods provided by the backend via the `wcAiReviewResponder` global object.
		 */
		it( 'should populate template and mood select options', () => {
			showPromptModal( onGenerateMock, onCancelMock );
			const templateSelect = document.querySelector(
				'#wc-ai-rr-prompt-modal-template-select'
			) as HTMLSelectElement;
			const moodSelect = document.querySelector(
				'#wc-ai-rr-prompt-modal-mood-select'
			) as HTMLSelectElement;

			// Assumes the global mock in setup.ts has 2 templates and 2 moods.
			expect( templateSelect.options.length ).toBe( 2 );
			expect( moodSelect.options.length ).toBe( 2 );
			expect( templateSelect.options[ 0 ].textContent ).toBe(
				'Template 1'
			);
			expect( moodSelect.options[ 0 ].textContent ).toBe( 'Mood 1' );
		} );

		/**
		 * Test: It should pre-select the suggested template and mood if they are provided.
		 * Why: This test checks that when AI suggestions are available, the function
		 * correctly sets the initial values of the dropdowns to guide the user.
		 */
		it( 'should pre-select suggested template and mood', () => {
			const suggestedTemplate = 'template2';
			const suggestedMood = 'mood2';

			showPromptModal(
				onGenerateMock,
				onCancelMock,
				suggestedTemplate,
				suggestedMood
			);

			const templateSelect = document.querySelector(
				'#wc-ai-rr-prompt-modal-template-select'
			) as HTMLSelectElement;
			const moodSelect = document.querySelector(
				'#wc-ai-rr-prompt-modal-mood-select'
			) as HTMLSelectElement;

			expect( templateSelect.value ).toBe( suggestedTemplate );
			expect( moodSelect.value ).toBe( suggestedMood );
		} );

		/**
		 * Test: It should show the suggestion text when suggestions are provided.
		 * Why: This verifies that the informational text, which tells the user that the
		 * pre-selected options are AI suggestions, is made visible.
		 */
		it( 'should show suggestion text when suggestions are provided', () => {
			showPromptModal(
				onGenerateMock,
				onCancelMock,
				'template1',
				'mood1'
			);
			const suggestionText = document.querySelector(
				'.wc-ai-rr-prompt-modal__suggestion'
			) as HTMLElement;
			expect( suggestionText.style.display ).toBe( 'block' );
		} );

		/**
		 * Test: It should show the suggestion failure text when the suggestionFailed flag is true.
		 * Why: This verifies that if the AI suggestion process fails, a specific failure
		 * message is shown to the user so they know to make a manual selection.
		 */
		it( 'should show suggestion failure text when suggestionFailed is true', () => {
			showPromptModal(
				onGenerateMock,
				onCancelMock,
				undefined,
				undefined,
				true
			);
			const failureText = document.querySelector(
				'.wc-ai-rr-prompt-modal__suggestion-failure'
			) as HTMLElement;
			expect( failureText.style.display ).toBe( 'block' );
		} );

		/**
		 * Test: It should call the onGenerate callback when the "Generate" button is clicked.
		 * Why: This ensures that the primary action of the modal (triggering the AI response
		 * generation) is correctly wired up to the callback function provided by the handler.
		 */
		it( 'should call onGenerate when the generate button is clicked', () => {
			showPromptModal( onGenerateMock, onCancelMock );
			const generateButton = document.querySelector(
				'#wc-ai-rr-prompt-modal-generate'
			) as HTMLButtonElement;
			generateButton.click();
			expect( onGenerateMock ).toHaveBeenCalledTimes( 1 );
		} );

		/**
		 * Test: It should call the onCancel callback when the "Cancel" button is clicked.
		 * Why: This test verifies that the cancellation action is correctly handled, allowing
		 * the user to safely exit the workflow.
		 */
		it( 'should call onCancel when the cancel button is clicked', () => {
			showPromptModal( onGenerateMock, onCancelMock );
			const cancelButton = document.querySelector(
				'#wc-ai-rr-prompt-modal-cancel'
			) as HTMLButtonElement;
			cancelButton.click();
			expect( onCancelMock ).toHaveBeenCalledTimes( 1 );
		} );

		/**
		 * Test: It should call the onCancel callback when the overlay is clicked.
		 * Why: This ensures that clicking outside the modal content (on the overlay)
		 * also correctly triggers the cancellation callback, which is an intuitive and
		 * expected behavior for modal dialogs.
		 */
		it( 'should call onCancel when the overlay is clicked', () => {
			showPromptModal( onGenerateMock, onCancelMock );
			const overlay = document.querySelector(
				'.wc-ai-rr-prompt-modal__overlay'
			) as HTMLElement;
			overlay.click();
			expect( onCancelMock ).toHaveBeenCalledTimes( 1 );
		} );

		/**
		 * Test: It should hide the modal after either the Generate or Cancel button is clicked.
		 * Why: This checks that the modal closes itself after the user makes a choice,
		 * providing a clean user experience and preventing the modal from lingering on the screen.
		 */
		it.each( [ 'generate', 'cancel' ] )(
			'should hide the modal after the %s button is clicked',
			( button ) => {
				showPromptModal( onGenerateMock, onCancelMock );
				const modal = document.querySelector(
					'.wc-ai-rr-prompt-modal'
				) as HTMLElement;
				const buttonElement = document.querySelector(
					`#wc-ai-rr-prompt-modal-${ button }`
				) as HTMLButtonElement;

				buttonElement.click();

				expect( modal.style.display ).toBe( 'none' );
			}
		);
	} );

	describe( 'Getters', () => {
		/**
		 * Test: getSelectedTemplate should return the current value of the template dropdown.
		 * Why: This verifies that the getter function can accurately read the selected value,
		 * which is necessary for passing the correct parameter to the AI generation API.
		 */
		it( 'should get the selected template value', () => {
			showPromptModal( onGenerateMock, onCancelMock );
			const templateSelect = document.querySelector(
				'#wc-ai-rr-prompt-modal-template-select'
			) as HTMLSelectElement;
			// Manually change the selection to test the getter.
			templateSelect.value = 'template2';
			expect( getSelectedTemplate() ).toBe( 'template2' );
		} );

		/**
		 * Test: getSelectedMood should return the current value of the mood dropdown.
		 * Why: This verifies that the getter function can accurately read the selected mood,
		 * which is a required parameter for the AI generation API.
		 */
		it( 'should get the selected mood value', () => {
			showPromptModal( onGenerateMock, onCancelMock );
			const moodSelect = document.querySelector(
				'#wc-ai-rr-prompt-modal-mood-select'
			) as HTMLSelectElement;
			// Manually change the selection to test the getter.
			moodSelect.value = 'mood2';
			expect( getSelectedMood() ).toBe( 'mood2' );
		} );

		/**
		 * Test: The getters should return default values if the modal is not in the DOM.
		 * Why: This ensures that calling the getter functions before the modal has been
		 * shown (or if it fails to render) does not cause errors and returns a predictable
		 * default value, preventing crashes.
		 */
		it( 'should return default values if the select elements are not found', () => {
			// Do not call showPromptModal, so the DOM is empty.
			expect( getSelectedTemplate() ).toBe( 'default' );
			expect( getSelectedMood() ).toBe( 'empathetic_problem_solver' );
		} );
	} );
} );
