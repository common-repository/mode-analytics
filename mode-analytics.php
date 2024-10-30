<?php
/**
 * Plugin Name:     Mode Analytics
 * Plugin URI:      https://modeanalytics.com/
 * Description:     WordPress plugin for Mode Analytics
 * Author:          Mode Analytics
 * Author URI:      https://modeanalytics.com/
 * Text Domain:     mode-analytics
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Mode_Analytics
 */

add_action( 'init', array( 'Mode_Analytics\Shortcode', 'action_init_register' ) );
add_action( 'admin_init', array( 'Mode_Analytics\Settings', 'action_admin_init' ) );
add_action( 'admin_menu', array( 'Mode_Analytics\Settings', 'action_admin_menu' ) );
add_action( 'admin_enqueue_scripts', array( 'Mode_Analytics\Assets', 'action_admin_enqueue_scripts' ) );
add_action( 'show_user_profile', array( 'Mode_Analytics\Settings', 'action_edit_user_profile' ) );
add_action( 'edit_user_profile', array( 'Mode_Analytics\Settings', 'action_edit_user_profile' ) );
add_action( 'edit_user_profile_update', array( 'Mode_Analytics\Settings', 'action_edit_user_profile_update' ) );
add_action( 'personal_options_update', array( 'Mode_Analytics\Settings', 'action_edit_user_profile_update' ) );

/**
 * Register the class autoloader
 */
spl_autoload_register(
	function( $class ) {
		$class = ltrim( $class, '\\' );
		if ( 0 !== stripos( $class, 'Mode_Analytics\\' ) ) {
			return;
		}

		$parts = explode( '\\', $class );
		array_shift( $parts ); // Don't need "Mode_Analytics".
		$last    = array_pop( $parts ); // File should be 'class-[...].php'.
		$last    = 'class-' . $last . '.php';
		$parts[] = $last;
		$file    = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );
		if ( file_exists( $file ) ) {
			require $file;
		}

	}
);
