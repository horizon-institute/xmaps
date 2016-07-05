<?php
/**
 * Settings manipulation functionality.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/**
 * Settings manipulation functionality.
 */
class XMapsSettings {

	/**
	 * Register plugin settings.
	 */
	public static function register_settings() {
		register_setting(
			'xmaps-options',
			'xmaps-google-maps-api-key',
		'sanitize_text_field' );
		add_settings_section(
			'xmaps-google',
			'Google Settings',
			function () { return ''; },
		'xmaps-options');
		add_settings_field(
			'xmaps-google-maps-api-key',
			'Maps API Key',
			function() {
				require 'fragments/settings/google-api-key-input.php';
			},
			'xmaps-options',
		'xmaps-google');
	}

	/**
	 * Adds plugin settings fields to option pages.
	 */
	public static function add_option_pages() {
		add_options_page(
			'xMaps Settings',
			'xMaps',
			'manage_options',
			'xmaps-options',
			$f = function() {
				require 'fragments/settings/admin-page.php';
			}
		);
	}
}
?>
