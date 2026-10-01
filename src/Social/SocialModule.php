<?php
/**
 * Prints Open Graph and X (Twitter) Card tags.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Social;

use ShubhamTiwariSeoTools\Compatibility\Conflicts;
use ShubhamTiwariSeoTools\Frontend\CurrentPage;
use ShubhamTiwariSeoTools\Module;
use ShubhamTiwariSeoTools\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Social meta tags in the page head (wp_head, priority 5).
 *
 * Steps aside when another plugin that prints these tags is active, and turns
 * off Jetpack's Open Graph tags (through Jetpack's own filter) while ShubhamTiwari SEO Tools
 * prints its own, so tags are never duplicated.
 */
final class SocialModule implements Module {

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Current page SEO data.
	 *
	 * @var CurrentPage
	 */
	private $page;

	/**
	 * Tag builder.
	 *
	 * @var SocialTags
	 */
	private $tags;

	/**
	 * Conflict detection.
	 *
	 * @var Conflicts
	 */
	private $conflicts;

	/**
	 * Constructor.
	 *
	 * @param Settings    $settings  Settings.
	 * @param CurrentPage $page      Current page SEO data.
	 * @param SocialTags  $tags      Tag builder.
	 * @param Conflicts   $conflicts Conflict detection.
	 */
	public function __construct( Settings $settings, CurrentPage $page, SocialTags $tags, Conflicts $conflicts ) {
		$this->settings  = $settings;
		$this->page      = $page;
		$this->tags      = $tags;
		$this->conflicts = $conflicts;
	}

	/**
	 * Frontend output plus a settings-screen notice in admin. Hooks only.
	 */
	public function should_load(): bool {
		return true;
	}

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'print_tags' ), 5 );
		add_filter( 'jetpack_enable_open_graph', array( $this, 'jetpack_open_graph' ), 100 );
		add_action( 'stseo_settings_section_social', array( $this, 'render_notice' ) );
	}

	/**
	 * Whether ShubhamTiwari SEO Tools prints social tags on this request.
	 */
	public function active(): bool {
		if ( '' !== $this->conflicts->social_plugin() ) {
			return false;
		}
		$any = false !== $this->settings->get( 'social_og_enabled' ) || false !== $this->settings->get( 'social_twitter_enabled' );

		/**
		 * Filters whether ShubhamTiwari SEO Tools outputs Open Graph / X Card tags.
		 *
		 * @param bool $any Whether any social output is enabled in settings.
		 */
		return (bool) apply_filters( 'stseo_social_output_enabled', $any );
	}

	/**
	 * Prints the tags.
	 */
	public function print_tags(): void {
		if ( ! $this->active() ) {
			return;
		}
		$data = $this->page->data();
		if ( null === $data ) {
			return;
		}

		foreach ( $this->tags->build( $data ) as $tag ) {
			printf(
				'<meta %1$s="%2$s" content="%3$s" />' . "\n",
				'name' === $tag['attr'] ? 'name' : 'property',
				esc_attr( $tag['key'] ),
				$tag['url'] ? esc_url( $tag['value'] ) : esc_attr( $tag['value'] )
			);
		}
	}

	/**
	 * Turns off Jetpack's Open Graph tags while ShubhamTiwari SEO Tools prints Open Graph tags.
	 *
	 * @param mixed $enabled Jetpack's decision.
	 * @return mixed
	 */
	public function jetpack_open_graph( $enabled ) {
		if ( $this->active() && false !== $this->settings->get( 'social_og_enabled' ) ) {
			return false;
		}
		return $enabled;
	}

	/**
	 * Notice on the ShubhamTiwari SEO Tools settings screen when another plugin handles social tags.
	 */
	public function render_notice(): void {
		$plugin = $this->conflicts->social_plugin();
		if ( '' === $plugin ) {
			return;
		}
		printf(
			'<div class="notice notice-info inline"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: name of another SEO plugin. */
					__( '%s is active and already prints social sharing tags, so ShubhamTiwari SEO Tools does not print its own. The settings below take effect once it is deactivated.', 'shubhamtiwari-seo-tools' ),
					$plugin
				)
			)
		);
	}
}
