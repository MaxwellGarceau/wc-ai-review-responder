/**
 * TypeScript type definitions for admin review actions.
 *
 * @since 1.0.0
 */

export interface Template {
	value: string;
	label: string;
}

export interface Mood {
	value: string;
	label: string;
}

export interface WcAiReviewResponder {
	ajaxurl: string;
	templates: Template[];
	moods: Mood[];
}

export interface AiResponseData {
	success: boolean;
	data: {
		reply?: string;
		message?: string;
	};
}

declare global {
	const wcAiReviewResponder: WcAiReviewResponder;
}
