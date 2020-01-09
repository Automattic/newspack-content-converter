/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import {
	fetchConversionInfo,
	fetchInitializeConversion,
	fetchInitializeRetryFailedConversion,
	fetchResetConversion,
} from '../utilities';

class Conversion extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			isConversionOngoing: false,
			queuedEntries: '...',
			conversionBatchSize: '...',
			queuedBatchesCsv: '...',
			maxBatch: '...',
			hasConvertedPosts: false,
			hasFailedConversions: false,
			countFailedConverting: '...',
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
					hasConvertedPosts,
					hasFailedConversions,
					countFailedConverting,
				} = response;
				const queuedBatchesCsv = queuedBatches ? queuedBatches.join( ',' ) : null;
				this.setState( {
					isConversionOngoing,
					queuedEntries,
					conversionBatchSize,
					queuedBatchesCsv,
					maxBatch,
					hasConvertedPosts,
					hasFailedConversions,
					countFailedConverting,
				} );
			}
			return new Promise( ( resolve, reject ) => resolve() );
		} );
	}

	handleOnClickInitializeConversion = () => {
		return fetchInitializeConversion().then( response => {
			if ( ! response || ! response.result || 'queued' != response.result ) {
				return;
			}

			// Redirect to Converter app to begin conversion.
			window.parent.location = '/wp-admin/post-new.php?newspack-content-converter';
		} );
	};

	handleOnClickInitializeRetryFailed = () => {
		return fetchInitializeRetryFailedConversion().then( response => {
			if ( ! response || ! response.result || 'queued' != response.result ) {
				return;
			}

			// Redirect to Converter app to begin conversion.
			window.parent.location = '/wp-admin/post-new.php?newspack-content-converter&retry-failed';
		} );
	};

	handleOnClickResetConversion = () => {
		return fetchResetConversion().then( response => {
			if ( response ) {
				location.reload();
			}
		} );
	};

	render() {
		const {
			isConversionOngoing,
			queuedEntries,
			conversionBatchSize,
			queuedBatchesCsv,
			maxBatch,
			hasConvertedPosts,
			hasFailedConversions,
			countFailedConverting,
		} = this.state;
		const someConversionsFailed = true === hasConvertedPosts && true === hasFailedConversions;

		if ( '1' == isConversionOngoing ) {
			return (
				<div className="ncc-page">
					<h1>{ __( 'Run Conversion' ) }</h1>
					<br />

					<h3>{ __( 'A Converson is Currently in Progress' ) }</h3>
					<p>
						{ __(
							'A designated browser tab has already started to convert your content. Conversion progress:'
						) }
					</p>
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

					<br />

					<h3>{ __( 'Reset Ongoing Conversion to Start Again' ) }</h3>
					<p>
						{ __(
							'In case that your active conversion browser tab has been closed by accident, or it has been interrupted and closed unexpectedly, you may reset the conversion status here, and start converting all over again. Note that this will enable you to restart the conversion, but any previous results may be lost.'
						) }
					</p>
					<input
						type="submit"
						className="large"
						value={ __( 'Reset Conversion' ) }
						onClick={ this.handleOnClickResetConversion }
					/>
				</div>
			);
		} else {
			return (
				<div className="ncc-page">
					<h1>{ __( 'Run Conversion' ) }</h1>

					<p>
						<b>
							<u>{ __( 'Note carefully' ) }</u>
						</b>
						{ ' -- ' }
						{ __(
							'once started, the conversion should not be interrupted! Your browser page needs to remain active until conversion is complete. You may, however, manually open multiple browser tabs once the conversion starts, and each of them will fetch and convert another batch of posts in parallel. Running multiple tabs speeds up the conversion. Recommended max number of tabs is 4.'
						) }
					</p>

					<h3>{ __( 'Planned Conversion' ) }</h3>
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
						value={ __( 'Start Conversion' ) }
						onClick={ this.handleOnClickInitializeConversion }
					/>

					<br />
					<br />

					{ !! someConversionsFailed && (
						<div>
							<h3>{ __( 'Retry Converting Failed Posts' ) }</h3>
							<p>
								{ __(
									"Looks like some posts weren't converted properly. This could have happened due to unexpected/unsupported Post content (valid HTML source is expected as Post content), or due to technical reasons (e.g. your server experiencing difficulties). You may retry converting these posts now."
								) }
							</p>
							<ul>
								<li>
									{ __( 'number of posts which failed getting converted:' ) }{' '}
									<b>
										<u>{ countFailedConverting }</u>
									</b>
								</li>
							</ul>

							<input
								type="submit"
								className="large"
								value={ __( 'Retry Failed' ) }
								onClick={ this.handleOnClickInitializeRetryFailed }
							/>
						</div>
					) }
				</div>
			);
		}
	}
}

export default Conversion;
