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
} from '../utilities';

class ContentConverter extends Component {
	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		this.state = {
			isActive: null,
			isConversionPrepared: null,
			isConversionFinished: null,
			ids: null,
			thisBatch: null,
			totalNumberOfBatches: null,
		};
	}

	componentDidMount() {

		// Run a batch of conversions.
		return fetchConversionBatch()
			.then( response => {
				if ( response ) {
					const { isConversionPrepared, isConversionFinished, ids, thisBatch, totalNumberOfBatches } = response;
					// Starting conversion, setting isActive to true.
					this.setState( {
						isConversionPrepared,
						isConversionFinished,
						ids,
						thisBatch,
						totalNumberOfBatches,
						isActive: true,
					} );
					// If conversion is not prepared and not finished, redirect to the plugin page.
					if ( '0' == isConversionPrepared && '0' == isConversionFinished ) {
						window.parent.location = '/wp-admin/admin.php?page=newspack-content-converter';
					} else if ( ids ) {
						console.log( ' ----------------------- ABOUT TO CONVERT BATCH: ' + thisBatch + ' IDS: ' + ids );
						return runMultiplePosts( ids );
					}
				}

				return new Promise( ( resolve, reject ) => resolve() );
			} )
			.then( () => {
				return new Promise( ( resolve, reject ) => {
					console.log( ' ----------------------- FINISHED.' );
					if ( this.state.ids && this.state.ids.length > 0 ) {
						// Conversion hasn't started yet, so isActive is null before it's either true or false.
						this.setState( { isActive: null } );
						// This should disable the browser's "Reload page?" popup, although it doesn't always work as expected.
						window.onbeforeunload = function() {};
						// Reload this window to pick up the next batch.
						window.location.reload( true );
					} else {
						// No more posts to convert, so isActive is false.
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
		const { isActive, thisBatch, totalNumberOfBatches } = this.state;

		if ( null == isActive ) {
			// This is the initial state of the interface, before conversion has started (true) or finished (false).
			return (
				<div className="newspack-content-converter__wrapper">
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
								<h2>{ __( 'Converting...' ) }</h2>
								<p>{ __( 'Conversion to Gutenberg blocks is in progress' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardFooter justify="center" isBorderless>
							<Spinner /> { __( 'Fetching posts for conversion... ' ) }
						</CardFooter>
					</Card>
				</div>
			);
		} else if ( true == isActive ) {
			// Conversion is running.
			return (
				<div className="newspack-content-converter__wrapper is-active">
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
							<Notice status="warning" isDismissible={ false }>
							{ __( 'If asked to Reload, chose yes.' ) }
							</Notice>
							<p>
								<em>
									{ __(
										'To convert another batch in parallel and increase conversion speed (depending on your computer, no more than 10 max parallel browser tabs are recommended)'
									) }
									--
									<a href="" target="_blank">open this page in a new tab</a>
								</em>
							</p>
						</CardBody>
						<CardFooter justify="center" className="newspack-content-converter__batch">
								<Spinner />
								<p>{ __( 'Now processing batch' ) } { thisBatch }/{ totalNumberOfBatches }</p>
						</CardFooter>
					</Card>
				</div>
			);
		} else if ( false == isActive ) {
			// Conversion has finished.
			return (
				<div className="newspack-content-converter__wrapper">
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
								<h2>{ __( 'Finished' ) }</h2>
								<p>{ __( 'Conversion to Gutenberg blocks is now complete' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardBody>
							<Notice isDismissible={ false } status="success">
								{ __( 'All content has been converted.' ) }
							</Notice>
						</CardBody>
						<CardFooter justify="flex-end">
							<Button href="/wp-admin/" isSecondary>
								{ __( 'Back to Dashboard' ) }
							</Button>
							<Button href="/wp-admin/admin.php?page=newspack-content-converter" isPrimary>
								{ __( 'Back to Converter' ) }
							</Button>
						</CardFooter>
					</Card>
				</div>
			);
		}
	}
}

export default ContentConverter;

