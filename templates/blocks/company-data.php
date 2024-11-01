<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="<?php echo esc_attr( $classnames ); ?>">

	<?php if ( $attributes['logo'] ) : ?>
		<div class="wp-block-wc-invoice-manager-company-data__logo">
			<img src="<?php echo esc_url( $attributes['logo_src'] ); ?>" width="<?php echo esc_url( $attributes['logo_size']['width'] ); ?>" height="<?php echo esc_url( $attributes['logo_size']['height'] ); ?>" alt="<?php echo esc_url( $attributes['title_content'] ); ?>" />
		</div>
	<?php endif; ?>

	<div class="wp-block-wc-invoice-manager-company-data__text">
		<?php if ( $attributes['title'] ) : ?>
			<h2><?php echo esc_html( $attributes['title_content'] ); ?></h2>
		<?php endif; ?>
		<?php if ( $attributes['website'] ) : ?>
			<?php echo esc_html( $attributes['website_url'] ); ?></a>
		<?php endif; ?>
		<?php if ( $attributes['description'] ) : ?>
			<?php echo esc_html( $attributes['description_content'] ); ?></a>
		<?php endif; ?>
	</div>
</div>
