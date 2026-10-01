<?php
/**
 * Plugin container and module registry.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools;

use ShubhamTiwariSeoTools\Analysis\AnalysisModule;
use ShubhamTiwariSeoTools\Analysis\Engine;
use ShubhamTiwariSeoTools\Analysis\InputFactory;
use ShubhamTiwariSeoTools\Admin\EditorModule;
use ShubhamTiwariSeoTools\Admin\Metabox;
use ShubhamTiwariSeoTools\Admin\SettingsPage;
use ShubhamTiwariSeoTools\Admin\TermFields;
use ShubhamTiwariSeoTools\Breadcrumbs\BreadcrumbsModule;
use ShubhamTiwariSeoTools\Breadcrumbs\Renderer;
use ShubhamTiwariSeoTools\Breadcrumbs\Trail;
use ShubhamTiwariSeoTools\Compatibility\Conflicts;
use ShubhamTiwariSeoTools\Frontend\CurrentPage;
use ShubhamTiwariSeoTools\Frontend\HeadModule;
use ShubhamTiwariSeoTools\Redirects\AdminScreen as RedirectsAdmin;
use ShubhamTiwariSeoTools\Redirects\RedirectsModule;
use ShubhamTiwariSeoTools\Redirects\Store as RedirectStore;
use ShubhamTiwariSeoTools\Images\ImagesModule;
use ShubhamTiwariSeoTools\Meta\Canonical;
use ShubhamTiwariSeoTools\Meta\MetaModule;
use ShubhamTiwariSeoTools\Meta\Resolver;
use ShubhamTiwariSeoTools\Meta\Robots;
use ShubhamTiwariSeoTools\Meta\TemplateEngine;
use ShubhamTiwariSeoTools\Meta\SearchAppearanceFields;
use ShubhamTiwariSeoTools\Meta\VariableValues;
use ShubhamTiwariSeoTools\Migrations\Migrator;
use ShubhamTiwariSeoTools\Migrations\Registry;
use ShubhamTiwariSeoTools\Schema\Graph;
use ShubhamTiwariSeoTools\Schema\SchemaModule;
use ShubhamTiwariSeoTools\Settings\Sanitizer;
use ShubhamTiwariSeoTools\Settings\Schema;
use ShubhamTiwariSeoTools\Settings\Settings;
use ShubhamTiwariSeoTools\Sitemap\Exclusions;
use ShubhamTiwariSeoTools\Sitemap\Images;
use ShubhamTiwariSeoTools\Sitemap\SitemapModule;
use ShubhamTiwariSeoTools\Social\SocialModule;
use ShubhamTiwariSeoTools\Social\SocialTags;
use ShubhamTiwariSeoTools\WooCommerce\WooModule;

defined( 'ABSPATH' ) || exit;

/**
 * Owns the service container and boots modules once.
 *
 * Extensions (including a future Pro add-on) add modules through the
 * `stseo_modules` filter and services through the `stseo_container`
 * action rather than by editing this class.
 */
final class Plugin {

	/**
	 * Shared instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Registered modules keyed by ID.
	 *
	 * @var array<string, Module>
	 */
	private $modules = array();

	/**
	 * Whether boot() has run.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Constructor.
	 *
	 * @param Container $container Service container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Returns the shared instance.
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self( self::build_container() );
		}
		return self::$instance;
	}

	/**
	 * Declares core services.
	 */
	public static function build_container(): Container {
		$container = new Container();

		$container->set(
			Context::class,
			static function () {
				return new Context();
			}
		);
		$container->set(
			Migrator::class,
			static function () {
				return new Migrator( STSEO_VERSION, Registry::all() );
			}
		);
		$container->set(
			Schema::class,
			static function () {
				return new Schema();
			}
		);
		$container->set(
			Settings::class,
			static function ( Container $c ) {
				return new Settings( $c->get( Schema::class ) );
			}
		);
		$container->set(
			Sanitizer::class,
			static function ( Container $c ) {
				return new Sanitizer( $c->get( Schema::class ) );
			}
		);
		$container->set(
			SettingsPage::class,
			static function ( Container $c ) {
				return new SettingsPage( $c->get( Context::class ), $c->get( Settings::class ), $c->get( Sanitizer::class ) );
			}
		);
		$container->set(
			Resolver::class,
			static function ( Container $c ) {
				return new Resolver( $c->get( Settings::class ), new TemplateEngine(), new VariableValues() );
			}
		);
		$container->set(
			Robots::class,
			static function ( Container $c ) {
				return new Robots( $c->get( Settings::class ) );
			}
		);
		$container->set(
			SitemapModule::class,
			static function ( Container $c ) {
				return new SitemapModule( $c->get( Settings::class ), new Exclusions( $c->get( Settings::class ) ), new Images() );
			}
		);
		$container->set(
			MetaModule::class,
			static function () {
				return new MetaModule( new SearchAppearanceFields() );
			}
		);
		$container->set(
			CurrentPage::class,
			static function ( Container $c ) {
				return new CurrentPage( $c->get( Resolver::class ), new Canonical(), $c->get( Robots::class ) );
			}
		);
		$container->set(
			SocialModule::class,
			static function ( Container $c ) {
				return new SocialModule(
					$c->get( Settings::class ),
					$c->get( CurrentPage::class ),
					new SocialTags( $c->get( Settings::class ), $c->get( Resolver::class ) ),
					new Conflicts()
				);
			}
		);
		$container->set(
			SchemaModule::class,
			static function ( Container $c ) {
				return new SchemaModule(
					$c->get( Settings::class ),
					$c->get( CurrentPage::class ),
					new Graph( $c->get( Settings::class ), new Trail() ),
					new Conflicts()
				);
			}
		);
		$container->set(
			AnalysisModule::class,
			static function ( Container $c ) {
				return new AnalysisModule( new InputFactory( $c->get( Resolver::class ), $c->get( Robots::class ) ), Engine::seo(), Engine::readability() );
			}
		);
		$container->set(
			BreadcrumbsModule::class,
			static function ( Container $c ) {
				return new BreadcrumbsModule( $c->get( CurrentPage::class ), new Renderer( $c->get( Settings::class ), new Trail() ) );
			}
		);
		$container->set(
			ImagesModule::class,
			static function ( Container $c ) {
				return new ImagesModule( $c->get( Context::class ) );
			}
		);
		$container->set(
			RedirectStore::class,
			static function () {
				return new RedirectStore();
			}
		);
		$container->set(
			RedirectsModule::class,
			static function ( Container $c ) {
				return new RedirectsModule( $c->get( RedirectStore::class ) );
			}
		);
		$container->set(
			RedirectsAdmin::class,
			static function ( Container $c ) {
				return new RedirectsAdmin( $c->get( Context::class ), $c->get( RedirectStore::class ) );
			}
		);
		$container->set(
			WooModule::class,
			static function ( Container $c ) {
				$schema = $c->get( SchemaModule::class );
				return new WooModule( $schema instanceof SchemaModule ? $schema : null );
			}
		);
		$container->set(
			HeadModule::class,
			static function ( Container $c ) {
				return new HeadModule( $c->get( Context::class ), $c->get( CurrentPage::class ) );
			}
		);
		$container->set(
			EditorModule::class,
			static function () {
				return new EditorModule();
			}
		);
		$container->set(
			Metabox::class,
			static function ( Container $c ) {
				return new Metabox( $c->get( Context::class ) );
			}
		);
		$container->set(
			TermFields::class,
			static function ( Container $c ) {
				return new TermFields( $c->get( Context::class ) );
			}
		);

		return $container;
	}

