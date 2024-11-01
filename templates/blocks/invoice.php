<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<body>
	<div class="<?php echo esc_attr( $classnames ); ?>">
		<?php echo trim( $content ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</body>
