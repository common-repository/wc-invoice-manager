<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$align = $attributes['align'];
?>
<p class="<?php echo esc_attr( $classnames ); ?>" style="text-align: <?php echo esc_attr( $align ); ?>;">
	<?php echo esc_html( $quadlayers_wcim_invoice->get_order()->get_customer_note() ); ?>
</p>
