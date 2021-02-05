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
	TextControl
} from '@wordpress/components';

/**
 * Newspack dependencies.
 */
import { NewspackLogo } from 'newspack-components';

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
					maxBatch,
					hasConvertedPosts,
					hasFailedConversions,
					countFailedConverting,
				} = response;
				this.setState( {
					isConversionOngoing,
					queuedEntries,
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
			maxBatch,
			hasConvertedPosts,
			hasFailedConversions,
			countFailedConverting,
		} = this.state;
		const someConversionsFailed = true === hasConvertedPosts && true === hasFailedConversions;

		if ( '1' == isConversionOngoing ) {
			return (
				<Fragment>
					<Card>
						<CardHeader isShady>
							<FlexBlock>
								<h2>{ __( 'Converting...' ) }</h2>
								<p>{ __( 'The conversion is already running' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardFooter isBorderless>
							<p>
								{ __( 'A designated browser tab has already started to convert your content.' ) }
							</p>
						</CardFooter>
					</Card>
					<Card>
						<CardHeader isShady>
							<FlexBlock>
								<h2>{ __( 'Reset Conversion' ) }</h2>
								<p>{ __( 'Start converting all over again' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardBody>
							<Notice status="warning" isDismissible={ false }>
								{ __(
									'This will enable you to restart the conversion, but any previous results may be lost.'
								) }
							</Notice>
							<p>
								{ __(
									'In case that your active conversion browser tab has been closed by accident, or it has been interrupted and closed unexpectedly, you may reset the conversion status here.'
								) }
							</p>
						</CardBody>
						<CardFooter justify="flex-end">
							<Button isPrimary onClick={ this.handleOnClickResetConversion }>
								{ __( 'Reset Conversion' ) }
							</Button>
						</CardFooter>
					</Card>
					<div className="newspack-logo__wrapper">
						<Button
							href="https://newspack.pub/"
							target="_blank"
							label={ __( 'By Newspack' ) }
						>
							<NewspackLogo />
						</Button>
					</div>
				</Fragment>
			);
		} else {
			return (
				<Fragment>
					{ !! someConversionsFailed && (
						<Card>
							<CardHeader isShady>
								<FlexBlock>
									<h2>{ __( 'Conversion Error' ) }</h2>
									<p>
										{ __( 'Retry converting the failed entries' ) }
									</p>
								</FlexBlock>
							</CardHeader>
							<CardBody>
								<FlexBlock>
									<Notice status="error" isDismissible={ false }>
										{ __(
											"Looks like some entries weren't converted properly."
										) }
									</Notice>
									<TextControl
										label={ __( 'Number of failed entries' ) }
										disabled={ true }
										value={ countFailedConverting }
									/>
								</FlexBlock>
							</CardBody>
							<CardFooter justify="flex-end">
								<Button isPrimary onClick={ this.handleOnClickInitializeRetryFailed }>
									{ __( 'Retry Conversion' ) }
								</Button>
							</CardFooter>
						</Card>
					) }
					<Card>
						<CardHeader isShady>
							<FlexBlock>
								<h2>{ __( 'Run Conversion' ) }</h2>
								<p>
									{ __( 'Start conversion to Gutenberg blocks' ) }
								</p>
							</FlexBlock>
						</CardHeader>
						<CardBody>
							<Notice status="warning" isDismissible={ false }>
								{ __(
									'Once started, the conversion should not be interrupted! Your browser page needs to remain active until conversion is complete.'
								) }
							</Notice>
							<h4>
								{ __( 'This page will automatically reload for every batch.' ) }
							</h4>
							<TextControl
								label={ __( 'Number of entries to be converted' ) }
								disabled={ true }
								value={ queuedEntries }
							/>
							<TextControl
								label={ __( 'Total conversion batches' ) }
								disabled={ true }
								value={ maxBatch }
							/>
						</CardBody>
						<CardFooter justify="flex-end">
							<Button
								isSecondary
								href="/wp-admin/admin.php?page=newspack-content-converter-settings"
							>
								{ __( 'Settings' ) }
							</Button>
							<Button isPrimary onClick={ this.handleOnClickInitializeConversion }>
								{ __( 'Run Conversion' ) }
							</Button>
						</CardFooter>
					</Card>
					<div className="newspack-logo__wrapper">
						<Button
							href="https://newspack.pub/"
							target="_blank"
							label={ __( 'By Newspack' ) }
						>
							<NewspackLogo />
						</Button>
					</div>
				</Fragment>
			);
		}
	}
}

export default Conversion;
