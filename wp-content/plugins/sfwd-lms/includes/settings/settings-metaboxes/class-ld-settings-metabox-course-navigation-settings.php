<?php
/**
 * LearnDash Settings Metabox for Course Navigation Settings.
 *
 * @since 3.0.0
 * @package LearnDash\Settings\Metaboxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Metabox' ) ) && ( ! class_exists( 'LearnDash_Settings_Metabox_Course_Navigation_Settings' ) ) ) {
	/**
	 * Class LearnDash Settings Metabox for Course Navigation Settings.
	 *
	 * @since 3.0.0
	 */
	class LearnDash_Settings_Metabox_Course_Navigation_Settings extends LearnDash_Settings_Metabox {

		/**
		 * Public constructor for class
		 *
		 * @since 3.0.0
		 */
		public function __construct() {
			// What screen ID are we showing on.
			$this->settings_screen_id = 'sfwd-courses';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_metabox_key = 'learndash-course-navigation-settings';

			// Section label/header.
			$this->settings_section_label = sprintf(
				// translators: placeholder: course.
				esc_html_x( '%s Navigation Settings', 'placeholder: course', 'learndash' ),
				learndash_get_custom_label( 'course' )
			);

			$this->settings_section_description = esc_html__( 'Controls how students interact with the content and their navigational experience', 'learndash' );

			// Map internal settings field ID to legacy field ID.
			$this->settings_fields_map = array(
				'course_disable_lesson_progression' => 'course_disable_lesson_progression',
			);
			parent::__construct();
		}

		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 3.0.0
		 */
		public function load_settings_values() {
			parent::load_settings_values();
			if ( true === $this->settings_values_loaded ) {
				if ( ! isset( $this->setting_option_values['course_disable_lesson_progression'] ) ) {
					$this->setting_option_values['course_disable_lesson_progression'] = '';
				}
			}

			// Ensure all settings fields are present.
			foreach ( $this->settings_fields_map as $_internal => $_external ) {
				if ( ! isset( $this->setting_option_values[ $_internal ] ) ) {
					$this->setting_option_values[ $_internal ] = '';
				}
			}
		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 3.0.0
		 */
		public function load_settings_fields() {
			$field_name_wrap             = false;
			$this->setting_option_fields = array(
				'course_disable_lesson_progression' => array(
					'name'    => 'course_disable_lesson_progression',
					'label'   => sprintf(
						// translators: placeholder: Course.
						esc_html_x( '%s Progression', 'placeholder: Course', 'learndash' ),
						learndash_get_custom_label( 'course' )
					),
					'type'    => 'radio',
					'options' => array(
						''   => array(
							'label'       => esc_html__( 'Linear', 'learndash' ),
							'description' => sprintf(
								// translators: placeholder: course, courses.
								esc_html_x( 'Student must progress through the %1$s in the designated sequence. Linear Progress does not work with Open %2$s.', 'placeholder: Course', 'learndash' ),
								esc_html( learndash_get_custom_label_lower( 'course' ) ),
								esc_html( learndash_get_custom_label_lower( 'courses' ) )
							),
						),
						'on' => array(
							'label'       => esc_html__( 'Free form', 'learndash' ),
							'description' => sprintf(
								// translators: placeholder: Course.
								esc_html_x( 'Allows the student to move freely through the %s without following the designated step sequence', 'placeholder: Course', 'learndash' ),
								esc_html( learndash_get_custom_label_lower( 'course' ) )
							),
						),
					),
					'value'   => $this->setting_option_values['course_disable_lesson_progression'],
					'default' => '',
					'rest'    => array(
						'show_in_rest' => LearnDash_REST_API::enabled(),
						'rest_args'    => array(
							'schema' => array(
								'field_key'   => 'progression_disabled',
								// translators: placeholder: Course.
								'description' => sprintf( esc_html_x( '%s Progression Disabled', 'placeholder: Course', 'learndash' ), learndash_get_custom_label( 'course' ) ),
								'type'        => 'boolean',
								'default'     => false,
							),
						),
					),
				),
			);

			/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_metabox_key );

			parent::load_settings_fields();
		}

		/**
		 * Filter settings values for metabox before save to database.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $settings_values Array of settings values.
		 * @param string $settings_metabox_key Metabox key.
		 * @param string $settings_screen_id Screen ID.
		 *
		 * @return array $settings_values.
		 */
		public function filter_saved_fields( $settings_values = array(), $settings_metabox_key = '', $settings_screen_id = '' ) {
			if ( ( $settings_screen_id === $this->settings_screen_id ) && ( $settings_metabox_key === $this->settings_metabox_key ) ) {

				if ( ! isset( $settings_values['course_disable_lesson_progression'] ) ) {
					$settings_values['course_disable_lesson_progression'] = '';
				}

				/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
				$settings_values = apply_filters( 'learndash_settings_save_values', $settings_values, $this->settings_metabox_key );
			}

			return $settings_values;
		}

		// End of functions.
	}

	add_filter(
		'learndash_post_settings_metaboxes_init_' . learndash_get_post_type_slug( 'course' ),
		function( $metaboxes = array() ) {
			if ( ( ! isset( $metaboxes['LearnDash_Settings_Metabox_Course_Navigation_Settings'] ) ) && ( class_exists( 'LearnDash_Settings_Metabox_Course_Navigation_Settings' ) ) ) {
				$metaboxes['LearnDash_Settings_Metabox_Course_Navigation_Settings'] = LearnDash_Settings_Metabox_Course_Navigation_Settings::add_metabox_instance();
			}

			return $metaboxes;
		},
		50,
		1
	);
}
