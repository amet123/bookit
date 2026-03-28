<?php
/**
 * The main Bookit plugin service provider: it bootstraps the plugin code.
 *
 * @since 2.5.0
 *
 * @package Bookit
 */

namespace Bookit;

use Bookit\Contracts\Container;
use Bookit\Gateways\StripeConnect\Provider as Stripe_Provider;
use Bookit\REST\Provider as REST_Provider;

/**
 * Class Plugin
 *
 * @since 2.5.0
 *
 * @package Bookit
 */
class Plugin {

	/**
	 * Stores the version for the plugin.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public const VERSION = '2.5.4';

	/**
	 * Stores the base slug for the plugin.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const SLUG = 'bookit';

	/**
	 * Stores the base slug for the plugin
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const FILE = BOOKIT_FILE;

	/**
	 * @var bool Prevent autoload initialization
	 */
	private $should_prevent_autoload_init = false;

	/**
	 * @since 2.5.0
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since 2.5.0
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since 2.5.0
	 *
	 * @var string Plugin basename.
	 */
	public $plugin_basename;

	/**
	 * @since 2.5.0
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * @since 2.5.0
	 *
	 * @var string Plugin Base Path.
	 */
	public static $plugin_base_path;

	/**
	 * @since 2.5.0
	 *
	 * @var string Plugin Base URL.
	 */
	public static $plugin_base_url;

	/**
	 * Allows this class to be used as a singleton.
	 *
	 * Note this specifically doesn't have a typing, just a type hinting via Docblocks, it helps
	 * avoid problems with deprecation since this is loaded so early.
	 *
	 * @since 2.5.0
	 *
	 * @var \Bookit__Container
	 */
	protected $container;

	/**
	 * Sets the container for the class.
	 *
	 * Note this specifically doesn't have a typing for the container, just a type hinting via Docblocks, it helps
	 * avoid problems with deprecation since this is loaded so early.
	 *
	 * @since 2.5.0
	 *
	 * @param ?\Bookit__Container $container The container to use, if any. If not provided, the global container will be used.
	 */
	public function set_container( $container = null ): void {
		$this->container = $container ?: new Container();
	}

	/**
	 * Boots the plugin class and registers it as a singleton.
	 *
	 * Note this specifically doesn't have a typing for the container, just a type hinting via Docblocks, it helps
	 * avoid problems with deprecation since this is loaded so early.
	 *
	 * @since 2.5.0
	 *
	 * @param ?\Bookit__Container $container The container to use, if any. If not provided, the global container will be used.
	 */
	public function boot( $container = null ): void {
		$this->plugin_path      = trailingslashit( dirname( static::FILE ) );
		self::$plugin_base_path = $this->plugin_path;
		$this->plugin_basename  = basename( static::FILE );
		$this->plugin_dir       = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url       = plugins_url( $this->plugin_dir, $this->plugin_path );
		self::$plugin_base_url  = $this->plugin_url;

		add_action( 'plugins_loaded', [ $this, 'bootstrap' ], 1 );
	}

	/**
	 * Plugins shouldn't include their functions before `plugins_loaded` because this will allow
	 * better compatibility with the autoloader methods.
	 *
	 * @since 2.5.0
	 */
	public function bootstrap() {
		if ( $this->should_prevent_autoload_init ) {
			return;
		}
		$plugin = new static();
		$plugin->register_autoloader();
		$plugin->set_container();
		$plugin->container->singleton( static::class, $plugin );
		$plugin->register();
	}

	/**
	 * Setup the Extension's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 *
	 * @since 2.5.0
	 */
	public function register() {
		$this->register_autoloader();

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'bookit', $this );
		$this->container->singleton( 'bookit.plugin', $this );

		$this->container->register( REST_Provider::class );
		$this->container->register( Stripe_Provider::class );
	}

	/**
	 * Register the Autoloader for Bookit.
	 *
	 * @since 2.5.0
	 */
	protected function register_autoloader() {
		// Load Composer autoload and strauss autoloader.
		require_once dirname( BOOKIT_FILE ) . '/vendor/vendor-prefixed/autoload.php';
		require_once dirname( BOOKIT_FILE ) . '/vendor/autoload.php';

		// The DI container class.
		require_once dirname( __FILE__ ) . '/Container.php';
	}

	/**
	 * Get Vendor URL.
	 *
	 * @since 2.5.0
	 */
	public static function get_vendor_url() {
		return self::$plugin_base_url . 'vendor/';
	}

	/**
	 * Get Assets Path.
	 *
	 * @since 2.5.0
	 */
	public static function get_asset_path() {
		return self::$plugin_base_path . 'src/resources/';
	}


	/**
	 * Get Assets Path.
	 *
	 * @since 2.5.0
	 */
	public static function get_asset_url() {
		return self::$plugin_base_url . 'src/resources/';
	}

	/**
	 * Plugin activation callback.
	 * @see register_activation_hook()
	 *
	 * @since 2.5.0
	 */
	public static function activate() {}

	/**
	 * Plugin deactivation callback.
	 * @see register_deactivation_hook()
	 *
	 * @since 2.5.0
	 *
	 * @param bool $network_deactivating
	 */
	public static function deactivate( $network_deactivating ) {
		flush_rewrite_rules();
	}
}
