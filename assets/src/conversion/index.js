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
	fetchPrepareConversion,
	fetchResetConversion,
	downloadListConvertedIds,
	downloadListUnsuccessfullyConvertedIds,
} from '../utilities';

class Conversion extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			isConversionPrepared: false,
			unconvertedCount: '...',
			totalNumberOfBatches: '...',
			areThereSuccessfullyConvertedIds: false,
			areThereUnconvertedIds: false,
			minIdToProcess: -1,
			maxIdToProcess: -1,
			pluginVersion: '',
		};
	}

	componentDidMount() {
		return fetchConversionInfo().then( response => {
			if ( response ) {
				const {
					isConversionPrepared,
					unconvertedCount,
					totalNumberOfBatches,
					areThereSuccessfullyConvertedIds,
					areThereUnconvertedIds,
					minIdToProcess,
					maxIdToProcess,
					pluginVersion,
				} = response;
				this.setState( {
					isConversionPrepared,
					unconvertedCount,
					totalNumberOfBatches,
					areThereSuccessfullyConvertedIds,
					areThereUnconvertedIds,
					minIdToProcess,
					maxIdToProcess,
					pluginVersion,
				} );
			}
			return new Promise( ( resolve, reject ) => resolve() );
		} );
	}

	handleOnClickRunConversion = () => {
		return fetchPrepareConversion().then( response => {
			if ( response && response.success ) {
				window.parent.location = '/wp-admin/post-new.php?newspack-content-converter';
			}
		} );
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
			isConversionPrepared,
			unconvertedCount,
			totalNumberOfBatches,
			areThereSuccessfullyConvertedIds,
			areThereUnconvertedIds,
			minIdToProcess,
			maxIdToProcess,
			pluginVersion,
		} = this.state;
		if ( '1' == isConversionPrepared ) {
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
								<p>{ __( 'A conversion is already running' ) }</p>
							</FlexBlock>
						</CardHeader>
						<CardBody>
							<Notice status="warning" isDismissible={ false }>
								{ __(
									'Conversion of your content has already been started in a designated browser tab. In case it was terminated or closed unexpectedly, you can reset the conversion here and resume converting again.'
								) }
							</Notice>
							<p>
								{ __(
									'Before attempting to see results on this page or to convert again, wait for the ongoing conversion to finish up.'
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
								<p>
									{ __('Plugin version: ') + pluginVersion }
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
								{ __( 'Conversion permanently modifies content so it is recommended to perform a full database backup before running it.' ) }
							</h4>
							<TextControl
								label={ __( 'Number of unconverted entries' ) }
								disabled={ true }
								value={ unconvertedCount }
							/>
							<TextControl
								label={ __( 'Total conversion batches' ) }
								disabled={ true }
								value={ totalNumberOfBatches }
							/>
						</CardBody>
						{ ( maxIdToProcess > 0 || minIdToProcess > 0 )&& (
							<CardBody>
								{ ( minIdToProcess > 0 ) && ( <p>{ sprintf( __( 'Min post ID to process is set to %d' ), minIdToProcess) } </p> ) }
								{ ( maxIdToProcess > 0 ) && ( <p>{ sprintf( __( 'Max post ID to process is set to %d' ), maxIdToProcess) } </p> ) }
							</CardBody>
						) }
						{ ( areThereSuccessfullyConvertedIds || areThereUnconvertedIds )&& (
							<CardBody>
								{ areThereSuccessfullyConvertedIds && (
									<a href="#" onClick={ this.handleDownloadListConverted }>{ __( 'Download IDs of all converted entries' ) }</a>
								) }
								{ areThereSuccessfullyConvertedIds && areThereUnconvertedIds && (
									<br/>
								) }
								{ areThereUnconvertedIds && (
									<a href="#" onClick={ this.handleDownloadListUnsuccessfullyConverted }>{ __( 'Download IDs of unconverted entries' ) }</a>
								) }
							</CardBody>
						) }
						<CardFooter justify="flex-end">
							<Button isPrimary onClick={ this.handleOnClickRunConversion } disabled={ ( true == areThereUnconvertedIds ) ? false : true } >
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
