<?php
/**
 * SEO fields on the term edit screen.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Admin;

use SEOEarth\Context;
use SEOEarth\Helpers\Text;
use SEOEarth\Meta\Keys;
use SEOEarth\Meta\Robots;
use SEOEarth\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Adds SEO title, meta description, canonical URL and robots settings to the
 * edit screen of every public taxonomy that has an admin UI, and saves them.
 *
 * Security: nonce per term, `edit_term` capability, and the taxonomy must be
 * one we render fields for.
 */
final class TermFields implements Module {

	public const NONCE_FIELD = 'seoearth_term_nonce';
	public const TITLE_FIELD = 'seoearth_title';
	public const DESC_FIELD  = 'seoearth_description';
	public const CANON_FIELD = 'seoearth_canonical';
	public const INDEX_FIELD = 'seoearth_robots_index';
	public const ROBOT_FIELD = 'seoearth_robots';

	/**
	 * Extra robots directives offered as checkboxes.
	 */
	private const EXTRA_DIRECTIVES = array( 'nofollow', 'noarchive', 'nosnippet', 'noimageindex' );

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
		add_action( 'admin_init', array( $this, 'add_form_hooks' ) );
		add_action( 'edited_term', array( $this, 'save' ), 10, 3 );
	}

	/**
	 * Taxonomies that get SEO fields.
	 *
	 * @return string[]
	 */
	public function taxonomies(): array {
		return array_values(
			get_taxonomies(
				array(
					'public'  => true,
					'show_ui' => true,
				)
			)
		);
	}

	/**
	 * Hooks the form renderer into each taxonomy's edit screen.
	 */
	public function add_form_hooks(): void {
		foreach ( $this->taxonomies() as $taxonomy ) {
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'render' ), 10, 1 );
		}
	}

	/**
	 * Renders the fields as table rows on the term edit screen.
	 *
	 * @param mixed $term Term being edited (from a core action; verified below).
	 */
	public function render( $term ): void {
		if ( ! $term instanceof \WP_Term || ! current_user_can( 'edit_term', $term->term_id ) ) {
			return;
		}

		$title       = (string) get_term_meta( $term->term_id, Keys::TITLE, true );
		$description = (string) get_term_meta( $term->term_id, Keys::DESCRIPTION, true );
		$canonical   = (string) get_term_meta( $term->term_id, Keys::CANONICAL, true );
		$robots      = Robots::parse( get_term_meta( $term->term_id, Keys::ROBOTS, true ) );
		$index       = in_array( 'noindex', $robots, true ) ? 'noindex' : ( in_array( 'index', $robots, true ) ? 'index' : '' );
		$directives  = array(
			'nofollow'     => __( 'Do not follow links (nofollow)', 'seoearth' ),
			'noarchive'    => __( 'Do not show a cached copy (noarchive)', 'seoearth' ),
			'nosnippet'    => __( 'Do not show a text snippet (nosnippet)', 'seoearth' ),
			'noimageindex' => __( 'Do not index images (noimageindex)', 'seoearth' ),
		);

		wp_nonce_field( $this->nonce_action( $term->term_id ), self::NONCE_FIELD );
		?>
		<tr class="form-field seoearth-term-title">
			<th scope="row"><label for="seoearth-term-title"><?php esc_html_e( 'SEO title', 'seoearth' ); ?></label></th>
			<td>
				<input type="text" id="seoearth-term-title" name="<?php echo esc_attr( self::TITLE_FIELD ); ?>" value="<?php echo esc_attr( $title ); ?>" aria-describedby="seoearth-term-title-description" />
				<p class="description" id="seoearth-term-title-description"><?php esc_html_e( 'Leave empty to use the title template from SEOEarth settings. Variables such as %%site_name%% are allowed.', 'seoearth' ); ?></p>
			</td>
		</tr>
		<tr class="form-field seoearth-term-description">
			<th scope="row"><label for="seoearth-term-description"><?php esc_html_e( 'Meta description', 'seoearth' ); ?></label></th>
			<td>
				<textarea id="seoearth-term-description" name="<?php echo esc_attr( self::DESC_FIELD ); ?>" rows="3" aria-describedby="seoearth-term-description-help"><?php echo esc_textarea( $description ); ?></textarea>
				<p class="description" id="seoearth-term-description-help"><?php esc_html_e( 'Leave empty to use the description template.', 'seoearth' ); ?></p>
			</td>
		</tr>
		<tr class="form-field seoearth-term-canonical">
			<th scope="row"><label for="seoearth-term-canonical"><?php esc_html_e( 'Canonical URL', 'seoearth' ); ?></label></th>
			<td>
				<input type="url" id="seoearth-term-canonical" name="<?php echo esc_attr( self::CANON_FIELD ); ?>" value="<?php echo esc_attr( $canonical ); ?>" aria-describedby="seoearth-term-canonical-help" />
				<p class="description" id="seoearth-term-canonical-help"><?php esc_html_e( 'Only if this archive duplicates another page. Must be a full address starting with https:// or http://. Leave empty for the archive’s own address.', 'seoearth' ); ?></p>
			</td>
		</tr>
		<tr class="form-field seoearth-term-robots">
			<th scope="row"><label for="seoearth-term-robots-index"><?php esc_html_e( 'Search engines', 'seoearth' ); ?></label></th>
			<td>
				<select id="seoearth-term-robots-index" name="<?php echo esc_attr( self::INDEX_FIELD ); ?>">
					<option value="" <?php selected( $index, '' ); ?>><?php esc_html_e( 'Default (from SEOEarth settings)', 'seoearth' ); ?></option>
					<option value="index" <?php selected( $index, 'index' ); ?>><?php esc_html_e( 'Show in search results (index)', 'seoearth' ); ?></option>
					<option value="noindex" <?php selected( $index, 'noindex' ); ?>><?php esc_html_e( 'Hide from search results (noindex)', 'seoearth' ); ?></option>
				</select>
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Additional search engine directives', 'seoearth' ); ?></legend>
					<?php foreach ( $directives as $directive => $label ) : ?>
						<label><input type="checkbox" name="<?php echo esc_attr( self::ROBOT_FIELD ); ?>[]" value="<?php echo esc_attr( $directive ); ?>" <?php checked( in_array( $directive, $robots, true ) ); ?> /> <?php echo esc_html( $label ); ?></label><br />
					<?php endforeach; ?>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Saves the fields.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 */
	public function save( $term_id, $tt_id = 0, $taxonomy = '' ): void {
		unset( $tt_id );
		$term_id = (int) $term_id;

		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) ) {
			return; // Not our form (e.g. quick edit or a programmatic update).
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) );
		if ( ! wp_verify_nonce( $nonce, $this->nonce_action( $term_id ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_term', $term_id ) || ! in_array( $taxonomy, $this->taxonomies(), true ) ) {
			return;
		}

		$fields = array(
			self::TITLE_FIELD => Keys::TITLE,
			self::DESC_FIELD  => Keys::DESCRIPTION,
		);
		foreach ( $fields as $input => $meta_key ) {
			$value = isset( $_POST[ $input ] ) && is_string( $_POST[ $input ] ) ? Text::sanitize_line( wp_unslash( $_POST[ $input ] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by Text::sanitize_line() (sanitize_text_field() with %%variables%% preserved).
			if ( '' === $value ) {
				delete_term_meta( $term_id, $meta_key );
			} else {
				update_term_meta( $term_id, $meta_key, $value );
			}
		}

		// Canonical: an invalid URL keeps the previous value instead of silently clearing it.
		$canonical = isset( $_POST[ self::CANON_FIELD ] ) ? Text::http_url( wp_unslash( $_POST[ self::CANON_FIELD ] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated and sanitized by Text::http_url() (esc_url_raw, http/https only).
		if ( '' === $canonical ) {
			delete_term_meta( $term_id, Keys::CANONICAL );
		} elseif ( null !== $canonical ) {
			update_term_meta( $term_id, Keys::CANONICAL, $canonical );
		}

		// Robots: index choice + extra directives, reduced to the allowlist.
		$tokens = array();
		if ( isset( $_POST[ self::INDEX_FIELD ] ) && is_string( $_POST[ self::INDEX_FIELD ] ) ) {
			$tokens[] = sanitize_key( wp_unslash( $_POST[ self::INDEX_FIELD ] ) );
		}
		if ( isset( $_POST[ self::ROBOT_FIELD ] ) && is_array( $_POST[ self::ROBOT_FIELD ] ) ) {
			$extra  = array_map( 'sanitize_key', wp_unslash( $_POST[ self::ROBOT_FIELD ] ) );
			$tokens = array_merge( $tokens, array_intersect( $extra, self::EXTRA_DIRECTIVES ) );
		}
		$robots = Robots::sanitize( $tokens );
		if ( '' === $robots ) {
			delete_term_meta( $term_id, Keys::ROBOTS );
		} else {
			update_term_meta( $term_id, Keys::ROBOTS, $robots );
		}
	}

	/**
	 * Nonce action for a term.
	 *
	 * @param int $term_id Term ID.
	 */
	private function nonce_action( int $term_id ): string {
		return 'seoearth_term_' . $term_id;
	}
}
