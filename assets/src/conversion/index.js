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
	fetchResetConversion,
	downloadListConvertedIds,
	downloadListUnsuccessfullyConvertedIds,
} from '../utilities';

class Conversion extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			isConversionOngoing: false,
			unconvertedCount: '...',
			numberOfBatchesToBeConverted: '...',
			areThereSuccessfullyConvertedIds: false,
			areThereUnsuccessfullyConvertedIds: false,
		};
	}

	componentDidMount() {
		return fetchConversionInfo().then( response => {
			if ( response ) {
				const {
					isConversionOngoing,
					unconvertedCount,
					numberOfBatchesToBeConverted,
					areThereSuccessfullyConvertedIds,
					areThereUnsuccessfullyConvertedIds,
				} = response;
				this.setState( {
					isConversionOngoing,
					unconvertedCount,
					numberOfBatchesToBeConverted,
					areThereSuccessfullyConvertedIds,
					areThereUnsuccessfullyConvertedIds,
				} );
			}
			return new Promise( ( resolve, reject ) => resolve() );
		} );
	}

	handleOnClickRunConversion = () => {
		window.parent.location = '/wp-admin/post-new.php?newspack-content-converter';
	};

	handleDownloadListConverted = () => {
		downloadListConvertedIds();
	};

	handleDownloadListUnsuccessfullyConverted = () => {
		downloadListUnsuccessfullyConvertedIds();
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
			unconvertedCount,
			numberOfBatchesToBeConverted,
			areThereSuccessfullyConvertedIds,
			areThereUnsuccessfullyConvertedIds,
		} = this.state;

		if ( '1' == isConversionOngoing ) {
			return (
				<Fragment>
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
				</Fragment>
			);
		} else {
			return (
				<Fragment>
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
								<h2>{ __( 'Newspack Content Converter' ) }</h2>
								<p>
									{ __( 'Convert classic HTML to Gutenberg blocks' ) }
								</p>
							</FlexBlock>
						</CardHeader>
						<CardBody>
							<Notice status="warning" isDismissible={ false }>
								{ __(
									'Once started, the conversion should not be interrupted! Your browser tab needs to remain active until conversion is complete.'
								) }
							</Notice>
							<h4>
								{ __( 'This page will automatically reload for every batch.' ) }
							</h4>
							<TextControl
								label={ __( 'Number of unconverted entries' ) }
								disabled={ true }
								value={ unconvertedCount }
							/>
							<TextControl
								label={ __( 'Total conversion batches' ) }
								disabled={ true }
								value={ numberOfBatchesToBeConverted }
							/>
						</CardBody>
							<CardBody>
								{ areThereSuccessfullyConvertedIds && (
									<a href="#" onClick={ this.handleDownloadListConverted }>{ __( 'Download list of converted IDs' ) }</a>
								) }
								<br/>
								{ areThereUnsuccessfullyConvertedIds && (
									<a href="#" onClick={ this.handleDownloadListUnsuccessfullyConverted }>{ __( 'Download list of unsuccessfully converted IDs' ) }</a>
								) }
							</CardBody>
						<CardFooter justify="flex-end">
							<Button
								isSecondary
								href="/wp-admin/admin.php?page=newspack-content-converter-settings"
							>
								{ __( 'Settings' ) }
							</Button>
							<Button isPrimary onClick={ this.handleOnClickRunConversion }>
								{ __( 'Run Conversion' ) }
							</Button>
						</CardFooter>
					</Card>
				</Fragment>
			);
		}
	}
}

export default Conversion;
