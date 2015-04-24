<?php
/*
Plugin Name: TTT Crop
Plugin URI: https://github.com/33themes/ttt-crop
Description: Simple and quickly image editor inline
Version: 0.1.3
Author: 33 Themes UG i.Gr.
Author URI: http://www.33themes.com
*/





define('TTTINC_CROP', dirname(__FILE__) );
define('TTTVERSION_CROP', 0.1 );


function ttt_autoload_crop( $class ) {
	if ( 0 !== strpos( $class, 'TTTCrop_' ) )
		return;
	
	$file = TTTINC_CROP . '/class/' . $class . '.php';
	if (is_file($file))
		require_once $file;
		return true;
	
	throw new Exception("Unable to load $class at ".$file);
}

if ( function_exists( 'spl_autoload_register' ) ) {
	spl_autoload_register( 'ttt_autoload_crop' );
} else {
	require_once TTTINC_CROP . '/class/TTTCrop_Common.php';
}

function tttcrop_init () {
 	$s = load_plugin_textdomain( 'tttcrop', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	if ( !is_admin() ) {
		global $TTTCrop_Front;
		$TTTCrop_Front = new TTTCrop_Front();
		$TTTCrop_Front->init();
	}
	else {
		$TTTCrop_Admin = new TTTCrop_Admin();
		$TTTCrop_Admin->init();
	}
}

add_action('init', 'tttcrop_init', 10);

register_activation_hook( __FILE__, array( 'TTTCrop_Admin', 'on_activation' ) );

?>
