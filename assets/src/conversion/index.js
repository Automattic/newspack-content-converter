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
	TextControl,
} from 'newspack-components';

/**
 * Material UI dependencies.
 */
import UnarchiveIcon from '@material-ui/icons/Unarchive';

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
					<div className="newspack-logo-wrapper">
						<NewspackLogo />
					</div>
					<Grid>
						<FormattedHeader
							headerIcon={ <UnarchiveIcon /> }
							headerText={ __( 'Run conversion' ) }
							subHeaderText={ __( 'Start conversion to Gutenberg blocks.' ) }
						/>
						<Card>
							<Notice
								noticeText={ __(
									'A designated browser tab has already started to convert your content.'
								) }
								isPrimary
							/>
							<hr />
							<h2>{ __( 'Reset ongoing conversion' ) }</h2>
							<p>
								{ __(
									'In case that your active conversion browser tab has been closed by accident, or it has been interrupted and closed unexpectedly, you may reset the conversion status here, and start converting all over again.'
								) }
							</p>
							<Notice
								noticeText={ __(
									'This will enable you to restart the conversion, but any previous results may be lost.'
								) }
								isWarning
							/>
							<div className="newspack-buttons-card">
								<Button isPrimary onClick={ this.handleOnClickResetConversion }>
									{ __( 'Reset conversion' ) }
								</Button>
							</div>
						</Card>
					</Grid>
				</Fragment>
			);
		} else {
			return (
				<Fragment>
					<div className="newspack-logo-wrapper">
						<NewspackLogo />
					</div>
					<Grid>
						<FormattedHeader
							headerIcon={ <UnarchiveIcon /> }
							headerText={ __( 'Run conversion' ) }
							subHeaderText={ __( 'Start conversion to Gutenberg blocks.' ) }
						/>
						<Card>
							{ !! someConversionsFailed && (
								<Fragment>
									<Notice
										noticeText={ __(
											"Looks like some entries weren't converted properly. You may retry converting these."
										) }
										isError
									/>
									<TextControl
										label={ __( 'Number of failed entries' ) }
										disabled={ true }
										value={ countFailedConverting }
									/>
									<Card noBackground className="newspack-card__buttons-card">
										<Button isPrimary onClick={ this.handleOnClickInitializeRetryFailed }>
											{ __( 'Retry conversion' ) }
										</Button>
									</Card>
									<hr />
								</Fragment>
							) }
							<Notice
								noticeText={ __( 'This page will automatically reload for every batch.' ) }
								isPrimary
							/>
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
							<Notice
								noticeText={ __(
									'Once started, the conversion should not be interrupted! Your browser page needs to remain active until conversion is complete.'
								) }
								isWarning
							/>
							<div className="newspack-buttons-card">
								<Button isPrimary onClick={ this.handleOnClickInitializeConversion }>
									{ __( 'Run conversion' ) }
								</Button>
								<Button isSecondary href="/wp-admin/admin.php?page=newspack-content-converter-settings">
									{ __( 'Settings' ) }
								</Button>
							</div>
						</Card>
					</Grid>
				</Fragment>
			);
		}
	}
}

export default Conversion;
