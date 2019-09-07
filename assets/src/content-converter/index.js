/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { runMultiplePosts, fetchConversionBatch } from '../utilities';
import './style.css';

class ContentConverter extends Component {
	/**
	 * Constructor.
	 */
	constructor(props) {
		super(props);

		this.state = {
			isActive: null,
			postIds: '',
			thisBatch: '',
			maxBatch: '',
		};
	}

	componentDidMount() {
		return Promise.resolve()
			.then(() => fetchConversionBatch())
			.then(response => {
				if (response && response.ids) {
					const { ids: postIds, thisBatch, maxBatch } = response;
					this.setState({ postIds, thisBatch, maxBatch, isActive: true });
					console.log(' ----------------------- ABOUT TO CONVERT IDS: ' + postIds);
					return runMultiplePosts(postIds);
				}

				return new Promise((resolve, reject) => resolve());
			})
			.then(() => {
				return new Promise((resolve, reject) => {
					console.log(' ----------------------- FINISHED.');
					if (this.state.postIds) {
						this.setState({ isActive: null });
						// This should disable the browser's "Reload page?" popup, although it doesn't always work as expected.
						window.onbeforeunload = function() {};
						// Reload this window to pick up the next batch.
						window.location.reload(true);
					} else {
						this.setState({ isActive: false });
					}

					return resolve();
				});
			});
	}

	/*
	 * render().
	 */
	render() {
		const { isActive, thisBatch, maxBatch } = this.state;

		if (null == isActive) {
			return (
				<div className="ncc-page">
					<h1>{__('Content Conversion')}</h1>
					<img src="/wp-admin/images/wpspin_light.gif" /> Loading...
				</div>
			);
		} else if (true == isActive) {
			return (
				<div className="ncc-page">
					<h1>{__('Content Conversion...')}</h1>
					<img src="/wp-admin/images/wpspin_light.gif" />
					&nbsp; {__('Now processing batch')} {thisBatch}/{maxBatch} ...
					<br />
					<h3>{__('Do not close this page!')}</h3>
					<ul>
						<li>
							{__(
								'This page will occasionally automatically reload, and notify you when the conversion is complete.'
							)}
						</li>
						<li>{__('If asked to Reload, chose yes.')}</li>
						<li>
							{__(
								'You may also carefully open an additional tab to convert another batch in parallel.'
							)}
						</li>
					</ul>
				</div>
			);
		} else if (false == isActive) {
			return (
				<div className="ncc-page">
					<h1>{__('Content Conversion Complete')}</h1>
					<p>{__('All queued content has been converted.')}</p>
				</div>
			);
		}
	}
}

export default ContentConverter;
