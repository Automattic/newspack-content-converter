/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { runMultiplePosts } from '../utilities';
import './style.css';

class ContentConverter extends Component {
	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		this.state = {
			isActive: false,
			postIdsCsv: '',
		};
	}
	/*
	 * Conversion main handler. Chains promises to ensure sequential async operations execution.
	 */
	handleOnClick = () => {
		return Promise.resolve()
			.then( () => {
				return new Promise( ( resolve, reject ) => {
					this.setState( { isActive: true } );
					resolve();
				} );
			} )
			.then( () => {
				const { postIdsCsv } = this.state;

				return runMultiplePosts( postIdsCsv );
			} )
			.then( () => {
				return new Promise( ( resolve, reject ) => {
					this.setState( { isActive: false } );
					resolve();
				} );
			} );
	};

	/*
	 * handlePostIdsInputChange().
	 */
	handlePostIdsInputChange = ( { currentTarget: input } ) => {
		const postIdsCsv = input.value;

		this.setState( { postIdsCsv } );
	};

	/*
	 * render().
	 */
	render() {
		const { isActive, postIdsCsv } = this.state;
		const isButtonDisabled = isActive;
		const labelButton = isActive ? __( 'Converting...' ) : __( 'Convert' );

		return (
			<div id="ncc-content">
				<h1>{ __( 'Newspack Content Converter' ) }</h1>
				<label htmlFor="ncc_post_ids_csv">{ __( 'Post IDs CSV' ) }</label>
				<input
					type="text"
					id="ncc_post_ids_csv"
					placeholder={ __( 'Post IDs CSV' ) }
					value={ postIdsCsv }
					onChange={ this.handlePostIdsInputChange }
				/>
				<input
					type="submit"
					id="ncc_run_button"
					value={ labelButton }
					onClick={ event => this.handleOnClick( event ) }
					disabled={ isButtonDisabled }
				/>
			</div>
		);
	}
}

export default ContentConverter;
