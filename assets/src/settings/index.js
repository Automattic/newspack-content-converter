/**
 * WordPress dependencies.
 */
import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Newspack dependencies.
 */
import { Button, Card, FormattedHeader, Grid, NewspackLogo, TextControl } from 'newspack-components';

/**
 * Material UI dependencies.
 */
import SettingsIcon from '@material-ui/icons/Settings';

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
				const {
					conversionContentTypesCsv,
					conversionContentStatusesCsv,
				} = response;
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
		const {
			conversionContentTypesCsv,
			conversionContentStatusesCsv,
		} = this.state;

		return (
			<Fragment>
				<div className="newspack-logo-wrapper">
					<NewspackLogo />
				</div>
				<Grid>
					<FormattedHeader
						headerIcon={ <SettingsIcon /> }
						headerText={ __( 'Conversion settings' ) }
						subHeaderText={ __( 'Adding content to the queue to convert it to Gutenberg blocks.' ) }
					/>
					<Card>
						<p>{ __( 'The type of HTML content to be converted to Gutenberg blocks is specified here.' ) }</p>
						<hr />
						<h2 id="content-type">{ __( 'Content type' ) }</h2>
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
						<p>
							<Button
								isPrimary
								href="/wp-admin/admin.php?page=ncc-conversion"
							>
								{ __( 'Run conversion' ) }
							</Button>
						</p>
					</Card>
				</Grid>
			</Fragment>
		);
	}
}

export default Settings;
