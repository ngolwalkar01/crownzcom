<?php

namespace uncanny_learndash_reporting;

/**
 * Class ReportingApi
 *
 * @package uncanny_learndash_reporting
 */
class ReportingApi extends Config {

	/**
	 * User Role
	 *
	 * @var null
	 */
	private static $user_role = null;

	/**
	 * Group Leader Group IDs
	 *
	 * @var null
	 */
	private static $group_leaders_group_ids = null;

	/**
	 * Isolated Group ID
	 *
	 * @var int
	 */
	private static $isolated_group_id = 0;

	/**
	 * Course List
	 *
	 * @var null
	 */
	private static $course_list = null;

	/**
	 * Courses User Access
	 *
	 * @var null
	 */
	private static $courses_user_access = null;

	/**
	 * Reporting REST Path
	 *
	 * @var string
	 */
	private static $rest_path = 'uncanny_reporting/v1';

	/**
	 * Temporary Table
	 *
	 * @var string
	 */
	private static $temp_table = 'tbl_reporting_api_user_id';

	/**
	 * Group Leader Groups
	 *
	 * @var array
	 */
	private static $group_leader_groups = array();

	/**
	 * Class constructor
	 */
	public function __construct() {
		//register api class
		add_action( 'rest_api_init', array( __CLASS__, 'reporting_api' ) );
	}

	/**
	 * Create Cache
	 *
	 * @param $key
	 * @param $data
	 */
	public static function create_cache( $key, $data ) {
		wp_cache_set( $key, $data, 'tincanny', 600 );
	}

	/**
	 * Get Cache
	 *
	 * @param $key
	 *
	 * @return array|false|mixed
	 */
	public static function get_cache( $key ) {
		if ( true === apply_filters( 'uo_tincanny_reporting_disable_cache', true ) ) {
			return array();
		}

		return wp_cache_get( $key, 'tincanny' );
	}

	/**
	 * Register Reporting API endpoints
	 */
	public static function reporting_api() {

		if ( ultc_filter_has_var( 'group_id' ) ) {
			self::$isolated_group_id = absint( ultc_filter_input( 'group_id' ) );
		}

		//dashboard_data
		register_rest_route(
			self::$rest_path,
			'/dashboard_data/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_dashboard_data' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		// Call get all courses and general user data
		register_rest_route(
			self::$rest_path,
			'/courses_overview/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_courses_overview' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),
			)
		);

