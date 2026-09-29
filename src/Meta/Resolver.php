<?php
/**
 * Resolves the SEO title and description for a page.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Meta;

use SEOEarth\Settings\Schema;
use SEOEarth\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Picks the custom value or the template, then renders it.
 *
 * Priority: per-object custom value (post/term meta) → template setting for
 * the page type. Custom values may contain variables too.
 */
class Resolver {

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Template engine.
	 *
	 * @var TemplateEngine
	 */
	private $engine;

	/**
	 * Variable values.
	 *
	 * @var VariableValues
	 */
	private $values;

	/**
	 * Constructor.
	 *
	 * @param Settings       $settings Settings.
	 * @param TemplateEngine $engine   Template engine.
	 * @param VariableValues $values   Variable values.
	 */
	public function __construct( Settings $settings, TemplateEngine $engine, VariableValues $values ) {
		$this->settings = $settings;
		$this->engine   = $engine;
		$this->values   = $values;
	}

	/**
	 * Plain-text SEO title, or '' to leave WordPress's own title in place.
	 *
	 * @param PageContext $context Page context.
	 */
	public function title( PageContext $context ): string {
		return $this->resolve( 'title', Keys::TITLE, $context );
	}

	/**
	 * Plain-text meta description, or '' for none.
	 *
	 * @param PageContext $context Page context.
	 */
	public function description( PageContext $context ): string {
		return $this->resolve( 'desc', Keys::DESCRIPTION, $context );
	}

	/**
	 * Settings key of the template for a context, e.g. "title_pt_post", or null.
	 *
	 * @param string      $kind    "title" or "desc".
	 * @param PageContext $context Page context.
	 */
	public static function template_key( string $kind, PageContext $context ): ?string {
		$object = $context->object;

		switch ( $context->type ) {
			case PageContext::SINGULAR:
				$suffix = $object instanceof \WP_Post ? 'pt_' . Keys::slug( $object->post_type ) : null;
				break;
			case PageContext::FRONT:
				$suffix = 'home';
				break;
			case PageContext::BLOG:
				$suffix = $object instanceof \WP_Post ? 'pt_' . Keys::slug( $object->post_type ) : 'home';
				break;
			case PageContext::TERM:
				$suffix = $object instanceof \WP_Term ? 'tax_' . Keys::slug( $object->taxonomy ) : null;
				break;
			case PageContext::PT_ARCHIVE:
				$suffix = $object instanceof \WP_Post_Type ? 'ptarchive_' . Keys::slug( $object->name ) : null;
				break;
			case PageContext::AUTHOR:
				$suffix = 'author';
				break;
			case PageContext::DATE:
				$suffix = 'date';
				break;
			case PageContext::SEARCH:
				$suffix = 'search';
				break;
			case PageContext::NOT_FOUND:
				$suffix = '404';
				break;
			default:
				$suffix = null;
		}

		return null === $suffix ? null : $kind . '_' . $suffix;
	}

	/**
	 * Shared resolution logic.
	 *
	 * @param string      $kind     "title" or "desc".
	 * @param string      $meta_key Custom value meta key.
	 * @param PageContext $context  Page context.
	 */
	private function resolve( string $kind, string $meta_key, PageContext $context ): string {
		$template = $this->custom_value( $meta_key, $context );

		if ( '' === $template ) {
			$key      = self::template_key( $kind, $context );
			$template = null === $key ? '' : (string) $this->settings->get( $key );
		}
		if ( '' === $template ) {
			return '';
		}

		$separator_key = (string) $this->settings->get( 'separator' );
		$separator     = Schema::SEPARATORS[ $separator_key ] ?? Schema::SEPARATORS['ndash'];

		return $this->engine->render( $template, $this->values->for_context( $context ), $separator );
	}

	/**
	 * Custom value saved on the post or term, or ''.
	 *
	 * @param string      $meta_key Meta key.
	 * @param PageContext $context  Page context.
	 */
	private function custom_value( string $meta_key, PageContext $context ): string {
		$object = $context->object;

		if ( $object instanceof \WP_Post ) {
			return trim( (string) get_post_meta( $object->ID, $meta_key, true ) );
		}
		if ( $object instanceof \WP_Term && PageContext::TERM === $context->type ) {
			return trim( (string) get_term_meta( $object->term_id, $meta_key, true ) );
		}
		return '';
	}
}
