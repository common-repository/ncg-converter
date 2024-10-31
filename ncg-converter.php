<?php
/**
 * --COPYRIGHT NOTICE------------------------------------------------------------------------------
 *
 * This file is part of NextCellent Converter.
 *
 * NextCellent Converter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * NextCellent Converter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextCellent Converter.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------------------------------
 *
 * @wordpress-plugin
 * Plugin Name:     NextCellent Converter
 * Plugin URI:      https://bitbucket.org/niknetniko/nextcellent-converter
 * Description:     Convert NextGEN 2.0 tags back to NextCellent shortcodes.
 * Version:         1.0.0
 * Author:          niknetniko
 * Author URI:      http://www.almiro.be/
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     ngc-converter
 * Domain Path:     /languages
 */

// If this file is called directly, abort.
use Almiro\Wordpress\Nextcellent\Converter\Deactivator;
use Almiro\Wordpress\Nextcellent\Converter\NCG_Converter;

if ( ! defined( 'WPINC' ) ) {
	wp_die('You cannot call this page directly');
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	function nextcellent_converter_check() {
		return ! defined( 'NEXTGEN_GALLERY_PLUGIN_VERSION ' ) && defined( 'NGG_FOLDER' ) && version_compare( PHP_VERSION, '5.4.0' ) >= 0;
	}

	if ( ! nextcellent_converter_check() ) {
		/**
		 * The core plugin class that is used to define internationalization,
		 * admin-specific hooks, and public-facing site hooks.
		 */
		require plugin_dir_path( __FILE__ ) . 'includes/class-ncg-converter.php';

		$plugin = new NCG_Converter();
		$plugin->run();
	}
}

run_plugin_name();