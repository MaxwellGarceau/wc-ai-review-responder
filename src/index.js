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
			<Woo.SectionHeader title={ __( 'Search', 'woocommerce' ) } />
			<Woo.Search
				type="products"
				placeholder="Search for something"
				selected={ [] }
				onChange={ ( items ) => {
					// Handle search selection
					// eslint-disable-next-line no-console
					console.log( 'Selected items:', items );
				} }
				inlineTags
			/>
		</Woo.Section>

		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Dropdown', 'woocommerce' ) } />
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
			<Woo.SectionHeader
				title={ __( 'Pill shaped container', 'woocommerce' ) }
			/>
			<Woo.Pill className={ 'pill' }>
				{ __( 'Pill Shape Container', 'woocommerce' ) }
			</Woo.Pill>
		</Woo.Section>

		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Spinner', 'woocommerce' ) } />
			<Woo.H>I am a spinner!</Woo.H>
			<Woo.Spinner />
		</Woo.Section>

		<Woo.Section component="article">
			<Woo.SectionHeader title={ __( 'Datepicker', 'woocommerce' ) } />
			<Woo.DatePicker
				text={ __( 'I am a datepicker!', 'woocommerce' ) }
				dateFormat={ 'MM/DD/YYYY' }
			/>
		</Woo.Section>
	</Fragment>
);

addFilter(
	'woocommerce_admin_pages_list',
	'wc-ai-review-responder',
	( pages ) => {
		pages.push( {
			container: MyExamplePage,
			path: '/wc-ai-review-responder',
			breadcrumbs: [ __( 'Wc Ai Review Responder', 'woocommerce' ) ],
			navArgs: {
				id: 'wc_ai_review_responder',
			},
		} );

		return pages;
	}
);
