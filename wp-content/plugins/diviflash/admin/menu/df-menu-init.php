<?php

require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

class DF_Menu_Admin_Init {

	public static $menu_item_settings_key = '_df_mega_menu_item_settings';

	const META_KEY = '_df_am_dashboard';

	function __construct() {
		add_action( 'rest_api_init', [ $this, 'df_register_menu_ex_route' ] );
		add_action( 'admin_init', [ $this, 'handle_old_menu_meta' ] );
		add_action( 'admin_footer', [ $this, 'render_container_for_dashboard' ] );
		// load menu dashboard styles and scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'load_styles_scripts' ] );
	}

	/**
	 * Handle old post meta which should be term meta.
	 *
	 * @return void
	 */
	public function handle_old_menu_meta() {
		$is_latest = '1.4.5' >= DIFL_VERSION;
		$meta_key  = self::META_KEY;

		if ( ! $is_latest ) {
			return;
		}

		global $wpdb;

		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key=%s", $meta_key ) );

		if ( ! $posts ) {
			return;
		}

		foreach ( $posts as $post ) {
			$post_id    = $post->post_id;
			$meta_value = $post->meta_value;

			$term = get_term( $post_id, 'nav_menu' );
			if ( empty( $term ) || is_wp_error( $term ) ) {
				continue;
			}
			update_term_meta( $post_id, self::META_KEY, $meta_value );
		}
	}

	/**
	 * Check current screen
	 * If the screen is not nav-menus then return false
	 *
	 * @return boolean
	 */
	public function check_current_screen() {
		$screen = get_current_screen();

		return $screen->id === 'nav-menus' ? true : false;
	}

	/**
	 * Render container for dashboard
	 *
	 * @return void
	 */
	public function render_container_for_dashboard() {
		if ( ! $this->check_current_screen() ) {
			return;
		}
		echo '<div id="df-menu-dashboard"></div>';
	}

	/**
	 * Load necessary styles & scripts
	 * for DiviFlash Menu Dashboard
	 *
	 * @return void
	 */
	public function load_styles_scripts() {
		if ( ! $this->check_current_screen() ) {
			return;
		}

		$dir = __DIR__;

		$df_dashboard_asset_path = "$dir/assets/index.asset.php";

		// dashboard script
		$df_dashboard_js           = 'assets/index.js';
		$df_dashboard_script_asset = require( $df_dashboard_asset_path );
		wp_enqueue_script(
			'diviflash-menu-dashboard-admin-editor',
			plugins_url( $df_dashboard_js, __FILE__ ),
			$df_dashboard_script_asset['dependencies'],
			$df_dashboard_script_asset['version'],
			true
		);
		wp_set_script_translations( 'diviflash-menu-dashboard-admin-editor', 'divi_flash' );

		wp_localize_script( 'diviflash-menu-dashboard-admin-editor', 'df_menu', [
			'nonce'    => wp_create_nonce( 'df_menu_settings' ),
			'layouts'  => wp_json_encode( $this->df_get_library_items_for_menu() ),
			'site_url' => get_site_url(),
		] );

		// dashboard css
		$df_dashboard_css = 'assets/index.css';
		wp_enqueue_style(
			'diviflash-menu-dashboard-admin',
			plugins_url( $df_dashboard_css, __FILE__ ),
			[ 'wp-components' ],
			filemtime( "$dir/$df_dashboard_css" )
		);
	}

