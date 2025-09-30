/**
 * Internationalization utilities for WC AI Review Responder.
 *
 * @since 1.0.0
 */

/**
 * Get a localized string by key.
 *
 * @param {string} key - The localization key.
 * @return {string} The localized string or the key if not found.
 */
export function __( key: string ): string {
	if ( typeof window !== 'undefined' && window.wcAiReviewResponder?.i18n ) {
		return window.wcAiReviewResponder.i18n[ key ] || key;
	}
	return key;
}

/**
 * Apply localization to all elements with data-i18n attributes.
 * This should be called after the DOM is ready.
 */
export function localizeElements(): void {
	if ( typeof window === 'undefined' || ! window.wcAiReviewResponder?.i18n ) {
		return;
	}

	const elements = document.querySelectorAll( '[data-i18n]' );
	elements.forEach( ( element ) => {
		const key = element.getAttribute( 'data-i18n' );
		if ( key && window.wcAiReviewResponder.i18n[ key ] ) {
			element.textContent = window.wcAiReviewResponder.i18n[ key ];
		}
	} );
}