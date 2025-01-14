<?php

namespace uncanny_learndash_reporting;

class Config {
	/**
	 * Version of the plugin
	 *
	 * @var string
	 */
	private static $version;

	/**
	 * File path
	 *
	 * @var string
	 */
	private static $file;

	/**
	 * Basename
	 *
	 * @var string
	 */
	private static $basename;

	/**
	 * Project name
	 *
	 * @var string
	 */
	private static $project_name;

	/**
	 * Plugin file path
	 *
	 * @var string
	 */
	private static $plugin_dir;

	/**
	 * Plugin url path
	 *
	 * @var string
	 */
	private static $plugin_url;

	/**
	 * CSS prefix
	 *
	 * @var string
	 */
	private static $css_prefix;

	/**
	 * Available plugins
	 *
	 * @var array
	 */
	private static $available_plugins;

	/**
	 * Caching on/off
	 *
	 * @var bool
	 */
	private static $caching_on = false;

	/**
	 * The main plugin file path that is set in the main plugin file
	 *
	 * @use get_plugin_file()
	 *
	 * @since    3.0
	 * @access   private
	 * @var      string
	 */
	private static $plugin_file;

	/**
	 * Check if caching is on
	 *
	 * @return boolean
	 */
	public static function is_caching_on() {
		return self::$caching_on;
	}

	/**
	 * Set available classes
	 *
	 * @param $class_names
	 *
	 * @return array
	 */
	public static function set_available_classes( $class_names ) {
		self::$available_plugins = $class_names;
	}

	/**
	 * Get available classes
	 *
	 * @return array of class names
	 */
	public static function get_active_classes() {
		if ( ! self::$available_plugins ) {
			self::$available_plugins = get_option( 'uncanny_reporting_active_classes', array() );
			if ( empty( self::$available_plugins ) ) {
				self::$available_plugins = array();
			}
		}

		return self::$available_plugins;
	}

	/**
	 * Get plugin basename
	 *
	 * @return mixed
	 */
	public static function get_basename() {
		if ( null === self::$basename ) {
			self::$basename = plugin_basename( self::$file );
		}

		return self::$basename;
	}

	/**
	 * Get plugin file
	 *
	 * @return string
	 */
	public static function get_file() {
		if ( null === self::$file ) {
			self::$file = __FILE__;
		}

		return self::$file;
	}

	/**
	 * Get plugin directory
	 *
	 * @return string
	 */
	public static function get_plugin_dir() {
		if ( null === self::$plugin_dir ) {
			self::$plugin_dir = plugin_dir_path( self::$file );
		}

		return self::$plugin_dir;
	}

	/**
	 * Get plugin url
	 *
	 * @return string
	 */
	public static function get_plugin_url() {
		if ( null === self::$plugin_url ) {
			self::$plugin_url = plugin_dir_url( self::$file );
		}

		return self::$plugin_url;
	}

