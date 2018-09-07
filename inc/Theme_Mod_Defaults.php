<?php
namespace Splinter\WP\Kirki\Theme_Mod_Defaults;
const FILTER_PRIORITY = 15;
const CONFIG_FILENAME = 'theme_mod.json';


/**
 * Looks for the kirki.json file in the STYLESHEETPATH, then traverses upwards
 * @return false|string     False on failure. Path string on success.
 */
function _locate_config_file(){
	
	if( !defined( 'STYLESHEETPATH') ){
		_doing_it_wrong( __FUNCTION__, 'This function must be called after the STYLEHSEETPATH constant has been defined',VERSION );
		return false;
	}
	
	$location = wp_cache_get( __FUNCTION__ ) ?: false;
	
	$path = STYLESHEETPATH;
	
	while( $path.'/' !== dirname( ABSPATH ) && false === $location ){
		$_location = $path.'/'. CONFIG_FILENAME;
		if( !file_exists( $_location ) ){
			$path = dirname( $path );
			continue;
		}
		$location = $_location;
		wp_cache_set( __FUNCTION__, $location, __NAMESPACE__ );
	}
	
	return $location;
}

/**
 * @return false|\stdClass     False on failure. JSON Decoded contents of json file
 */
function json_config(){
	
	$json = wp_cache_get( __FUNCTION__, __NAMESPACE__ ) ?: false;
	
	if( $json ){
		return $json;
	}
	
	$config_file_path = _locate_config_file();
	
	if( !$config_file_path ){
		return false;
	}
	
	$json = json_decode( file_get_contents( $config_file_path ), true ) ?: false;
	
	if( $json ){
		wp_cache_set( __FUNCTION__, $json, __NAMESPACE__ );
	}else{
		error_log(__FUNCTION__.': Invalid JSON Format.' );
		error_log(__FUNCTION__.": $config_file_path" );
	}
	
	return $json;
}


/**
 * Checks if there is a config file being used
 *
 * @return bool
 */
function _has_config(){
	if( !json_config() ){
		return false;
	}
	return true;
}

function init(){
	
	if( !_has_config() ){
		return false;
	}
	
	$json_config = json_config();
	
	foreach ( $json_config as $setting_name => $default_value ) {
		add_filter( "theme_mod_{$setting_name}", function ( $value ) use ( $json_config, $setting_name ) {
			return false === $value ? ( $json_config[ $setting_name ] ?? $value ) : $value;
		}, FILTER_PRIORITY );
	}
}

add_action( 'after_setup_theme', __NAMESPACE__.'\init' );