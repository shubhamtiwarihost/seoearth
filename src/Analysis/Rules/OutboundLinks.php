<?php
/**
 * Links to other sites.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Analysis\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * Linking to good sources is useful to readers. Never an error: plenty of
 * pages have no reason to link out.
 */
final class OutboundLinks extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'outbound_links';
	}

	/**
	 * Only when there is text.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return $input->content->word_count > 0;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		$count = 0;
		foreach ( $input->content->links as $href ) {
			if ( ! \ShubhamTiwariSeoTools\Analysis\Document::is_internal( $href, $input->host ) ) {
				++$count;
			}
		}
		$meta = array( 'count' => $count );
		if ( 0 === $count ) {
			return $this->result(
				Result::INFO,
				Result::LOW,
				__( 'The text has no links to other websites.', 'shubhamtiwari-seo-tools' ),
				__( 'Where you rely on facts from elsewhere, link to the source.', 'shubhamtiwari-seo-tools' ),
				$meta
			);
		}
		/* translators: %d: number of links. */
		return $this->result( Result::PASS, Result::LOW, sprintf( _n( 'The text has %d link to another website.', 'The text has %d links to other websites.', $count, 'shubhamtiwari-seo-tools' ), $count ), '', $meta );
	}
}
