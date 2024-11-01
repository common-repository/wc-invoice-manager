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

$recipient_name = $attributes['recipient_name'] && $quadlayers_wcim_invoice->get_order()->get_billing_first_name() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_first_name() . ' ' . $quadlayers_wcim_invoice->get_order()->get_billing_last_name() ) : null;
$company_name   = $attributes['company_name'] && $quadlayers_wcim_invoice->get_order()->get_billing_company() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_company() ) : null;
$address        = $attributes['address'] && $quadlayers_wcim_invoice->get_order()->get_billing_address_1() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_address_1() . ' ' . $quadlayers_wcim_invoice->get_order()->get_billing_address_2() ) : null;
$country        = $attributes['country'] && $quadlayers_wcim_invoice->get_order()->get_billing_country() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_country() ) : null;
$city           = $attributes['city'] && $quadlayers_wcim_invoice->get_order()->get_billing_city() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_city() ) : null;
$state          = $attributes['state'] && $quadlayers_wcim_invoice->get_order()->get_billing_state() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_state() ) : null;
$postcode       = $attributes['postcode'] && $quadlayers_wcim_invoice->get_order()->get_billing_postcode() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_postcode() ) : null;
$email          = $attributes['email'] && $quadlayers_wcim_invoice->get_order()->get_billing_email() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_email() ) : null;
$phone          = $attributes['phone'] && $quadlayers_wcim_invoice->get_order()->get_billing_phone() ? esc_html( $quadlayers_wcim_invoice->get_order()->get_billing_phone() ) : null;

$style_header_padding = Helpers::convert_padding_to_style( $attributes['header_padding'] );
$style_header_border  = Helpers::convert_border_to_style( $attributes['header_border'] );

$style_cell_padding = Helpers::convert_padding_to_style( $attributes['cell_padding'] );
$style_cell_border  = Helpers::convert_border_to_style( $attributes['cell_border'] );

?>

<table class="<?php echo esc_attr( $classnames ); ?>" style="text-align: <?php echo esc_attr( $align ); ?>">
	<?php if ( $header ) : ?>
		<thead style="color: <?php echo esc_attr( $header_color ); ?>; background-color: <?php echo esc_attr( $header_background ); ?>">
			<th style="<?php echo esc_js( $style_header_padding . $style_header_border ); ?>">
				<?php echo esc_html__( 'Billing Data', 'wc-invoice-manager' ); ?>
			</th>
		</thead>
	<?php endif; ?>
	<tbody>
		<?php if ( $recipient_name ) : ?>
			<tr>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $recipient_name ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $company_name ) : ?>
			<tr>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $company_name ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $address ) : ?>
			<tr>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $address ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $country ) : ?>
			<tr>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $country ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $city || $state || $postcode ) : ?>
			<tr>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $city . $state . $postcode ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $email ) : ?>
			<tr>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $email ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $phone ) : ?>
			<tr>
				<td style="<?php echo esc_js( $style_cell_padding . $style_cell_border ); ?>">
					<?php echo esc_html( $phone ); ?>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>
