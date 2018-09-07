<?php
/**
 * Plugin Name:     Theme Mod Defaults
 * Plugin URI:      https://github.com/craigiswayne/theme-mod-defaults
 * Description:     Set default Theme Mods
 * Author:          Craig Wayne
 * Author URI:      https://github.com/craigiswayne
 * Text Domain:     theme-mod-defaults
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Theme_Mod_Defaults
 */

namespace Splinter\WP;

if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Naughty Naughty...' );
}

require_once( __DIR__ . '/inc/Theme_Mod_Defaults.php' );
