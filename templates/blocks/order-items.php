<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use QuadLayers\WCIM\Helpers;
$header            = $attributes['header'];
$header_color      = $attributes['header_color'];
$header_background = $attributes['header_background'];
$footer            = $attributes['footer'];
$footer_color      = $attributes['footer_color'];
$footer_background = $attributes['footer_background'];
$align             = $attributes['align'];

$invoice_currency = isset( $block->context['invoice/currency'] ) ? $block->context['invoice/currency'] : 'symbol';
// Calculate subtotal.
$subtotal = 0;
foreach ( $quadlayers_wcim_invoice->get_order()->get_items() as $item ) {
	$subtotal += floatval( $item['subtotal'] );
}

// Get order currency and format total values.
$order_currency        = $quadlayers_wcim_invoice->get_order()->get_currency() ?? 'USD'; // Default to 'USD' if not set
$order_currency_symbol = get_woocommerce_currency_symbol( $order_currency ) ?? '$'; // Default to '$' if not set
$order_currency        = 'symbol' === $invoice_currency ? $order_currency_symbol : $order_currency;

$order_total          = Helpers::get_number_format( $order_currency, $quadlayers_wcim_invoice->get_order()->get_total() );
$order_total_items    = Helpers::get_number_format( $order_currency, $subtotal );
$order_total_tax      = Helpers::get_number_format( $order_currency, $quadlayers_wcim_invoice->get_order()->get_total_tax() );
$order_total_discount = Helpers::get_number_format( $order_currency, $quadlayers_wcim_invoice->get_order()->get_discount_total() );
$order_total_shipping = Helpers::get_number_format( $order_currency, $quadlayers_wcim_invoice->get_order()->get_shipping_total() );

$total_quantity = 0;
$total_tax      = 0;
$total_subtotal = 0;
$total_overall  = 0;

// Iterate over line items to calculate totals.
foreach ( $quadlayers_wcim_invoice->get_order()->get_items() as $item ) {
	$total_quantity += intval( $item->get_quantity() );
	$total_tax      += floatval( $item->get_total_tax() );
	$total_subtotal += floatval( $item->get_subtotal() );
	$total_overall  += floatval( $item->get_total() );
}
?>

<?php
$column_count         = count(
	array_filter(
		array(
			'num'      => $attributes['column_num'],
			'image'    => $attributes['column_image'],
			'sku'      => $attributes['column_sku'],
			'product'  => $attributes['column_product'],
			'quantity' => $attributes['column_quantity'],
			'price'    => $attributes['column_price'],
			'subtotal' => $attributes['column_subtotal'],
			'taxes'    => $attributes['column_taxes'],
			'total'    => $attributes['column_total'],
		),
		function ( $value ) {
			return ! empty( $value );
		}
	)
);
$style_header_padding = Helpers::convert_padding_to_style( $attributes['header_padding'] );
$style_header_border  = Helpers::convert_border_to_style( $attributes['header_border'] );

$style_cell_padding = Helpers::convert_padding_to_style( $attributes['cell_padding'] );
$style_cell_border  = Helpers::convert_border_to_style( $attributes['cell_border'] );

$style_footer_padding = Helpers::convert_padding_to_style( $attributes['footer_padding'] );
$style_footer_border  = Helpers::convert_border_to_style( $attributes['footer_border'] );
?>

<table class="<?php echo esc_attr( $classnames ); ?>" style="text-align: <?php echo esc_attr( $align ); ?>">
	<?php if ( $header ) : ?>
		<thead style="color: <?php echo esc_attr( $header_color ); ?>; background-color: <?php echo esc_attr( $header_background ); ?>">
			<tr>
				<?php if ( $attributes['column_num'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Number', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_image'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Image', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_sku'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'SKU', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_product'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Product', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_price'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Price', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_quantity'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Quantity', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_subtotal'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Subtotal', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_taxes'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Taxes', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_total'] ) : ?>
					<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
						<?php echo esc_html__( 'Total', 'wc-invoice-manager' ); ?>
					</th>
				<?php endif; ?>
			</tr>
		</thead>
	<?php endif; ?>
	<tbody>
		<?php
		$i = 1;
		foreach ( $quadlayers_wcim_invoice->get_order()->get_items() as $item ) :
			$product = wc_get_product( $item->get_data()['product_id'] );
			if ( ! $product = wc_get_product( $item->get_data()['product_id'] ) ) {
				$product = new WC_Product();
			}
			// Skip free products if option is enabled.
			if ( ! $attributes['free_products'] && ( empty( $item->get_total() ) || '0.00' === $item->get_total() || '0,00' === $item->get_total() ) ) {
				return;
			}
			$thumbnail = $product->get_image( '', array( 'title' => '' ), false );
			?>
			<tr>
				<?php if ( $attributes['column_num'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( $i ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_image'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo wp_kses_post( $thumbnail ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_sku'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( $product->get_sku() ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_product'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( $product->get_name() ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_price'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( Helpers::get_number_format( $order_currency, $item->get_subtotal() / $item->get_quantity() ) ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_quantity'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( $item->get_quantity() ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_subtotal'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( Helpers::get_number_format( $order_currency, $item->get_subtotal() ) ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_taxes'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( Helpers::get_number_format( $order_currency, $item->get_subtotal_tax() ) ); ?>
					</td>
				<?php endif; ?>
				<?php if ( $attributes['column_total'] ) : ?>
					<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
						<?php echo esc_html( Helpers::get_number_format( $order_currency, $item->get_total() ) ); ?>
					</td>
				<?php endif; ?>
			</tr>
		<?php
			++$i;
		endforeach;
		?>
	</tbody>
	<?php if ( $footer ) : ?>
		<tfoot style="color: <?php echo esc_attr( $footer_color ); ?>; background-color: <?php echo esc_attr( $footer_background ); ?>">
			<tr>
				<?php if ( $attributes['column_num'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>"></th>
				<?php endif; ?>
				<?php if ( $attributes['column_image'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>"></th>
				<?php endif; ?>
				<?php if ( $attributes['column_sku'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>"></th>
				<?php endif; ?>
				<?php if ( $attributes['column_product'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>"></th>
				<?php endif; ?>
				<?php if ( $attributes['column_price'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>"></th>
				<?php endif; ?>
				<?php if ( $attributes['column_quantity'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>">
						<?php echo esc_html( $total_quantity ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_subtotal'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>">
						<?php echo esc_html( Helpers::get_number_format( $order_currency, $total_subtotal ) ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_taxes'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>">
						<?php echo esc_html( Helpers::get_number_format( $order_currency, $total_tax ) ); ?>
					</th>
				<?php endif; ?>
				<?php if ( $attributes['column_total'] ) : ?>
					<th style="<?php echo esc_js( $style_footer_padding . $style_footer_border ); ?>">
						<?php echo esc_html( Helpers::get_number_format( $order_currency, $total_overall ) ); ?>
					</th>
				<?php endif; ?>
			</tr>
		</tfoot>
	<?php endif; ?>
</table>
