/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { fetchConversionInfo, postConversionInitialize } from '../utilities';

class Conversion extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			isConversionOngoing: null,
			queuedEntries: '',
			conversionBatchSize: '',
			queuedBatchesCsv: '',
			maxBatch: '',
		};
	}

	componentDidMount() {
		return fetchConversionInfo().then( response => {
			if ( response ) {
				const {
					isConversionOngoing,
					queuedEntries,
					conversionBatchSize,
					queuedBatches,
					maxBatch,
				} = response;
				const queuedBatchesCsv = queuedBatches.join( ',' );
				this.setState( {
					isConversionOngoing,
					queuedEntries,
					conversionBatchSize,
					queuedBatchesCsv,
					maxBatch,
				} );
			}
			return new Promise( ( resolve, reject ) => resolve() );
		} );
	}

	handleOnClickInitializeConversion = () => {
		return postConversionInitialize().then( response => {
			console.log( response );

			if ( ! response || ! response.result || 'queued' != response.result ) {
				return new Promise( ( resolve, reject ) => resolve() );
			}

			// Redirect to Converter app to begin conversion.
			window.parent.location = '/wp-admin/post-new.php?newspack-content-converter';
		} );
	};

	render() {
		const {
			isConversionOngoing,
			queuedEntries,
			conversionBatchSize,
			queuedBatchesCsv,
			maxBatch,
		} = this.state;

		if ( '1' == isConversionOngoing ) {
			return (
				<div className="ncc-page">
					<h1>{ __( 'Run Conversion' ) }</h1>
					<br />

					<h3>{ __( 'Converson is currently in progress...' ) }</h3>
					<ul>
						<li>
							{ __( 'conversion batches processed so far:' ) }{' '}
							<b>
								<u>{ queuedBatchesCsv }</u>
							</b>
						</li>
						<li>
							{ __( 'total batches queued:' ) }{' '}
							<b>
								<u>{ maxBatch }</u>
							</b>
						</li>
						<li>
							{ __( 'conversion batch size (posts per batch):' ) }{' '}
							<b>
								<u>{ conversionBatchSize }</u>
							</b>
						</li>
					</ul>
					<p>
						<i>
							*{' '}
							{ __(
								'note: keep an eye on your active browser tabs currently performing the conversion'
							) }
						</i>
					</p>
				</div>
			);
		} else {
			return (
				<div className="ncc-page">
					<h1>{ __( 'Run Conversion' ) }</h1>
					<br />

					<h3>{ __( 'Planned conversion' ) }</h3>
					<ul>
						<li>{ __( 'the entire queued content will be converted to Gutenberg Blocks' ) }</li>
						<li>{ __( 'this page will automatically reload for every batch' ) }</li>
						<li>
							{ __( 'number of posts/entries to be converted:' ) }{' '}
							<b>
								<u>{ queuedEntries }</u>
							</b>
						</li>
						<li>
							{ __( 'total conversion batches queued' ) }:{' '}
							<b>
								<u>{ maxBatch }</u>
							</b>
						</li>
						<li>
							{ __( 'batch size (posts per batch):' ) }{' '}
							<b>
								<u>{ conversionBatchSize }</u>
							</b>
						</li>
					</ul>
					<br />

					<input
						type="submit"
						className="large"
						id="convert_button"
						value={ __( 'Start Conversion Now' ) }
						onClick={ event => this.handleOnClickInitializeConversion( event ) }
					/>
					<p>
						<i>
							*{' '}
							{ __(
								'note carefully -- once started, the conversion should not be interrupted! Your browser page needs to remain active until conversion is complete. You may, however, manually open multiple browser tabs once the conversion starts, and each of them will fetch and convert another batch of posts in parallel. Running multiple tabs speeds up the conversion. Recommended max number of tabs is 4.'
							) }
						</i>
					</p>
				</div>
			);
		}
	}
}

export default Conversion;
