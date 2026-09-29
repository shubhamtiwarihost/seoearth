<?php
/**
 * Plugin container and module registry.
 *
 * @package SEOEarth
 */

namespace SEOEarth;

defined( 'ABSPATH' ) || exit;

/**
 * Holds the module registry and boots modules once.
 *
 * Extensions (including a future Pro add-on) add modules through the
 * `seoearth_modules` filter rather than by editing this class.
 */
final class Plugin {

	/**
	 * Shared instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

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
	 * Returns the shared instance.
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Loads translations and registers all applicable modules.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		/**
		 * Filters the modules SEOEarth loads.
		 *
		 * Third-party callbacks may return anything, so entries that are not
		 * Module instances are skipped.
		 *
		 * @param array<string, mixed> $modules Modules keyed by ID.
		 */
		$modules = apply_filters( 'seoearth_modules', $this->default_modules() );

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
	 * Built-in modules. Populated from Phase 3 onwards.
	 *
	 * @return array<string, Module>
	 */
	private function default_modules(): array {
		return array();
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
