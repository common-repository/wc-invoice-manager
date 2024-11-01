<?php

namespace QuadLayers\WCIM;

use WP_Theme_JSON_Resolver;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Services\Template;
use QuadLayers\WCIM\Services\Zip;

class Helpers {

	/**
	 * Get wp_head to multiple times on the same execution time.
	 */
	public static function get_wp_head() {
		static $wp_head = null;
		if ( ! empty( $wp_head ) ) {
			return $wp_head;
		}
		ob_start();
		wp_head();
		$wp_head = ob_get_contents();
		ob_end_clean();

		return $wp_head;
	}

	public static function get_style_custom_property( $property, $properties ) {
		if ( isset( $properties[ $property ] ) ) {
			$value = $properties[ $property ];
			if ( strpos( $value, 'var(' ) !== false ) {
				preg_match_all( '/var\((--[\w-]+)\)/', $value, $matches );
				foreach ( $matches[1] as $match ) {
					$value = str_replace( "var($match)", self::get_style_custom_property( $match, $properties ), $value );
				}
			}
			return $value;
		} else {
			return $property; // Return the property as is if it's not found in the array.
		}
	}

	public static function get_style_properties( $style ) {
		$pattern = '/(--[\w-]+):\s*([^;]+);/';
		preg_match_all( $pattern, $style, $matches, PREG_SET_ORDER );

		$properties = array();
		foreach ( $matches as $match ) {
			$properties[ $match[1] ] = trim( $match[2] );
		}

		$resolved_properties = array();
		foreach ( $properties as $key => $value ) {
			$resolved_properties[ $key ] = self::get_style_custom_property( $key, $properties );
		}

		return $resolved_properties;
	}

	public static function wp_get_global_stylesheet() {
		$origins = array( 'default', 'theme', 'custom' );
		$types   = array( 'variables', 'presets', 'styles', 'base-layout-styles' );
		$tree    = WP_Theme_JSON_Resolver::get_merged_data();

		// Remove variables from the list of types to fetch.
		$styles_css = $tree->get_stylesheet( array_diff( $types, array( 'variables', 'base-layout-styles' ) ), $origins );

		return self::replace_custom_properties( $styles_css );
	}

	public static function replace_custom_properties( $style ) {
		$origins          = array( 'default', 'theme', 'custom' );
		$types            = array( 'variables', 'presets', 'styles', 'base-layout-styles' );
		$tree             = WP_Theme_JSON_Resolver::get_merged_data();
		$styles_variables = $tree->get_stylesheet( $types, $origins );

		// Remove variables from the list of types to fetch.
		$styles_properties = self::get_style_properties( $styles_variables );

		foreach ( $styles_properties as $name => $value ) {
			$style = str_replace( "var($name)", $value, $style );
		}

		return $style;
	}

