/**
 * SEOEarth Classic Editor metabox: search preview and analysis.
 *
 * Sends the current (unsaved) form values to the analysis endpoint and shows
 * the results. Every value is written with textContent, never as HTML.
 */
( function () {
	'use strict';

	const config = window.seoearthMetabox;
	const box = document.querySelector( '.seoearth-metabox' );
	if ( ! config || ! box || ! window.wp || ! window.wp.apiFetch ) {
		return;
	}

	const { __, sprintf } = window.wp.i18n;
	const apiFetch = window.wp.apiFetch;
	const statusEl = box.querySelector( '.seoearth-analysis-status' );
	const resultsEl = box.querySelector( '#seoearth-results' );
	const button = box.querySelector( '#seoearth-analyse' );

	const STATUS_LABELS = {
		error: __( 'Problem', 'seoearth' ),
		warning: __( 'Improvement', 'seoearth' ),
		info: __( 'Note', 'seoearth' ),
		pass: __( 'Good', 'seoearth' ),
	};

	const value = ( selector ) => {
		const el = document.querySelector( selector );
		return el ? el.value : undefined;
	};

	/**
	 * Post content from TinyMCE (Visual tab) or the textarea (Text tab).
	 */
	const content = () => {
		const editor = window.tinymce && window.tinymce.get( 'content' );
		if ( editor && ! editor.isHidden() ) {
			return editor.getContent();
		}
		return value( '#content' );
	};

	const request = () => {
		const data = {
			post_id: config.postId,
			keyphrase: value( '#seoearth-keyphrase' ),
			seo_title: value( '#seoearth-title' ),
			seo_description: value( '#seoearth-description' ),
			title: value( '#title' ),
			excerpt: value( '#excerpt' ),
			slug: value( '#post_name' ),
			content: content(),
		};
		Object.keys( data ).forEach( ( key ) => {
			if ( data[ key ] === undefined ) {
				delete data[ key ];
			}
		} );
		return data;
	};

	const renderReport = ( heading, report ) => {
		const section = document.createElement( 'section' );
		const h = document.createElement( 'h4' );
		h.textContent = heading;
		section.appendChild( h );

		const list = document.createElement( 'ul' );
		report.results.forEach( ( result ) => {
			const item = document.createElement( 'li' );
			item.className = 'seoearth-result seoearth-result--' + result.status;

			const label = document.createElement( 'strong' );
			label.className = 'seoearth-result__status';
			label.textContent = ( STATUS_LABELS[ result.status ] || result.status ) + ': ';
			item.appendChild( label );
			item.appendChild( document.createTextNode( result.message ) );

			if ( result.recommendation ) {
				const advice = document.createElement( 'span' );
				advice.className = 'seoearth-result__advice';
				advice.textContent = ' ' + result.recommendation;
				item.appendChild( advice );
			}
			list.appendChild( item );
		} );
		section.appendChild( list );
		return section;
	};

	let running = 0;
	const analyse = () => {
		const ticket = ++running;
		statusEl.textContent = __( 'Checking…', 'seoearth' );
		button.disabled = true;

		apiFetch( { path: config.path, method: 'POST', data: request() } )
			.then( ( response ) => {
				if ( ticket !== running ) {
					return; // A newer check started meanwhile.
				}
				box.querySelector( '.seoearth-preview__title' ).textContent = response.preview.title;
				box.querySelector( '.seoearth-preview__description' ).textContent = response.preview.description;

				resultsEl.replaceChildren(
					renderReport( __( 'SEO', 'seoearth' ), response.seo ),
					renderReport( __( 'Readability', 'seoearth' ), response.readability )
				);
				const counts = response.seo.counts;
				statusEl.textContent = sprintf(
					/* translators: 1: number of problems, 2: number of improvements. */
					__( 'Done: %1$d problems, %2$d improvements.', 'seoearth' ),
					counts.error + response.readability.counts.error,
					counts.warning + response.readability.counts.warning
				);
			} )
			.catch( ( error ) => {
				if ( ticket === running ) {
					statusEl.textContent = sprintf(
						/* translators: %s: error message. */
						__( 'The check failed: %s', 'seoearth' ),
						( error && error.message ) || __( 'unknown error', 'seoearth' )
					);
				}
			} )
			.finally( () => {
				if ( ticket === running ) {
					button.disabled = false;
				}
			} );
	};

	let timer;
	const scheduleAnalyse = () => {
		window.clearTimeout( timer );
		timer = window.setTimeout( analyse, 1500 );
	};

	button.addEventListener( 'click', analyse );
	[ '#seoearth-keyphrase', '#seoearth-title', '#seoearth-description', '#title' ].forEach( ( selector ) => {
		const el = document.querySelector( selector );
		if ( el ) {
			el.addEventListener( 'input', scheduleAnalyse );
		}
	} );
	analyse();
}() );
