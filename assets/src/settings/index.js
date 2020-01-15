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
			conversionBatchSize: '...',
			queuedEntries: '...',
		};
	}

	componentDidMount() {
		return fetchSettingsInfo().then( response => {
			if ( response ) {
				const {
					conversionContentTypesCsv,
					conversionContentStatusesCsv,
					conversionBatchSize,
					queuedEntries,
				} = response;
				this.setState( {
					conversionContentTypesCsv,
					conversionContentStatusesCsv,
					conversionBatchSize,
					queuedEntries,
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
			conversionBatchSize,
			queuedEntries,
		} = this.state;

		return (
			<Fragment>
				<div className="newspack-logo-wrapper">
					<NewspackLogo />
				</div>
				<Grid>
					<FormattedHeader
						headerIcon={ <SettingsIcon /> }
						headerText={ __( 'Content conversion settings' ) }
						subHeaderText={ __( 'Adding content to the queue to convert it to Gutenberg blocks.' ) }
					/>
					<Card>
						<p>
							{ __( 'The type of HTML content to be converted to Gutenberg blocks is specified here.' ) }
							<br />
							{ __( 'This content is then added to a conversion queue.' ) }
							<br />
							{ __( 'The queue is also a backup point for possible reverting.' ) }
						</p>
						<p>
							<Button
								isPrimary
								href="/wp-admin/admin.php?page=ncc-conversion"
							>
								{ __( 'Run conversion' ) }
							</Button>
						</p>
						<hr />
						<h2>{ __( 'Content type' ) }</h2>
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
						<hr />
						<h2>{ __( 'Conversion params' ) }</h2>
						<TextControl
							label={ __( 'Conversion batch size (entries per batch)' ) }
							disabled={ true }
							value={ conversionBatchSize }
						/>
						<hr />
						<h2>{ __( 'Queued stats' ) }</h2>
						<TextControl
							label={ __( 'Number of entries to be converted' ) }
							disabled={ true }
							value={ queuedEntries }
						/>
					</Card>
				</Grid>
			</Fragment>
		);
	}
}

export default Settings;
