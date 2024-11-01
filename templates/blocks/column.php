<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$width = $attributes['width'];
?>
<td class="<?php echo esc_attr( $classnames ); ?>" style="width: <?php echo esc_attr( $width ); ?>">
	<?php echo trim( $content );  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</td>
