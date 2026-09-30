<?php
/**
 * Redirect edit screen and list.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Redirects;

use SEOEarth\Context;
use SEOEarth\Module;

defined( 'ABSPATH' ) || exit;

/**
 * SEOEarth → Redirects. Title = source path; a box holds the target and type.
 * "Publish" makes a redirect active, "Save Draft" keeps it inactive.
 *
 * Validation happens before the post is written (wp_insert_post_data), so an
 * invalid redirect is never active: it is saved as a draft and the problem is
 * shown. Rules: source must be a path on this site (not the homepage, not
 * wp-admin/login/API), target must be an http(s) URL or a "/path", no
 * duplicate active source, no loop. The index is rebuilt after every change.
 */
final class AdminScreen implements Module {

	public const NONCE_FIELD  = 'seoearth_redirect_nonce';
	public const TARGET_FIELD = 'seoearth_redirect_target';
	public const TYPE_FIELD   = 'seoearth_redirect_type';

	/**
	 * Request context.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Storage.
	 *
	 * @var Store
	 */
	private $store;

	/**
	 * Constructor.
	 *
	 * @param Context $context Request context.
	 * @param Store   $store   Storage.
	 */
	public function __construct( Context $context, Store $store ) {
		$this->context = $context;
		$this->store   = $store;
	}

