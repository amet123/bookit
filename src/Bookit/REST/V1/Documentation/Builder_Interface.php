<?php
/**
 * The Builder Interface.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Documentation
 */

namespace Bookit\REST\V1\Documentation;

/**
 * Builder Interface.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Documentation
 */
interface Builder_Interface {

	/**
	 * Registers a documentation provider for a path.
	 *
	 * @since 2.5.0
	 *
	 * @param string             $path     The path to register the provider for.
	 * @param Provider_Interface $endpoint The endpoint provider.
	 */
	public function register_documentation_provider( $path, Provider_Interface $endpoint );

	/**
	 * Returns the registered documentation providers.
	 *
	 * @since 2.5.0
	 *
	 * @return array<string, Provider_Interface> The registered documentation providers.
	 */
	public function get_registered_documentation_providers();

	/**
	 * Registers a documentation provider for a definition.
	 *
	 * @since 2.5.0
	 *
	 * @param string             $type     The type of the definition.
	 * @param Provider_Interface $provider The provider for the definition.
	 */
	public function register_definition_provider( $type, Provider_Interface $provider );

	/**
	 * Returns the registered definition providers.
	 *
	 * @since 2.5.0
	 *
	 * @return array<string, Provider_Interface> The registered definition providers.
	 */
	public function get_registered_definition_providers();
}
