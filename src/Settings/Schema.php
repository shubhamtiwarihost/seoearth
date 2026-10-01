<?php
/**
 * Settings schema.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The list of site-wide settings and the sections they belong to.
 *
 * Feature phases add their fields here. Extensions add fields with the
 * `stseo_settings_fields` filter.
 */
class Schema {

	/**
	 * Separator keys => characters used in titles.
	 */
	public const SEPARATORS = array(
		'hyphen' => '-',
		'ndash'  => '–',
		'mdash'  => '—',
		'pipe'   => '|',
		'middot' => '·',
		'bullet' => '•',
		'raquo'  => '»',
	);

	/**
	 * Built fields, cached per request.
	 *
	 * @var array<string, Field>|null
	 */
	private $fields = null;

	/**
	 * Cache validity code the fields were built under.
	 *
	 * @var string
	 */
	private $cache_code = '';

	/**
	 * Section IDs => translated titles, in display order.
	 *
	 * @return array<string, string>
	 */
	public function sections(): array {
		$sections = array(
			'general'  => __( 'Site identity', 'shubhamtiwari-seo-tools' ),
			'sitemap'  => __( 'XML sitemap', 'shubhamtiwari-seo-tools' ),
			'social'   => __( 'Social sharing', 'shubhamtiwari-seo-tools' ),
			'schema'   => __( 'Structured data', 'shubhamtiwari-seo-tools' ),
			'crumbs'   => __( 'Breadcrumbs', 'shubhamtiwari-seo-tools' ),
			'images'   => __( 'Images', 'shubhamtiwari-seo-tools' ),
			'advanced' => __( 'Advanced', 'shubhamtiwari-seo-tools' ),
		);

		/**
		 * Filters the settings sections (ID => title, in display order).
		 *
		 * @param array<string, mixed> $sections Sections.
		 */
		$filtered = apply_filters( 'stseo_settings_sections', $sections );

		$clean = array();
		foreach ( (array) $filtered as $id => $title ) {
			if ( is_string( $title ) ) {
				$clean[ (string) $id ] = $title;
			}
		}
		return $clean;
	}

