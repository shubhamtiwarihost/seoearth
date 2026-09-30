<?php
/**
 * What is being analysed.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis;

defined( 'ABSPATH' ) || exit;

/**
 * Everything the rules need, resolved up front (by InputFactory for real
 * posts). Rules never look anything up themselves, so they are pure and fast.
 */
final class Input {

	/**
	 * Focus keyphrase.
	 *
	 * @var Keyphrase
	 */
	public $keyphrase;

	/**
	 * SEO title as it will be printed (templates rendered).
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Meta description as it will be printed ('' when none).
	 *
	 * @var string
	 */
	public $description;

	/**
	 * URL slug.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Parsed content.
	 *
	 * @var Document
	 */
	public $content;

	/**
	 * This site's host, for telling internal links from outbound ones.
	 *
	 * @var string
	 */
	public $host;

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Whether the page is hidden from search engines (noindex).
	 *
	 * @var bool
	 */
	public $noindex;

	/**
	 * IDs of other posts that use the same focus keyphrase.
	 *
	 * @var int[]
	 */
	public $keyphrase_used_by;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $values Keys: keyphrase, title, description, slug, content (HTML), host, post_type, noindex, keyphrase_used_by.
	 */
	public function __construct( array $values ) {
		$this->keyphrase         = new Keyphrase( (string) ( $values['keyphrase'] ?? '' ) );
		$this->title             = (string) ( $values['title'] ?? '' );
		$this->description       = (string) ( $values['description'] ?? '' );
		$this->slug              = (string) ( $values['slug'] ?? '' );
		$this->content           = new Document( (string) ( $values['content'] ?? '' ) );
		$this->host              = (string) ( $values['host'] ?? '' );
		$this->post_type         = (string) ( $values['post_type'] ?? 'post' );
		$this->noindex           = ! empty( $values['noindex'] );
		$this->keyphrase_used_by = array_map( 'intval', (array) ( $values['keyphrase_used_by'] ?? array() ) );
	}

	/**
	 * Whether a focus keyphrase was given.
	 */
	public function has_keyphrase(): bool {
		return ! $this->keyphrase->is_empty();
	}
}
