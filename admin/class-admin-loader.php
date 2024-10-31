<?php

namespace Almiro\Wordpress\Nextcellent\Converter\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Admin_Loader {

	/**
	 * The ID of this plugin.
	 *
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	private $page;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		require_once( dirname( __FILE__ ) . '/converter.php' );

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->page        = new Converter_Page();

	}

	/**
	 * Register the pages.
	 *
	 * @since 1.0.0
	 */
	public function register_pages() {
		add_menu_page(
			__( 'Converter', 'ncg-converter' ),
			__( 'Converter', 'ncg-converter' ),
			'edit_posts',
			'converter',
			function () {
				$variables = $this->page->request_handler();
				$this->page->render( $variables );
			},
			'dashicons-hammer'
		);
	}

	/**
	 * Register the settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		register_setting( 'ngg_converter_options', 'ngg_converter_options', [ $this, 'sanitize_settings' ] );
		add_settings_section( 'converter_main', 'Settings', [ $this, 'setting_section_text' ], 'converter' );
		add_settings_field( 'converter_use_as', 'Add as filter', [ $this, 'settings_filter' ], 'converter',
			'converter_main' );
	}

	/**
	 * Sanitize the settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The given input.
	 *
	 * @return array The sanitized input.
	 */
	public function sanitize_settings( $input ) {
		if ( $input['filtered'] === '1' ) {
			$filtered = 1;
		} else {
			$filtered = 0;
		}

		return [ 'filtered' => $filtered ];
	}

	/**
	 * Render the description for the settings.
	 *
	 * @since 1.0.0
	 */
	public function setting_section_text() {
		echo '<p>Change the settings of NextGEN to NextCellent converter.</p>';
	}

	/**
	 * Render the setting for the filter.
	 *
	 * @since 1.0.0
	 */
	public function settings_filter() {
		$options = get_option( 'ngg_converter_options', [ ] );
		if ( ! isset( $options['filtered'] ) ) {
			$options['filtered'] = 0;
		}

		echo '<input name="ngg_converter_options[filtered]" type="checkbox" value="1" ' . checked( 1,
				$options['filtered'], false ) . ' /> Dynamically filter the old NextGEN in posts.<br>
		<p class="description">Note that this will probably slow down your site a little, as it must convert everything every time. This is meant as temporary fix until you convert all of them.</p>
		';
	}
}