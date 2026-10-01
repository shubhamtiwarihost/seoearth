<?php
/**
 * Search appearance settings: templates and indexing per page type.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Meta;

use ShubhamTiwariSeoTools\Settings\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the search appearance settings per page type: a title template, a
 * description template and (where it makes sense) a noindex toggle for the
 * homepage, each public post type, each post type archive, each public
 * taxonomy, author archives, date archives, search results and 404.
 *
 * The homepage has no noindex toggle on purpose; search and 404 are always noindex.
 *
 * Built after `init`, when post types and taxonomies are registered.
 */
final class SearchAppearanceFields {

	public const SECTION = 'search_appearance';

	/**
	 * All template fields.
	 *
	 * @return Field[]
	 */
	public function fields(): array {
		$page   = ' %%separator%% %%page%% %%separator%% %%site_name%%';
		$fields = array(
			$this->title( 'home', __( 'Homepage', 'shubhamtiwari-seo-tools' ), '%%site_name%% %%separator%% %%page%% %%separator%% %%sitedesc%%' ),
			$this->desc( 'home', __( 'Homepage', 'shubhamtiwari-seo-tools' ), '%%sitedesc%%' ),
		);

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			$slug     = Keys::slug( $type->name );
			$label    = (string) $type->labels->name;
			$fields[] = $this->title( 'pt_' . $slug, $label, '%%title%%' . $page );
			$fields[] = $this->desc( 'pt_' . $slug, $label, '%%excerpt%%' );
			$fields[] = $this->noindex( 'pt_' . $slug, $label );

			if ( $type->has_archive ) {
				/* translators: %s: post type plural name, e.g. "Products". */
				$archive  = sprintf( __( '%s archive', 'shubhamtiwari-seo-tools' ), $label );
				$fields[] = $this->title( 'ptarchive_' . $slug, $archive, '%%pt_plural%%' . $page );
				$fields[] = $this->desc( 'ptarchive_' . $slug, $archive, '%%description%%' );
			}
		}

		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $taxonomy ) {
			$slug     = Keys::slug( $taxonomy->name );
			$label    = (string) $taxonomy->labels->name;
			$fields[] = $this->title( 'tax_' . $slug, $label, '%%title%%' . $page );
			$fields[] = $this->desc( 'tax_' . $slug, $label, '%%description%%' );
			$fields[] = $this->noindex( 'tax_' . $slug, $label );
		}

		$author   = __( 'Author archives', 'shubhamtiwari-seo-tools' );
		$fields[] = $this->title( 'author', $author, '%%author%%' . $page );
		$fields[] = $this->desc( 'author', $author, '%%description%%' );
		$fields[] = $this->noindex( 'author', $author );

		$date     = __( 'Date archives', 'shubhamtiwari-seo-tools' );
		$fields[] = $this->title( 'date', $date, '%%date%%' . $page );
		$fields[] = $this->desc( 'date', $date, '' );
		$fields[] = $this->noindex( 'date', $date );

		$fields[] = $this->title(
			'search',
			__( 'Search results', 'shubhamtiwari-seo-tools' ),
			/* translators: Keep the %%searchphrase%%, %%page%%, %%separator%% and %%site_name%% variables unchanged. */
			__( 'Search results for “%%searchphrase%%” %%separator%% %%page%% %%separator%% %%site_name%%', 'shubhamtiwari-seo-tools' )
		);
		$fields[] = $this->title(
			'404',
			__( 'Page not found (404)', 'shubhamtiwari-seo-tools' ),
			/* translators: Keep the %%separator%% and %%site_name%% variables unchanged. */
			__( 'Page not found %%separator%% %%site_name%%', 'shubhamtiwari-seo-tools' )
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
		return new Field( 'title_' . $suffix, self::SECTION, Field::TYPE_TEMPLATE, $default_value, sprintf( __( '%s: title', 'shubhamtiwari-seo-tools' ), $label ) );
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
		return new Field( 'desc_' . $suffix, self::SECTION, Field::TYPE_TEMPLATE, $default_value, sprintf( __( '%s: meta description', 'shubhamtiwari-seo-tools' ), $label ) );
	}

	/**
	 * "Hide from search engines" toggle. Off by default: nothing is hidden unless the owner asks.
	 *
	 * @param string $suffix Key suffix.
	 * @param string $label  Page type label.
	 */
	private function noindex( string $suffix, string $label ): Field {
		return new Field(
			'noindex_' . $suffix,
			self::SECTION,
			Field::TYPE_BOOL,
			false,
			/* translators: %s: page type, e.g. "Posts" or "Author archives". */
			sprintf( __( '%s: hide from search engines (noindex)', 'shubhamtiwari-seo-tools' ), $label ),
			__( 'Individual items can still be set to “Index” in their own SEO settings.', 'shubhamtiwari-seo-tools' )
		);
	}

	/**
	 * Help text listing the variables, shown above the section.
	 */
	public function render_help(): void {
		$variables = array(
			'%%title%%'        => __( 'Title of the post, term, author or archive', 'shubhamtiwari-seo-tools' ),
			'%%site_name%%'    => __( 'Site title', 'shubhamtiwari-seo-tools' ),
			'%%sitedesc%%'     => __( 'Site tagline', 'shubhamtiwari-seo-tools' ),
			'%%separator%%'    => __( 'Title separator chosen above', 'shubhamtiwari-seo-tools' ),
			'%%excerpt%%'      => __( 'Post excerpt, or the start of the content', 'shubhamtiwari-seo-tools' ),
			'%%description%%'  => __( 'Term description, author biography or post type description', 'shubhamtiwari-seo-tools' ),
			'%%category%%'     => __( 'First category of the post', 'shubhamtiwari-seo-tools' ),
			'%%author%%'       => __( 'Author name', 'shubhamtiwari-seo-tools' ),
			'%%date%%'         => __( 'Publish date, or the date of a date archive', 'shubhamtiwari-seo-tools' ),
			'%%page%%'         => __( '“Page 2 of 5” on paginated pages; empty on page 1', 'shubhamtiwari-seo-tools' ),
			'%%searchphrase%%' => __( 'Search terms', 'shubhamtiwari-seo-tools' ),
			'%%pt_singular%%'  => __( 'Post type name, singular', 'shubhamtiwari-seo-tools' ),
			'%%pt_plural%%'    => __( 'Post type name, plural', 'shubhamtiwari-seo-tools' ),
			'%%currentyear%%'  => __( 'Current year', 'shubhamtiwari-seo-tools' ),
		);

		echo '<p>' . esc_html__( 'Templates are used when a post, page or term has no custom title or description of its own. Available variables:', 'shubhamtiwari-seo-tools' ) . '</p><dl class="stseo-variables">';
		foreach ( $variables as $variable => $meaning ) {
			printf( '<dt><code>%1$s</code></dt><dd>%2$s</dd>', esc_html( $variable ), esc_html( $meaning ) );
		}
		echo '</dl>';
	}
}
