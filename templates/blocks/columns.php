<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="<?php echo esc_attr( $classnames ); ?>" >
	<tr>
		<?php echo trim( $content ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</tr>
</table>