	public static function get_current_post_type() {
		global $post, $typenow, $current_screen;

		// We have a post so we can just return the post type from that.
		if ( $post && $post->post_type ) {
			return $post->post_type;
		} elseif ( $typenow ) {
			// Check the global $typenow - set in admin.php.
			return $typenow;
		} elseif ( $current_screen && $current_screen->post_type ) {
			// Check the global $current_screen object - set in sceen.php.
			return $current_screen->post_type;
		} elseif ( isset( $_REQUEST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Check the post_type querystring.
			return sanitize_key( $_REQUEST['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( is_single() || is_page() ) {
			// Finally make a last attempt through get_query_var.
			return get_query_var( 'post_type' );
		}
		// Full site editing support.
		$fse_template_slug = filter_input( INPUT_GET, 'postId' );

		if ( 'wcim//single-invoice-template' === $fse_template_slug ) {
			return 'invoice-template';
		}
		// We don\'tknow the post type!
		return null;
	}

	public static function get_block_classname( $block_name ) {
		return 'wp-block-' . str_replace( '/', '-', $block_name );
	}

	public static function build_block_template( array $blocks ) {
		$output = '';

		foreach ( $blocks as $block ) {
			// Start the block opening comment.
			$output .= '<!-- wp:' . $block[0];

			// If attributes exist, JSON encode them.
			if ( ! empty( $block[1] ) ) {
				$output .= ' ' . json_encode( $block[1] );
			}

			// Close the opening comment if no inner blocks, otherwise just end this tag.
			if ( empty( $block[2] ) ) {
				$output .= ' /-->';
			} else {
				$output .= ' -->';
			}

			// If innerBlocks exist, recursively call the function to build those blocks.
			if ( ! empty( $block[2] ) ) {
				$output .= self::build_block_template( $block[2] );
				// Add the block closing comment.
				$output .= '<!-- /wp:' . $block[0] . ' -->';
			}
		}

		return $output;
	}

	public static function build_block_support_classes( array $attributes ) {

		$classes            = array();
		$support_attributes = array(
			'backgroundColor'   => array( 'has-%s-background-color', 'has-background' ),
			'textColor'         => array( 'has-%s-color', 'has-text-color' ),
			'fontSize'          => 'has-%s-font-size',
			'dropCap'           => 'has-drop-cap',
			'verticalAlignment' => 'is-vertically-aligned-%s',
		);

		foreach ( $attributes as $name => $value ) {
			if ( ! isset( $support_attributes[ $name ] ) ) {
				continue;
			}

			$regex = $support_attributes[ $name ];

			if ( $value ) {
				if ( is_array( $regex ) ) {
					foreach ( $regex as $format ) {
						$classes[] = sprintf( $format, $value );
					}
				} else {
					$classes[] = sprintf( $regex, $value );
				}
			}
		}

		return implode( ' ', $classes );
	}

	public static function build_block_theme_supports_css() {
		$css = '';

		$color_palette = get_theme_support( 'editor-color-palette' );
		$font_sizes    = get_theme_support( 'editor-font-sizes' );

		if ( is_array( $color_palette ) && isset( $color_palette[0] ) ) {
			foreach ( $color_palette[0] as $color ) {
				$css .= ".has-{$color['slug']}-color { color: {$color['color']}; }";
				$css .= ".has-{$color['slug']}-background-color { background-color: {$color['color']}; }";
			}
		}

		if ( is_array( $font_sizes ) && isset( $font_sizes[0] ) ) {
			foreach ( $font_sizes[0] as $size ) {
				$css .= ".has-{$size['slug']}-font-size { font-size: {$size['size']}px; }";
			}
		}

		return $css;
	}

	public static function get_number_format( $currency = '$', $number, $decimals = 2 ) {
		if ( null !== $number && '' !== $number ) {
			return $currency . ' ' . number_format( floatval( $number ), $decimals, '.', '' );
		} else {
			return $currency . ' 0.00';
		}
	}

	public static function get_download_zip_from_invoices( $invoices_ids ) {

		$template       = new Template();
		$invoices_model = Invoices_Model::instance();

		$pdf_list = array();
		foreach ( $invoices_ids as $invoice_id ) {
			$invoice = $invoices_model->find( $invoice_id );
			if ( ! $invoice instanceof Invoice ) {
				continue;
			}
			list( $pdf, $filename ) = $template->get_pdf( $invoice );
			$pdf_list[]             = array( $pdf, $filename );
		}

		$zip = new Zip();

		// Create .zip file.
		$zip_file = $zip->create( $pdf_list );

		// Send the file to the browser.
		header( 'Content-Type: application/zip' );
		header( 'Content-disposition: attachment; filename=Invoices.zip' );
		header( 'Content-Length: ' . filesize( $zip_file ) );
		readfile( $zip_file );

		// Delete the temporary file.
		$zip->delete( $zip_file );

		// End the script to avoid sending any additional output.
		exit;
	}

	/**
	 * Get invoice ID from order_id.
	 */
	public static function get_order_invoice( $order_id ) {
		$invoices_model = Invoices_Model::instance();
		$invoice        = $invoices_model->get_by_invoice_order_id( $order_id );
		if ( ! $invoice instanceof Invoice ) {
			return false;
		}
		return $invoice;
	}

	public static function get_order_status_options() {
		$order_statuses = wc_get_order_statuses();

		$remove_status = array(
			'wc-pending',
			'wc-on-hold',
			'wc-cancelled',
			'wc-refunded',
			'wc-failed',
			'wc-checkout-draft',
		);

		$available_statuses = array();

		foreach ( $order_statuses as $value => $label ) {
			if ( ! in_array( $value, $remove_status, true ) ) {
				$value                = preg_match( '/wc-(.*)/', $value, $matches ) ? $matches[1] : null;
				$available_statuses[] = array(
					'value' => $value,
					'label' => $label,
				);
			}
		}

		return $available_statuses;
	}

	public static function get_invoices_templates() {
		$args      = array(
			'post_type'   => 'invoice-template',
			'post_status' => 'publish',
		);
		$templates = new \WP_Query( $args );
		if ( ! isset( $templates->posts[0] ) ) {
			return array();
		}
		return $templates->posts;
	}

	public static function convert_border_to_style( $style_border = array() ) {
		$add_border_style = function ( &$inline_style, $prefix, $border_style ) {
			if ( isset( $border_style['width'] ) ) {
				$inline_style .= $prefix . '-width: ' . $border_style['width'] . '; ';
			}
			if ( isset( $border_style['color'] ) ) {
				$inline_style .= $prefix . '-color: ' . $border_style['color'] . '; ';
			}
			if ( isset( $border_style['style'] ) ) {
				$inline_style .= $prefix . '-style: ' . $border_style['style'] . '; ';
			}
		};

		$inline_style = '';

		if ( array_key_exists( 'top', $style_border ) || array_key_exists( 'right', $style_border ) ||
			array_key_exists( 'bottom', $style_border ) || array_key_exists( 'left', $style_border ) ) {

			if ( isset( $style_border['top'] ) ) {
				$add_border_style( $inline_style, 'border-top', $style_border['top'] );
			}
			if ( isset( $style_border['right'] ) ) {
				$add_border_style( $inline_style, 'border-right', $style_border['right'] );
			}
			if ( isset( $style_border['bottom'] ) ) {
				$add_border_style( $inline_style, 'border-bottom', $style_border['bottom'] );
			}
			if ( isset( $style_border['left'] ) ) {
				$add_border_style( $inline_style, 'border-left', $style_border['left'] );
			}
		} else {
			$add_border_style( $inline_style, 'border', $style_border );
		}

		return $inline_style;
	}

	public static function convert_padding_to_style( $style_padding = array() ) {
		$inline_style = '';

		if ( isset( $style_padding['top'] ) ) {
			$inline_style .= 'padding-top: ' . $style_padding['top'] . '; ';
		}
		if ( isset( $style_padding['right'] ) ) {
			$inline_style .= 'padding-right: ' . $style_padding['right'] . '; ';
		}
		if ( isset( $style_padding['bottom'] ) ) {
			$inline_style .= 'padding-bottom: ' . $style_padding['bottom'] . '; ';
		}
		if ( isset( $style_padding['left'] ) ) {
			$inline_style .= 'padding-left: ' . $style_padding['left'] . '; ';
		}

		return $inline_style;
	}


	public static function get_valid_css2_value( $value ) {
		// Regex to match values inside min(), max(), or clamp().
		$regex = '/\b(min|max|clamp)\(([^)]+)\)/';

		if ( preg_match( $regex, $value, $matches ) ) {
			// Split the matched string by commas to get individual values.
			$values = explode( ',', $matches[2] );

			// Define a list of valid units.
			$units = array( 'px', 'em', '%' );

			foreach ( $values as $value ) {
				// Trim whitespace and check if the value ends with a valid unit.
				$css2_value = trim( $value );
				foreach ( $units as $unit ) {
					if ( substr( $css2_value, -strlen( $unit ) ) === $unit ) {
						return $css2_value; // Return the first value with a valid unit.
					}
				}
			}
		}

		// Return original value if no function found or no valid unit.
		return $value;
	}

	public static function get_valid_css2_font_weight( $value ) {
		if ( strpos( $value, ' ' ) !== false ) {
			$weights = explode( ' ', $value );
			return max( $weights ) > 500 ? 'bold' : 'normal';
		} else {
			return (int) $value > 500 ? 'bold' : 'normal';
		}
		return $value;
	}
}
