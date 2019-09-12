/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { fetchPatchingInfo, postPatchingInitialize } from "../utilities";

class Patchers extends Component {

	constructor( props ) {
		super( props );

		this.state = {
			isPatchingOngoing: null,
			queuedBatchesPatching: '',
			maxBatchPatching: '',
			patchingBatchSize: '',
			queuedEntries: '',
		};
	}

	componentDidMount() {
		return fetchPatchingInfo()
			.then( response => {
				if ( response ) {
					const { isPatchingOngoing, queuedBatchesPatching, maxBatchPatching, patchingBatchSize, queuedEntries } = response;
					this.setState( { isPatchingOngoing , queuedBatchesPatching, maxBatchPatching, patchingBatchSize, queuedEntries } );
				}
				return new Promise( ( resolve, reject ) => resolve() );
			} );
	}

	handleOnClickInitializePatching = () => {
		return postPatchingInitialize()
			.then( response => {
				console.log(response)

				if ( ! response || ! response.result || 'queued' != response.result ) {
					return new Promise( ( resolve, reject ) => resolve() );
				}

				// Redirect to Converter app to begin conversion.
				window.parent.location = '/wp-admin/admin.php?page=ncc-content-repatching';
			} );
	};

	/*
	 * render().
	 */
	render() {
		const { isPatchingOngoing, queuedBatchesPatching, maxBatchPatching, patchingBatchSize, queuedEntries } = this.state;
		const queuedBatchesPatchingCsv = queuedBatchesPatching ? queuedBatchesPatching.join( ', ' ) : '';

		// Non loaded state.
		if ( null == isPatchingOngoing ) {

			return (
				<div className="ncc-page">
					<h1>{ __( 'Re-apply Patchers' ) }</h1>
				</div>
			);

		// Patching in progress.
		} else if ( '1' == isPatchingOngoing ) {

			return (
				<div className="ncc-page">
					<h1>{ __( 'Re-apply Patchers' ) }</h1>
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
					<p>
						<i>* { __( 'note: keep an eye on your active browser tabs currently performing the conversion' ) }</i>
					</p>
				</div>
			);

		// Patching not in progress.
		} else {

			return (
				<div className="ncc-page">
					<h1>{ __( 'Re-apply Patchers' ) }</h1>
					<br />

					<p>
						<i>{ __( 'This is a *dev* feature, automatically performed as a part of the conversion.' ) }</i>
					</p>

					<h3>{ __( 'Planned patching' ) }</h3>
					<ul>
						<li>
							{ __( 'patching will be applied on the entire queued content') }
						</li>
						<li>
							{ __( 'this page will automatically reload for every batch') }
						</li>
						<li>
							{ __( 'number of posts/entries to be patched:') } <b><u>{ queuedEntries }</u></b>
						</li>
						<li>
							{ __( 'content grouped in total patching batches:') }: <b><u>{ maxBatchPatching }</u></b>
						</li>
						<li>
							{ __( 'batch size (posts per batch):' ) } <b><u>{ patchingBatchSize }</u></b>
						</li>
					</ul>
					<br />

					<p>
						Before re-running patchers, <b>make sure all queued content is already converted</b>.
					</p>

					<input
						type="submit"
						className="large"
						id="convert_button"
						value={ __( 'Patch Content Now' ) }
						onClick={ event => this.handleOnClickInitializePatching( event ) }
					/>

					<p>
						<i>* { __( 'note carefully -- once started, patching should not be interrupted! Your browser page needs to remain active until patching is complete. You may, however, manually open multiple browser tabs once the patching starts, and each of them will fetch and patch another batch of content in parallel. Running multiple tabs speeds up the patching. Recommended max number of tabs is 4.' ) }</i>
					</p>
				</div>
			);

		}
	}
}

export default Patchers;
