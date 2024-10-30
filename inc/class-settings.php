<?php
/**
 * Controls the settings page.
 *
 * @package Mode_Analytics
 */

namespace Mode_Analytics;

/**
 * Controls the settings page.
 */
class Settings {

	/**
	 * Option name for the access key.
	 *
	 * @var string
	 */
	const ACCESS_KEY_OPTION = 'mode_analytics_access_key';

	/**
	 * Option name for the access secret.
	 *
	 * @var string
	 */
	const ACCESS_SECRET_OPTION = 'mode_analytics_access_secret';

	/**
	 * Capability required to access the settings page.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Parent page for the settings page.
	 *
	 * @var string
	 */
	const PAGE_BASE = 'options-general.php';

	/**
	 * Slug used for the settings page.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'mode-analytics';

	/**
	 * Option name for the reports.
	 *
	 * @var string
	 */
	const REPORTS_OPTION = 'mode_analytics_reports';

	/**
	 * Option name for the tokens.
	 *
	 * @var string
	 */
	const TOKENS_OPTION = 'mode_analytics_tokens';

	/**
	 * Group used for all registered settings.
	 *
	 * @var string
	 */
	const SETTINGS_GROUP = 'mode_analytics';

	/**
	 * Section used for authentication settings.
	 *
	 * @var string
	 */
	const SETTINGS_SECTION_AUTH = 'mode-analytics-auth';

	/**
	 * Section used for report settings.
	 *
	 * @var string
	 */
	const SETTINGS_SECTION_REPORTS = 'mode-analytics-reports';

	/**
	 * Register our settings with WordPress.
	 */
	public static function action_admin_init() {
		register_setting( self::SETTINGS_GROUP, self::ACCESS_KEY_OPTION );
		register_setting( self::SETTINGS_GROUP, self::ACCESS_SECRET_OPTION );
		register_setting( self::SETTINGS_GROUP, self::REPORTS_OPTION );
	}

	/**
	 * Register the settings menu with WordPress.
	 */
	public static function action_admin_menu() {
		$page_title = __( 'Mode Analytics', 'mode-analytics' );
		add_submenu_page( self::PAGE_BASE, $page_title, $page_title, self::CAPABILITY, self::PAGE_SLUG, array( __CLASS__, 'render_settings_menu' ) );
	}