	/**
	 * All fields keyed by storage key.
	 *
	 * @return array<string, Field>
	 */
	public function fields(): array {
		if ( null !== $this->fields && $this->cache_code === $this->cache_code() ) {
			return $this->fields;
		}

		$fields = array(
			new Field(
				'separator',
				'general',
				Field::TYPE_ENUM,
				'ndash',
				__( 'Title separator', 'shubhamtiwari-seo-tools' ),
				__( 'Placed between parts of generated titles, for example “Post title – Site name”.', 'shubhamtiwari-seo-tools' ),
				self::SEPARATORS
			),
			new Field(
				'site_represents',
				'general',
				Field::TYPE_ENUM,
				'organization',
				__( 'This website represents', 'shubhamtiwari-seo-tools' ),
				__( 'Used in structured data to describe who publishes this site.', 'shubhamtiwari-seo-tools' ),
				array(
					'organization' => __( 'An organization', 'shubhamtiwari-seo-tools' ),
					'person'       => __( 'A person', 'shubhamtiwari-seo-tools' ),
				)
			),
			new Field(
				'organization_name',
				'general',
				Field::TYPE_TEXT,
				'',
				__( 'Organization or person name', 'shubhamtiwari-seo-tools' ),
				__( 'Leave empty to use the site title.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'organization_logo',
				'general',
				Field::TYPE_IMAGE_URL,
				'',
				__( 'Logo URL', 'shubhamtiwari-seo-tools' ),
				__( 'A square image of at least 112 × 112 pixels works best.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'sitemap_enabled',
				'sitemap',
				Field::TYPE_BOOL,
				true,
				__( 'Enable the XML sitemap', 'shubhamtiwari-seo-tools' ),
				__( 'Helps search engines find your content. Uses the sitemap built into WordPress at /wp-sitemap.xml.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'sitemap_images',
				'sitemap',
				Field::TYPE_BOOL,
				true,
				__( 'Include images', 'shubhamtiwari-seo-tools' ),
				__( 'Lists the featured image and up to 10 images from this site found in each post.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'sitemap_users',
				'sitemap',
				Field::TYPE_BOOL,
				true,
				__( 'Include author archives', 'shubhamtiwari-seo-tools' ),
				__( 'Always left out while author archives are hidden from search engines.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'social_og_enabled',
				'social',
				Field::TYPE_BOOL,
				true,
				__( 'Add Open Graph tags', 'shubhamtiwari-seo-tools' ),
				__( 'Controls the title, description and image shown when a page is shared on Facebook, LinkedIn, WhatsApp, Slack and similar apps.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'social_twitter_enabled',
				'social',
				Field::TYPE_BOOL,
				true,
				__( 'Add X (Twitter) Card tags', 'shubhamtiwari-seo-tools' ),
				__( 'Controls how links look when shared on X.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'default_social_image',
				'social',
				Field::TYPE_IMAGE_URL,
				'',
				__( 'Default sharing image URL', 'shubhamtiwari-seo-tools' ),
				__( 'Used when a page has no featured image. 1200 × 630 pixels is recommended.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'twitter_site',
				'social',
				Field::TYPE_TWITTER_HANDLE,
				'',
				__( 'X (Twitter) username', 'shubhamtiwari-seo-tools' ),
				__( 'Without the @. Letters, numbers and underscores, up to 15 characters.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'schema_enabled',
				'schema',
				Field::TYPE_BOOL,
				true,
				__( 'Add structured data (schema.org)', 'shubhamtiwari-seo-tools' ),
				__( 'Describes your site, pages, articles, authors and breadcrumbs to search engines in one JSON-LD block. Uses the “Site identity” settings above.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'breadcrumbs_home',
				'crumbs',
				Field::TYPE_TEXT,
				'',
				__( 'Label for the homepage', 'shubhamtiwari-seo-tools' ),
				__( 'First item of the trail. Leave empty for “Home”.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'breadcrumbs_separator',
				'crumbs',
				Field::TYPE_TEXT,
				'›',
				__( 'Separator', 'shubhamtiwari-seo-tools' ),
				__( 'Shown between items, for example › or / or ». Screen readers skip it.', 'shubhamtiwari-seo-tools' )
			),
			new Field(
				'remove_data_on_uninstall',
				'advanced',
				Field::TYPE_BOOL,
				false,
				__( 'Remove all ShubhamTiwari SEO Tools data when the plugin is deleted', 'shubhamtiwari-seo-tools' ),
				__( 'Deletes ShubhamTiwari SEO Tools settings and SEO data stored for your content. Your posts and pages are never deleted. This cannot be undone.', 'shubhamtiwari-seo-tools' )
			),
		);

		$by_key = array();
		foreach ( $fields as $field ) {
			$by_key[ $field->key ] = $field;
		}

		/**
		 * Filters the settings fields.
		 *
		 * Entries that are not Field instances are ignored.
		 *
		 * @param array<string, mixed> $by_key Fields keyed by storage key.
		 */
		$filtered = apply_filters( 'stseo_settings_fields', $by_key );

		$result = array();
		foreach ( (array) $filtered as $field ) {
			if ( $field instanceof Field ) {
				$result[ $field->key ] = $field;
			}
		}

		$this->fields     = $result;
		$this->cache_code = $this->cache_code();
		return $result;
	}

	/**
	 * Changes whenever a post type or taxonomy is registered or unregistered,
	 * because fields added by extensions (search appearance templates) are
	 * derived from them.
	 */
	private function cache_code(): string {
		return implode(
			':',
			array(
				did_action( 'init' ),
				did_action( 'registered_post_type' ),
				did_action( 'unregistered_post_type' ),
				did_action( 'registered_taxonomy' ),
				did_action( 'unregistered_taxonomy' ),
			)
		);
	}

	/**
	 * Default value for every field.
	 *
	 * @return array<string, bool|string>
	 */
	public function defaults(): array {
		$defaults = array();
		foreach ( $this->fields() as $key => $field ) {
			$defaults[ $key ] = $field->default;
		}
		return $defaults;
	}
}
