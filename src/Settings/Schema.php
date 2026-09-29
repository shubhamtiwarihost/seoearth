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
	 * Section IDs => translated titles, in display order.
	 *
	 * @return array<string, string>
	 */
	public function sections(): array {
		return array(
			'general'  => __( 'Site identity', 'seoearth' ),
			'social'   => __( 'Social sharing', 'seoearth' ),
			'advanced' => __( 'Advanced', 'seoearth' ),
		);
	}

	/**
	 * All fields keyed by storage key.
	 *
	 * @return array<string, Field>
	 */
	public function fields(): array {
		if ( null !== $this->fields ) {
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

		$this->fields = array();
		foreach ( (array) $filtered as $field ) {
			if ( $field instanceof Field ) {
				$this->fields[ $field->key ] = $field;
			}
		}
		return $this->fields;
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
