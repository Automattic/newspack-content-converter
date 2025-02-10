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
	Notice,
	TextControl,
	TextareaControl
} from '@wordpress/components';

/**
 * Newspack dependencies.
 */
import { NewspackIcon } from 'newspack-components';

/**
 * Internal dependencies.
 */
import { fetchRestoreInfo, fetchRestorePostContents, fetchFlushAllMetaBackups } from '../utilities';

class Restore extends Component {
	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		this.state = {
			restoredSuccessfully: null,
			numberOfConvertedIds: '...',
			idCsvs: '',
		};
	}

	componentDidMount() {
		return fetchRestoreInfo().then( response => {
			if ( response ) {
				const { numberOfConvertedIds } = response;
				this.setState( {
					numberOfConvertedIds,
				} );
			}
			return new Promise( ( resolve, reject ) => resolve() );
		} );
	}

	handleTextControlOnChange = (value) => {
		this.setState( { ...this.state, idCsvs: value } );
	}

	handleFlushAllMetaBackupsOnClick() {
		if (confirm("Are you sure you want to permanently delete any original content backups stored as postmeta for all converted posts?")) {
			return fetchFlushAllMetaBackups().then( response => {
				window.location.reload();
			} );
		}
	}

	handleRestoreContentOnClick() {
		const idCsvs = this.state.idCsvs;
		const regexCSVofIntegers = /^(\d+)(,\d+)*$/;

		// If custom CSV IDs are empty, restore all posts.
		if ( '' === idCsvs ) {
			if (confirm("Are you sure you want to restore all the converted post contents to before conversion?")) {
				// Restore all IDs.
				return fetchRestorePostContents().then( response => {
					if ( response && response.success ) {
						this.setState( { restoredSuccessfully: true } );
					} else {
						this.setState( { restoredSuccessfully: false } );
					}
					return new Promise( ( resolve, reject ) => resolve() );
				} );
			}
		} else {
			// Custom CSV IDs are not empty.

			// If not valid CSV, alert.
			if ( !regexCSVofIntegers.test(idCsvs) ) {
				alert('Please enter a valid CSV of integers, or leave the field empty to restore all posts.');
			} else {
				if (confirm("Are you sure you want to restore the custom IDs post contents to before conversion?")) {
					return fetchRestorePostContents( idCsvs ).then( response => {
						if ( response && response.success ) {
							this.setState( { restoredSuccessfully: true } );
						} else {
							this.setState( { restoredSuccessfully: false } );
						}
						return new Promise( ( resolve, reject ) => resolve() );
					} );
				}
			}
		}
	}

	/*
	 * render().
	 */
	render() {
		const { restoredSuccessfully, numberOfConvertedIds, idCsvs } = this.state;
		const isRestoreButtonDisabled = ( '...' === numberOfConvertedIds || 0 == numberOfConvertedIds ) && ( '' === idCsvs );

		if ( null === restoredSuccessfully ) {
			return (
				<Fragment>
					<div className="newspack-header">
						<NewspackIcon />
						<h2>{ __( 'Content Converter / Restore content' ) }</h2>
					</div>
					<Card>
						<CardBody>
							<p>
								{ __(
									'Restore post contents to the original contents before conversion to blocks.'
								) }
							</p>
							<TextControl
								label={ __( 'Total number of converted IDs' ) }
								disabled={ true }
								value={ numberOfConvertedIds }
								/>
							{ numberOfConvertedIds > 0 &&
							<p>
								{ __('Get the list of converted post IDs from ') }<a href="/wp-admin/admin.php?page=newspack-content-converter">{ __( 'the converter page' ) }</a>.
							</p>
							}

							<TextareaControl
								label={ __( 'Optional -- custom CSV post IDs' ) }
								onChange={ ( value ) => { this.handleTextControlOnChange( value ); } }
							/>
							<Notice status="warning" isDismissible={ false }>
								{ __(
									'By entering some custom CSV post IDs, only those posts contents will be restored. Otherwise all converted posts will be restored.'
								) }
							</Notice>
							<p>
								{ __( 'This plugin uses postmeta to back up and store original content of posts before the conversion. Click Flush backups to permanently delete all the content backups from your database.' ) }
							</p>
						</CardBody>
						<CardFooter justify="flex-end">
							<Button
								isSecondary
								onClick={ () => { this.handleFlushAllMetaBackupsOnClick() } }
							>
								{ __( 'Flush backups' ) }
							</Button>
							<Button
								isPrimary
								onClick={ () => { this.handleRestoreContentOnClick() } }
								disabled={ isRestoreButtonDisabled }
							>
								{ __( 'Restore content' ) }
							</Button>
						</CardFooter>
					</Card>
				</Fragment>
			);
		} else if ( true === restoredSuccessfully ) {
			return (
				<Fragment>
					<div className="newspack-header">
						<NewspackIcon />
						<h2>{ __( 'Content Converter / Content restored' ) }</h2>
					</div>
					<Card>
						<CardBody>
							<Notice isDismissible={ false } status="success">
								{ __( 'Content has been restored to before conversion.' ) }
							</Notice>
						</CardBody>
						<CardFooter justify="flex-end">
							<Button href="/wp-admin/admin.php?page=newspack-content-converter" isPrimary>
								{ __( 'Back to Converter' ) }
							</Button>
						</CardFooter>
					</Card>
				</Fragment>
			);
		} else if ( false === restoredSuccessfully ) {
			return (
				<Fragment>
					<div className="newspack-header">
						<NewspackIcon />
						<h2>{ __( 'Content Converter / Error' ) }</h2>
					</div>
					<Card>
						<CardBody>
							<Notice status="warning" isDismissible={ false }>
								{ __(
									'An error occurred while restoring posts to original content before conversion.'
								) }
							</Notice>
						</CardBody>
						<CardFooter justify="flex-end">
							<Button href="/wp-admin/admin.php?page=newspack-content-converter" isPrimary>
								{ __( 'Back to Converter' ) }
							</Button>
						</CardFooter>
					</Card>
				</Fragment>
			);
		}
	}
}

export default Restore;
