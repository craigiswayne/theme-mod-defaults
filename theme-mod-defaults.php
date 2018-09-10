<?php
/**
 * Plugin Name:     Theme Mod Defaults
 * Plugin URI:      https://github.com/craigiswayne/theme-mod-defaults
 * Description:     Set default Theme Mods via JSON Configuration
 * Author:          Craig Wayne
 * Author URI:      https://github.com/craigiswayne
 * Text Domain:     theme-mod-defaults
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Theme_Mod_Defaults
 */

namespace Splinter\WP\Theme_Mod_Defaults;

if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Naughty Naughty...' );
}

const FILTER_PRIORITY = 15;
const CONFIG_FILENAME = 'theme_mod.json';


/**
 * Looks for the theme_mod.json file in the STYLESHEETPATH, then traverses upwards
 *
 * @return false|string     False on failure. Path string on success.
 */
function _locate_config_file() {

	if ( ! defined( 'STYLESHEETPATH' ) ) {
		_doing_it_wrong( __FUNCTION__, 'This function must be called after the STYLEHSEETPATH constant has been defined', VERSION );

		return false;
	}

	$location = wp_cache_get( __FUNCTION__ ) ?: false;

	$path = STYLESHEETPATH;

	while ( dirname( ABSPATH ) !== $path . '/' && false === $location ) {
		$_location = $path . '/' . CONFIG_FILENAME;
		if ( ! file_exists( $_location ) ) {
			$path = dirname( $path );
			continue;
		}
		$location = $_location;
		wp_cache_set( __FUNCTION__, $location, __NAMESPACE__ );
	}

	return $location;
}

/**
 * Fetches the json config as an associative array
 *
 * @return false|array     False on failure
 */
function json_config() {

	$json = wp_cache_get( __FUNCTION__, __NAMESPACE__ ) ?: false;

	if ( $json ) {
		return $json;
	}

	$config_file_path = _locate_config_file();

	if ( ! $config_file_path ) {
		return false;
	}

	$json = json_decode( file_get_contents( $config_file_path ), true ) ?: false;

	if ( $json ) {
		wp_cache_set( __FUNCTION__, $json, __NAMESPACE__ );
	} else {
		error_log( __FUNCTION__ . ': Invalid JSON Format.' );
		error_log( __FUNCTION__ . ": $config_file_path" );
	}

	return $json;
}


/**
 * Checks if there is a config file being used
 *
 * @return bool
 */
function _has_config() {
	if ( ! json_config() ) {
		return false;
	}

	return true;
}

/**
 * This is the entry point for this plugin.
 * Sort of a controller, checking if a config exists/valid
 *
 * @return bool
 */
function init() {

	if ( ! _has_config() ) {
		return false;
	}

	$json_config = json_config();

	foreach ( $json_config as $setting_name => $default_value ) {
		add_filter(
			"theme_mod_{$setting_name}", function ( $value ) use ( $json_config, $setting_name ) {

				if ( false !== $value ) {
					return $value;
				}

				return isset( $json_config[ $setting_name ] ) ? $json_config[ $setting_name ] : $value;

			}, FILTER_PRIORITY
		);
	}
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\init' );
