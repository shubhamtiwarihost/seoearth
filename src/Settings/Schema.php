<?php
/**
 * Settings schema.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The list of site-wide settings and the sections they belong to.
 *
 * Feature phases add their fields here. Extensions add fields with the
 * `seoearth_settings_fields` filter.
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
			'general'  => __( 'Site identity', 'seoearth' ),
			'sitemap'  => __( 'XML sitemap', 'seoearth' ),
			'social'   => __( 'Social sharing', 'seoearth' ),
			'schema'   => __( 'Structured data', 'seoearth' ),
			'advanced' => __( 'Advanced', 'seoearth' ),
		);

		/**
		 * Filters the settings sections (ID => title, in display order).
		 *
		 * @param array<string, mixed> $sections Sections.
		 */
		$filtered = apply_filters( 'seoearth_settings_sections', $sections );

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
				__( 'Title separator', 'seoearth' ),
				__( 'Placed between parts of generated titles, for example “Post title – Site name”.', 'seoearth' ),
				self::SEPARATORS
			),
			new Field(
				'site_represents',
				'general',
				Field::TYPE_ENUM,
				'organization',
				__( 'This website represents', 'seoearth' ),
				__( 'Used in structured data to describe who publishes this site.', 'seoearth' ),
				array(
					'organization' => __( 'An organization', 'seoearth' ),
					'person'       => __( 'A person', 'seoearth' ),
				)
			),
			new Field(
				'organization_name',
				'general',
				Field::TYPE_TEXT,
				'',
				__( 'Organization or person name', 'seoearth' ),
				__( 'Leave empty to use the site title.', 'seoearth' )
			),
			new Field(
				'organization_logo',
				'general',
				Field::TYPE_URL,
				'',
				__( 'Logo URL', 'seoearth' ),
				__( 'A square image of at least 112 × 112 pixels works best.', 'seoearth' )
			),
			new Field(
				'sitemap_enabled',
				'sitemap',
				Field::TYPE_BOOL,
				true,
				__( 'Enable the XML sitemap', 'seoearth' ),
				__( 'Helps search engines find your content. Uses the sitemap built into WordPress at /wp-sitemap.xml.', 'seoearth' )
			),
			new Field(
				'sitemap_images',
				'sitemap',
				Field::TYPE_BOOL,
				true,
				__( 'Include images', 'seoearth' ),
				__( 'Lists the featured image and up to 10 images from this site found in each post.', 'seoearth' )
			),
			new Field(
				'sitemap_users',
				'sitemap',
				Field::TYPE_BOOL,
				true,
				__( 'Include author archives', 'seoearth' ),
				__( 'Always left out while author archives are hidden from search engines.', 'seoearth' )
			),
			new Field(
				'social_og_enabled',
				'social',
				Field::TYPE_BOOL,
				true,
				__( 'Add Open Graph tags', 'seoearth' ),
				__( 'Controls the title, description and image shown when a page is shared on Facebook, LinkedIn, WhatsApp, Slack and similar apps.', 'seoearth' )
			),
			new Field(
				'social_twitter_enabled',
				'social',
				Field::TYPE_BOOL,
				true,
				__( 'Add X (Twitter) Card tags', 'seoearth' ),
				__( 'Controls how links look when shared on X.', 'seoearth' )
			),
			new Field(
				'default_social_image',
				'social',
				Field::TYPE_URL,
				'',
				__( 'Default sharing image URL', 'seoearth' ),
				__( 'Used when a page has no featured image. 1200 × 630 pixels is recommended.', 'seoearth' )
			),
			new Field(
				'twitter_site',
				'social',
				Field::TYPE_TWITTER_HANDLE,
				'',
				__( 'X (Twitter) username', 'seoearth' ),
				__( 'Without the @. Letters, numbers and underscores, up to 15 characters.', 'seoearth' )
			),
			new Field(
				'schema_enabled',
				'schema',
				Field::TYPE_BOOL,
				true,
				__( 'Add structured data (schema.org)', 'seoearth' ),
				__( 'Describes your site, pages, articles, authors and breadcrumbs to search engines in one JSON-LD block. Uses the “Site identity” settings above.', 'seoearth' )
			),
			new Field(
				'remove_data_on_uninstall',
				'advanced',
				Field::TYPE_BOOL,
				false,
				__( 'Remove all SEOEarth data when the plugin is deleted', 'seoearth' ),
				__( 'Deletes SEOEarth settings and SEO data stored for your content. Your posts and pages are never deleted. This cannot be undone.', 'seoearth' )
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
		$filtered = apply_filters( 'seoearth_settings_fields', $by_key );

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
