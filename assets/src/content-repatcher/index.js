/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { fetchPatchingInfo, callPatchingProcessNextBatch } from '../utilities';

class ContentRepatcher extends Component {
	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		this.state = {
			isActive: null,
			isPatchingOngoing: null,
			queuedBatchesPatchingCsv: '',
			maxBatchPatching: '',
			patchingBatchSize: '',
		};
	}

	componentDidMount() {
		return Promise.resolve()
			.then( () => fetchPatchingInfo() )
			.then( response => {
				if ( response ) {
					const { isPatchingOngoing, queuedBatchesPatchingCsv, maxBatchPatching, patchingBatchSize } = response;
					if ( '0' == isPatchingOngoing ) {
						this.setState( { isActive: false } );
						console.log( ' ----------------------- FINISHED.' );

						// Terminate promise, no more patching to do.
						throw new Error( 'Patching is not scheduled.' );
					}

					this.setState( { isPatchingOngoing, queuedBatchesPatchingCsv, maxBatchPatching, patchingBatchSize , isActive: true } );
				}

				console.log( ' ----------------------- ABOUT TO PATCH NEXT BATCH.' );
				return new Promise( ( resolve, reject ) => resolve() );
			} )
			.then( () => callPatchingProcessNextBatch() )
			.then( response => {
				if ( null == response ) {
					this.setState( { isActive: false } );
				} else if ( response && response.result && 'patched' == response.result ) {

					// Reload this window to pick up the next batch.
					window.location.reload(true);
				}
				console.log( ' ----------------------- FINISHED.' );

				return new Promise( ( resolve, reject ) => resolve() );
			} )
			.catch( err => {
				console.log( err );
			} );
	}

	/*
	 * render().
	 */
	render() {
		const { isActive, queuedBatchesPatchingCsv, maxBatchPatching, patchingBatchSize } = this.state;

		if ( null == isActive ) {

			return (
				<div className="ncc-page">
					<h1>{ __( 'Content Re-patching' ) }</h1>
					<img src="/wp-admin/images/wpspin_light.gif" /> Loading...
				</div>
			);

		} else if ( true == isActive ) {

			return (
				<div className="ncc-page">
					<h1>{ __( 'Content Re-patching...' ) }</h1>
					<img src="/wp-admin/images/wpspin_light.gif" />
					&nbsp; { __( 'Now patching the next batch' ) } ...
					<br />

					<h3>{ __( 'Patching is currently in progress...' ) }</h3>
					<ul>
						<li>
							{ __( 'patching batches processed so far:' ) } <b><u>{ queuedBatchesPatchingCsv }</u></b>
						</li>
						<li>
							{ __( 'total batches queued:' ) } <b><u>{ maxBatchPatching }</u></b>
						</li>
						<li>
							{ __( 'conversion batch size (posts per batch):' ) } <b><u>{ patchingBatchSize }</u></b>
						</li>
					</ul>

					<h3>{ __( 'Do not close this page!' ) }</h3>
					<ul>
						<li>{ __( 'This page will occasionally automatically reload, and notify you when the patching is complete.') }</li>
						<li>{ __( 'If asked to Reload, chose yes.' ) }</li>
						<li>{ __( 'You may also carefully open an additional tab to process another batch in parallel.' ) }</li>
					</ul>
				</div>
			);

		} else if ( false == isActive ) {

			return (
				<div className="ncc-page">
					<h1>{ __( 'Content Re-patching Complete' ) }</h1>
					<p>{ __( 'All queued content has been patched.') }</p>
				</div>
			);

		}
	}
}

export default ContentRepatcher;
