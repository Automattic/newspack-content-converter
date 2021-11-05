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
	Notice,
	Spinner
} from '@wordpress/components';

/**
 * Newspack dependencies.
 */
import { NewspackLogo } from 'newspack-components';

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
					<div className="newspack-logo__wrapper">
						<Button
							href="https://newspack.pub/"
							target="_blank"
							label={ __( 'By Newspack' ) }
						>
							<NewspackLogo />
						</Button>
					</div>
					<Card>
						<CardHeader isShady>
							<FlexBlock>
								<h2>{ __( 'Converting...' ) }</h2>
								<p>{ __( 'Conversion to Gutenberg blocks is in progress' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardFooter justify="center" isBorderless>
							<Spinner />
						</CardFooter>
					</Card>
				</div>
			);
		} else if ( true == isActive ) {
			return (
				<div className="newspack-content-converter__wrapper is-active">
					<div className="newspack-logo__wrapper">
						<Button
							href="https://newspack.pub/"
							target="_blank"
							label={ __( 'By Newspack' ) }
						>
							<NewspackLogo />
						</Button>
					</div>
					<Card>
						<CardHeader isShady>
							<FlexBlock>
								<h2>{ __( 'Converting...' ) }</h2>
								<p>{ __( 'Conversion to Gutenberg blocks is in progress' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardBody>
							<h4>{ __( 'Do not close this page!' ) }</h4>
							<p>
								{ __(
									'This page will occasionally automatically reload, and notify you when the conversion is complete.'
								) }
							</p>
							<p>{ __( 'If asked to Reload, chose yes.' ) }</p>
							<p>
								<em>
									{ __(
										'You may also carefully open an additional tab to convert another batch in parallel.'
									) }
								</em>
							</p>
						</CardBody>
						<CardFooter justify="center" className="newspack-content-converter__batch">
								<Spinner />
								<p>{ __( 'Now processing batch' ) } { thisBatch }/{ maxBatch }</p>
						</CardFooter>
					</Card>
				</div>
			);
		} else if ( false == isActive ) {
			return (
				<div className="newspack-content-converter__wrapper">
					<div className="newspack-logo__wrapper">
						<Button
							href="https://newspack.pub/"
							target="_blank"
							label={ __( 'By Newspack' ) }
						>
							<NewspackLogo />
						</Button>
					</div>
					<Card>
						<CardHeader isShady>
							<FlexBlock>
								<h2>{ __( 'Finished' ) }</h2>
								<p>{ __( 'Conversion to Gutenberg blocks is now complete' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardBody>
						{ true == hasIncompleteConversions ? (
							<Notice isDismissible={ false } status="error">
								{ __(
									'Certain entries were not converted successfully. You may try converting those again on the "Run conversion" page.'
								) }
							</Notice>
						) : (
							<Notice isDismissible={ false } status="success">
								{ __( 'All queued content has been converted successfully.' ) }
							</Notice>
						) }
						</CardBody>
						{ true == hasIncompleteConversions ? (
							<CardFooter justify="flex-end">
								<Button href="/wp-admin/" isSecondary>
									{ __( 'Back to Dashboard' ) }
								</Button>
								<Button href="/wp-admin/admin.php?page=newspack-content-converter" isPrimary>
									{ __( 'Run Conversion' ) }
								</Button>
							</CardFooter>
						) : (
							<CardFooter justify="flex-end">
								<Button href="/wp-admin/admin.php?page=newspack-content-converter" isSecondary>
									{ __( 'Run Conversion' ) }
								</Button>
								<Button href="/wp-admin/" isPrimary>
									{ __( 'Back to Dashboard' ) }
								</Button>
							</CardFooter>
						) }
					</Card>
				</div>
			);
		}
	}
}

export default ContentConverter;
