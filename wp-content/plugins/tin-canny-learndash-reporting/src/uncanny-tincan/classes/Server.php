<?php
/**
 * Server Module ( http(s)://server.com/ucTinCan )
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace UCTINCAN;

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Server {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'check_request' ) );
	}

	/**
	 * Check Request and Process
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function check_request() {
		if ( strpos( $_SERVER['REQUEST_URI'], Init::TINCAN_URL_KEY ) === false ) {
			return;
		}

		$this->process_request();
	}

	/**
	 * Process Request
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function process_request() {
		//error_reporting( 0 );

		do_action( 'tincanny_before_process_request' );

		$this->modify_header();
		$content = $this->get_decoded();

		// Get Resume
		$this->by_request( $content );

		// Get Client and Contents
		$client = $this->get_client();

		$is_app = strstr( $_SERVER['HTTP_USER_AGENT'], 'AdobeAIR/' );

		// Check Nonce
		if ( 'H5P' !== $client && ! $this->get_nonce() && ! $is_app ) {
			header( 'Status: 403 Forbidden' );
			header( 'HTTP/1.1 403 Forbidden' );
			exit();
		}

		if ( isset( $content['content'] ) && ! is_array( $content['content'] ) ) {
			$content = json_decode( $content['content'], true );
		}

		// Get the First Key
		$keys      = array_keys( $content );
		$first_key = array_shift( $keys );

		// Multiple Request
		if ( is_numeric( $first_key ) || 0 === $first_key ) {
			for ( $i = ( count( $content ) - 1 ); $i >= 0; $i -- ) {
				$completion = $this->create_tincan_record( $client, $content[ $i ] );
			}

			// Single Request
		} else {
			$completion = $this->create_tincan_record( $client, $content );
		}

		$this->print_guid( $completion );
	}

	/**
	 * Create TinCan Record (Save Data)
	 *
	 * @access private
	 *
	 * @param  string $client
	 * @param  array  $content
	 *
	 * @return void
	 * @since  1.0.0
	 */
	private function create_tincan_record( $client, $content ) {
		switch ( $client ) {
			case 'Captivate':
			case 'Captivate2017':
				$module = new TinCanRequest\Slides\Captivate( $content );

				return $module->get_completion();

			case 'Storyline':
				$module = new TinCanRequest\Slides\Storyline( $content, $this->get_decoded() );

				return $module->get_completion();

			case 'iSpring':
				$module = new TinCanRequest\Slides\iSpring( $content );

				return $module->get_completion();

			case 'ArticulateRise':
				$module = new TinCanRequest\Slides\ArticulateRise( $content, $this->get_decoded() );

				return $module->get_completion();

			case 'AR2017':
				$module = new TinCanRequest\Slides\ArticulateRise2017( $content, $this->get_decoded() );

				return $module->get_completion();

			/* add Presenter360 tin can format */
			case 'Presenter360':
				$module = new TinCanRequest\Slides\Presenter360( $content, $this->get_decoded() );

				return $module->get_completion();

			/* END Presenter360 */

			/* add Lectora tin can format */
			case 'Lectora':
				$module = new TinCanRequest\Slides\Lectora( $content, $this->get_decoded() );

				return $module->get_completion();
			/* END Lectora */

			/* add Scorm tin can format */
			case 'Scorm':
				$module = new TinCanRequest\Slides\Scorm( $content, $this->get_decoded() );

				return $module->get_completion();
			/* END Scorm */

			/* add Tincan tin can format */
			case 'Tincan':
				$module = new TinCanRequest\Slides\Tincan( $content, $this->get_decoded() );

				return $module->get_completion();
			/* END Tincan */

			case 'H5P':
				$module = new TinCanRequest\H5P( $content );

				return $module->get_completion();
			/* add Scorm tin can format */
			default:
				$module = new TinCanRequest\Slides\Scorm( $content, $this->get_decoded() );

				return $module->get_completion();
			/* END Scorm */
		}
	}

	/**
	 * Modify Document Header
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function modify_header() {
		$domain = apply_filters( 'tincanny_access_control_origin', esc_url_raw( site_url() ) );
		header( 'Access-Control-Allow-Origin: ' . $domain );
		header( 'Access-Control-Allow-Methods: HEAD, GET, POST, PUT, DELETE' );
		header( 'Access-Control-Allow-Headers: X-Experience-API-Version, Authorization, Content-Type, ETag, X-TinCanny-Complete' );
		header( 'Access-Control-Expose-Headers: ETag, X-TinCanny-Complete' );
	}

	/**
	 * Ignore Some Captivate Signal
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function by_request( $content ) {

		$state_id = ultc_get_filter_var( 'stateId', '' );
		if ( isset( $content['stateId'] ) ) {
			$state_id = $content['stateId'];
		}

		if ( $state_id ) {

			$method = ultc_filter_has_var( 'method' ) ? ultc_filter_input( 'method' ) : $_SERVER['REQUEST_METHOD'];
			$url    = isset( $content['activityId'] ) ? $content['activityId'] : $_SERVER['HTTP_REFERER'];

			if ( 'PUT' === $method ) {
				if ( isset( $content['content'] ) ) {
					$content = $content['content'];

				} elseif ( is_array( $content ) && count( $content ) === 1 && empty( array_values( $content )[0] ) ) {
					$content = key( $content );
					$content = str_replace( array( '_html', '_htm' ), array( '.html', '.htm' ), $content );

				} else {
					$content = wp_json_encode( $content );
				}
			}

			if ( $method ) {
				$this->get_state( $method, $url, $state_id, $content );
			}
		}
	}

	/**
	 * Return Client from URL
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function get_client() {
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) && strstr( $_SERVER['HTTP_REFERER'], '&client=' ) !== false ) {
			parse_str( $_SERVER['HTTP_REFERER'], $request_url );

			return ( isset( $request_url['client'] ) ) ? $request_url['client'] : 'H5P';
		}

		if ( isset( $_REQUEST['client'] ) && ! empty( $_REQUEST['client'] ) ) {
			return $_REQUEST['client'];
		}

		$client = $this->get_decoded();

		if ( isset( $client['client'] ) && ! is_array( $client['client'] ) ) {
			$client = $client['client'];
		} elseif ( ! empty( $client['object'] ) && strstr( $client['object']['id'], 'h5p' ) !== false ) {
			$client = 'H5P';
		}

		return $client;
	}

	/**
	 * Get nonce
	 *
	 * @access private
	 * @return bool
	 * @since  1.4.0
	 */
	private function get_nonce() {
		$nonce      = false;
		$protection = 'No';

		// Protection Setting
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) && strstr( $_SERVER['HTTP_REFERER'], '&auth=' ) !== false ) {
			parse_str( $_SERVER['HTTP_REFERER'], $request_url );
			$auth    = $request_url['auth'];
			$post_id = str_replace( 'LearnDashId', '', $auth );

			$postmeta          = get_post_meta( $post_id, '_WE-meta_', true );
			$global_protection = get_option( 'tincanny_nonce_protection', 'yes' );
			$protection        = 'Yes';

			if ( ! empty( $postmeta['protect-scorm-tin-can-modules'] ) ) {
				switch ( $postmeta['protect-scorm-tin-can-modules'] ) {
					case 'Yes':
						$protection = 'Yes';
						break;
					case 'No':
						$protection = 'No';
						break;
					default:
						if ( $global_protection === 'yes' ) {
							$protection = 'Yes';
						}

						if ( $global_protection === 'no' ) {
							$protection = 'No';
						}
						break;
				}
			} else {
				if ( $global_protection === 'no' ) {
					$protection = 'No';
				}
			}

			if ( $protection == 'Yes' ) {
				return true;
			}
		}

		// Check if the content is protected
		// If it isn't, then return true, we don't have to validate the nonce
		if ( $protection == 'No' ) {
			return true;
		}

		// Protection Setting -->

		if ( ! empty( $_SERVER['HTTP_REFERER'] ) && strstr( $_SERVER['HTTP_REFERER'], '&nonce=' ) !== false ) {
			parse_str( $_SERVER['HTTP_REFERER'], $request_url );
			$nonce = $request_url['nonce'];
		} else {
			$decoded = $this->get_decoded();

			if ( isset( $decoded['nonce'] ) && ! is_array( $decoded['nonce'] ) ) {
				$nonce = $decoded['nonce'];
			}
		}

		if ( ! wp_verify_nonce( $nonce, 'tincanny-module' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Parse and Decode php://input
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function get_decoded() {
		$contents = file_get_contents( 'php://input' );
		$decoded  = json_decode( $contents, true );

		if ( ! is_array( $decoded ) ) {
			parse_str( $contents, $decoded );
			$state_id = ultc_get_filter_var( 'stateId', '' );
			if ( 'suspend_data' === $state_id ) {
				if ( count( $decoded ) === 1 ) {
					$decoded = array( $contents => '' );
				}
			}
			if ( 'resume' === $state_id ) {
				if ( count( $decoded ) === 1 ) {
					$decoded = array( $contents => '' );
				}
			}
		}

		return $decoded;
	}

	/**
	 * Get / Save State Data
	 *
	 * @access private
	 * @return void
	 * @since  1.3.6
	 */
	private function get_state( $method, $url, $state_id, $content ) {
		$database = new \UCTINCAN\Database\State();

		switch ( $method ) {
			case 'GET':
				echo $database->get_state( $url, $state_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'PUT':
				$database->save_state( $url, $state_id, $content );
				break;
		}

		die;
	}

	/**
	 * Print GUID and Kill Process
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function print_guid( $completion ) {
		$method_mark_complete_for_tincan = get_option( 'method_mark_complete_for_tincan', 'new' );
		if ( 'old' === $method_mark_complete_for_tincan ) {
			if ( $completion ) {
				echo wp_json_encode( array( '189c3d30-COMP-491a-85ab-1f00c84e651f' ) );
				die;
			} else {
				echo wp_json_encode( array( '189c3d30-FALS-491a-85ab-1f00c84e651f' ) );
				die;
			}
		} else {
			if ( $completion ) {
				if ( ! is_bool( $completion ) ) {
					if ( is_array( $completion ) ) {
						$completion['completion_matched'] = true;
						echo wp_json_encode( $completion );
					} elseif ( intval( $completion ) ) {
						echo wp_json_encode(
							array(
								'content_id'         => $completion,
								'completion_matched' => true,
							)
						);
					} else {
						// check if its already json
						$completion = json_decode( $completion );
						echo wp_json_encode( $completion );
					}
				} else {
					echo wp_json_encode( array( '189c3d30-COMP-491a-85ab-1f00c84e651f' ) );
				}
				die;
			} else {
				echo wp_json_encode( array( '189c3d30-FALS-491a-85ab-1f00c84e651f' ) );
				die;
			}
		}
		// Create a token
		$token = $_SERVER['HTTP_HOST'];
		$token .= $_SERVER['REQUEST_URI'];
		$token .= uniqid( wp_rand(), true );

		// GUID is 128-bit hex
		$hash = md5( $token );

		// Create formatted GUID
		$guid = '';

		// GUID format is XXXXXXXX-XXXX-4XXX-8XXX-8XXXXXXXXXXX for readability
		$guid .= substr( $hash, 0, 8 ) .
			'-' .
			'COMP' .
			'-4' .
			substr( $hash, 13, 3 ) .
			'-8' .
			substr( $hash, 17, 3 ) .
			'-' .
			substr( $hash, 20, 12 );

		echo wp_json_encode( array( $guid ) );
		die;
	}
}
