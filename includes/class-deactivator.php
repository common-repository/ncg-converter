<?php

namespace Almiro\Wordpress\Nextcellent\Converter;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Deactivator {

	public static function deactivate() {

		unregister_setting( 'nextcellent_converter', 'ngg_converter_options' );

	}
}