	/**
	 * The service container.
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Migrates data if needed, then registers all applicable modules.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		$this->migrator()->maybe_run();

		/**
		 * Fires before modules are built, so extensions can declare or replace services.
		 *
		 * @param Container $container Service container.
		 */
		do_action( 'stseo_container', $this->container );

		/**
		 * Filters the modules ShubhamTiwari SEO Tools loads.
		 *
		 * Third-party callbacks may return anything, so entries that are not
		 * Module instances are skipped.
		 *
		 * @param array<string, mixed> $modules   Modules keyed by ID.
		 * @param Container            $container Service container.
		 */
		$modules = apply_filters( 'stseo_modules', $this->default_modules(), $this->container );

		foreach ( (array) $modules as $id => $module ) {
			if ( ! $module instanceof Module || ! $module->should_load() ) {
				continue;
			}
			$module->register();
			$this->modules[ (string) $id ] = $module;
		}

		/**
		 * Fires after ShubhamTiwari SEO Tools has registered its modules.
		 *
		 * @param Plugin $plugin The plugin instance.
		 */
		do_action( 'stseo_loaded', $this );
	}

	/**
	 * Returns a loaded module, or null.
	 *
	 * @param string $id Module ID.
	 */
	public function module( string $id ): ?Module {
		return $this->modules[ $id ] ?? null;
	}

	/**
	 * The migrator service.
	 *
	 * @throws \UnexpectedValueException When an extension replaced it with the wrong type.
	 */
	private function migrator(): Migrator {
		$migrator = $this->container->get( Migrator::class );
		if ( ! $migrator instanceof Migrator ) {
			throw new \UnexpectedValueException( 'The Migrator service was replaced with an incompatible object.' );
		}
		return $migrator;
	}

	/**
	 * Built-in modules. Populated as feature phases land.
	 *
	 * Modules are built from the container only here, after extensions had
	 * their chance to replace services.
	 *
	 * @return array<string, mixed>
	 */
	private function default_modules(): array {
		return array(
			'meta'          => $this->container->get( MetaModule::class ),
			'settings_page' => $this->container->get( SettingsPage::class ),
			'term_fields'   => $this->container->get( TermFields::class ),
			'editor'        => $this->container->get( EditorModule::class ),
			'metabox'       => $this->container->get( Metabox::class ),
			'head'          => $this->container->get( HeadModule::class ),
			'sitemap'       => $this->container->get( SitemapModule::class ),
			'social'        => $this->container->get( SocialModule::class ),
			'schema'        => $this->container->get( SchemaModule::class ),
			'analysis'      => $this->container->get( AnalysisModule::class ),
			'breadcrumbs'   => $this->container->get( BreadcrumbsModule::class ),
			'images'        => $this->container->get( ImagesModule::class ),
			'redirects'     => $this->container->get( RedirectsModule::class ),
			'redirects_ui'  => $this->container->get( RedirectsAdmin::class ),
			'woocommerce'   => $this->container->get( WooModule::class ),
		);
	}

	/**
	 * Resets state. For tests only.
	 *
	 * @internal
	 */
	public static function reset(): void {
		self::$instance = null;
	}
}
