<?php
/**
 * Image SEO: missing alternative text.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Images;

use ShubhamTiwariSeoTools\Admin\SettingsPage;
use ShubhamTiwariSeoTools\Context;
use ShubhamTiwariSeoTools\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Finds images without alternative text. Read-only: ShubhamTiwari SEO Tools never changes
 * media; the site owner edits alt text in the media library.
 *
 * - Media Library (list view): "Alt text" column and a "Missing alt text" filter.
 * - ShubhamTiwari SEO Tools settings, "Images" section: how many images have no alt text, with a link to that list.
 */
final class ImagesModule implements Module {

	/**
	 * Query variable of the media list filter.
	 */
	public const FILTER_VAR = 'stseo_alt';

	/**
	 * Media list column ID.
	 */
	public const COLUMN = 'stseo_alt';

	/**
	 * Request context.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param Context $context Request context.
	 */
	public function __construct( Context $context ) {
		$this->context = $context;
	}

	/**
	 * Admin requests only.
	 */
	public function should_load(): bool {
		return $this->context->is_admin();
	}

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'manage_media_columns', array( $this, 'add_column' ) );
		add_action( 'manage_media_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'render_filter' ) );
		add_action( 'pre_get_posts', array( $this, 'apply_filter' ) );
		add_action( 'stseo_settings_section_images', array( $this, 'render_report' ) );
	}

	/**
	 * Query arguments that select images without alt text.
	 *
	 * An image counts as missing alt text when the `_wp_attachment_image_alt`
	 * row does not exist or is empty. Whitespace-only values are rare and
	 * would need a slow REGEXP, so they are not detected.
	 *
	 * @return array<string, mixed>
	 */
	public static function missing_alt_args(): array {
		return array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin-only report and list filter.
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_wp_attachment_image_alt',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => '_wp_attachment_image_alt',
					'value' => '',
				),
			),
		);
	}

	/**
	 * Number of images without alt text (one COUNT query).
	 */
	public static function missing_alt_count(): int {
		$query = new \WP_Query(
			self::missing_alt_args() + array(
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		return (int) $query->found_posts;
	}

	/**
	 * Adds the column after the title.
	 *
	 * @param mixed $columns Columns.
	 * @return array<string, string>
	 */
	public function add_column( $columns ): array {
		$result = array();
		foreach ( (array) $columns as $key => $label ) {
			$result[ (string) $key ] = (string) $label;
			if ( 'title' === $key ) {
				$result[ self::COLUMN ] = __( 'Alt text', 'shubhamtiwari-seo-tools' );
			}
		}
		if ( ! isset( $result[ self::COLUMN ] ) ) {
			$result[ self::COLUMN ] = __( 'Alt text', 'shubhamtiwari-seo-tools' );
		}
		return $result;
	}

	/**
	 * Column content: the alt text, or "Missing" (symbol + word, never color alone).
	 *
	 * @param mixed $column  Column ID.
	 * @param mixed $post_id Attachment ID.
	 */
	public function render_column( $column, $post_id ): void {
		if ( self::COLUMN !== $column || ! wp_attachment_is_image( (int) $post_id ) ) {
			return;
		}
		$alt = trim( (string) get_post_meta( (int) $post_id, '_wp_attachment_image_alt', true ) );
		if ( '' === $alt ) {
			printf(
				'<span class="stseo-alt-missing"><span aria-hidden="true">✕ </span>%s</span>',
				esc_html__( 'Missing', 'shubhamtiwari-seo-tools' )
			);
			return;
		}
		echo esc_html( wp_html_excerpt( $alt, 80, '…' ) );
	}

	/**
	 * "Alt text" filter above the media list.
	 *
	 * @param mixed $post_type Post type of the list.
	 */
	public function render_filter( $post_type ): void {
		if ( 'attachment' !== $post_type ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filter, like core's own filters.
		$current = isset( $_GET[ self::FILTER_VAR ] ) ? sanitize_key( wp_unslash( $_GET[ self::FILTER_VAR ] ) ) : '';
		?>
		<label for="stseo-alt-filter" class="screen-reader-text"><?php esc_html_e( 'Filter by alt text', 'shubhamtiwari-seo-tools' ); ?></label>
		<select name="<?php echo esc_attr( self::FILTER_VAR ); ?>" id="stseo-alt-filter">
			<option value=""><?php esc_html_e( 'All alt text', 'shubhamtiwari-seo-tools' ); ?></option>
			<option value="missing" <?php selected( $current, 'missing' ); ?>><?php esc_html_e( 'Missing alt text', 'shubhamtiwari-seo-tools' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Applies the filter to the media list query.
	 *
	 * @param mixed $query Query.
	 */
	public function apply_filter( $query ): void {
		if ( ! $query instanceof \WP_Query || ! $query->is_main_query() || 'attachment' !== $query->get( 'post_type' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filter, like core's own filters.
		if ( ! isset( $_GET[ self::FILTER_VAR ] ) || 'missing' !== sanitize_key( wp_unslash( $_GET[ self::FILTER_VAR ] ) ) ) {
			return;
		}
		$args = self::missing_alt_args();
		$query->set( 'post_mime_type', $args['post_mime_type'] );
		$query->set( 'meta_query', $args['meta_query'] );
	}

	/**
	 * Report in the "Images" settings section.
	 */
	public function render_report(): void {
		if ( ! current_user_can( SettingsPage::CAPABILITY ) ) {
			return;
		}
		$count = self::missing_alt_count();
		if ( 0 === $count ) {
			printf( '<p><span aria-hidden="true">✓ </span>%s</p>', esc_html__( 'Every image in the media library has alternative text.', 'shubhamtiwari-seo-tools' ) );
			return;
		}
		printf(
			'<p><span aria-hidden="true">! </span>%1$s <a href="%2$s">%3$s</a></p>',
			esc_html(
				sprintf(
					/* translators: %s: number of images. */
					_n( '%s image in the media library has no alternative text.', '%s images in the media library have no alternative text.', $count, 'shubhamtiwari-seo-tools' ),
					number_format_i18n( $count )
				)
			),
			esc_url(
				add_query_arg(
					array(
						'mode'           => 'list',
						self::FILTER_VAR => 'missing',
					),
					admin_url( 'upload.php' )
				)
			),
			esc_html__( 'Review them in the media library', 'shubhamtiwari-seo-tools' )
		);
		echo '<p class="description">' . esc_html__( 'Alternative text describes an image for people who cannot see it and helps image search. ShubhamTiwari SEO Tools only reports; it never changes your media. Purely decorative images can stay empty.', 'shubhamtiwari-seo-tools' ) . '</p>';
	}
}
