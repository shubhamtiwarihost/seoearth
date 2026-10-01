/**
 * The ShubhamTiwari SEO Tools sidebar.
 */
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import {
	Button,
	CheckboxControl,
	Notice,
	PanelBody,
	SelectControl,
	Spinner,
	TextareaControl,
	TextControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import * as editPost from '@wordpress/edit-post';
import * as editor from '@wordpress/editor';
import { createElement, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import {
	LengthHint,
	ResultList,
	SearchPreview,
	StatusMarker,
} from './components';
import { KEYS, useAnalysis, useSeoMeta } from './hooks';
import {
	indexChoice,
	overallStatus,
	parseRobots,
	withDirective,
	withIndexChoice,
} from './utils';

// WordPress 6.6 moved these to @wordpress/editor; older versions only have them in @wordpress/edit-post.
const PluginSidebar = editor.PluginSidebar || editPost.PluginSidebar;
const PluginSidebarMoreMenuItem =
	editor.PluginSidebarMoreMenuItem || editPost.PluginSidebarMoreMenuItem;

const DIRECTIVES = {
	nofollow: __( 'Do not follow links (nofollow)', 'shubhamtiwari-seo-tools' ),
	noarchive: __(
		'Do not show a cached copy (noarchive)',
		'shubhamtiwari-seo-tools'
	),
	nosnippet: __(
		'Do not show a text snippet (nosnippet)',
		'shubhamtiwari-seo-tools'
	),
	noimageindex: __(
		'Do not index images (noimageindex)',
		'shubhamtiwari-seo-tools'
	),
};

/**
 * Analysis panel body: loading state, error, or results.
 *
 * @param {Object}  props         Props.
 * @param {Object}  props.report  Report.
 * @param {boolean} props.loading Whether a request is running.
 * @param {string}  props.error   Error message.
 */
function AnalysisBody( { report, loading, error } ) {
	return (
		<Fragment>
			{ loading && (
				<p className="stseo-loading">
					<Spinner /> { __( 'Checking…', 'shubhamtiwari-seo-tools' ) }
				</p>
			) }
			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ __(
						'The check could not run:',
						'shubhamtiwari-seo-tools'
					) }{ ' ' }
					{ error }
				</Notice>
			) }
			{ report && report.results.length === 0 && (
				<p>
					{ __(
						'There is not enough text to check yet.',
						'shubhamtiwari-seo-tools'
					) }
				</p>
			) }
			<ResultList report={ report } />
		</Fragment>
	);
}

/**
 * Panel title with the report's overall status (none until something was checked).
 *
 * @param {Object}      props        Props.
 * @param {string}      props.label  Panel name.
 * @param {Object|null} props.report Report.
 */
function PanelTitle( { label, report } ) {
	return (
		<span className="stseo-panel-title">
			{ label }
			{ report && report.results.length > 0 && (
				<StatusMarker status={ overallStatus( [ report ] ) } />
			) }
		</span>
	);
}

