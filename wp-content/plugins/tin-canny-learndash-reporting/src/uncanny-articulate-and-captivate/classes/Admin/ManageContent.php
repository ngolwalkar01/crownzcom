<?php
/**
 * Admin Manage Content Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      3.0.0
 */

namespace TINCANNYSNC\Admin;

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class ManageContent {

	private static $tincan_database;
	private static $tincan_per_pages;

	/**
	 * Initialize
	 *
	 * @access  public
	 * @return  void
	 * @since   3.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
		add_action( 'wp_ajax_SnC_Content_Delete', array( $this, 'ajax_delete' ) );
		add_action( 'wp_ajax_SnC_Content_Bookmark_Delete', array( $this, 'ajax_delete_bookmarks_only' ) );
		add_action( 'wp_ajax_SnC_Content_Delete_All', array( $this, 'ajax_delete_all_data' ) );
	}

	/**
	 * Register Admin Menu
	 *
	 * @trigger admin_menu Action
	 * @access  public
	 * @return  void
	 * @since   3.0.0
	 */
	public function register_admin_menu() {

		add_submenu_page(
			'uncanny-learnDash-reporting',
			'Manage Content',
			'Manage Content',
			apply_filters( 'tc_manage_content_cap', 'manage_options' ),
			'manage-content',
			array(
				$this,
				'view_content_page',
			)
		);
	}

	/**
	 * Page loaded from admin_menu
	 *
	 * @trigger view_content_page
	 * @access  public
	 * @return  void
	 * @since   3.0.0
	 */
	public function view_content_page() {

		include_once dirname( UO_REPORTING_FILE ) . '/src/includes/TinCan_Content_List_Table.php';
		$tincan_content_table = new \TinCan_Content_List_Table();
		$columns              = array(
			'ID'      => __( 'ID', 'uncanny-learndash-reporting' ),
			'content' => __( 'Content', 'uncanny-learndash-reporting' ),
			'type'    => __( 'Type', 'uncanny-learndash-reporting' ),
			'actions' => __( 'Actions', 'uncanny-learndash-reporting' ),
		);

		$tincan_content_table->column = $columns;
		unset( $columns['actions'] );
		$tincan_content_table->sortable_columns = $columns;
		$tincan_content_table->prepare_items();
		$tincan_content_table->views();

		include_once SnC_PLUGIN_DIR . 'views/manage_content.php';
	}

	/**
	 * Ajax delete module
	 *
	 * @return void
	 */
	public function ajax_delete() {

		$id     = $this->get_posted_item_id();
		$module = \TINCANNYSNC\Module::get_module( $id );
		$module->delete();

		die;
	}

	/**
	 * Ajax delete bookmarks
	 *
	 * @return void
	 */
	public function ajax_delete_bookmarks_only() {

		$id     = $this->get_posted_item_id();
		$module = \TINCANNYSNC\Module::get_module( $id );
		$url    = str_replace( site_url(), '', $module->get_url() );
		\UCTINCAN\Database::delete_bookmarks( $id, $url );

		die;
	}

	/**
	 * Ajax delete all data
	 *
	 * @return void
	 */
	public function ajax_delete_all_data() {

		$id     = $this->get_posted_item_id();
		$module = \TINCANNYSNC\Module::get_module( $id );
		$url    = str_replace( site_url(), '', $module->get_url() );

		\UCTINCAN\Database::delete_all_data( $id, $url );

		die;
	}


	/**
	 * Get Item ID from POST
	 *
	 * @return mixed int | dies
	 */
	private function get_posted_item_id() {

		if ( ultc_get_filter_var( 'mode', '', INPUT_POST ) !== 'vc' ) {
			check_ajax_referer( 'snc-media_enbed_form', 'security' );
		}

		$id = ultc_get_filter_var( 'item_id', 0, INPUT_POST );
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			die;
		}

		return $id;
	}

}
