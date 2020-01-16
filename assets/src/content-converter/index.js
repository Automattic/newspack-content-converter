/**
 * WordPress dependencies.
 */
import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Newspack dependencies.
 */
import {
	Button,
	Card,
	FormattedHeader,
	Grid,
	NewspackLogo,
	Notice,
	Waiting,
} from 'newspack-components';

/**
 * Material UI dependencies.
 */
import ArrowBackIcon from '@material-ui/icons/ArrowBack';
import UnarchiveIcon from '@material-ui/icons/Unarchive';

/**
 * Internal dependencies.
 */
import {
	runMultiplePosts,
	fetchConversionBatch,
	fetchRetryFailedConversionsBatch,
} from '../utilities';

class ContentConverter extends Component {
	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		this.state = {
			isActive: null,
			retryFailedConversions: props.retryFailedConversions,
			postIds: null,
			thisBatch: null,
			maxBatch: null,
			hasIncompleteConversions: false,
		};
	}

	componentDidMount() {
		const { retryFailedConversions } = this.state;

		// Get a batch of regular conversions, or retry the failed ones.
		const fetchBatchPromise = retryFailedConversions
			? fetchRetryFailedConversionsBatch
			: fetchConversionBatch;

		return fetchBatchPromise()
			.then( response => {
				if ( response ) {
					const { ids: postIds, thisBatch, maxBatch, hasIncompleteConversions } = response;
					this.setState( {
						postIds,
						thisBatch,
						maxBatch,
						hasIncompleteConversions,
						isActive: true,
					} );
					if ( postIds ) {
						console.log( ' ----------------------- ABOUT TO CONVERT IDS: ' + postIds );
						return runMultiplePosts( postIds );
					}
				}

				return new Promise( ( resolve, reject ) => resolve() );
			} )
			.then( () => {
				return new Promise( ( resolve, reject ) => {
					console.log( ' ----------------------- FINISHED.' );
					if ( this.state.postIds ) {
						this.setState( { isActive: null } );
						// This should disable the browser's "Reload page?" popup, although it doesn't always work as expected.
						window.onbeforeunload = function() {};
						// Reload this window to pick up the next batch.
						window.location.reload( true );
					} else {
						this.setState( { isActive: false } );
					}

					return resolve();
				} );
			} );
	}

	/*
	 * render().
	 */
	render() {
		const { isActive, thisBatch, maxBatch, hasIncompleteConversions } = this.state;

		if ( null == isActive ) {
			return (
				<div className="newspack-content-converter__wrapper">
					<div className="newspack-logo-wrapper">
						<NewspackLogo />
					</div>
					<Grid>
						<FormattedHeader
							headerIcon={ <UnarchiveIcon /> }
							headerText={ __( 'Conversion' ) }
							subHeaderText={ __( 'Conversion to Gutenberg blocks is in progress.' ) }
						/>
						<Card>
							<div className="newspack-content-converter__status">
								<Waiting isLeft />
								{ __( 'Loading...' ) }
							</div>
						</Card>
					</Grid>
				</div>
			);
		} else if ( true == isActive ) {
			return (
				<div className="newspack-content-converter__wrapper newspack-content-converter__is-active">
					<div className="newspack-logo-wrapper">
						<NewspackLogo />
					</div>
					<Grid>
						<FormattedHeader
							headerIcon={ <UnarchiveIcon /> }
							headerText={ __( 'Conversion' ) }
							subHeaderText={ __( 'Conversion to Gutenberg blocks is in progress.' ) }
						/>
						<Card>
							<h2>{ __( 'Do not close this page!' ) }</h2>
							<div className="newspack-content-converter__status">
								<Waiting isLeft />
								{ __( 'Now processing batch' ) } { thisBatch }/{ maxBatch }...
							</div>
							<p>
								{ __(
									'This page will occasionally automatically reload, and notify you when the conversion is complete.'
								) }
							</p>
							<p>{ __( 'If asked to Reload, chose yes.' ) }</p>
							<Notice
								noticeText={ __(
									'You may also carefully open an additional tab to convert another batch in parallel.'
								) }
								isPrimary
							/>
						</Card>
					</Grid>
				</div>
			);
		} else if ( false == isActive ) {
			return (
				<div className="newspack-content-converter__wrapper">
					<div className="newspack-logo-wrapper">
						<NewspackLogo />
					</div>
					<Grid>
						<FormattedHeader
							headerIcon={ <UnarchiveIcon /> }
							headerText={ __( 'Conversion' ) }
							subHeaderText={ __( 'Conversion to Gutenberg blocks is complete.' ) }
						/>
						<Card>
							{ true == hasIncompleteConversions ? (
								<Notice
									noticeText={ __(
										'Certain entries were not converted successfully. You may try converting those again on the Run conversion page.'
									) }
									isError
								/>
							) : (
								<Notice
									noticeText={ __( 'All queued content has been converted successfully.' ) }
									isSuccess
								/>
							) }
							<div className="newspack-buttons-card">
								<Button href="/wp-admin/admin.php?page=newspack-content-converter" isPrimary>
									{ __( 'Back to Run conversion' ) }
								</Button>
							</div>
						</Card>
					</Grid>
				</div>
			);
		}
	}
}

export default ContentConverter;
