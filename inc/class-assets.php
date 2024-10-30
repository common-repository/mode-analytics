<?php
/**
 * Enqueue scripts and styles.
 *
 * @package Mode_Analytics
 */

namespace Mode_Analytics;

/**
 * Enqueue scripts and styles.
 */
class Assets {

	/**
	 * Enqueue scripts and styles in the admin.
	 */
	public static function action_admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'settings_page_mode-analytics' === $screen->id ) {
			$fmtime = filemtime( dirname( __DIR__ ) . '/assets/js/settings.js' );
			wp_enqueue_script( 'mode-analytics-settings', plugins_url( 'assets/js/settings.js', __DIR__ ), array( 'jquery', 'wp-util' ), $fmtime );
		}
		if ( in_array( $screen->id, array( 'profile', 'user-edit' ), true ) ) {
			$fmtime = filemtime( dirname( __DIR__ ) . '/assets/js/user.js' );
			wp_enqueue_script( 'mode-analytics-user', plugins_url( 'assets/js/user.js', __DIR__ ), array( 'jquery', 'wp-util' ), $fmtime );
		}
	}

}