	/**
	 * Admin requests only (includes admin-ajax quick edit).
	 */
	public function should_load(): bool {
		return $this->context->is_admin();
	}

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'enter_title_here', array( $this, 'title_placeholder' ), 10, 2 );
		add_action( 'add_meta_boxes_' . Store::POST_TYPE, array( $this, 'add_box' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'validate' ), 10, 2 );
		add_action( 'save_post_' . Store::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'render_errors' ) );
		add_filter( 'manage_' . Store::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . Store::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
	}

	/**
	 * Placeholder of the title field.
	 *
	 * @param mixed $placeholder Default placeholder.
	 * @param mixed $post        Post.
	 * @return mixed
	 */
	public function title_placeholder( $placeholder, $post = null ) {
		return $post instanceof \WP_Post && Store::POST_TYPE === $post->post_type ? __( 'Old address, e.g. /old-page', 'seoearth' ) : $placeholder;
	}

	/**
	 * Adds the target/type box.
	 */
	public function add_box(): void {
		add_meta_box( 'seoearth-redirect-box', __( 'Redirect', 'seoearth' ), array( $this, 'render_box' ), Store::POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Renders the box.
	 *
	 * @param \WP_Post $post Redirect post.
	 */
	public function render_box( \WP_Post $post ): void {
		$target = (string) get_post_meta( $post->ID, Store::META_TARGET, true );
		$type   = (int) get_post_meta( $post->ID, Store::META_TYPE, true );
		$type   = in_array( $type, Store::TYPES, true ) ? $type : 301;
		wp_nonce_field( 'seoearth_redirect_' . $post->ID, self::NONCE_FIELD );
		?>
		<p>
			<label for="seoearth-redirect-target"><strong><?php esc_html_e( 'New address', 'seoearth' ); ?></strong></label>
			<input type="text" class="widefat code" id="seoearth-redirect-target" name="<?php echo esc_attr( self::TARGET_FIELD ); ?>" value="<?php echo esc_attr( $target ); ?>" aria-describedby="seoearth-redirect-target-help" />
			<span class="description" id="seoearth-redirect-target-help"><?php esc_html_e( 'A path on this site such as /new-page/, or a full address starting with https://. Not needed for “Gone (410)”.', 'seoearth' ); ?></span>
		</p>
		<fieldset>
			<legend><strong><?php esc_html_e( 'Type', 'seoearth' ); ?></strong></legend>
			<?php foreach ( self::type_labels() as $value => $label ) : ?>
				<label><input type="radio" name="<?php echo esc_attr( self::TYPE_FIELD ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" <?php checked( $type, $value ); ?> /> <?php echo esc_html( $label ); ?></label><br />
			<?php endforeach; ?>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Publish to turn the redirect on; save as a draft to keep it off. Query strings on the old address are ignored when matching and passed on to the new address.', 'seoearth' ); ?></p>
		<?php
	}

	/**
	 * Validates and normalises a redirect before it is written.
	 *
	 * @param mixed $data    Slashed post data about to be saved.
	 * @param mixed $postarr Raw post array (includes ID).
	 * @return mixed
	 */
	public function validate( $data, $postarr ) {
		if ( ! is_array( $data ) || Store::POST_TYPE !== ( $data['post_type'] ?? '' ) || in_array( $data['post_status'] ?? '', array( 'auto-draft', 'trash', 'inherit' ), true ) ) {
			return $data;
		}
		$post_id = is_array( $postarr ) ? (int) ( $postarr['ID'] ?? 0 ) : 0;
		$host    = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$errors  = array();

		$source = Paths::source( wp_unslash( (string) $data['post_title'] ), $host, (string) wp_parse_url( home_url(), PHP_URL_PATH ) );
		if ( null === $source ) {
			$errors[] = __( 'The old address must be a path on this site, such as /old-page. The homepage, the dashboard, the login page and the REST API cannot be redirected.', 'seoearth' );
		} else {
			$data['post_title'] = wp_slash( $source );
		}

		list( $target, $type ) = $this->submitted( $post_id );
		if ( 410 !== $type && null === Paths::target( $target ) ) {
			$errors[] = __( 'The new address must be a path such as /new-page/ or a full address starting with https://.', 'seoearth' );
		}

		if ( null !== $source && array() === $errors ) {
			$others = array_filter(
				$this->store->index(),
				static function ( array $entry ) use ( $post_id ): bool {
					return $entry['id'] !== $post_id;
				}
			);
			if ( isset( $others[ $source ] ) ) {
				$errors[] = __( 'Another active redirect already uses this old address.', 'seoearth' );
			} elseif ( 410 !== $type && Paths::loops( $source, $target, $others, $host ) ) {
				$errors[] = __( 'This redirect would send visitors in a loop (back to the old address, possibly through other redirects).', 'seoearth' );
			}
		}

		if ( array() !== $errors ) {
			if ( 'publish' === ( $data['post_status'] ?? '' ) ) {
				$data['post_status'] = 'draft';
			}
			set_transient( 'seoearth_redirect_errors_' . get_current_user_id(), $errors, 60 );
		}
		return $data;
	}

	/**
	 * Saves target and type, then rebuilds the index.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post.
	 */
	public function save( $post_id, $post ): void {
		$post_id = (int) $post_id;
		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( $this->has_valid_nonce( $post_id ) ) {
			list( $target, $type ) = $this->submitted( $post_id );
			update_post_meta( $post_id, Store::META_TYPE, $type );
			update_post_meta( $post_id, Store::META_TARGET, wp_slash( 410 === $type ? '' : (string) Paths::target( $target ) ) );
		}
		unset( $post );
		// The index itself is refreshed by Store::changed() (hooked for every context in RedirectsModule).
	}

	/**
	 * Shows validation problems from the last save.
	 */
	public function render_errors(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen instanceof \WP_Screen || Store::POST_TYPE !== $screen->post_type ) {
			return;
		}
		$key    = 'seoearth_redirect_errors_' . get_current_user_id();
		$errors = get_transient( $key );
		if ( ! is_array( $errors ) || array() === $errors ) {
			return;
		}
		delete_transient( $key );
		echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'The redirect was saved as a draft and is not active:', 'seoearth' ) . '</strong></p><ul>';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( (string) $error ) . '</li>';
		}
		echo '</ul></div>';
	}

	/**
	 * List columns.
	 *
	 * @param mixed $columns Columns.
	 * @return array<string, string>
	 */
	public function columns( $columns ): array {
		$columns = (array) $columns;
		return array(
			'cb'                => (string) ( $columns['cb'] ?? '' ),
			'title'             => __( 'Old address', 'seoearth' ),
			'seoearth_target'   => __( 'New address', 'seoearth' ),
			'seoearth_type'     => __( 'Type', 'seoearth' ),
			'seoearth_redirect' => __( 'Status', 'seoearth' ),
		);
	}

	/**
	 * List column content.
	 *
	 * @param mixed $column  Column.
	 * @param mixed $post_id Post ID.
	 */
	public function render_column( $column, $post_id ): void {
		$post_id = (int) $post_id;
		$type    = (int) get_post_meta( $post_id, Store::META_TYPE, true );
		switch ( $column ) {
			case 'seoearth_target':
				echo 410 === $type ? '—' : '<code>' . esc_html( (string) get_post_meta( $post_id, Store::META_TARGET, true ) ) . '</code>';
				break;
			case 'seoearth_type':
				echo esc_html( self::type_labels()[ $type ] ?? '' );
				break;
			case 'seoearth_redirect':
				echo 'publish' === get_post_status( $post_id ) ? esc_html__( 'Active', 'seoearth' ) : esc_html__( 'Inactive', 'seoearth' );
				break;
		}
	}

	/**
	 * Update messages without "View post" links (redirects have no page).
	 *
	 * @param mixed $messages Messages per post type.
	 * @return mixed
	 */
	public function messages( $messages ) {
		if ( is_array( $messages ) ) {
			$messages[ Store::POST_TYPE ]     = array_fill( 0, 11, '' );
			$messages[ Store::POST_TYPE ][1]  = __( 'Redirect updated.', 'seoearth' );
			$messages[ Store::POST_TYPE ][6]  = __( 'Redirect saved and active.', 'seoearth' );
			$messages[ Store::POST_TYPE ][7]  = __( 'Redirect saved.', 'seoearth' );
			$messages[ Store::POST_TYPE ][10] = __( 'Redirect saved as a draft (inactive).', 'seoearth' );
		}
		return $messages;
	}

	/**
	 * Type values => labels.
	 *
	 * @return array<int, string>
	 */
	public static function type_labels(): array {
		return array(
			301 => __( 'Moved permanently (301)', 'seoearth' ),
			302 => __( 'Found — temporary (302)', 'seoearth' ),
			307 => __( 'Temporary redirect (307)', 'seoearth' ),
			410 => __( 'Gone (410) — the page was removed on purpose', 'seoearth' ),
		);
	}

	/**
	 * Target and type from the form (with a valid nonce), else the stored values.
	 *
	 * @param int $post_id Post ID.
	 * @return array{0: string, 1: int}
	 */
	private function submitted( int $post_id ): array {
		if ( $this->has_valid_nonce( $post_id ) ) {
			// Nonce verified by has_valid_nonce() above. The target is not passed through sanitize_text_field(),
			// which strips %XX sequences from URLs; Paths::target() validates it strictly before anything is stored.
			// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$target = isset( $_POST[ self::TARGET_FIELD ] ) && is_string( $_POST[ self::TARGET_FIELD ] ) ? trim( wp_unslash( $_POST[ self::TARGET_FIELD ] ) ) : '';
			$type   = isset( $_POST[ self::TYPE_FIELD ] ) ? (int) $_POST[ self::TYPE_FIELD ] : 301;
			// phpcs:enable
		} else {
			$target = (string) get_post_meta( $post_id, Store::META_TARGET, true );
			$type   = (int) get_post_meta( $post_id, Store::META_TYPE, true );
		}
		return array( $target, in_array( $type, Store::TYPES, true ) ? $type : 301 );
	}

	/**
	 * Whether the request carries this box's valid nonce for the post.
	 *
	 * @param int $post_id Post ID.
	 */
	private function has_valid_nonce( int $post_id ): bool {
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) ) {
			return false;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) );
		return (bool) wp_verify_nonce( $nonce, 'seoearth_redirect_' . $post_id );
	}
}
