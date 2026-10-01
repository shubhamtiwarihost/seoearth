<?php
/**
 * Alternative text on images.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Analysis\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * Images need alt text for screen-reader users and image search.
 */
final class ImageAlt extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'image_alt';
	}

	/**
	 * Only when the text has images.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return array() !== $input->content->image_alts;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		$total   = count( $input->content->image_alts );
		$missing = count(
			array_filter(
				$input->content->image_alts,
				static function ( string $alt ): bool {
					return '' === $alt;
				}
			)
		);
		$meta    = array(
			'images'  => $total,
			'missing' => $missing,
		);
		if ( 0 === $missing ) {
			return $this->result( Result::PASS, Result::MEDIUM, __( 'Every image has alternative text.', 'shubhamtiwari-seo-tools' ), '', $meta );
		}
		return $this->result(
			Result::WARNING,
			Result::MEDIUM,
			/* translators: 1: images without alt text, 2: total images. */
			sprintf( _n( '%1$d of %2$d image has no alternative text.', '%1$d of %2$d images have no alternative text.', $total, 'shubhamtiwari-seo-tools' ), $missing, $total ),
			__( 'Describe what each image shows. Purely decorative images can stay empty.', 'shubhamtiwari-seo-tools' ),
			$meta
		);
	}
}
