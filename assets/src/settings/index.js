/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import './style.css';
import { fetchSettingsInfo, runMultiplePosts } from "../utilities";

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
			patchingBatchSize: '...',
			queuedEntries: '...',
		};
	}

	componentDidMount() {
		return Promise.resolve()
			.then( () => fetchSettingsInfo() )
			.then( response => {
				if ( response && response.info ) {
					const { conversionContentTypesCsv, conversionContentStatusesCsv, conversionBatchSize, patchingBatchSize, queuedEntries } = response.info;
					this.setState( { conversionContentTypesCsv, conversionContentStatusesCsv, conversionBatchSize, patchingBatchSize, queuedEntries } );
				}
				return new Promise( ( resolve, reject ) => resolve() );
			} );
	}

	/*
	 * render().
	 */
	render() {
		const { conversionContentTypesCsv, conversionContentStatusesCsv, conversionBatchSize, patchingBatchSize, queuedEntries } = this.state;

		return (
			<div className="ncc-page">
				<h1>{ __( 'Content Conversion Settings' ) }</h1>
				<br />

				<p>
					{ __( 'Adding content to the queue, enables it to be converted to Gutenberg Blocks. The queue is also a backup point for possible reverting, or re-applying the content patchers (which is one of *dev* functionalities).' ) }
				</p>
				<p>
					{ __( 'Existing HTML content is first selected by type, and added to a conversion queue. Queued content is then converted.' ) }
				</p>
				<h3>{ __( 'Specify content type' ) }</h3>
				<ul>
					<li>
						content types: <input type="text" disabled={ true } value={ conversionContentTypesCsv } />
					</li>
					<li>
						content statuses: <input type="text" disabled={ true } value={ conversionContentStatusesCsv } />
					</li>
				</ul>

				<h3>{ __( 'Conversion params' ) }</h3>
				<ul>
					<li>
						conversion batch size: <input type="text" disabled={ true } value={ conversionBatchSize } />
					</li>
				</ul>

				<h3>{ __( 'Re-patching params' ) }</h3>
				<ul>
					<li>
						patching batch size: <input type="text" disabled={ true } value={ patchingBatchSize } />
					</li>
				</ul>

				<h3>{ __( 'Queued stats' ) }</h3>
				<ul>
					<li>
						<b><u>{ queuedEntries }</u></b> posts are currently selected and queued for conversion
					</li>
				</ul>
			</div>
		);
	}
}

export default Settings;
