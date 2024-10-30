<?php
/**
 * Manages the shortcode.
 *
 * @package Mode_Analytics
 */

namespace Mode_Analytics;

/**
 * Manages the shortcode.
 */
class Shortcode {

	/**
	 * Name of the shortcode.
	 */
	const NAME = 'mode-analytics';

	/**
	 * Registers the shortcode.
	 */
	public static function action_init_register() {
		add_shortcode( self::NAME, array( __CLASS__, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array $attrs Shortcode attributes.
	 */
	public static function render( $attrs ) {
		$defaults = array(
			'report_name' => '',
		);
		$attrs    = array_merge( $defaults, $attrs );

		if ( empty( $attrs['report_name'] ) ) {
			return self::return_error_if_permission( esc_html__( 'report_name cannot be empty.', 'mode-analytics' ) );
		}

		$reports        = get_option( Settings::REPORTS_OPTION, array() );
		$matched_report = false;
		foreach ( $reports as $report ) {
			if ( $report['report_name'] === $attrs['report_name'] ) {
				$matched_report = $report;
				break;
			}
		}
		if ( empty( $matched_report ) ) {
			return self::return_error_if_permission( esc_html__( 'No report found for report_name.', 'mode-analytics' ) );
		}

		$report_url    = $matched_report['report_url'];
		$original_path = parse_url( $report_url, PHP_URL_PATH );
		$test_path     = rtrim( $original_path, '/' );
		if ( '/embed' !== substr( $test_path, -6 ) ) {
			$report_url = str_replace( $original_path, $test_path . '/embed', $report_url );
		}

		$access_key    = get_option( 'mode_analytics_access_key', '' );
		$access_secret = get_option( 'mode_analytics_access_secret', '' );
		$timestamp     = time();

		$query_args = array();
		if ( 'white-label' === $matched_report['embed_type'] ) {
			$query_args['access_key'] = $access_key;
			$query_args['timestamp']  = $timestamp;
		}

		if ( isset( $matched_report['max_age'] )
			&& '' !== $matched_report['max_age'] ) {
			$query_args['max_age'] = (int) $matched_report['max_age'];
		}

		$editable_params = array();
		$tokens          = array();
		if ( is_user_logged_in() ) {
			$user_tokens = get_user_meta( get_current_user_id(), Settings::TOKENS_OPTION, true );
			if ( $user_tokens ) {
				foreach ( $user_tokens as $user_token ) {
					$tokens[ $user_token['token'] ] = $user_token['value'];
				}
			}
		}
		if ( ! empty( $matched_report['parameters'] ) ) {
			foreach ( $matched_report['parameters'] as $parameter ) {
				if ( empty( $parameter['slug'] ) ) {
					continue;
				}
				if ( ! empty( $tokens ) ) {
					$parameter['default_value'] = str_replace( array_keys( $tokens ), array_values( $tokens ), $parameter['default_value'] );
				}
				$user_edit          = ! empty( $parameter['user_edit'] ) && 'on' === $parameter['user_edit'];
				$key                = 'param_' . $parameter['slug'];
				$param_value        = isset( $_GET[ $key ] ) && $user_edit ? $_GET[ $key ] : $parameter['default_value'];
				$query_args[ $key ] = rawurlencode( $param_value );
				if ( $user_edit ) {
					$editable_params[] = array(
						'slug'  => $parameter['slug'],
						'value' => $param_value,
						'type'  => $parameter['data_type'],
					);
				}
			}
		}

		// White-label query args need to be sorted, so might as well always sort.
		ksort( $query_args, SORT_STRING );
		$report_url = add_query_arg( $query_args, $report_url );
		// Create a signature for the white-label embed.
		if ( 'white-label' === $matched_report['embed_type'] ) {
			// 1B2M2Y8AsgTpgAmY7PhCfg== is md5() of empty string.
			$request_string = 'GET,,1B2M2Y8AsgTpgAmY7PhCfg==,' . $report_url . ',' . $timestamp;
			$signature      = hash_hmac( 'sha256', $request_string, $access_secret, false );
			$report_url     = add_query_arg( 'signature', $signature, $report_url );
		}
		$embed_code   = sprintf(
			'<a href="%s" class="mode-embed">Mode Analytics</a><script src="https://modeanalytics.com/embed/embed.js"></script>',
			esc_url( $report_url )
		);
		$user_form    = self::get_user_form( $editable_params );
		$user_buttons = self::get_user_buttons( $matched_report, $access_key );
		return $user_form . PHP_EOL . $user_buttons . PHP_EOL . $embed_code;
	}

	/**
	 * Return an error if the user can see it, empty string otherwise.
	 *
	 * @param string $message Error message.
	 * @return string
	 */
	private static function return_error_if_permission( $message ) {
		if ( current_user_can( 'edit_posts' ) ) {
			// translators: Shortcode configuration error.
			return sprintf( esc_html__( 'Mode Analytics configuration error: %s', 'mode-analytics' ), $message );
		}
		return '';
	}

	/**
	 * Get form HTML so the user can make changes to values
	 *
	 * @param array $editable_params Editable parameters.
	 * @return string
	 */
	private static function get_user_form( $editable_params ) {
		if ( empty( $editable_params ) ) {
			return '';
		}

		$form  = '<form class="mode-analytics-parameters">';
		$form .= '<h3>' . esc_html__( 'Parameters', 'mode-analytics' ) . '</h3>';
		foreach ( $editable_params as $parameter ) {
			$name  = 'param_' . $parameter['slug'];
			$form .= '<label for="' . esc_attr( $name ) . '"'
				. ' style="display: inline-block; width:auto;">'
				. esc_html( $parameter['slug'] ) . ' '
				. '<input type="' . esc_attr( $parameter['type'] ) . '"'
				. ' name="' . esc_attr( $name ) . '"'
				. ' value="' . esc_attr( $parameter['value'] ) . '"'
				. ' style="display: inline-block; width:auto;" />'
				. '</label> ';
		}
		$form .= '<input type="submit" value="' . esc_attr__( 'Run', 'mode-analytics' ) . '">';
		$form .= '</form>';
		return $form;
	}

	/**
	 * Get the extra buttons a user can use for white-label reports.
	 *
	 * @param array  $report Report to view.
	 * @param string $token  Token for the report.
	 * @return string
	 */
	private static function get_user_buttons( $report, $token ) {
		if ( 'white-label' !== $report['embed_type'] ) {
			return '';
		}
		$buttons = '<div class="button-group">';
		if ( ! empty( $report['show_export_csv'] ) && 'on' === $report['show_export_csv'] ) {
			$link_id  = md5( 'csv-export-' . mt_rand() );
			$buttons .= '<a href="#" id="' . esc_attr( $link_id ) . '" class="button">' . esc_html__( 'Export CSV', 'mode-analytics' ) . '</a>';
			$buttons .= <<<EOT
<script>
  window.addEventListener('message', function (e) {
    // always check the origin and make sure it is from modeanalytics.com
    if (e.origin === 'https://modeanalytics.com') {
      if (e.data['type'] == 'reportExportPaths') {
       var modeBaseUrl = e.origin
       var csvExportUrl = e.data['report_csv_export_path']
       csvExportLink = document.getElementById('{$link_id}')
       csvExportLink.href = modeBaseUrl + csvExportUrl
      }
    }
 })
</script>
EOT;
			$buttons .= ' ';
		}
		if ( ! empty( $report['show_filters'] ) && 'on' === $report['show_filters'] ) {
			preg_match( '#modeanalytics\.com/[^/]+/reports/([^/]+)#', $report['report_url'], $matches );
			$report_id = ! empty( $matches[1] ) ? $matches[1] : '';
			$buttons  .= '<a href="#" onclick="event.preventDefault();toggleFilterPanel(\'' . esc_attr( $report_id ) . '\')" class="button">' . esc_html__( 'Show/Hide Filters', 'mode-analytics' ) . '</a>';
			$buttons  .= <<<EOT
<script>
function toggleFilterPanel(r){window.document.querySelector('iframe[data-report="'+r+'"]').contentWindow.postMessage({type:'reportFilterPanelDisplay',wleFilterPanelToggle:true},'*');}; </script>
EOT;
			$buttons  .= ' ';
		}
		$buttons  = rtrim( $buttons, ' ' );
		$buttons .= '</div>';
		return $buttons;
	}

}
