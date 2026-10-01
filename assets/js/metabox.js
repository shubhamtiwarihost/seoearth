/**
 * ShubhamTiwari SEO Tools Classic Editor metabox: search preview and analysis.
 *
 * Sends the current (unsaved) form values to the analysis endpoint and shows
 * the results. Every value is written with textContent, never as HTML.
 */
( function () {
	'use strict';

	const config = window.stseoMetabox;
	const box = document.querySelector( '.stseo-metabox' );
	if ( ! config || ! box || ! window.wp || ! window.wp.apiFetch ) {
		return;
	}

	const { __, sprintf } = window.wp.i18n;
	const apiFetch = window.wp.apiFetch;
	const statusEl = box.querySelector( '.stseo-analysis-status' );
	const resultsEl = box.querySelector( '#stseo-results' );
	const button = box.querySelector( '#stseo-analyse' );

	const STATUS_LABELS = {
		error: __( 'Problem', 'shubhamtiwari-seo-tools' ),
		warning: __( 'Improvement', 'shubhamtiwari-seo-tools' ),
		info: __( 'Note', 'shubhamtiwari-seo-tools' ),
		pass: __( 'Good', 'shubhamtiwari-seo-tools' ),
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
			keyphrase: value( '#stseo-keyphrase' ),
			seo_title: value( '#stseo-title' ),
			seo_description: value( '#stseo-description' ),
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
			item.className = 'stseo-result stseo-result--' + result.status;

			const label = document.createElement( 'strong' );
			label.className = 'stseo-result__status';
			label.textContent = ( STATUS_LABELS[ result.status ] || result.status ) + ': ';
			item.appendChild( label );
			item.appendChild( document.createTextNode( result.message ) );

			if ( result.recommendation ) {
				const advice = document.createElement( 'span' );
				advice.className = 'stseo-result__advice';
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
		statusEl.textContent = __( 'Checking…', 'shubhamtiwari-seo-tools' );
		button.disabled = true;

		apiFetch( { path: config.path, method: 'POST', data: request() } )
			.then( ( response ) => {
				if ( ticket !== running ) {
					return; // A newer check started meanwhile.
				}
				box.querySelector( '.stseo-preview__title' ).textContent = response.preview.title;
				box.querySelector( '.stseo-preview__description' ).textContent = response.preview.description;

				resultsEl.replaceChildren(
					renderReport( __( 'SEO', 'shubhamtiwari-seo-tools' ), response.seo ),
					renderReport( __( 'Readability', 'shubhamtiwari-seo-tools' ), response.readability )
				);
				const counts = response.seo.counts;
				statusEl.textContent = sprintf(
					/* translators: 1: number of problems, 2: number of improvements. */
					__( 'Done: %1$d problems, %2$d improvements.', 'shubhamtiwari-seo-tools' ),
					counts.error + response.readability.counts.error,
					counts.warning + response.readability.counts.warning
				);
			} )
			.catch( ( error ) => {
				if ( ticket === running ) {
					statusEl.textContent = sprintf(
						/* translators: %s: error message. */
						__( 'The check failed: %s', 'shubhamtiwari-seo-tools' ),
						( error && error.message ) || __( 'unknown error', 'shubhamtiwari-seo-tools' )
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
	[ '#stseo-keyphrase', '#stseo-title', '#stseo-description', '#title' ].forEach( ( selector ) => {
		const el = document.querySelector( selector );
		if ( el ) {
			el.addEventListener( 'input', scheduleAnalyse );
		}
	} );
	analyse();
}() );
