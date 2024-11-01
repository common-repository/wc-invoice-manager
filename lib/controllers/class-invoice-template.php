<?php

namespace QuadLayers\WCIM\Controllers;

use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Models\Admin_Menu_Settings_Model;

class Invoice_Template {

	protected static $instance;

	private function __construct() {
		add_action( 'init', array( $this, 'register_scripts' ) );
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
		add_filter( 'wp_theme_json_data_theme', array( $this, 'theme_json_data_theme' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'dequeue_assets' ), 100 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ), 100 );
		add_filter( 'render_block_data', array( $this, 'modify_heading_block' ), 100, 3 );
	}

	public function register_scripts() {
		$store     = include QUADLAYERS_WCIM_PLUGIN_DIR . 'build/store/js/index.asset.php';
		$gutenberg = include QUADLAYERS_WCIM_PLUGIN_DIR . 'build/gutenberg/js/index.asset.php';
		wp_register_style( 'wcim-gutenberg-style', plugins_url( '/build/gutenberg/css/style.css', QUADLAYERS_WCIM_PLUGIN_FILE ), array(), QUADLAYERS_WCIM_PLUGIN_VERSION );
		wp_register_style( 'wcim-gutenberg-editor', plugins_url( '/build/gutenberg/css/editor.css', QUADLAYERS_WCIM_PLUGIN_FILE ), array(), QUADLAYERS_WCIM_PLUGIN_VERSION );
		wp_register_script( 'wcim-store', plugins_url( '/build/store/js/index.js', QUADLAYERS_WCIM_PLUGIN_FILE ), $store['dependencies'], $store['version'], true );
		wp_register_script( 'wcim-gutenberg', plugins_url( '/build/gutenberg/js/index.js', QUADLAYERS_WCIM_PLUGIN_FILE ), $gutenberg['dependencies'], $gutenberg['version'], true );
	}

	public function register_post_type() {

		$labels = array(
			'name'               => esc_html__( 'Invoice Template', 'wc-invoice-manager' ),
			'singular_name'      => esc_html__( 'Invoice Template', 'wc-invoice-manager' ),
			'menu_name'          => esc_html__( 'Invoice Template', 'wc-invoice-manager' ),
			'name_admin_bar'     => esc_html__( 'Invoice Template', 'wc-invoice-manager' ),
			'add_new'            => esc_html__( 'Add New', 'wc-invoice-manager' ),
			'add_new_item'       => esc_html__( 'Add New Invoice Template', 'wc-invoice-manager' ),
			'new_item'           => esc_html__( 'New Invoice Template', 'wc-invoice-manager' ),
			'edit_item'          => esc_html__( 'Edit Invoice Template', 'wc-invoice-manager' ),
			'view_item'          => esc_html__( 'View Invoice Template', 'wc-invoice-manager' ),
			'all_items'          => esc_html__( 'Templates', 'wc-invoice-manager' ),
			'search_items'       => esc_html__( 'Search Invoice Templates', 'wc-invoice-manager' ),
			'parent_item_colon'  => esc_html__( 'Parent Template:', 'wc-invoice-manager' ),
			'not_found'          => esc_html__( 'No invoice template found.', 'wc-invoice-manager' ),
			'not_found_in_trash' => esc_html__( 'No invoice template found in Trash.', 'wc-invoice-manager' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => esc_html__( 'Description', 'wc-invoice-manager' ),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'invoice-template' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array(
				'title',
				'editor',
				'author',
				'custom-fields',
			),
			'show_in_rest'       => true,
			'template'           => array(
				array(
					'wc-invoice-manager/invoice',
					array(
						'size'  => 'legal',
						'lock'  => array(
							'move'   => false,
							'remove' => false,
						),
						'style' => array(
							'spacing' => array(
								'padding' => array(
									'top'    => '30px',
									'bottom' => '30px',
									'left'   => '30px',
									'right'  => '30px',
								),
							),
						),
					),
					array(
						array(
							'wc-invoice-manager/columns',
							array(
								'spacing' => 0,
							),
							array(
								array(
									'wc-invoice-manager/column',
									array(
										'verticalAlignment' => 'center',
										'fontSize' => 'small',
									),
									array(
										array(
											'core/site-title',
											array(
												'fontSize' => 'large',
											),
										),
										array(
											'core/site-tagline',
											array(
												'style' => array(
													'typography' => array(
														'lineHeight' => '1',
													),
												),
											),
										),
									),
								),
								array(
									'wc-invoice-manager/column',
									array(
										'verticalAlignment' => 'center',
									),
									array(
										array(
											'core/site-logo',
											array(
												'align' => 'right',
												'shouldSyncIcon' => 'true',
											),
										),
									),
								),
							),

						),
						array(
							'wc-invoice-manager/columns',
							array(
								'style' => array(
									'spacing' => array(
										'padding' => array(
											'top'    => '0',
											'bottom' => '0',
											'left'   => '0',
											'right'  => '0',
										),
										'margin'  => array(
											'top'    => '2.2em',
											'bottom' => '2.2em',
											'left'   => '0',
											'right'  => '0',
										),
									),
								),
							),
							array(
								array(
									'wc-invoice-manager/column',
									array(
										'verticalAlignment' => 'top',
										'width' => '25%',
									),
									array(
										array(
											'wc-invoice-manager/invoice-data',
											array(
												'header_padding' => array(
													'top'  => '0.5em',
													'right' => '0em',
													'bottom' => '0.5em',
													'left' => '0em',
												),
												'cell_padding' => array(
													'top'  => '0em',
													'right' => '0em',
													'bottom' => '0em',
													'left' => '0em',
												),
												'fontSize' => 'small',
											),
										),
									),
								),
								array(
									'wc-invoice-manager/column',
									array(
										'verticalAlignment' => 'top',
										'width' => '25%',
									),
									array(
										array(
											'wc-invoice-manager/order-billing-data',
											array(
												'align'    => 'right',
												'city'     => false,
												'country'  => false,
												'postcode' => false,
												'header_padding' => array(
													'top'  => '0.5em',
													'right' => '0em',
													'bottom' => '0.5em',
													'left' => '0em',
												),
												'cell_padding' => array(
													'top'  => '0em',
													'right' => '0em',
													'bottom' => '0em',
													'left' => '0em',
												),
												'fontSize' => 'small',
											),
										),
									),
								),
								array(
									'wc-invoice-manager/column',
									array(
										'verticalAlignment' => 'top',
										'width' => '25%',
									),
									array(
										array(
											'wc-invoice-manager/order-shipping-data',
											array(
												'align'    => 'right',
												'header_padding' => array(
													'top'  => '0.5em',
													'right' => '0em',
													'bottom' => '0.5em',
													'left' => '0em',
												),
												'cell_padding' => array(
													'top'  => '0em',
													'right' => '0em',
													'bottom' => '0em',
													'left' => '0em',
												),
												'fontSize' => 'small',
											),
										),
									),
								),
							),
						),
						array(
							'wc-invoice-manager/columns',
							array(
								'spacing' => 0,
							),
							array(
								array(
									'wc-invoice-manager/column',
									array(),
									array(
										array(
											'wc-invoice-manager/order-items',
											array(
												'align'    => 'center',
												'column_sku' => false,
												'column_subtotal' => false,
												'column_taxes' => false,
												'header_border' => array(
													'top'  => array( 'width' => '2px' ),
													'bottom' => array( 'width' => '2px' ),
													'left' => array(
														'width' => '0px',
														'style' => 'none',
													),
												),
												'header_color' => '#ffffff',
												'header_background' => '#63b7e1',
												'cell_padding' => array(
													'top'  => '1em',
													'right' => '0em',
													'bottom' => '0em',
													'left' => '0em',
												),
												'cell_border' => array(
													'width' => '0px',
													'style' => 'none',
												),
												'footer'   => false,
												'footer_padding' => array(
													'top'  => '5em',
													'right' => '0em',
													'bottom' => '0em',
													'left' => '0em',
												),
												'footer_border' => array(
													'width' => '0px',
													'style' => 'none',
												),
												'style'    => array(
													'spacing' => array(
														'padding' => array(
															'top'  => '0',
															'bottom' => '0',
															'left' => '0',
															'right' => '0',
														),
														'margin'  => array(
															'top'  => '0',
															'bottom' => '0',
															'left' => '0',
															'right' => '0',
														),
													),
													'typography' => array(
														'lineHeight' => '1.5',
													),
												),
												'fontSize' => 'small',
											),
										),
										array(
											'wc-invoice-manager/order-totals',
											array(
												'align'    => 'right',
												'header'   => false,
												'header_padding' => array(
													'top'  => '0em',
													'right' => '0em',
													'bottom' => '0em',
													'left' => '0em',
												),
												'cell_padding' => array(
													'top'  => '0.5em',
													'right' => '1.4em',
													'bottom' => '0.5em',
													'left' => '0.5em',
												),
												'style'    => array(
													'typography' => array(
														'lineHeight' => '1.5',
													),
													'spacing' => array(
														'margin' => array(
															'top' => '30px',
														),
													),
												),
												'fontSize' => 'small',
											),
										),
									),
								),
							),
						),
					),
				),

			),
			'template_lock'      => 'insert',
		);

		register_post_type( 'invoice-template', $args );

		register_post_meta(
			'invoice-template',
			'paper',
			array(
				'type'              => 'object',
				'description'       => 'Paper properties',
				'single'            => true,
				'default'           => array(
					// 'size'        => 'a4',
					// 'orientation' => 'portrait',
					'zoomLevel' => 100,
					// 'padding'     => array(
					// 'top'    => 5,
					// 'bottom' => 5,
					// 'right'  => 5,
					// 'left'   => 5,
					// ),
				),
				'show_in_rest'      => array(
					// 'schema' => array(
					// 'type'       => 'object',
					// 'properties' => array(
					// 'size'        => array(
					// 'type' => 'string',
					// ),
					// 'orientation' => array(
					// 'type' => 'string',
					// ),
					// 'zoomLevel'   => array(
					// 'type' => 'num',
					// ),
					// 'padding'     => array(
					// 'type'       => 'object',
					// 'properties' => array(
					// 'top'    => array(
					// 'type' => 'num',
					// ),
					// 'bottom' => array(
					// 'type' => 'num',
					// ),
					// 'right'  => array(
					// 'type' => 'num',
					// ),
					// 'left'   => array(
					// 'type' => 'num',
					// ),
					// ),
					// ),
					// ),
					// ),
				),
				'sanitize_callback' => '',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}


	public function modify_heading_block( $parsed_block, $source_block, $parent_block ) {

		if ( ! isset( $parsed_block['attrs']['style']['typography']['fontWeight'] ) ) {
			return $parsed_block;
		}

		$font_weight = Helpers::get_valid_css2_font_weight( $parsed_block['attrs']['style']['typography']['fontWeight'] );

		$parsed_block['attrs']['style']['typography']['fontWeight'] = $font_weight;

		return $parsed_block;
	}

	public function add_theme_support() {
		global $quadlayers_wcim_invoice;

		$post_type = Helpers::get_current_post_type();

		if ( 'invoice-template' !== $post_type && ! $quadlayers_wcim_invoice instanceof Invoice ) {
			return;
		}

		add_theme_support( 'appearance-tools' );
		add_theme_support( 'disable-layout-styles' );
	}


	public function theme_json_data_theme( $theme_json ) {
		global $quadlayers_wcim_invoice;

		$post_type = Helpers::get_current_post_type();

		if ( 'invoice-template' !== $post_type && ! $quadlayers_wcim_invoice instanceof Invoice ) {
			return $theme_json;
		}
		// Get the existing data.
		$data = $theme_json->get_data();

		// Disable fluid typography if fontSizes are set.
		if ( isset( $data['settings']['typography'] ) ) {
			$data['settings']['typography']['fluid'] = false;
		}
		if ( isset( $data['settings']['typography']['fontSizes']['theme'] ) ) {
			foreach ( $data['settings']['typography']['fontSizes']['theme'] as $key => $value ) {
				$data['settings']['typography']['fontSizes']['theme'][ $key ]['size'] = Helpers::get_valid_css2_value( $value['size'] );
			}
		}

		// set to normal or bold instead of numeric values.
		if ( isset( $data['settings']['typography']['fontFamilies']['theme'] ) ) {
			foreach ( $data['settings']['typography']['fontFamilies']['theme'] as $key => $value ) {
				if ( isset( $value['fontFace'] ) ) {
					foreach ( $value['fontFace'] as $font_key => $font_value ) {
						$data['settings']['typography']['fontFamilies']['theme'][ $key ]['fontFace'][ $font_key ]['fontWeight'] =
						Helpers::get_valid_css2_font_weight( $font_value['fontWeight'] );
					}
				}
			}
		}

		if ( isset( $data['styles']['blocks'] ) ) {
			foreach ( $data['styles']['blocks'] as $key => $block ) {
				if ( isset( $block['typography']['fontWeight'] ) ) {
					$block['typography']['fontWeight'] =
					Helpers::get_valid_css2_font_weight( $block['typography']['fontWeight'] );
				}
			}
		}

		if ( isset( $data['styles']['elements'] ) ) {
			foreach ( $data['styles']['elements'] as $key => $element ) {
				if ( isset( $element['typography']['fontWeight'] ) ) {
					$element['typography']['fontWeight'] =
					Helpers::get_valid_css2_font_weight( $element['typography']['fontWeight'] );
				}
			}
		}

		if ( isset( $data['styles']['typography']['fontWeight'] ) ) {
			$data['styles']['typography']['fontWeight'] =
			Helpers::get_valid_css2_font_weight( $data['styles']['typography']['fontWeight'] );
		}
		// Disable root padding.
		if ( isset( $data['settings']['useRootPaddingAwareAlignments'] ) ) {
			$data['settings']['useRootPaddingAwareAlignments'] = false;
		}
		// Disable layout styles.
		if ( isset( $data['settings']['layout'] ) ) {
			$data['settings']['layout']['contentSize'] = 'fixed';
			$data['settings']['layout']['wideSize']    = 'fixed';
		}
		// Disable unsupported units.
		if ( isset( $data['settings']['spacing']['spacingSizes']['theme'] ) ) {
			$data['settings']['spacing']['units'] = array( 'px', 'em', '%' );
		}
		// Replace invalid spacing sizes.
		if ( isset( $data['settings']['spacing']['spacingSizes']['theme'] ) ) {
			foreach ( $data['settings']['spacing']['spacingSizes']['theme'] as $key => $value ) {
				$data['settings']['spacing']['spacingSizes']['theme'][ $key ]['size'] = Helpers::get_valid_css2_value( $value['size'] );
			}
		}
		// Disable global padding and block gap.
		if ( isset( $data['styles']['spacing']['padding']['left'] ) ) {
			$data['styles']['spacing']['padding']['left'] = '0px';
		}
		if ( isset( $data['styles']['spacing']['padding']['right'] ) ) {
			$data['styles']['spacing']['padding']['right'] = '0px';
		}
		if ( isset( $data['styles']['spacing']['blockGap'] ) ) {
			$data['styles']['spacing']['blockGap'] = '0px';
		}

		return $theme_json->update_with( $data );
	}

	/**
	 * Dequeue styles in the editor.
	 */
	public function dequeue_assets() {
		global $wp_styles, $editor_styles, $invoice;

		$post_type = Helpers::get_current_post_type();

		if ( 'invoice-template' !== $post_type && ! $invoice instanceof Invoice ) {
			return;
		}
		/**
		 * Remove all stylesheets regigstered by add_editor_style().
		 */
		$editor_styles = array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Define allowed styles.
		$wp_allowed_styles = array(
			'global-styles',
			'wp-block-library',
			'wcim-gutenberg-editor',
			'wcim-gutenberg-style',
		);

		$wp_required_styles = array(
			'admin-bar',
			'media-views',
			'imgareaselect',
			'buttons',
			'editor-buttons',
			'wp-edit-post',
			'wc-blocks-style',
			'wc-blocks-editor-style',
			'wp-block-directory',
			'wp-format-library',
			'global-styles-css-custom-properties',
		);

		$allowed_styles = array_merge( $wp_allowed_styles, $wp_required_styles );

		foreach ( $wp_styles->queue as $handle ) {
			if ( ! in_array( $handle, $allowed_styles ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}

	public function enqueue_assets() {

		global $quadlayers_wcim_invoice;

		$post_type = Helpers::get_current_post_type();

		if ( 'invoice-template' !== $post_type && ! $quadlayers_wcim_invoice instanceof Invoice ) {
			return;
		}

		$this->add_theme_support();

		wp_enqueue_style( 'wcim-gutenberg-style' );
	}

	public function enqueue_editor_assets() {

		global $quadlayers_wcim_invoice;

		$post_type = Helpers::get_current_post_type();

		if ( 'invoice-template' !== $post_type && ! $quadlayers_wcim_invoice instanceof Invoice ) {
			return;
		}

		$settings = Admin_Menu_Settings_Model::instance()->get();

		wp_enqueue_style( 'wcim-gutenberg-editor' );
		wp_enqueue_script( 'wcim-gutenberg' );
		wp_localize_script(
			'wcim-gutenberg',
			'wcim_gutenberg',
			array(
				'settings' => $settings,
			)
		);
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
