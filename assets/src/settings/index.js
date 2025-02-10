/**
 * WordPress dependencies.
 */
import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Card,
	CardBody,
	CardFooter,
	TextControl
} from '@wordpress/components';

/**
 * Newspack dependencies.
 */
import { NewspackIcon } from 'newspack-components';

/**
 * Internal dependencies.
 */
import { fetchSettingsInfo, runMultiplePosts } from '../utilities';

class Settings extends Component {
	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		this.state = {
			conversionContentTypesCsv: '...',
			conversionContentStatusesCsv: '...',
		};
	}

	componentDidMount() {
		return fetchSettingsInfo().then( response => {
			if ( response ) {
				const { conversionContentTypesCsv, conversionContentStatusesCsv } = response;
				this.setState( {
					conversionContentTypesCsv,
					conversionContentStatusesCsv,
				} );
			}
			return new Promise( ( resolve, reject ) => resolve() );
		} );
	}

	/*
	 * render().
	 */
	render() {
		const { conversionContentTypesCsv, conversionContentStatusesCsv } = this.state;

		return (
			<Fragment>
				<div className="newspack-header">
					<NewspackIcon />
					<h2>{ __( 'Content Converter / Settings' ) }</h2>
				</div>
				<Card>
					<CardBody>
						<p>
							{ __(
								'The type of HTML content to be converted to Gutenberg blocks is specified here.'
							) }
						</p>
						<TextControl
							label={ __( 'Content types CSV' ) }
							disabled={ true }
							value={ conversionContentTypesCsv }
						/>
						<TextControl
							label={ __( 'Content statuses CSV' ) }
							disabled={ true }
							value={ conversionContentStatusesCsv }
						/>
					</CardBody>
					<CardFooter justify="flex-end">
						<Button
							isPrimary
							href="/wp-admin/admin.php?page=newspack-content-converter"
						>
							{ __( 'Go to Converter' ) }
						</Button>
					</CardFooter>
				</Card>
			</Fragment>
		);
	}
}

export default Settings;
