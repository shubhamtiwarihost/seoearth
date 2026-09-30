/**
 * Tests for the sidebar helpers.
 */
import {
	analysisRequest,
	charLength,
	indexChoice,
	lengthBand,
	overallStatus,
	parseRobots,
	withDirective,
	withIndexChoice,
} from '../utils';

describe( 'robots helpers', () => {
	it( 'parses stored values', () => {
		expect( parseRobots( ' NoIndex, nofollow ,,' ) ).toEqual( [
			'noindex',
			'nofollow',
		] );
		expect( parseRobots( undefined ) ).toEqual( [] );
	} );

	it( 'reads and replaces the index choice, keeping other directives', () => {
		expect( indexChoice( 'nofollow' ) ).toBe( '' );
		expect( indexChoice( 'index,nofollow' ) ).toBe( 'index' );
		expect( indexChoice( 'index,noindex' ) ).toBe( 'noindex' );

		expect( withIndexChoice( 'index,nofollow', 'noindex' ) ).toBe(
			'noindex,nofollow'
		);
		expect( withIndexChoice( 'noindex,nofollow', '' ) ).toBe( 'nofollow' );
		expect( withIndexChoice( '', 'index' ) ).toBe( 'index' );
	} );

	it( 'switches directives on and off without duplicates', () => {
		expect( withDirective( 'noindex', 'nofollow', true ) ).toBe(
			'noindex,nofollow'
		);
		expect( withDirective( 'noindex,nofollow', 'nofollow', true ) ).toBe(
			'noindex,nofollow'
		);
		expect( withDirective( 'noindex,nofollow', 'nofollow', false ) ).toBe(
			'noindex'
		);
	} );
} );

describe( 'lengthBand', () => {
	it.each( [
		[ 0, 'empty' ],
		[ 29, 'short' ],
		[ 30, 'good' ],
		[ 60, 'good' ],
		[ 61, 'long' ],
	] )( '%i characters is %s', ( length, band ) => {
		expect( lengthBand( length, 30, 60 ) ).toBe( band );
	} );
} );

describe( 'overallStatus', () => {
	it( 'returns the worst status, ignoring info', () => {
		const report = ( counts ) => ( {
			counts: { error: 0, warning: 0, info: 0, pass: 0, ...counts },
		} );
		expect( overallStatus( [ report( { pass: 3, info: 2 } ) ] ) ).toBe(
			'pass'
		);
		expect(
			overallStatus( [ report( { warning: 1 } ), report( { pass: 1 } ) ] )
		).toBe( 'warning' );
		expect(
			overallStatus( [
				report( { warning: 1 } ),
				report( { error: 1 } ),
			] )
		).toBe( 'error' );
		expect( overallStatus( [ null ] ) ).toBe( 'pass' );
	} );
} );

describe( 'charLength', () => {
	it( 'counts characters, not UTF-16 units', () => {
		expect( charLength( 'Boots – Acme' ) ).toBe( 12 );
		expect( charLength( '😀a' ) ).toBe( 2 );
		expect( charLength( null ) ).toBe( 0 );
	} );
} );

describe( 'analysisRequest', () => {
	it( 'sends every text field as a string (new posts have a numeric slug)', () => {
		expect(
			analysisRequest( 7, {
				keyphrase: 'boots',
				slug: 7,
				title: undefined,
				excerpt: null,
				content: '<p>x</p>',
			} )
		).toEqual( {
			post_id: 7,
			keyphrase: 'boots',
			seo_title: '',
			seo_description: '',
			title: '',
			slug: '7',
			excerpt: '',
			content: '<p>x</p>',
		} );
	} );
} );