		// Call get all courses and general user data
		register_rest_route(
			self::$rest_path,
			'/table_data/',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'get_table_data' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),
			)
		);

		register_rest_route(
			self::$rest_path,
			'/user_avatar/',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'get_user_avatar' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),
			)
		);

		//
		register_rest_route(
			self::$rest_path,
			'/users_completed_courses/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_users_completed_courses' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		register_rest_route(
			self::$rest_path,
			'/course_modules/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_course_modules' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		register_rest_route(
			self::$rest_path,
			'/assignment_data/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_assignment_data' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		register_rest_route(
			self::$rest_path,
			'/tincan_data/(?P<user_ID>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_tincan_data' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		// Update it the user see the Tin Can tables
		register_rest_route(
			self::$rest_path,
			'/show_tincan/(?P<show_tincan>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'show_tincan_tables' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		// Update it the user see the Tin Can tables
		register_rest_route(
			self::$rest_path,
			'/disable_mark_complete/(?P<disable_mark_complete>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'disable_mark_complete' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		// Update it the user see the Tin Can tables
		register_rest_route(
			self::$rest_path,
			'/nonce_protection/(?P<nonce_protection>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'nonce_protection' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		// Reset Tin Can Data
		register_rest_route(
			self::$rest_path,
			'/reset_tincan_data/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'reset_tincan_data' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		// Reset Quiz Data
		register_rest_route(
			self::$rest_path,
			'/reset_quiz_data/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'reset_quiz_data' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		register_rest_route(
			self::$rest_path,
			'/reset_bookmark_data/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'reset_bookmark_data' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		register_rest_route(
			self::$rest_path,
			'/purge_experienced/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'purge_experienced' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);

		register_rest_route(
			self::$rest_path,
			'/purge_answered/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'purge_answered' ),
				'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

			)
		);
	}

	/**
	 * This is our callback function that allows access to tincanny data
	 *
	 * @return bool|\WP_Error
	 */
	public static function tincanny_permissions() {
		$capability = apply_filters( 'tincanny_can_get_data', 'manage_options' );

		// Restrict endpoint to only users who have the manage_options capability.
		if ( current_user_can( $capability ) ) {
			return true;
		}

		if ( current_user_can( 'group_leader' ) ) {
			return true;
		}

		return new \WP_Error( 'rest_forbidden', esc_html__( 'You do not have the capability to view tincanny data.', 'uncanny-learndash-reporting' ) );
	}

	/**
	 * Get data for the admin dashboard page
	 *
	 * @return array
	 */
	public static function get_dashboard_data() {
		return self::get_courses_overview_data( 'dashboard-only' );
	}

	/**
	 * Collect general user course data and LearnDash Labels
	 *
	 * @return array
	 */
	public static function get_courses_overview() {
		$cache = self::get_cache( 'get_courses_overview' );
		if ( ! empty( $cache ) ) {
			return apply_filters( 'tc_api_get_courses_overview', $cache );
		}
		$start                          = microtime( true );
		$json_return                    = array();
		$json_return['learnDashLabels'] = self::get_labels();
		$json_return['links']           = self::get_links();
		$json_return['get']             = self::$isolated_group_id;
		$json_return['message']         = '';
		$json_return['success']         = true;
		$json_return['data']            = self::course_progress_data();
		$end                            = microtime( true );
		$json_return['get_courses_overview_microtime'] = $end - $start;
		self::create_cache( 'get_courses_overview', $json_return );

		return apply_filters( 'tc_api_get_courses_overview', $json_return );
	}


	/**
	 * Collect general course data
	 *
	 * @return array|bool
	 */
	private static function course_progress_data() {
		$cache = self::get_cache( 'course_progress_data' );
		if ( ! empty( $cache ) ) {
			return $cache;
		}
		$start                                  = microtime( true );
		$data                                   = array(
			'userList'   => self::get_courses_overview_data(),
			'courseList' => self::get_course_list(),
			'success'    => true,
		);
		$end                                    = microtime( true );
		$data['course_progress_data_microtime'] = $end - $start;
		self::create_cache( 'course_progress_data', $data );

		return $data;
	}

	/**
	 * Get Completions
	 *
	 * @param $leader_id
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public static function get_completions( $leader_id ) {
		global $wpdb;
		$temp_table = $wpdb->prefix . self::$temp_table;
		// phpcs:disable WordPress.DB.PreparedSQL
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.post_id as course_id, a.user_id, a.activity_completed
				FROM {$wpdb->prefix}learndash_user_activity a
				JOIN {$temp_table} t ON t.user_id = a.user_id
				WHERE a.activity_type = 'course'
				AND a.activity_completed IS NOT NULL
				AND a.activity_completed <> 0
				AND t.group_leader_id = %d",
				$leader_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 * Get User Data
	 *
	 * @param $leader_id
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public static function get_user_data( $leader_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$temp_table;
		// phpcs:disable WordPress.DB.PreparedSQL
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID, u.display_name, u.user_email, u.user_login, m1.meta_value AS first_name, m2.meta_value AS last_name, m3.meta_value AS user_roles
				FROM {$wpdb->users} u
				JOIN {$table_name} t 
				ON t.user_id = u.ID AND t.group_leader_id = %d
				LEFT JOIN {$wpdb->usermeta} m1
				ON m1.user_id = t.user_id AND m1.meta_key = 'first_name'
				LEFT JOIN {$wpdb->usermeta} m2
				ON m2.user_id = t.user_id AND m2.meta_key = 'last_name'
				LEFT JOIN {$wpdb->usermeta} m3
				ON m3.user_id = t.user_id AND m3.meta_key = %s",
				$leader_id,
				$wpdb->prefix . 'capabilities'
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 * Get Course Overview Data
	 *
	 * @param string $type
	 *
	 * @return mixed|void
	 */
	public static function get_courses_overview_data( $type = 'both' ) {
		$start = microtime( true );
		$cache = self::get_cache( 'uo_get_courses_overview_data' );
		if ( ! empty( $cache ) ) {
			return apply_filters( 'uo_get_courses_overview_data', $cache, $type );
		}
		self::create_temp_table();
		self::drop_temp_table();

		// Get all users from groups
		$return    = array(
			'users_overview'              => array(),
			'completions'                 => array(),
			'in_progress'                 => array(),
			'all_user_ids'                => array(),
			'course_access_count'         => array(),
			'course_access_list'          => array(),
			'course_quiz_averages'        => array(),
			'course_completion_by_dates'  => array(),
			'course_completion_by_course' => array(),
			'dashboard_data'              => array(
				'total_users'              => 0,
				'total_courses'            => 0,
				'top_course_completions'   => array(),
				'localizedStrings'         => array(),
				'learnDashLabels'          => array(),
				'courses_tincan_completed' => array(),
				'report_link'              => '',
			),
		);
		$leader_id = wp_get_current_user()->ID;
		// Get list of all courses
		$course_list = self::get_course_list();
		// Config::log( $course_list, '$course_list', true, '$course_list1112' );
		// Get lists of all groups
		$groups_list         = self::get_groups_list( $leader_id, user_can( $leader_id, 'manage_options' ) );
		$course_access_list  = array();
		$course_access_count = array();
		$all_user_ids        = array();
		global $wpdb;
		$temp_table        = $wpdb->prefix . self::$temp_table;
		$excluded_roles    = apply_filters( 'uo_tincanny_reporting_exclude_roles', array() );
		$excluded_user_ids = apply_filters( 'uo_tincanny_reporting_exclude_user_ids', array() );

		// For admins only
		if ( 0 === self::$isolated_group_id && apply_filters( 'tincanny_view_all_reports_permission', current_user_can( 'manage_options' ) ) ) {
			// Get all users
			$all_user_ids  = get_users(
				array(
					'role__not_in' => $excluded_roles,
					'exclude'      => $excluded_user_ids,
					'fields'       => 'ID',
					'blog_id'      => get_current_blog_id(),
				)
			);
			$leader_groups = isset( self::$group_leader_groups[ $leader_id ] ) ? self::$group_leader_groups[ $leader_id ] : array();
			foreach ( $course_list as $course_id => $course ) {

				if ( ! isset( $course_access_list[ $course_id ] ) ) {
					$course_access_list[ $course_id ] = array();
				}
				// Course access
				if ( ! empty( $course->course_user_access_list ) ) {
					foreach ( $course->course_user_access_list as $user_id ) {
						$course_access_list[ $course_id ][ (int) $user_id ] = (int) $user_id;
					}
				}
				//Group access
				if ( ! empty( $leader_groups ) ) {
					foreach ( $leader_groups as $group_id ) {
						$_group_courses = isset( $groups_list[ $group_id ]['groups_course_access'] ) ? array_map( 'absint', $groups_list[ $group_id ]['groups_course_access'] ) : array();
						if ( in_array( $course_id, $_group_courses, true ) ) {
							$_group_users = isset( $groups_list[ $group_id ]['groups_user'] ) ? array_map( 'absint', $groups_list[ $group_id ]['groups_user'] ) : array();
							if ( ! empty( $_group_users ) ) {
								foreach ( $_group_users as $group_user_id ) {
									$course_access_list[ $course_id ][ (int) $group_user_id ] = (int) $group_user_id;
								}
							}
						}
					}
				}
			}
		} elseif ( 0 === self::$isolated_group_id && learndash_is_group_leader_user( wp_get_current_user() ) ) {
			$leader_groups = isset( self::$group_leader_groups[ $leader_id ] ) ? self::$group_leader_groups[ $leader_id ] : array();
			if ( empty( $leader_groups ) ) {
				return $return;
			}

			// Get data from group data
			foreach ( $leader_groups as $group_id ) {
				$group_users = isset( $groups_list[ $group_id ]['groups_user'] ) ? array_map( 'absint', $groups_list[ $group_id ]['groups_user'] ) : array();

				if ( ! empty( $group_users ) ) {
					$all_user_ids = array_merge( $all_user_ids, $group_users );
				}
				$group_courses = isset( $groups_list[ $group_id ]['groups_course_access'] ) ? array_map( 'absint', $groups_list[ $group_id ]['groups_course_access'] ) : array();
				if ( ! empty( $group_courses ) ) {
					foreach ( $group_courses as $course_id ) {
						foreach ( $group_users as $user_id ) {
							$course_access_list[ $course_id ][ (int) $user_id ] = (int) $user_id;
						}
					}
				}
			}
		} elseif ( 0 !== self::$isolated_group_id && ( current_user_can( 'manage_options' ) || learndash_is_group_leader_user( wp_get_current_user() ) ) ) {
			$all_user_ids  = isset( $groups_list[ self::$isolated_group_id ]['groups_user'] ) ? array_map( 'absint', $groups_list[ self::$isolated_group_id ]['groups_user'] ) : array();
			$group_courses = isset( $groups_list[ self::$isolated_group_id ]['groups_course_access'] ) ? array_map( 'absint', $groups_list[ self::$isolated_group_id ]['groups_course_access'] ) : array();
			if ( ! empty( $group_courses ) && ! empty( $all_user_ids ) ) {
				foreach ( $group_courses as $course_id ) {
					foreach ( $all_user_ids as $user_id ) {
						$course_access_list[ $course_id ][ (int) $user_id ] = (int) $user_id;
					}
				}
			}
		}

		// eliminating duplicates
		$all_user_ids = array_unique( $all_user_ids );

		if ( empty( $all_user_ids ) ) {
			return $return;
		}
		// Exclude IDs sent by the filter
		if ( ! empty( $excluded_user_ids ) ) {
			$excluded_user_ids = array_map( 'absint', $excluded_user_ids );
			foreach ( $all_user_ids as $k => $user_id ) {
				if ( in_array( $user_id, $excluded_user_ids, true ) ) {
					unset( $all_user_ids[ $k ] );
				}
			}
		}

		// Store user IDs in the DB for future queries
		$injected_user_ids = self::inject_group_leader_id( $all_user_ids, $leader_id );
		self::insert_user_ids_in_temp_table( $injected_user_ids );
		unset( $injected_user_ids );

		unset( $groups_list );

		$user_data = self::get_user_data( $leader_id );

		// Config::log( $course_access_list, '$course_access_list', true, '$course_access_list' );
		if ( empty( $user_data ) ) {
			return $return;
		}

		$all_user_data_rearranged = array();
		$filtered_user_ids        = array();
		foreach ( $user_data as $user ) {
			$user->enrolled    = 0;
			$user->in_progress = 0;
			$user->completed   = 0;
			$roles             = maybe_unserialize( $user->user_roles );
			$user->roles       = is_array( $roles ) ? array_keys( $roles ) : array();
			unset( $user->user_roles );
			// If the role is excluded, no need to add user data
			if ( is_array( $user->roles ) && is_array( $excluded_roles ) && array_intersect( $user->roles, $excluded_roles ) ) {
				$filtered_user_ids[] = $user->ID;
				continue;
			}
			$all_user_data_rearranged[ (int) $user->ID ] = $user;
		}
		unset( $user_data );
		if ( ! empty( $filtered_user_ids ) ) {
			$filtered_user_ids = array_map( 'absint', $filtered_user_ids );
			self::remove_user_id_by_role_exclusion( $filtered_user_ids, $leader_id );
		}

		$all_user_ids_rearranged = array();
		foreach ( $all_user_ids as $user_id ) {
			if ( ! empty( $filtered_user_ids ) && in_array( $user_id, $filtered_user_ids, true ) ) {
				continue;
			}
			$all_user_ids_rearranged[ $user_id ] = $user_id;
		}
		// Config::log( $all_user_ids_rearranged, '$all_user_ids_rearranged', true, '$all_user_ids_rearranged' );
		unset( $all_user_ids );

		$dashboard_data_object['total_users'] = count( $all_user_ids_rearranged );

		$course_users = array();
		// Config::log( $course_access_list, '$course_access_list', true, 'get_course_users' );
		// Config::log( $course_list, '$course_list', true, 'get_course_users' );
		// Config::log( $users, '$users', true, 'get_course_users' );
		// Config::log( $all_user_ids_rearranged, '$all_user_ids_rearranged', true, 'get_course_users' );

		foreach ( $course_access_list as $course_id => $users ) {
			$course_access_count[ $course_id ] = count( $users );
			$course_price_type                 = $course_list[ (int) $course_id ]->course_price_type;

			if ( 'open' === $course_price_type ) {
				foreach ( $all_user_data_rearranged as $user_id => $data ) {
					if ( isset( $all_user_data_rearranged[ (int) $user_id ] ) ) {
						$all_user_data_rearranged[ (int) $user_id ]->enrolled ++;
					}
				}
			}

			$course_users_temp = array();
			foreach ( $users as $user_id => $user_id_ ) {
				if ( isset( $all_user_ids_rearranged[ $user_id ] ) ) {
					$course_users_temp[ $user_id ] = $user_id;
					if ( 'open' !== $course_price_type ) {
						if ( isset( $all_user_data_rearranged[ (int) $user_id ] ) ) {
							$all_user_data_rearranged[ (int) $user_id ]->enrolled ++;
						}
					}
				}
			}
			$course_users[ $course_id ] = $course_users_temp;

		}

		// Completion
		$completions = self::get_completions( $leader_id );
		// Config::log( $completions, '$completions', true, '$completions' );
		//      if ( empty( $completions ) ) {
		//          return $return;
		//      }
		$completions_rearranged                          = array();
		$dashboard_data_object['top_course_completions'] = array();
		$completions_by_date                             = array();
		$completions_by_course                           = array();
		if ( ! empty( $completions ) ) {
			foreach ( $completions as $completion ) {
				if ( ! isset( $all_user_data_rearranged[ (int) $completion->user_id ] ) ) {
					continue;
				}
				if ( ! isset( $course_access_list[ $completion->course_id ] ) ) {
					continue;
				}

				$course_price_type = $course_list[ $completion->course_id ]->course_price_type;

				if ( ! isset( $all_user_ids_rearranged[ (int) $completion->user_id ] ) ) {
					continue;
				}

				if ( isset( $course_access_list[ $completion->course_id ][ $completion->user_id ] ) || 'open' === $course_price_type ) {

					$completed_on_date = date( 'Y-m-d', $completion->activity_completed ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

					if ( ! isset( $completions_by_course[ $completion->course_id ] ) ) {
						$completions_by_course[ $completion->course_id ] = array();
					}

					$all_user_data_rearranged[ (int) $completion->user_id ]->completed ++;
					$all_user_data_rearranged[ (int) $completion->user_id ]->completed_on[ $completion->course_id ] = array(
						'display'   => learndash_adjust_date_time_display( $completion->activity_completed ),
						'timestamp' => (string) $completion->activity_completed,
					);

					if ( ! isset( $completions_by_course[ $completion->course_id ][ $completed_on_date ] ) ) {
						$completions_by_course[ $completion->course_id ][ $completed_on_date ] = array( $completion->user_id );
					} else {
						$completions_by_course[ $completion->course_id ][ $completed_on_date ][] = $completion->user_id;
					}

					if ( ! isset( $completions_by_date[ $completed_on_date ] ) ) {
						$completions_by_date[ $completed_on_date ] = 1;
					} else {
						$completions_by_date[ $completed_on_date ] ++;
					}

					if ( ! isset( $dashboard_data_object['top_course_completions'][ $completion->course_id ] ) ) {
						$completions_rearranged[ $completion->course_id ]                          = 1;
						$dashboard_data_object['top_course_completions'][ $completion->course_id ] = array(
							'post_title'              => $course_list[ $completion->course_id ]->post_title,
							'course_price_type'       => $course_list[ $completion->course_id ]->course_price_type,
							'course_user_access_list' => ( 'open' === $course_price_type ) ? $all_user_ids_rearranged : $course_users[ $completion->course_id ],
							'completions'             => 1,
						);
					} else {
						$completions_rearranged[ $completion->course_id ] ++;
						$dashboard_data_object['top_course_completions'][ $completion->course_id ]['completions'] ++;
					}
				} else {
					if ( ! isset( $dashboard_data_object['top_course_completions'][ $completion->course_id ] ) ) {
						$completions_rearranged[ $completion->course_id ]                          = 0;
						$dashboard_data_object['top_course_completions'][ $completion->course_id ] = array(
							'post_title'              => $course_list[ $completion->course_id ]->post_title,
							'course_price_type'       => $course_list[ $completion->course_id ]->course_price_type,
							'course_user_access_list' => ( 'open' === $course_price_type ) ? $all_user_ids_rearranged : $course_users[ $completion->course_id ],
							'completions'             => 0,
						);
					}
				}
			}
		}

		foreach ( $course_list as $course_id => $course ) {
			if ( ! isset( $dashboard_data_object['top_course_completions'][ $course_id ] ) ) {
				$course_price_type = $course->course_price_type;
				$dashboard_data_object['top_course_completions'][ $course_id ] = array(
					'post_title'              => $course->post_title,
					'course_price_type'       => $course->course_price_type,
					'course_user_access_list' => ( 'open' === $course_price_type ) ? $all_user_ids_rearranged : ( isset( $course_users[ $course_id ] ) ? $course_users[ $course_id ] : array() ),
					'completions'             => 0,
				);
			}
		}

		// In-progress
		// phpcs:disable WordPress.DB.PreparedSQL
		$in_progress = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.post_id as course_id, a.user_id
				FROM {$wpdb->prefix}learndash_user_activity a
				JOIN {$temp_table} t ON t.user_id = a.user_id AND t.group_leader_id = %d
				WHERE a.activity_type = 'course'
				AND ( a.activity_completed = 0 OR a.activity_completed IS NULL )
				AND ( a.activity_started != 0 OR a.activity_updated != 0 )",
				$leader_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL

		$in_progress_rearranged = array();

		foreach ( $in_progress as $progress ) {
			if ( ! isset( $all_user_data_rearranged[ (int) $progress->user_id ] ) ) {
				continue;
			}
			if (
			isset( $course_access_list[ $progress->course_id ] ) &&
			isset(
				$course_access_list[ $progress->course_id ][ (int) $progress->user_id ]
			)
			||
			(
				isset( $course_list[ $progress->course_id ] ) &&
				'open' === $course_list[ $progress->course_id ]->course_price_type &&
				isset( $all_user_ids_rearranged[ (int) $progress->user_id ] )
			)
			) {

				if ( ! isset( $in_progress_rearranged[ $progress->course_id ] ) ) {
					$in_progress_rearranged[ $progress->course_id ] = 1;
				} else {
					$in_progress_rearranged[ $progress->course_id ] ++;
				}

				if ( isset( $all_user_data_rearranged[ (int) $progress->user_id ] ) ) {
					$all_user_data_rearranged[ (int) $progress->user_id ]->in_progress ++;
				}
			}
		}
		//$user_ids_extracted = array_keys( $all_user_data_rearranged );
		unset( $in_progress );
		unset( $course_list );

		// phpcs:disable WordPress.DB.PreparedSQL
		$quiz_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.course_id, a.post_id, m.activity_meta_value as activity_percentage, a.user_id
				FROM {$wpdb->prefix}learndash_user_activity a
				JOIN {$temp_table} t
					ON t.user_id = a.user_id AND t.group_leader_id = %d
				LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
				WHERE a.activity_type = 'quiz'
				AND m.activity_meta_key = 'percentage'",
				$leader_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL

		$course_quiz_average = array();
		foreach ( $course_access_list as $course_id => $users ) {
			$course_quiz_average[ $course_id ] = self::get_course_quiz_average( $course_id, $quiz_results, $all_user_ids_rearranged );
		}

		unset( $quiz_results );

		$dashboard_data_object['total_courses'] = count( $course_access_list );

		usort(
			$dashboard_data_object['top_course_completions'],
			function ( $a, $b ) {
				return $b['completions'] - $a['completions'];
			}
		);

		$completions                = array();
		$course_completion_by_dates = array();

		// min max date
		foreach ( $completions_by_date as $date => $amount_completions ) {
			$object              = new \stdClass();
			$object->date        = $date;
			$object->completions = $amount_completions;
			if ( $amount_completions > 0 ) {
				array_push( $completions, $object );
				array_push( $course_completion_by_dates, $object );
			}
		}

		unset( $completions_by_date );

		$course_completion_by_course = array();
		foreach ( $completions_by_course as $completion_course_id => $data ) {
			$course_completion_by_course[ $completion_course_id ] = array();
			foreach ( $data as $date => $count ) {

				$object              = new \stdClass();
				$object->date        = $date;
				$object->completions = count( $count );
				if ( count( $count ) > 0 ) {
					array_push( $course_completion_by_course[ $completion_course_id ], $object );
				}
			}
		}

		unset( $completions_by_course );
		// phpcs:disable WordPress.DB.PreparedSQL
		$tin_can_completed = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT x.xstored, x.user_id, x.course_id
				FROM {$wpdb->prefix}uotincan_reporting x
				JOIN {$temp_table} t
					ON t.user_id = x.user_id AND t.group_leader_id = %d
				WHERE x.xstored >= NOW() - INTERVAL 1 MONTH",
				$leader_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL

		$temp_array = array();
		foreach ( $tin_can_completed as $completion ) {

			if ( 'group_leader' === self::get_user_role() || 0 !== self::$isolated_group_id ) {
				if ( ! isset( $all_user_ids_rearranged[ $completion->user_id ] ) ) {
					continue;
				}
				if ( ! isset( $course_access_list[ $completion->course_id ] ) ) {
					continue;
				}
			}

			$date = date( 'Y-m-d', strtotime( $completion->xstored ) );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			if ( ! isset( $temp_array[ $date ] ) ) {
				$temp_array[ $date ] = 1;
			} else {
				$temp_array[ $date ] ++;
			}
		}

		unset( $tin_can_completed );

		$tin_can_stored = array();
		foreach ( $temp_array as $date => $amount_completions ) {
			$object         = new \stdClass();
			$object->date   = $date;
			$object->tinCan = $amount_completions; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( $amount_completions > 0 ) {
				array_push( $tin_can_stored, $object );
			}
		}

		unset( $temp_array );

		$courses_tincan_completed = array_merge( $tin_can_stored, $completions );

		unset( $tin_can_stored );
		unset( $completions );

		usort(
			$courses_tincan_completed,
			function ( $a, $b ) {
				return strtotime( $a->date ) - strtotime( $b->date );
			}
		);

		$dashboard_data_object['courses_tincan_completed'] = $courses_tincan_completed;
		$dashboard_data_object['report_link']              = admin_url( 'admin.php?page=uncanny-learnDash-reporting' );
		$dashboard_data_object['learnDashLabels']          = self::get_labels();

		// TODO Might no need this
		$dashboard_data_object['localizedStrings'] = array(
			'Loading Dashboard Report' => 'xLoading Dashboard Report',
			'Total Users'              => 'xTotal Users',
		);

		if ( 'both' === $type ) {
			$return['users_overview']              = $all_user_data_rearranged;
			$return['completions']                 = $completions_rearranged;
			$return['in_progress']                 = $in_progress_rearranged;
			$return['all_user_ids']                = $all_user_ids_rearranged;
			$return['course_access_count']         = $course_access_count;
			$return['course_access_list']          = $course_access_list;
			$return['dashboard_data']              = $dashboard_data_object;
			$return['course_quiz_averages']        = $course_quiz_average;
			$return['course_completion_by_dates']  = $course_completion_by_dates;
			$return['course_completion_by_course'] = $course_completion_by_course;
		}

		if ( 'dashboard-only' === $type ) {
			$return = $dashboard_data_object;
		}

		if ( 'report-only' === $type ) {
			$return['users_overview']              = $all_user_data_rearranged;
			$return['completions']                 = $completions_rearranged;
			$return['in_progress']                 = $in_progress_rearranged;
			$return['all_user_ids']                = $all_user_ids_rearranged;
			$return['course_access_count']         = $course_access_count;
			$return['course_access_list']          = $course_access_list;
			$return['course_quiz_averages']        = $course_quiz_average;
			$return['course_completion_by_dates']  = $course_completion_by_dates;
			$return['course_completion_by_course'] = $course_completion_by_course;
		}

		unset( $course_access_list );
		//self::drop_temp_table();

		self::create_cache( 'uo_get_courses_overview_data', $return );
		$end = microtime( true );
		//Config::log( $end - $start, 'microtime', true, 'tincanny_microtime' );
		$return['microtime'] = $end - $start;

		/**
		 * Filters the course overview data
		 */
		return apply_filters( 'uo_get_courses_overview_data', $return, $type );
	}

	/**
	 * Remove user IDs by role exclusion
	 *
	 * @param $filtered_user_ids
	 * @param $leader_id
	 *
	 * @return void
	 */
	public static function remove_user_id_by_role_exclusion( $filtered_user_ids, $leader_id ) {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL
		$temp_table = $wpdb->prefix . self::$temp_table;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$temp_table} WHERE group_leader_id = %d AND user_id IN (%s)",
				$leader_id,
				join( ', ', $filtered_user_ids )
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 * Get users completed courses
	 *
	 * @return array
	 */
	public static function get_users_completed_courses() {
		$json_return            = array();
		$json_return['success'] = false;

		$json_return['message'] = __( 'You do not have permission to access this information', 'uncanny-learndash-reporting' );
		$json_return['data']    = array();

		global $wpdb;

		// check current user if admin or group leader
		if ( current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {

			// Modify custom query to restrict data to group leaders available data
			if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

				// Verify the group leader has groups assigned
				if ( ! count( self::get_administrators_group_ids() ) ) {

					$json_return['message'] = __( 'Group Leader has no groups assigned', 'uncanny-learndash-reporting' );
					$json_return['success'] = false;

					return $json_return;
				}

				$leader_groups = self::get_administrators_group_ids();
				if ( ! empty( $leader_groups ) ) {
					foreach ( $leader_groups as $group_id ) {
						// restrict group leader to a single group it its set
						// @REVIEW - This condition seems useless both set the same value.
						if ( self::$isolated_group_id && (int) $group_id === (int) self::$isolated_group_id ) {
							$meta_keys[] = "'learndash_group_users_" . $group_id . "'";
						} else {
							$meta_keys[] = "'learndash_group_users_" . $group_id . "'";
						}
					}
					$imploded_meta_keys             = implode( ',', $meta_keys );
					$restrict_group_leader_usermeta = "AND user_id IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key IN ($imploded_meta_keys) )";
				} else {
					$restrict_group_leader_usermeta = '';
				}
			} elseif ( self::$isolated_group_id ) {
				$restrict_group_leader_usermeta = "AND user_id IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'learndash_group_users_" . self::$isolated_group_id . "' )";
			} else {
				$restrict_group_leader_usermeta = '';
			}

			if ( is_multisite() ) {

				$blog_id = get_current_blog_id();

				$base_capabilities_key = $wpdb->base_prefix . 'capabilities';
				$site_capabilities_key = $wpdb->base_prefix . $blog_id . '_capabilities';
				$key                   = 1 === $blog_id ? $base_capabilities_key : $site_capabilities_key;
				$restrict_to_blog      = "AND user_id IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '{$key}')";

			} else {
				$restrict_to_blog = '';
			}

			// Get all user data
			// Users' Progress
			$sql_string = "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key LIKE 'course_completed_%' $restrict_group_leader_usermeta $restrict_to_blog";

			$courses_completed = $wpdb->get_results( $sql_string ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( is_array( $courses_completed ) ) {
				$json_return['message'] = '';
				$json_return['success'] = true;
				foreach ( $courses_completed as $course_completed ) {
					$user_id                           = (int) $course_completed->user_id;
					$course_id                         = explode( '_', $course_completed->meta_key );
					$course_id                         = (int) $course_id[2];
					$time_stamp                        = (int) $course_completed->meta_value;
					$date_format                       = 'Y-m-d';
					$json_return['data'][ $user_id ][] = array( $course_id, date( $date_format, $time_stamp ) ); // // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				}
			}
		}

		return $json_return;
	}


	/**
	 * Get Course List
	 *
	 * @return array|null
	 */
	public static function get_course_list() {

		if ( null !== self::$course_list ) {
			return self::$course_list;
		}

		global $wpdb;

		//$group_id                               = 0;
		$groups_list                            = array();
		$group_courses                          = array();
		$restrict_group_leader_post             = '';
		$restrict_group_leader_postmeta         = '';
		$restrict_group_leader_associated_posts = '';
		if ( apply_filters( 'tincanny_view_all_reports_permission', current_user_can( 'manage_options' ) ) && 0 === self::$isolated_group_id ) {
			$course_list = get_posts(
				array(
					'post_type'      => 'sfwd-courses',
					'posts_per_page' => 99999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'fields'             => 'ids',
				)
			);
		} else {
			if ( 0 !== self::$isolated_group_id ) {
				$groups_list[] = self::$isolated_group_id;
			} else {
				$groups_list = self::get_administrators_group_ids();
			}
			if ( empty( $groups_list ) ) {
				return array();
			}
			foreach ( $groups_list as $group_id ) {
				$__courses = learndash_group_enrolled_courses( $group_id );
				if ( ! empty( $__courses ) ) {
					foreach ( $__courses as $__course_id ) {
						$group_courses[] = $__course_id;
					}
				}
			}
			$course_list = array_unique( $group_courses );
			unset( $group_courses );
		}

		$restrict_group_leader_post             = apply_filters_deprecated(
			'uo_tincanny_reporting_restrict_group_leader_post',
			array(
				$restrict_group_leader_post,
				0,
			),
			4.2,
			'uo_tincanny_reporting_list_of_course_ids'
		);
		$restrict_group_leader_postmeta         = apply_filters_deprecated(
			'uo_tincanny_reporting_restrict_group_leader_postmeta',
			array(
				$restrict_group_leader_postmeta,
				0,
			),
			4.2,
			'uo_tincanny_reporting_list_of_course_ids'
		);
		$restrict_group_leader_associated_posts = apply_filters_deprecated(
			'uo_tincanny_reporting_restrict_group_leader_associated_posts',
			array(
				$restrict_group_leader_associated_posts,
				0,
			),
			4.2,
			'uo_tincanny_reporting_list_of_course_ids'
		);

		$course_list            = apply_filters( 'uo_tincanny_reporting_list_of_course_ids', $course_list );
		$rearranged_course_list = array();

		foreach ( $course_list as $course ) {
			$__course                                      = get_post( $course );
			$rearranged_course_list[ $course ]             = new \stdClass();
			$rearranged_course_list[ $course ]->ID         = $__course->ID;
			$rearranged_course_list[ $course ]->post_title = $__course->post_title;
			$rearranged_course_list[ $course ]->post_name  = $__course->post_name;
		}

		// Course settings
		if ( ! empty( $course_list ) ) {
			// Query 1
			$sql_string      = "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_sfwd-courses' AND post_id IN (" . join( ', ', $course_list ) . ')';
			$course_settings = $wpdb->get_results( $sql_string ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			foreach ( $course_settings as $course_setting ) {

				$course_id = (int) $course_setting->post_id;

				$courses_settings_values = maybe_unserialize( $course_setting->meta_value );

				if ( is_array( $courses_settings_values ) ) {

					$rearranged_course_list[ $course_id ]->course_user_access_list = self::course_user_access( $course_id );

					foreach ( $courses_settings_values as $key => $value ) {
						if ( 'sfwd-courses_course_price_type' === $key ) {
							$rearranged_course_list[ $course_id ]->course_price_type = $value;
						}
					}
				}
				if ( isset( $rearranged_course_list[ $course_id ] ) && isset( $rearranged_course_list[ $course_id ]->course_user_access_list ) ) {
					$rearranged_course_list[ $course_id ]->enrolled_users = count( $rearranged_course_list[ $course_id ]->course_user_access_list );
				}
				// Default value set if course price type settings not found
				if ( ! isset( $rearranged_course_list[ $course_id ]->course_price_type ) ) {
					$rearranged_course_list[ $course_id ]->course_price_type = 'open';
				}
			}
		}
		// Course associated LearnDash Posts
		// Modify custom query to restrict data to group leaders available data
		$courses_posts = array();
		if ( ! empty( $course_list ) ) {
			$sql_string    = "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = 'course_id' OR meta_key LIKE 'ld_course_%' AND post_id IN (" . join( ', ', $course_list ) . ')';
			$courses_posts = $wpdb->get_results( $sql_string );// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			foreach ( $courses_posts as $course_post ) {

				$sub_post_id       = (int) $course_post->post_id;
				$associated_course = (int) $course_post->meta_value;

				if ( ! array_key_exists( $associated_course, $rearranged_course_list ) ) {
					continue;
				}

				// make sure that there is an associate course
				if ( 0 === $associated_course ) {
					continue;
				}
				if ( ! isset( $rearranged_course_list[ $associated_course ]->associatedPosts ) ) {
					$rearranged_course_list[ $associated_course ]->associatedPosts = array();
				}

				array_push( $rearranged_course_list[ $associated_course ]->associatedPosts, $sub_post_id );

			}
		}

		self::$course_list = $rearranged_course_list;

		return $rearranged_course_list;
	}

	/**
	 * Get Groups List
	 *
	 * @param int $leader_id
	 * @param bool $has_admin_access
	 *
	 * @return array
	 */
	public static function get_groups_list( $leader_id, $has_admin_access = false ) {
		$rearrange_group_list = array();
		// Admin group lists
		if ( 0 === self::$isolated_group_id && ( $has_admin_access || learndash_is_group_leader_user( $leader_id ) ) ) {
			$groups = self::learndash_get_administrators_group_ids( $leader_id, $has_admin_access );
			if ( ! isset( self::$group_leader_groups[ $leader_id ] ) ) {
				self::$group_leader_groups[ $leader_id ] = $groups;
			}
			if ( $groups ) {
				foreach ( $groups as $group_id ) {
					$rearrange_group_list[ $group_id ]['ID']                   = $group_id;
					$rearrange_group_list[ $group_id ]['post_title']           = get_the_title( $group_id );
					$rearrange_group_list[ $group_id ]['groups_course_access'] = learndash_group_enrolled_courses( $group_id );
					$rearrange_group_list[ $group_id ]['groups_user']          = self::learndash_get_groups_user_ids( $group_id );
				}
			}
		} elseif ( 0 !== self::$isolated_group_id && ( $has_admin_access || learndash_is_group_leader_user( $leader_id ) ) ) {
			// Specific group
			if ( ! isset( self::$group_leader_groups[ $leader_id ] ) ) {
				self::$group_leader_groups[ $leader_id ] = self::$isolated_group_id;
			}
			$group_id = self::$isolated_group_id;

			$rearrange_group_list[ $group_id ]['ID']                   = $group_id;
			$rearrange_group_list[ $group_id ]['post_title']           = get_the_title( $group_id );
			$rearrange_group_list[ $group_id ]['groups_course_access'] = learndash_group_enrolled_courses( $group_id );
			$rearrange_group_list[ $group_id ]['groups_user']          = self::learndash_get_groups_user_ids( $group_id );
		}

		return $rearrange_group_list;
	}

	/**
	 * LearnDash get administrators group IDs
	 *
	 * @param $leader_id
	 * @param bool $has_admin_access - Allows for custom role checks
	 *
	 * @return array
	 */
	public static function learndash_get_administrators_group_ids( $leader_id, $has_admin_access = false) {

		// Return all groups if admin user.
		if ( $has_admin_access || learndash_is_admin_user( $leader_id ) ) {
			$group_ids = get_posts(
				array(
					'post_type'      => learndash_get_post_type_slug( 'group' ),
					'posts_per_page' => 99999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'fields'             => 'ids',
				)
			);
			return ! empty( $group_ids ) ? $group_ids : array();
		}

		// Query for group IDs by leaders meta key.
		global $wpdb;
		$group_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key LIKE %s AND user_id = %d", 'learndash_group_leaders_%%', $leader_id ) );
		$group_ids = ! empty( $group_ids ) ? array_unique( $group_ids, SORT_NUMERIC ) : array();

		// If hierarchical groups are enabled, get all child groups.
		if ( learndash_is_groups_hierarchical_enabled() ) {
			$all_children = self::get_all_learndash_group_child_posts_ids();
			if ( ! empty( $all_children ) ) {
				foreach ( $group_ids as $group_id ) {
					$children_ids = self::learndash_get_group_children_ids( $group_id, $all_children );
					if ( ! empty( $children_ids ) ) {
						$group_ids = array_merge( $group_ids, $children_ids );
					}
				}
				$group_ids = array_unique( $group_ids, SORT_NUMERIC );
			}
		}

		return $group_ids;
	}

	/**
	 * LearnDash get group children IDs
	 *
	 * @param $group_id
	 *
	 * @return array
	 */
	public static function learndash_get_group_children_ids( $group_id, $all_children = null ) {

		$group_id = absint( $group_id );
		if ( empty( $group_id ) ) {
			return array();
		}

		if ( is_null( $all_children ) ) {
			$all_children = self::get_all_learndash_group_child_posts_ids();
		}

		if ( empty( $all_children ) ) {
			return array();
		}

		$children = array();
		foreach ( $all_children as $child ) {
			if ( $child->post_parent === $group_id ) {
				$children[] = $child->ID;
				$children2  = self::learndash_get_group_children_ids( $child->ID, $all_children );
				if ( ! empty( $children2 ) ) {
					$children = array_merge( $children, $children2 );
				}
			}
		}

		return ! empty( $children ) ? array_unique( $children, SORT_NUMERIC ) : array();
	}

	/**
	 * Return all child groups
	 *
	 * @return array - array of child group objects with ID and post_parent
	 */
	public static function get_all_learndash_group_child_posts_ids() {

		static $all_children = null;
		if ( is_null( $all_children ) ) {
			$children_query = new \WP_Query(
				array(
					'post_type'           => learndash_get_post_type_slug( 'group' ),
					'post_parent__not_in' => array( 0 ), // Only retrieve child posts
					'posts_per_page'      => 99999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'fields'                  => 'id=>parent', // Retrieve only ID and post_parent
				)
			);
			$all_children   = $children_query->have_posts() ? $children_query->posts : array();
			wp_reset_postdata();
		}

		return $all_children;
	}

	/**
	 * LearnDash get groups user IDs
	 *
	 * @param $group_id
	 *
	 * @return array
	 */
	public static function learndash_get_groups_user_ids( $group_id ) {
		global $wpdb;

		return $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s", 'learndash_group_users_' . $group_id ) );
	}

	/**
	 * Course user access
	 *
	 * @param $course_id
	 *
	 * @return array|mixed
	 */
	public static function course_user_access( $course_id ) {
		$users = learndash_get_course_users_access_from_meta( $course_id );
		if ( empty( $users ) ) {
			return array();
		}

		return array_map( 'absint', array_unique( $users ) );
	}

	/**
	 * Collect general user course data and LearnDash Labels
	 *
	 * @return array
	 */
	public static function get_table_data() {

		$json_return            = array();
		$json_return['message'] = '';
		$json_return['success'] = true;
		$json_return['data']    = array();

		$table_type = ultc_get_filter_var( 'tableType', '', INPUT_POST );

		switch ( $table_type ) {
			case 'courseSingleTable':
				$json_return['success'] = false;
				$json_return['data']    = $_POST; // phpcs:ignore WordPress.Security

				if ( ultc_filter_has_var( 'courseId', INPUT_POST ) && ultc_filter_has_var( 'rows', INPUT_POST ) ) {

					$course_id = absint( ultc_filter_input( 'courseId', INPUT_POST ) );
					$rows      = array();
					$post_rows = array();

					// phpcs:disable WordPress.Security
					if ( is_string( $_POST['rows'] ) ) {
						$post_rows = json_decode( stripslashes( ultc_filter_input( 'rows', INPUT_POST ) ), true );
					} elseif ( is_array( $_POST['rows'] ) ) {
						$post_rows = ultc_filter_input_array( 'rows', INPUT_POST );
					}

					if ( ! empty( $post_rows ) ) {
						foreach ( $post_rows as $row ) {
							if ( isset( $row['rowId'], $row['ID'] ) ) {
								$rows[ absint( $row['rowId'] ) ] = absint( $row['ID'] );
							}
						}
					}
					// phpcs:enable WordPress.Security

					$json_return['message'] = '';
					$json_return['success'] = true;
					$json_return['data']    = self::get_course_single_overview( $course_id, $rows );

					return apply_filters( 'tc_api_get_courseSingleTable', $json_return, $course_id, $rows ); // phpcs:ignore WordPress.NamingConventions.ValidHookName

				} else {
					$json_return['message'] = 'courseId or rowsIds not set';
				}

				return $json_return;
			case 'userSingleCoursesOverviewTable':
				$json_return['message'] = 'userId or rowsIds not set';
				$json_return['success'] = false;
				$json_return['data']    = $_POST; // phpcs:ignore WordPress.Security

				if ( ultc_filter_has_var( 'userId', INPUT_POST ) ) {

					$user_id     = absint( ultc_filter_input( 'userId', INPUT_POST ) );
					$rows        = array();
					$posted_rows = ultc_filter_input_array( 'rows', INPUT_POST );
					if ( is_null( $posted_rows ) ) {
						$posted_rows = json_decode( stripslashes( ultc_filter_input( 'rows', INPUT_POST ) ), true );
					}
					foreach ( $posted_rows as $row ) {
						$rows[ absint( $row['rowId'] ) ] = absint( $row['ID'] );
					}

					$json_return['message'] = '';
					$json_return['success'] = true;
					$json_return['user_id'] = $user_id;
					$json_return['data']    = self::get_user_single_overview( $user_id, $rows );

					return apply_filters( 'tc_api_get_userSingleCoursesOverviewTable', $json_return, $user_id, $rows ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
				}

				return $json_return;
			case 'userSingleCourseProgressSummaryTable':
				$json_return['message'] = 'userId or courseId not set';
				$json_return['success'] = false;
				$json_return['data']    = $_POST;// phpcs:ignore WordPress.Security

				if ( ultc_filter_has_var( 'userId', INPUT_POST ) && ultc_filter_has_var( 'courseId', INPUT_POST ) ) {
					$user_id   = absint( ultc_filter_input( 'userId', INPUT_POST ) );
					$course_id = absint( ultc_filter_input( 'courseId', INPUT_POST ) );

					$json_return['message'] = '';
					$json_return['success'] = true;
					$json_return['data']    = self::get_user_single_course_overview( $user_id, $course_id );

					return apply_filters( 'tc_api_get_userSingleCourseProgressSummaryTable', $json_return, $user_id, $course_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
				}

				return $json_return;
			default:
				$json_return['message'] = 'tableType not set';
				$json_return['success'] = false;
				$json_return['data']    = array();

				return $json_return;
		}
	}

	/**
	 * Get user avatar
	 *
	 * @return array
	 */
	public static function get_user_avatar() {

		$response = array(
			'message' => '',
			'success' => false,
			'data'    => array(),
		);

		// Check if the user id is defined.
		$user_id = absint( ultc_get_filter_var( 'user_id', 0, INPUT_POST ) );
		if ( empty( $user_id ) ) {
			$response['message']    = __( 'Invalid user ID', 'uncanny-learndash-reporting' );
			$response['error_code'] = 1;
			return $response;
		}

		// Get avatar
		$avatar_url = get_avatar_url( $user_id );

		// Check if it has a valid value
		if ( false !== $avatar_url ) {
			// It's valid, save it
			$response['data']['avatar'] = $avatar_url;
			// and change "success" value
			$response['success'] = true;
		} else {
			$response['message']    = __( "We couldn't find an avatar.", 'uncanny-learndash-reporting' );
			$response['error_code'] = 2;
		}

		return $response;
	}

	/**
	 * Get course single overview
	 *
	 * @param $course_id
	 * @param $user_ids
	 *
	 * @return array
	 */
	private static function get_course_single_overview( $course_id, $user_ids ) {

		$table_page = ultc_filter_has_var( 'tablePage', INPUT_POST ) ? ultc_filter_input_array( 'tablePage', INPUT_POST ) : array();

		if ( ! empty( $table_page ) ) {
			$page   = isset( $table_page['page'] ) ? absint( $table_page['page'] ) : 0;
			$length = isset( $table_page['length'] ) ? absint( $table_page['length'] ) : 10;
			$column = isset( $table_page['column'] ) ? absint( $table_page['column'] ) : 0;
		}

		if ( isset( $table_page['order'] ) ) {

			if ( 'desc' === $table_page['order'] ) {
				$order = 'DESC';
			} else {
				$order = 'ASC';
			}
		} else {
			$order = 'ASC';
		}

		$user_ids_rearranged = array();
		foreach ( $user_ids as $row_id => $user_id ) {
			$user_ids_rearranged[ $user_id ]             = array();
			$user_ids_rearranged[ $user_id ]['progress'] = 0;
			$user_ids_rearranged[ $user_id ]['date']     = array(
				'display'   => '',
				'timestamp' => '0',
			);
		}

		global $wpdb;

		$complete_key = "course_completed_{$course_id}";
		$user_data    = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '_sfwd-course_progress' OR meta_key = %s",
				$complete_key
			)
		);

		foreach ( $user_data as $data ) {
			$user_id = $data->user_id;

			if ( ! isset( $user_ids_rearranged[ $user_id ] ) ) {
				continue;
			}

			$meta_key   = $data->meta_key;
			$meta_value = $data->meta_value;

			if ( $complete_key === $meta_key ) {
				if ( absint( $meta_value ) ) {
					$user_ids_rearranged[ $user_id ]['date'] = array(
						'display'   => learndash_adjust_date_time_display( $meta_value ),
						'timestamp' => (string) $meta_value,
					);
				}
			} elseif ( '_sfwd-course_progress' === $meta_key ) {
				$progress = maybe_unserialize( $meta_value );
				if ( ! empty( $progress ) && ! empty( $progress[ $course_id ] ) && ! empty( $progress[ $course_id ]['total'] ) ) {
					$completed = intVal( $progress[ $course_id ]['completed'] );
					$total     = intVal( $progress[ $course_id ]['total'] );
					if ( $total > 0 ) {
						$percentage                                  = intval( $completed * 100 / $total );
						$percentage                                  = ( $percentage > 100 ) ? 100 : $percentage;
						$user_ids_rearranged[ $user_id ]['progress'] = $percentage;
					}
				}
			}
		}

		$quiz_averages = self::get_course_quiz_average_by_user( $course_id, $user_ids );

		$rows = array();
		foreach ( $user_ids as $row_id => $user_id ) {

			$rows[ $row_id ]['user_id']        = $user_id;
			$rows[ $row_id ]['completed_date'] = $user_ids_rearranged[ $user_id ]['date'];
			$rows[ $row_id ]['progress']       = $user_ids_rearranged[ $user_id ]['progress'];

			if ( isset( $quiz_averages[ $user_id ] ) ) {
				$rows[ $row_id ]['quiz_average'] = $quiz_averages[ $user_id ];
			} else {
				$rows[ $row_id ]['quiz_average'] = '';
			}
		}

		return $rows;
	}

	/**
	 * Get user single overview
	 *
	 * @param $user_id
	 * @param $course_ids
	 *
	 * @return array
	 */
	private static function get_user_single_overview( $user_id, $course_ids ) {

		$rows = array();

		// quiz scores
		global $wpdb;

		$user_activities = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.course_id, a.post_id, m.activity_meta_value as activity_percentage, a.activity_status
				FROM {$wpdb->prefix}learndash_user_activity a
				LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
				WHERE a.user_id = %d
				AND a.activity_type = 'quiz'
				AND m.activity_meta_key = 'percentage'",
				$user_id
			)
		);

		$progress              = get_user_meta( $user_id, '_sfwd-course_progress', true );
		$course_ids_rearranged = array();

		foreach ( $course_ids as $row_id => $course_id ) {
			$course_ids_rearranged[ $course_id ] = array();
			if ( ! empty( $progress ) && ! empty( $progress[ $course_id ] ) && ! empty( $progress[ $course_id ]['total'] ) ) {
				$completed = intVal( $progress[ $course_id ]['completed'] );
				$total     = intVal( $progress[ $course_id ]['total'] );
				if ( $total > 0 ) {
					$percentage                                      = intval( $completed * 100 / $total );
					$percentage                                      = ( $percentage > 100 ) ? 100 : $percentage;
					$course_ids_rearranged[ $course_id ]['progress'] = $percentage;
				}
			} else {
				$course_ids_rearranged[ $course_id ]['progress'] = 0;
			}

			$course_ids_rearranged[ $course_id ]['date'] = array(
				'display'   => '',
				'timestamp' => '0',
			);

		}

		$user_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE %s AND user_id = %d",
				$wpdb->esc_like( 'course_completed_' ) . '%',
				$user_id
			)
		);

		foreach ( $user_data as $data ) {
			$x_meta_key = explode( '_', $data->meta_key );
			$course_id  = $x_meta_key[2];

			$meta_value = $data->meta_value;

			$course_ids_rearranged[ $course_id ]['date'] = array(
				'display'   => learndash_adjust_date_time_display( $meta_value ),
				'timestamp' => (string) $meta_value,
			);
		}

		foreach ( $course_ids as $row_id => $course_id ) {

			$rows[ $row_id ]['course_id']      = $course_id;
			$rows[ $row_id ]['completed_date'] = $course_ids_rearranged[ $course_id ]['date'];
			$rows[ $row_id ]['progress']       = $course_ids_rearranged[ $course_id ]['progress'];

			// Column Quiz Average
			$course_quiz_average = self::get_avergae_quiz_result( $course_id, $user_activities );

			$avg_score = '';

			if ( $course_quiz_average ) {
				/* Translators: 1. number percentage */
				$avg_score = sprintf( __( '%1$s%%', 'uncanny-learndash-reporting' ), $course_quiz_average );
			}

			$rows[ $row_id ]['avg_score'] = $avg_score;
		}

		return $rows;
	}

	/**
	 * Get user single course overview
	 *
	 * @param $user_id
	 * @param $course_id
	 *
	 * @return array
	 */
	private static function get_user_single_course_overview( $user_id, $course_id ) {

		$status                 = array();
		$status['completed']    = __( 'Completed', 'uncanny-learndash-reporting' );
		$status['notcompleted'] = __( 'Not Completed', 'uncanny-learndash-reporting' );

		// Get Lessons
		$lessons_list       = learndash_get_course_lessons_list( $course_id, $user_id, array( 'per_page' => - 1 ) );
		$course_quiz_list   = array();
		$course_quiz_list[] = learndash_get_course_quiz_list( $course_id );

		$course_label = \LearnDash_Custom_Label::get_label( 'course' );

		$lessons      = array();
		$topics       = array();
		$lesson_names = array();
		$topic_names  = array();
		$quiz_names   = array();

		$lesson_order = 0;
		$topic_order  = 0;
		foreach ( $lessons_list as $lesson ) {

			$lesson_names[ $lesson['post']->ID ] = $lesson['post']->post_title;
			$lessons[ $lesson_order ]            = array(
				'name'   => $lesson['post']->post_title,
				'status' => $status[ $lesson['status'] ],
			);

			$course_quiz_list[] = learndash_get_lesson_quiz_list( $lesson['post']->ID, $user_id, $course_id );
			$lesson_topics      = learndash_get_topic_list( $lesson['post']->ID, $course_id );

			foreach ( $lesson_topics as $topic ) {

				$course_quiz_list[] = learndash_get_lesson_quiz_list( $topic->ID, $user_id, $course_id );

				$topic_progress = learndash_get_course_progress( $user_id, $topic->ID, $course_id );

				$topic_names[ $topic->ID ] = $topic->post_title;

				$topics[ $topic_order ] = array(
					'name'              => $topic->post_title,
					'status'            => $status['notcompleted'],
					'associated_lesson' => $lesson['post']->post_title,
				);

				if ( ( isset( $topic_progress['posts'] ) ) && ( ! empty( $topic_progress['posts'] ) ) ) {
					foreach ( $topic_progress['posts'] as $topic_progress ) {

						if ( $topic->ID !== $topic_progress->ID ) {
							continue;
						}

						if ( 1 === $topic_progress->completed ) {
							$topics[ $topic_order ]['status'] = $status['completed'];
						}
					}
				}
				$topic_order ++;
			}
			$lesson_order ++;
		}

		global $wpdb;

		// Assignments
		$assignments            = array();
		$assignment_data_object = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post.ID, post.post_title, post.post_date, postmeta.meta_key, postmeta.meta_value
				FROM {$wpdb->posts} post
				JOIN {$wpdb->postmeta} postmeta ON post.ID = postmeta.post_id
				WHERE post.post_status = 'publish' AND post.post_type = 'sfwd-assignment'
				AND post.post_author = %d
				AND ( postmeta.meta_key = 'approval_status' OR postmeta.meta_key = 'course_id' OR postmeta.meta_key LIKE %s )",
				$user_id,
				$wpdb->esc_like( 'ld_course_' ) . '%'
			)
		);

		foreach ( $assignment_data_object as $assignment ) {

			// Assignment List
			$data               = array();
			$data['ID']         = $assignment->ID;
			$data['post_title'] = $assignment->post_title;

			$assignment_id                                = (int) $assignment->ID;
			$rearranged_assignment_list[ $assignment_id ] = $data;

			// User Assignment Data
			$assignment_id = (int) $assignment->ID;
			$meta_key      = $assignment->meta_key;
			$meta_value    = (int) $assignment->meta_value;

			$date = learndash_adjust_date_time_display( strtotime( $assignment->post_date ) );

			$assignments[ $assignment_id ]['name']           = '<a target="_blank" href="' . get_edit_post_link( $assignment->ID ) . '">' . $assignment->post_title . '</a>';
			$assignments[ $assignment_id ]['completed_date'] = $date;
			$assignments[ $assignment_id ][ $meta_key ]      = $meta_value;

		}

		foreach ( $assignments as $assignment_id => &$assignment ) {
			if ( isset( $assignment['course_id'] ) && $course_id !== (int) $assignment['course_id'] ) {
				unset( $assignments[ $assignment_id ] );
			} else {
				if ( isset( $assignment['approval_status'] ) && 1 === (int) $assignment['approval_status'] ) {
					$assignment['approval_status'] = __( 'Approved', 'uncanny-learndash-reporting' );
				} else {
					$assignment['approval_status'] = __( 'Not Approved', 'uncanny-learndash-reporting' );
				}
			}
		}

		// Quizzes Scores Avg
		global $wpdb;

		$user_activities = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.activity_id, a.course_id, a.post_id, a.activity_status, a.activity_completed, m.activity_meta_value as activity_percentage
				FROM {$wpdb->prefix}learndash_user_activity a
				LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
				WHERE a.user_id = %d
				AND a.course_id = %d
				AND a.activity_type = 'quiz'
				AND m.activity_meta_key = 'percentage'",
				$user_id,
				$course_id
			)
		);

		// Quizzes
		$quizzes = array();

		foreach ( $course_quiz_list as $module_quiz_list ) {
			if ( empty( $module_quiz_list ) ) {
				continue;
			}

			foreach ( $module_quiz_list as $quiz ) {

				if ( isset( $quiz['post'] ) ) {

					$quiz_names[ $quiz['post']->ID ] = $quiz['post']->post_title;
					$certificate_link                = '';
					$certificate                     = learndash_certificate_details( $quiz['post']->ID, $user_id );
					if ( ! empty( $certificate ) && isset( $certificate['certificateLink'] ) ) {
						$certificate_link = $certificate['certificateLink'];
					}

					foreach ( $user_activities as $activity ) {

						if ( (int) $activity->post_id === (int) $quiz['post']->ID ) {

							$pro_quiz_id = learndash_get_user_activity_meta( $activity->activity_id, 'pro_quizid', true );
							if ( empty( $pro_quiz_id ) ) {
								// LD is starting to deprecated pro quiz IDs from LD activity Tables. This is a back up if its not there
								$pro_quiz_id = absint( get_post_meta( $quiz['post']->ID, 'quiz_pro_id', true ) );
							}

							$statistic_ref_id = learndash_get_user_activity_meta( $activity->activity_id, 'statistic_ref_id', true );
							if ( empty( $statistic_ref_id ) ) {

								if ( class_exists( '\LDLMS_DB' ) ) {
									$pro_quiz_master_table   = \LDLMS_DB::get_table_name( 'quiz_master' );
									$pro_quiz_stat_ref_table = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
								} else {
									$pro_quiz_master_table   = $wpdb->prefix . 'wp_pro_quiz_master';
									$pro_quiz_stat_ref_table = $wpdb->prefix . 'wp_pro_quiz_statistic_ref';
								}

								// LD is starting to deprecated pro quiz IDs from LD activity Tables. This is a back up if its not there
								// phpcs:disable WordPress.DB.PreparedSQL
								$statistic_ref_id = $wpdb->get_var(
									$wpdb->prepare(
										"SELECT statistic_ref_id FROM {$pro_quiz_stat_ref_table} as stat
										INNER JOIN {$pro_quiz_master_table} as master ON stat.quiz_id=master.id
										WHERE  user_id = %d AND quiz_id = %d AND create_time = %d AND master.statistics_on=1 
										LIMIT 1",
										$user_id,
										$pro_quiz_id,
										$activity->activity_completed
									)
								);
								// phpcs:enable WordPress.DB.PreparedSQL
							}

							$modal_link = '';

							if ( empty( $statistic_ref_id ) || empty( $pro_quiz_id ) ) {
								if ( ! empty( $statistic_ref_id ) ) {
									$modal_link = '<a class="user_statistic"
									     data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $statistic_ref_id . '_' . get_current_user_id() . '_' . $user_id ) . '"
									     data-user_id="' . esc_attr( $user_id ) . '"
									     data-quiz_id="' . esc_attr( $pro_quiz_id ) . '"
									     data-ref_id="' . esc_attr( intval( $statistic_ref_id ) ) . '"
									     data-uo-pro-quiz-id="' . esc_attr( intval( $pro_quiz_id ) ) . '"
									     data-uo-quiz-id="' . esc_attr( intval( $activity->post_id ) ) . '"
									     data-nonce="' . esc_attr( wp_create_nonce( 'wpProQuiz_nonce' ) ) . '"
									     href="#"> </a>';
								}
							} else {
								if ( ! empty( $statistic_ref_id ) ) {
									$modal_link = '<a class="user_statistic"
									     data-statistic_nonce="' . esc_attr( wp_create_nonce( 'statistic_nonce_' . $statistic_ref_id . '_' . get_current_user_id() . '_' . $user_id ) ) . '"
									     data-user_id="' . esc_attr( $user_id ) . '"
									     data-quiz_id="' . esc_attr( $pro_quiz_id ) . '"
									     data-ref_id="' . esc_attr( intval( $statistic_ref_id ) ) . '"
									     data-uo-pro-quiz-id="' . esc_attr( intval( $pro_quiz_id ) ) . '"
									     data-uo-quiz-id="' . esc_attr( intval( $activity->post_id ) ) . '"
									     data-nonce="' . esc_attr( wp_create_nonce( 'wpProQuiz_nonce' ) ) . '"
									     href="#">';
									$modal_link .= '<div class="statistic_icon"></div>';
									$modal_link .= '</a>';
								}
							}

							$quizzes[] = array(
								'name'             => $quiz['post']->post_title,
								'score'            => $activity->activity_percentage,
								'detailed_report'  => $modal_link,
								'completed_date'   => array(
									'display'   => learndash_adjust_date_time_display( $activity->activity_completed ),
									'timestamp' => $activity->activity_completed,
								),
								'certificate_link' => $certificate_link,
							);
						}
					}
				}
			}
		}

		$progress = learndash_course_progress(
			array(
				'course_id' => $course_id,
				'user_id'   => $user_id,
				'array'     => true,
			)
		);

		$completed_date = '';

		if ( 100 <= $progress['percentage'] ) {
			$progress_percentage = $progress['percentage'];
			$completed_timestamp = learndash_user_get_course_completed_date( $user_id, $course_id );
			if ( absint( $completed_timestamp ) ) {
				$completed_date = learndash_adjust_date_time_display( learndash_user_get_course_completed_date( $user_id, $course_id ) );
				$status         = __( 'Completed', 'uncanny-learndash-reporting' );
			} else {
				$status = __( 'In Progress', 'uncanny-learndash-reporting' );
			}
		} else {
			// Division by zero causing fatals.
			$completed = isset( $progress['completed'] ) ? absint( $progress['completed'] ) : 0;
			$total     = isset( $progress['total'] ) ? absint( $progress['total'] ) : 0;
			if ( $total > 0 ) {
				$progress_percentage = absint( $completed / $total * 100 );
				$status              = __( 'In Progress', 'uncanny-learndash-reporting' );
			} else {
				$progress_percentage = 0;
			}
		}

		if ( 0 === $progress_percentage ) {
			$progress_percentage = '';
			$status              = __( 'Not Started', 'uncanny-learndash-reporting' );
		} else {
			$progress_percentage = $progress_percentage . __( '%', 'uncanny-learndash-reporting' );
		}

		// Column Quiz Average
		$course_quiz_average = self::get_avergae_quiz_result( $course_id, $user_activities );
		$avg_score           = '';
		if ( $course_quiz_average ) {
			/* Translators: 1. number percentage */
			$avg_score = sprintf( __( '%1$s%%', 'uncanny-learndash-reporting' ), $course_quiz_average );
		}

		// TinCanny
		global $wpdb;
		$statements_list = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT lesson_id as post_id, module_name, target_name, verb as action, result, xstored 
				FROM {$wpdb->prefix}uotincan_reporting
				WHERE user_id = %d AND course_id = %d",
				$user_id,
				$course_id
			)
		);
		$statements      = array();
		foreach ( $statements_list as $statement ) {

			if ( isset( $quiz_names[ (int) $statement->post_id ] ) ) {
				$related_post_name = $quiz_names[ (int) $statement->post_id ];
			} elseif ( isset( $topic_names[ (int) $statement->post_id ] ) ) {
				$related_post_name = $topic_names[ (int) $statement->post_id ];
			} elseif ( isset( $lesson_names[ (int) $statement->post_id ] ) ) {
				$related_post_name = $lesson_names[ (int) $statement->post_id ];
			} elseif ( (int) $statement->post_id === $course_id ) {
				$related_post_name = get_the_title( $course_id );
			} else {
				$tmp_post = get_post( $statement->post_id );

				if ( $tmp_post ) {
					$related_post_name = $tmp_post->post_title;
					$tmp_post          = null;
				} else {
					$related_post_name = __( 'Not Found: ', 'uncanny-learndash-reporting' ) . $statement->post_id;
				}
			}

			$date = $statement->xstored;

			$statements[] = array(
				'related_post' => $related_post_name,
				'module'       => $statement->module_name,
				'target'       => $statement->target_name,
				'action'       => $statement->action,
				'result'       => $statement->result,
				'date'         => $date,
			);

		}

		return array(
			'completed_date'      => $completed_date,
			'progress_percentage' => $progress_percentage,
			'avg_score'           => $avg_score,
			'status'              => $status,
			'lessons'             => $lessons,
			'topics'              => $topics,
			'quizzes'             => $quizzes,
			'assigments'          => $assignments,
			'statements'          => $statements,
			'course_certificate'  => learndash_get_course_certificate_link( $course_id, $user_id ),
		);
	}

	/**
	 * Get Average Quiz Result
	 *
	 * @param $course_id
	 * @param $user_activities
	 *
	 * @return false|int
	 */
	private static function get_avergae_quiz_result( $course_id, $user_activities ) {

		$quiz_scores = array();

		foreach ( $user_activities as $activity ) {

			if ( (int) $course_id === (int) $activity->course_id ) {

				if ( ! isset( $quiz_scores[ $activity->post_id ] ) ) {

					$quiz_scores[ $activity->post_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->post_id ] < $activity->activity_percentage ) {

					$quiz_scores[ $activity->post_id ] = $activity->activity_percentage;
				}
			}
		}

		if ( 0 !== count( $quiz_scores ) ) {
			$average = absint( array_sum( $quiz_scores ) / count( $quiz_scores ) );
		} else {
			$average = false;
		}

		return $average;
	}

	/**
	 * Get Course Quiz Average
	 *
	 * @param $course_id
	 * @param $user_activities
	 * @param $user_ids
	 *
	 * @return int|string
	 */
	private static function get_course_quiz_average( $course_id, $user_activities, $user_ids ) {

		$quiz_scores = array();

		foreach ( $user_activities as $activity ) {

			if ( isset( $user_ids[ (int) $activity->user_id ] ) && (int) $course_id === (int) $activity->course_id ) {

				if ( ! isset( $quiz_scores[ $activity->post_id . $activity->user_id ] ) ) {

					$quiz_scores[ $activity->post_id . $activity->user_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->post_id . $activity->user_id ] < $activity->activity_percentage ) {

					$quiz_scores[ $activity->post_id . $activity->user_id ] = $activity->activity_percentage;
				}
			}
		}

		if ( 0 !== count( $quiz_scores ) ) {
			$average = absint( array_sum( $quiz_scores ) / count( $quiz_scores ) );
		} else {
			$average = 'false';
		}

		return $average;
	}

	/**
	 * Get Course Quiz Average By User
	 *
	 * @param $course_id
	 * @param $user_ids
	 *
	 * @return array
	 */
	private static function get_course_quiz_average_by_user( $course_id, $user_ids ) {

		global $wpdb;

		$user_ids_rearranged = array();
		foreach ( $user_ids as $user_id ) {
			$user_ids_rearranged[ $user_id ] = $user_id;
		}

		$quiz_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.course_id, a.post_id, m.activity_meta_value as activity_percentage, a.user_id
				FROM {$wpdb->prefix}learndash_user_activity a
				LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
				WHERE a.activity_type = 'quiz'
				AND a.course_id = %d
				AND m.activity_meta_key = 'percentage'",
				$course_id
			)
		);

		$quiz_scores = array();

		foreach ( $quiz_results as $activity ) {

			if ( isset( $user_ids_rearranged[ (int) $activity->user_id ] ) ) {

				if ( ! isset( $quiz_scores[ $activity->user_id ] ) ) {
					$quiz_scores[ $activity->user_id ] = array();
				}

				if ( ! isset( $quiz_scores[ $activity->user_id ][ $activity->post_id ] ) ) {
					$quiz_scores[ $activity->user_id ][ $activity->post_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->user_id ][ $activity->post_id ] < $activity->activity_percentage ) {
					$quiz_scores[ $activity->user_id ][ $activity->post_id ] = $activity->activity_percentage;
				}
			}
		}

		$averages = array();
		if ( 0 !== count( $quiz_scores ) ) {
			foreach ( $quiz_scores as $user_id => $scores ) {
				$averages[ $user_id ] = absint( array_sum( $scores ) / count( $scores ) );
			}
		}

		return $averages;
	}

	/**
	 * Get course modules
	 *
	 * @return array
	 */
	public static function get_course_modules() {

		$course_modules = array();

		if ( current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {

			$course_modules['lessonList'] = self::get_lesson_list();
			$course_modules['topicList']  = self::get_topic_list();
			$course_modules['quizList']   = self::get_quiz_list();

		}

		return $course_modules;
	}

	/**
	 * Get lesson list
	 *
	 * @return array
	 */
	private static function get_lesson_list() {

		global $wpdb;

		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {
					// REVIEW: Why is this condition here it adds the same meta key if passed or failed.
					if ( self::$isolated_group_id && (int) $group_id === (int) self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}
				}
				$imploded_meta_keys         = implode( ',', $meta_keys );
				$restrict_group_leader_post
									= "AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key IN ($imploded_meta_keys)
				)
				)";
			} else {
				$restrict_group_leader_post = '';
			}
		} elseif ( self::$isolated_group_id ) {
			$restrict_group_leader_post =
				"AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'
				)
				)";
		} else {
			$restrict_group_leader_post = '';
		}

		$rearranged_lesson_list = array();

		$sql_string = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-lessons' $restrict_group_leader_post";
		$sql_string = apply_filters( 'get_lesson_list_sql', $sql_string, $restrict_group_leader_post );
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$lesson_list = $wpdb->get_results( $sql_string );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		foreach ( $lesson_list as $lesson ) {
			$lesson_id                            = (int) $lesson->ID;
			$rearranged_lesson_list[ $lesson_id ] = $lesson;
		}

		$rearranged_lesson_list[1] = array();

		return $rearranged_lesson_list;
	}

	/**
	 * Get topic list
	 *
	 * @return array
	 */
	private static function get_topic_list() {

		global $wpdb;

		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {
					// REVIEW: Why is this condition here it adds the same meta key if passed or failed.
					if ( self::$isolated_group_id && (int) $group_id === (int) self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}
				}

				$imploded_meta_keys         = implode( ',', $meta_keys );
				$restrict_group_leader_post
									= "AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key IN ($imploded_meta_keys)
				)
				)";
			} else {
				$restrict_group_leader_post = '';
			}
		} elseif ( self::$isolated_group_id ) {
			$restrict_group_leader_post =
				"AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'
				)
				)";
		} else {
			$restrict_group_leader_post = '';
		}

		$sql_string = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-topic' $restrict_group_leader_post";
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$topic_list = $wpdb->get_results( $sql_string );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		$rearranged_topic_list = array();
		foreach ( $topic_list as $topic ) {
			$topic_id                           = (int) $topic->ID;
			$rearranged_topic_list[ $topic_id ] = $topic;
		}

		$rearranged_topic_list[1] = array();

		return $rearranged_topic_list;

	}

	/**
	 * Get Quiz List
	 *
	 * @return array
	 */
	private static function get_quiz_list() {

		global $wpdb;

		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {
					// REVIEW: Why is this condition here it adds the same meta key if passed or failed.
					if ( self::$isolated_group_id && (int) $group_id === (int) self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}
				}
				$imploded_meta_keys         = implode( ',', $meta_keys );
				$restrict_group_leader_post
									= "AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key IN ($imploded_meta_keys)
				)
				)";
			} else {
				$restrict_group_leader_post = '';
			}
		} elseif ( self::$isolated_group_id ) {
			$restrict_group_leader_post =
				"AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'
				)
				)";
		} else {
			$restrict_group_leader_post = '';
		}

		$sql_string = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-quiz'";
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$quiz_list = $wpdb->get_results( $sql_string );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		$rearranged_quiz_list = array();
		foreach ( $quiz_list as $quiz ) {
			$quiz_id                          = (int) $quiz->ID;
			$rearranged_quiz_list[ $quiz_id ] = $quiz;
		}

		$rearranged_quiz_list[1] = array();

		return $rearranged_quiz_list;

	}

	/**
	 * Get assignment data
	 *
	 * @return array
	 */
	public static function get_assignment_data() {

		global $wpdb;

		$rearranged_assignment_list      = array();
		$merged_approval_assignment_data = array();

		if ( current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {

			// Modify custom query to restrict data to group leaders available data
			if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

				$leader_groups = self::get_administrators_group_ids();
				if ( ! empty( $leader_groups ) ) {
					foreach ( $leader_groups as $group_id ) {
						// REVIEW: Why is this condition here it adds the same meta key if passed or failed.
						if ( self::$isolated_group_id && (int) $group_id === (int) self::$isolated_group_id ) {
							$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
						} else {
							$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
						}
					}
					$imploded_meta_keys = implode( ',', $meta_keys );
					// TODO CHECK ASSIGNMENT ACTIVITIES
					$restrict_group_leader_post = "AND post.ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ($imploded_meta_keys) )";
				} else {
					$restrict_group_leader_post = '';
				}
			} elseif ( self::$isolated_group_id ) {
				$restrict_group_leader_post = "AND post.ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "' )";
			} else {
				$restrict_group_leader_post = '';
			}

			$sql_string = "SELECT post.ID, post.post_author, post.post_title, post.post_date, postmeta.meta_key, postmeta.meta_value FROM $wpdb->posts post JOIN $wpdb->postmeta postmeta ON post.ID = postmeta.post_id WHERE post.post_status = 'publish' AND post.post_type = 'sfwd-assignment' AND ( postmeta.meta_key = 'approval_status' OR postmeta.meta_key = 'course_id' OR postmeta.meta_key LIKE 'ld_course_%' ) $restrict_group_leader_post";
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$assignment_data_object = $wpdb->get_results( $sql_string );
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

			foreach ( $assignment_data_object as $assignment ) {

				// Assignment List
				$data               = array();
				$data['ID']         = $assignment->ID;
				$data['post_title'] = $assignment->post_title;

				$assignment_id                                = (int) $assignment->ID;
				$rearranged_assignment_list[ $assignment_id ] = $data;

				// User Assignment Data
				$assignment_id      = (int) $assignment->ID;
				$assignment_user_id = (int) $assignment->post_author;
				$meta_key           = $assignment->meta_key;
				$meta_value         = (int) $assignment->meta_value;

				// SQL Time '1970-01-17 05:54:21' exploded to get date only
				$date = explode( ' ', $assignment->post_date );
				$merged_approval_assignment_data[ $assignment_user_id ][ $assignment_id ]['completed_on'] = $date[0];

				$merged_approval_assignment_data[ $assignment_user_id ][ $assignment_id ]['ID'] = $assignment_id;

				$merged_approval_assignment_data[ $assignment_user_id ][ $assignment_id ][ $meta_key ] = $meta_value;

			}

			$rearranged_assignment_list[1] = array();

			$assignment_data['userAssignmentData'] = $merged_approval_assignment_data;
			$assignment_data['assignmentList']     = $rearranged_assignment_list;

		}

		return $assignment_data;
	}

	/**
	 * Get labels
	 *
	 * @return array
	 */
	public static function get_labels() {

		$labels['course']  = \LearnDash_Custom_Label::get_label( 'course' );
		$labels['courses'] = \LearnDash_Custom_Label::get_label( 'courses' );

		$labels['lesson']  = \LearnDash_Custom_Label::get_label( 'lesson' );
		$labels['lessons'] = \LearnDash_Custom_Label::get_label( 'lessons' );

		$labels['topic']  = \LearnDash_Custom_Label::get_label( 'topic' );
		$labels['topics'] = \LearnDash_Custom_Label::get_label( 'topics' );

		$labels['quiz']    = \LearnDash_Custom_Label::get_label( 'quiz' );
		$labels['quizzes'] = \LearnDash_Custom_Label::get_label( 'quizzes' );

		return $labels;
	}

	/**
	 * Get links
	 *
	 * @return array
	 */
	public static function get_links() {

		$labels = array();

		$labels['profile']    = admin_url( 'user-edit.php', 'admin' );
		$labels['assignment'] = admin_url( 'post.php', 'admin' );

		return $labels;
	}

	/**
	 * Get Tin Can Data
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function get_tincan_data( $data ) {

		$return_object = array();

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			$return_object['message'] = __( 'Current User doesn\'t have permissions to Tin Can report data', 'uncanny-learndash-reporting' );
			$return_object['user_ID'] = get_current_user_id();

			return $return_object;
		}

		// validate inputs
		$user_id = absint( $data['user_ID'] );

		// if any of the values are 0 then they didn't validate, storage is not possible
		if ( 0 === $user_id ) {
			$return_object['message'] = 'invalid user id supplied';
			$return_object['user_ID'] = $data['user_ID'];

			return $return_object;
		}

		//      global $wpdb;
		$group_course_ids = array();
		$leader_groups    = self::get_administrators_group_ids();
		if ( ! empty( $leader_groups ) ) {
			foreach ( $leader_groups as $group_id ) {
				$__courses = learndash_group_enrolled_courses( $group_id );
				if ( ! empty( $__courses ) ) {
					foreach ( $__courses as $__course_id ) {
						$group_course_ids[] = $__course_id;
					}
				}
			}
		}

		if ( empty( $group_course_ids ) ) {
			return array();
		}
		$group_course_ids = array_map( 'absint', $group_course_ids );
		$group_course_ids = array_unique( $group_course_ids );

		$tin_can_data = null;
		if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
			$database          = new \UCTINCAN\Database\Admin();
			$database->user_id = $user_id;
			$tin_can_data      = $database->get_data();
		}

		if ( null !== $tin_can_data && ! empty( $tin_can_data ) ) {

			$data = array();
			//$sample = array();
			//$sample['All'] = $tin_can_data;
			foreach ( $tin_can_data as $user_single_tin_can_object ) {

				$tc_course_id = (int) $user_single_tin_can_object['course_id'];
				$tc_lesson_id = (int) $user_single_tin_can_object['lesson_id'];

				if ( 'group_leader' === self::get_user_role() || current_user_can( 'manage_options' ) ) {
					if ( ! in_array( $tc_course_id, $group_course_ids, true ) ) {
						continue;
					}
				}

				if ( $user_single_tin_can_object['lesson_id'] && $user_single_tin_can_object['course_id'] ) {

					if ( ! isset( $data[ $tc_course_id ] ) ) {
						$data[ $tc_course_id ] = array();
					}
					if ( ! isset( $data[ $tc_course_id ][ $tc_lesson_id ] ) ) {
						$data[ $tc_course_id ][ $tc_lesson_id ] = array();
					}
					$tc_course_id = (int) $user_single_tin_can_object['course_id'];
					$tc_lesson_id = (int) $user_single_tin_can_object['lesson_id'];
					array_push( $data[ $tc_course_id ][ $tc_lesson_id ], $user_single_tin_can_object );

				}
			}

			return array(
				'user_ID'          => $user_id,
				'tinCanStatements' => $data,
			);

		}

		return array();
	}

	/**
	 * Show Tin Can Tables
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public static function show_tincan_tables( $data ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return 'no permissions';
		}

		$show_tincan_tables = absint( $data['show_tincan'] );
		$value              = 1 === $show_tincan_tables ? 'yes' : 'no';
		$updated            = update_option( 'show_tincan_reporting_tables', $value );
		return $value;
	}

	/**
	 * Disable Mark Complete
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public static function disable_mark_complete( $data ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return 'no permissions';
		}

		$disable_mark_complete = absint( $data['disable_mark_complete'] );

		if ( 1 === $disable_mark_complete ) {
			$value = 'yes';
		}
		if ( 0 === $disable_mark_complete ) {
			$value = 'no';
		}
		if ( 3 === $disable_mark_complete ) {
			$value = 'hide';
		}
		if ( 4 === $disable_mark_complete ) {
			$value = 'remove';
		}
		if ( 5 === $disable_mark_complete ) {
			$value = 'autoadvance';
		}

		$updated = update_option( 'disable_mark_complete_for_tincan', $value );
		return $value;
	}

	/**
	 * Nonce Protection
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public static function nonce_protection( $data ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return 'no permissions';
		}

		$nonce_protection = absint( $data['nonce_protection'] );
		$value            = 1 === $nonce_protection ? 'yes' : 'no';
		$updated          = update_option( 'tincanny_nonce_protection', $value );

		// Check if the user chose not to protect the content.
		if ( 'no' === $value ) {
			\uncanny_learndash_reporting\Boot::delete_protection_htaccess();
		}

		return $value;
	}

	/**
	 * Reset Tin Can Data
	 *
	 * @return bool
	 */
	public static function reset_tincan_data() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				$database = new \UCTINCAN\Database\Admin();
				$database->reset();

				return true;
			}
		}

		return false;
	}

	/**
	 * Reset Quiz Data
	 *
	 * @return bool
	 */
	public static function reset_quiz_data() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				$database = new \UCTINCAN\Database\Admin();
				$database->reset_quiz();

				return true;
			}
		}

		return false;
	}

	/**
	 * Reset Bookmark Data
	 *
	 * @return bool
	 */
	public static function reset_bookmark_data() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				$database = new \UCTINCAN\Database\Admin();
				$database->reset_bookmark_data();

				return true;
			}
		}

		return false;
	}

	/**
	 * Purge Experienced
	 *
	 * @return bool
	 */
	public static function purge_experienced() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				// Run query
				global $wpdb;
				$wpdb->query( "DELETE FROM {$wpdb->prefix}uotincan_reporting WHERE verb = 'experienced'" );
				return true;
			}
		}

		return false;
	}

	/**
	 * Purge Answered
	 *
	 * @return bool
	 */
	public static function purge_answered() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				// Run query
				global $wpdb;
				$wpdb->query( "DELETE FROM {$wpdb->prefix}uotincan_reporting  WHERE verb = 'answered'" );
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Adminsitrators Group IDs
	 *
	 * @return array|null
	 */
	private static function get_administrators_group_ids() {

		if ( ! self::$group_leaders_group_ids ) {
			self::$group_leaders_group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
		}

		return self::$group_leaders_group_ids;
	}

	/**
	 * Get User Role
	 *
	 * @return string|null
	 */
	private static function get_user_role() {

		if ( ! self::$user_role ) {

			// Default value
			self::$user_role = 'unknown';

			// is it an administrator
			if ( current_user_can( 'manage_options' ) ) {
				self::$user_role = 'administrator';
			} elseif ( learndash_is_group_leader_user( get_current_user_id() ) ) {
				// Is it a group leader
				self::$user_role = 'group_leader';
			}
		}

		return self::$user_role;
	}

	/**
	 * Create temporary table
	 *
	 * @return bool|int
	 */
	private static function create_temp_table() {
		global $wpdb;
		$temp_table = $wpdb->prefix . self::$temp_table;
		// phpcs:disable WordPress.DB.PreparedSQL
		if ( ! self::check_if_col_exists() ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$temp_table};" );
		}

		return $wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$temp_table} (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`group_leader_id` int(20) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`,`group_leader_id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 * Check if column exists
	 *
	 * @return bool
	 */
	private static function check_if_col_exists() {
		global $wpdb;
		$temp_table = $wpdb->prefix . self::$temp_table;
		// phpcs:disable WordPress.DB.PreparedSQL
		$result = $wpdb->get_row( "SHOW COLUMNS FROM {$temp_table} LIKE 'group_leader_id'" );
		// phpcs:enable WordPress.DB.PreparedSQL
		if ( empty( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Drop temporary table
	 *
	 * @return bool|int
	 */
	private static function drop_temp_table() {
		global $wpdb;
		$temp_table = $wpdb->prefix . self::$temp_table;
		//$q   = "DROP TABLE IF EXISTS `$temp_table`;";
		//$q = "TRUNCATE TABLE `$temp_table`;";
		// phpcs:disable WordPress.DB.PreparedSQL
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$temp_table} WHERE group_leader_id = %d;",
				wp_get_current_user()->ID
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 * Insert user ids in temp table
	 *
	 * @param $user_ids
	 */
	private static function insert_user_ids_in_temp_table( $user_ids ) {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL
		$temp_table = $wpdb->prefix . self::$temp_table;
		$chunk      = 500;
		//$wpdb->query( "TRUNCATE TABLE $wpdb->prefix" . self::$temp_table );
		//      if ( count( $user_ids ) > $chunk ) {
		$chunks = array_chunk( $user_ids, $chunk );
		if ( $chunks ) {
			$add_user_ids = array();
			foreach ( $chunks as $chunk ) {
				foreach ( $chunk as $chunk_values ) {
					$add_user_ids[] = '(' . implode( ', ', $chunk_values ) . ')';
				}
				//Config::log( $add_user_ids, '$add_user_ids', true, 'user_ids' );
				// Config::log( "INSERT INTO $wpdb->prefix" . self::$temp_table . ' (`user_id`, `group_leader_id`) VALUES ' . implode( ',', $add_user_ids ), '$query', true, 'user_ids' );
				if ( $add_user_ids ) {
					$implode = implode( ',', $add_user_ids );
					$r       = $wpdb->query( "INSERT INTO {$temp_table} (`user_id`, `group_leader_id`) VALUES $implode" );
					// Config::log( $r, '$r', true, 'user_ids' );
				}
				$add_user_ids = array();
			}
		}
		// phpcs:enable WordPress.DB.PreparedSQL

	}

	/**
	 * Get users
	 *
	 * @param $args
	 *
	 * @return void
	 */
	public static function get_users( $args = array() ) {
		$user_query = self::query_users( $args );
		// Config::log( $user_query, '$user_query', true, '$user_query' );
		// insert first set
		self::insert_users( $user_query );
	}

	/**
	 * Query Users
	 *
	 * @param $args
	 * @param $paged
	 * @param $number
	 *
	 * @return \WP_User_Query
	 */
	public static function query_users( $args, $paged = 1, $number = 1000000 ) {
		$args['number']   = $number;
		$args['paged']    = $paged;
		$args['order_by'] = 'ID';
		$args['order']    = 'ASC';

		return new \WP_User_Query( $args );
	}

	/**
	 * Inject group leader id
	 *
	 * @param $user_ids
	 * @param $current_user_id
	 *
	 * @return array
	 */
	public static function inject_group_leader_id( $user_ids, $current_user_id ) {
		$updated_ids = array();
		foreach ( $user_ids as $user_id ) {
			$updated_ids[] = array(
				$user_id,
				$current_user_id,
			);
		}

		return $updated_ids;
	}

	/**
	 * Insert Users
	 *
	 * @param $user_query
	 *
	 * @return void
	 */
	public static function insert_users( \WP_User_Query $user_query ) {
		$users = $user_query->get_results(); // array of WP_User objects, like get_users
		if ( ! empty( $users ) ) {
			$user_ids        = array_column( $users, 'ID' );
			$current_user_id = wp_get_current_user()->ID;
			$updated_ids     = array();
			foreach ( $user_ids as $user_id ) {
				$updated_ids[] = array(
					$user_id,
					$current_user_id,
				);
			}
			// Config::log( $updated_ids, '$updated_ids', true, '$user_query' );
			self::insert_user_ids_in_temp_table( $updated_ids );
		}
	}

	/**
	 * Get all user ids
	 *
	 * @return array
	 */
	public static function get_all_user_ids() {
		global $wpdb;
		$temp_table = $wpdb->prefix . self::$temp_table;
		// phpcs:disable WordPress.DB.PreparedSQL
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT user_id FROM {$temp_table} WHERE group_leader_id = %d",
				wp_get_current_user()->ID
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 * Get all users data
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public static function get_all_users_data() {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT temp.user_id AS ID, u.display_name, u.user_email, u.user_login, um1.meta_value AS 'first_name', um2.meta_value AS 'last_name'
FROM {$wpdb->prefix}`tbl_reporting_api_user_id` temp
JOIN {$wpdb->users} u
ON temp.user_id = u.ID AND temp.group_leader_id = %d
JOIN {$wpdb->usermeta} um1
ON temp.user_id = um1.user_id AND um1.meta_key = %s
JOIN {$wpdb->usermeta} um2
ON temp.user_id = um2.user_id AND um2.meta_key = %s
GROUP BY u.ID;",
				wp_get_current_user()->ID,
				'first_name',
				'last_name'
			)
		);
	}

	/**
	 * Tin Can Course Access
	 *
	 * @param $access
	 *
	 * @return void
	 */
	public static function tc_course_access( $access ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'tc_course_access';
		// phpcs:disable WordPress.DB.PreparedSQL
		if ( 'no' === get_option( 'course_access_table_created', 'no' ) ) {

			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `{$table_name}` (
				`ID`        bigint(20) NOT NULL AUTO_INCREMENT,
				`course_id` bigint(20) COLLATE utf8_unicode_ci NOT NULL,
				`user_id`   bigint(20) COLLATE utf8_unicode_ci NOT NULL,
				`group_id`  bigint(20) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`ID`),
				KEY `course_id`(`course_id`),
				KEY `user_id`(`user_id`),
				KEY `group_id`(`group_id`)
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;"
			);

			update_option( 'course_access_table_created', 'yes', true );
		}

		$wpdb->query( "Truncate table {$table_name}" );
		// phpcs:enable WordPress.DB.PreparedSQL

		$queries = array();
		$count   = 0;
		$index   = 0;
		foreach ( $access as $course_id => $data ) {
			foreach ( $data as $user_id => $group_id ) {
				$count ++;
				if ( ! isset( $queries[ $index ] ) ) {
					$queries[ $index ] = '';
				}
				$queries[ $index ] .= '(null,' . $course_id . ',' . $user_id . ',' . $group_id . '),';
				if ( 50000 === $count ) {
					$index ++;
					$count = 0;
				}
			}
		}

		//unset( $access );

		foreach ( $queries as $query ) {
			$query = substr( $query, 0, - 1 );
			$q     = "INSERT INTO {$table_name}(ID,course_id,user_id,group_id) values $query;";
		}

		unset( $queries );
		unset( $q );
	}
}
