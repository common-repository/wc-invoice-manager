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
$date_format = 'Y-m-d H:i:s';
?>

<table class="<?php echo esc_attr( $classnames ); ?>" style="text-align: <?php echo esc_attr( $align ); ?>">
	<?php if ( $header ) : ?>
		<thead style="color: <?php echo esc_attr( $header_color ); ?>; background-color: <?php echo esc_attr( $header_background ); ?>">
			<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
				<?php echo esc_html__( 'Invoice', 'wc-invoice-manager' ); ?>
			</th>
		</thead>
	<?php endif; ?>
	<?php if ( $attributes['number'] ) : ?>
		<tr>
			<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
				<b><?php echo esc_html__( 'Number:', 'wc-invoice-manager' ); ?></b> <?php echo esc_html( $quadlayers_wcim_invoice->get( 'code' ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
	<?php if ( $attributes['date'] && null !== $quadlayers_wcim_invoice->get( 'datetime' ) ) : ?>
		<tr>
			<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
				<b><?php echo esc_html__( 'Date:', 'wc-invoice-manager' ); ?></b> <?php echo esc_html( wp_date( $date_format, strtotime( $quadlayers_wcim_invoice->get( 'datetime' ) ) ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
	<?php if ( $attributes['order_number'] ) : ?>
		<tr>
			<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
				<b><?php echo esc_html__( 'Order Number:', 'wc-invoice-manager' ); ?></b> <?php echo esc_html( $quadlayers_wcim_invoice->get_order()->get_id() ); ?>
			</td>
		</tr>
	<?php endif; ?>
	<?php if ( $attributes['order_date'] ) : ?>
		<tr>
			<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
				<b><?php echo esc_html__( 'Order Date:', 'wc-invoice-manager' ); ?></b> <?php echo esc_html( wp_date( $date_format, strtotime( $quadlayers_wcim_invoice->get_order()->get_date_created() ) ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
</table>
