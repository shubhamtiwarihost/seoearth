<?php
/**
 * Plugin container and module registry.
 *
 * @package SEOEarth
 */

namespace SEOEarth;

use SEOEarth\Admin\SettingsPage;
use SEOEarth\Admin\TermFields;
use SEOEarth\Breadcrumbs\Trail;
use SEOEarth\Compatibility\Conflicts;
use SEOEarth\Frontend\CurrentPage;
use SEOEarth\Frontend\HeadModule;
use SEOEarth\Meta\Canonical;
use SEOEarth\Meta\MetaModule;
use SEOEarth\Meta\Resolver;
use SEOEarth\Meta\Robots;
use SEOEarth\Meta\TemplateEngine;
use SEOEarth\Meta\SearchAppearanceFields;
use SEOEarth\Meta\VariableValues;
use SEOEarth\Migrations\Migrator;
use SEOEarth\Migrations\Registry;
use SEOEarth\Schema\Graph;
use SEOEarth\Schema\SchemaModule;
use SEOEarth\Settings\Sanitizer;
use SEOEarth\Settings\Schema;
use SEOEarth\Settings\Settings;
use SEOEarth\Sitemap\Exclusions;
use SEOEarth\Sitemap\Images;
use SEOEarth\Sitemap\SitemapModule;
use SEOEarth\Social\SocialModule;
use SEOEarth\Social\SocialTags;

defined( 'ABSPATH' ) || exit;

/**
 * Owns the service container and boots modules once.
 *
 * Extensions (including a future Pro add-on) add modules through the
 * `seoearth_modules` filter and services through the `seoearth_container`
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
				return new Migrator( SEOEARTH_VERSION, Registry::all() );
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
			HeadModule::class,
			static function ( Container $c ) {
				return new HeadModule( $c->get( Context::class ), $c->get( CurrentPage::class ) );
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
		do_action( 'seoearth_container', $this->container );

		/**
		 * Filters the modules SEOEarth loads.
		 *
		 * Third-party callbacks may return anything, so entries that are not
		 * Module instances are skipped.
		 *
		 * @param array<string, mixed> $modules   Modules keyed by ID.
		 * @param Container            $container Service container.
		 */
		$modules = apply_filters( 'seoearth_modules', $this->default_modules(), $this->container );

		foreach ( (array) $modules as $id => $module ) {
			if ( ! $module instanceof Module || ! $module->should_load() ) {
				continue;
			}
			$module->register();
			$this->modules[ (string) $id ] = $module;
		}

		/**
		 * Fires after SEOEarth has registered its modules.
		 *
		 * @param Plugin $plugin The plugin instance.
		 */
		do_action( 'seoearth_loaded', $this );
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
			'head'          => $this->container->get( HeadModule::class ),
			'sitemap'       => $this->container->get( SitemapModule::class ),
			'social'        => $this->container->get( SocialModule::class ),
			'schema'        => $this->container->get( SchemaModule::class ),
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