	/**
	 * Registering Rest API endpoints.
	 *
	 * - get-nav-menu
	 * - get-nav-menu-items
	 * - save-nav-menu-items
	 *
	 * @return void
	 */
	public function df_register_menu_ex_route() {
		register_rest_route( 'df-menu-settings/v2', '/get-nav-menu', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_menu_items_data_callback' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			},
		] );
		register_rest_route( 'df-menu-settings/v2', '/get-nav-menu-items', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_nav_menu_items_callback' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			},
		] );
		register_rest_route( 'df-menu-settings/v2', '/save-nav-menu-items', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'save_nav_menu_items_callback' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			},
		] );
		register_rest_route( 'df-menu-settings/v2', '/df-am-option-edit', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'df_am_option_edit_callback' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			},
		] );
		register_rest_route( 'df-menu-settings/v2', '/df-am-option-edit-set', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'df_am_option_edit_set_callback' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			},
		] );
		register_rest_route( 'df-menu-settings/v2', '/df-am-export-menu', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'df_am_export_menu' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			},
		] );
		register_rest_route( 'df-menu-settings/v2', '/df-am-import-menu', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'df_am_import_menu' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			},
		] );
	}

	public function df_am_export_menu( $request ) {
		$items         = $this->get_nav_menu_items_callback( $request );
		$data          = wp_json_encode( $this->get_menu_items_data_callback( $request ) );
		$library_items = [];
		$parent_object = [];

		foreach ( json_decode( $items ) as $id => $value ) {
			$item = json_decode( $value, true );
			if ( array_key_exists( 'library_items', $item ) && ! empty( $item['library_items'] ) ) {
				array_push( $library_items, get_post( $item['library_items'] ) );
			}

			if ( array_key_exists( 'menu_item_object_id', $item ) && ! empty( $item['menu_item_object_id'] ) ) {
				array_push( $parent_object, get_post( $item['menu_item_object_id'] ) );
			}
		}
		$response = [
			'menu'  => $data,
			'items' => $items,
		];

		if ( ! empty( $library_items ) ) {
			$response['library_items'] = wp_json_encode( $library_items );
		}

		if ( ! empty( $parent_object ) ) {
			$response['parent_object'] = wp_json_encode( $parent_object );
		}

		wp_send_json( wp_json_encode( $response ) );
	}

	public function df_am_import_menu( $request ) {
        $menuId = $this->get_menu_id( $request );
        if ( is_wp_error( $menuId ) && array_key_exists( 'menu_exists', $menuId->errors ) ) {
            $request->set_param( 'menuId', $request->get_param( 'menuId' ) . '_' . wp_rand( 1, 20 ) );
            $menuId = $this->get_menu_id( $request );
        }
        $settings         = json_decode( $request['settings'], true );
		$meta_items       = json_decode( $settings['items'] );
		$menu_items       = json_decode( $settings['menu'] );
		$library_items    = array_key_exists( 'library_items', $settings ) ? json_decode( $settings['library_items'] ) : '';
		$parent_objects    = array_key_exists( 'parent_object', $settings ) ? json_decode( $settings['parent_object'] ) : '';
		$lib_mapper       = [];
		$menu_item_mapper = [];
		$parent_id_mapper = [];
		$parent_object_mapper = [];
		$menu_meta_keys   = [
			'type',
			'menu_item_parent',
			'object',
			'target',
			'classes',
			'xfn',
			'url',
		];
		$menu_meta_prefix = '_menu_item_';
		if ( ! empty( $library_items ) ) {
			foreach ( $library_items as $library_item ) {
				$old_id = $library_item->ID;
				unset( $library_item->ID );
				$post_id               = wp_insert_post( $library_item );
				$lib_mapper[ $old_id ] = $post_id;
			}
		}

		if ( ! empty( $parent_objects ) ) {
			foreach ( $parent_objects as $parent_object ) {
				$old_id = $parent_object->ID;
				unset( $parent_object->ID );
				$post_id               = wp_insert_post( $parent_object );
				$parent_object_mapper[ $old_id ] = $post_id;
			}
		}

		if ( ! empty( $menu_items ) ) {
			foreach ( $menu_items as $menu_item ) {
				$old_id = $menu_item->ID;
				unset( $menu_item->ID );
				unset( $menu_item->guid );
				$post_id              = wp_insert_post( $menu_item );
				$menu_item->object_id = $post_id;
				foreach ( $menu_meta_keys as $meta_key ) {
					update_post_meta( $post_id, $menu_meta_prefix . $meta_key, $menu_item->$meta_key );
				}

				$term = get_term( $menuId, 'nav_menu' );
				wp_set_object_terms( $post_id, $term->term_taxonomy_id, 'nav_menu' );
				$menu_item_mapper[ $old_id ] = $post_id;
				$parent_id_mapper[ $old_id ] = $menu_item->menu_item_parent;
			}
		}

		foreach ( $meta_items as $id => $value ) {
			$item                        = json_decode( $value, true );
			$item['menu_id']             = $menuId;
			$item['menu_item_id']        = $menu_item_mapper[ ! empty( $item['menu_item_id'] ) ? $item['menu_item_id'] : 0 ];
			$item['library_items']       = $lib_mapper[ ! empty( $item['library_items'] ) ? $item['library_items'] : 0 ];
			$item['menu_item_parent_id'] = ! empty( $item['menu_item_parent_id'] ) ? $item['menu_item_parent_id'] : 0;
			$post_id                     = $item['menu_item_id'];
			update_post_meta( $post_id, self::$menu_item_settings_key, wp_json_encode( $item ) );
			update_post_meta( $post_id, '_menu_item_object_id', $parent_object_mapper[$item['menu_item_object_id']] );
		}

		foreach ( $menu_item_mapper as $id => $new_id ) {
			if ( empty( $parent_id_mapper[ $id ] ) ) {
				continue;
			}
			update_post_meta( $new_id, '_menu_item_menu_item_parent', $menu_item_mapper[ $parent_id_mapper[ $id ] ] );
		}

		update_term_meta( $menuId, self::META_KEY, 'on' );

		wp_send_json( [ 'message' => 'Successfully Imported', 'success' => true, 'redirectURL' => admin_url( 'nav-menus.php?menu=' . $menuId ) ] );
	}

	public function get_menu_id( $request ) {
		$menuId = $request['menuId'];
		if ( empty( intval( $menuId ) ) ) {
			$menuId = wp_create_nav_menu( $menuId );
		}

		return $menuId;
	}
	public function df_am_option_edit_callback( WP_REST_Request $request ) {
		$id      = sanitize_key( $request['id'] );
		$options = get_term_meta( $id, self::META_KEY, true );

		return $options;
	}

	public function df_am_option_edit_set_callback( WP_REST_Request $request ) {
		$id      = sanitize_key( $request['id'] );
		$options = $request['_opt'];

		$options = $options == 'on' ? 'off' : 'on';

		update_term_meta( $id, self::META_KEY, $options );

		return $options;
	}

	/**
	 * Rest API callback.
	 * Process the request and return menu items.
	 *
	 * @return string | response
	 */
	public function get_nav_menu_items_callback( WP_REST_Request $request ) {
		$menu_id = sanitize_key( $request['id'] );
		$menu    = wp_get_nav_menu_object( $menu_id );

		if ( is_nav_menu( $menu ) ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id, [ 'post_status' => 'any' ] );
			$a          = [];
			foreach ( $menu_items as $key => $menu_item ) {
				$meta                            = get_post_meta( $menu_item->ID, DF_Menu_Admin_Init::$menu_item_settings_key, true );
				$parent_id                       = get_post_meta( $menu_item->ID, '_menu_item_menu_item_parent', true );
				$meta                            = json_decode( $meta, true );
				$meta['menu_id']                 = (string) $menu->term_id;
				$meta['menu_item_id']            = (string) $menu_item->ID;
				$meta['menu_item_object_id']     = get_post_meta( $menu_item->ID, '_menu_item_object_id', true );
				$meta['menu_item_parent_id']     = $parent_id;
				$meta['parent_mega_menu']        = DF_Menu_Admin_Init::get_parent_item_data( $menu_item->menu_item_parent, 'mega_menu' );
				$meta['parent_mega_menu_column'] = DF_Menu_Admin_Init::get_parent_item_data( $menu_item->menu_item_parent, 'mega_menu_column' );
				$meta                            = wp_json_encode( $meta );
				$a[ $menu_item->ID ]             = $meta;
			}

			return wp_json_encode( $a );
		}

		return 'No menu found';
	}

	/**
	 * Request API callback.
	 * Process the request and save menu items data.
	 *
	 * @return string | response
	 */
	public function save_nav_menu_items_callback( WP_REST_Request $request ) {
		$menu_id   = sanitize_key( $request['id'] );
		$menu_data = $request['menu'];
		$menu      = wp_get_nav_menu_object( $menu_id );

		if ( is_nav_menu( $menu ) ) {

			foreach ( $menu_data as $id => $data ) {
				update_post_meta( $id, DF_Menu_Admin_Init::$menu_item_settings_key, $data );
			}

			return 'Successfully Saved';
		}

		return 'No menu found';
	}

	/**
	 * Get the parent has mega menu
	 * enabled/disabled
	 *
	 * @param string | $id
	 * @param string | $key
	 *
	 * @return string
	 */
	public static function get_parent_item_data( $id, $key ) {
		$parent        = get_post_meta( $id, DF_Menu_Admin_Init::$menu_item_settings_key, true );
		$parent_object = json_decode( $parent, true );

		return isset( $parent_object[ $key ] ) ? $parent_object[ $key ] : null;
	}

	/**
	 * Menu item data array
	 *
	 * @param object $request
	 *
	 * @return string
	 */
	public function get_menu_items_data_callback( WP_REST_Request $request ) {
		$menu_id = sanitize_key( $request['id'] );
		$menu    = wp_get_nav_menu_object( $menu_id );

		if ( is_nav_menu( $menu ) ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id, [ 'post_status' => 'any' ] );

			return $menu_items;
		}

		return ['No menu found'];
	}

	/**
	 * Get layouts from the Divi Library
	 * and create a new array
	 *
	 * @return array
	 */
	public function df_get_library_items_for_menu() {
		$args       = [
			'post_type'      => 'et_pb_layout',
			'posts_per_page' => - 1,
		];
		$item_array = [
			[ 'label' => 'Select Layout', 'value' => 'none' ],
		];
		$lib_items  = get_posts( $args );
		foreach ( $lib_items as $lib_item ) {
			$new_layout          = [];
			$new_layout['value'] = $lib_item->ID;
			$new_layout['label'] = $lib_item->post_title;
			$item_array[]        = $new_layout;
		}

		return $item_array;
	}

}

new DF_Menu_Admin_Init;
