<?php
/**
 * Title/description template settings.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Meta;

use SEOEarth\Settings\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Builds one title and one description template setting per page type:
 * homepage, each public post type, each post type archive, each public
 * taxonomy, author archives, date archives, search results and 404.
 *
 * Built after `init`, when post types and taxonomies are registered.
 */
final class TemplateFields {

	public const SECTION = 'search_appearance';

	/**
	 * All template fields.
	 *
	 * @return Field[]
	 */
	public function fields(): array {
		$page   = ' %%separator%% %%page%% %%separator%% %%site_name%%';
		$fields = array(
			$this->title( 'home', __( 'Homepage', 'seoearth' ), '%%site_name%% %%separator%% %%page%% %%separator%% %%sitedesc%%' ),
			$this->desc( 'home', __( 'Homepage', 'seoearth' ), '%%sitedesc%%' ),
		);

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			$slug     = Keys::slug( $type->name );
			$label    = (string) $type->labels->name;
			$fields[] = $this->title( 'pt_' . $slug, $label, '%%title%%' . $page );
			$fields[] = $this->desc( 'pt_' . $slug, $label, '%%excerpt%%' );

			if ( $type->has_archive ) {
				/* translators: %s: post type plural name, e.g. "Products". */
				$archive  = sprintf( __( '%s archive', 'seoearth' ), $label );
				$fields[] = $this->title( 'ptarchive_' . $slug, $archive, '%%pt_plural%%' . $page );
				$fields[] = $this->desc( 'ptarchive_' . $slug, $archive, '%%description%%' );
			}
		}

		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $taxonomy ) {
			$slug     = Keys::slug( $taxonomy->name );
			$label    = (string) $taxonomy->labels->name;
			$fields[] = $this->title( 'tax_' . $slug, $label, '%%title%%' . $page );
			$fields[] = $this->desc( 'tax_' . $slug, $label, '%%description%%' );
		}

		$author   = __( 'Author archives', 'seoearth' );
		$fields[] = $this->title( 'author', $author, '%%author%%' . $page );
		$fields[] = $this->desc( 'author', $author, '%%description%%' );

		$date     = __( 'Date archives', 'seoearth' );
		$fields[] = $this->title( 'date', $date, '%%date%%' . $page );
		$fields[] = $this->desc( 'date', $date, '' );

		$fields[] = $this->title(
			'search',
			__( 'Search results', 'seoearth' ),
			/* translators: Keep the %%searchphrase%%, %%page%%, %%separator%% and %%site_name%% variables unchanged. */
			__( 'Search results for “%%searchphrase%%” %%separator%% %%page%% %%separator%% %%site_name%%', 'seoearth' )
		);
		$fields[] = $this->title(
			'404',
			__( 'Page not found (404)', 'seoearth' ),
			/* translators: Keep the %%separator%% and %%site_name%% variables unchanged. */
			__( 'Page not found %%separator%% %%site_name%%', 'seoearth' )
		);

		return $fields;
	}

	/**
	 * Title template field.
	 *
	 * @param string $suffix  Key suffix.
	 * @param string $label   Page type label.
	 * @param string $default_value Default template.
	 */
	private function title( string $suffix, string $label, string $default_value ): Field {
		/* translators: %s: page type, e.g. "Posts" or "Homepage". */
		return new Field( 'title_' . $suffix, self::SECTION, Field::TYPE_TEMPLATE, $default_value, sprintf( __( '%s: title', 'seoearth' ), $label ) );
	}

	/**
	 * Description template field.
	 *
	 * @param string $suffix  Key suffix.
	 * @param string $label   Page type label.
	 * @param string $default_value Default template.
	 */
	private function desc( string $suffix, string $label, string $default_value ): Field {
		/* translators: %s: page type, e.g. "Posts" or "Homepage". */
		return new Field( 'desc_' . $suffix, self::SECTION, Field::TYPE_TEMPLATE, $default_value, sprintf( __( '%s: meta description', 'seoearth' ), $label ) );
	}

	/**
	 * Help text listing the variables, shown above the section.
	 */
	public function render_help(): void {
		$variables = array(
			'%%title%%'        => __( 'Title of the post, term, author or archive', 'seoearth' ),
			'%%site_name%%'    => __( 'Site title', 'seoearth' ),
			'%%sitedesc%%'     => __( 'Site tagline', 'seoearth' ),
			'%%separator%%'    => __( 'Title separator chosen above', 'seoearth' ),
			'%%excerpt%%'      => __( 'Post excerpt, or the start of the content', 'seoearth' ),
			'%%description%%'  => __( 'Term description, author biography or post type description', 'seoearth' ),
			'%%category%%'     => __( 'First category of the post', 'seoearth' ),
			'%%author%%'       => __( 'Author name', 'seoearth' ),
			'%%date%%'         => __( 'Publish date, or the date of a date archive', 'seoearth' ),
			'%%page%%'         => __( '“Page 2 of 5” on paginated pages; empty on page 1', 'seoearth' ),
			'%%searchphrase%%' => __( 'Search terms', 'seoearth' ),
			'%%pt_singular%%'  => __( 'Post type name, singular', 'seoearth' ),
			'%%pt_plural%%'    => __( 'Post type name, plural', 'seoearth' ),
			'%%currentyear%%'  => __( 'Current year', 'seoearth' ),
		);

		echo '<p>' . esc_html__( 'Templates are used when a post, page or term has no custom title or description of its own. Available variables:', 'seoearth' ) . '</p><dl class="seoearth-variables">';
		foreach ( $variables as $variable => $meaning ) {
			printf( '<dt><code>%1$s</code></dt><dd>%2$s</dd>', esc_html( $variable ), esc_html( $meaning ) );
		}
		echo '</dl>';
	}
}