export default function Sidebar() {
	const [ meta, setMeta ] = useSeoMeta();
	const { data, loading, error } = useAnalysis( meta );
	const permalink = useSelect(
		( s ) => s( 'core/editor' ).getPermalink(),
		[]
	);

	const robots = meta[ KEYS.robots ] || '';
	const status = data
		? overallStatus( [ data.seo, data.readability ] )
		: null;
	const hidden =
		indexChoice( robots ) === 'noindex' ||
		( window.stseoEditor && ! window.stseoEditor.blogPublic );

	return (
		<Fragment>
			<PluginSidebarMoreMenuItem target="stseo-sidebar">
				{ __( 'ShubhamTiwari SEO Tools', 'shubhamtiwari-seo-tools' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				className="stseo-sidebar"
				name="stseo-sidebar"
				title={ __(
					'ShubhamTiwari SEO Tools',
					'shubhamtiwari-seo-tools'
				) }
				icon="search"
			>
				<PanelBody
					title={ __(
						'Search appearance',
						'shubhamtiwari-seo-tools'
					) }
				>
					{ hidden && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'This page is hidden from search engines.',
								'shubhamtiwari-seo-tools'
							) }
						</Notice>
					) }
					<SearchPreview
						title={ data ? data.preview.title : '' }
						url={ permalink || '' }
						description={ data ? data.preview.description : '' }
					/>
					<TextControl
						label={ __(
							'Focus keyphrase',
							'shubhamtiwari-seo-tools'
						) }
						help={ __(
							'The words people would search for to find this page.',
							'shubhamtiwari-seo-tools'
						) }
						value={ meta[ KEYS.keyphrase ] || '' }
						onChange={ ( value ) =>
							setMeta( KEYS.keyphrase, value )
						}
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'SEO title', 'shubhamtiwari-seo-tools' ) }
						help={
							<Fragment>
								{ __(
									'Leave empty to use the title template. Variables such as %%site_name%% are allowed.',
									'shubhamtiwari-seo-tools'
								) }{ ' ' }
								{ data && (
									<LengthHint
										text={ data.preview.title }
										min={ 30 }
										max={ 60 }
									/>
								) }
							</Fragment>
						}
						value={ meta[ KEYS.title ] || '' }
						onChange={ ( value ) => setMeta( KEYS.title, value ) }
						__nextHasNoMarginBottom
					/>
					<TextareaControl
						label={ __(
							'Meta description',
							'shubhamtiwari-seo-tools'
						) }
						help={
							<Fragment>
								{ __(
									'Leave empty to use the description template.',
									'shubhamtiwari-seo-tools'
								) }{ ' ' }
								{ data && (
									<LengthHint
										text={ data.preview.description }
										min={ 120 }
										max={ 160 }
									/>
								) }
							</Fragment>
						}
						value={ meta[ KEYS.description ] || '' }
						onChange={ ( value ) =>
							setMeta( KEYS.description, value )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody
					title={
						<PanelTitle
							label={ __(
								'SEO analysis',
								'shubhamtiwari-seo-tools'
							) }
							report={ data && data.seo }
						/>
					}
				>
					<AnalysisBody
						report={ data && data.seo }
						loading={ loading }
						error={ error }
					/>
				</PanelBody>

				<PanelBody
					title={
						<PanelTitle
							label={ __(
								'Readability',
								'shubhamtiwari-seo-tools'
							) }
							report={ data && data.readability }
						/>
					}
					initialOpen={ false }
				>
					<AnalysisBody
						report={ data && data.readability }
						loading={ loading }
						error={ error }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Social sharing', 'shubhamtiwari-seo-tools' ) }
					initialOpen={ false }
				>
					<TextControl
						label={ __(
							'Social sharing title',
							'shubhamtiwari-seo-tools'
						) }
						help={ __(
							'Leave empty to use the SEO title.',
							'shubhamtiwari-seo-tools'
						) }
						value={ meta[ KEYS.socialTitle ] || '' }
						onChange={ ( value ) =>
							setMeta( KEYS.socialTitle, value )
						}
						__nextHasNoMarginBottom
					/>
					<TextareaControl
						label={ __(
							'Social sharing description',
							'shubhamtiwari-seo-tools'
						) }
						help={ __(
							'Leave empty to use the meta description.',
							'shubhamtiwari-seo-tools'
						) }
						value={ meta[ KEYS.socialDescription ] || '' }
						onChange={ ( value ) =>
							setMeta( KEYS.socialDescription, value )
						}
						__nextHasNoMarginBottom
					/>
					<TextControl
						type="url"
						label={ __(
							'Social sharing image URL',
							'shubhamtiwari-seo-tools'
						) }
						help={ __(
							'Leave empty to use the featured image, then the default sharing image.',
							'shubhamtiwari-seo-tools'
						) }
						value={ meta[ KEYS.socialImage ] || '' }
						onChange={ ( value ) =>
							setMeta( KEYS.socialImage, value )
						}
						__nextHasNoMarginBottom
					/>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							onSelect={ ( media ) =>
								setMeta( KEYS.socialImage, media.url )
							}
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ __(
										'Choose from media library',
										'shubhamtiwari-seo-tools'
									) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>

				<PanelBody
					title={ __( 'Advanced', 'shubhamtiwari-seo-tools' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __(
							'Search engines',
							'shubhamtiwari-seo-tools'
						) }
						value={ indexChoice( robots ) }
						options={ [
							{
								value: '',
								label: __(
									'Default (from ShubhamTiwari SEO Tools settings)',
									'shubhamtiwari-seo-tools'
								),
							},
							{
								value: 'index',
								label: __(
									'Show in search results (index)',
									'shubhamtiwari-seo-tools'
								),
							},
							{
								value: 'noindex',
								label: __(
									'Hide from search results (noindex)',
									'shubhamtiwari-seo-tools'
								),
							},
						] }
						onChange={ ( value ) =>
							setMeta(
								KEYS.robots,
								withIndexChoice( robots, value )
							)
						}
						__nextHasNoMarginBottom
					/>
					{ Object.keys( DIRECTIVES ).map( ( directive ) => (
						<CheckboxControl
							key={ directive }
							label={ DIRECTIVES[ directive ] }
							checked={ parseRobots( robots ).includes(
								directive
							) }
							onChange={ ( checked ) =>
								setMeta(
									KEYS.robots,
									withDirective( robots, directive, checked )
								)
							}
							__nextHasNoMarginBottom
						/>
					) ) }
					<TextControl
						type="url"
						label={ __(
							'Canonical URL',
							'shubhamtiwari-seo-tools'
						) }
						help={ __(
							'Only if this page duplicates another one. Leave empty for the page’s own address.',
							'shubhamtiwari-seo-tools'
						) }
						value={ meta[ KEYS.canonical ] || '' }
						onChange={ ( value ) =>
							setMeta( KEYS.canonical, value )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				{ status && (
					<p className="screen-reader-text" aria-live="polite">
						{ STATUS_SUMMARY[ status ] }
					</p>
				) }
			</PluginSidebar>
		</Fragment>
	);
}

const STATUS_SUMMARY = {
	error: __(
		'ShubhamTiwari SEO Tools found problems.',
		'shubhamtiwari-seo-tools'
	),
	warning: __(
		'ShubhamTiwari SEO Tools suggests improvements.',
		'shubhamtiwari-seo-tools'
	),
	pass: __(
		'ShubhamTiwari SEO Tools checks passed.',
		'shubhamtiwari-seo-tools'
	),
};
