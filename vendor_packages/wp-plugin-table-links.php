<?php

if ( class_exists( 'QuadLayers\\WP_Plugin_Table_Links\\Load' ) ) {
	new \QuadLayers\WP_Plugin_Table_Links\Load(
		QUADLAYERS_WCIM_PLUGIN_FILE,
		array(
			array(
				'text' => esc_html__( 'Settings', 'wc-invoice-manager' ),
				'url'  => admin_url( 'admin.php?page=wcim' ),
				'target' => '_self',
			),
			
			array(
				'text' => esc_html__( 'Premium', 'wc-invoice-manager' ),
				'url'  => QUADLAYERS_WCIM_PREMIUM_SELL_URL,
			),
			array(
				'place' => 'row_meta',
				'text'  => esc_html__( 'Support', 'wc-invoice-manager' ),
				'url'   => QUADLAYERS_WCIM_SUPPORT_URL,
			),
			array(
				'place' => 'row_meta',
				'text'  => esc_html__( 'Documentation', 'wc-invoice-manager' ),
				'url'   => QUADLAYERS_WCIM_DOCUMENTATION_URL,
			),
		)
	);
}
