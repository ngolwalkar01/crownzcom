<?php

class TinCan_Content_List_Table extends \WP_List_Table {

	public $site_id;
	public $data;
	public $count;
	public $per_page = 20;

	private $columns          = array();
	private $sortable_columns = array();

	public function __construct( $args = array() ) {
		parent::__construct();
	}

	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'column':
				if ( is_array( $value ) ) {
					foreach ( $value as $key => $val ) {
						$key                   = 'ID' === $key ? 'id' : $key;
						$this->columns[ $key ] = $val;
					}
				}
				break;

			case 'sortable_columns':
				if ( is_array( $value ) ) {
					foreach ( $value as $key => $val ) {
						$key                            = 'ID' === $key ? 'id' : $key;
						$this->sortable_columns[ $key ] = array( $key, true );
					}
				}
				break;

			case 'extra_tablenav':
				$this->extra_tablenav = $value;
				break;
		}
	}

	public function prepare_items() {
		global $wpdb;
		$paged                 = $this->get_pagenum();
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$search   = '';
		$limit    = '';
		$order_by = '';
		if ( ! empty( ultc_get_filter_var( 'search_key', '' ) ) ) {
			$search = $wpdb->_real_escape( ultc_filter_input( 'search_key' ) );
		}
		$order   = ! empty( ultc_get_filter_var( 'order', '' ) ) ? $wpdb->_real_escape( ultc_filter_input( 'order' ) ) : 'DESC';
		$orderby = ! empty( ultc_get_filter_var( 'orderby', '' ) ) ? $wpdb->_real_escape( ultc_filter_input( 'orderby' ) ) : 'ID';

		if ( ! empty( $orderby ) && ! empty( $order ) ) {
			$order_by = ' ORDER BY ' . $orderby . ' ' . $order;
		}
		if ( ! empty( $paged ) && ! empty( $this->per_page ) ) {
			$offset = ( $paged - 1 ) * $this->per_page;
			$limit  = ' LIMIT ' . (int) $offset . ',' . (int) $this->per_page;
		} else {
			$limit = ' LIMIT 0,' . (int) $this->per_page;
		}
		$data = \TINCANNYSNC\Database::get_contents( $search, $limit, $order_by );

		$contents = array();
		if ( ! empty( $data ) ) {
			foreach ( $data as $post ) {
				$src        = site_url( $post->url );
				$src        = apply_filters( 'tincanny_module_url_preview', $src, $post );
				$user       = wp_get_current_user();
				$user_data  = is_a( $user, 'WP_User' ) && isset( $user->data ) ? $user->data : false;
				$user_name  = $user_data && isset( $user_data->display_name ) ? $user_data->display_name : 'Unknown';
				$user_name  = str_replace( array( '"', "'" ), array( '', '' ), $user_name );
				$user_email = $user_data && isset( $user_data->user_email ) ? $user_data->user_email : 'Unknown@anonymous.com';

				$args = array(
					'endpoint'    => \UCTINCAN\Init::$endpint_url . '/',
					'auth'        => 'LearnDashId' . $post->ID,
					'actor'       => rawurlencode(
						sprintf(
							'{"name": ["%s"], "mbox": ["mailto:%s"]}',
							$user_name,
							$user_email
						)
					),
					'activity_id' => $src,
					'client'      => $post->type,
					'base_url'    => get_option( 'home' ),
					'nonce'       => wp_create_nonce( 'tincanny-module' ),
					'TB_iframe'   => 'true',
					'width'       => '800',
					'height'      => 'auto',
				);

				$src = add_query_arg( $args, $src );

				$content = array(
					'id'      => $post->ID,
					'content' => $post->content,
					'type'    => $post->type,
					'actions' => '<a href="' . $src . '" class="snc_preview thickbox" data-item_id="' . $post->ID . '">Preview</a> | <a href="#TB_inline?height=150&width=400&inlineId=tclr-replace-content" class="snc_replace_confirm thickbox" data-item_id="' . $post->ID . '">Replace</a> | <a href="#" class="delete" data-item_id="' . $post->ID . '">Delete</a>',
				);

				$contents[] = $content;
			}
		}

		$this->items = $contents;

		$count      = \TINCANNYSNC\Database::get_contents_count( $search );
		$totalpages = ceil( $count / $this->per_page );
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'total_pages' => $totalpages,
				'per_page'    => ( $this->per_page ) ? $this->per_page : 100,
			)
		);
	}

	public function no_items() {
		esc_html_e( 'No Items found.' );
	}

	protected function get_views() {
		/*
				$view["view_all"] = sprintf( '<a href="%s">View all Codes</a>', add_query_arg( array( "group_id" => "all" ), remove_query_arg( array( "orderby", "order") ) ) );
				return $view;
		*/
	}


	public function single_row( $row ) {
		$r = "<tr data-item_id='{$row['id']}'>";
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$r .= '<td>';

			if ( array_key_exists( $column_name, $this->columns ) ) {
				if ( isset( $this->columns[ $column_name ] ) && isset( $row[ $column_name ] ) ) {
					$r .= $row[ $column_name ];
				}
			}

			$r .= '</td>';
		}

		$r .= '</tr>';

		return $r;
	}

	public function get_columns() {
		return $this->columns;
	}

	protected function get_sortable_columns() {
		return $this->sortable_columns;
	}

	public function display_rows() {
		foreach ( $this->items as $group ) {
			echo "\n\t" . $this->single_row( $group ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	protected function extra_tablenav( $which ) {
		switch ( $which ) {
			case 'top':
				$filter_html = '';
				$filter_html .= '<form id="content-filter" method="get" style="margin-top: -25px">';
				$filter_html .= '<input type="hidden" name="page" value="manage-content"/>';

				$filter_html .= '<p class="search-box">';
				$filter_html .= '<input type="text" name="search_key" value="' . esc_attr( ultc_get_filter_var( 'search_key', '' ) ) . '"/>';
				$filter_html .= '<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Search">';
				$filter_html .= '</p><br class="clear">';
				$filter_html .= '</form><a href="media-upload.php?type=snc&tab=upload&min-height=400&no_tab=1&TB_iframe=true" class="page-title-action thickbox">' . __( 'Upload Content', 'uncanny-learndash-reporting' ) . '</a>';
				echo $filter_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'bottom':
				echo '';
				break;
		}
	}
}