	/**
	 * Get admin media url
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_media( $file_name ) {
		$asset_url = plugins_url( 'assets/admin/media/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * Get admin css url
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_css( $file_name ) {
		$asset_url = plugins_url( 'assets/admin/css/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * Get admin js url
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_js( $file_name, $suffix = null ) {

		if ( null === $suffix ) {
			$file_name_suffix = '.min.js';

			if ( true === UO_REPORTING_DEBUG ) {
				$file_name_suffix = '.js';
			}
		} else {
			$file_name_suffix = $suffix;
		}

		$asset_url = plugins_url( 'assets/admin/js/dist/' . $file_name . $file_name_suffix, __FILE__ );

		return $asset_url;
	}

	public static function get_gulp_css( $file_name ) {
		$file_name_suffix = '.min.css';

		if ( true === UO_REPORTING_DEBUG ) {
			$file_name_suffix = '.css';
		}

		$asset_url = plugins_url( 'assets/dist/css/' . $file_name . $file_name_suffix, __FILE__ );

		return $asset_url;
	}

	public static function get_gulp_js( $file_name ) {
		$file_name_suffix = '.min.js';

		if ( true === UO_REPORTING_DEBUG ) {
			$file_name_suffix = '.js';
		}

		$asset_url = plugins_url( 'assets/dist/scripts/' . $file_name . $file_name_suffix, __FILE__ );

		return $asset_url;
	}

	/**
	 * Get site media url
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_site_media( $file_name ) {
		$asset_url = plugins_url( 'assets/site/media/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * Get site css url
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_site_css( $file_name ) {
		$asset_url = plugins_url( 'assets/site/css/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * Get site js url
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_site_js( $file_name ) {
		$asset_url = plugins_url( 'assets/site/js/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * Get template path
	 *
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 * @param mixed $file (false || __FILE__ )
	 *
	 * @return string
	 */
	public static function get_template( $file_name, $file = false ) {

		if ( false === $file ) {
			$file = __FILE__;
		}

		$asset_uri = dirname( $file ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name;

		return $asset_uri;
	}

	/**
	 * Get includes path
	 *
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 * @param mixed $file (false || __FILE__ )
	 *
	 * @return string
	 */
	public static function get_include( $file_name, $file = false ) {

		if ( false === $file ) {
			$file = __FILE__;
		}

		$asset_uri = dirname( $file ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $file_name;

		return $asset_uri;
	}

	/**
	 * Get plugin name
	 *
	 * @return string
	 */
	public static function get_project_name() {
		if ( null === self::$project_name ) {
			self::$project_name = 'uncanny_learndash_reporting';
		}

		return self::$project_name;
	}

	/**
	 * Set plugin name
	 *
	 * @param $project_name
	 */
	public static function set_project_name( $project_name ) {
		self::$project_name = $project_name;
	}

	/**
	 * Get plugin prefix
	 *
	 * @return string
	 */
	public static function get_prefix() {
		return self::get_project_name() . '_';
	}

	/**
	 * Get CSS Prefix
	 *
	 * @return string
	 */
	public static function get_css_prefix() {
		if ( null === self::$css_prefix ) {
			self::$css_prefix = str_replace( '_', '-', self::get_prefix() );
		}

		return self::$css_prefix;
	}

	/**
	 * Get underscored prefix
	 *
	 * @return string
	 */
	public static function _get_prefix() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		return '_' . self::get_prefix();
	}

	/**
	 * Get namespace
	 *
	 * @return string
	 */
	public static function get_namespace() {
		return self::get_project_name();
	}

	/**
	 * Set the main plugin file path
	 *
	 * @since    3.0
	 *
	 * @param string $plugin_file The main plugin file path
	 *
	 * @return string
	 */
	public static function set_plugin_file( $plugin_file ) {
		if ( null === self::$plugin_file ) {
			self::$plugin_file = $plugin_file;
		}

		return self::$plugin_file;
	}

	/**
	 * Get the main plugin file path
	 *
	 * @since    3.0
	 *
	 * @return string
	 */
	public static function get_plugin_file() {
		return self::$plugin_file;
	}

	/**
	 * Get date format
	 *
	 * @return string
	 */
	public static function get_date_formant() {
		return 'y/m/d g:i';
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public static function get_version() {
		if ( null === self::$version ) {
			self::$version = '1.2.8';
		}

		return self::$version;
	}

	/**
	 * Strip slashes from array
	 *
	 * @param array $array Array where there is slashes in the key
	 *
	 * @return array
	 */
	public static function stripslashes_deep( $array ) {
		$new_array = array();

		// strip slashes of all keys in array
		foreach ( $array as $key => $content ) {
			$key               = stripslashes( $key );
			$new_array[ $key ] = $content;
		}

		return $new_array;
	}

	/*
	 * Loops through array of setting values and return an link and settings html
	 * @param array		$settings
	 * @return array
	 */
	public static function settings_output( $settings ) {

		$class   = $settings['class'];// define by __CLASS__ from related php file
		$title   = $settings['title'];
		$options = $settings['options'];

		//create unique clean html id from class name
		$modal_id = stripslashes( $class );
		$modal_id = str_replace( __NAMESPACE__, '', $modal_id );

		$modal_link = '<a class="uo_settings_link" rel="leanModal" href="#' . $modal_id . '"><span class="dashicons dashicons-admin-generic"></span></a>';

		ob_start();

		//Wrapper Start - open div.uo_setting, open div.uo_settings_options
		?>

		<div id="<?php echo esc_attr( $modal_id ); ?>" class="uo_settings">

			<div class="uo_settings_header">
				<h2>
				<?php
				/* translators: %s: Settings title */
					echo esc_html( sprintf( __( 'Settings: %s', 'uncanny-learndash-reporting' ), $title ) );
				?>
				</h2>
			</div>

			<div class="sk-folding-cube">
				<div class="sk-cube1 sk-cube"></div>
				<div class="sk-cube2 sk-cube"></div>
				<div class="sk-cube4 sk-cube"></div>
				<div class="sk-cube3 sk-cube"></div>
			</div>

			<div class="uo_settings_options">

				<?php

				// Create options
				foreach ( $options as $content ) {
					switch ( $content['type'] ) {

						case 'html':
							echo '<div class="uo_settings_single ' . esc_attr( $content['class'] ) . '">' . $content['inner_html'] . '</div>';
							break;

						case 'text':
							echo '<div class="uo_settings_single"><span>' . esc_html( $content['label'] ) . '</span> <input placeholder="' . esc_attr( $content['placeholder'] ) . '" class="uo_settings_form_field" name="' . esc_attr( $content['option_name'] ) . '" type="text" /></div>';
							break;

						case 'color':
							echo '<div class="uo_settings_single"><span>' . esc_html( $content['label'] ) . '</span> <input class="uo_settings_form_field" name="' . esc_attr( $content['option_name'] ) . '" type="color" /></div>';
							break;

						case 'checkbox':
							echo '<div class="uo_settings_single"><input class="uo_settings_form_field" name="' . esc_attr( $content['option_name'] ) . '" type="checkbox" /> <span>' . esc_html( $content['label'] ) . '</span></div>';
							break;

						case 'radio':
							$inputs = '';
							foreach ( $content['radios'] as $radio ) {
								$inputs .= '<input class="uo_settings_form_field" type="radio" name="' . esc_attr( $content['radio_name'] ) . '" value="' . esc_attr( $radio['value'] ) . '">' . esc_html( $radio['text'] ) . ' ';
							}
							echo '<div class="uo_settings_single"><span>' . esc_html( $content['label'] ) . '</span><br><br>' . $inputs . '</div>';
							break;

						case 'select':
							$options = '';
							foreach ( $content['options'] as $option ) {
								$options .= '<option value="' . esc_attr( $option['value'] ) . '"> ' . esc_html( $option['text'] ) . '</option>';
							}
							echo '<div class="uo_settings_single"><span>' . esc_html( $content['label'] ) . '</span>
								<select class="uo_settings_form_field" name="' . esc_attr( $content['select_name'] ) . '" >' . $options . '</select>
							</div>';
							break;

					}
				}

				//Wrapper End - create button, close div.uo_setting, close div.uo_settings_options
				?>
				<button class="uo_save_settings"><?php esc_html_e( 'Save Settings', 'uncanny-learndash-reporting' ); ?></button>

			</div>

		</div>

		<?php

		$html_options = ob_get_clean();

		return array(
			'link'  => $modal_link,
			'modal' => $html_options,
		);

	}

	/**
	 * Ajax save settings
	 *
	 * @return string
	 */
	public static function ajax_settings_save() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( ultc_filter_has_var( 'class', INPUT_POST ) ) {

				$class   = ultc_filter_input( 'class', INPUT_POST );
				$options = ultc_filter_has_var( 'options', INPUT_POST ) ? ultc_filter_input_array( 'options', INPUT_POST ) : array();

				// Delete option and add option are called instead of update option because
				// sometimes update value is equal to the existing value and a false
				// positive is returned

				delete_option( $class );

				$save_settings = add_option( $class, $options, '', 'no' );
				$response      = $save_settings ? 'success' : 'notsaved';

			} else {
				$response = __( 'Class for addon is not set.', 'uncanny-learndash-reporting' );
			}
		} else {

			$response = __( 'You must be an admin to save settings.', 'uncanny-learndash-reporting' );

		}

		// Clean Buffer before Response
		ob_clean();

		echo esc_html( $response );

		wp_die();

	}

