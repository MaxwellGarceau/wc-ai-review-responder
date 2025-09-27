/**
 * External dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { Dropdown } from '@wordpress/components';
import * as Woo from '@woocommerce/components';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './index.scss';

const MyExamplePage = () => (
	<Fragment>
		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Search', 'wc-ai-review-responder' ) } />
			<Woo.Search
				type="products"
				placeholder="Search for something"
				selected={ [] }
				onChange={ ( items ) => setInlineSelect( items ) }
				inlineTags
			/>
		</Woo.Section>

		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Dropdown', 'wc-ai-review-responder' ) } />
			<Dropdown
				renderToggle={ ( { isOpen, onToggle } ) => (
					<Woo.DropdownButton
						onClick={ onToggle }
						isOpen={ isOpen }
						labels={ [ 'Dropdown' ] }
					/>
				) }
				renderContent={ () => <p>Dropdown content here</p> }
			/>
		</Woo.Section>

		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Pill shaped container', 'wc-ai-review-responder' ) } />
			<Woo.Pill className={ 'pill' }>
				{ __( 'Pill Shape Container', 'wc-ai-review-responder' ) }
			</Woo.Pill>
		</Woo.Section>

		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Spinner', 'wc-ai-review-responder' ) } />
			<Woo.H>I am a spinner!</Woo.H>
			<Woo.Spinner />
		</Woo.Section>

		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Datepicker', 'wc-ai-review-responder' ) } />
			<Woo.DatePicker
				text={ __( 'I am a datepicker!', 'wc-ai-review-responder' ) }
				dateFormat={ 'MM/DD/YYYY' }
			/>
		</Woo.Section>
	</Fragment>
);

addFilter( 'woocommerce_admin_pages_list', 'wc-ai-review-responder', ( pages ) => {
	pages.push( {
		container: MyExamplePage,
		path: '/wc-ai-review-responder',
		breadcrumbs: [ __( 'Wc Ai Review Responder', 'wc-ai-review-responder' ) ],
		navArgs: {
			id: 'wc_ai_review_responder',
		},
	} );

	return pages;
} );
