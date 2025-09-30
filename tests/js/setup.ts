/**
 * Jest setup file.
 *
 * This file is loaded before each test file.
 *
 * @since 1.2.0
 */

// Mock the global wcAiReviewResponder object.
// This object is normally created by WordPress and contains configuration and localization data.
window.wcAiReviewResponder = {
	ajaxurl: 'admin-ajax.php',
	templates: [
		{ value: 'template1', label: 'Template 1' },
		{ value: 'template2', label: 'Template 2' },
	],
	moods: [
		{ value: 'mood1', label: 'Mood 1' },
		{ value: 'mood2', label: 'Mood 2' },
	],
};

// Mock the global tinymce object which can be present on WordPress pages.
// This is necessary because some utility functions interact with it.
window.tinymce = {
	get: jest.fn(),
};
