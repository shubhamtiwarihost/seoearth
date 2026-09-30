/**
 * SEOEarth settings screen: media library picker for image URL fields.
 * Only fills in the field; nothing is saved until the form is submitted.
 */
( function ( $ ) {
	'use strict';

	if ( ! window.wp || ! window.wp.media ) {
		return;
	}
	const { __ } = window.wp.i18n;

	$( document ).on( 'click', '.seoearth-pick-image', function ( event ) {
		event.preventDefault();
		const input = document.getElementById( this.getAttribute( 'data-target' ) );
		if ( ! input ) {
			return;
		}

		const frame = window.wp.media( {
			title: __( 'Choose an image', 'seoearth' ),
			button: { text: __( 'Use this image', 'seoearth' ) },
			library: { type: 'image' },
			multiple: false,
		} );
		frame.on( 'select', () => {
			const image = frame.state().get( 'selection' ).first().toJSON();
			input.value = image.url;
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			input.focus();
		} );
		frame.open();
	} );
}( window.jQuery ) );
