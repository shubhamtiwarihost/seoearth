<?php
/**
 * Prints the SEO title and meta description.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Frontend;

use SEOEarth\Context;
use SEOEarth\Meta\PageContext;
use SEOEarth\Meta\Resolver;
use SEOEarth\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend <head> output for titles and descriptions.
 *
 * - Themes with `title-tag` support: `pre_get_document_title`.
 * - Legacy themes calling wp_title(): `wp_title` (skipped in feeds, where core
 *   uses the same filter for the feed title).
 * - `<meta name="description">` on `wp_head`, priority 1.
 *
 * Everything is resolved once per request, after the main query has run.
 */
final class HeadModule implements Module {

	/**
	 * Request context.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Resolver.
	 *
	 * @var Resolver
	 */
	private $resolver;

	/**
	 * Resolved values for this request.
	 *
	 * @var array{title: string, description: string}|null
	 */
	private $resolved = null;

	/**
	 * Constructor.
	 *
	 * @param Context  $context  Request context.
	 * @param Resolver $resolver Resolver.
	 */
	public function __construct( Context $context, Resolver $resolver ) {
		$this->context  = $context;
		$this->resolver = $resolver;
	}

	/**
	 * Frontend requests only.
	 */
	public function should_load(): bool {
		return $this->context->is_frontend();
	}

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'pre_get_document_title', array( $this, 'document_title' ), 15 );
		add_filter( 'wp_title', array( $this, 'wp_title' ), 15 );
		add_action( 'wp_head', array( $this, 'print_description' ), 1 );
	}

	/**
	 * Title for themes that support title-tag. Returned value is echoed by core unescaped.
	 *
	 * @param mixed $title Title from earlier filters.
	 * @return mixed
	 */
	public function document_title( $title ) {
		if ( ! $this->enabled() ) {
			return $title;
		}
		$ours = $this->resolved()['title'];
		return '' === $ours ? $title : esc_html( $ours );
	}

	/**
	 * Title for legacy themes that call wp_title().
	 *
	 * @param mixed $title Title from core.
	 * @return mixed
	 */
	public function wp_title( $title ) {
		if ( is_feed() ) {
			return $title;
		}
		return $this->document_title( $title );
	}

	/**
	 * Prints the meta description tag.
	 */
	public function print_description(): void {
		if ( ! $this->enabled() ) {
			return;
		}
		$description = $this->resolved()['description'];
		if ( '' !== $description ) {
			printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
		}
	}

	/**
	 * Resolves title and description once.
	 *
	 * @return array{title: string, description: string}
	 */
	public function resolved(): array {
		if ( null === $this->resolved ) {
			global $wp_query;
			if ( ! $wp_query instanceof \WP_Query ) {
				return array(
					'title'       => '',
					'description' => '',
				);
			}
			$page_context   = PageContext::from_query( $wp_query );
			$this->resolved = array(
				'title'       => $this->resolver->title( $page_context ),
				'description' => $this->resolver->description( $page_context ),
			);
		}
		return $this->resolved;
	}

	/**
	 * Clears the per-request cache. For tests that simulate several requests.
	 *
	 * @internal
	 */
	public function reset(): void {
		$this->resolved = null;
	}

	/**
	 * Whether SEOEarth should print title/description tags.
	 */
	private function enabled(): bool {
		/**
		 * Filters whether SEOEarth outputs the title and meta description.
		 *
		 * @param bool $enabled Default true.
		 */
		return (bool) apply_filters( 'seoearth_head_output_enabled', true );
	}
}
