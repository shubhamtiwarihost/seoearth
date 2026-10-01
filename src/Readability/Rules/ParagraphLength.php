<?php
/**
 * Long paragraphs.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Readability\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;
use ShubhamTiwariSeoTools\Analysis\Rules\BaseRule;

defined( 'ABSPATH' ) || exit;

/**
 * Paragraphs over 150 words are hard to follow, especially on phones.
 * Works for any language.
 */
final class ParagraphLength extends BaseRule {

	public const MAX_WORDS = 150;

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'paragraph_length';
	}

	/**
	 * Only when there are paragraphs.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return array() !== $input->content->paragraphs;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		$long    = 0;
		$longest = 0;
		foreach ( $input->content->paragraphs as $paragraph ) {
			$words   = \ShubhamTiwariSeoTools\Analysis\Document::count_words( $paragraph );
			$longest = max( $longest, $words );
			if ( $words > self::MAX_WORDS ) {
				++$long;
			}
		}
		$meta = array(
			'long'    => $long,
			'longest' => $longest,
		);
		if ( 0 === $long ) {
			/* translators: %d: word limit. */
			return $this->result( Result::PASS, Result::LOW, sprintf( __( 'No paragraph is longer than %d words.', 'shubhamtiwari-seo-tools' ), self::MAX_WORDS ), '', $meta );
		}
		return $this->result(
			Result::WARNING,
			Result::LOW,
			/* translators: 1: number of paragraphs, 2: word limit. */
			sprintf( _n( '%1$d paragraph is longer than %2$d words.', '%1$d paragraphs are longer than %2$d words.', $long, 'shubhamtiwari-seo-tools' ), $long, self::MAX_WORDS ),
			__( 'Break long paragraphs where the thought changes.', 'shubhamtiwari-seo-tools' ),
			$meta
		);
	}
}
