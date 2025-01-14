<?php

namespace uncanny_learndash_reporting;

use ReflectionClass;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class AdminMenu
 *
 * @package uncanny_custom_reporting
 */
class ReportingAdminMenu extends Boot {

	/**
	 * Database Class
	 *
	 * @var \UCTINCAN\Database\Admin
	 */
	private static $tincan_database;

	/**
	 * Per page option
	 *
	 * @var int
	 */
	private static $tincan_opt_per_pages;

	/**
	 * Per page option
	 *
	 * @var array
	 */
	private static $template_data = array();

	/**
	 * Style CSS string
	 *
	 * @var string
	 */
	private static $tincan_show = '';

	/**
	 * Queried Groups
	 *
	 * @var array
	 */
	private static $groups_query;

	/**
	 * Current Group
	 *
	 * @var int
	 */
	private static $isolated_group = 0;

	/**
	 * XAPI Report Columns
	 *
	 * @var array
	 */
	private static $xapi_report_columns = array();

	/**
	 * Class constructor
	 */
	public function __construct() {
		self::$xapi_report_columns = array(
			'group'            => array(
				'label'   => esc_attr__( 'Group', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'user'             => array(
				'label'   => esc_attr__( 'User', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'course'           => array(
				'label'   => esc_attr__( 'Course', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'module'           => array(
				'label'   => esc_attr__( 'Module', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'question'         => array(
				'label'   => esc_attr__( 'Question', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'result'           => array(
				'label'   => esc_attr__( 'Result', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'score'            => array(
				'label'   => esc_attr__( 'Score', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'date_time'        => array(
				'label'   => esc_attr__( 'Date Time', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			),
			'choices'          => array(
				'label'   => esc_attr__( 'Choices', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => false,
				'value'   => false,
			),
			'correct_response' => array(
				'label'   => esc_attr__( 'Correct Response', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => false,
				'value'   => false,
			),
			'user_response'    => array(
				'label'   => esc_attr__( 'User Response', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => false,
				'value'   => false,
			),
		);
		// Setup Theme Options Page Menu in Admin
		if ( is_admin() ) {
			// TinCan CSV
			self::csv_export();
			add_action( 'admin_init', array( __CLASS__, 'tincan_change_per_page' ) );

			add_action( 'admin_menu', array( __CLASS__, 'register_options_menu_page' ), 10 );
			add_action( 'admin_init', array( __CLASS__, 'register_options_menu_page_settings' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'scripts' ), 99999 );
			// load columns settings from user meta.
			add_filter( 'screen_options_show_screen', '__return_true' );
			add_filter( 'screen_settings', array( $this, 'filter__screen_settings' ), 10, 2 );

			if ( 'uncanny-learnDash-reporting' === ultc_get_filter_var( 'page', '' ) ) {
				add_filter( 'set_screen_option_xapi_report_columns', array( $this, 'filter__set_screen_option' ), 10, 3 );
			}
		} else {
			self::csv_export();
			add_action( 'init', array( __CLASS__, 'tincan_change_per_page' ) );
			add_shortcode( 'tincanny', array( __CLASS__, 'frontend_tincanny' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'scripts' ) );
			add_action( 'init', array( __CLASS__, 'render_callback' ) );
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_REQUEST['paged'] ) ) {
				$_REQUEST['paged'] = explode( '/page/', sanitize_text_field( $_SERVER['REQUEST_URI'] ), 2 );
				if ( isset( $_REQUEST['paged'][1] ) ) {
					list( $_REQUEST['paged'], ) = explode( '/', sanitize_text_field( $_REQUEST['paged'][1] ), 2 );
				}
				if ( isset( $_REQUEST['paged'] ) && $_REQUEST['paged'] != '' ) {
					$_REQUEST['paged'] = intval( $_REQUEST['paged'] );
					if ( $_REQUEST['paged'] < 2 ) {
						$_REQUEST['paged'] = '';
					}
				} else {
					$_REQUEST['paged'] = '';
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}
	}

	private static function csv_export() {
		if ( 'csv' === ultc_get_filter_var( 'tc_filter_mode', '' ) ) {
			add_action( 'init', array( __CLASS__, 'execute_csv_export' ) );
		}
		if ( 'csv-xapi' === ultc_get_filter_var( 'tc_filter_mode', '' ) ) {
			add_action( 'init', array( __CLASS__, 'execute_csv_export_xapi' ) );
		}
	}

	public static function render_callback() {
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				'tincanny-learndash-reporting/frontend-course-reports',
				array(
					'render_callback' => array( __CLASS__, 'frontend_tincanny_block' ),
				)
			);
		}
	}

	/*
	* Render to Screen Options string.
	*/

	/**
	 * Create Plugin options menu
	 */
	public static function register_options_menu_page() {

		$page_title = esc_html_x( 'Tin Canny Reporting for LearnDash', 'uncanny-learndash-reporting', 'uncanny-learndash-reporting' );
		$menu_title = esc_html_x( 'Tin Canny Reporting', 'uncanny-learndash-reporting', 'uncanny-learndash-reporting' );

		$capability = apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' );

		$menu_slug = 'uncanny-learnDash-reporting';
		$function  = array( __CLASS__, 'options_menu_page_output' );

		$icon_url
			= 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDU4MSA2NDAiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDU4MSA2NDAiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTUyNi40IDM0LjFjMC42IDUgMSAxMC4xIDEuMyAxNS4xIDAuNSAxMC4zIDEuMiAyMC42IDAuOCAzMC45LTAuNSAxMS41LTEgMjMtMi4xIDM0LjQtMi42IDI2LjctNy44IDUzLjMtMTYuNSA3OC43LTcuMyAyMS4zLTE3LjEgNDEuOC0yOS45IDYwLjQtMTIgMTcuNS0yNi44IDMzLTQzLjggNDUuOS0xNy4yIDEzLTM2LjcgMjMtNTcuMSAyOS45LTI1LjEgOC41LTUxLjUgMTIuNy03Ny45IDEzLjggNzAuMyAyNS4zIDEwNi45IDEwMi44IDgxLjYgMTczLjEtMTguOSA1Mi42LTY4LjEgODguMS0xMjQgODkuNWgtNi4xYy0xMS4xLTAuMi0yMi4xLTEuOC0zMi45LTQuNy0yOS40LTcuOS01NS45LTI2LjMtNzMuNy01MC45LTI5LjItNDAuMi0zNC4xLTkzLjEtMTIuNi0xMzgtMjUgMjUuMS00NC41IDU1LjMtNTkuMSA4Ny40LTguOCAxOS43LTE2LjEgNDAuMS0yMC44IDYxLjEtMS4yLTE0LjMtMS4yLTI4LjYtMC42LTQyLjkgMS4zLTI2LjYgNS4xLTUzLjIgMTIuMi03OC45IDUuOC0yMS4yIDEzLjktNDEuOCAyNC43LTYwLjlzMjQuNC0zNi42IDQwLjYtNTEuM2MxNy4zLTE1LjcgMzcuMy0yOC4xIDU5LjEtMzYuOCAyNC41LTkuOSA1MC42LTE1LjIgNzYuOC0xNy4yIDEzLjMtMS4xIDI2LjctMC44IDQwLjEtMi4zIDI0LjUtMi40IDQ4LjgtOC40IDcxLjMtMTguMyAyMS05LjIgNDAuNC0yMS44IDU3LjUtMzcuMiAxNi41LTE0LjkgMzAuOC0zMi4xIDQyLjgtNTAuOCAxMy0yMC4yIDIzLjQtNDIuMSAzMS42LTY0LjcgNy42LTIxLjEgMTMuNC00Mi45IDE2LjctNjUuM3ptLTI3OS40IDMyOS41Yy0xOC42IDEuOC0zNi4yIDguOC01MC45IDIwLjQtMTcuMSAxMy40LTI5LjggMzIuMi0zNi4yIDUyLjktNy40IDIzLjktNi44IDQ5LjUgMS43IDczIDcuMSAxOS42IDE5LjkgMzcuMiAzNi44IDQ5LjYgMTQuMSAxMC41IDMwLjkgMTYuOSA0OC40IDE4LjZzMzUuMi0xLjYgNTEtOS40YzEzLjUtNi43IDI1LjQtMTYuMyAzNC44LTI4LjEgMTAuNi0xMy40IDE3LjktMjkgMjEuNS00NS43IDQuOC0yMi40IDIuOC00NS43LTUuOC02Ni45LTguMS0yMC0yMi4yLTM3LjYtNDAuMy00OS4zLTE4LTExLjctMzkuNS0xNy02MS0xNS4xeiIgZmlsbD0iIzgyODc4QyIvPjxwYXRoIGQ9Im0yNDIuNiA0MDIuNmM2LjItMS4zIDEyLjYtMS44IDE4LjktMS41LTExLjQgMTEuNC0xMi4yIDI5LjctMS44IDQyIDExLjIgMTMuMyAzMS4xIDE1LjEgNDQuNCAzLjkgNS4zLTQuNCA4LjktMTAuNCAxMC41LTE3LjEgMTIuNCAxNi44IDE2LjYgMzkuNCAxMSA1OS41LTUgMTguNS0xOCAzNC42LTM1IDQzLjUtMzQuNSAxOC4yLTc3LjMgNS4xLTk1LjUtMjkuNS0xLTItMi00LTIuOS02LjEtOC4xLTE5LjYtNi41LTQzIDQuMi02MS4zIDEwLTE3IDI2LjgtMjkuMiA0Ni4yLTMzLjR6IiBmaWxsPSIjODI4NzhDIi8+PC9zdmc+';

		$position = 81; // 81 - Above Settings Menu
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	}

	/*
	* Validate Screen Option on update.
	*/

	public static function register_options_menu_page_settings() {
		register_setting( 'uncanny_learndash_reporting-group', 'uncanny_reporting_active_classes' );
	}

	/*
	 * Whitelisted Options that are saved on the page
	 */

	/**
	 * Populates an array of classes in internal and external file in the classes folder
	 *
	 * @param mixed (Array || false) $external_classes
	 *
	 * @return array
	 */
	public static function get_available_classes() {

		$class_details = array();

		// loop file in classes folded and call get_details
		// check function exist first
		$path = dirname( __FILE__ ) . '/classes/';

		$files = scandir( $path );

		$internal_details = self::get_class_details( $path, $files, __NAMESPACE__ );

		$class_details = array_merge( $class_details, $internal_details );

		return $class_details;
	}

	private static function get_class_details( $path, $files, $name_space ) {

		$details = array();

		foreach ( $files as $file ) {
			if ( is_dir( $path . $file ) || '..' === $file || '.' === $file ) {
				continue;
			}

			//get class name
			$class_name = str_replace( '.php', '', $file );
			$class_name = str_replace( '-', ' ', $class_name );
			$class_name = ucwords( $class_name );
			$class_name = $name_space . '\\' . str_replace( ' ', '', $class_name );

			// test for required functions
			$class = new ReflectionClass( $class_name );
			if ( $class->implementsInterface( 'uncanny_learndash_reporting\RequiredFunctions' ) ) {
				$details[ $class_name ] = $class_name::get_details();
			} else {
				$details[ $class_name ] = false;
			}
		}

		return $details;

	}

	/*
	 * get_class_details
	 * @param string $path
	 * @param array $files
	 * @param string $namespace
	 *
	 * @return array $details
	 */

	public static function scripts( $hook ) {

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return;
		}
		if ( is_admin() ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : '';
		}

		if ( ! wp_script_is( 'wp-hooks', 'enqueued' ) ) {
			wp_enqueue_script( 'wp-hooks' );
		}

		global $post;

		$page_title = sanitize_title(
			esc_html__( 'Tin Canny Reporting', 'uncanny-learndash-reporting' )
		);

		$page_is_to_load_scripts = ( 'toplevel_page_uncanny-learnDash-reporting' === $hook || $page_title . '_page_snc_options' === $hook
			|| ( ! is_admin() && $post instanceof \WP_Post && has_shortcode( $post->post_content, 'tincanny' ) )
		);

		$page_is_to_load_scripts = apply_filters( 'tc_page_is_to_load_scripts', $page_is_to_load_scripts, $hook, $post );

		if ( $page_is_to_load_scripts ) {
			// Admin Page Reporting UI Files

			// Admin CSS
			// TODO add debug and minify
			wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), array(), UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), array(), UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'data-tables', Config::get_admin_css( 'datatables.min.css' ), array(), UNCANNY_REPORTING_VERSION );

			wp_register_style( 'reporting-admin', Config::get_admin_css( 'admin-style.css' ), array(), UNCANNY_REPORTING_VERSION );

			$dynamic_css = self::get_dynamic_css();
			wp_add_inline_style( 'reporting-admin', $dynamic_css );

			wp_enqueue_style( 'reporting-admin' );

			// Admin JS
			wp_register_script(
				'reporting_js_handle',
				Config::get_admin_js( 'reporting' ),
				array(
					'jquery',
					'wp-hooks',
					'wp-i18n',
				),
				UNCANNY_REPORTING_VERSION,
				true
			);

			// Add custom colors to use them in the JS
			$ui = self::get_ui_data();
			wp_localize_script( 'reporting_js_handle', 'TincannyUI', $ui );

			// Add Tin Canny data
			wp_localize_script( 'reporting_js_handle', 'TincannyData', self::get_script_data() );
			$isolated_group_id = absint( ultc_get_filter_var( 'group_id', 0 ) );

			// Get Tin Canny settings
			$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

			// API data
			$reporting_api_setup = array(
				'root'                          => esc_url_raw( rest_url() . 'uncanny_reporting/v1/' ),
				'nonce'                         => \wp_create_nonce( 'wp_rest' ),
				'learnDashLabels'               => ReportingApi::get_labels(),
				'isolated_group_id'             => $isolated_group_id,
				'isAdmin'                       => is_admin(),
				'editUsers'                     => current_user_can( 'edit_users' ),
				'localizedStrings'              => self::get_js_localized_strings(),
				'optimized_build'               => '1',
				'page'                          => 'reporting',
				'showTinCanTab'                 => isset( $tincanny_settings['tinCanActivation'] ) && 1 === (int) $tincanny_settings['tinCanActivation'] ? '1' : '0',
				'disablePerformanceEnhancments' => isset( $tincanny_settings['disablePerformanceEnhancments'] ) && 1 === (int) $tincanny_settings['disablePerformanceEnhancments'] ? '1' : '0',
				'userIdentifierDisplayName'     => isset( $tincanny_settings['userIdentifierDisplayName'] ) && 1 === (int) $tincanny_settings['userIdentifierDisplayName'] ? '1' : '0',
				'userIdentifierFirstName'       => isset( $tincanny_settings['userIdentifierFirstName'] ) && 1 === (int) $tincanny_settings['userIdentifierFirstName'] ? '1' : '0',
				'userIdentifierLastName'        => isset( $tincanny_settings['userIdentifierLastName'] ) && 1 === (int) $tincanny_settings['userIdentifierLastName'] ? '1' : '0',
				'userIdentifierUsername'        => isset( $tincanny_settings['userIdentifierUsername'] ) && 1 === (int) $tincanny_settings['userIdentifierUsername'] ? '1' : '0',
				'userIdentifierEmail'           => isset( $tincanny_settings['userIdentifierEmail'] ) && 1 === (int) $tincanny_settings['userIdentifierEmail'] ? '1' : '0',
				'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
				'base_url'                      => self::get_base_url(),
			);

			wp_localize_script( 'reporting_js_handle', 'reportingApiSetup', $reporting_api_setup );
			wp_enqueue_script( 'reporting_js_handle' );

			// TinCan
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), '1.8.2' );

			wp_enqueue_style( 'tincanny-admin-tincanny-report-tab', Config::get_gulp_css( 'admin.tincan.report.tab' ), array(), UNCANNY_REPORTING_VERSION );
			wp_enqueue_script(
				'tincanny-admin-tincanny-report-tab-js',
				Config::get_gulp_js( 'admin.tincan.report.tab' ),
				array(
					'jquery',
					'wp-hooks',
					'wp-i18n',
				),
				UNCANNY_REPORTING_VERSION,
				false
			);

		} elseif ( $page_title . '_page_manage-content' === $hook || $page_title . '_page_uncanny-reporting-license-activation' === $hook ) {
			wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), array(), UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), array(), UNCANNY_REPORTING_VERSION );

			wp_register_style( 'tclr-backend', Config::get_admin_css( 'admin-style.css' ), array(), UNCANNY_REPORTING_VERSION );

			$dynamic_css = self::get_dynamic_css();
			wp_add_inline_style( 'tclr-backend', $dynamic_css );

			wp_enqueue_style( 'tclr-backend' );

		} elseif ( ! empty( $screen ) && 'dashboard' === $screen->id ) {

			$disable_dash_widget = get_option( 'tincanny_disableDashWidget', 'no' );

			if ( 'no' === $disable_dash_widget ) {
				// WP Dashboard Reporting UI Files

				// Load Styles for WP Dashboard Reporting  page located in general plugin styles
				wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), array(), UNCANNY_REPORTING_VERSION );
				wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), array(), UNCANNY_REPORTING_VERSION );

				wp_register_style( 'reporting-admin', Config::get_admin_css( 'admin-style.css' ), array(), UNCANNY_REPORTING_VERSION );

				$dynamic_css = self::get_dynamic_css();
				wp_add_inline_style( 'reporting-admin', $dynamic_css );

				wp_enqueue_style( 'reporting-admin' );

				// Get Tin Canny settings
				$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

				wp_register_script(
					'reporting_js_handle',
					Config::get_admin_js( 'tc-reporting-dashboard' ),
					array(
						'jquery',
						'wp-hooks',
						'wp-i18n',
					),
					UNCANNY_REPORTING_VERSION,
					true
				);
				$reporting_api_setup = array(
					'root'             => esc_url_raw( rest_url() . 'uncanny_reporting/v1/' ),
					'nonce'            => \wp_create_nonce( 'wp_rest' ),
					'learnDashLabels'  => ReportingApi::get_labels(),
					'page'             => 'dashboard',
					'localizedStrings' => self::get_js_localized_strings(),
					'optimized_build'  => '1',
					'showTinCanTab'    => 1 === (int) $tincanny_settings['tinCanActivation'] ? '1' : '0',
				);

				// Add custom colors to use them in the JS
				$ui = self::get_ui_data();
				wp_localize_script( 'reporting_js_handle', 'TincannyUI', $ui );

				// Add Tin Canny data
				wp_localize_script( 'reporting_js_handle', 'TincannyData', self::get_script_data() );

				wp_localize_script( 'reporting_js_handle', 'reportingApiSetup', $reporting_api_setup );
				wp_enqueue_script( 'reporting_js_handle' );
			}
		}

		//wp_enqueue_script( 'tincanny-report-block', Config::get_gulp_js( 'tincanny-block' ), array('wp-blocks', 'wp-i18n', 'wp-element',), UNCANNY_REPORTING_VERSION );

	}

	/*
	 * Load Scripts
	 * @paras string $hook Admin page being loaded
	 */

	private static function get_dynamic_css() {
		// Get colors
		$ui = self::get_ui_data();

		// Start output
		ob_start();

		?>

		/* Main font */

		.tclr.wrap,
		.tclr-select2 .select2-dropdown {
		font-family: <?php echo esc_attr( $ui['mainFont'] ); ?>
		}

		/* Primary color */

		.reporting-dashboard-status--loading .reporting-dashboard-status__icon {
		background: <?php echo esc_attr( $ui['colors']['primary'] ); ?>;
		}

		.reporting-datatable__search .dataTables_filter input:focus {
		border-color: <?php echo esc_attr( $ui['colors']['primary'] ); ?>;
		}

		.reporting-table-see-details,
		.reporting-breadcrumbs-item__link {
		color: <?php echo esc_attr( $ui['colors']['primary'] ); ?>;
		}

		.reporting-single-course-progress-tabs__item.reporting-single-course-progress-tabs__item--selected {
		box-shadow: inset 3px 0 0 0 <?php echo esc_attr( $ui['colors']['primary'] ); ?>;
		}

		/* Secondary color */

		.reporting-dashboard-quick-links__icon {
		color: <?php echo esc_attr( $ui['colors']['secondary'] ); ?>;
		}

		/* Notice */

		.reporting-dashboard-status--warning .reporting-dashboard-status__icon {
		background: 
		<?php
		echo esc_attr( $ui['colors']['notice'] );
		?>
		;
		}

		<?php

		// Get output
		$dynamic_css = ob_get_clean();

		// Return output
		return $dynamic_css;
	}

	private static function get_script_data() {

		$roles = array();
		if ( is_user_logged_in() ) {
			$user  = wp_get_current_user();
			$roles = (array) $user->roles;
		}

		$administrator_view = false;
		if ( in_array( 'administrator', $roles, true ) ) {
			$administrator_view = true;
		}

		return array(
			'url'               => array(
				'updateData' => admin_url( 'admin.php?page=learndash_data_upgrades' ),
			),
			'i18n'              => self::get_i18n_strings(),
			'administratorView' => $administrator_view,
		);
	}

	private static function get_i18n_strings() {
		return array(
			'dataNotRight'    => __( 'Data not right?', 'uncanny-learndash-reporting' ),
			/* translators: %s is a link */
			'tryRunning'      => __( 'Try running the %s.', 'uncanny-learndash-reporting' ),
			'updatesLinkText' => __( 'LearnDash Data Upgrades', 'uncanny-learndash-reporting' ),
			'allQuestions'    => __( 'All Questions', 'uncanny-learndash-reporting' ),
			'dropdown'        => array(
				'errorLoading'    => _x( 'The results could not be loaded.', 'Dropdown', 'uncanny-learndash-reporting' ),
				'inputTooLong'    => array(
					'singular' => _x( 'Please delete 1 character', 'Dropdown', 'uncanny-learndash-reporting' ),
					/* translators: %s is a character count */
					'plural'   => _x( 'Please delete %s characters', 'Dropdown', 'uncanny-learndash-reporting' ),
				),
				/* translators: %s is a character count */
				'inputTooShort'   => _x( 'Please enter %s or more characters', 'Dropdown', 'uncanny-learndash-reporting' ),
				'loadingMore'     => _x( 'Loading more results...', 'Dropdown', 'uncanny-learndash-reporting' ),
				'maximumSelected' => array(
					'singular' => _x( 'You can only select 1 item', 'Dropdown', 'uncanny-learndash-reporting' ),
					/* translators: %s is a number of items */
					'plural'   => _x( 'You can only select %s items', 'Dropdown', 'uncanny-learndash-reporting' ),
				),
				'noResults'       => _x( 'No results found', 'Dropdown', 'uncanny-learndash-reporting' ),
				'searching'       => _x( 'Searching...', 'Dropdown', 'uncanny-learndash-reporting' ),
				'removeAllItems'  => _x( 'Remove all items', 'Dropdown', 'uncanny-learndash-reporting' ),
			),
			'tables'          => array(
				'processing'        => _x( 'Processing...', 'Table', 'uncanny-learndash-reporting' ),
				'sSearch'           => _x( 'Search', 'Table', 'uncanny-learndash-reporting' ),
				'searchPlaceholder' => _x( 'Search', 'Table', 'uncanny-learndash-reporting' ),
				/* translators: %s is a number */
				'lengthMenu'        => sprintf( _x( 'Show %s entries', 'Table', 'uncanny-learndash-reporting' ), '_MENU_' ),
				/* translators: Both %1$s and %2$s are numbers */
				'info'              => sprintf( _x( 'Showing page %1$s of %2$s', 'Table', 'uncanny-learndash-reporting' ), '_PAGE_', '_PAGES_' ),
				'infoEmpty'         => _x( 'Showing 0 to 0 of 0 entries', 'Table', 'uncanny-learndash-reporting' ),
				/* translators: %s is a number */
				'infoFiltered'      => sprintf( _x( '(filtered from %s total entries)', 'Table', 'uncanny-learndash-reporting' ), '_MAX_' ),
				'loadingRecords'    => _x( 'Loading', 'Table', 'uncanny-learndash-reporting' ),
				'zeroRecords'       => _x( 'No matching records found', 'Table', 'uncanny-learndash-reporting' ),
				'emptyTable'        => _x( 'No data available in table', 'Table', 'uncanny-learndash-reporting' ),
				'paginate'          => array(
					/* translators: Table pagination */
					'first'    => _x( 'First', 'Table', 'uncanny-learndash-reporting' ),
					/* translators: Table pagination */
					'previous' => _x( 'Previous', 'Table', 'uncanny-learndash-reporting' ),
					/* translators: Table pagination */
					'next'     => _x( 'Next', 'Table', 'uncanny-learndash-reporting' ),
					/* translators: Table pagination */
					'last'     => _x( 'Last', 'Table', 'uncanny-learndash-reporting' ),
				),
				'sortAscending'     => _x( ': activate to sort column ascending', 'Table', 'uncanny-learndash-reporting' ),
				'sortDescending'    => _x( ': activate to sort column descending', 'Table', 'uncanny-learndash-reporting' ),
				'buttons'           => array(
					/* translators: Table button */
					'csvExport' => _x( 'CSV export', 'Table', 'uncanny-learndash-reporting' ),
					/* translators: Table button */
					'pdfExport' => _x( 'PDF export', 'Table', 'uncanny-learndash-reporting' ),
				),
			),
		);
	}

	private static function get_ui_data() {
		// Define default font
		$font = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';

		// Get colors from DB
		$primary   = '';
		$secondary = '';
		$notice    = '';

		$not_started = '';
		$in_progress = '';
		$completed   = '';

		// Define default values
		$primary   = empty( $primary ) ? '#0290c2' : $primary;
		$secondary = empty( $secondary ) ? '#d52c82' : $secondary;
		$notice    = empty( $notice ) ? '#f5ba05' : $notice;

		$completed   = empty( $completed ) ? '#02c219' : $completed;
		$in_progress = empty( $in_progress ) ? '#FF9E01' : $in_progress;
		$not_started = empty( $not_started ) ? '#e3e3e3' : $not_started;

		return apply_filters(
			'tincanny_ui',
			array(
				'mainFont' => $font,
				'colors'   => array(
					'primary'   => $primary,
					'secondary' => $secondary,
					'notice'    => $notice,
					'status'    => array(
						'completed'  => $completed,
						'inProgress' => $in_progress,
						'notStarted' => $not_started,
					),
				),
				'show'     => array(
					'tinCanData' => 'no' !== get_option( 'show_tincan_reporting_tables' ),
				),
			)
		);
	}

	private static function get_js_localized_strings() {

		// listed as they appear in file order under \plugins\tin-canny-learndash-reporting\src\assets\admin\js\scripts\components\*.js
		return array(
			// charts.js
			'In Progress'                                 => __( 'In Progress', 'uncanny-learndash-reporting' ),
			'Completed'                                   => __( 'Completed', 'uncanny-learndash-reporting' ),
			'Not Started'                                 => __( 'Not Started', 'uncanny-learndash-reporting' ),
			'No Data'                                     => __( 'No Data', 'uncanny-learndash-reporting' ),
			// config.js
			'Are you sure you want to permanently delete all Tin Can data? This cannot be undone.' => __( 'Are you sure you want to permanently delete all Tin Can data? This cannot be undone.', 'uncanny-learndash-reporting' ),
			'Are you sure you want to permanently delete all Bookmark data? This cannot be undone.' => __( 'Are you sure you want to permanently delete all Bookmark data? This cannot be undone.', 'uncanny-learndash-reporting' ),
			// data-object.js
			'%'                                           => __( '%', 'uncanny-learndash-reporting' ),
			// query-string.js
			'Please select your criteria to filter the Tin Can data.' => __( 'Please select your criteria to filter the Tin Can data.', 'uncanny-learndash-reporting' ),
			'Please select your criteria to filter the xAPI Quiz data.' => __( 'Please select your criteria to filter the xAPI Quiz data.', 'uncanny-learndash-reporting' ),
			// tables.js
			'Enrolled'                                    => __( 'Enrolled', 'uncanny-learndash-reporting' ),
			'Avg Time to Complete'                        => __( 'Avg Time to Complete', 'uncanny-learndash-reporting' ),
			'Avg Time Spent'                              => __( 'Avg Time Spent', 'uncanny-learndash-reporting' ),
			'% Complete'                                  => __( '% Complete', 'uncanny-learndash-reporting' ),
			'Certificate Link'                            => __( 'Certificate Link', 'uncanny-learndash-reporting' ),
			'Users Enrolled'                              => __( 'Users Enrolled', 'uncanny-learndash-reporting' ),
			'Average Time to Complete'                    => __( 'Average Time to Complete', 'uncanny-learndash-reporting' ),
			'Average '                                    => __( 'Average ', 'uncanny-learndash-reporting' ),
			'Display Name'                                => __( 'Name', 'uncanny-learndash-reporting' ),
			'First Name'                                  => __( 'First Name', 'uncanny-learndash-reporting' ),
			'Last Name'                                   => __( 'Last Name', 'uncanny-learndash-reporting' ),
			'Username'                                    => __( 'Username', 'uncanny-learndash-reporting' ),
			'Email Address'                               => __( 'Email Address', 'uncanny-learndash-reporting' ),
			'Course Enrolled'                             => __( 'Course Enrolled', 'uncanny-learndash-reporting' ),
			/* translators: %s is the "Course" label */
			'Quiz Average'                                => sprintf( __( '%s Average', 'uncanny-learndash-reporting' ), \LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'Completion Date'                             => __( 'Completion Date', 'uncanny-learndash-reporting' ),
			'Time to Complete'                            => __( 'Time to Complete', 'uncanny-learndash-reporting' ),
			'Time Spent'                                  => __( 'Time Spent', 'uncanny-learndash-reporting' ),
			'Status'                                      => __( 'Status', 'uncanny-learndash-reporting' ),
			/* translators: %s is the "Lesson" label */
			'Associated Lesson'                           => sprintf( __( 'Associated %s', 'uncanny-learndash-reporting' ), \LearnDash_Custom_Label::get_label( 'lesson' ) ),
			'Name'                                        => __( 'Name', 'uncanny-learndash-reporting' ),
			'Date Completed'                              => __( 'Date Completed', 'uncanny-learndash-reporting' ),
			'Assignment Name'                             => __( 'Assignment Name', 'uncanny-learndash-reporting' ),
			'Approval'                                    => __( 'Approval', 'uncanny-learndash-reporting' ),
			'Submitted On'                                => __( 'Submitted On', 'uncanny-learndash-reporting' ),
			'Module'                                      => __( 'Module', 'uncanny-learndash-reporting' ),
			'Target'                                      => __( 'Target', 'uncanny-learndash-reporting' ),
			'Action'                                      => __( 'Action', 'uncanny-learndash-reporting' ),
			'Result'                                      => __( 'Result', 'uncanny-learndash-reporting' ),
			'Date'                                        => __( 'Date', 'uncanny-learndash-reporting' ),
			'There are no activities to report.'          => __( 'There are no activities to report.', 'uncanny-learndash-reporting' ),
			'CSV Export'                                  => __( 'CSV Export', 'uncanny-learndash-reporting' ),
			'Excel Export'                                => __( 'Excel Export', 'uncanny-learndash-reporting' ),
			/* translators: %s is the course label */
			'missingTitleCourseId'                        => _x( '%s ID: ', '%s is the "Course" label', 'uncanny-learndash-reporting' ),
			'Not Complete'                                => __( 'Not Complete', 'uncanny-learndash-reporting' ),
			'Not Approved'                                => __( 'Not Approved', 'uncanny-learndash-reporting' ),
			'Approved'                                    => __( 'Approved', 'uncanny-learndash-reporting' ),
			'Page'                                        => __( 'Page', 'uncanny-learndash-reporting' ),
			'View'                                        => __( 'View', 'uncanny-learndash-reporting' ),
			// tabs.js
			' Summary'                                    => __( ' Summary', 'uncanny-learndash-reporting' ),
			'< Users Overview'                            => __( '< Users Overview', 'uncanny-learndash-reporting' ),
			'< User Overview'                             => __( '< User Overview', 'uncanny-learndash-reporting' ),
			'< Users '                                    => __( '< User\'s ', 'uncanny-learndash-reporting' ),
			' Overview'                                   => __( ' Overview', 'uncanny-learndash-reporting' ),
			'Showing _START_ to _END_ of _TOTAL_ entries' => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'uncanny-learndash-reporting' ),
			'Showing 0 to 0 of 0 entries'                 => __( 'Showing 0 to 0 of 0 entries', 'uncanny-learndash-reporting' ),
			'(filtered from _MAX_ total entries)'         => __( '(filtered from _MAX_ total entries)', 'uncanny-learndash-reporting' ),
			'Show _MENU_ entries'                         => __( 'Show _MENU_ entries', 'uncanny-learndash-reporting' ),
			'Loading...'                                  => __( 'Loading...', 'uncanny-learndash-reporting' ),
			'Processing...'                               => __( 'Processing...', 'uncanny-learndash-reporting' ),
			'Search:'                                     => __( 'Search:', 'uncanny-learndash-reporting' ),
			'No matching records found'                   => __( 'No matching records found', 'uncanny-learndash-reporting' ),
			'First'                                       => __( 'First', 'uncanny-learndash-reporting' ),
			'Last'                                        => __( 'Last', 'uncanny-learndash-reporting' ),
			'Next'                                        => __( 'Next', 'uncanny-learndash-reporting' ),
			'Previous'                                    => __( 'Previous', 'uncanny-learndash-reporting' ),
			': activate to sort column ascending'         => __( ': activate to sort column ascending', 'uncanny-learndash-reporting' ),
			': activate to sort column descending'        => __( ': activate to sort column descending', 'uncanny-learndash-reporting' ),
			'Detailed Report'                             => __( 'Detailed Report', 'uncanny-learndash-reporting' ),
			'Score'                                       => __( 'Score', 'uncanny-learndash-reporting' ),
			'Order'                                       => __( 'Order', 'uncanny-learndash-reporting' ),
			/* translators: %s is the quiz title */
			'tablesColumnsAvgQuizScore'                   => _x( 'Avg %s Score', '%s is the "Quiz" label', 'uncanny-learndash-reporting' ),
			/* translators: %s is the course title */
			'tablesColumnsCoursesEnrolled'                => _x( '%s Enrolled', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			/* translators: %s is the lesson title */
			'tablesColumnsLessonName'                     => _x( '%s Name', '%s is the "Lesson" label', 'uncanny-learndash-reporting' ),
			/* translators: %s is the topic title */
			'tablesColumnsTopicName'                      => _x( '%s Name', '%s is the "Topic" label', 'uncanny-learndash-reporting' ),
			/* translators: %s is the quiz title */
			'tablesColumnsQuizName'                       => _x( '%s Name', '%s is the "Quiz" label', 'uncanny-learndash-reporting' ),
			'tablesColumnsDetails'                        => __( 'Details', 'uncanny-learndash-reporting' ),
			'tablesButtonSeeDetails'                      => __( 'See details', 'uncanny-learndash-reporting' ),
			'tablesSearchPlaceholder'                     => __( 'Search...', 'uncanny-learndash-reporting' ),
			/* translators: %s is the course title */
			'overviewGoToCourseOverview'                  => _x( '%s Overview', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			'overviewGoToCourseUserReport'                => __( 'User Report', 'uncanny-learndash-reporting' ),
			'overviewUsers'                               => __( 'Users', 'uncanny-learndash-reporting' ),
			/* translators: %s is the user ID */
			'overviewUserCardId'                          => __( 'ID: %s', 'uncanny-learndash-reporting' ),
			'overviewBoxesTitleRecentActivities'          => __( 'Recent Activities', 'uncanny-learndash-reporting' ),
			'overviewBoxesTitleReports'                   => __( 'Reports', 'uncanny-learndash-reporting' ),
			/* translators: %s is the "Courses" label */
			'overviewBoxesTitleCompletedCourses'          => _x( 'Most Completed %s', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			/* translators: %s is the "Courses" label */
			'overviewBoxesReportsCourseReportTitle'       => _x( '%s Report', '%s is the "Course" label', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsCourseReportDescription' => __( 'A summary-level overview of LearnDash courses and user progress', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsUserReportTitle'         => __( 'User Report', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsUserReportDescription'   => __( 'Monitor progress for individual users enrolled in LearnDash courses', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsTinCanReportTitle'       => __( 'Tin Can Report', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsTinCanReportDescription' => __( 'Detailed records of user activity in H5P and uploaded modules', 'uncanny-learndash-reporting' ),
			/* translators: %s is the "Courses" label */
			'overviewBoxesReportsCoursesCompletionSeeAll' => _x( 'See all %s', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsCoursesCompletionNoData' => __( 'No completions registered', 'uncanny-learndash-reporting' ),
			'overviewLoading'                             => __( 'Loading', 'uncanny-learndash-reporting' ),
			'graphNoActivity'                             => __( 'No activity registered', 'uncanny-learndash-reporting' ),
			'graphNoEnrolledUsers'                        => __( 'No enrolled users', 'uncanny-learndash-reporting' ),
			/* translators: %s is the "Courses" label */
			'graphCourseCompletions'                      => _x( '%s Completions', '%s is the "Course" label', 'uncanny-learndash-reporting' ),
			'graphTinCanStatements'                       => __( 'Tin Can Statements', 'uncanny-learndash-reporting' ),
			/* translators: %s is the number of completions */
			'graphTooltipCompletions'                     => _x( '%s completion(s)', '%s is a number', 'uncanny-learndash-reporting' ),
			/* translators: %s is the number of statements */
			'graphTooltipStatements'                      => _x( '%s statement(s)', '%s is a number', 'uncanny-learndash-reporting' ),
			'customizeColumns'                            => _x( 'Customize columns', 'Customize columns', 'uncanny-learndash-reporting' ),
			'hideCustomizeColumns'                        => _x( 'Hide customize columns', 'Customize columns', 'uncanny-learndash-reporting' ),
			'showAll'                                     => __( 'Show all', 'uncanny-learndash-reporting' ),
			'ID'                                          => __( 'ID', 'uncanny-learndash-reporting' ),
		);
	}

	/**
	 * Add tincany via shortcode on the frontend
	 *
	 * @return string
	 */
	public static function frontend_tincanny() {
		ob_start();

		self::options_menu_page_output();

		$output = ob_get_clean();

		return str_replace( "id='module'", '', $output );
	}

	/**
	 * Create Theme Options page
	 */
	public static function options_menu_page_output() {

		if ( ! is_user_logged_in() ) {
			echo esc_html__( 'You must be logged in to view this report.', 'uncanny-learndash-reporting' );

			return;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			echo esc_html__( 'You do not have access to this report', 'uncanny-learndash-reporting' );

			return;
		}

		// Get Tin Canny settings
		$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();
		// Check if the parameter exists and has a valid value
		if ( ultc_filter_has_var( 'tab' ) && in_array( ultc_filter_input( 'tab' ), array( 'userReportTab', 'tin-can', 'xapi-tincan' ), true ) ) {
			// Set current tab
			$current_tab = ultc_filter_input( 'tab' );
		} else {
			// If the tab parameter is not defined, or it is, but it's not one of the values in
			// the in_array function, then use the default tab
			$current_tab = 'courseReportTab';
		}

		$is_course_report = 'courseReportTab' === $current_tab;
		$is_user_report   = 'userReportTab' === $current_tab;
		$is_tincan_report = 'tin-can' === $current_tab;
		$is_xapi_report   = 'xapi-tincan' === $current_tab;

		self::$template_data['labels'] = array(
			'course'      => \LearnDash_Custom_Label::get_label( 'course' ),
			'courses'     => \LearnDash_Custom_Label::get_label( 'courses' ),
			'lessons'     => \LearnDash_Custom_Label::get_label( 'lessons' ),
			'topics'      => \LearnDash_Custom_Label::get_label( 'topics' ),
			'quizzes'     => \LearnDash_Custom_Label::get_label( 'quizzes' ),
			'assignments' => __( 'Assignments', 'learndash' ),
		);

		// Options - Restrict for group leader
		$show_tincan = get_option( 'show_tincan_reporting_tables', 'yes' );

		if ( 'no' === $show_tincan ) {
			self::$tincan_show = 'style="display: none;"';
		}

		$user_can_view_all_reports = apply_filters( 'tincanny_view_all_reports_permission', current_user_can( 'manage_options' ) );

		if ( $user_can_view_all_reports ) {
			$group_ids = array();
		} elseif ( learndash_is_group_leader_user( get_current_user_id() ) ) {
			$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
		} else {
			echo esc_html( __( 'This report is only accessible by group leaders and administrators.', 'uncanny-learndash-reporting' ) );

			return;
		}

		if ( empty( $group_ids ) && ! $user_can_view_all_reports ) {
			echo esc_html( __( 'Group Leader has no groups assigned.', 'uncanny-learndash-reporting' ) );

			return;
		} else {
			$groups = get_posts(
				array(
					'numberposts' => 9999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
					'include'     => $group_ids,
					'post_type'   => 'groups',
					'orderby'     => 'title',
					'order'       => 'ASC',
				)
			);
			if ( $groups && ! $user_can_view_all_reports ) {

				$gl_ids = array();
				foreach ( $groups as $__group ) {
					$gl__users              = learndash_get_groups_administrators( $__group->ID );
					$gl_ids[ $__group->ID ] = array();
					foreach ( $gl__users as $rr ) {
						$gl_ids[ $__group->ID ][] = $rr->ID;
					}
				}

				foreach ( $groups as $key => $__groups ) {
					if ( ! in_array( get_current_user_id(), $gl_ids[ $__groups->ID ], true ) ) {
						unset( $groups[ $key ] );
					}
				}
			}
		}

		self::$groups_query = $groups;

		if ( ultc_filter_has_var( 'group_id' ) ) {
			self::$isolated_group = absint( ultc_filter_input( 'group_id' ) );
		}

		// Get context
		// Values:
		// - dashboard:  WP Admin main page
		// - plugin:     The Tin Canny Dashboard page
		// - frontend:   Frontend
		$context = 'frontend';

		if ( is_admin() ) {
			$context = 'uncanny-learnDash-reporting' === ultc_get_filter_var( 'page', '' ) ? 'plugin' : 'dashboard';
		}
		// Get Tin Canny settings
		$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

		// Add CSS classes to main container
		$css_classes   = array();
		$css_classes[] = sprintf( 'uo-reporting--%s', $context );

		?>

		<div class="tclr wrap <?php echo esc_attr( $css_classes[0] ); ?>" id="tincanny-reporting"
			data-context="<?php echo esc_attr( $context ); ?>">
			<div id="ld_course_info" class="uo-tclr-admin uo-admin-reporting">

				<?php

				if (
					( is_admin() && defined( 'LEARNDASH_LMS_PLUGIN_URL' ) )
					|| (
						! is_admin() && defined( 'LEARNDASH_LMS_PLUGIN_URL' ) && function_exists( 'learndash_is_active_theme' )
						&& learndash_is_active_theme( 'ld30' )
					)
				) {
					$icon = LEARNDASH_LMS_PLUGIN_URL . 'themes/legacy/templates/images/statistics-icon-small.png';
					?>

					<style>
						.statistic_icon {
							background: url(<?php echo esc_attr( $icon ); ?>) no-repeat scroll 0 0 transparent;
							width: 23px;
							height: 23px;
							margin: auto;
							background-size: 23px;
						}
					</style>

					<?php
				}

				$filepath = \SFWD_LMS::get_template( 'learndash_template_script.js', null, null, true );

				if ( ! empty( $filepath ) ) {

					wp_register_script( 'uo_learndash_template_script_js', learndash_template_url_from_path( $filepath ), array(), '1.0', true );
					$learndash_assets_loaded['scripts']['learndash_template_script_js'] = __FUNCTION__;

					$data            = array();
					$data['ajaxurl'] = admin_url( 'admin-ajax.php' );
					$data            = array( 'json' => wp_json_encode( $data ) );
					wp_localize_script( 'uo_learndash_template_script_js', 'sfwd_data', $data );

					wp_enqueue_script( 'uo_learndash_template_script_js' );
				}

				\LD_QuizPro::showModalWindow();

				// Add admin header and tabs
				$base_url = self::get_base_url();
				include self::get_part( 'header.php' );

				do_action( 'tincanny_reporting_wrapper_before_begin' );

				?>

				<div class="uo-tclr-admin__content">

					<?php do_action( 'tincanny_reporting_wrapper_after_begin' ); ?>

					<script>

						// Move the statistics container just before the closing body tag
						jQuery(document).on('click', 'a.user_statistic', function (event) {
							// Move user overlay
							jQuery('#wpProQuiz_user_overlay').appendTo(jQuery('body'));
						});

					</script>

					<h3 id="failed-response"></h3>

					<?php include self::get_part( 'groups-drop-down.php' ); ?>

					<section id="first-tab-group" class="uo-admin-reporting-tabgroup">

						<?php
						// Add Course report tab content.
						if ( $is_course_report || $is_user_report ) {
							include self::get_part( 'course-report-tab.php' );
						}
						// Add User report tab content.
						if ( $is_user_report ) {
							include self::get_part( 'user-report-tab.php' );
						}
						?>

						<?php if ( is_admin() ) { ?>

							<div class="uo-admin-reporting-tab-single" id="tin-can"
								style="display: <?php echo $is_tincan_report ? 'block' : 'none'; ?>">

								<?php
								// Add Tin Can tab content.
								if ( $is_tincan_report ) {
									do_action( 'tincanny_reporting_tin_can_after_begin' );

									self::show_tincan_list_table( 'tin-can' );

									do_action( 'tincanny_reporting_tin_can_before_end' );
								}
								?>

							</div>

							<div class="uo-admin-reporting-tab-single" id="xapi-tincan"
								style="display: <?php echo $is_xapi_report ? 'block' : 'none'; ?>">

								<?php
								// Add xAPI tab content.
								if ( $is_xapi_report ) {
									do_action( 'tincanny_reporting_xtin_quiz_after_begin' );

									self::show_tincan_list_table( 'xapi-tincan' );

									do_action( 'tincanny_reporting_xtin_quiz_before_end' );
								}
								?>

							</div>

							<?php
						} else {
							if ( isset( $tincanny_settings['enableTinCanReportFrontEnd'] ) ) {
								if ( 1 === (int) $tincanny_settings['enableTinCanReportFrontEnd'] ) {
									?>
									<div class="uo-admin-reporting-tab-single" id="tin-can"
										style="display: <?php echo $is_tincan_report ? 'block' : 'none'; ?>">

									<?php
									// Add Tin Can tab content.
									if ( $is_tincan_report ) {

										do_action( 'tincanny_reporting_tin_can_after_begin' );
										?>

										<div class="reporting-datatable__table">
											<?php self::show_tincan_list_table( 'tin-can' ); ?>
										</div>

										<?php
										do_action( 'tincanny_reporting_tin_can_before_end' );
									}
									?>

									</div>

									<?php
								}
							}
							if ( isset( $tincanny_settings['enablexapiReportFrontEnd'] ) ) {
								if ( 1 === (int) $tincanny_settings['enablexapiReportFrontEnd'] ) {
									?>
									<div class="uo-admin-reporting-tab-single" id="xapi-tincan"
										style="display: <?php echo $is_xapi_report ? 'block' : 'none'; ?>">

									<?php
									// Add xAPI tab content.
									if ( $is_xapi_report ) {
										do_action( 'tincanny_reporting_xtin_quiz_after_begin' );
										?>

										<div class="reporting-datatable__table">
											<?php self::show_tincan_list_table( 'xapi-tincan' ); ?>
										</div>

										<?php
										do_action( 'tincanny_reporting_tin_can_before_end' );
									}
									?>

									</div>
									<?php
								}
							}
						}
						?>
					</section>

					<?php do_action( 'tincanny_reporting_wrapper_before_end' ); ?>

				</div>

				<?php do_action( 'tincanny_reporting_wrapper_after_end' ); ?>

			</div>
		</div>

		<?php
	}

	/*
	 * Add add-ons to options page
	 *
	 * @param Array() $classes_available
	 * @param Array() $active_classes
	 *
	 */

	/**
	 * Get file part path
	 *
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 *
	 * @return string
	 */
	public static function get_part( $file_name ) {

		$asset_uri = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'uncanny-reporting' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name;
		$asset_uri = apply_filters( 'tinccanny_get_part_path', $asset_uri, $file_name );

		return $asset_uri;
	}

	/*
	 * Check for Adds that are located in other UO plugins
	 *@param string $uo_plugin
	 *
	 *return mixed(false || String)
	*/

	private static function show_tincan_list_table( $current_tab = 'tin-can', $column_settings = array() ) {

		self::$tincan_database      = new \UCTINCAN\Database\Admin();
		self::$tincan_opt_per_pages = get_user_meta( get_current_user_id(), 'ucTinCan_per_page', true );
		self::$tincan_opt_per_pages = ( self::$tincan_opt_per_pages ) ? self::$tincan_opt_per_pages : 25;

		if ( ! is_admin() ) {
			// @todo REVIEW this setup
			// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
			global $hook_suffix;
			$hook_suffix = '';
			if ( isset( $page_hook ) ) {
				$hook_suffix = $page_hook;
			} elseif ( isset( $plugin_page ) ) {
				$hook_suffix = $plugin_page;
			} elseif ( isset( $pagenow ) ) {
				$hook_suffix = $pagenow;
			}
			// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			require_once ABSPATH . 'wp-admin/includes/screen.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
			require_once ABSPATH . 'wp-admin/includes/template.php';

		}

		include_once dirname( __FILE__ ) . '/includes/TinCan_List_Table.php';

		$tincan_list_table = new \TinCan_List_Table();
		$columns           = array();
		if ( 'xapi-tincan' === $current_tab ) {
			$user_settings   = get_user_meta( get_current_user_id(), 'xapi_report_columns', true );
			$column_settings = wp_parse_args( $user_settings, self::$xapi_report_columns );

			foreach ( $column_settings as $key => $column ) {
				if ( ! is_admin() ) {
					$columns[] = $column['label'];
				} else {
					if ( true === $column['value'] ) {
						$columns[] = $column['label'];
					}
				}
			}

			$columns = apply_filters( 'tincan_xapi_table_columns', $columns );

		} else {

			add_filter( 'tincan_table_columns', array( __CLASS__, 'tincan_remove_success_column' ), 10 );

			$columns = apply_filters(
				'tincan_table_columns',
				array(
					__( 'Group', 'uncanny-learndash-reporting' ),
					__( 'User', 'uncanny-learndash-reporting' ),
					__( 'Course', 'uncanny-learndash-reporting' ),
					__( 'Module', 'uncanny-learndash-reporting' ),
					__( 'Target', 'uncanny-learndash-reporting' ),
					__( 'Action', 'uncanny-learndash-reporting' ),
					__( 'Result', 'uncanny-learndash-reporting' ),
					__( 'Success', 'uncanny-learndash-reporting' ),
					__( 'Date Time', 'uncanny-learndash-reporting' ),
				)
			);

		}

		$tincan_list_table->sortable_columns = $columns;
		$tincan_list_table->__set( 'column', $columns );//           = $coulmns;

		if ( 'xapi-tincan' === $current_tab ) {
			$tincan_list_table->data           = array( __CLASS__, 'patchData_xapi' );
			$tincan_list_table->count          = array( __CLASS__, 'patchNumRows_xapi' );
			$tincan_list_table->per_page       = self::$tincan_opt_per_pages;
			$tincan_list_table->extra_tablenav = array( __CLASS__, 'ExtraTableNav_xapi' );
		} else {
			$tincan_list_table->data           = array( __CLASS__, 'patchData' );
			$tincan_list_table->count          = array( __CLASS__, 'patchNumRows' );
			$tincan_list_table->per_page       = self::$tincan_opt_per_pages;
			$tincan_list_table->extra_tablenav = array( __CLASS__, 'ExtraTableNav' );
		}

		$tincan_list_table->prepare_items();
		$tincan_list_table->views();

		$tincan_list_table->display();
	}

	/**
	 * Remove 'Success' column.
	 *
	 * @return array
	 */
	public static function tincan_remove_success_column( $columns ) {

		if ( is_array( $columns ) && ! empty( $columns ) ) {
			$key = array_search( 'Success', $columns, false ); // phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
			if ( false !== $key ) {
				unset( $columns[ $key ] );
			}
		}
		return $columns;
	}

	/**
	 * Add tincany via shortcode on the frontend
	 *
	 * @return string
	 */
	public static function frontend_tincanny_block() {
		// Admin Page Reporting UI Files

		// Admin CSS
		// TODO add debug and minify
		wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), array(), UNCANNY_REPORTING_VERSION );
		wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), array(), UNCANNY_REPORTING_VERSION );
		wp_enqueue_style( 'data-tables', Config::get_admin_css( 'datatables.min.css' ), array(), UNCANNY_REPORTING_VERSION );

		wp_register_style( 'reporting-admin', Config::get_admin_css( 'admin-style.css' ), array(), UNCANNY_REPORTING_VERSION );

		$dynamic_css = self::get_dynamic_css();
		wp_add_inline_style( 'reporting-admin', $dynamic_css );

		wp_enqueue_style( 'reporting-admin' );

		// Admin JS
		wp_register_script(
			'reporting_js_handle',
			Config::get_admin_js( 'reporting' ),
			array(
				'jquery',
				'wp-hooks',
				'wp-i18n',
			),
			UNCANNY_REPORTING_VERSION,
			false
		);

		// Add custom colors to use them in the JS
		$ui = self::get_ui_data();
		wp_localize_script( 'reporting_js_handle', 'TincannyUI', $ui );

		// Add Tin Canny data
		wp_localize_script( 'reporting_js_handle', 'TincannyData', self::get_script_data() );
		$isolated_group_id = absint( ultc_get_filter_var( 'group_id', 0 ) );

		// Get Tin Canny settings
		$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

		// API data
		$reporting_api_setup = array(
			'root'                      => esc_url_raw( rest_url() . 'uncanny_reporting/v1/' ),
			'nonce'                     => \wp_create_nonce( 'wp_rest' ),
			'learnDashLabels'           => ReportingApi::get_labels(),
			'isolated_group_id'         => $isolated_group_id,
			'isAdmin'                   => is_admin(),
			'editUsers'                 => current_user_can( 'edit_users' ),
			'localizedStrings'          => self::get_js_localized_strings(),
			'page'                      => 'frontend',
			'optimized_build'           => '1',
			'showTinCanTab'             => 1 === (int) $tincanny_settings['tinCanActivation'] ? '1' : '0',
			'userIdentifierDisplayName' => isset( $tincanny_settings['userIdentifierDisplayName'] ) && 1 === (int) $tincanny_settings['userIdentifierDisplayName'] ? '1' : '0',
			'userIdentifierFirstName'   => isset( $tincanny_settings['userIdentifierFirstName'] ) && 1 === (int) $tincanny_settings['userIdentifierFirstName'] ? '1' : '0',
			'userIdentifierLastName'    => isset( $tincanny_settings['userIdentifierLastName'] ) && 1 === (int) $tincanny_settings['userIdentifierLastName'] ? '1' : '0',
			'userIdentifierUsername'    => isset( $tincanny_settings['userIdentifierUsername'] ) && 1 === (int) $tincanny_settings['userIdentifierUsername'] ? '1' : '0',
			'userIdentifierEmail'       => isset( $tincanny_settings['userIdentifierEmail'] ) && 1 === (int) $tincanny_settings['userIdentifierEmail'] ? '1' : '0',
			'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
			'base_url'                  => self::get_base_url(),
		);

		wp_localize_script( 'reporting_js_handle', 'reportingApiSetup', $reporting_api_setup );
		wp_enqueue_script( 'reporting_js_handle' );

		// TinCan
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), '1.8.2' );

		wp_enqueue_style( 'tincanny-admin-tincanny-report-tab', Config::get_gulp_css( 'admin.tincan.report.tab' ), array(), UNCANNY_REPORTING_VERSION );
		wp_enqueue_script( 'tincanny-admin-tincanny-report-tab-js', Config::get_gulp_js( 'admin.tincan.report.tab' ), array(), UNCANNY_REPORTING_VERSION, false );
		// TODO remove if no issues
		//wp_enqueue_script( 'unTinCanAdmin', UCTINCAN_PLUGIN_URL . 'assets/dist/admin.table-min.js' );
		ob_start();

		self::options_menu_page_output();

		return ob_get_clean();
	}

	public static function create_features( $classes_available, $active_classes ) {

		//strip slashes from all keys in array
		$active_classes = Config::stripslashes_deep( $active_classes );

		// Sort add ons alphabetically by title
		$add_on_titles = array();
		foreach ( $classes_available as $key => $row ) {
			$add_on_titles[ $key ] = $row['title'];
		}
		array_multisort( $add_on_titles, SORT_ASC, $classes_available );

		foreach ( $classes_available as $key => $class ) {

			// skip sample classes
			if ( 'uncanny_learndash_reporting\Sample' === $key || 'uncanny_custom_reporting\Sample' === $key || 'uncanny_pro_reporting\Sample' === $key ) {
				continue;
			}

			if ( false === $class ) {
				?>
				<div class="uo_feature">
					<div class="uo_feature_title"><?php echo esc_html( $key ); ?></div>
					<div class="uo_feature_description">
					<?php
						esc_html_e( 'This class is not configured properly. Contact Support for assistance.', 'uncanny-learndash-reporting' );
					?>
					</div>
				</div>
				<?php
				continue;
			}

			$dependants_exist = $class['dependants_exist'];

			$is_activated = 'uo_feature_deactivated';
			$class_name   = $key;
			if ( isset( $active_classes[ $class_name ] ) ) {
				$is_activated = 'uo_feature_activated';
			}
			if ( true !== $dependants_exist ) {
				$is_activated = 'uo_feature_needs_dependants';
			}

			$icon = '<div class="uo_icon"></div>';
			if ( $class['icon'] ) {
				$icon = $class['icon'];
			}

			if ( ! isset( $class['settings'] ) || false === $class['settings'] ) {
				$class['settings']['modal'] = '';
				$class['settings']['link']  = '';
			}
			?>

			<?php
			// Setting Modal Popup
			echo $class['settings']['modal'];
			?>

			<div class="uo_feature">

				<?php
				// Settings Modal Popup trigger
				echo $class['settings']['link'];
				?>

				<div class="uo_feature_title">

					<?php echo esc_html( $class['title'] ); ?>

					<?php
					// Link to KB for Feature
					if ( null !== $class['kb_link'] ) {
						?>
						<a class="uo_feature_more_info" href="<?php echo esc_attr( $class['kb_link'] ); ?>" target="_blank">
							<i class="fa fa-question-circle"></i>
						</a>
					<?php } ?>

				</div>

				<div class="uo_feature_description"><?php echo $class['description']; ?></div>
				<div class="uo_icon_container"><?php echo esc_html( $icon ); ?></div>
				<div class="uo_feature_button <?php echo esc_attr( $is_activated ); ?>">

					<?php
					if ( true !== $dependants_exist ) {
						echo '<div><strong>' . esc_html( $dependants_exist ) . '</strong>' . esc_html__( ' is needed for this add-on', 'uncanny-learndash-reporting' ) . '</div>';
					} else {
						?>
						<div class="uo_feature_button_toggle"></div>
						<label class="uo_feature_label" for="<?php echo esc_attr( $class_name ); ?>">
							<?php echo esc_html( __( 'Activate ', 'uncanny-learndash-reporting' ) . $class['title'] ); ?>
						</label>
						<input class="uo_feature_checkbox" type="checkbox" id="<?php echo esc_attr( $class_name ); ?>"
							name="uncanny_reporting_active_classes[<?php echo esc_attr( $class_name ); ?>]"
							value="<?php echo esc_attr( $class_name ); ?>" 
							<?php
							if ( array_key_exists( $class_name, $active_classes ) ) {
								// Some wp installs remove slashes during db calls, being extra safe when comparing DB vs php values with stripslashes
								checked( stripslashes( $active_classes[ $class_name ] ), stripslashes( $class_name ), true );
							}
							?>
						/>
					<?php } ?>
				</div>
			</div>
			<?php
		}
	}

	public static function execute_csv_export() {
		include_once dirname( __FILE__ ) . '/uncanny-tincan/uncanny-tincan.php';

		self::$tincan_database = new \UCTINCAN\Database\Admin();

		self::SetTcFilters();
		self::SetOrder();

		$data = self::$tincan_database->get_data( 0, 'csv' );

		new \UCTINCAN\Admin\CSV( $data );
	}

	private static function SetTcFilters() {
		// Group
		if ( ! empty( ultc_get_filter_var( 'tc_filter_group', '' ) ) ) {
			self::$tincan_database->group = ultc_filter_input( 'tc_filter_group' );
		}

		// Actor
		if ( ! empty( ultc_get_filter_var( 'tc_filter_user', '' ) ) ) {
			self::$tincan_database->actor = ultc_filter_input( 'tc_filter_user' );
		}

		// Course
		if ( ! empty( ultc_get_filter_var( 'tc_filter_course', '' ) ) ) {
			self::$tincan_database->course = ultc_filter_input( 'tc_filter_course' );
		}

		// Lesson
		if ( ! empty( ultc_get_filter_var( 'tc_filter_lesson', '' ) ) ) {
			self::$tincan_database->lesson = ultc_filter_input( 'tc_filter_lesson' );
		}

		// Module
		if ( ! empty( ultc_get_filter_var( 'tc_filter_module', '' ) ) ) {
			self::$tincan_database->module = ultc_filter_input( 'tc_filter_module' );
		}

		// Verb
		if ( ! empty( ultc_get_filter_var( 'tc_filter_action', '' ) ) ) {
			self::$tincan_database->verb = strtolower( ultc_filter_input( 'tc_filter_action' ) );
		}

		// Questions
		if ( ! empty( ultc_get_filter_var( 'tc_filter_quiz', '' ) ) ) {
			self::$tincan_database->question = strtolower( ultc_filter_input( 'tc_filter_quiz' ) );
		}

		// Result
		if ( ! empty( ultc_get_filter_var( 'tc_filter_results', '' ) ) ) {
			self::$tincan_database->results = strtolower( ultc_filter_input( 'tc_filter_results' ) );
		}

		// Date
		if ( ! empty( ultc_get_filter_var( 'tc_filter_date_range', '' ) ) ) {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName
			switch ( ultc_filter_input( 'tc_filter_date_range' ) ) {
				case 'last':
					$date_range_last = ultc_get_filter_var( 'tc_filter_date_range_last', '' );
					if ( ! empty( $date_range_last ) ) {
						$current_time = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
						// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
						switch ( $date_range_last ) {
							case 'week':
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( 'last week', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;
							case 'month':
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( 'first day of last month', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;

							case '90days':
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( '-90 days', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;
							case '3months':
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( '-3 months', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;
							case '6months':
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( '-6 months', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;
						}
						// phpcs:enable WordPress.DateTime.RestrictedFunctions.date_date
					}
					break;
				case 'from':
					if ( ! empty( ultc_get_filter_var( 'tc_filter_start', '' ) ) ) {
						self::$tincan_database->dateStart = ultc_filter_input( 'tc_filter_start' );
					}

					if ( ! empty( ultc_get_filter_var( 'tc_filter_end', '' ) ) ) {
						self::$tincan_database->dateEnd = ultc_filter_input( 'tc_filter_end' ) . ' 23:59:59';
					}
					break;
			}
			// phpcs:enable WordPress.NamingConventions.ValidVariableName
		}
	}

	private static function SetOrder() {
		self::$tincan_database->orderby = 'xstored';
		self::$tincan_database->order   = 'desc';
		$order_by                       = ultc_get_filter_var( 'order_by', '' );
		if ( ! empty( $order_by ) ) {
			switch ( $order_by ) {
				case 'group':
				case 'user':
				case 'course':
					self::$tincan_database->orderby = $order_by . '_id';
					break;

				case 'action':
					self::$tincan_database->orderby = 'verb';
					break;

				case 'date-time':
					self::$tincan_database->orderby = 'xstored';
					break;
			}

			self::$tincan_database->order = ultc_get_filter_var( 'order', 'desc' );
		}
	}

	public static function execute_csv_export_xapi() {
		include_once dirname( __FILE__ ) . '/uncanny-tincan/uncanny-tincan.php';

		self::$tincan_database = new \UCTINCAN\Database\Admin();

		self::SetTcFilters();
		self::SetOrder();

		$data = self::$tincan_database->get_xapi_data( 0, 'csv-xapi' );

		new \UCTINCAN\Admin\CSV( $data );
	}

	public static function tincan_change_per_page() {
		$per_page = ultc_get_filter_var( 'per_page', '' );
		if ( ! empty( $per_page ) ) {
			update_user_meta( get_current_user_id(), 'ucTinCan_per_page', $per_page );
		}
	}

	// Number of Data

	public static function patchData() {
		$data = array();
		if ( 'tin-can' !== ultc_get_filter_var( 'tab', '' ) ) {
			return $data;
		}
		if ( ! is_admin() ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_REQUEST['paged'] ) ) {
				$_REQUEST['paged'] = explode( '/page/', sanitize_text_field( $_SERVER['REQUEST_URI'] ), 2 );
				if ( isset( $_REQUEST['paged'][1] ) ) {
					list( $_REQUEST['paged'], ) = explode( '/', sanitize_text_field( $_REQUEST['paged'][1] ), 2 );
				}
				if ( isset( $_REQUEST['paged'] ) && $_REQUEST['paged'] != '' ) {
					$_REQUEST['paged'] = intval( $_REQUEST['paged'] );
					if ( $_REQUEST['paged'] < 2 ) {
						$_REQUEST['paged'] = '';
					}
				} else {
					$_REQUEST['paged'] = '';
				}
			}
			self::$tincan_database->paged = isset( $_REQUEST['paged'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) : 1;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		} else {
			self::$tincan_database->paged = ultc_get_filter_var( 'paged', 1 );
		}
		$tincan_post_types = array(
			'sfwd-courses',
			'sfwd-lessons',
			'sfwd-topic',
			'sfwd-quiz',
			'sfwd-certificates',
			'sfwd-assignment',
			'groups',
		);

		self::SetOrder();

		if ( 'list' === ultc_get_filter_var( 'tc_filter_mode', '' ) ) {
			self::SetTcFilters();

			$data = self::$tincan_database->get_data( self::$tincan_opt_per_pages );
		}

		foreach ( $data as &$row ) {
			$lesson = get_post( $row['lesson_id'] );

			if ( is_object( $lesson ) && in_array( $lesson->post_type, $tincan_post_types, true ) ) {
				$group_link = admin_url( "post.php?post={$row[ 'group_id' ]}&action=edit" );
				$group_name = $row['group_name'];
				$group      = sprintf( '<a href="%s">%s</a>', $group_link, $group_name );

				$course_link = admin_url( "post.php?post={$row[ 'course_id' ]}&action=edit" );
				$course_name = $row['course_name'];
				$course      = sprintf( '<a href="%s">%s</a>', $course_link, $course_name );
			} else {

				$group  = __( 'n/a', 'uncanny-learndash-reporting' );
				$course = __( 'n/a', 'uncanny-learndash-reporting' );

				if ( $row['course_id'] > 0 && '' !== $row['course_name'] ) {
					$course_link = admin_url( "post.php?post={$row[ 'course_id' ]}&action=edit" );
					$course_name = $row['course_name'];
					$course      = sprintf( '<a href="%s">%s</a>', $course_link, $course_name );
				}
			}

			$row['group']  = $group;
			$row['user']   = sprintf( '<a href="%s">%s</a>', admin_url( "user-edit.php?user_id={$row[ 'user_id' ]}" ), $row['user_name'] );
			$row['course'] = $course;
			$row['module'] = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['module'], site_url() ), $row['module_name'] );
			$row['target'] = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['target'], site_url() ), $row['target_name'] );
			$row['action'] = ucfirst( $row['verb'] );

			$result = $row['result'];

			if ( ! is_null( $row['result'] ) && $row['minimum'] ) {
				$result = $row['result'] . ' / ' . $row['minimum'];
			}

			$completion = false;

			if ( ! is_null( $row['completion'] ) ) {
				$completion = ( $row['completion'] ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';
			}

			$row['result']    = '<span class="tclr-reporting-datatable__no-wrap">' . $result . '</span>';
			$row['success']   = $completion;
			$row['date-time'] = $row['xstored'];
			$row              = apply_filters( 'tincanny_row_data', $row );
		}

		return $data;
	}

	// Number of Data

	private static function make_absolute( $url, $base ) {
		// Return base if no url
		if ( ! $url ) {
			return $base;
		}

		// Return if already absolute URL
		if ( ! empty( wp_parse_url( $url, PHP_URL_SCHEME ) ) ) {
			return $url;
		}

		// Urls only containing query or anchor
		if ( '#' === $url[0] || '?' === $url[0] ) {
			return $base . $url;
		}

		// Parse base URL and convert to local variables: $scheme, $host, $path
		$parsed_url = wp_parse_url( $base );
		// If no path, use /
		$path   = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '/';
		$host   = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
		$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : '';

		// Dirty absolute URL
		$abs = "$host$path/$url";

		// Replace '//' or '/./' or '/foo/../' with '/'
		$re  = array( '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' );
		$abs = preg_replace( $re, '/', $abs, - 1, $n );

		// Absolute URL is ready!
		return $scheme . '://' . $abs;
	}

	public static function patchData_xapi() {
		$data = array();
		if ( 'xapi-tincan' !== ultc_get_filter_var( 'tab', '' ) ) {
			return $data;
		}
		if ( ! is_admin() ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_REQUEST['paged'] ) ) {
				$_REQUEST['paged'] = explode( '/page/', sanitize_text_field( $_SERVER['REQUEST_URI'] ), 2 );
				if ( isset( $_REQUEST['paged'][1] ) ) {
					list( $_REQUEST['paged'], ) = explode( '/', sanitize_text_field( $_REQUEST['paged'][1] ), 2 );
				}
				if ( isset( $_REQUEST['paged'] ) && $_REQUEST['paged'] != '' ) {
					$_REQUEST['paged'] = intval( $_REQUEST['paged'] );
					if ( $_REQUEST['paged'] < 2 ) {
						$_REQUEST['paged'] = '';
					}
				} else {
					$_REQUEST['paged'] = '';
				}
			}
			self::$tincan_database->paged = isset( $_REQUEST['paged'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) : 1;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		} else {
			self::$tincan_database->paged = absint( ultc_get_filter_var( 'paged', 1 ) );
		}
		$tincan_post_types = array(
			'sfwd-courses',
			'sfwd-lessons',
			'sfwd-topic',
			'sfwd-quiz',
			'sfwd-certificates',
			'sfwd-assignment',
			'groups',
		);

		self::SetOrder();

		if ( 'list' === ultc_get_filter_var( 'tc_filter_mode', '' ) ) {
			self::SetTcFilters();

			$data = self::$tincan_database->get_xapi_data( self::$tincan_opt_per_pages );
		}

		foreach ( $data as &$row ) {
			$lesson = get_post( $row['lesson_id'] );
			if ( ! empty( $lesson ) && in_array( $lesson->post_type, $tincan_post_types, true ) ) {
				$group_link = admin_url( "post.php?post={$row[ 'group_id' ]}&action=edit" );
				$group_name = $row['group_name'];
				$group      = sprintf( '<a href="%s">%s</a>', $group_link, $group_name );

				$course_link = admin_url( "post.php?post={$row[ 'course_id' ]}&action=edit" );
				$course_name = $row['course_name'];
				$course      = sprintf( '<a href="%s">%s</a>', $course_link, $course_name );
			} else {
				$group  = __( 'n/a', 'uncanny-learndash-reporting' );
				$course = __( 'n/a', 'uncanny-learndash-reporting' );
			}

			$row['group']  = $group;
			$row['user']   = sprintf( '<a href="%s">%s</a>', admin_url( "user-edit.php?user_id={$row[ 'user_id' ]}" ), $row['user_name'] );
			$row['course'] = $course;
			$row['module'] = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['module'], site_url() ), $row['module_name'] );
			//$row[' Target '] = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['target'], site_url() ), $row['target_name'] );
			$row['question'] = ucfirst( $row['activity_name'] );

			$result     = $row['result'];
			$completion = false;

			if ( ! is_null( $row['result'] ) ) {
				$completion = ( $row['result'] > 0 ) ? 'Correct' : 'Incorrect';
			} else {
				$completion = 'Incorrect';
			}

			$result = $row['result'];

			if ( isset( $row['minimum'] ) ) {
				if ( ! is_null( $row['result'] ) && $row['minimum'] ) {
					$result = $row['result'] . ' / ' . $row['minimum'];
				}
			}

			$row['score']            = (int) $result;
			$row['result']           = $completion;
			$row['success']          = $completion;
			$row['more-info']        = '<a href="javascript::void(0);" onclick="jQuery(\'#other_details_' . $row['id'] . '\').show();">Show details</a><p style="display: none" id="other_details_' . $row['id'] . '"><strong>Choices:</strong> ' . $row['available_responses'] . '<br/><strong>Correct Answer:</strong> ' . $row['correct_response'] . '<br/><strong>User\'s Answer:</strong> ' . $row['user_response'] . '</p>';
			$row['date-time']        = $row['xstored'];
			$row['choices']          = $row['available_responses'];
			$row['correct-response'] = $row['correct_response'];
			$row['user-response']    = $row['user_response'];
			$row                     = apply_filters( 'tincanny_row_data', $row );
		}

		return $data;
	}

	public static function patchNumRows() {
		if ( 'list' === ultc_get_filter_var( 'tc_filter_mode', '' ) ) {
			return self::$tincan_database->get_count();
		}

		return 0;
	}

	public static function patchNumRows_xapi() {
		if ( 'list' === ultc_get_filter_var( 'tc_filter_mode', '' ) ) {
			return self::$tincan_database->get_count_xapi();
		}

		return 0;
	}

	public static function ExtraTableNav( $which ) {
		switch ( $which ) {
			case 'top':
				self::ExtraTableNavTop();
				break;
			case 'bottom':
				self::ExtraTableNavBottom();
				break;
		}
	}

	//! Search Box

	private static function ExtraTableNavTop() {
		$ld_groups  = array();
		$ld_courses = array();
		if ( ! is_admin() ) {
			$group_leader_id = get_current_user_id();
			$user_group_ids  = learndash_get_administrators_group_ids( $group_leader_id, true );
			$args            = array(
				'numberposts' => 9999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
				'include'     => array_map( 'intval', $user_group_ids ),
				'post_type'   => 'groups',
				'orderby'     => 'title',
				'order'       => 'ASC',
			);

			$ld_groups_user = get_posts( $args );
			if ( ! empty( $ld_groups_user ) ) {
				foreach ( $ld_groups_user as $ld_group ) {
					$ld_groups[] = array(
						'group_id'   => $ld_group->ID,
						'group_name' => $ld_group->post_title,
					);
				}
			}
			// Courses
			$get_filter_group_id = absint( ultc_get_filter_var( 'tc_filter_group', 0 ) );
			if ( ! empty( $get_filter_group_id ) ) {

				// check is user group
				if ( in_array( $get_filter_group_id, $user_group_ids, true ) ) {
					$courses = learndash_group_enrolled_courses( $get_filter_group_id );
					$args    = array(
						'numberposts' => 9999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
						'include'     => array_map( 'intval', $courses ),
						'post_type'   => 'sfwd-courses',
						'orderby'     => 'title',
						'order'       => 'ASC',
					);

					$courses = get_posts( $args );
					foreach ( $courses as $course ) {
						$ld_courses[] = array(
							'course_id'   => $course->ID,
							'course_name' => $course->post_title,
						);
					}
				}
			}
			// Actions
			$ld_actions = self::$tincan_database->get_actions();

		} else {

			// Group
			$ld_groups = self::$tincan_database->get_groups();

			// Courses
			$ld_courses = self::$tincan_database->get_courses();

			// Actions
			$ld_actions = self::$tincan_database->get_actions();
		}

		include self::get_part( 'tc-tincan-filter.php' );

		?>

		<script>
			jQuery(document).ready(function ($) {
				$('.datepicker').datepicker({
					'dateFormat': 'yy-mm-dd'
				});

				$('.dashicons-calendar-alt').click(function () {
					$(this).prev().focus();
				});
			});
		</script>
		<?php
	}

	private static function ExtraTableNavBottom() {
		$per_pages = array(
			10,
			25,
			50,
			100,
			200,
			500,
			self::$tincan_opt_per_pages,
		);

		$per_pages = array_unique( $per_pages );
		asort( $per_pages );

		?>
		<div id="tincan-filters-per_page">
			<select>
				<?php foreach ( $per_pages as $per_page ) { ?>
					<option
						value="<?php echo esc_attr( $per_page ); ?>" <?php echo esc_attr( ( (int) self::$tincan_opt_per_pages === (int) $per_page ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $per_page ); ?></option>
				<?php } // foreach( $ld_groups ) ?>
			</select>

			<?php esc_html_e( 'Per Page', 'uncanny-learndash-reporting' ); ?>
		</div>

		<div id="tincan-filters-export">
			<form action="
			<?php
			echo esc_attr(
				remove_query_arg(
					array(
						'paged',
						'tc_filter_mode',
						'tc_filter_group',
						'tc_filter_user',
						'tc_filter_course',
						'tc_filter_lesson',
						'tc_filter_module',
						'tc_filter_action',
						'tc_filter_date_range',
						'tc_filter_date_range_last',
						'tc_filter_start',
						'tc_filter_end',
						'orderby',
						'order',
					)
				)
			);
			?>
			" method="get" id="tincan-filters-bottom">
				<input type="hidden" name="tc_filter_mode" value="csv"/>
				<input type="hidden" name="tab" value="tin-can"/>

				<input type="hidden" name="tc_filter_group" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_group', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_user" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_user', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_course" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_course', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_lesson" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_lesson', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_module" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_module', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_action" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_action', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_date_range" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_date_range', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_date_range_last" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_date_range_last', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_start" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_start', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_end" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_end', '' ) ); ?>"/>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( ultc_get_filter_var( 'orderby', '' ) ); ?>"/>
				<input type="hidden" name="order" value="<?php echo esc_attr( ultc_get_filter_var( 'order', '' ) ); ?>"/>

				<?php submit_button( __( 'Export To CSV', 'uncanny-learndash-reporting' ), 'action', '', false, array( 'id' => 'do_tc_export_csv' ) ); ?>
			</form>
		</div>

		<?php
	}

	//! Search Box XAPI

	public static function ExtraTableNav_xapi( $which ) {
		switch ( $which ) {
			case 'top':
				self::ExtraTableNavTop_xapi();
				break;
			case 'bottom':
				self::ExtraTableNavBottom_xapi();
				break;
		}
	}

	private static function ExtraTableNavTop_xapi() {
		$ld_groups  = array();
		$ld_courses = array();
		if ( ! is_admin() ) {
			$group_leader_id = get_current_user_id();
			$user_group_ids  = learndash_get_administrators_group_ids( $group_leader_id, true );
			$args            = array(
				'numberposts' => 9999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
				'include'     => array_map( 'intval', $user_group_ids ),
				'post_type'   => 'groups',
				'orderby'     => 'title',
				'order'       => 'ASC',
			);

			$ld_groups_user = get_posts( $args );
			if ( ! empty( $ld_groups_user ) ) {
				foreach ( $ld_groups_user as $ld_group ) {
					$ld_groups[] = array(
						'group_id'   => $ld_group->ID,
						'group_name' => $ld_group->post_title,
					);
				}
			}
			// Courses
			$get_filter_group_id = absint( ultc_get_filter_var( 'tc_filter_group', 0 ) );
			if ( ! empty( $get_filter_group_id ) ) {

				// check is user group
				if ( in_array( $get_filter_group_id, $user_group_ids, true ) ) {
					$courses = learndash_group_enrolled_courses( $get_filter_group_id );
					$args    = array(
						'numberposts' => 9999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
						'include'     => array_map( 'intval', $courses ),
						'post_type'   => 'sfwd-courses',
						'orderby'     => 'title',
						'order'       => 'ASC',
					);

					$courses = get_posts( $args );
					foreach ( $courses as $course ) {
						$ld_courses[] = array(
							'course_id'   => $course->ID,
							'course_name' => $course->post_title,
						);
					}
				}
			}
		} else {

			// Group
			$ld_groups = self::$tincan_database->get_groups( 'quiz' );

			// Courses
			$ld_courses = self::$tincan_database->get_courses( 'quiz' );

			// Actions
			//$ld_actions = self::$tincan_database->get_questions();
		}

		include self::get_part( 'tc-xapi-filter.php' );

		?>

		<?php
		if ( 'list' === ultc_get_filter_var( 'tc_filter_mode', '' ) ) {
			if ( is_admin() ) {
				?>

				<div class="reporting-table-info">
					<?php esc_html_e( 'To customize the columns that are displayed, use the Screen Options tab in the top right.', 'uncanny-learndash-reporting' ); ?>
				</div>

				<?php
			}
		}
		?>

		<script>
			jQuery(document).ready(function ($) {
				$('.datepicker').datepicker({
					'dateFormat': 'yy-mm-dd'
				});

				$('.dashicons-calendar-alt').click(function () {
					$(this).prev().focus();
				});
			});
		</script>
		<?php
	}

	public static function limit_text( $text, $limit ) {
		if ( str_word_count( $text, 0 ) > $limit ) {
			$words = str_word_count( $text, 2 );
			$pos   = array_keys( $words );
			$text  = substr( $text, 0, $pos[ $limit ] ) . '...';
		}

		return $text;
	}

	private static function ExtraTableNavBottom_xapi() {
		$per_pages = array(
			10,
			25,
			50,
			100,
			200,
			500,
			self::$tincan_opt_per_pages,
		);

		$per_pages = array_unique( $per_pages );
		asort( $per_pages );

		?>
		<div id="tincan-filters-per_page">
			<select>
				<?php foreach ( $per_pages as $per_page ) { ?>
					<option
						value="<?php echo esc_attr( $per_page ); ?>" <?php echo esc_attr( ( (int) self::$tincan_opt_per_pages === (int) $per_page ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $per_page ); ?></option>
				<?php } // foreach( $ld_groups ) ?>
			</select>

			<?php esc_html_e( 'Per Page', 'uncanny-learndash-reporting' ); ?>
		</div>

		<div id="tincan-filters-export">
			<form action="
			<?php
			echo esc_attr(
				remove_query_arg(
					array(
						'paged',
						'tc_filter_mode',
						'tc_filter_group',
						'tc_filter_user',
						'tc_filter_course',
						'tc_filter_lesson',
						'tc_filter_module',
						'tc_filter_action',
						'tc_filter_quiz',
						'tc_filter_results',
						'tc_filter_date_range',
						'tc_filter_date_range_last',
						'tc_filter_start',
						'tc_filter_end',
						'orderby',
						'order',
					)
				)
			);
			?>
			" method="get" id="xapi-filters-bottom">
				<input type="hidden" name="tc_filter_mode" value="csv-xapi"/>
				<input type="hidden" name="tc_filter_group" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_group', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_user" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_user', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_course" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_course', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_lesson" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_lesson', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_module" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_module', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_action" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_action', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_quiz" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_quiz', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_results" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_results', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_date_range" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_date_range', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_date_range_last" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_date_range_last', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_start" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_start', '' ) ); ?>"/>
				<input type="hidden" name="tc_filter_end" value="<?php echo esc_attr( ultc_get_filter_var( 'tc_filter_end', '' ) ); ?>"/>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( ultc_get_filter_var( 'orderby', '' ) ); ?>"/>
				<input type="hidden" name="order" value="<?php echo esc_attr( ultc_get_filter_var( 'order', '' ) ); ?>"/>

				<?php submit_button( __( 'Export To CSV', 'uncanny-learndash-reporting' ), 'action', '', false, array( 'id' => 'do_tc_export_csv_xapi' ) ); ?>
			</form>
		</div>

		<?php
	}

	private static function check_for_other_uo_plugin_classes( $uo_plugin ) {

		// plugins dir
		$directory_contents = scandir( WP_PLUGIN_DIR );

		// loop through all contents
		foreach ( $directory_contents as $content ) {

			// exclude parent directories
			if ( '.' !== $content || '..' !== $content ) {
				// create absolute path
				$plugin_dir = WP_PLUGIN_DIR . '/' . $content;

				if ( is_dir( $plugin_dir ) ) {

					if ( 'pro' === $uo_plugin ) {
						if ( 'uo-plugin-pro' === $content || 'uncanny-reporting-pro' === $content ) {
							// Check if plugin is active
							if ( is_plugin_active( $content . '/uncanny-reporting-pro.php' ) ) {
								return $plugin_dir . '/src/classes/';
							}
						}
					}

					if ( 'custom' === $uo_plugin ) {

						$explode_directory = explode( '-', $content );
						if ( 3 === count( $explode_directory ) ) {
							// custom plugin directory is may be prefixed with client name
							// check suffix uo-custom-plugin
							if ( in_array( 'uo', $explode_directory, true ) && in_array( 'custom', $explode_directory, true ) && in_array( 'plugin', $explode_directory, true ) ) {

								// Check if plugin is active
								if ( is_plugin_active( $content . '/uncanny-reporting-custom.php' ) ) {
									return $plugin_dir . '/src/classes/';
								}
							}

							if ( 'uncanny-reporting-custom' === $content ) {

								// Check if plugin is active
								if ( is_plugin_active( $content . '/uncanny-reporting-custom.php' ) ) {
									return $plugin_dir . '/src/classes/';
								}
							}
						}
					}
				}
			}
		}

		return false;

	}

	public function filter__screen_settings( $screen_settings, $screen ) {
		if ( 'uncanny-learnDash-reporting' !== $screen->parent_base ) {
			return $screen_settings;
		}
		$user_settings = get_user_meta( get_current_user_id(), 'xapi_report_columns', true );

		self::$xapi_report_columns = wp_parse_args( $user_settings, self::$xapi_report_columns );

		$out = '';
		foreach ( self::$xapi_report_columns as $option => $args ) {
			$label   = isset( $args['label'] ) && ! empty( $args['label'] ) ? $args['label'] : $option;
			$default = $args['default'];
			$type    = $args['type'];
			switch ( $type ) {
				default:
					$value = self::$xapi_report_columns[ $option ]['value'];
					if ( is_null( $value ) ) {
						$value = $default;
					}
					$out .= sprintf( '<label for="%1$s"> <input id="%1$s" name="wp_screen_options[value][%1$s]" value="1" type="checkbox" class="screen-per-page" %3$s />%2$s</label>', esc_attr( $option ), $label, true === $value ? 'checked="checked"' : '' );
			}
		}
		if ( $out ) {
			$screen_settings .= sprintf(
				'
				<fieldset class="metabox-prefs">
				<legend>xAPI Quiz Report</legend>
				<input type="hidden" name="wp_screen_options[option]" value="%s" />
				%s%s</fieldset>',
				'xapi_report_columns',
				$out,
				get_submit_button( __( 'Apply' ), 'button', 'screen-options-apply', false )
			);
		}

		return $screen_settings;
	}

	public function filter__set_screen_option( $status, $option, $values ) {
		if ( 'xapi_report_columns' === $option ) {
			// This class owns the option.
			if ( is_array( $values ) ) {
				foreach ( self::$xapi_report_columns as $option => $details ) {
					self::$xapi_report_columns[ $option ]['value'] = false;
					if ( isset( $values[ $option ] ) ) {
						self::$xapi_report_columns[ $option ]['value'] = true;
					}
				}

				return self::$xapi_report_columns;
			}
		}

		return $status;
	}

	public static function get_base_url() {
		if ( is_admin() ) {
			return admin_url( 'admin.php?page=uncanny-learnDash-reporting' );
		} else {
			global $wp;
			return home_url( add_query_arg( array(), $wp->request ) );
		}
	}

}
