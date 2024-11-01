<?php

namespace QuadLayers\WCIM\Controllers;

use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Entities\Invoice;

class Invoice_Template_Blocks {

	protected static $instance;

	private function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'allowed_block_types', array( $this, 'allowed_block_types' ), 10, 2 );
	}

	public function render_callback( $attributes, $content, $block ) {

		global $quadlayers_wcim_invoice;

		if ( ! $quadlayers_wcim_invoice instanceof Invoice ) {
			return;
		}

		/**
		 * Return error if invoice order_id not found.
		 */
		if ( ! $quadlayers_wcim_invoice->get_order() ) {
			return;
		}

		// Get the block name (e.g., 'wc-invoice-manager/order-billing-data').
		$block_name = $block->name;

		// Split the namespace and the block base name.
		list($namespace, $base_block_name) = explode( '/', $block_name );

		// Verify the namespace to ensure we're only affecting our custom blocks.
		if ( 'wc-invoice-manager' !== $namespace ) {
			return '';
		}

		// Define the path to the block template file.
		$template_path = QUADLAYERS_WCIM_PLUGIN_DIR . "/templates/blocks/{$base_block_name}.php";

		// Get the block class name.
		$block_class = Helpers::get_block_classname( $block_name );

		// Create custom unique class for block.
		$block_class_id = wp_unique_id( 'wcim__block-' );

		// Get block support classes.
		$block_support_classes = Helpers::build_block_support_classes( $attributes );

		/**
		 * Get styles for block
		 */
		$styles = wp_style_engine_get_styles(
			isset( $attributes['style'] ) ? $attributes['style'] : array(),
			array(
				'selector' => ".{$block_class_id}.{$block_class}",
				'context'  => 'block-supports',
			)
		);

		$css = isset( $styles['css'] ) ? Helpers::replace_custom_properties( wp_strip_all_tags( $styles['css'] ) ) : null;
		/**
		 * Get classnames for block
		 */
		$classnames = isset( $styles['classnames'] ) ? $styles['classnames'] : '';
		$classnames = trim( $classnames . ' ' . $block_class_id . ' ' . $block_class . ' ' . $block_support_classes );

		// If the template file exists, load and return its content.
		if ( file_exists( $template_path ) ) {
			// Start output buffering.
			ob_start();
			?>

			<?php if ( $css ) : ?>
				<style>
					<?php echo $css;  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</style>
			<?php endif; ?>

			<?php

			// You can make $attributes and $content available within the template.
			include $template_path;

			// End output buffering and return the template content.
			return ob_get_clean();
		}

		return '';
	}

	public function register_blocks() {
		$block_types = array(
			'order-customer-note' => array(

				'uses_context' => array(
					'invoice/currency',
				),
				'attributes'   => array(
					'align' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'supports'     => array(
					'spacing'           => array(
						'margin'  => true,
						'padding' => true,
					),
					'color'             => array(
						'background' => true,
						'text'       => true,
						'link'       => false,
					),
					'typography'        => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper' => true,
				),
			),
			'order-billing-data'  => array(

				'uses_context' => array(
					'invoice/currency',
				),
				'attributes'   => array(
					'align'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'recipient_name'    => array(
						'type'    => 'bool',
						'default' => true,
					),
					'company_name'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'address'           => array(
						'type'    => 'bool',
						'default' => true,
					),
					'city'              => array(
						'type'    => 'bool',
						'default' => true,
					),
					'country'           => array(
						'type'    => 'bool',
						'default' => true,
					),
					'state'             => array(
						'type'    => 'bool',
						'default' => true,
					),
					'postcode'          => array(
						'type'    => 'bool',
						'default' => true,
					),
					'email'             => array(
						'type'    => 'bool',
						'default' => true,
					),
					'phone'             => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header'            => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header_padding'    => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'header_border'     => array(
						'type'    => 'object',
						'default' => array(),
					),
					'header_color'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'header_background' => array(
						'type'    => 'string',
						'default' => '',
					),
					'cell_padding'      => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'cell_border'       => array(
						'type'    => 'object',
						'default' => array(),
					),
				),
				'supports'     => array(
					'spacing'           => array(
						'margin'  => true,
						'padding' => true,
					),
					'color'             => array(
						'background' => true,
						'text'       => true,
						'link'       => false,
					),
					'typography'        => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper' => true,
				),
			),
			'order-shipping-data' => array(

				'uses_context' => array(
					'invoice/currency',
				),
				'attributes'   => array(
					'align'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'recipient_name'    => array(
						'type'    => 'bool',
						'default' => true,
					),
					'company_name'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'address'           => array(
						'type'    => 'bool',
						'default' => true,
					),
					'country'           => array(
						'type'    => 'bool',
						'default' => true,
					),
					'city'              => array(
						'type'    => 'bool',
						'default' => true,
					),
					'state'             => array(
						'type'    => 'bool',
						'default' => true,
					),
					'postcode'          => array(
						'type'    => 'bool',
						'default' => true,
					),
					'phone'             => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header'            => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header_padding'    => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'header_border'     => array(
						'type'    => 'object',
						'default' => array(),
					),
					'header_color'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'header_background' => array(
						'type'    => 'string',
						'default' => '',
					),
					'cell_padding'      => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'cell_border'       => array(
						'type'    => 'object',
						'default' => array(),
					),
				),
				'supports'     => array(
					'spacing'           => array(
						'margin'  => true,
						'padding' => true,
					),
					'color'             => array(
						'background' => true,
						'text'       => true,
						'link'       => false,
					),
					'typography'        => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper' => true,

				),
			),
			'order-items'         => array(
				'uses_context' => array(
					'invoice/currency',
				),
				'attributes'   => array(
					'align'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'column_num'        => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_image'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_sku'        => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_product'    => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_quantity'   => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_price'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_subtotal'   => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_taxes'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'column_total'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'free_products'     => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header'            => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header_padding'    => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'header_border'     => array(
						'type'    => 'object',
						'default' => array(),
					),
					'header_color'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'header_background' => array(
						'type'    => 'string',
						'default' => '',
					),
					'cell_padding'      => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'cell_border'       => array(
						'type'    => 'object',
						'default' => array(),
					),
					'footer'            => array(
						'type'    => 'bool',
						'default' => true,
					),
					'footer_padding'    => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'footer_border'     => array(
						'type'    => 'object',
						'default' => array(),
					),
					'footer_color'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'footer_background' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'supports'     => array(
					'spacing'           => array(
						'margin'  => true,
						'padding' => true,
					),
					'color'             => array(
						'background' => true,
						'text'       => true,
						'link'       => false,
					),
					'typography'        => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper' => true,
				),
			),
			'order-totals'        => array(

				'uses_context' => array(
					'invoice/currency',
				),
				'attributes'   => array(
					'align'                => array(
						'type'    => 'string',
						'default' => '',
					),
					'order_total_items'    => array(
						'type'    => 'bool',
						'default' => true,
					),
					'order_total_tax'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'order_total_discout'  => array(
						'type'    => 'bool',
						'default' => true,
					),
					'order_total_shipping' => array(
						'type'    => 'bool',
						'default' => true,
					),
					'order_total'          => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header'               => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header_padding'       => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'header_border'        => array(
						'type'    => 'object',
						'default' => array(),
					),
					'header_color'         => array(
						'type'    => 'string',
						'default' => '',
					),
					'header_background'    => array(
						'type'    => 'string',
						'default' => '',
					),
					'cell_padding'         => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'cell_border'          => array(
						'type'    => 'object',
						'default' => array(),
					),
				),
				'supports'     => array(
					'spacing'           => array(
						'margin'  => true,
						'padding' => true,
					),
					'color'             => array(
						'background' => true,
						'text'       => true,
						'link'       => false,
					),
					'typography'        => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper' => true,

				),
			),
			'invoice'             => array(
				'parent'           => array( 'core/page' ),
				'attributes'       => array(
					'align'       => array(
						'type'    => 'string',
						'default' => '',
					),
					'currency'    => array(
						'type'    => 'string',
						'default' => 'symbol',
					),
					'size'        => array(
						'type'    => 'string',
						'default' => 'a4',
					),
					'orientation' => array(
						'type'    => 'string',
						'default' => 'portrait',
					),
				),
				'provides_context' => array(
					'invoice/currency' => 'currency',
				),
				'lock'             => array(
					'remove' => true,
					'move'   => true,
				),
				'supports'         => array(
					'multiple'          => false,
					'reusable'          => false,
					'lock'              => false,
					'inserter'          => false,
					'spacing'           => array(
						'padding' => true,
					),
					'color'             => array(
						'background' => true,
						'text'       => true,
						'link'       => false,
					),
					'typography'        => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper' => true,
				),
			),
			'invoice-data'        => array(
				'attributes' => array(
					'align'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'number'            => array(
						'type'    => 'bool',
						'default' => true,
					),
					'date'              => array(
						'type'    => 'bool',
						'default' => true,
					),
					'order_number'      => array(
						'type'    => 'bool',
						'default' => true,
					),
					'order_date'        => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header'            => array(
						'type'    => 'bool',
						'default' => true,
					),
					'header_padding'    => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'header_border'     => array(
						'type'    => 'object',
						'default' => array(),
					),
					'header_color'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'header_background' => array(
						'type'    => 'string',
						'default' => '',
					),
					'cell_padding'      => array(
						'type'    => 'object',
						'default' => array(
							'top'    => '0.5em',
							'right'  => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
						),
					),
					'cell_border'       => array(
						'type'    => 'object',
						'default' => array(),
					),
				),
				'supports'   => array(
					'spacing'           => array(
						'margin'  => true,
						'padding' => true,
					),
					'color'             => array(
						'background' => true,
						'text'       => true,
						'link'       => false,
					),
					'typography'        => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper' => true,
				),
			),
			'columns'             => array(
				'attributes' => array(
					'verticalAlignment' => array(
						'type' => 'string',
					),
					'templateLock'      => array(
						'type' => array( 'string', 'boolean' ),
						'enum' => array( 'all', 'insert', 'contentOnly', false ),
					),
					'spacing'           => array(
						'type'    => 'num',
						'default' => 15,
					),
				),
				'supports'   => array(
					'anchor'               => true,
					'html'                 => false,
					'color'                => array(
						'link'       => true,
						'background' => true,
						'text'       => true,
					),
					'spacing'              => array(
						'margin'  => true,
						'padding' => true,
					),
					'__experimentalBorder' => array(
						'color'  => true,
						'radius' => true,
						'style'  => true,
						'width'  => true,
					),
					'typography'           => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper'    => true,
				),
			),
			'column'              => array(
				'attributes' => array(
					'verticalAlignment' => array(
						'type' => 'string',
					),
					'width'             => array(
						'type'    => 'string',
						'default' => 'auto',
					),
					'allowedBlocks'     => array(
						'type' => 'array',
					),
					'templateLock'      => array(
						'type' => array( 'string', 'boolean' ),
						'enum' => array( 'all', 'insert', 'contentOnly', false ),
					),
				),
				'supports'   => array(
					'anchor'               => true,
					'reusable'             => false,
					'html'                 => false,
					'color'                => array(
						'gradients' => false,
						'link'      => true,
					),
					'spacing'              => array(
						'margin'  => true,
						'padding' => true,
					),
					'__experimentalBorder' => array(
						'color' => true,
						'style' => true,
						'width' => true,
					),
					'typography'           => array(
						'fontSize'                     => true,
						'lineHeight'                   => true,
						'__experimentalFontFamily'     => true,
						'__experimentalFontWeight'     => true,
						'__experimentalFontStyle'      => true,
						'__experimentalTextTransform'  => true,
						'__experimentalTextDecoration' => true,
						'__experimentalLetterSpacing'  => true,
					),
					'lightBlockWrapper'    => true,
				),
			),
		);

		// Register each block type with our custom render function.
		foreach ( $block_types as $block_name => $block_props ) {
			register_block_type(
				"wc-invoice-manager/{$block_name}",
				array_merge(
					array(
						'render_callback' => array( $this, 'render_callback' ),
					),
					$block_props
				)
			);
		}
	}

	public function allowed_block_types( $allowed_blocks ) {
		global $quadlayers_wcim_invoice;

		$post_type = Helpers::get_current_post_type();

		if ( 'invoice-template' !== $post_type && ! $quadlayers_wcim_invoice instanceof Invoice ) {
			return;
		}
		// Get all registered blocks.
		$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		// Filter the blocks to only include those in the 'wc-invoice-manager' namespace.
		$plugin_blocks = array_filter(
			array_keys( $registered_blocks ),
			function ( $block_name ) {
				return strpos( $block_name, 'wc-invoice-manager/' ) === 0;
			}
		);

		$core_blocks = array(
			'core/site-logo',
			'core/site-title',
			'core/site-tagline',
			'core/table',
			'core/paragraph',
			'core/heading',
			'core/verse',
			'core/pullquote',
			'core/preformatted',
			'core/quote',
			'core/image',
			'core/image',
		);

		// Add any additional blocks outside of the 'wc-invoice-manager' namespace that you want to allow.
		$allowed_blocks = array_merge(
			$plugin_blocks,
			$core_blocks
		);

		return $allowed_blocks;
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
