<?php

namespace Almiro\Wordpress\Nextcellent\Converter;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 */
class Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = [];
		$this->filters = [];

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $hook       The name of the WordPress action that is being registered.
	 * @param      callable $callable A reference to the instance of the object on which the action is defined.
	 * @param      int $priority      The priority at which the function should be fired.
	 * @param      int $accepted_args The number of arguments that should be passed to the $callback.
	 */
	public function add_action( $hook, callable $callable, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $callable, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $hook       The name of the WordPress filter that is being registered.
	 * @param      callable $callable The name of the function definition on the $component.
	 * @param      int $priority      The priority at which the function should be fired.
	 * @param      int $accepted_args The number of arguments that should be passed to the $callback.
	 */
	public function add_filter( $hook, callable $callable, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $callable, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 *
	 * @param      array $hooks       The collection of hooks that is being registered (that is, actions or filters).
	 * @param      string $hook       The name of the WordPress filter that is being registered.
	 * @param      callable $callable The name of the function definition on the $component.
	 * @param      int $priority      The priority at which the function should be fired.
	 * @param      int $accepted_args The number of arguments that should be passed to the $callback.
	 *
	 * @return array
	 */
	private function add( $hooks, $hook, callable $callable, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'callable'      => $callable,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], $hook['callable'], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], $hook['callable'], $hook['priority'], $hook['accepted_args'] );
		}
	}
}