	/*
	 * @return string
	 */
	public static function ajax_settings_load() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( ultc_filter_has_var( 'class', INPUT_POST ) ) {

				$class    = ultc_filter_input( 'class', INPUT_POST );
				$settings = get_option( $class, array() );
				$response = wp_json_encode( $settings );

			} else {
				$response = __( 'Class for addon is not set.', 'uncanny-learndash-reporting' );
			}
		} else {

			$response = __( 'You must be an admin to save settings.', 'uncanny-learndash-reporting' );

		}

		// Clean Buffer before Response
		ob_clean();

		echo esc_html( $response );

		wp_die();

	}

	/**
	 * Get settings value
	 *
	 * @param $key
	 * @param $class
	 *
	 * @return string
	 */
	public static function get_settings_value( $key, $class ) {

		$class   = str_replace( __NAMESPACE__, '', stripslashes( $class ) );
		$options = get_option( $class, '' );

		if ( ! empty( $options ) && '' !== $options ) {
			foreach ( $options as $option ) {
				if ( in_array( $key, $option, true ) ) {
					return $option['value'];
				}
			}
		}

		return '';
	}

	/**
	 * Create and store logs @ wp-content/{plugin_folder_name}/uo-{$file_name}.log
	 *
	 * @since    1.0.0
	 *
	 * @param string $trace_message The message logged
	 * @param string $trace_heading The heading of the current trace
	 * @param bool $force_log Create log even if debug mode is off
	 * @param string $file_name The file name of the log file
	 *
	 * @return bool $error_log Was the log successfully created
	 */
	public static function log( $trace_message = '', $trace_heading = '', $force_log = false, $file_name = 'logs' ) {

		// Only return log if debug mode is on OR if log is forced
		if ( ! $force_log ) {

			if ( ! UO_REPORTING_DEBUG ) {
				return false;
			}
		}

		$timestamp = date( 'F j, Y, g:i a' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

		$current_page_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		$trace_start = "\n===========================<<<< $timestamp >>>>===========================\n";

		$trace_heading = "* Heading: $trace_heading \n";

		$trace_heading .= "* Current Page: $current_page_link \n";

		$trace_end = "\n===========================<<<< TRACE END >>>>===========================\n\n";

		$trace_message = print_r( $trace_message, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		//$file = dirname( self::get_plugin_file() ) . '/uo-' . $file_name . '.log';
		$file = WP_CONTENT_DIR . '/uo-' . $file_name . '.log';

		$error_log = error_log( $trace_start . $trace_heading . $trace_message . $trace_end, 3, $file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		return $error_log;

	}
}
