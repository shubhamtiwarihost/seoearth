<?php
/**
 * Keyphrase used on other posts.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Analysis\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * Two pages targeting the same keyphrase compete with each other.
 */
final class KeyphraseUnique extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'keyphrase_unique';
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		$count = count( $input->keyphrase_used_by );
		if ( 0 === $count ) {
			return $this->result( Result::PASS, Result::MEDIUM, __( 'No other content uses this focus keyphrase.', 'shubhamtiwari-seo-tools' ) );
		}
		return $this->result(
			Result::WARNING,
			Result::MEDIUM,
			/* translators: %d: number of other posts. */
			sprintf( _n( '%d other post already uses this focus keyphrase.', '%d other posts already use this focus keyphrase.', $count, 'shubhamtiwari-seo-tools' ), $count ),
			__( 'Choose a different keyphrase, or combine the pages if they cover the same topic.', 'shubhamtiwari-seo-tools' ),
			array(
				'count'    => $count,
				'post_ids' => implode( ',', $input->keyphrase_used_by ),
			)
		);
	}
}
