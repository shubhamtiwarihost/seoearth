/**
 * Presentational components for the SEOEarth sidebar.
 */
import { createElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { charLength, lengthBand } from './utils';

export const STATUS = {
	error: { symbol: '✕', label: __( 'Problem', 'seoearth' ) },
	warning: { symbol: '!', label: __( 'Improvement', 'seoearth' ) },
	info: { symbol: 'i', label: __( 'Note', 'seoearth' ) },
	pass: { symbol: '✓', label: __( 'Good', 'seoearth' ) },
};

/**
 * Status marker: symbol for sighted users, word for everyone (never color alone).
 *
 * @param {Object} props        Props.
 * @param {string} props.status Status.
 */
export function StatusMarker( { status } ) {
	const info = STATUS[ status ] || STATUS.info;
	return (
		<span className={ `seoearth-status seoearth-status--${ status }` }>
			<span aria-hidden="true" className="seoearth-status__symbol">
				{ info.symbol }
			</span>
			<span className="seoearth-status__label">{ info.label }</span>
		</span>
	);
}

/**
 * Character counter shown under a field.
 *
 * @param {Object} props      Props.
 * @param {string} props.text Rendered text.
 * @param {number} props.min  Recommended minimum.
 * @param {number} props.max  Recommended maximum.
 */
export function LengthHint( { text, min, max } ) {
	const length = charLength( text );
	const band = lengthBand( length, min, max );
	let message;
	switch ( band ) {
		case 'empty':
			message = __( 'Empty.', 'seoearth' );
			break;
		case 'short':
			message = sprintf(
				/* translators: 1: characters, 2: recommended minimum, 3: recommended maximum. */
				__(
					'%1$d characters — a little short (aim for %2$d–%3$d).',
					'seoearth'
				),
				length,
				min,
				max
			);
			break;
		case 'long':
			message = sprintf(
				/* translators: 1: characters, 2: recommended minimum, 3: recommended maximum. */
				__(
					'%1$d characters — may be cut off (aim for %2$d–%3$d).',
					'seoearth'
				),
				length,
				min,
				max
			);
			break;
		default:
			message = sprintf(
				/* translators: 1: characters, 2: recommended minimum, 3: recommended maximum. */
				__( '%1$d characters — good length (%2$d–%3$d).', 'seoearth' ),
				length,
				min,
				max
			);
	}
	return (
		<span className={ `seoearth-length seoearth-length--${ band }` }>
			{ message }
		</span>
	);
}

/**
 * Search result preview.
 *
 * @param {Object} props             Props.
 * @param {string} props.title       Rendered title.
 * @param {string} props.url         Page URL.
 * @param {string} props.description Rendered description.
 */
export function SearchPreview( { title, url, description } ) {
	return (
		<div
			className="seoearth-preview"
			aria-label={ __( 'Search result preview', 'seoearth' ) }
			role="group"
		>
			<p className="seoearth-preview__url">{ url }</p>
			<p className="seoearth-preview__title">
				{ title || __( '(no title)', 'seoearth' ) }
			</p>
			<p className="seoearth-preview__description">
				{ description ||
					__(
						'No description: search engines will pick text from the page.',
						'seoearth'
					) }
			</p>
		</div>
	);
}

/**
 * List of analysis results.
 *
 * @param {Object} props        Props.
 * @param {Object} props.report Report {results: [...]}.
 */
export function ResultList( { report } ) {
	if ( ! report ) {
		return null;
	}
	return (
		<ul className="seoearth-results">
			{ report.results.map( ( result ) => (
				<li
					key={ result.id }
					className={ `seoearth-result seoearth-result--${ result.status }` }
				>
					<StatusMarker status={ result.status } />
					<span className="seoearth-result__message">
						{ result.message }
					</span>
					{ result.recommendation && (
						<span className="seoearth-result__advice">
							{ result.recommendation }
						</span>
					) }
				</li>
			) ) }
		</ul>
	);
}
