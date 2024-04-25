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
	TextControl,
	TextareaControl
} from '@wordpress/components';

/**
 * Newspack dependencies.
 */
import { NewspackLogo } from 'newspack-components';

/**
 * Internal dependencies.
 */
import { fetchRestoreInfo } from '../utilities';

class Restore extends Component {
	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		this.state = {
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

	handleRestoreContentOnClick() {
	}

	/*
	 * render().
	 */
	render() {
		const { numberOfConvertedIds, idCsvs } = this.state;
		const isRestoreButtonDisabled = ( '...' === numberOfConvertedIds || 0 == numberOfConvertedIds ) && ( '' === idCsvs );

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
							<h2>{ __( 'Restore' ) }</h2>
							<p>
								{ __( 'Restore posts to original content before conversion.' ) }
							</p>
						</FlexBlock>
					</CardHeader>
					<CardBody>
						<p>
							{ __(
								'Restore post contents to the original content before the conversion to blocks was performed.'
							) }
						</p>
						<TextControl
							label={ __( 'Total number of converted IDs' ) }
							disabled={ true }
							value={ numberOfConvertedIds }
							/>

						<TextareaControl
							label={ __( 'Optional -- custom CSV post IDs' ) }
							onChange={ ( value ) => { this.handleTextControlOnChange( value ); } }
						/>
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'By entering some custom CSV post IDs, only those posts contents will be restored. Otherwise all converted posts will be restored.'
							) }
						</Notice>
					</CardBody>
					<CardFooter justify="flex-end">
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
	}
}

export default Restore;
