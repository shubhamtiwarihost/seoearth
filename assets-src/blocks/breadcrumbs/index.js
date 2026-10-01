/**
 * "Breadcrumbs" block (editor side). The block is dynamic: PHP renders the
 * real trail on the frontend, so the editor shows a sample with the same markup.
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { createElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const SAMPLE = [
	__( 'Home', 'shubhamtiwari-seo-tools' ),
	__( 'Category', 'shubhamtiwari-seo-tools' ),
	__( 'Current page', 'shubhamtiwari-seo-tools' ),
];

function Edit() {
	const blockProps = useBlockProps( { className: 'stseo-breadcrumbs' } );
	return (
		<nav
			{ ...blockProps }
			aria-label={ __( 'Breadcrumbs', 'shubhamtiwari-seo-tools' ) }
		>
			<ol className="stseo-breadcrumbs__list">
				{ SAMPLE.map( ( name, index ) => (
					<li key={ name } className="stseo-breadcrumbs__item">
						{ index < SAMPLE.length - 1 ? (
							// Not a real link in the editor; the frontend links to the real pages.
							<a
								href="#stseo-breadcrumbs"
								onClick={ ( event ) => event.preventDefault() }
							>
								{ name }
							</a>
						) : (
							<span aria-current="page">{ name }</span>
						) }
						{ index < SAMPLE.length - 1 && (
							<span
								className="stseo-breadcrumbs__separator"
								aria-hidden="true"
							>
								›
							</span>
						) }
					</li>
				) ) }
			</ol>
		</nav>
	);
}

registerBlockType( 'stseo/breadcrumbs', {
	apiVersion: 3,
	title: __( 'Breadcrumbs', 'shubhamtiwari-seo-tools' ),
	description: __(
		'Shows the path from the homepage to the current page. The trail is built for each page when it is displayed.',
		'shubhamtiwari-seo-tools'
	),
	category: 'theme',
	icon: 'arrow-right-alt2',
	keywords: [
		__( 'navigation', 'shubhamtiwari-seo-tools' ),
		__( 'path', 'shubhamtiwari-seo-tools' ),
	],
	edit: Edit,
	save: () => null,
} );
