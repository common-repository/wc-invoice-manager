<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use QuadLayers\WCIM\Helpers;
$header            = $attributes['header'];
$header_color      = $attributes['header_color'];
$header_background = $attributes['header_background'];
$align             = $attributes['align'];

$style_header_padding = Helpers::convert_padding_to_style( $attributes['header_padding'] );
$style_header_border  = Helpers::convert_border_to_style( $attributes['header_border'] );

$style_cell_padding = Helpers::convert_padding_to_style( $attributes['cell_padding'] );
$style_cell_border  = Helpers::convert_border_to_style( $attributes['cell_border'] );
$invoice_currency   = isset( $block->context['invoice/currency'] ) ? $block->context['invoice/currency'] : 'symbol';

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

?>

<table class="<?php echo esc_attr( $classnames ); ?>" style="text-align: <?php echo esc_attr( $align ); ?>">

	<?php if ( $header ) : ?>
		<thead style="color: <?php echo esc_attr( $header_color ); ?>; background-color: <?php echo esc_attr( $header_background ); ?>">
			<th colspan="2" style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
				<?php echo esc_html__( 'Totals', 'wc-invoice-manager' ); ?>
			</th>
		</thead>
	<?php endif; ?>
	<tbody>
		<?php if ( $order_total_items ) : ?>
			<tr>
				<th style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html__( 'Subtotal', 'wc-invoice-manager' ); ?>
				</th>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $order_total_items ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $order_total_tax ) : ?>
			<tr>
				<th style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html__( 'Taxes', 'wc-invoice-manager' ); ?>
				</th>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $order_total_tax ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $order_total_discount ) : ?>
			<tr>
				<th style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html__( 'Discount', 'wc-invoice-manager' ); ?>
				</th>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $order_total_discount ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $order_total_shipping ) : ?>
			<tr>
				<th style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html__( 'Shipping', 'wc-invoice-manager' ); ?>
				</th>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $order_total_shipping ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $order_total ) : ?>
			<tr>
				<th style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html__( 'Total', 'wc-invoice-manager' ); ?>
				</th>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $order_total ); ?>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>