	/**
	 * Render the settings menu.
	 */
	public static function render_settings_menu() {
		add_settings_section(
			self::SETTINGS_SECTION_AUTH,
			__( 'Authentication', 'mode-analytics' ),
			false,
			self::PAGE_SLUG
		);
		add_settings_field(
			self::ACCESS_KEY_OPTION,
			__( 'Access Key', 'mode-analytics' ),
			array( __CLASS__, 'render_input_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_AUTH,
			array(
				'name'        => self::ACCESS_KEY_OPTION,
				'description' => __( 'Only required if white label embeds are used.', 'mode-analytics' ),
			)
		);
		add_settings_field(
			self::ACCESS_SECRET_OPTION,
			__( 'Access Secret', 'mode-analytics' ),
			array( __CLASS__, 'render_input_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_AUTH,
			array(
				'name'        => self::ACCESS_SECRET_OPTION,
				'description' => __( 'Only required if white label embeds are used.', 'mode-analytics' ),
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Mode Analytics Settings', 'mode-analytics' ); ?></h1>
			<form method="POST" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php
					settings_fields( self::SETTINGS_GROUP );
					do_settings_sections( self::PAGE_SLUG );
				?>
				<h2><?php esc_html_e( 'Reports', 'mode-analytics' ); ?></h2>
				<table class="form-table">
					<tbody>
						<tr>
							<td>
								<?php self::render_reports_field(); ?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the form for users to manage custom tokens.
	 *
	 * @param WP_User $user User being edited.
	 */
	public static function action_edit_user_profile( $user ) {
		if ( ! self::current_user_can_edit_tokens() ) {
			return;
		}
		?>
		<h2><?php esc_html_e( 'Mode Analytics', 'mode-analytics' ); ?></h2>
		<style>
			.button.mode-analytics-remove-button {
				margin-top: 17px;
			}
		</style>
		<p class="description"><?php esc_html_e( 'Add one or more tokens to dynamically change report output.', 'mode-analytics' ); ?></p>
		<table class="form-table" id="mode-analytics-tokens" style="max-width: 600px">
			<tbody>
			<?php
			$index  = 0;
			$tokens = get_user_meta( $user->ID, self::TOKENS_OPTION, true );
			if ( ! empty( $tokens ) ) {
				foreach ( $tokens as $token ) {
					$index++;
					if ( empty( $token['token'] ) ) {
						continue;
					}
					$token['index'] = $index;
					self::render_tokens_table_row( $token );
				}
			}
			?>
			</tbody>
		</table>
		<?php wp_nonce_field( 'mode-nonce-' . $user->ID, 'mode-nonce' ); ?>
		<a id="mode-analytics-add-token" href="#" disabled data-template="mode-analytics-token-table-row" class="button"><?php esc_html_e( 'Add Token', 'mode-analytics' ); ?></a>
		<script type="text/html" id="tmpl-mode-analytics-token-table-row">
			<?php self::render_tokens_table_row(); ?>
		</script>
		<?php
	}

	/**
	 * Save Mode analytics tokens when form is submitted.
	 *
	 * @param integer $user_id User being saved.
	 */
	public static function action_edit_user_profile_update( $user_id ) {
		if ( empty( $_POST['mode-nonce'] )
			|| ! wp_verify_nonce( $_POST['mode-nonce'], 'mode-nonce-' . $user_id ) ) {
			return;
		}
		if ( ! self::current_user_can_edit_tokens() ) {
			return;
		}
		$tokens = array();
		if ( ! empty( $_POST[ self::TOKENS_OPTION ] ) ) {
			foreach ( $_POST[ self::TOKENS_OPTION ] as $raw_token ) {
				$tokens[] = array(
					'token' => sanitize_text_field( $raw_token['token'] ),
					'value' => sanitize_text_field( $raw_token['value'] ),
				);
			}
		}
		update_user_meta( $user_id, self::TOKENS_OPTION, $tokens );
	}

	/**
	 * Whether or not the current user can edit tokens.
	 *
	 * @return boolean
	 */
	private static function current_user_can_edit_tokens() {
		return current_user_can( 'edit_users' );
	}

	/**
	 * Render an input field.
	 *
	 * @param array $args Configuration arguments used by the input field.
	 */
	public static function render_input_field( $args ) {
		$defaults = array(
			'type' => 'text',
			'name' => '',
		);
		$args     = array_merge( $defaults, $args );
		if ( empty( $args['name'] ) ) {
			return;
		}
		$value = get_option( $args['name'] );
		?>
		<input type="<?php echo esc_attr( $args['type'] ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		/>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p><?php echo esc_html( $args['description'] ); ?></p>
		<?php
		endif;
	}

	/**
	 * Render the reports field.
	 */
	public static function render_reports_field() {
		$reports = get_option( self::REPORTS_OPTION );
		if ( ! is_array( $reports ) ) {
			$reports = array();
		}
		?>
		<style>
			#mode-analytics-reports {
				border-collapse: collapse;
			}
			#mode-analytics-reports th:nth-child(1),
			#mode-analytics-reports .mode-analytics-report-row td:nth-child(1),
			#mode-analytics-reports th:nth-child(1) input,
			#mode-analytics-reports .mode-analytics-report-row td:nth-child(1) input {
				max-width: 150px;
			}
			#mode-analytics-reports th:nth-child(4),
			#mode-analytics-reports .mode-analytics-report-row td:nth-child(4),
			#mode-analytics-reports th:nth-child(4) input,
			#mode-analytics-reports .mode-analytics-report-row td:nth-child(4) input {
				max-width: 100px;
			}
			#mode-analytics-reports tr {
				background-color: white;
			}
			#mode-analytics-reports .mode-analytics-heading-row th {
				width: auto;
				padding-bottom: 5px;
				padding-left: 10px;
			}
			#mode-analytics-reports .mode-analytics-parameter-row {
				border-bottom: 10px solid #f1f1f1;
			}
			#mode-analytics-reports .mode-analytics-report-buttons .inner {
				visibility: hidden;
			}
			#mode-analytics-reports .mode-analytics-add-parameter {
				margin-top: 5px;
			}
			#mode-analytics-reports .mode-analytics-report-buttons[data-embed-type="white-label"] .inner {
				visibility: visible;
			}
		</style>
		<table id="mode-analytics-reports">
			<tbody class="mode-analytics-reports-list">
				<?php
				$index = 0;
				foreach ( $reports as $report ) {
					if ( self::is_empty_report( $report ) ) {
						continue;
					}
					$index++;
					$report['index'] = $index;
					self::render_reports_table_row( $report );
				}
				?>
			</tbody>
		</table>
		<a id="mode-analytics-add-report" href="#" disabled data-template="mode-analytics-report-table-row" class="button"><?php esc_html_e( 'Add Report', 'mode-analytics' ); ?></a>
		<script type="text/html" id="tmpl-mode-analytics-report-table-row">
			<?php self::render_reports_table_row(); ?>
		</script>
		<script type="text/html" id="tmpl-mode-analytics-report-parameter-row">
			<?php self::render_reports_parameter_row(); ?>
		</script>
		<?php
	}

	/**
	 * Renders a row in the reports table.
	 *
	 * @param array $args Arguments to pass to the rendered table.
	 */
	private static function render_reports_table_row( $args = array() ) {
		$defaults    = array(
			'report_name' => '',
			'report_url'  => '',
			'embed_type'  => 'white-label',
			'max_age'     => '',
			'index'       => '%index%',
			'parameters'  => array(),
		);
		$args        = array_merge( $defaults, $args );
		$embed_types = array(
			'white-label' => 'White-Label',
			'internal'    => 'Mode Report',
		);
		?>
		<tr data-index="<?php echo esc_attr( $args['index'] ); ?>" class="mode-analytics-heading-row">
			<th><?php esc_html_e( 'Report Name', 'mode-analytics' ); ?></th>
			<th><?php esc_html_e( 'Report URL', 'mode-analytics' ); ?></th>
			<th><?php esc_html_e( 'Embed Type', 'mode-analytics' ); ?></th>
			<th><?php esc_html_e( 'Max Age', 'mode-analytics' ); ?></th>
			<th><?php esc_html_e( 'Buttons', 'mode-analytics' ); ?></th>
			<th></th>
		</tr>
		<tr data-index="<?php echo esc_attr( $args['index'] ); ?>" class="mode-analytics-report-row">
			<td>
				<input required type="text" placeholder="<?php esc_attr_e( 'Enter report name', 'mode-analytics' ); ?>" name="<?php echo esc_attr( self::REPORTS_OPTION . '[' . $args['index'] . '][report_name]' ); ?>" value="<?php echo esc_attr( $args['report_name'] ); ?>">
			</td>
			<td>
				<input required type="url" placeholder="<?php esc_attr_e( 'Enter a report URL', 'mode-analytics' ); ?>" name="<?php echo esc_attr( self::REPORTS_OPTION . '[' . $args['index'] . '][report_url]' ); ?>" value="<?php echo esc_attr( $args['report_url'] ); ?>">
			</td>
			<td>
				<select class="mode-analytics-embed-type" name="<?php echo esc_attr( self::REPORTS_OPTION . '[' . $args['index'] . '][embed_type]' ); ?>">
				<?php foreach ( $embed_types as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $args['embed_type'] ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input required type="number" placeholder="<?php esc_attr_e( 'Choose a max age', 'mode-analytics' ); ?>" name="<?php echo esc_attr( self::REPORTS_OPTION . '[' . $args['index'] . '][max_age]' ); ?>" value="<?php echo esc_attr( $args['max_age'] ); ?>">
			</td>
			<td class="mode-analytics-report-buttons" data-embed-type="<?php echo esc_attr( $args['embed_type'] ); ?>">
				<div class="inner">
					<?php
					$name = self::REPORTS_OPTION . '[' . $args['index'] . '][show_filters]';
					?>
					<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="on" <?php checked( $args['show_filters'], 'on' ); ?>> <?php esc_html_e( 'Show/Hide Filters', 'mode-analytics' ); ?></label><br />
					<?php
					$name = self::REPORTS_OPTION . '[' . $args['index'] . '][show_export_csv]';
					?>
					<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="on" <?php checked( $args['show_export_csv'], 'on' ); ?>> <?php esc_html_e( 'Export CSV', 'mode-analytics' ); ?></label>
				</div>
			</td>
			<td>
				<a class="button mode-analytics-remove-button" href="#"><?php esc_html_e( 'Remove Report', 'mode-analytics' ); ?></a>
			</td>
		</tr>
		<tr data-index="<?php echo esc_attr( $args['index'] ); ?>" class="mode-analytics-parameter-row">
			<td colspan="6">
				<div><?php esc_html_e( 'Parameters:', 'mode-analytics' ); ?></div>
				<table class="mode-analytics-report-parameters">
					<tbody>
						<?php
						if ( ! empty( $args['parameters'] ) ) {
							$index = 0;
							foreach ( $args['parameters']as $parameter ) {
								if ( self::is_empty_parameter( $parameter ) ) {
									continue;
								}
								$index++;
								$parameter['index']        = $index;
								$parameter['parent_index'] = $args['index'];
								self::render_reports_parameter_row( $parameter );
							}
						}
						?>
					</tbody>
				</table>
				<a class="button mode-analytics-add-parameter" data-template="mode-analytics-report-parameter-row" href="#"><?php esc_html_e( 'Add Parameter', 'mode-analytics' ); ?></a>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renders a parameter row in the reports row/
	 *
	 * @param array $args Individal parameter arguments.
	 */
	private static function render_reports_parameter_row( $args = array() ) {
		$defaults   = array(
			'slug'          => '',
			'data_type'     => '',
			'user_edit'     => 0,
			'default_value' => '',
			'parent_index'  => '%parent_index%',
			'index'         => '%index%',
		);
		$args       = array_merge( $defaults, $args );
		$data_types = array(
			'text'   => esc_html__( 'Text', 'mode-analytics' ),
			'number' => esc_html__( 'Number', 'mode-analytics' ),
			'date'   => esc_html__( 'Date', 'mode-analytics' ),
		);
		?>
		<tr data-index="<?php echo esc_attr( $args['index'] ); ?>">
			<td>
				<?php
				$name = self::REPORTS_OPTION . '[' . $args['parent_index'] . '][parameters][' . $args['index'] . '][slug]';
				?>
				<label><?php esc_html_e( 'Slug', 'mode-analytics' ); ?> <input type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $args['slug'] ); ?>"></label>
			</td>
			<td>
				<?php
				$name = self::REPORTS_OPTION . '[' . $args['parent_index'] . '][parameters][' . $args['index'] . '][data_type]';
				?>
				<label><?php esc_html_e( 'Data Type', 'mode-analytics' ); ?>
				<select class="mode-analytics-data-type" name="<?php echo esc_attr( $name ); ?>">
				<?php foreach ( $data_types as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $args['data_type'] ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php endforeach; ?>
				</select></label>
			</td>
			<td>
				<?php
				$name = self::REPORTS_OPTION . '[' . $args['parent_index'] . '][parameters][' . $args['index'] . '][user_edit]';
				?>
				<label><?php esc_html_e( 'Users Can Edit', 'mode-analytics' ); ?> <input type="checkbox" name="<?php echo esc_attr( $name ); ?>" <?php checked( 'on', $args['user_edit'] ); ?>></label>
			</td>
			<td>
				<?php
				$name = self::REPORTS_OPTION . '[' . $args['parent_index'] . '][parameters][' . $args['index'] . '][default_value]';
				$type = ! empty( $args['data_type'] ) ? $args['data_type'] : 'text';
				?>
				<label><?php esc_html_e( 'Default Value', 'mode-analytics' ); ?> <input class="mode-analytics-default-value" type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $args['default_value'] ); ?>"></label>
			</td>
			<td></td>
			<td>
				<a class="button mode-analytics-remove-button" href="#"><?php esc_html_e( 'Remove', 'mode-analytics' ); ?></a>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renders a row in the tokens table.
	 *
	 * @param array $args Arguments to pass to the rendered table.
	 */
	private static function render_tokens_table_row( $args = array() ) {
		$defaults = array(
			'token' => '',
			'value' => '',
			'index' => '%index%',
		);
		$args     = array_merge( $defaults, $args );
		?>
		<tr data-index="<?php echo esc_attr( $args['index'] ); ?>" class="mode-analytics-token-row">
			<td>
				<label><strong><?php esc_html_e( 'Token Name', 'mode-analytics' ); ?></strong>
				<input required type="text" placeholder="<?php esc_attr_e( 'Token like {account_id}', 'mode-analytics' ); ?>" name="<?php echo esc_attr( self::TOKENS_OPTION . '[' . $args['index'] . '][token]' ); ?>" value="<?php echo esc_attr( $args['token'] ); ?>">
				</label>
			</td>
			<td>
				<label><strong><?php esc_html_e( 'Custom Value', 'mode-analytics' ); ?></strong>
				<input required type="text" placeholder="<?php esc_attr_e( 'Value to substitute', 'mode-analytics' ); ?>" name="<?php echo esc_attr( self::TOKENS_OPTION . '[' . $args['index'] . '][value]' ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>">
				</label>
			</td>
			<td>
				<a class="button mode-analytics-remove-button" href="#"><?php esc_html_e( 'Remove Token', 'mode-analytics' ); ?></a>
			</td>
		</tr>
		<?php
	}

	/**
	 * Whether a report is an empty report.
	 *
	 * @param array $report Report data.
	 * @return boolean
	 */
	private static function is_empty_report( $report ) {
		if ( ! empty( $report['report_name'] ) ) {
			return false;
		}
		if ( ! empty( $report['report_url'] ) ) {
			return false;
		}
		if ( ! empty( $report['max_age'] ) ) {
			return false;
		}
		return true;
	}


	/**
	 * Whether a parameter is an empty parameter.
	 *
	 * @param array $parameter Report data.
	 * @return boolean
	 */
	private static function is_empty_parameter( $parameter ) {
		if ( ! empty( $parameter['slug'] ) ) {
			return false;
		}
		return true;
	}

}
