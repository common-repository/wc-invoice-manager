<?php
namespace QuadLayers\WCIM\Services;

use Exception;
use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Models\Admin_Menu_Settings_Model;
use QuadLayers\WCIM\Services\File;

class Template {

	public function get_id() {
		$settings_model = Admin_Menu_Settings_Model::instance();
		$settings       = $settings_model->get();

		if ( ! $settings['template_id'] ) {
			return;
		}

		$template = get_post( $settings['template_id'] );

		if ( ! $template || ! isset( $template->post_status ) ) {
			return;
		}

		if ( 'publish' !== $template->post_status ) {
			return;
		}

		return intval( $settings['template_id'] );
	}

	public function get_blocks() {
		$template_id = $this->get_id();

		$template_post = $template_id ? get_post( $template_id ) : null;

		if ( ! $template_post ) {
			$blocks = Helpers::build_block_template( get_post_type_object( 'invoice-template' )->template );
		} else {
			$blocks = $template_post->post_content;
		}

		return $blocks;
	}

	public function get_edit_url() {

		$template_id = $this->get_id();

		$template_exists = ! empty( $template_id ) ? true : false;

		if ( ! $template_exists ) {
			return admin_url( 'post-new.php?post_type=invoice-template' );
		}
		return admin_url( "post.php?post={$template_id}&action=edit" );
	}

	public function get_list_url() {
		return admin_url( 'edit.php?post_type=invoice-template' );
	}

	public function get_attributes() {
		$blocks         = $this->get_blocks();
		$content_blocks = parse_blocks( $blocks );
		$attributes     = $content_blocks[0]['attrs'];
		return $attributes;
	}

	public function get_meta() {
		$template_id = $this->get_id();

		if ( ! $template_id ) {
			return array();
		}

		return get_post_meta( $template_id, 'paper', true );
	}

	public function render( Invoice $invoice ) {

		/**
		 * Set invoice global to be used in Invoice_Template::enqueue_scripts and Invoice_Template::dequeue_styles
		 *
		 * @var Invoice
		 */
		$GLOBALS['quadlayers_wcim_invoice'] = $invoice;

		/**
		 * Return error if invoice order_id not found
		 */
		if ( ! $invoice->get_order() ) {
			throw new Exception( sprintf( esc_html__( 'Order %s not found', 'wc-invoice-manager' ), esc_attr( $invoice->get( 'order_id' ) ) ) );
		}

		$blocks = $this->get_blocks();

		ob_start();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
			<head>
				<title><?php echo esc_html( $invoice->get_filename() ); ?></title>
				<meta charset="<?php bloginfo( 'charset' ); ?>">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<?php echo _wp_get_iframed_editor_assets()['styles']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<style>
					<?php echo Helpers::build_block_theme_supports_css();  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo Helpers::wp_get_global_stylesheet();  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</style>
			</head>
			<?php echo do_blocks( $blocks ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render PDF.
	 */
	public function get_pdf( Invoice $invoice, $force_update = false ) {

		$file             = new File();
		$invoice_filename = $invoice->get_filename();

		list( $pdf, $filename ) = $file->get( $invoice_filename );

		if ( ! isset( $pdf, $filename ) || $force_update ) {
			$pdf                    = new PDF();
			$html                   = $this->render( $invoice );
			$attributes             = $this->get_attributes();
			$size                   = isset( $attributes['size'] ) ? $attributes['size'] : null;
			$orientation            = isset( $attributes['orientation'] ) ? $attributes['orientation'] : null;
			list( $pdf, $filename ) = $pdf->create( $html, $size, $orientation, $invoice_filename );
			return $file->create( $pdf, $filename );
		}
		return array( $pdf, $filename );
	}
}
