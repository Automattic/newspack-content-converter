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
	CardHeader,
	FlexBlock,
	TextControl
} from '@wordpress/components';

/**
 * Newspack dependencies.
 */
import { NewspackLogo } from 'newspack-components';

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
				<div className="newspack-logo__wrapper">
					<Button
						href="https://newspack.com/"
						target="_blank"
						label={ __( 'By Newspack' ) }
					>
						<NewspackLogo />
					</Button>
				</div>
				<Card>
					<CardHeader isShady>
						<FlexBlock>
							<h2>{ __( 'Settings' ) }</h2>
							<p>
								{ __( 'Adding content to the queue to convert it to Gutenberg blocks' ) }
							</p>
						</FlexBlock>
					</CardHeader>
					<CardBody>
						<p>
							{ __(
								'The type of HTML content to be converted to Gutenberg blocks is specified here.'
							) }
						</p>
						<TextControl
							label={ __( 'Content types' ) }
							disabled={ true }
							value={ conversionContentTypesCsv }
						/>
						<TextControl
							label={ __( 'Content statuses' ) }
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
